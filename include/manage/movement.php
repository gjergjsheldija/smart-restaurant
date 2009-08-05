<?php
/**
* My Handy Restaurant
*
* http://www.myhandyrestaurant.org
*
* My Handy Restaurant is a restaurant complete management tool.
* Visit {@link http://www.myhandyrestaurant.org} for more info.
* Copyright (C) 2003-2005 Fabio De Pascale
* 
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*
* @author		Fabio 'Kilyerd' De Pascale <public@fabiolinux.com>
* @package		MyHandyRestaurant
* @copyright		Copyright 2003-2005, Fabio De Pascale
*/

$inizio=microtime();

define('ROOTDIR','..');
require(ROOTDIR."/include/manage/mgmt_funs.php");
require(ROOTDIR."/include/manage/mgmt_start.php");

if(!access_allowed(USER_BIT_STOCK)) $command='access_denied';

switch($command) {
	case 'access_denied':
				echo access_denied_admin();
				break;
	case "new":
		echo "<form action=\"stock.php\" method=\"get\">\n";
		echo "<input type=\"hidden\" name=\"command\" value=\"edit\">\n";
		$err=form_stock_new();
		echo "<input type=\"submit\" value=\"".ucphr('SEND_TO_STOCK')."\">\n";
		echo "</form>\n";
		echo "<br><a href=\"#\" onclick=\"javascript:history.go(-1); return false\">".ucfirst(phr('GO_BACK'))."</a><br>\n";
		break;
	case "edit":
		echo "<form action=\"movement.php\" method=\"GET\">
		<input type=\"hidden\" name=\"command\" value=\"update\">
		<input type=\"hidden\" name=\"id\" value=\"$start_id\">\n";
		movement_form($start_id);
		echo "<input type=\"submit\" value=\"".ucphr('UPDATE')."\">\n";
		echo "<br><a href=\"#\" onclick=\"javascript:history.go(-1); return false\">".ucfirst(phr('GO_BACK'))."</a><br>\n";
		break;
	case "update":
		$err=movement_update($start_data,$start_id);

		if($err) echo ucphr('STOCK_UPDATE_ERROR').".<br>\n";
		else {
			echo ucphr('STOCK_UPDATE_OK').".<br>\n";
			echo("<META HTTP-EQUIV=\"Refresh\" CONTENT=\"".get_conf(__FILE__,__LINE__,"refresh_time_management")."; URL=movement.php?command=\">");
		}
		break;
	case "list":
		//caching disabler
		echo "<meta http-equiv=\"Cache-Control\" content=\"no-cache\" />\n";
		echo "<meta http-equiv=\"Expires\" content=\"0\" />\n";


main_header("movement.php");


		movement_table();
		echo "<form action=\"movement.php\" method=\"get\">
		<input type=\"hidden\" name=\"command\" value=\"new\"><input type=\"submit\" value=\"".GLOBALMSG_STOCK_MOVEMENT_INSERT."\">
		</form>\n";
		break;
	default:
		//caching disabler
		echo "<meta http-equiv=\"Cache-Control\" content=\"no-cache\" />\n";
		echo "<meta http-equiv=\"Expires\" content=\"0\" />\n";

?>
<center><form action="movement.php" method="GET" name="time_range">
<table>
<tr valign="center">
<td align="right"><?php echo GLOBALMSG_REPORT_PERIOD; ?></td>
	<td align="left">
		<input type="text" name="date_start" value="<?php echo $_SESSION['date']['start'] ?>" size=10>
		<input type="text" name="date_end" value="<?php echo $_SESSION['date']['end'] ?>" size=10>
	</td>
	<td><?php echo ucfirst(GLOBALMSG_REPORT_ACCOUNT); ?></td>
	<td align="left">
<?php

	$table='accounting_dbs';
	$query="SELECT * FROM `$table`";
	$res = mysql_db_query ($_SESSION['common_db'],$query);
	if($errno=mysql_errno()) {
		$msg="Error in ".__FUNCTION__." array - ";
		$msg.='mysql: '.mysql_errno().' '.mysql_error();
		echo $msg,"<br>\n";
		error_msg(__FILE__,__LINE__,$msg);
		return 1;
	}
	while($arr=mysql_fetch_array($res)) {
		$checked="";
		if(mysql_list_tables($arr['db'])) {
			if($_SESSION['common_db']==$arr['db'])
				$checked=" checked";
			echo '<input type="radio" onClick="JavaScript:document.time_range.submit();" name="mgmt_db_number" value="'.$arr['db'].'"'.$checked.'>'.$arr['name'].' '."\n";
		}
	}

?>
	</td>
<td ><input type="submit" value="<?php echo ucfirst(GLOBALMSG_REPORT_GENERATE); ?>"></td>
</tr>
</table></form></center>

<?php

		movement_table();
		echo "<form action=\"movement.php\" method=\"get\">
		<input type=\"hidden\" name=\"command\" value=\"new\"><input type=\"submit\" value=\"".GLOBALMSG_STOCK_MOVEMENT_INSERT."\">
		</form>\n";
		break;
}

if($command!="delete") unset($_SESSION["delete"]);


echo generating_time($inizio);

?>
