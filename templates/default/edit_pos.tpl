<div>
	<a class="modalCloseImg simplemodal-close" title="Close"></a>
	{scripts}
	{navbar}<center><h1 style="margin-top:-10px">{dishname}</h1></center>
	<center>
	{form_start}
		{print_info}
		<table cellspacing="20" cellpadding="5">
			<tr>
				<td>
					<table>
						<tr valign="top">
							<td>{quantity}</td>
							<td>&nbsp;&nbsp;&nbsp;</td>
							<td>{priority}</td>
						</tr>
					</table>
				</td>
				<td valign="top"><strong>{suspend}</strong></td>
				<td valign="top"><strong>{extra_care}</strong></td>
			</tr>
		</table>
	{form_end}
	</center>
</div>