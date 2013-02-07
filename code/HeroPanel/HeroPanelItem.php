<?php

class HeroPanelItem extends DataObject{

	static $db = array(
		"Name" => "Varchar(255)",
		"Link" => "HTMLText"
 	);

	static $has_one = array(
		"PersonalisationScheme" => "PersonalisationScheme",
		"Image" => "Image",
		"HeroPanel" => "HeroPanel"
	);

	function getCMSFields(){
		Requirements::javascript('personalisation/javascript/HeroPanelEditor.js');
		$fields = parent::getCMSFields();
		$options = array("Image", "Personalisation Scheme");
		$imageOrScheme = new OptionsetField("ImageOrScheme", "Image Or Scheme", $options);
		$fields->addFieldToTab("Root.Main", $imageOrScheme);
		return $fields;
	}
}