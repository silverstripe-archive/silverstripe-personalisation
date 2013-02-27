<div class="el-report-list">
	<h2 class="title">Available Reports</h2>

	<ul>
		<% loop data %>
			<li><a href="#" class="report" data-report-ID="$ClassName">$Title</a></li>
		<% end_loop %>
	</ul>
</div>
<div class="el-report-detail">
	<div class="el-form">
	</div>
	<div class="el-chart-container"></div>
</div>