<div class="el-report-list">
	<h2>Available Reports</h2>

	<ul>
		<% loop data %>
			<li><h3><a href="#" class="report" data-report-ID="$ClassName">$Title</a></h3></li>
		<% end_loop %>
	</ul>
</div>
<div class="el-report-detail">
	<div class="el-form">
	</div>
	<div class="el-chart-container"></div>
</div>