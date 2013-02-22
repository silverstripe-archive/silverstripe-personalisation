<?php

class PersonalisationScheme extends DataObject {

	static $db = array(
		"Title" => "Varchar(255)",
		"MeasurementEnabled" => "Boolean"
	);

	static $has_many = array(
		// Optional measures defined for this scheme
		"Measure" => "PersonalisationMeasure"
	);

	static $summary_fields = array(
		'Title', 'ClassName'
	);

	static $is_abstract = false;

	/**
	 * Perform the personalisation. This should be overridden by sub classes.
	 * @return null
	 */
	function personalise(Controller $controller = null) {
		return null;
	}

	static function personalise_with($name, Controller $controller = null) {
		$scheme = DataObject::get_one("PersonalisationScheme", "\"Title\"='" . $name . "'");
		return $scheme ? $scheme->personalise($controller) : null;
	}

	public function canCreate($member = null) {
		return false;
	}

	public function ajaxLink() {
		// Delegate link generation to the controller that will process it.
		return PersonalisationController::calc_ajax_link($this);
	}

	function getCMSFields() {
		Requirements::javascript("personalisation/javascript/PersonalisationReportsField.js");
		Requirements::customScript("var CurrentPersonalisationSchemeID=" . $this->ID . ";");
		$fields = parent::getCMSFields();

		$reports = PersonalisationReport::reports_for($this);
		$reportsDisplay = new PersonalisationReportsField('ReportsGrid', '');
		$reportsDisplay->setData($reports);
		$fields->addFieldToTab("Root.Reports", $reportsDisplay);

		return $fields;
	}

	function getReport() {
		return "foo";
	}
}

class PersonalisationReportsField extends FormField {

	var $data;

	function setData($data) {
		$this->data = $data;
	}

//	function Field() {
//		return "boo";
//	}
}