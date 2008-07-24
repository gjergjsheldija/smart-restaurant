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

if(isset($start_id)){
	$_SESSION['who']=$start_id;
}

//echo "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."<br>";
?>
<meta http-equiv="Cache-Control" content="no-cache" />
<meta http-equiv="Expires" content="0" />

<?php

if(!access_allowed(USER_BIT_CONTACTS)) $command='access_denied';

switch($command) {
	case 'access_denied':
				echo access_denied_admin();
				break;
	case "new":
		if(isset($_GET['insert_type'])){
			$insert_type=$_GET['insert_type'];
		} elseif(isset($_POST['insert_type'])){
			$insert_type=$_POST['insert_type'];
		}
		echo "
		<form action=\"supply.php\" method=\"POST\">
		<input type=\"hidden\" name=\"command\" value=\"insert\">
		";

		display_supplier_form(0,$insert_type);
		echo "
		<input type=\"submit\">
		</form>
		";
		break;
	case "insert":
		insert_supplier_data($start_data);
		break;
	case "edit":
		if(isset($_GET['orderby'])){
			$orderby=$_GET['orderby'];
		} elseif(isset($_POST['orderby'])){
			$orderby=$_POST['orderby'];
		}
		if(isset($_GET['name'])){
			$name=$_GET['name'];
		} elseif(isset($_POST['name'])){
			$name=$_POST['name'];
		}

?>
		<form action="supply.php" method="GET">
		<input type="hidden" name="command" value="update">
		<input type="hidden" name="id" value="<?php echo $start_id; ?>">
<?php
		if(isset($start_id)){
			display_supplier_form($start_id);
			echo "
			<input type=\"submit\" value=\"".GLOBALMSG_RECORD_EDIT."\">
			</form>
			";
		}
		break;
	case "showknownsupplier":
		if(isset($_GET['orderby'])){
			$orderby=$_GET['orderby'];
		} elseif(isset($_POST['orderby'])){
			$orderby=$_POST['orderby'];
		}
		if(isset($_GET['name'])){
			$name=$_GET['name'];
		} elseif(isset($_POST['name'])){
			$name=$_POST['name'];
		}

		main_header('supply.php');
		echo "
		<form action=\"supply.php\" method=\"GET\">
		<input type=\"hidden\" name=\"command\" value=\"edit\">
		<input type=\"hidden\" name=\"id\" value=\"$start_id\">
		";
		if(isset($start_id)){

			echo "
			<table width=\"100%\"><tr><td valign=\"top\" align=\"left\">\n";

			display_supplier_show($start_id);
			echo "
			<input type=\"submit\" value=\"".GLOBALMSG_RECORD_EDIT."\">
			</form>\n";

			//form_insert_type();

			echo "</td><td valign=\"top\" align=\"center\">\n";
			form_supplier_note($start_id);
			table_general($orderby,"",1,$start_id);
			echo "</td></tr></table>\n";
		}
		break;
	case "show":
		if(isset($_GET['orderby'])){
			$orderby=$_GET['orderby'];
		} elseif(isset($_POST['orderby'])){
			$orderby=$_POST['orderby'];
		}
		if(isset($_GET['name'])){
			$name=$_GET['name'];
		} elseif(isset($_POST['name'])){
			$name=$_POST['name'];
		}

		if (isset($name)) {
			main_header();
			table_general($orderby,"",2,$name);
		}
		break;
	case "update":
		update_supplier_data($start_id,$start_data);
		break;
	case "update_note":
		update_supplier_note($start_id,$start_data);
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

			delete_supplier($delete);
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
						$description=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],"account_mgmt_addressbook","name",$key);
						echo $description."<br>";
					}
				echo "
				<table><tr><td>
				<form action=\"supply.php\" method=\"GET\">
				<input type=\"hidden\" name=\"command\" value=\"delete\">\n";
				echo "
				<input type=\"hidden\" name=\"deleteconfirm\" value=\"1\">
				<input type=\"submit\" value=\"".ucfirst(phr('YES'))."\">
				</form></td>
				<td><form action=\"supply.php\" method=\"GET\">
				<input type=\"hidden\" name=\"command\" value=\"list\">
				<input type=\"submit\" value=\"".ucfirst(phr('NO'))."\">
				</form>
				</td></tr></table>
				";
			} else {
				echo GLOBALMSG_RECORD_NONE_SELECTED_ERROR.".<br>";
			}
		}
		break;
	case "list":
		if(isset($_GET['orderby'])){
			$orderby=$_GET['orderby'];
		} elseif(isset($_POST['orderby'])){
			$orderby=$_POST['orderby'];
		}
		// main_header();
		table_supplier($orderby);
		break;
	case "list_by_type":
		if(isset($_GET['orderby'])){
			$orderby=$_GET['orderby'];
			$_SESSION['orderby']=$orderby;
		} elseif(isset($_POST['orderby'])){
			$orderby=$_POST['orderby'];
			$_SESSION['orderby']=$orderby;
		} elseif(isset($_SESSION['orderby'])){
			$orderby=$_SESSION['orderby'];
		}
		if(isset($_GET['supplier_type'])){
			$supplier_type=$_GET['supplier_type'];
			$_SESSION['supplier_type']=$supplier_type;
		} elseif(isset($_POST['supplier_type'])){
			$supplier_type=$_POST['supplier_type'];
			$_SESSION['supplier_type']=$supplier_type;
		} elseif(isset($_SESSION['supplier_type'])){
			$supplier_type=$_SESSION['supplier_type'];
		}

		main_header();
		table_general($orderby,"list_by_type",3,$supplier_type);
		break;
	default:
		if(isset($_GET['orderby'])){
			$orderby=$_GET['orderby'];
		} elseif(isset($_POST['orderby'])){
			$orderby=$_POST['orderby'];
		}
		//main_header();
		table_supplier($orderby);
		break;

}
if($command!="delete") unset($_SESSION["delete"]);

echo "<br><a href=\"#\" onclick=\"javascript:history.go(-1); return false\">".ucfirst(phr('GO_BACK'))."</a><br>\n";
echo "<br><a href=\"index.php\">".ucfirst(phr('GO_MAIN_REPORT'))."</a><br>";
echo generating_time($inizio);
?>

