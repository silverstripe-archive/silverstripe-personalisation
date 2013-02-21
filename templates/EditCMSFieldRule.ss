<% if DefaultOpt %>
	<p> 
		Default Option (always true) <span>$DefaultOpt</span>
	</p>
<% end_if %>

<div class="rule-lines">
	<% loop Rules %>
		<p class="rule-line">
			<span class="param-one">
				$ParamOne
				$ParamOneMockDropdown
				<span class="metadata-field-separator">.</span> 
				$ParamOneMockTextField
			</span> 
			<span>$Operator</span> 
			<span class="param-two">
				$ParamTwo
				$ParamTwoMockEnumField
			</span>
			<span class="rulesActions">
				<a href="#" class="remove-rule ui-button-icon-primary ui-icon btn-icon-cross-circle">[x]</a>
			</span>
		</p>
	<% end_loop %>
</div>

<p>
	<% if showAddRulesLink %><a href="#" class="add-rule ss-ui-action-constructive ss-ui-button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary">Add rule</a><% else %><span id="isDefaultAlready">This is the default rule</span><% end_if %>
</p>