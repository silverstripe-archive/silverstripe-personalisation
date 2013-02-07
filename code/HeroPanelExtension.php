<?php
class HeroPanelExtension extends DataExtension{

	static $has_many = array(
		"HeroItems" => "HeroPanelItem"
	);

	function updateCMSFields(FieldList $fields){
		Requirements::javascript('personalisation/javascript/personalisationAdmin.js');

		$heroItemsField = new GridField("HeroItems", "Items", $this->owner->HeroItems(), GridFieldConfig_RecordEditor::create());
		$fields->addFieldToTab("Root.HeroPanel", $heroItemsField);
	}

	function HeroPanel(){
		Requirements::css("personalisation/javascript/jsImgSlider/themes/1/js-image-slider.css");
		$heroItems = $this->owner->getComponents("HeroItems");
		return $this->owner->customise(array("HeroItems" =>  $heroItems))->renderWith("Carousel");
	}

}