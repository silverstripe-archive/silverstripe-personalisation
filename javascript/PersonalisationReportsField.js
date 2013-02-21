(function($) {
	var currentReportClass

	$("a.report").entwine({
		// on click of a report title, retrieve the report. Requires that personalisationReportsBase has been set as
		// a global variable, which points at PersonalisationAdmin base controller.
		onclick: function(event) {
			currentReportClass = this.attr("data-report-id");
			var url = personalisationReportsBase + "/getReport/" + currentReportClass + "/" + CurrentPersonalisationSchemeID;
			$.ajax(url, {
				success: function(data, textStatus, jqXHR) {
					$(".el-report-detail").html(data);
					$(".el-report-detail").refreshChart();
				}
			});
			return false;
		}
	});

	$(".el-report-detail").entwine({
		refreshChart: function() {
			// collect parameters
			// @todo collect parameters
			// make an ajax request
			var url = personalisationReportsBase + "/getChartData/" + currentReportClass + "/" + CurrentPersonalisationSchemeID;
			$.ajax(url, {
				success: function(data, textStatus, jqXHR) {
					// parse the json data we get back
					var p = eval("(" + data + ")");
					// pass what we get back to flot.

					$.plot($(".el-chart"), p.data, p.options);
				}
			});
		}
	});
}) (jQuery);