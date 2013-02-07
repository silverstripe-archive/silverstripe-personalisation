<?php

class HeroPanelItem extends DataObject{

	static $db = array(
		"Name" => "Varchar(255)",
		"Link" => "Varchar(255)",
		"ImageType" => "Int",
		"LinkType" => "Int",
 	);

	static $has_one = array(
		"Page" => "Page",
		"PersonalisationScheme" => "PersonalisationScheme",
		"HeroImage" => "Image",
		"InternalLink" => "SiteTree"
	);

	function getCMSFields(){
		$fields = parent::getCMSFields();
		$internalExternal = array(1 => "Internal", 2 => "External");
		$optionSet = new DropDownField("LinkType", "Add internal or external link", $internalExternal);
		$fields->addFieldToTab("Root.Main", $optionSet, "Link");
		$fields->addFieldToTab("Root.Main", new TreeDropdownField("InternalLinkID", "Internal Link", "SiteTree", "ID", "Title"), "Link");
		$options = array( 1 => "HeroImage", 2 => "Personalisation Scheme");
		$imageOrScheme = new DropDownField("ImageType", "Image Or Personalisation Scheme", $options);
		$fields->addFieldToTab("Root.Main", $imageOrScheme, "PersonalisationSchemeID");
		$fields->removeByName("HeroPanelID");
		$fields->removeByName("PageID");
		return $fields;
	}

	function getHeroLink(){
		if($this->InternalLinkType == 1 && $page = Page::get_by_id("Page", (int)$this->InternalLinkID)){
			return $page->Link();
		}elseif($this->InternalLinkType == 2){
			return $this->Link;
		}else{
			return null;
		}
	}

	function getHeroObject(){
		if($this->ImageType == 1){
			return $this->HeroImage();
		}elseif($this->ImageType == 2){
			return $this->Personalise();
		}else{
			return null;
		}
	}

	function Personalise() {
		if($this->PersonalisationSchemeID && $ps = PersonalisationScheme::get_by_id("PersonalisationScheme",  $this->PersonalisationSchemeID)){
			return PersonalisationScheme::personalise_with($ps->Title);
		}else{
			return null;
		}
	}
}