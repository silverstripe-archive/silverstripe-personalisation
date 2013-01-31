<% if DefaultOpt %>
	<p> 
		Default Option (always true) <span>$DefaultOpt</span>
	</p>
<% end_if %>
<p>
	<% if showAddRulesLink %><a href="#" class="add-rule">Add rule</a><% else %><span id="isDefaultAlready">This is the default rule</span><% end_if %>
</p>
<% loop Rules %>
	<p class="rule-line">
		<span>$ParamOne</span> 
		<span>$Operator</span> 
		<span>$ParamTwo</span>
		<span class="rulesActions">
			<a href="#" class="remove-rule">[x]</a>
		</span>
	</p>
<% end_loop %>