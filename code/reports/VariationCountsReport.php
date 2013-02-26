<?php

/**
 * Report that shows
 */
class VariationCountsReport extends PersonalisationReport {

	function applies($scheme) {
		if (!is_object($scheme)) return false;
		if (!($scheme instanceof VaryingPersonalisationScheme)) return false;
		return true;
	}

	function ReportFormFields($scheme = null) {
		$now = SS_Datetime::now(); 
		$lastWeek = Date::create_field('Date', strtotime('last week'));

		$fields = FieldList::create(
			HiddenField::create('SchemeID', '', $scheme ? $scheme->ID : ''),
			HiddenField::create('ReportName', '', get_class($this)),
			TextField::create("StartDate", "Start Date", $lastWeek->Format('Y-m-d')),
			TextField::create("EndDate", "End Date", $now->Format('Y-m-d'))
		);

		return $fields;
	}

	/**
	 * Returns an array of Series. There is a Series for each variation, as well as for
	 * each measure. Data for all series is across the same time range.
	 * @return void
	 */
	function getReportData($scheme, $params) {
		$startDate = isset($params['StartDate']) ? $params['StartDate'] : null;
		$endDate = isset($params['EndDate']) ? $params['EndDate'] : null;

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

		$allSeries = array();

		$prop = $scheme->getRenderProperty();

		// Generate the series	
		foreach ($scheme->Variations() as $var) {
			$trackerProps = array(
				"function" => "getEvents",
				"params" => array(
					"property" => $prop,
					"values" => array($var->ID)
				)
			);
			
			if($startDate) $trackerProps["params"]["startTime"] = $startDate;
			if($endDate) $trackerProps["params"]["endTime"] = $endDate;

			$data = Tracker::query(array(
				$trackerProps,
				array(
					"function" => "countByTime",
					"params" => array(
						"period" => "minute"
					)
				)
			));

			// The data will come back as an array of maps which contain the data. Transform this to what flot expects.
			// Flot expects time stamps to be in milliseconds
			$flotData = array();
			foreach ($data as $d) {
				$flotData[] = array($d["time"] * 1000, $d["count"]);
			}

			$series = array(
				"label" => "Renders of " . $var->Name,
				"data" => $flotData
			);
			$allSeries[] = $series;
		}
		$result = array(
			"options" => $chartOptions,
			"data" => $allSeries
		);
		return $result;
	}

}

