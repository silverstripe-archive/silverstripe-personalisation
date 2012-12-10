<?php

class DefaultTrackingStore implements TrackingStore {

	function init($params) {
		// nothing to do.
	}

	function getProperties(TrackingIdentity $id, $properties) {
		$result = array();
		foreach ($properties as $name => $def) {
			$items = DefaultTrackingStoreItem::get()
				->filter("TrackingIdentityID", $id->ID)
				->where("\"Key\" = '" . $name . "'")
				->sort("\"LastEdited\" desc");
			$multiple = isset($def["multiple"]) && $def["multiple"];
			$metadata = isset($def["metadata"]) && $def["metadata"];
			if (!$multiple) {
				$r = $items->First();
				if (!$r) continue;

				$v = self::json_decode_typed($r->Value);
				if ($metadata) $r = array("value" => $v, "timestamp" => $r->LastEdited); // @todo LastEdited is not timestamp
				else $r = $v;
				$result[$name] = $r;
			}
			else {
				// @todo complete implementation of retrieval of multiple objects.
				// @todo filtering
				$v = $items->toArray();
			}
		}

		return $result;
	}

	function setProperties(TrackingIdentity $id, $properties) {
		foreach ($properties as $key => $value) {
			$item = DefaultTrackingStoreItem::get()
				->filter("Key", $key)
				->filter("TrackingIdentityID", $id->ID)
				->First();
			if (!$item) {
				$item = new DefaultTrackingStoreItem();
				$item->Key = $key;
				$item->TrackingIdentityID = $id->ID;
			}
			$item->Value = self::json_encode_typed($value);
			$item->write();
		}
	}

	/**
	 * When we're asked to merge identities, we update all store items for the mergeIdentities
	 * to use the new master identity.
	 */
	function mergeIdentities($masterIdentity, $mergeIdentities) {
//		$a = array();
//		foreach ($mergeIdentities as $i) $a[] = $i->ID;
//		$sql = "update \"DefaultTrackingStoreItem\" set \"TrackingIdentifierID\"=" .
//					$masterIdentity->ID .
//					" where TrackingIdentifierID in (" . implode(",", $a);
//		DB::query($sql);
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
