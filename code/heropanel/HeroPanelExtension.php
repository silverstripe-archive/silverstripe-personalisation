<?php
class HeroPanelExtension extends DataExtension
{

    public static $has_many = array(
        "HeroItems" => "HeroPanelItem"
    );

    public function updateCMSFields(FieldList $fields)
    {
        Requirements::javascript('personalisation/javascript/personalisationAdmin.js');

        $heroItemsField = new GridField("HeroItems", "Items", $this->owner->HeroItems(), GridFieldConfig_RecordEditor::create());
        $fields->addFieldToTab("Root.HeroPanel", $heroItemsField);
    }

    public function HeroPanel()
    {
        Requirements::css("personalisation/javascript/jsImgSlider/themes/1/js-image-slider.css");
        $heroItems = $this->owner->getComponents("HeroItems")->Sort("Sequence");
        return $this->owner->customise(array("HeroItems" =>  $heroItems))->renderWith("HeroPanel");
    }
}
