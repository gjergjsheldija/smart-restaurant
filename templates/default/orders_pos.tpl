<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<META name="HandheldFriendly" content="True">
		{head}
		{scripts}
	</head>
	<body>
		<div class="menuMain">					
			<span id="right">{vertical_navbar}</span>
			<span id="left">{fast_order_id}</span>
			<center id="tableName">{people_number}{messages}</center>	
		</div>
		<div class="mainBody">
			<div class="categoriesMenu">
				<!--  {toplist} -->
				{categories}
			</div>
			<div class="dishesMenu">
				<div id="dishes_response" style="height:100%"></div>
			</div>			
			<div class="receiptMenu">
				<div id="receiptMenu_response" style="height:100%">{orders_list}</div>
			</div>
		</div>
			<!--  {commands} -->
		<div class="footer">
			<center><dd>Powered by <a href="http://smartres.sourceforge.net/">Smart Restaurant</a></dd></center>
		</div>
	</body>
</html>
