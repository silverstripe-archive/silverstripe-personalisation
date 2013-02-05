<h3 data-rule-id="$ID">$Title</h3>
<% loop Rules %>
	<p>
		<% if ParamOne %><span class="rule-param1">$ParamOne</span><% end_if %>
		<span class="rule-operator">$Operator</span> 
		<% if ParamTwo %><span class="rule-param2">$ParamTwo</span><% end_if %>
	</p>
<% end_loop %>