<?php

class DefaultTrackingStore implements TrackingStore {

	function init($params) {
		// nothing to do.
	}

	function getProperties(array $identities, $properties) {
//		echo "DefaultTrackingStore::getProperties:" . print_r($properties,true) . "\n";
		if (!is_array($properties)) $properties = array($properties);

		$ids = DefaultTrackingStoreIdentity::find_identities($identities);

		// if we haven't seen these ID's before, we'll have no data.
		if (!$ids || count($ids) == 0) return array();

		$result = array();
		foreach ($properties as $property) {
			$propID = $this->getPropertyID($property->getName());
			if (!$propID) continue;

			// get the tracking store item by name and any of the identities
			$items = DefaultTrackingStoreItem::get()
				->innerJoin("DefaultTrackingStoreItem_Identities", "\"DefaultTrackingStoreItem\".\"ID\"=\"DefaultTrackingStoreItem_Identities\".\"DefaultTrackingStoreItemID\"")
				->where("\"PropertyID\" = " . $propID)
				->where("\"DefaultTrackingStoreItem_Identities\".\"DefaultTrackingStoreIdentityID\" in (" . implode($ids, ",") . ")")
				->sort("\"LastEdited\" desc")
				->limit($property->getMaxRequested());
			$values = array();
			foreach ($items as $item) {
				$values[] = new ContextProperty(array(
					"name" => $property->name,
					"value" => self::json_decode_typed($item->Value),
					"confidence" => 100,  // we're completely sure of the request. @todo pull from db record
					"timestamp" => $item->LastEdited
				));
			}
			if (count($values) > 0)
				$result[$property->getName()] = $values;
		}

		return $result;
	}

	function setProperties(array $identities, $properties) {
        if (!is_array($identities)) return; // do nothing if the identity is not provided
		if (!is_array($properties)) return; // do nothing if there is nothing to set

		// Get the internal identity IDs for the requested entities, and get full objects not just IDs.
		$ids = DefaultTrackingStoreIdentity::find_identities($identities, true, false);

		foreach ($properties as $key => $value) {
			$propID = $this->getPropertyID($key, true);

			// We always create the tracking record. This means potentially alot of data.
			// @todo implement a way to set properties and provide a limit, like VMS used to do.
			$item = new DefaultTrackingStoreItem();
			$item->PropertyID = $propID;
			$item->Value = self::json_encode_typed($value);
			$item->write();

			// set the identities
			foreach ($ids as $id) {
				$item->Identities()->add($id);
			}
			$item->write();
		}
	}

	/**
	 * Per-request cache of properties we've mapped from name to ID.
	 * @var array
	 */
	static $property_ids = array();

	/**
	 * Given a property name, return the ID of the DefaultTrackingStoreProperty. This used in querying items,
	 * which only store the IDs.
	 * @param $propertyName
	 * @return void
	 */
	function getPropertyID($propertyName, $createOnDemand = false) {
		if (!isset(self::$property_ids[$propertyName])) {
			// See if we have a definition for this property
			$propID = DefaultTrackingStoreProperty::get_id_from_name($propertyName);
			if (!$propID) {
				if ($createOnDemand) {
					$propID = DefaultTrackingStoreProperty::create_from_name($propertyName);
				}
				else
					return null;  // We're not creating it, and it's not defined, so return nothing.
			}
			self::$property_ids[$propertyName] = $propID;
		}
		return self::$property_ids[$propertyName];
	}

	/**
	 * Retrieve metadata for the specified namespace(s). Returns a map of property name to data type.
	 * @param array $namespaces
	 * @return array
	 */
	function getMetadata(array $namespaces) {
		$result = array();

		// Construct an order map of property name to data type.
		$defined = DefaultTrackingStoreProperty::get()->map('PropertyName', 'DataType')->toArray();
		ksort($defined);

		foreach ($namespaces as $ns) {
			// force match to full namespace components
			if (substr($ns, -1) != ".") $ns .= ".";

			foreach ($defined as $property => $def) {
				if ($ns == "*." || substr($property, 0, strlen($ns)) == $ns) {
					// this property matches up to the length of the name space
					$inst = Object::create_from_string($def);
					$result[$property] = $inst;
				}
			}
		}

		return $result;
	}

	static function _escape($s) {
		return addcslashes($s, "\v\t\n\r\f\"\\/");
	}

	static function json_encode_typed($val) {
//		$_escape = function($s) {
//			return addcslashes($s, "\v\t\n\r\f\"\\/");
//		};

		if (is_null($val)) return "null";
		if (is_bool($val)) return $val ? "true" : "false";
		if (is_string($val)) return "\"" . self::_escape($val) . "\"";
		if (is_object($val)) {
			$vars = get_object_vars($val);
			$a = array();
			$class = get_class($val);
			$a[] = "\"_className\":\"{$class}\"";
			foreach ($vars as $key => $val) {
				$a[] = "\"" . self::_escape($key) . "\":" . self::json_encode_typed($val);
			}
			return "{" . implode($a, ",") . "}";
		}
		if (is_array($val)) {
			$obj = false;
			$a = array();
			foreach($val as $key => $value) {
				if (!is_numeric($key)) $obj = true;
				$a[$key] = self::json_encode_typed($value);
			}
			if ($obj) {
				foreach ($a as $k => $v) {
					$a[$k] = "\"" . self::_escape($k) . "\":" . $v;
				}
				return "{" . implode($a, ",") . "}";
			}
			else {
				return "[" . implode($a, ",") . "]";
			}
		}
		return $val;
	}

	static function json_decode_typed($s) {
		// decode using json_decode, which gives us stdClass for all objects.
		$o = json_decode($s);
		return self::json_decode_typed_normalise($o);
	}

	static function json_decode_typed_normalise($o) {
		if (is_object($o)) {
			// create a new instance
			if (!isset($o->_className)) return $o; // cannot deal with untyped
			$class = $o->_className;
			$new = new $class();
			foreach ($o as $k => $v) {
				$new->$k = self::json_decode_typed_normalise($v);
			}
			return $new;
		}
		if (is_array($o)) {
			$a = array();
			foreach ($o as $item) {
				$a[] = self::json_decode_typed_normalise($item);
			}
			return $a;
		}
		return $o;
	}

}
