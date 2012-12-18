<?php

/**
 * Represents a single "fact", being a prooerty/value pair with a confidence level.
 */

class DefaultTrackingStoreItem extends DataObject {

	static $db = array(
		"Key" => "Varchar(255)",
		"Value" => "Text",
		"Confidence" => "Float"
	);

	static $many_many = array(
		"Identities" => "DefaultTrackingStoreIdentity"
	);
}
