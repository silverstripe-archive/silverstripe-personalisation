<?php

/**
 * This type of measure uses a condition to determine whether or not something is true.
 * Typical usage would be to make the rule test if a property has been set for a user in the tracker. If it
 * has, true 1. If it hasn't, return 0.
 */
/* @todo its still not clear how this actually works. does every measure have a tracking value? think of the possible
 * @todo timelines and amount of data, and what it means for the condition. is there a standard filter applied to the
 * @todo data that is tested? how much sits in the tracking store, and how much out of it?
 */
class RuleMeasure extends PersonalisationMeasure {

	static $db = array(
		// json encoded object represents an array of BasicPersonalisationCondition objects.
		"EncodedCondition" => "Text"
	);

	/**
	 * Returns 1 or 0 depending on weather the rule is true for the site user identified by one of the
	 * identities.
	 * @param $identities
	 * @return void
	 */
	function getValue($identities) {
	}
}
