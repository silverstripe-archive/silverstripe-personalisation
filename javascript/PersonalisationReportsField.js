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
					data += '<input type="submit" value="Generate graph" class="el-form-submit" />';
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
			var url = personalisationReportsBase + "/FilterFormFields/" + reportClass + '/' + CurrentPersonalisationSchemeID;

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

			chartContainer.html('<p class="report-message report-message-info">Loading...</p>');
			$.ajax({
				url: url,
				success: function(data, textStatus, jqXHR) {
					console.log(data);
					chartContainer.html(data).refreshChart(reportClass, params);
				},

				error: function(xhr, status, text) {
					chartContainer.html('<p class="report-message report-message-error">Erro: ' + text + '</p>');
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
			var url = personalisationReportsBase + "/getChartData/" + reportClass + "/" + CurrentPersonalisationSchemeID,
				chartContainer = $('.el-chart-container');

			$.ajax({
				url: url,
				data: params,
				success: function(data, textStatus, jqXHR) {
					// parse the json data we get back
					var p = eval("(" + data + ")");
					// pass what we get back to flot.

					if(p.data.length == 0) {
						chartContainer.html('<p class="report-message report-message-warning">There\'s no data to display.</p>');
					}
					else {
						$.plot($(".el-chart"), p.data, p.options);
					}
				},

				error: function(xhr, status, text) {
					chartContainer.html('<p class="report-message report-message-error">Erro: ' + text + '</p>');
				}
			});
		}
	});

	$('.secondary-filter-fields').entwine({
		onmatch: function() {
			this.find('.secondary-filter-fields-wrapper').hide();
		},

		toogle: function() {
			var fieldWrapper = this.find('.secondary-filter-fields-wrapper'),
				toggle = this.find('.toggle');

			if(fieldWrapper.is(':visible')) {
				fieldWrapper.hide();
				toggle.text(toggle.attr('data-label-show'));
			}
			else {
				fieldWrapper.show();
				toggle.text(toggle.attr('data-label-hide'));
			}

			return false;
		}
	});

	$('.secondary-filter-fields .toggle').entwine({
		onclick: function() {
			this.parents('.secondary-filter-fields').toogle();
			return false;
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
				inputs = container.find('input[type!=hidden], select'),
				params = {};

			inputs.each(function() {
				var input = $(this);
				if(input.attr('type') == 'checkbox' && !input.attr('checked')) return false; 
				params[input.attr('name')] = input.val();
			});

			reportList.displayReport(reportClass, params);
		}
	});

}) (jQuery);