<?php

class PersonalisationScheme extends DataObject {

	static $db = array(
		"Title" => "Varchar(255)"
	);

	/**
	 * Perform the personalisation. This should be overridden by sub classes.
	 * @return null
	 */
	function personalise(Controller $controller = null) {
		return null;
	}

	static function personalise_with($name, Controller $controller = null) {
		$scheme = DataObject::get_one("PersonalisationScheme", "\"Title\"='" . $name . "'");
		return $scheme ? $scheme->personalise($controller) : null;
	}

	public function canCreate($member = null) {
		return false;
	}
}

