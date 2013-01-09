<p> 
	Default Option (always true) <span>$DefaultOpt</span>
</p>
<p>
	<a href="#" class="add-rule">Add rule</a>
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