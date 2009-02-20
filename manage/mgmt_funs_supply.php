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

function printable_css($i){
	if($i==-2){
		return 'mgmt_printable_background';
	}elseif($i==-1){
		return 'mgmt_printable_tablebg';
	} elseif(($i%2)){
		return 'mgmt_printable_cellbg1';
	} else {
		return 'mgmt_printable_cellbg0';
	}
}

function color_css($i){
	if($i==-2){
		return 'mgmt_color_background';
	}elseif($i==-1){
		return 'mgmt_color_tablebg';
	} elseif(($i%2)){
		return 'mgmt_color_cellbg1';
	} else {
		return 'mgmt_color_cellbg0';
	}
}

function table_supplier($orderby){

	if($orderby==""){
		$orderby="name";
	}
	echo "<form name=\"form1\" action=\"supply.php\" method=\"GET\">\n";
	echo "<input type=\"hidden\" name=\"command\" value=\"delete\">\n";
	echo "<table bgcolor=\"".color(-1)."\">\n";
	echo "<thead ><tr>
	<td></td>
	<td><a href=\"supply.php?command=list&orderby=name\">".ucfirst(phr('NAME'))."</a></td>
	<td><a href=\"supply.php?command=list&orderby=vat\">".ucfirst(phr('VAT_ACCOUNT'))."</a></td>
	<td><a href=\"supply.php?command=list&orderby=type\">".ucfirst(phr('TYPE'))."</a></td>
	</tr></thead>
	<tbody>\n";

	$i=0;
	$table='account_mgmt_addressbook';
	$query="SELECT * FROM $table ORDER BY `$orderby`";
	$res=mysql_db_query($_SESSION['common_db'],$query);
	if(mysql_num_rows($res)){
		while($row=mysql_fetch_array($res)){
			$id=$row['id'];
			$name=$row['name'];
			$VAT=$row['vat'];

			$table='mgmt_people_types';
			$res_type=mysql_db_query($_SESSION['common_db'],"SELECT * FROM $table WHERE `id`='".$row['type']."'");
			$row_type=mysql_fetch_array($res_type);
			$people_type = new mgmt_people_type($row['type']);
			$type=ucfirst(strtolower($people_type -> name($_SESSION['language'])));
			unset($people_type);

			$color=color($i);
?>
	<tr bgcolor="<?php echo $color; ?>">
		<td><input name="delete[<?php echo $id; ?>]" type="checkbox"></td>
		<td><a href="supply.php?command=showknownsupplier&id=<?php echo $id; ?>"><?php echo $name; ?></a></td>
		<td><?php echo $VAT; ?></td>
		<td><a href="supply.php?command=list_by_type&supplier_type=<?php echo $row['type']; ?>"><?php echo $type; ?></a></td>
	</tr>
<?php
			$i++;
		}
	}
	echo "</tbody></table>";
	echo "<input type=\"submit\" value=\"".GLOBALMSG_RECORD_DELETE_SELECTED."\">";

	echo "</form>\n";
}

function check_supplier_compulsory_fields($data){
	if(!isset($data["name"])) {
		return 1;
	} elseif(isset($data["name"]) && $data["name"]=="") {
		return 1;
	}
	return 0;

}
function insert_supplier_data($data) {
	if($err=check_supplier_compulsory_fields($data)){
		switch($err){
			case 1: $msg=ucfirst(phr('CHECK_NAME'))."."; break;
		}
		echo "<script language=\"javascript\">
			window.alert(\"".$msg."\");
			history.go(-1);
		</script>\n";
		return 1;
	}

	// Now we'll build the correct INSERT query, based on the fields provided
	$table='account_mgmt_addressbook';
	$query="INSERT INTO $table (";
	for (reset ($data); list ($key, $value) = each ($data); ) {
		$query.="`".$key."`,";
	}
	// strips the last comma that has been put
	$query = substr ($query, 0, strlen($query)-1);
	$query.=") VALUES (";
	for (reset ($data); list ($key, $value) = each ($data); ) {
		$query.="'".$value."',";
	}
	// strips the last comma that has been put
	$query = substr ($query, 0, strlen($query)-1);	$query.=")";

	$res = mysql_db_query ($_SESSION['common_db'],$query);
	$num_affected=mysql_affected_rows();
	$inserted_id = mysql_insert_id();

	if ($num_affected==1) {
		echo("<META HTTP-EQUIV=\"Refresh\" CONTENT=\"".get_conf(__FILE__,__LINE__,"refresh_time_management")."; URL=".ROOTDIR."/manage/supply.php?command=list\">");
		echo GLOBALMSG_RECORD_THE." <b>".$data['name']."</b> ".GLOBALMSG_RECORD_ADD_OK.". <br>\n";
		echo "ID: $inserted_id.<br>\n";
	}elseif(mysql_errno()) {
		echo ucfirst(phr('ERROR')).".<br>\n";
	} else {
		echo GLOBALMSG_RECORD_ADD_NONE.".<br>\n";
	}
}
function form_supplier_standard($id,$editing){
	if ($editing) {
		$table='account_mgmt_addressbook';
		$res=mysql_db_query($_SESSION['common_db'],"SELECT * FROM $table WHERE `id`='$id'");
		$row=mysql_fetch_array($res);
	}

	echo "<tr><td>".ucfirst(phr('NAME'))."</td>\n";
	echo "<td>\n";
	echo "<input type=\"text\" name=\"data[name]\" value=\"".$row['name']."\">";
	echo "</td></tr>\n";

	echo "<tr><td>".ucfirst(phr('ADDRESS'))."</td>\n";
	echo "<td>\n";
	echo "<input type=\"text\" name=\"data[address]\" value=\"".$row['address']."\">";
	echo "</td></tr>\n";

	echo "<tr><td>".ucfirst(phr('PHONE'))."</td>\n";
	echo "<td>\n";
	echo "<input type=\"text\" name=\"data[telephone]\" value=\"".$row['telephone']."\">";
	echo "</td></tr>\n";

	echo "<tr><td>".ucfirst(phr('FAX'))."</td>\n";
	echo "<td>\n";
	echo "<input type=\"text\" name=\"data[fax]\" value=\"".$row['fax']."\">";
	echo "</td></tr>\n";

	echo "<tr><td>".ucfirst(phr('EMAIL'))."</td>\n";
	echo "<td>\n";
	echo "<input type=\"text\" name=\"data[email]\" value=\"".$row['email']."\">";
	echo "</td></tr>\n";

	echo "<tr><td>".ucfirst(phr('WEBSITE'))."</td>\n";
	echo "<td>\n";
	echo "<input type=\"text\" name=\"data[web]\" value=\"".$row['web']."\">";
	echo "</td></tr>\n";

	echo "<tr><td>".ucfirst(phr('VAT_ACCOUNT'))."<br/>".ucfirst(phr('SOCIAL_SECURITY_NUMBER'))."</td>\n";
	echo "<td>\n";
	echo "<input type=\"text\" name=\"data[vat]\" value=\"".$row['vat']."\">";
	echo "</td></tr>\n";

	echo "<tr><td>".phr('ACCOUNT_NUMBER')."</td>\n";
	echo "<td>\n";
	echo "<input maxlength=\"12\" type=\"text\" name=\"data[bank_account]\" value=\"".$row['bank_account']."\">";
	echo "</td></tr>\n";

	echo "<tr><td>".phr('ACCOUNT_ABI')."</td>\n";
	echo "<td>\n";
	echo "<input maxlength=\"5\" type=\"text\" name=\"data[abi]\" value=\"".$row['abi']."\">";
	echo "</td></tr>\n";

	echo "<tr><td>".phr('ACCOUNT_CAB')."</td>\n";
	echo "<td>\n";
	echo "<input maxlength=\"5\" type=\"text\" name=\"data[cab]\" value=\"".$row['cab']."\">";
	echo "</td></tr>\n";
}

function form_bank($id,$type_id){
	if($id) {
		$editing=1;
		$table='account_mgmt_addressbook';
		$res=mysql_db_query($_SESSION['common_db'],"SELECT * FROM $table WHERE `id`='$id'");
		$row=mysql_fetch_array($res);

	} else {
		$editing=0;
	}

	echo "<input type=\"hidden\" name=\"data[type]\" value=\"".$type_id."\">\n";
	echo "<h4>".ucfirst(phr('BANK_FILE'))."</h4><br>\n";
	echo "<table>\n";

	form_supplier_standard($id,$editing);
	echo "</table>\n";


}

function form_supplier($id,$type_id){
	if($id) {
		$editing=1;
		$table='account_mgmt_addressbook';
		$res=mysql_db_query($_SESSION['common_db'],"SELECT * FROM $table WHERE `id`='$id'");
		$row=mysql_fetch_array($res);

	} else {
		$editing=0;
	}

	echo "<input type=\"hidden\" name=\"data[type]\" value=\"".$type_id."\">\n";
	echo "<h4>".ucphr('SUPPLIER_FILE')."</h4><br>\n";
	echo "<table>\n";

	form_supplier_standard($id,$editing);
	echo "</table>\n";
}

function form_employee($id,$type_id){
	if($id) {
		$editing=1;
		$table='account_mgmt_addressbook';
		$res=mysql_db_query($_SESSION['common_db'],"SELECT * FROM $table WHERE `id`='$id'");
		$row=mysql_fetch_array($res);

	} else {
		$editing=0;
	}
	echo "<input type=\"hidden\" name=\"data[type]\" value=\"".$type_id."\">\n";

	echo "<h4>".ucfirst(phr('EMPLOYEE_FILE'))."</h4><br>\n";
	echo "<table>\n";

	form_supplier_standard($id,$editing);
	echo "</table>\n";
}

function form_pos($id,$type_id){
	if($id) {
		$editing=1;
		$table='account_mgmt_addressbook';
		$res=mysql_db_query($_SESSION['common_db'],"SELECT * FROM $table WHERE `id`='$id'");
		$row=mysql_fetch_array($res);

	} else {
		$editing=0;
	}
	echo "<input type=\"hidden\" name=\"data[type]\" value=\"".$type_id."\">\n";

	echo "<h4>".GLOBALMSG_POS_CIRCUIT_FILE."</h4><br>\n";
	echo "<table>\n";

	form_supplier_standard($id,$editing);
	echo "</table>\n";

}

function form_other($id,$type_id){
	if($id) {
		$editing=1;
		$table='account_mgmt_addressbook';
		$res=mysql_db_query($_SESSION['common_db'],"SELECT * FROM $table WHERE `id`='$id'");
		$row=mysql_fetch_array($res);

	} else {
		$editing=0;
	}

	echo "<input type=\"hidden\" name=\"data[type]\" value=\"".$type_id."\">\n";

	echo "<h4>".GLOBALMSG_OTHER_FILE."</h4><br>\n";
	echo "<table>\n";

	form_supplier_standard($id,$editing);
	echo "</table>\n";
}


function display_supplier_form($id,$insert_type=0){
	if($id){
		$table='account_mgmt_addressbook';
		$res=mysql_db_query($_SESSION['common_db'],"SELECT * FROM $table WHERE `id`='$id'");
		$row=mysql_fetch_array($res);
		$insert_type=$row['type'];
	}

	$type_id=$insert_type;
	$table='mgmt_people_types';
	$res=mysql_db_query($_SESSION['common_db'],"SELECT * FROM $table WHERE `id`='$insert_type'");
	$row=mysql_fetch_array($res);
	$insert_type=strtolower($row['name']);

	switch($insert_type){
		case "banca": form_bank($id,$type_id); break;
		case "operatore pos": form_pos($id,$type_id); break;
		case "dipendente": form_employee($id,$type_id); break;
		case "altro": form_other($id,$type_id); break;
		case "fornitore": form_supplier($id,$type_id); break;
		default: echo "ERROR"; return 1; break;
	}
}

function display_supplier_show($id){
	require("./mgmt_start.php");

	$table='account_mgmt_addressbook';
	$query="SELECT * FROM $table WHERE `id`='".$id."'";
	$res=mysql_db_query($_SESSION['common_db'],$query);
	if(mysql_num_rows($res)!=1) return 1;
	$row=mysql_fetch_array($res);

	$type=$row['type'];

	$people_type = new mgmt_people_type($type);
	$type=strtolower($people_type -> name($_SESSION['language']));
	unset($people_type);


	echo "<table bgcolor=\"".color(-1)."\">";
	echo "<tr>
		<td bgcolor=\"$mgmt_color_tablebg\">".ucfirst(phr('NAME'))."</td>
		<td bgcolor=\"$mgmt_color_background\">".$row['name']."</td>
	</tr>\n";
	echo "<tr>
		<td bgcolor=\"$mgmt_color_tablebg\">".ucfirst(phr('ADDRESS'))."</td>
		<td bgcolor=\"$mgmt_color_background\">".$row['address']."</td>
	</tr>\n";
	echo "<tr>
		<td bgcolor=\"$mgmt_color_tablebg\">".ucfirst(phr('PHONE'))."</td>
		<td bgcolor=\"$mgmt_color_background\">".$row['telephone']."</td>
	</tr>\n";
	echo "<tr>
		<td bgcolor=\"$mgmt_color_tablebg\">".ucfirst(phr('FAX'))."</td>
		<td bgcolor=\"$mgmt_color_background\">".$row['fax']."</td>
	</tr>\n";
	echo "<tr>
		<td bgcolor=\"$mgmt_color_tablebg\">".ucfirst(phr('EMAIL'))."</td>
		<td bgcolor=\"$mgmt_color_background\">".$row['email']."</td>
	</tr>\n";
?>
	<tr>
		<td bgcolor="<?php echo $mgmt_color_tablebg; ?>"><?php echo ucfirst(phr('WEBSITE')); ?></td>
		<td bgcolor="<?php echo $mgmt_color_background; ?>"><a href="http://<?php echo $row['web']; ?>" target="_blank"><?php echo $row['web']; ?></a></td>
	</tr>
<?php
		echo "<tr>
			<td bgcolor=\"$mgmt_color_tablebg\">".ucfirst(phr('VAT_ACCOUNT'))."<br>".ucfirst(phr('SOCIAL_SECURITY_NUMBER'))."</td>
			<td bgcolor=\"$mgmt_color_background\">".$row['vat']."</td>
		</tr>\n";
		echo "<tr>
			<td bgcolor=\"$mgmt_color_tablebg\">".phr('ACCOUNT_NUMBER')."</td>
			<td bgcolor=\"$mgmt_color_background\">".$row['bank_account']."</td>
		</tr>\n";
		echo "<tr>
			<td bgcolor=\"$mgmt_color_tablebg\">".phr('ACCOUNT_ABI')."</td>
			<td bgcolor=\"$mgmt_color_background\">".$row['abi']."</td>
		</tr>\n";
		echo "<tr>
			<td bgcolor=\"$mgmt_color_tablebg\">".phr('ACCOUNT_CAB')."</td>
			<td bgcolor=\"$mgmt_color_background\">".$row['cab']."</td>
		</tr>\n";
	echo "</table>\n";
}

function form_insert_supplier() {
	$table='mgmt_people_types';
	$res = mysql_db_query ($_SESSION['common_db'],"SELECT * FROM $table ORDER BY `name`");
?>
	<form action="supply.php" method="GET" name="supplier_form">
	<input type="hidden" name="command" value="new">


	<table border="0">
	<tbody>
	<tr><td>
	<FIELDSET>
	<LEGEND><?php echo ucfirst(phr('CONTACT_INSERT')); ?></LEGEND>

		<table border="0">
		<tbody>
<?php
		$i=0;
		while($row=mysql_fetch_array ($res)) {
			$people_type = new mgmt_people_type($row['id']);
			$type_name=$people_type -> name($_SESSION['language']);
			unset($people_type);

			if($i%2) {
				echo "<td>";
			} else {
				echo "<tr><td>\n";
			}
			if(!$i){
?>
			<input type="radio" onClick="document.supplier_form.submit()" name="insert_type" value="<?php echo $row['id']; ?>" checked>
			<a href="#" onclick="JavaScript:type_insert_check('supplier_form','insert_type',<?php echo $i; ?>);document.supplier_form.submit();return(false);"><?php echo $type_name; ?></a><br>
<?php
			} else {
?>
			<input type="radio" onClick="document.supplier_form.submit()" name="insert_type" value="<?php echo $row['id']; ?>">
			<a href="#" onclick="JavaScript:type_insert_check('supplier_form','insert_type',<?php echo $i; ?>);document.supplier_form.submit();return(false);"><?php echo $type_name; ?></a><br>
<?php
			}
			if($i%2) {
				echo "</td></tr>\n";
			} else {
				echo "</td>\n";
			}
			$i++;
		}

		if($i%2) {
			echo "</tr>";
		}

		echo "<tr><td align=\"center\" colspan=\"2\">\n";
		echo "<div align=\"center\"></form>\n";
		echo "<form action=\"supply.php\" method=\"GET\" name=\"supplier_list\">\n";
		echo "<input type=\"hidden\" name=\"command\" value=\"list\">\n";
		echo "<input type=\"submit\" value=\"".ucfirst(phr('CONTACTS_LIST'))."\">\n";
		echo "</form></div>\n";
		echo "</td></tr>\n";
		echo "</FIELDSET></tbody></table>\n";
	echo "</td></tr>\n";
	echo "
</table>\n";
}

function form_supplier_note($id){
	$table='account_mgmt_addressbook';
	$res=mysql_db_query($_SESSION['common_db'],"SELECT * FROM $table WHERE `id`='$id'");
	if(!mysql_num_rows($res)) return 1;
	$row=mysql_fetch_array($res);
	echo "<form action=\"supply.php\" method=\"GET\" name=\"supplier_note\" target=\"_blank\">\n";
	echo "<input type=\"hidden\" name=\"command\" value=\"update_note\">\n";
	echo "<input type=\"hidden\" name=\"id\" value=\"$id\">\n";
	echo "<textarea rows=\"10\" cols=\"100\" name=\"data[note]\">".$row['note']."</textarea><br>\n";
	echo "<input type=\"submit\" value=\"".ucfirst(phr('UPDATE_NOTE'))."\">\n";
	echo "</form><br>\n";

}

function update_supplier_data($id,$data) {
	if($err=check_supplier_compulsory_fields($data)){
		switch($err){
			case 1: $msg=ucfirst(phr('CHECK_NAME'))."."; break;
		}
		echo "<script language=\"javascript\">
			window.alert(\"".$msg."\");
			history.go(-1);
		</script>\n";

		return 1;
	}

	// Now we'll build the correct INSERT query, based on the fields provided
	$table='account_mgmt_addressbook';
	$query="UPDATE $table SET ";
	for (reset ($data); list ($key, $value) = each ($data); ) {
		$query.="`".$key."`='".$value."',";
	}
	// strips the last comma that has been put
	$query = substr ($query, 0, strlen($query)-1);
	$query.=" WHERE `id`='$id'";

	$res = mysql_db_query ($_SESSION['common_db'],$query);
	$num_affected=mysql_affected_rows();

	if ($num_affected==1) {
		echo("<META HTTP-EQUIV=\"Refresh\" CONTENT=\"".get_conf(__FILE__,__LINE__,"refresh_time_management")."; URL=supply.php?command=showknownsupplier&id=$id\">");
		echo GLOBALMSG_RECORD_THE." <b>".$data['name']."</b> ".GLOBALMSG_RECORD_EDIT_OK.". <br>\n";
	}elseif(mysql_errno()) {
		echo ucfirst(phr('ERROR')).".<br>\n";
	} else {
		echo GLOBALMSG_RECORD_EDIT_NONE.".<br>\n";
	}
}

function update_supplier_note($id,$data) {
	if($data['note']=="") return 1;

	// Now we'll build the correct INSERT query, based on the fields provided
	$table='account_mgmt_addressbook';
	$query="UPDATE $table SET ";
	for (reset ($data); list ($key, $value) = each ($data); ) {
		$query.="`".$key."`='".$value."',";
	}
	// strips the last comma that has been put
	$query = substr ($query, 0, strlen($query)-1);
	$query.=" WHERE `id`='$id'";

	$res = mysql_db_query ($_SESSION['common_db'],$query);
	$num_affected=mysql_affected_rows();

	if ($num_affected==1) {
		echo("<META HTTP-EQUIV=\"Refresh\" CONTENT=\"".get_conf(__FILE__,__LINE__,"refresh_time_management")."; URL=javascript:window.close();\">");
		echo GLOBALMSG_RECORD_THE." <b>".$data['name']."</b> ".GLOBALMSG_RECORD_EDIT_OK.". <br>\n";
	}elseif(mysql_errno()) {
		echo ucfirst(phr('ERROR')).".<br>\n";
	} else {
		echo GLOBALMSG_RECORD_EDIT_NONE.".<br>\n";
	}
}


function delete_supplier($delete){

// next is only a ref line. to be deleted.
	$firstline=1;
	$counter=0;

	$table='account_mgmt_addressbook';
	$query="DELETE FROM $table WHERE ";
	if(is_array($delete)){
		for (reset ($delete); list ($key, $value) = each ($delete); ) {
			if($firstline) {
				$query.="`id`='".$key."'";
				$firstline=0;
			} else {
				$query.=" OR `id`='".$key."'";
			}
			$description[$key]=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],"account_mgmt_addressbook","name",$key);
			$counter++;
		}
	} else {
		$query.="`id`='$delete'";
		$description=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],"account_mgmt_addressbook","name",$delete);
		$counter++;
	}

	$res = mysql_db_query ($_SESSION['common_db'],$query);
	$num_affected=mysql_affected_rows();

	if ($num_affected!=0 && $counter>1) {
		echo("<META HTTP-EQUIV=\"Refresh\" CONTENT=\"".get_conf(__FILE__,__LINE__,"refresh_time_management")."; URL=".ROOTDIR."/manage/supply.php?command=list\">");
		$msg = GLOBALMSG_RECORD_THE_MANY." <b>";
		for (reset ($delete); list ($key, $value) = each ($delete); ) {
			$msg.=$description[$key];
			$msg.=", ";
		}
		$msg = substr($msg,0,strlen($msg)-2);

		$msg.="</b> ".GLOBALMSG_RECORD_DELETE_OK_MANY.". <br>\n";
	} elseif ($num_affected!=0 && $counter==1) {
		echo("<META HTTP-EQUIV=\"Refresh\" CONTENT=\"".get_conf(__FILE__,__LINE__,"refresh_time_management")."; URL=".ROOTDIR."/manage/supply.php?command=list\">");
		$msg = GLOBALMSG_RECORD_THE." <b>";
		if(is_array($delete)) {
		for (reset ($delete); list ($key, $value) = each ($delete); ) {
			$msg.=$description[$key];
			$msg.=", ";
		}
		$msg = substr($msg,0,strlen($msg)-2);
		} else {
			$msg.=$description;
		}

		$msg.="</b> ".GLOBALMSG_RECORD_DELETE_OK.". <br>\n";

	}elseif(mysql_errno()) {
		echo ucfirst(phr('ERROR')).".<br>\n";
		return 1;
	} else {
		echo GLOBALMSG_RECORD_DELETE_NONE.".<br>\n";
	}
	echo $msg;
	return 0;
}


?>
