<?php
/**
 * Basic implementation of Navigation Items Variation
 *
 */
class NavVariation extends PersonalisationVariation
{

    public static $db = array(
        "IncludeBasicTemplate" => "Boolean"
    );

    public static $many_many = array(
        "NavItems" => "SiteTree"
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('NavItems');
        $fields->addFieldToTab("Root.NavItems", new CheckboxField("IncludeBasicTemplate", "Include Basic Template - Returns component set if false.", $this->IncludeBasicTemplate));
        $fields->addFieldToTab("Root.NavItems", new TreeMultiselectField("NavItems", "Select items to include in navigation items", "SiteTree", "ID"));
        $navField = new ReadonlyField('Variation', 'Variation', 'Navigation items can be added after you have saved for the first time');
        $fields->push($navField);

        return $fields;
    }

    public function render(ContextProvider $context, Controller $controller = null)
    {
        if (!$this->IncludeBasicTemplate) {
            return $this->NavItems();
        } else {
            if ($this->NavItems()->Count() > 0) {
                return $controller->customise(array("NavItems" => $this->NavItems()))->renderWith('NavItemsVariation');
            } else {
                return null;
            }
        }
    }
}
