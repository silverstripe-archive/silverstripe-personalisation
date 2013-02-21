<?php

/**
 * This is a default implementation of the tracking store. It is a "poor man's" implementation that should be suitable
 * for simple installations where the number of users and/or the number of tracked items is not too large.
 * The querying functions are fairly basic, and probably not too performant.
 * 
 * @throws Exception
 *
 */
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
				->sort("\"LastEdited\" desc, \"ID\" desc")
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

	/**
	 * @param $pipeline		An array of maps that define the sequence of things to do. Each map has at least properties:
	 * 							"function"		name of function supported by the tracking store
	 * 							"params"		a map of additional parameters to that function.
	 * 						The output of each pipeline element is fed as input to the next stage. The initial dataset
	 * 						is empty, so typically the first function in the pipeline is a generator of data from the
	 * 						store.
	 * @return non-null value if there is a result, null if there is an error
	 */
	function query($pipeline) {
		$data = array();
		foreach ($pipeline as $function) {
			$data = $this->executeFunction($function, $data);
			if ($data === null) return null;  // error
		}
		return $data;
	}

	protected static $supported_functions = array(
		"getEvents",
		"countByTime"
	);

	protected function executeFunction($fn, $inputData) {
		$name = $fn["function"];
		$params = isset($fn["params"]) ? $fn["params"] : array();

		if (! in_array($name, self::$supported_functions)) throw new Exception("executeFunction: $name is not a supported function");
		// if function is foo, we'll call executeFnFoo
		$fnName = "executeFn" . ucfirst($name);

		return $this->$fnName($params, $inputData);
	}

	/**
	 * Retrieve a list of events that is filtered by something. Params may include:
	 *   - startTime - timestamp of start of date range
	 *   - endTime - timestamp of end of date range
	 *   - property - property name, or array of property names, to filter to.
	 * @param $params
	 * @return void
	 */
	protected function executeFnGetEvents($params, $data) {
		// there must be at least a property or properties provided
		if (!isset($params["property"])) throw new Exception("executeFnGetEvents: property must be supplied");

		$props = $params["property"];
		if (is_string($props)) $props = array($props);
		if (!is_array($props)) throw new Exception("executeFnGetEvents: properties must be an array or single string");

		// translate the property names into property IDs
		$propIDs = array();
		$propMap = array();
		foreach ($props as $p) {
			$id = DefaultTrackingStoreProperty::get_id_from_name($p);
			if ($id) {
				$propIDs[] = $id;
				$propMap[$id] = $p;
			}
		}

		// If none of the request properties actually exist, return an empty result.
		if (count($propIDs) == 0)
			return array();

		$query = DefaultTrackingStoreItem::get()->where("\"PropertyID\" in (" . implode(",", $propIDs) . ")");

		// Add in date filters if they are present
		if (isset($params["startTime"])) {
			$query->where("\"Created\" >= " . date("Y-m-d h:i:s", $params["startTime"]));
		}

		if (isset($params["endTime"])) {
			$query->where("\"Created\" < " . date("Y-m-d h:i:s", $params["endTime"]));
		}

		if (isset($params["values"])) {
			$values = $params["values"];
			if (!is_array($values)) $values = array($values);
			for ($i = 0; $i < count($values); $i++)
				$values[$i] = "'". addslashes($values[$i]) . "'";
			$query->where("\"Value\" in (" . implode(",", $values) . ")");
		}

		$result = $query->toArray();

		foreach ($result as $rec) {
			$rec->PropertyName = $propMap[$rec->PropertyID];
		}
		return $result;
	}

	/**
	 * Map of period names to the number of seconds used for grouping.
	 * @var array
	 */
	static $periods = array(
		"minute" => 60,
		"hour" => 3600,
		"day" => 86400,
		"week" => 604800
	);

	/**
	 * Given a set of events, group them by time and count them. Returns an array of maps with "time" and "count"
	 * properties.
	 * @param $params
	 * @param $data
	 * @return void
	 */
	protected function executeFnCountByTime($params, $data) {
		$period = isset($params["period"]) ? $params["period"] : "hour";
		if (!isset(self::$periods[$period])) throw new Exception("Invalid periond $period");
		$sec = self::$periods[$period];

		$min = 4102444800;
		$max = 0;

		$counts = array();
		foreach ($data as $item) {
			$t = strtotime($item->Created);
			$m = $t % $sec;
			$t -= $m;
			if ($t < $min) $min = $t;
			if ($t > $max) $max = $t;
			$counts[$t] = isset($counts[$t]) ? $counts[$t] + 1 : 1;
		}

		// Fill in the spaces with zero
		if (count($counts) > 0) {
			for ($t = $min; $t < $max; $t+=$sec) {
				if (!isset($counts[$t]))
					$counts[$t] = 0;
			}
		}

		ksort($counts);

		//die ("input data: " . print_r($data,true) . " and output is " . print_r($counts, true));
		$result = array();
		foreach ($counts as $time => $count) {
			$result[] = array("time" => $time, "count" => $count);
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
