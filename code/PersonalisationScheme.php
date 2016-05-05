<?php

class PersonalisationScheme extends DataObject
{

    public static $db = array(
        "Title" => "Varchar(255)",
        "MeasurementEnabled" => "Boolean"
    );

    public static $has_many = array(
        // Optional measures defined for this scheme
        "Measure" => "PersonalisationMeasure"
    );

    public static $summary_fields = array(
        'Title', 'ClassName'
    );

    public static $is_abstract = false;

    /**
     * Perform the personalisation. This should be overridden by sub classes.
     * @return null
     */
    public function personalise(Controller $controller = null)
    {
        return null;
    }

    public static function personalise_with($name, Controller $controller = null)
    {
        $scheme = DataObject::get_one("PersonalisationScheme", "\"Title\"='" . $name . "'");
        return $scheme ? $scheme->personalise($controller) : null;
    }

    public function canCreate($member = null)
    {
        return false;
    }

    public function ajaxLink()
    {
        // Delegate link generation to the controller that will process it.
        return PersonalisationController::calc_ajax_link($this);
    }

    public function getCMSFields()
    {
        Requirements::javascript("personalisation/javascript/PersonalisationReportsField.js");
        Requirements::customScript("var CurrentPersonalisationSchemeID=" . $this->ID . ";");
        $fields = parent::getCMSFields();

        $reports = PersonalisationReport::reports_for($this);
        $reportsDisplay = new PersonalisationReportsField('ReportsGrid', '');
        $reportsDisplay->setData($reports);
        $fields->addFieldToTab("Root.Reports", $reportsDisplay);

        return $fields;
    }

    public function getReport()
    {
        return "foo";
    }
}

class PersonalisationReportsField extends FormField
{

    public $data;

    public function setData($data)
    {
        $this->data = $data;
    }

//	function Field() {
//		return "boo";
//	}
}
