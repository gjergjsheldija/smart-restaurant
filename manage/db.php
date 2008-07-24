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
require(ROOTDIR."/manage/mgmt_funs.php");
require(ROOTDIR."/manage/mgmt_start.php");
$GLOBALS['end_require_time']=microtime();

if(isset($_REQUEST['orderby'])){
	$orderby=$_REQUEST['orderby'];
	$_SESSION['orderby']=$orderby;
} elseif(isset($_SESSION['orderby'])){
	$orderby=$_SESSION['orderby'];
}

if(!access_allowed(USER_BIT_ACCOUNTING)) $command='access_denied';

switch($command) {
	case 'access_denied':
				echo access_denied_admin();
				break;
	case "show_all":
		main_header();
		table_general($orderby,"show_all",6);
		break;
	case "income_collapse":
		main_header();
		table_general($orderby,"income_collapse",5);
		break;
	case "show_only":
		if(isset($_GET['show_only'])){
			$show_only=$_GET['show_only'];
			$_SESSION['show_only']=$show_only;
		} elseif(isset($_POST['show_only'])){
			$show_only=$_POST['show_only'];
			$_SESSION['show_only']=$show_only;
		} elseif(isset($_SESSION['show_only'])){
			$show_only=$_SESSION['show_only'];
		}

		main_header();
		table_general($orderby,"show_only",4,$show_only);
		break;
	case "new":
		if(isset($_GET['insert_type'])){
			$insert_type=$_GET['insert_type'];
		} elseif(isset($_POST['insert_type'])){
			$insert_type=$_POST['insert_type'];
		}
		echo "
		<form action=\"db.php\" method=\"POST\" name=\"form1\">
		<input type=\"hidden\" name=\"command\" value=\"insert\">
		<input type=\"hidden\" name=\"data[type]\" value=\"$insert_type\">
		";

		display_form(0,$insert_type);
		echo "
		<input type=\"submit\">
		</form>
		";
		break;
	case "insert":
		//caching disabler
//		echo "<meta http-equiv=\"Cache-Control\" content=\"no-cache\" />\n";
//		echo "<meta http-equiv=\"Expires\" content=\"0\" />\n";
		insert_data($start_data,$payment_data);
		break;
	case "edit":
		$start_id=invoice_payment_access_lock($start_id);
		echo "
		<table border=\"0\"><tr><td>
		<form action=\"db.php\" method=\"GET\" name=\"form1\">
		<input type=\"hidden\" name=\"command\" value=\"update\">
		<input type=\"hidden\" name=\"id\" value=\"$start_id\">\n";
		display_form($start_id);

		echo "</td>
		<td></td></tr>";

		echo "<tr><td>
		<input type=\"submit\" value=\"".ucfirst(phr('APPLY'))."\">
		</form></td><td>
		<form action=\"db.php\" method=\"GET\">
		<input type=\"hidden\" name=\"command\" value=\"delete\">
		<input type=\"hidden\" name=\"delete[$start_id]\" value=\"1\">
		<input type=\"submit\" value=\"".GLOBALMSG_RECORD_DELETE."\">
		</form></td></tr>
		</table>
		";
		break;
	case "show":
		//caching disabler
//		echo "<meta http-equiv=\"Cache-Control\" content=\"no-cache\" />\n";
//		echo "<meta http-equiv=\"Expires\" content=\"0\" />\n";

		$start_id=invoice_payment_access_lock($start_id);

		display_show($start_id);

		echo "
		<form action=\"db.php\" method=\"GET\">
		<input type=\"hidden\" name=\"command\" value=\"edit\">
		<input type=\"hidden\" name=\"id\" value=\"$start_id\">
		";
		echo "
		<input type=\"submit\" value=\"".GLOBALMSG_RECORD_EDIT."\">
		</form>
		";
		break;
	case "update":
		//caching disabler
//		echo "<meta http-equiv=\"Cache-Control\" content=\"no-cache\" />\n";
//		echo "<meta http-equiv=\"Expires\" content=\"0\" />\n";
		update_data($start_id,$start_data,$payment_data);
		break;
	case "delete":
		//caching disabler
//		echo "<meta http-equiv=\"Cache-Control\" content=\"no-cache\" />\n";
//		echo "<meta http-equiv=\"Expires\" content=\"0\" />\n";
		if(isset($_GET['deleteconfirm'])){
			$deleteconfirm=$_GET['deleteconfirm'];
		} elseif(isset($_POST['deleteconfirm'])){
			$deleteconfirm=$_POST['deleteconfirm'];
		}
		if($deleteconfirm){
			$delete=$_SESSION["delete"];
			unset($_SESSION["delete"]);

			delete_rows($delete);
		} else {
			if(isset($_GET['delete'])){
				$delete=$_GET['delete'];
			} elseif(isset($_POST['delete'])){
				$delete=$_POST['delete'];
			}

			if(is_array($delete)){
				echo "
				".GLOBALMSG_RECORDS_DELETE_CONFIRM."<br><br>\n";
					$_SESSION["delete"]=$delete;
					for (reset ($delete); list ($key, $value) = each ($delete); ) {
						$description=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],"account_mgmt_main","description",$key);
						echo "<LI>".$description."</LI>";
					}
				echo "
				<table><tr><td>
				<form action=\"db.php\" method=\"GET\">
				<input type=\"hidden\" name=\"command\" value=\"delete\">\n";
				echo "
				<input type=\"hidden\" name=\"deleteconfirm\" value=\"1\">
				<input type=\"submit\" value=\"".ucfirst(phr('YES'))."\">
				</form></td>
				<td><form action=\"index.php\" method=\"GET\">
				<input type=\"submit\" value=\"".ucfirst(phr('NO'))."\">
				</form>
				</td></tr></table>
				";
			} else {
				echo GLOBALMSG_RECORD_NONE_SELECTED_ERROR.".<br>";
			}
		}
		break;
}

if($command!="delete") unset($_SESSION["delete"]);

echo "<br><a href=\"#\" onclick=\"javascript:history.go(-1); return false\">".ucfirst(phr('GO_BACK'))."</a><br>\n";
//echo "<br><a href=\"".$_SESSION['lastpage']."\">Torna indietro</a>\n";
echo "<br><a href=\"index.php\">".ucfirst(phr('GO_MAIN_REPORT'))."</a><br>";
echo generating_time($inizio);
?>

