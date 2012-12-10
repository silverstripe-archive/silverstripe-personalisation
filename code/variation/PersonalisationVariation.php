<?php

class PersonalisationVariation extends DataObject {

	static $db = array(
		"Name" => "Varchar(255)",
		"Description" => "Text"
	);

	static $has_one = array(
		"Parent" => "VaryingPersonalisationScheme"
	);

	function render(ContextProvider $context) {
		// should be overridden by subclasses.
	}
}
