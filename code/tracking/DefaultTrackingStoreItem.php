<?php

class DefaultTrackingStoreItem extends DataObject {

	static $db = array(
		"Key" => "Varchar(255)",
		"Value" => "Text"
	);

	static $has_one = array(
		"TrackingIdentity" => "TrackingIdentity"
	);
}
