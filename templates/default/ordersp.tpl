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
		<div class="informational message">{messages}</div>
		<table>
			<tr>
				<td valign="top" align="left">{fast_order_id}</td>
				<td>{scripts}</td>
				<td valign="top" align="center">{vertical_navbar}</td>
				<td valign="top" align="right">{logout}</td>
			</tr>
			<tr>
				<td colspan="3">
					<table>
						<tr>
							<td valign="top">{toplist}</td>
							<td valign="top">{categories}</td>
							<td valign="top">{letters}</td>
						</tr>
					</table>
				</td>
				<td align="left" valign="top" rowspan="4">
					<table>
					<tr><td>
					{orders_list}
					&nbsp;
					</td></tr>
					</table>
				</td>
			</tr>
			<tr>
				<td>
					{commands}
				</td>
			</tr>
		</table>
		</center>
		<center><dd>Powered by <a href="http://smartres.sourceforge.net/">Smart Restaurant</a></dd></center>
	</body>
</html>
