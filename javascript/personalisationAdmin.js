(function($) {
	$.entwine('ss', function($){
		$('#DefaultOption').entwine({
			onmatch: function() {
				if(this.is(':checked')) {
					this.disableAll();
				}
			},
			onclick: function() {
				if(this.is(':checked')) {
					this.disableAll();
				} else {
					this.enableAll();
				}
				$('.add-rule').updateState();
				$('.remove-rule').updateState();
			},
			disableAll: function() {
				this.parents('.middleColumn').find('input').each(function() {
					if($(this).hasClass('checkbox') === false) $(this).attr('disabled', 'disabled');
				});
				this.parents('.middleColumn').find('select').each(function() {
					$(this).attr('disabled', 'disabled');
				});
			},
			enableAll: function() {
				this.parents('.middleColumn').find('input').each(function() {
					if($(this).hasClass('checkbox') === false) $(this).removeAttr('disabled');
				});
				this.parents('.middleColumn').find('select').each(function() {
					$(this).removeAttr('disabled');
				});
			}
		});

		$('.remove-rule').entwine({
			onclick: function() {
				var self = this;
				this.parents('p').fadeOut('slow', function() { 
					$(this).remove(); 
					self.rearrangeRules(); 
					$('.add-rule').updateState(); 
				});
				return false;
			},
			rearrangeRules: function() {
				var i = 1;
				$('.rule-line').each( function() {
					$(this).find('input').each( function() {
						var newNameID = $(this).attr('id');
						newNameID = newNameID.split("_");
						$(this).attr('id', newNameID[0] + "_" + i);
						$(this).attr('name', newNameID[0] + "_" + i);
					});
					$(this).find('select').each( function() {
						var newNameID = $(this).attr('id');
						newNameID = newNameID.split("_");
						$(this).attr('id', newNameID[0] + "_" + i);
						$(this).attr('name', newNameID[0] + "_" + i);
					});

					i++;
				});
			},
			onmatch: function() {
				this.updateState();
			},
			updateState: function() {
				if($('#DefaultOption').is(':checked')) {
					this.hide();
					return;
				} 
				this.show();
			}
		});

		$('.add-rule').entwine({
			onclick: function() {
				if($('.rule-line').length) {
					$('.rule-line:last').clone().appendTo('#EditEncodedCondition .middleColumn');
					$('.rule-line:last').find('input').each( function() {
						$(this).val('');
					});
					$('.rule-line:last').find('select').each( function() {
						$(this).val('eq');
					});
				} else {
					$('<p class="rule-line"><span><input id="Param1_1" class="text nolabel" type="text" value="" name="Param1_1"></span><span><select id="Operator_1" class="dropdown nolabel" name="Operator_1"><option selected="" value="eq">eq</option><option value="ne">ne</option><option value="contains">contains</option></select></span><span><input type="text" id="Param2_1" class="text nolabel" value="" name="Param2_1"></span><span class="rulesActions"><a class="remove-rule" href="#">[x]</a></span></p>').appendTo('#EditEncodedCondition .middleColumn');
				}
				
				this.updateState();
				$('.remove-rule').rearrangeRules();
				return false;
			},
			onmatch: function() {
				this.updateState();
			},
			updateState: function() {
				if($('#DefaultOption').is(':checked')) {
					this.hide();
					return;
				} 

				if($('.rule-line').length == 4) this.hide();
				else this.show();
			}
		});

		$('.cms-edit-form .Actions input.action[type=submit], .cms-edit-form .Actions button.action').entwine({
			/**
			 * Function: onclick
			 */
			onclick: function(e) {
				this.convertToString(this.parents('form'));
				if(!this.is(':disabled')) {
					this.parents('form').trigger('submit', [this]);
				}
				e.preventDefault();
				return false;
			},
			convertToString: function(form) {
				var resp = '[';
				var i = 1;
				form.find('.rule-line').each( function() {
					resp += '{"_className":"BasicPersonalisationCondition","operator":"' + $(this).find('#Operator_'+i).val() + '",';
					resp += '"param1":{"_className":"BasicPersonalisationValue","kind":"P","value":"' + $(this).find('#Param1_'+i).val() + '"},';
					resp += '"param2":{"_className":"BasicPersonalisationValue","kind":"L","value":"' + $(this).find('#Param2_'+i).val() + '"}},';
					i++;
				});
				resp = resp.substring(0, resp.length - 1);
				resp += ']';
				form.find('input[name=EncodedCondition]').val(resp);
			}
		});
	});

})(jQuery);