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

if(isset($_GET['orderby'])){
	$orderby=$_GET['orderby'];
} elseif(isset($_POST['orderby'])){
	$orderby=$_POST['orderby'];
}

if(!access_allowed(USER_BIT_ACCOUNTING)) $command='access_denied';

switch($command) {
	case 'access_denied':
				echo access_denied_admin();
				break;
	case "movement_new":
		account_movement_form();
		break;
	case "movement_insert":
		account_movement_insert($start_data);
		break;
	case "movement_edit":
		account_movement_form($start_id);
		break;
	case "movement_show":
		account_movement_show($start_id);
		break;
	case "movement_list":
		if(!isset($orderby)) {
			$orderby="timestamp";
		}
		main_header("account.php");
		account_list("name");
		account_movement_list($start_id,$orderby);
		break;
	case "movement_update":
		account_movement_update($start_id,$start_data);
		break;
	case "movement_delete":
		if(isset($_GET['deleteconfirm'])){
			$deleteconfirm=$_GET['deleteconfirm'];
		} elseif(isset($_POST['deleteconfirm'])){
			$deleteconfirm=$_POST['deleteconfirm'];
		}
		if($deleteconfirm){
			$delete=$_SESSION["delete"];
			unset($_SESSION["delete"]);

			for (reset ($delete); list ($key, $value) = each ($delete); ) {
				if($value)
					account_movement_delete($key);
			}
		} else {
			if(isset($_GET['delete'])){
				$delete=$_GET['delete'];
			} elseif(isset($_POST['delete'])){
				$delete=$_POST['delete'];
			}

			if(is_array($delete)){
				echo "
				".GLOBALMSG_RECORDS_DELETE_CONFIRM."<br>\n";
					$_SESSION["delete"]=$delete;
					for (reset ($delete); list ($key, $value) = each ($delete); ) {
						$description=get_db_data(__FILE__,__LINE__,$_SESSION["common_db"],"account_account_log","description",$key);
						echo $description."<br>";
					}
?>
<table>
	<tr>
		<td>
			<form action="account.php" method="GET">
			<input type="hidden" name="command" value="movement_delete">
			<input type="hidden" name="deleteconfirm" value="1">
			<input type="submit" value="<?php echo ucfirst(phr('YES')); ?>">
			</form>
		</td>
		<td>
			<form action="account.php" method="GET">
			<input type="hidden" name="command" value="list">
			<input type="submit" value="<?php echo ucfirst(phr('NO')); ?>">
			</form>
		</td>
	</tr>
</table>
<?php
			} else {
?>
<?php echo GLOBALMSG_RECORD_NONE_SELECTED_ERROR; ?>.<br>
<?php
			}
		}
		break;
	case "new":
		account_form();
		break;
	case "insert":
		account_insert($start_data);
		break;
	case "edit":
		account_form($start_id);
		break;
	case "show":
		account_form($start_id);
		break;
	case "update":
		account_update($start_id,$start_data);
		break;
	case "list":
		if(!isset($orderby)) {
			$orderby="name";
		}

		main_header("account.php");
		account_list($orderby);
		break;
	default:
		if(isset($_GET['orderby'])){
			$orderby=$_GET['orderby'];
		} elseif(isset($_POST['orderby'])){
			$orderby=$_POST['orderby'];
		} else {
			$orderby="name";
		}
		main_header();
		account_list($orderby);
		break;
}
if($command!="movement_delete" && $command!="delete") unset($_SESSION["delete"]);
echo generating_time($inizio);
?>

