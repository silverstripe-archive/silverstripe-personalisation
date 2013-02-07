<?php
class HeroPanelExtension extends DataExtension{

	static $has_one = array(
		"HeroPanel" => "HeroPanel"
	);

	function updateCMSFields(FieldList $fields){

		$gridFieldConfig = new GridFieldConfig_RecordEditor();
		$hpList = HeroPanel::get()->filter(array("PageID" => $this->owner->ID));
		$fields->addFieldToTab("Root.HeroPanel", new GridField("HeroPanel", "HeroPanel", $hpList, $gridFieldConfig));
	}
}