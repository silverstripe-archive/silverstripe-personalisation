<?php

/**
 * A measure is used for reporting. It provides a flexible way to return a value
 */
class PersonalisationMeasure extends DataObject {

	static $db = array(
		// this is used on graphs
		"Title" => "Varchar(255)"
	);

	static $has_one = array(
		"Scheme" => "PersonalisationScheme"
	);

	// @todo Is a measure boolean or numeric?
	// @todo boolean can be used to determine counts
	// @todo numeric can be used for example to determine amounts
	/**
	 * Evaluate this measure given a set of identities that represent one site visitor. There may be
	 * multiples, as there are different ways to track someone. The measure is evaluated for any
	 * of the identities (e.g. only need one identity match). The subclass determines further semantics.
	 * @param $identities
	 * @return void
	 */
	function getValue($identities) {
	}
}
