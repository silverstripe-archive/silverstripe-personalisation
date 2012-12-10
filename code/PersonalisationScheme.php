<?php

class PersonalisationScheme extends DataObject {

	static $db = array(
		"Title" => "Varchar(255)"
	);

	/**
	 * Perform the personalisation. This should be overridden by sub classes.
	 * @return null
	 */
	function personalise() {
		return null;
	}

	static function personalise_with($name) {
		$scheme = DataObject::get_one("PersonalisationScheme", "\"Title\"='" . $name . "'");
		return $scheme->personalise();
	}
}

