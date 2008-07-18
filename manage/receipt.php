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

if(!access_allowed(USER_BIT_ACCOUNTING)) $command='access_denied';

switch($command) {
	case 'access_denied':
				echo access_denied_admin();
				break;
	case "show":
		show_receipt($start_id);
		break;
	case "delete":
		if(isset($_GET['deleteconfirm'])){
			$deleteconfirm=$_GET['deleteconfirm'];
		} elseif(isset($_POST['deleteconfirm'])){
			$deleteconfirm=$_POST['deleteconfirm'];
		}
		if($deleteconfirm){
			$delete=$_SESSION["delete"];
			unset($_SESSION["delete"]);

			delete_receipt_rows($delete);
			delete_log_rows($delete);
		} else {
			if(isset($_GET['delete'])){
				$delete=$_GET['delete'];
			} elseif(isset($_POST['delete'])){
				$delete=$_POST['delete'];
			}

			if(is_array($delete)){
				echo GLOBALMSG_RECEIPT_ANNULL_CONFIRM."<br>\n";
				$_SESSION["delete"]=$delete;
				for (reset ($delete); list ($key, $value) = each ($delete); ) {
					$table=$GLOBALS['table_prefix'].'account_mgmt_main';
					$res=mysql_db_query($_SESSION['common_db'],"SELECT * FROM $table WHERE `id`='$key'");
					$row=mysql_fetch_array($res);
					mysql_free_result($res);
					echo "<LI>".$row['internal_id']."</LI>";
				}
				echo "
				<table><tr><td>
				<form action=\"receipt.php\" method=\"GET\">
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
	case "annul":
		if(isset($_GET['annulconfirm'])){
			$annulconfirm=$_GET['annulconfirm'];
		} elseif(isset($_POST['annulconfirm'])){
			$annulconfirm=$_POST['annulconfirm'];
		}
		if($annulconfirm){
			$annul=$_SESSION["annul"];
			unset($_SESSION["annul"]);

			annul_receipt_rows($annul);
			delete_log_rows($annul);
		} else {
			if(isset($_GET['annul'])){
				$annul=$_GET['annul'];
			} elseif(isset($_POST['annul'])){
				$annul=$_POST['annul'];
			}

			if(is_array($annul)){
				echo GLOBALMSG_RECEIPT_ANNULL_CONFIRM."<br>\n";
				$_SESSION["annul"]=$annul;
				for (reset ($annul); list ($key, $value) = each ($annul); ) {
					$table=$GLOBALS['table_prefix'].'account_mgmt_main';
					$res=mysql_db_query($_SESSION['common_db'],"SELECT * FROM $table WHERE `id`='$key'");
					$row=mysql_fetch_array($res);
					mysql_free_result($res);
					echo "<LI>".$row['internal_id']."</LI>";
				}
				echo "
				<table><tr><td>
				<form action=\"receipt.php\" method=\"GET\">
				<input type=\"hidden\" name=\"command\" value=\"annul\">\n";
				echo "
				<input type=\"hidden\" name=\"annulconfirm\" value=\"1\">
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
	default:
		if(isset($_GET['orderby'])){
			$orderby=$_GET['orderby'];
		} elseif(isset($_POST['orderby'])){
			$orderby=$_POST['orderby'];
		}

		echo "<table width=\"100%\"><tr><td align=\"left\">\n";
		// Next is the form to decide the type of data to insert
		//form_insert_receipt_type();

		echo "</td><td align=\"right\">\n";

		echo "</td></tr></table>\n";
		// next is the general report table creator
		table_receipt($orderby);
		break;
}
//echo "<a href=\"#\" onclick=\"javascript:history.go(-1); return false\">go back</a><br>\n";
echo "
	<br>
	<form action=\"index.php\" method=\"GET\">
	<input type=\"submit\" value=\"".ucfirst(phr('GO_MAIN_REPORT'))."\">
	</form>
";

echo generating_time($inizio);



?>
