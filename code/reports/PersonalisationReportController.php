<?php

class PersonalisationReportController extends Controller {

	static $base_link = "pers/reports";

	static $allowed_actions = array(
		"getReport",
		"getChartData", 
		"FilterFormFields"
	);

	function init() {
		parent::init();
		$this->getRequest()->shift(1);
	}

	function getReport() {
		$params = $this->getParameters();
		if (isset($params["errorStatus"]))
			return $this->httpError($params["errorStatus"], $params["errorMessage"]);

		$report = $params["report"];
		$scheme = $params["scheme"];
		
		return $report->render($scheme);
	}

	function FilterFormFields() {
		$params = $this->getParameters();
		$report = $params["report"];
		$scheme = $params["scheme"];
		$fields = $report->FilterFormFields($scheme);
		$secFields = $report->SecondaryFilterFormFields($scheme);

		$html = '';
		foreach ($fields as $field) {
			$html .= $field->FieldHolder();
		}

		if($secFields) {
			$html .= '<div class="secondary-filter-fields"> <a href="#" class="toggle" data-label-show="Show extra fields" data-label-hide="Hide extra fields">Show extra fields</a> <div class="secondary-filter-fields-wrapper">';
			foreach ($secFields as $field) {
				$html .= $field->FieldHolder();
			}			
			$html .= '</div></div>';
		}

		return $html;
	}

	/**
	 * Get the request parameters and package them into a map. The minimum mandatory values are schemeID and
	 * reportClass. If these are not present or invalid, return errorStatus and errorMessage in the map instead. If they
	 * are present and valid, they are looked up and returned with keys "report" and "scheme", both objects.
	 * @return void
	 */
	function getParameters() {
		$reportClass = $this->getRequest()->param("Report");
		$schemeID = $this->getRequest()->param("OtherID");

		if (!$reportClass || !$schemeID || !is_numeric($schemeID))
			return array("errorStatus" => 400, "errorMessage" => "invalid report request");

		$report = new $reportClass();
		$scheme = PersonalisationScheme::get()->filter("ID", $schemeID)->First();
		if (!$scheme)
			return array("errorStatus" => 404, "errorMessage" => "scheme not found");

		if (!$report->applies($scheme))
			return array("errorStatus" => 400, "errorMessage" => "Report does not apply to this scheme");

		$result = array();
		foreach ($_REQUEST as $key => $value)
			$result[$key] = $value;

		$result["reportClass"] = $reportClass;
		$result["schemeID"] = $schemeID;
		$result["report"] = $report;
		$result["scheme"] = $scheme;

		return $result;
	}

	/**
	 * Ajax handler for fetching reporting data that can be charted based on parameters passed. The response is
	 * json text, representing a single object with 'options' and 'data' properties. Options property is an
	 * object representing charting options. Data property is the data itself.
	 * @return void
	 */
	function getChartData() {
		$params = $this->getParameters();
		if (isset($params["errorStatus"]))
			return $this->httpError($params["errorStatus"], $params["errorMessage"]);

		$report = $params["report"];
		$scheme = $params["scheme"];
		$result = $report->getReportData($scheme, $_REQUEST);

		if(isset($result['errorStatus'])) {
			return $this->httpError($result["errorStatus"], $result["errorMessage"]);			
		}
/*
		$chartOptions = array(
			"chartType" => "line",
			"xaxis" => array(
				"mode" => "time",
				"tickDecimals" => 0
			),
			"legend" => array(
				"noColumns" => 2
			),
			"series" => array(
				"lines" => array("show" => "true"),
				"points" => array("show" => "true")
			)
		);
		$series1 = array(
				"label" => "variation A => outcome 1",
				"data" => array(
					array(strtotime("yesterday") * 1000, 1),
					array(strtotime("today") * 1000, 2),
					array(strtotime("tomorrow") * 1000, 1.5),
					array(strtotime("next week") * 1000, 5),
				)
			);
		$result = array(
			"options" => $chartOptions,
			"data" => array($series1)
		);
*/
		return json_encode($result);
	}
}
