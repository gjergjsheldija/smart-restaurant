<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<META name="HandheldFriendly" content="True">
	{head}
	</head>
	<body>
		{scripts}
		<center>
		{people_number}
		{messages}
		{navbar}

		{form_start}
		{substitute}<br/>
		<b>{dishname}</b>
		{print_info}
		<table>
			<tr valign="top">
				<td>{quantity}</td>
				<td>{priority}</td>
			</tr>
		</table>
		{suspend}<br/>
		{extra_care}
		{form_end}
		
		{logout}
		{generating_time}
		</center>
		<dd>Powered by <a href="http://smartres.sourceforge.net/">Smart Restaurant</a></dd>
	</body>
</html>
