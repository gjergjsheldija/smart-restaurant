<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<META name="HandheldFriendly" content="True">
		{head}
	</head>
	<body>
		{scripts}
		<div class="informational message">{messages} - {people_number}</div>
		<div class="menuMain">
			<table>
				<tr>
					<td align="left" width="30%">{fast_order_id}</td>
					<td width="45%">{scripts}</td>
					<td align="right" width="25%">{vertical_navbar}</td>
				</tr>
			</table>
			<!--{logout} -->
		</div>
		<div class="mainBody">
			<div class="categoriesMenu">
				<!--  {toplist} -->
				{categories}
			</div>
			<div class="dishesMenu">
				<div id="dishes_response" style="height:70%"></div>
			</div>			
			<div class="receiptMenu">
				{orders_list}
			</div>
		</div>
			<!--  {commands} -->
		<div class="footer">
			<center><dd>Powered by <a href="http://smartres.sourceforge.net/">Smart Restaurant</a></dd></center>
		</div>
	</body>
</html>
