(function($) {
	var currentReportClass

	$("a.report").entwine({
		// on click of a report title, retrieve the report. Requires that personalisationReportsBase has been set as
		// a global variable, which points at PersonalisationAdmin base controller.
		onclick: function(event) {
			var reportList = this.parents('.el-report-list'),
				reportClass = this.attr("data-report-id"),
				formContainer = $('.el-report-detail .el-form'),
				chartContainer = $('.el-chart-container');; 

			formContainer.html('<p>Loading...</p>');
			chartContainer.html('');
			reportList.getReportForm(
				reportClass, 
				function(data) {
					data += '<input type="submit" value="Filter" class="el-form-submit" />';
					formContainer.html(data);
				},
				function(xhr, status, text) {
					formContainer.html('<p>Error: ' + text + '</p>');
				}
			);

			return false;
		}
	});

	$(".el-report-list").entwine({

		/**
		 * @param string
		 * @param callback
		 */
		getReportForm: function(reportClass, successCallback, errorCallback) {
			var url = personalisationReportsBase + "/ReportFormFields/" + reportClass + '/' + CurrentPersonalisationSchemeID;

			$.ajax(url, {
				success: function(data, textStatus, jqXHR) {
					if(typeof successCallback === 'function') {
						successCallback(data);
					}
				}, 

				error: function(xhr, status, text) {
					if(typeof errorCallback === 'function') {
						errorCallback(xhr, status, text);
					}
				}
			});
		},

		/**
		 * @param 	string
		 * @param 	literal object
		 */
		displayReport: function(reportClass, params) {
			var url = personalisationReportsBase + "/getReport/" + reportClass + "/" + CurrentPersonalisationSchemeID,
				chartContainer = $('.el-chart-container');

			chartContainer.html('<p>Loading...</p>');
			$.ajax({
				url: url,
				success: function(data, textStatus, jqXHR) {
					chartContainer.html(data).refreshChart(reportClass, params);
				},

				error: function(xhr, status, text) {
					chartContainer.html('<p>Erro: ' + text + '</p>');
				}
			});
		}

	});

	$(".el-chart-container").entwine({

		/**
		 * @param 	string
		 * @param 	literal object
		 */
		refreshChart: function(reportClass, params) {
			// collect parameters
			// @todo collect parameters
			// make an ajax request
			var url = personalisationReportsBase + "/getChartData/" + reportClass + "/" + CurrentPersonalisationSchemeID;
			$.ajax({
				url: url,
				data: params,
				success: function(data, textStatus, jqXHR) {
					// parse the json data we get back
					var p = eval("(" + data + ")");
					// pass what we get back to flot.

					$.plot($(".el-chart"), p.data, p.options);
				}
			});
		}
	});

	$(".el-form .el-form-submit").entwine({
		onclick: function() {
			this.submit();
		}, 

		submit: function() {
			var container = $(".el-form"),
				reportList = $(".el-report-list"),
				reportClass = container.find('[name="ReportName"]').val(),
				inputs = container.find('input.text, select'),
				params = {};

			inputs.each(function() {
				var input = $(this);
				params[input.attr('name')] = input.val();
			});

			reportList.displayReport(reportClass, params);
		}
	});

}) (jQuery);