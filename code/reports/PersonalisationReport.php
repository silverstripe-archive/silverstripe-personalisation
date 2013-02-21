<?php

class PersonalisationReport extends ViewableData {

	/**
	 * Abstract function that can be used to determine whether this report applies to the given personalisation scheme.
	 * @param $scheme
	 * @return boolean
	 */
	function applies($scheme) {
		return false;
	}

	function summaryFields() {
		return array("Report name" => get_class($this));
	}

	function canView() {
		return true;
	}

	/**
	 * Given a scheme, return an SS_List containing instances of the reports that can be displayed for that scheme.
	 * @static
	 * @param $scheme
	 * @return ArrayList
	 */
	static function reports_for($scheme) {
		// iterate over all subclasses of Personalisation Report, return a map for those where it applies
		$result = array();
		foreach (ClassInfo::subclassesFor("PersonalisationReport") as $k => $class) {
			$inst = new $class();
			$inst->Title = get_class($inst);
			$inst->ClassName = get_class($inst);
			// @todo split camel cases report title
			if ($inst->applies($scheme)) $result[] = $inst;
		}
		return new ArrayList($result);
	}

	/**
	 * Abstract method for rendering the report. This might directly render a chart or table, or may return a form
	 * that fetches the report chart from the parameters.
	 * @param $scheme
	 * @return void
	 */
	function render($scheme) {
		return $this->renderWith("PersonalisationReportDetail");
	}
}
