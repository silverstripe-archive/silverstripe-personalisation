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

	/**
	 * Returns an array of Series. There is a Series for each variation, as well as for
	 * each measure. Data for all series is across the same time range.
	 * @return void
	 */
	function getReportData($scheme) {
		$startDate = strtotime("last week");
		$endDate = strtotime("now");

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
			$data = Tracker::query(array(
				array(
					"function" => "getEvents",
					"params" => array(
						"property" => $prop,
						"values" => array($var->ID)
					)
				),
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

