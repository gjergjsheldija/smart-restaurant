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

function invoice_payment_access_lock($id){
	$table='#prefix#account_mgmt_main';
	$query="SELECT * FROM $table WHERE `id`='".$id."'";
	$res=common_query($query,__FILE__,__LINE__);
	$row=mysql_fetch_array($res);
	while($row['associated_invoice']){
		$id=$row['associated_invoice'];
		$query="SELECT * FROM `$table` WHERE `id`='".$id."'";
		$res=mysql_db_query($_SESSION['common_db'],$query);
		$row=mysql_fetch_array($res);
	}
	return $id;
}

function file_show($id){
	require("./mgmt_start.php");

	$table='#prefix#account_mgmt_main';
	$query="SELECT * FROM $table WHERE `id`='".$id."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(mysql_num_rows($res)!=1) return 1;

	$row=mysql_fetch_array($res);

	$type=$row['type'];

	$table=$GLOBALS['table_prefix'].'mgmt_types';
	$res_type=mysql_db_query($_SESSION['common_db'],"SELECT * FROM $table WHERE `id`='$type'");
	$row_type=mysql_fetch_array($res_type);
	$type=strtolower($row_type['name']);

	$table='#prefix#account_mgmt_addressbook';
	$res_supplier = common_query ("SELECT * FROM $table WHERE `name`='".$row['who']."'",__FILE__,__LINE__);
	if(mysql_num_rows($res_supplier)==1){
		$row_supplier = mysql_fetch_array ($res_supplier);
		$supplier['id']=$row_supplier['id'];
		$who="<a href=\"supply.php?command=showknownsupplier&id=".$row_supplier['id']."\">".$row['who']."</a>";
	} else {
		$who="<a href=\"supply.php?command=show&name=".$row['who']."\">".$row['who']."</a>";
	}


	$year=substr($row['date'],0,4);
	$month=substr($row['date'],4,2);
	$day=substr($row['date'],6,2);
	$hour=substr($row['date'],8,2);
	$minute=substr($row['date'],10,2);
	$second=substr($row['date'],12,2);


	echo "<table bgcolor=\"".color(-1)."\">";
	echo "<tr>
		<td bgcolor=\"$mgmt_color_tablebg\">".ucfirst(phr('DATE'))."</td>
		<td bgcolor=\"$mgmt_color_background\">".$day."/".$month."/".$year."</td>
	</tr>\n";
	echo "<tr>
		<td bgcolor=\"$mgmt_color_tablebg\">".ucfirst(phr('TIME'))."</td>
		<td bgcolor=\"$mgmt_color_background\">".$hour.":".$minute.":".$second."</td>
	</tr>\n";
	echo "<tr>
		<td bgcolor=\"$mgmt_color_tablebg\">".ucfirst(phr('DESCRIPTION'))."</td>
		<td bgcolor=\"$mgmt_color_background\">".$row['description']."</td>
	</tr>\n";
	echo "<tr>
		<td bgcolor=\"$mgmt_color_tablebg\">".ucfirst(phr('WHO'))."</td>
		<td bgcolor=\"$mgmt_color_background\">".$who."</td>
	</tr>\n";
	switch($type){
		case "assegno":
			$row['bank_amount']=$row['bank_amount']*(-1);
			echo "<tr>
				<td bgcolor=\"$mgmt_color_tablebg\">".ucfirst(phr('AMOUNT'))."</td>
				<td bgcolor=\"$mgmt_color_background\">".abs($row['bank_amount'])."</td>
			</tr>\n";
			echo "<tr>
				<td bgcolor=\"$mgmt_color_tablebg\">".ucfirst(phr('CHEQUE_NUMBER'))."</td>
				<td bgcolor=\"$mgmt_color_background\">".$row['number']."</td>
			</tr>\n";
			echo "<tr>
				<td bgcolor=\"$mgmt_color_tablebg\">".ucfirst(phr('PLACE'))."</td>
				<td bgcolor=\"$mgmt_color_background\">".$row['place']."</td>
			</tr>\n";
			break;
		case "bonifico":
			$row['bank_amount']=$row['bank_amount']*(-1);
			echo "<tr>
				<td bgcolor=\"$mgmt_color_tablebg\">".ucfirst(phr('AMOUNT'))."</td>
				<td bgcolor=\"$mgmt_color_background\">".abs($row['bank_amount'])."</td>
			</tr>\n";
			echo "<tr>
				<td bgcolor=\"$mgmt_color_tablebg\">".ucfirst(phr('BANK'))."</td>
				<td bgcolor=\"$mgmt_color_background\">".$row['bank_name']."</td>
			</tr>\n";
			echo "<tr>
				<td bgcolor=\"$mgmt_color_tablebg\">".ucfirst(phr('ACCOUNT_NUMBER'))."</td>
				<td bgcolor=\"$mgmt_color_background\">".$row['bank_account']."</td>
			</tr>\n";
			echo "<tr>
				<td bgcolor=\"$mgmt_color_tablebg\">".ucfirst(phr('ACCOUNT_ABI'))."</td>
				<td bgcolor=\"$mgmt_color_background\">".$row['abi']."</td>
			</tr>\n";
			echo "<tr>
				<td bgcolor=\"$mgmt_color_tablebg\">".ucfirst(phr('ACCOUNT_CAB'))."</td>
				<td bgcolor=\"$mgmt_color_background\">".$row['cab']."</td>
			</tr>\n";
			break;
		case "fattura":
			if($row['paid']) {
				$table='#prefix#account_mgmt_main';
				$query="SELECT * FROM $table WHERE `associated_invoice`='$id'";
				$res=common_query($query,__FILE__,__LINE__);
				$arr_local=mysql_fetch_array($res);
				$payment_type=$arr_local['type'];
				if($payment_type==1){
					$taxable=$arr_local['bank_taxable_amount'];
					$vat=$arr_local['bank_vat_amount'];
				}elseif($payment_type==2){
					$taxable=$arr_local['bank_taxable_amount'];
					$vat=$arr_local['bank_vat_amount'];
				}elseif($payment_type==4){
					$taxable=$arr_local['cash_taxable_amount'];
					$vat=$arr_local['cash_vat_amount'];
				}
			} else {
				$taxable=$row['debit_taxable_amount'];
				$vat=$row['debit_vat_amount'];
			}
			echo "<tr>
				<td bgcolor=\"$mgmt_color_tablebg\">".ucfirst(phr('TAXABLE'))."</td>
				<td bgcolor=\"$mgmt_color_background\">".abs($taxable)."</td>
			</tr>\n";
			echo "<tr>
				<td bgcolor=\"$mgmt_color_tablebg\">".ucfirst(phr('TAX'))."</td>
				<td bgcolor=\"$mgmt_color_background\">".abs($vat)."</td>
			</tr>\n";
			if($row['paid']==1) $value="Si";
			else $value="No";
			echo "<tr>
				<td bgcolor=\"$mgmt_color_tablebg\">".ucfirst(phr('PAID'))."</td>
				<td bgcolor=\"$mgmt_color_background\">".$value."</td>
			</tr>\n";
			break;
		case "pos":
			echo "<tr>
				<td bgcolor=\"$mgmt_color_tablebg\">".ucfirst(phr('AMOUNT'))."</td>
				<td bgcolor=\"$mgmt_color_background\">".abs($row['bank_amount'])."</td>
			</tr>\n";
			break;
		case "ricevuta":
			echo "<tr>
				<td bgcolor=\"$mgmt_color_tablebg\">".ucfirst(phr('AMOUNT'))."</td>
				<td bgcolor=\"$mgmt_color_background\">".abs($row['cash_amount'])."</td>
			</tr>\n";
			break;
		case "scontrino":
			echo "<tr>
				<td bgcolor=\"$mgmt_color_tablebg\">".ucfirst(phr('AMOUNT'))."</td>
				<td bgcolor=\"$mgmt_color_background\">".abs($row['cash_amount'])."</td>
			</tr>\n";
			break;
		case "versamento":
			echo "<tr>
				<td bgcolor=\"$mgmt_color_tablebg\">".ucfirst(phr('AMOUNT'))."</td>
				<td bgcolor=\"$mgmt_color_background\">".abs($row['bank_amount'])."</td>
			</tr>\n";
			break;
	}
	echo "</table>\n";
}

function display_show($id){
	require("./mgmt_start.php");

	$table='#prefix#account_mgmt_main';
	$query="SELECT * FROM $table WHERE `id`='".$id."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(mysql_num_rows($res)!=1) return 1;
	$row=mysql_fetch_array($res);

	$type=$row['type'];
	$table=$GLOBALS['table_prefix'].'mgmt_types';
	$res_type=mysql_db_query($_SESSION['common_db'],"SELECT * FROM $table WHERE `id`='$type'");
	$row_type=mysql_fetch_array($res_type);
	$type=strtolower($row_type['name']);


	echo "<table><tr>\n";
	echo "<td>\n";
	file_show($id);
	echo "</td>\n";
	echo "<td align=\"right\">\n";
	if($type=="fattura"){
		invoice_stock_show($id);
	}
	echo "</td>\n";
	echo "</tr></table>\n";
}

function form_insert_type() {
	$table=$GLOBALS['table_prefix'].'mgmt_types';
	$res = mysql_db_query ($_SESSION['common_db'],"SELECT * FROM $table WHERE `account_only`=0 ORDER BY `name`");

?>
<table border="0">
	<tbody>
		<tr>
			<td>
	<FIELDSET>
	<LEGEND><?php echo ucfirst(GLOBALMSG_RECORD_INSERT); ?></LEGEND>

	<form action="db.php" method="GET" name="insert_form">
	<input type="hidden" name="command" value="new">
	<table border="0">
	<tbody>
<?php
	$i=0;
	while($row=mysql_fetch_array ($res)) {
		$mgmt_type = new mgmt_type($row['id']);
		$type_name=$mgmt_type -> name($_SESSION['language']);
		unset($mgmt_type);

		if($i%2) {
			echo "<td>";
		} else {
			echo "<tr><td>\n";
		}
		if(!$i){
?>
			<input type="radio" onClick="document.insert_form.submit()" name="insert_type" value="<?php echo $row['id']; ?>" checked>
			<a href="#" onclick="JavaScript:type_insert_check('insert_form','insert_type',<?php echo $i; ?>); document.insert_form.submit(); return(false);"><?php echo $type_name; ?></a><br>
<?php
		} else {
?>
			<input type="radio" onClick="document.insert_form.submit()" name="insert_type" value="<?php echo $row['id']; ?>">
			<a href="#" onclick="JavaScript:type_insert_check('insert_form','insert_type',<?php echo $i; ?>);document.insert_form.submit();return(false);"><?php echo $type_name; ?></a><br>
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
?>
	</tbody></table>
	</td></tr>
	<tr><td align="center">
	<table border="0"><tr><td align="center">
	</form>
	</td></tr></table>
	</FIELDSET>
	</td></tr></table>

</form>
<?php
}

function input_standard($id,$editing){
	if ($editing) {
		$table='#prefix#account_mgmt_main';
		$res=common_query("SELECT * FROM $table WHERE `id`='$id'",__FILE__,__LINE__);

		$row=mysql_fetch_array($res);

		$year=substr($row['date'],0,4);
		$month=substr($row['date'],5,2);
		$day=substr($row['date'],8,2);
		$hour=substr($row['date'],11,2);
		$minute=substr($row['date'],14,2);
		$second=substr($row['date'],17,2);

		$paid=$row['paid'];

		switch($row['type']){
			case 1: $amount=$row['bank_amount']; break;
			case 2: $amount=$row['bank_amount']; break;
			case 3:	if($row['paid']) {
						$table='#prefix#account_mgmt_main';
						$query="SELECT * FROM $table WHERE `associated_invoice`='$id'";
						$res=common_query($query,__FILE__,__LINE__);
						$arr_local=mysql_fetch_array($res);
						$payment_type=$arr_local['type'];
						if($payment_type==1)
							$amount=$arr_local['bank_amount'];
						elseif($payment_type==2)
							$amount=$arr_local['bank_amount'];
						elseif($payment_type==4)
							$amount=$arr_local['cash_amount'];

					} else {
						$amount=$row['debit_amount'];
					}
					break;
			case 4: $amount=$row['cash_amount']; break;
			case 5: $amount=$row['cash_amount']; break;
			case 6: $amount=$row['bank_amount']; break;
			case 7: $amount=$row['bank_amount']; break;
			default: $amount=0;
		}
		if($amount<=0){
			$oper['in']="";
			$oper['out']="checked";
		} else {
			$oper['in']="checked";
			$oper['out']="";
		}
	} else {
		$day=date("j",time());
		$month=date("n",time());
		$year=date("Y",time());
		$hour=date("H",time());
		$minute=date("i",time());
		$second=date("s",time());
		$debit=0;

		$oper['in']="";
		$oper['out']="checked";

	}
	echo "
	<tr>
	<td>".ucfirst(phr('DATE'))."</td>
	<td>
		<input type=\"text\" size=\"2\" name=\"data[date][day]\" value=\"$day\"> /
		<input type=\"text\" size=\"2\" name=\"data[date][month]\" value=\"$month\"> /
		<input type=\"text\" size=\"4\" name=\"data[date][year]\" value=\"$year\">
	</td>
	</tr>
	";


	echo "<tr><td>".ucfirst(phr('DESCRIPTION'))."</td>\n";
	echo "<td>\n";
	echo "<input type=\"text\" name=\"data[description]\" value=\"".$row['description']."\">";
	echo "</td></tr>\n";

	echo "<tr>
		<td>".ucfirst(phr('WHO'))."</td>
		<td>
		<select name=\"data[who]\">\n";
	$table='#prefix#account_mgmt_addressbook';
	$res_supplier = common_query ("SELECT * FROM $table ORDER BY `name`",__FILE__,__LINE__);

	if(!$editing){
		$selected="selected";
	} else {
		$selected="";
	}
	echo "<option value=\".\" $selected></option>";
	while ($row_supplier=mysql_fetch_array($res_supplier)){
		if($row_supplier['name']==$row['who']){
			$selected="selected";
		} else {
			$selected="";
		}
		echo "<option value=\"".$row_supplier['name']."\" $selected>".$row_supplier['name']."</option>";
	}
	echo "
		</select></td>
	</tr>\n";
?>
	<tr>
		<td>
		<input type="radio" name="data[operation]" value=-1 <?php echo $oper['out']; ?>><?php echo ucfirst(GLOBALMSG_RECORD_OUTGOING); ?>
		<input type="radio" name="data[operation]" value=1 <?php echo $oper['in']; ?>><?php echo ucfirst(GLOBALMSG_RECORD_INCOMING); ?>
		</td>
	</tr>
<?php
}

function display_form_invoice($id){
	$disabled="";
	echo "<table><tr><td>\n";

	echo "
	<FIELDSET>
	<LEGEND>".ucfirst(phr('INVOICE'))."</LEGEND>
	";

	echo "<table>\n";
	echo "<tr><td>\n";

	if($id) {
		$editing=1;
		$table='#prefix#account_mgmt_main';
		$res=common_query("SELECT * FROM $table WHERE `id`='$id'",__FILE__,__LINE__);
		$row=mysql_fetch_array($res);

		$paid=$row['paid'];
		echo "<input type=\"hidden\" name=\"data[type]\" value=\"".$row['type']."\">\n";
	} else {
		$id=0;
		$editing=0;
	}
	echo "</td><td></td></tr>\n";

	input_standard($id,$editing);

	if($paid) {
		$table='#prefix#account_mgmt_main';
		$res_payment=common_query("SELECT * FROM $table WHERE `associated_invoice`='$id'",__FILE__,__LINE__);

		$row_payment=mysql_fetch_array($res_payment);

		if($row_payment['type']==4) {
			$taxable=abs($row_payment['cash_taxable_amount']);
			$vat=abs($row_payment['cash_vat_amount']);
		} elseif($row_payment['type']==1 || $row_payment['type']==2) {
			$taxable=abs($row_payment['bank_taxable_amount']);
			$vat=abs($row_payment['bank_vat_amount']);
		}
	} else {
		$taxable=abs($row['debit_taxable_amount']);
		$vat=abs($row['debit_vat_amount']);
	}

	echo "<tr><td>".ucfirst(phr('TAXABLE'))."</td>\n";
	echo "<td>\n";
	echo "<input type=\"text\" name=\"data[debit_taxable_amount]\" value=\"".$taxable."\">";
	echo "</td></tr>\n";

	echo "<tr><td>".ucfirst(phr('TAX'))."</td>\n";
	echo "<td>\n";
	echo "<input type=\"text\" name=\"data[debit_vat_amount]\" value=\"".$vat."\">";
	echo "</td></tr>\n";
	echo "</table>\n";
	echo "</FIELDSET>\n";

	echo "
	<FIELDSET>
	<LEGEND>".ucfirst(GLOBALMSG_RECORD_PAYMENT)."</LEGEND>
	";

	echo "<table><tr>\n<td>".ucfirst(phr('PAID'))."</td>\n";
	if($editing && $paid==1) {
		$disabled="";
		echo "<td><input type=\"checkbox\" onclick=\"payment_activation();\" value=\"1\" name=\"data[paid]\" checked></td>\n";
	} elseif(!$editing) {
		$disabled="";
		echo "<td><input type=\"checkbox\" onclick=\"payment_activation();\" value=\"1\" name=\"data[paid]\" checked></td>\n";
	} else {
		$disabled="disabled=true";
		echo "<td><input type=\"checkbox\" onclick=\"payment_activation();\" value=\"1\" name=\"data[paid]\"></td>\n";
	}
	echo "</tr>\n";





	if($editing==1 && $paid==1){
		$table='#prefix#account_mgmt_main';
		$res=common_query("SELECT * FROM $table WHERE `associated_invoice`='$id'",__FILE__,__LINE__);

		$row=mysql_fetch_array($res);

		$year=substr($row['date'],0,4);
		$month=substr($row['date'],4,2);
		$day=substr($row['date'],6,2);
		$hour=substr($row['date'],8,2);
		$minute=substr($row['date'],10,2);
		$second=substr($row['date'],12,2);
	} else {
		$day=date("j",time());
		$month=date("n",time());
		$year=date("Y",time());
		$hour=date("H",time());
		$minute=date("i",time());
		$second=date("s",time());
	}
	echo "
	<tr>
	<td>".ucfirst(GLOBALMSG_RECORD_PAYMENT_DATE)."</td>
	<td>
		<input type=\"text\" size=\"2\" $disabled name=\"payment_data_date_day\" value=\"$day\"> /
		<input type=\"text\" size=\"2\" $disabled name=\"payment_data_date_month\" value=\"$month\"> /
		<input type=\"text\" size=\"4\" $disabled name=\"payment_data_date_year\" value=\"$year\">
	</td>
	</tr>";
	echo "<tr><td>";

	$table=$GLOBALS['table_prefix'].'mgmt_types';
	$res = mysql_db_query ($_SESSION['common_db'],"SELECT * FROM $table ORDER BY `name`");
	$i=0;
	while($row=mysql_fetch_array ($res)) {
		$mgmt_type = new mgmt_type($row['id']);
		$type_name=$mgmt_type -> name($_SESSION['language']);
		unset($mgmt_type);

		if($row['is_invoice_payment']){
			if($editing && $row['id']==$row_payment['type'] && $disabled==""){
				echo "<input type=\"radio\" $disabled name=\"payment_data_type\" value=\"".$row['id']."\" checked>".$type_name."<br>\n";
			} elseif (!$i && !$paid && $disabled==""){
				echo "<input type=\"radio\" $disabled name=\"payment_data_type\" value=\"".$row['id']."\" checked>".$type_name."<br>\n";
			} else {
				echo "<input type=\"radio\" $disabled name=\"payment_data_type\" value=\"".$row['id']."\">".$type_name."<br>\n";
			}
			$i++;
		}

	}

	?>
<select name="payment_data_account_id" <?php echo $disabled; ?>>
	<?php
	$table='#prefix#account_accounts';
	$res = common_query ("SELECT * FROM $table ORDER BY `name`",__FILE__,__LINE__);

	$i=0;
	while($row=mysql_fetch_array ($res)) {
		if($editing && $row['id']==$row_payment['account_id'] && $disabled==""){
			//echo "<input type=\"radio\" $disabled name=\"payment_data_account_id\" value=\"".$row['id']."\" checked>".$row['bank']."/".$row['number']." - ".$row['name']."<br>\n";
			echo "<option value=\"".$row['id']."\" selected>".$row['bank']."/".$row['number']." - ".$row['name']."</option><br>\n";
		} elseif (!$i && !$paid && $disabled==""){
			//echo "<input type=\"radio\" $disabled name=\"payment_data_account_id\" value=\"".$row['id']."\" checked>".$row['bank']."/".$row['number']." - ".$row['name']."<br>\n";
			echo "<option value=\"".$row['id']."\" selected>".$row['bank']."/".$row['number']." - ".$row['name']."</option><br>\n";
		} else {
			//echo "<input type=\"radio\" $disabled name=\"payment_data_account_id\" value=\"".$row['id']."\">".$row['bank']."/".$row['number']." - ".$row['name']."<br>\n";
			echo "<option value=\"".$row['id']."\">".$row['bank']."/".$row['number']." - ".$row['name']."</option><br>\n";
		}
		$i++;
	}
?>
</select>
<?php
	echo "</td></tr>\n";
	echo "</table>\n";
	echo "</FIELDSET>\n";
	echo "</td><td></td></tr></table>\n";
}

function display_form_pos($id){
	if($id) {
		$editing=1;
		$table='#prefix#account_mgmt_main';
		$res=common_query("SELECT * FROM $table WHERE `id`='$id'",__FILE__,__LINE__);
		$row=mysql_fetch_array($res);

		echo "<input type=\"hidden\" name=\"data[type]\" value=\"".$row['type']."\">\n";
		echo "<input type=\"hidden\" name=\"data[account_movement]\" value=\"".$row['account_movement']."\">\n";

	} else {
		$editing=0;
	}

	echo "<table><tr><td>\n";
	echo "
	<FIELDSET>
	<LEGEND>".ucfirst(GLOBALMSG_RECORD_POS)."</LEGEND>
	";
	echo "<table>\n";

	input_standard($id,$editing);

	echo "<tr><td>".ucfirst(phr('TAXABLE'))."</td>\n";
	echo "<td>\n";
	echo "<input type=\"text\" name=\"data[bank_taxable_amount]\" value=\"".$row['bank_taxable_amount']."\">";
	echo "</td></tr>\n";

	echo "<tr><td>".ucfirst(phr('TAX'))."</td>\n";
	echo "<td>\n";
	echo "<input type=\"text\" name=\"data[bank_vat_amount]\" value=\"".$row['bank_vat_amount']."\">";
	echo "</td></tr>\n";
?>
	<tr><td><?php echo ucfirst(phr('BANK_ACCOUNT')); ?></td>
	<td>
<?php
	account_select_plugin($row['account_id']);
?>
	</td>
	</tr>
<?php
	echo "</table>\n";
	echo "</FIELDSET>\n";
	echo "</td><td></td></tr></table>\n";

}

function display_form_check($id){
	if($id) {
		$editing=1;
		$table='#prefix#account_mgmt_main';
		$res=common_query("SELECT * FROM $table WHERE `id`='$id'",__FILE__,__LINE__);

		$row=mysql_fetch_array($res);

		echo "<input type=\"hidden\" name=\"data[type]\" value=\"".$row['type']."\">\n";
		echo "<input type=\"hidden\" name=\"data[account_movement]\" value=\"".$row['account_movement']."\">\n";
	} else {
		$editing=0;
	}

	echo "<table><tr><td>\n";
	echo "
	<FIELDSET>
	<LEGEND>".ucfirst(GLOBALMSG_RECORD_CHEQUE)."</LEGEND>
	";
	echo "<table>\n";

	$row['bank_taxable_amount']=$row['bank_taxable_amount']*(-1);
	$row['bank_vat_amount']=$row['bank_vat_amount']*(-1);

	input_standard($id,$editing);

	echo "<tr><td>".ucfirst(phr('TAXABLE'))."</td>\n";
	echo "<td>\n";
	echo "<input type=\"text\" name=\"data[bank_taxable_amount]\" value=\"".abs($row['bank_taxable_amount'])."\">";
	echo "</td></tr>\n";

	echo "<tr><td>".ucfirst(phr('TAX'))."</td>\n";
	echo "<td>\n";
	echo "<input type=\"text\" name=\"data[bank_vat_amount]\" value=\"".abs($row['bank_vat_amount'])."\">";
	echo "</td></tr>\n";

	echo "<tr><td>".ucfirst(phr('CHEQUE_NUMBER'))."</td>\n";
	echo "<td>\n";
	echo "<input type=\"text\" name=\"data[number]\" value=\"".$row['number']."\">";
	echo "</td></tr>\n";

	echo '<tr><td>'.ucfirst(phr('BANK_ACCOUNT')).'</td>'."\n";
	echo '<td>'."\n";

 	account_select_plugin($row['account_id']);
?>
	</td>
	</tr>
<?php
	echo "</table>\n";
	echo "</FIELDSET>\n";
	echo "</td><td></td></tr></table>\n";

}

function display_form_bonifico($id){
	if($id) {
		$editing=1;
		$table='#prefix#account_mgmt_main';
		$res=common_query("SELECT * FROM $table WHERE `id`='$id'",__FILE__,__LINE__);

		$row=mysql_fetch_array($res);

		echo "<input type=\"hidden\" name=\"data[type]\" value=\"".$row['type']."\">\n";
		echo "<input type=\"hidden\" name=\"data[account_movement]\" value=\"".$row['account_movement']."\">\n";
	} else {
		$editing=0;
	}

	echo "<table><tr><td>\n";
	echo "
	<FIELDSET>
	<LEGEND>".ucfirst(GLOBALMSG_RECORD_WIRE_TRANSFER)."</LEGEND>
	";
	echo "<table>\n";

	$row['bank_taxable_amount']=$row['bank_taxable_amount']*(-1);
	$row['bank_vat_amount']=$row['bank_vat_amount']*(-1);

	input_standard($id,$editing);

	echo "<tr><td>".ucfirst(phr('TAXABLE'))."</td>\n";
	echo "<td>\n";
	echo "<input type=\"text\" name=\"data[bank_taxable_amount]\" value=\"".abs($row['bank_taxable_amount'])."\">";
	echo "</td></tr>\n";

	echo "<tr><td>".ucfirst(phr('TAX'))."</td>\n";
	echo "<td>\n";
	echo "<input type=\"text\" name=\"data[bank_vat_amount]\" value=\"".abs($row['bank_vat_amount'])."\">";
	echo "</td></tr>\n";
?>
	<tr><td><?php echo ucfirst(phr('BANK_ACCOUNT')); ?></td>
	<td>
<?php
	account_select_plugin($row['account_id']);
?>
	</td>
	</tr>
<?php
	echo "</table>\n";
	echo "</FIELDSET>\n";
	echo "</td><td></td></tr></table>\n";
}

function display_form_scontrino($id){
	if($id) {
		$editing=1;
		$table='#prefix#account_mgmt_main';
		$res=common_query("SELECT * FROM $table WHERE `id`='$id'",__FILE__,__LINE__);
		$row=mysql_fetch_array($res);

		echo "<input type=\"hidden\" name=\"data[type]\" value=\"".$row['type']."\">\n";
	} else {
		$editing=0;
	}

	echo "<table><tr><td>\n";
	echo "
	<FIELDSET>
	<LEGEND>".ucfirst(GLOBALMSG_RECORD_BILL)."</LEGEND>
	";
	echo "<table>\n";

	input_standard($id,$editing);

	echo "<tr><td>".ucfirst(phr('TAXABLE'))."</td>\n";
	echo "<td>\n";
	echo "<input type=\"text\" name=\"data[cash_taxable_amount]\" value=\"".abs($row['cash_taxable_amount'])."\">";
	echo "</td></tr>\n";

	echo "<tr><td>".ucfirst(phr('TAX'))."</td>\n";
	echo "<td>\n";
	echo "<input type=\"text\" name=\"data[cash_vat_amount]\" value=\"".abs($row['cash_vat_amount'])."\">";
	echo "</td></tr>\n";

	echo "</table>\n";
	echo "</FIELDSET>\n";
	echo "</td><td></td></tr></table>\n";
}

function display_form_versamento($id){
	if($id) {
		$editing=1;
		$table='#prefix#account_mgmt_main';
		$res=common_query($_SESSION['common_db'],"SELECT * FROM $table WHERE `id`='$id'",__FILE__,__LINE__);

		$row=mysql_fetch_array($res);

		echo "<input type=\"hidden\" name=\"data[type]\" value=\"".$row['type']."\">\n";
		echo "<input type=\"hidden\" name=\"data[account_movement]\" value=\"".$row['account_movement']."\">\n";
	} else {
		$editing=0;
	}

	echo "<table><tr><td>\n";
	echo "
	<FIELDSET>
	<LEGEND>".ucfirst(GLOBALMSG_RECORD_DEPOSIT)."</LEGEND>
	";
	echo "<table>\n";

	input_standard($id,$editing);

	echo "<tr><td>".ucfirst(phr('TAXABLE'))."</td>\n";
	echo "<td>\n";
	echo "<input type=\"text\" name=\"data[bank_taxable_amount]\" value=\"".abs($row['bank_taxable_amount'])."\">";
	echo "</td></tr>\n";

	echo "<tr><td>".ucfirst(phr('TAX'))."</td>\n";
	echo "<td>\n";
	echo "<input type=\"text\" name=\"data[bank_vat_amount]\" value=\"".abs($row['bank_vat_amount'])."\">";
	echo "</td></tr>\n";

?>
	<tr><td><?php echo ucfirst(phr('BANK_ACCOUNT')); ?></td>
	<td>
<?php
	account_select_plugin($row['account_id']);
?>
	</td>
	</tr>
<?php
	echo "</table>\n";
	echo "</FIELDSET>\n";
	echo "</td><td></td></tr></table>\n";
}

function display_form_ricevuta($id){
	if($id) {
		$editing=1;
		$table='#prefix#account_mgmt_main';
		$res=common_query($_SESSION['common_db'],"SELECT * FROM $table WHERE `id`='$id'",__FILE__,__LINE__);

		$row=mysql_fetch_array($res);

		echo "<input type=\"hidden\" name=\"data[type]\" value=\"".$row['type']."\">\n";
	} else {
		$editing=0;
	}

	echo "<table><tr><td>\n";
	echo "
	<FIELDSET>
	<LEGEND>".ucfirst(GLOBALMSG_RECORD_RECEIPT)."</LEGEND>
	";
	echo "<table>\n";

	input_standard($id,$editing);

	echo "<tr><td>".ucfirst(phr('TAXABLE'))."</td>\n";
	echo "<td>\n";
	echo "<input type=\"text\" name=\"data[cash_taxable_amount]\" value=\"".abs($row['cash_taxable_amount'])."\">";
	echo "</td></tr>\n";

	echo "<tr><td>".ucfirst(phr('TAX'))."</td>\n";
	echo "<td>\n";
	echo "<input type=\"text\" name=\"data[cash_vat_amount]\" value=\"".abs($row['cash_vat_amount'])."\">";
	echo "</td></tr>\n";

	echo "</table>\n";
	echo "</FIELDSET>\n";
	echo "</td><td></td></tr></table>\n";
}

function display_form($id,$insert_type=0){
	if($id){
		$table='#prefix#account_mgmt_main';
		$res=common_query("SELECT * FROM $table WHERE `id`='$id'",__FILE__,__LINE__);
		$row=mysql_fetch_array($res);
		$insert_type=$row['type'];
	}

	$table=$GLOBALS['table_prefix'].'mgmt_types';
	$res=mysql_db_query($_SESSION['common_db'],"SELECT * FROM $table WHERE `id`='$insert_type'");
	$row=mysql_fetch_array($res);
	$insert_type=strtolower($row['name']);

	switch($insert_type){
		case "pos":
			display_form_pos($id);
			break;
		case "assegno":
			display_form_check($id);
			break;
		case "bonifico":
			display_form_bonifico($id);
			break;
		case "scontrino": display_form_scontrino($id); break;
		case "versamento": display_form_versamento($id); break;
		case "ricevuta": display_form_ricevuta($id); break;
		case "fattura": display_form_invoice($id); break;
		default: echo "ERROR"; return 1; break;
	}
}

function delete_rows($delete){
	require("./mgmt_start.php");

	$firstline=1;
	$counter=0;

	if(!is_array($delete)){
		echo GLOBALMSG_RECORD_DELETE_NONE.".<br>\n";
		return 1;
	}
	$table='#prefix#account_mgmt_main';
	$query="DELETE FROM $table WHERE ";
	for (reset ($delete); list ($key, $value) = each ($delete); ) {
		if($firstline) {
			$query.="`id`='".$key."'";
			$firstline=0;
		} else {
			$query.=" OR `id`='".$key."'";
		}
		$table='#prefix#account_mgmt_main';
		$res=common_query("SELECT * FROM $table WHERE `id`='$key'",__FILE__,__LINE__);

		$row=mysql_fetch_array($res);
		$description[$key]=$row['description'];
		$counter++;

		movement_invoice_delete($key);
		invoice_payment_delete($key);

		$table='#prefix#account_mgmt_main';
		$query_local="SELECT * FROM $table WHERE `id`='$key'";
		$res_local = common_query ($query_local,__FILE__,__LINE__);

		$arr_local=mysql_fetch_array($res_local);
		if(get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'mgmt_types','log_to_bank',$arr_local['type'])){
			$movement_id=account_movement_delete($arr_local['account_movement']);
		}
	}

	$res = common_query ($query,__FILE__,__LINE__);

	$num_affected=mysql_affected_rows();

	if ($num_affected==$counter && $counter>1) {
		echo("<META HTTP-EQUIV=\"Refresh\" CONTENT=\"".get_conf(__FILE__,__LINE__,"refresh_time_management")."; URL=index.php\">");
		$msg = GLOBALMSG_RECORD_THE_MANY." <b>";
		for (reset ($delete); list ($key, $value) = each ($delete); ) {
			$msg.=$description[$key];
			$msg.=", ";
		}
		$msg = substr($msg,0,strlen($msg)-2);

		$msg.="</b> ".GLOBALMSG_RECORD_DELETE_OK_MANY.". <br>\n";
	} elseif ($num_affected==1 && $counter==1) {
		echo("<META HTTP-EQUIV=\"Refresh\" CONTENT=\"".get_conf(__FILE__,__LINE__,"refresh_time_management")."; URL=index.php\">");
		$msg = GLOBALMSG_RECORD_THE." <b>";
		for (reset ($delete); list ($key, $value) = each ($delete); ) {
			$msg.=$description[$key];
			$msg.=", ";
		}
		$msg = substr($msg,0,strlen($msg)-2);

		$msg.="</b> ".GLOBALMSG_RECORD_DELETE_OK_MANY.". <br>\n";

	} elseif(mysql_errno()) {
		echo ucfirst(phr('ERROR')).".<br>\n";
		return 1;
	} else {
		echo GLOBALMSG_RECORD_DELETE_NONE.".<br>\n";
	}
	echo $msg;
	return 0;
}

function invoice_payment_delete($invoice_id){

	$table='#prefix#account_mgmt_main';
	$query="SELECT * FROM $table WHERE `associated_invoice`='$invoice_id'";
	$res = common_query ($query,__FILE__,__LINE__);

	$arr=mysql_fetch_array($res);
	if(get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'mgmt_types','log_to_bank',$arr['type'])){


		$movement_id=account_movement_delete($arr['account_movement']);
	}

	$table='#prefix#account_mgmt_main';
	$query="DELETE FROM $table WHERE `associated_invoice`='$invoice_id'";
	$res = common_query ($query,__FILE__,__LINE__);

	return 0;
}


function type_specific_variation($data){
	$table=$GLOBALS['table_prefix'].'mgmt_types';
	$res=mysql_db_query($_SESSION['common_db'],"SELECT * FROM $table WHERE `id`='".$data['type']."'");
	$row=mysql_fetch_array($res);
	mysql_free_result($res);
	$insert_type=strtolower($row['name']);
	switch($insert_type){
		case "fattura":	
						$data['debit']='1';
						$data['debit_taxable_amount']=$data['operation']*abs($data['debit_taxable_amount']);
						$data['debit_vat_amount']=$data['operation']*abs($data['debit_vat_amount']);
						break;
		case "scontrino":	$data['cash_taxable_amount']=$data['operation']*abs($data['cash_taxable_amount']);
							$data['cash_vat_amount']=$data['operation']*abs($data['cash_vat_amount']);
							$data['debit']='0';
							break;
		case "ricevuta":	$data['cash_taxable_amount']=$data['operation']*abs($data['cash_taxable_amount']);
							$data['cash_vat_amount']=$data['operation']*abs($data['cash_vat_amount']);
							$data['debit']='0';
							break;
		case "pos":	$data['cash_taxable_amount']=-1*abs($data['bank_taxable_amount']);
					$data['cash_vat_amount']=-1*abs($data['bank_vat_amount']);
					$data['debit']='0';
					break;
		case "versamento":	$data['cash_taxable_amount']=-1*abs($data['bank_taxable_amount']);
							$data['cash_vat_amount']=-1*abs($data['bank_vat_amount']);
							$data['debit']='0';
							break;
		case "assegno":		$data['bank_taxable_amount']=$data['operation']*abs($data['bank_taxable_amount']);
							$data['bank_vat_amount']=$data['operation']*abs($data['bank_vat_amount']);
							$data['debit']=0;
							break;
		case "bonifico":	$data['bank_taxable_amount']=$data['operation']*abs($data['bank_taxable_amount']);
							$data['bank_vat_amount']=$data['operation']*abs($data['bank_vat_amount']);
							$data['debit']='0';
							break;
	}
	unset($data['operation']);
	return $data;
}

function insert_data($input_data,$payment_data=0) {
	require("./mgmt_start.php");


	if($err=check_compulsory_fields($input_data)){
		switch($err){
			case 1: $msg = ucfirst(phr('CHECK_DATE')); break;
			case 2: $msg = ucfirst(phr('CHECK_TAXABLE')); break;
			case 3: $msg = ucfirst(phr('CHECK_DESCRIPTION')); break;
			case 4: $msg = ucfirst(phr('CHECK_NO_TYPE_ERROR'))." ".get_conf(__FILE__,__LINE__,"vendor_name"); break;
		}
		echo "<script language=\"javascript\">
			window.alert(\"".$msg."\");
			history.go(-1);
		</script>\n";
		return 1;
	}

	$input_data=type_specific_variation($input_data);

	$table=$GLOBALS['table_prefix'].'mgmt_types';
	$res_local=mysql_db_query($_SESSION['common_db'],"SELECT * FROM $table WHERE `id`='".$input_data['type']."'");
	$row_local=mysql_fetch_array($res_local);
	$type=strtolower($row_local['name']);

	if($err=check_date($input_data)) {
		switch($err){
			case 1:  $msg = ucfirst(phr('CHECK_DAY')); break;
			case 2:  $msg = ucfirst(phr('CHECK_MONTH')); break;
			case 3:  $msg = ucfirst(phr('CHECK_YEAR')); break;
			case 4: $msg = ucfirst(phr('CHECK_DATE')); break;
		}
		//echo("<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; URL=javascript:window.close();\">");
		echo "<script language=\"javascript\">
			window.alert(\"".$msg."\");
			history.go(-1);
		</script>\n";
		return 2;
	}

	$input_data=format_date($input_data);

	$input_data=format_currency($input_data);

	$input_data=calculate_amount($input_data);


	// Now we'll build the correct INSERT query, based on the fields provided
	$table='#prefix#account_mgmt_main';
	$query="INSERT INTO $table (";
	for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
		$query.="`".$key."`,";
	}
	// strips the last comma that has been put
	$query = substr ($query, 0, strlen($query)-1);
	$query.=") VALUES (";
	for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
		$query.="'".$value."',";
	}
	// strips the last comma that has been put
	$query = substr ($query, 0, strlen($query)-1);	$query.=")";

	$res = common_query ($query,__FILE__,__LINE__);

	if($errno=mysql_errno()) {
		$msg="Error - ";
		$msg.='mysql: '.mysql_errno().' '.mysql_error();
		echo $msg,"<br>\n";
		error_msg(__FILE__,__LINE__,$msg);
		return 1;
	}
	$num_affected=mysql_affected_rows();
	$inserted_id = mysql_insert_id();

	if(get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'mgmt_types','log_to_bank',$input_data['type'])){
		$movement_id=account_movement_from_manage($inserted_id);
		if($movement_id) {
			$table='#prefix#account_mgmt_main';
			$query2="UPDATE $table SET `account_movement`='$movement_id'";
			$query2.=" WHERE `id`='$inserted_id'";
			$res2 = common_query ($query2,__FILE__,__LINE__);
			$res=common_query($query,__FILE__,__LINE__);
			$num_affected2=mysql_affected_rows();
			if ($num_affected2!=1) {
				$error=true;
			}
		} else {
			$error=true;
		}
	}

	if ($num_affected==1) {
		if($type!="fattura" && !$input_data['associated_invoice'])
			echo("<META HTTP-EQUIV=\"Refresh\" CONTENT=\"".get_conf(__FILE__,__LINE__,"refresh_time_management")."; URL=index.php\">");
		echo GLOBALMSG_RECORD_THE." <b>".$input_data['description']."</b> ".GLOBALMSG_RECORD_ADD_OK.". <br>\n";

		if($type=="fattura" && $input_data['paid']) {

			$invoice_data=$input_data;
			unset($input_data);
			$input_data['date']=$invoice_data['date'];
			$input_data['debit_amount']=0;
			$input_data['debit_taxable_amount']=0;
			$input_data['debit_vat_amount']=0;

			$table='#prefix#account_mgmt_main';
			$query="UPDATE $table SET ";
			for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
				$query.="`".$key."`='".$value."',";
			}
			// strips the last comma that has been put
			$query = substr ($query, 0, strlen($query)-1);
			$query.=" WHERE `id`='$inserted_id'";

			$res = common_query ($query,__FILE__,__LINE__);


			if($payment_data["type"]==1 || $payment_data["type"]==2) {
				$payment_data['bank_taxable_amount']=-1*$invoice_data['debit_taxable_amount'];
				$payment_data['bank_vat_amount']=-1*$invoice_data['debit_vat_amount'];
			} elseif($payment_data["type"]==4) {
				$payment_data['cash_taxable_amount']=-1*$invoice_data['debit_taxable_amount'];
				$payment_data['cash_vat_amount']=-1*$invoice_data['debit_vat_amount'];
			}

			if($invoice_data['debit_amount']<=0) $payment_data['operation']=-1;
			elseif($invoice_data['debit_amount']>0) $payment_data['operation']=1;
			$payment_data['description']=$invoice_data['description']." - Pag.";
			$payment_data['who']=$invoice_data['who'];
			$payment_data['associated_invoice']=$inserted_id;

			insert_data($payment_data);

		}

		if($type=="fattura") {
			//mizuko : changed get to post
			//10.05.2007
			echo "<form action=\"stock.php\" method=\"get\">\n";
			echo "<input type=\"hidden\" name=\"command\" value=\"edit\">\n";
			echo "<input type=\"hidden\" name=\"data[invoice_id]\" value=\"$inserted_id\">\n";
			$err=form_stock_edit($inserted_id);
			echo "<input type=\"submit\" value=\"".ucphr('SEND_TO_STOCK')."\">\n";
			echo "</form>\n";
		}
	}elseif(mysql_errno()) {
		echo ucfirst(phr('ERROR'))."<br>\n";
		echo "Error: mysql ".mysql_errno().": ".mysql_error().".<br>\n";
	} else {
		echo GLOBALMSG_RECORD_ADD_NONE.".<br>\n";
	}
}

function update_data($input_id,$input_data,$payment_data=0) {
	require("./mgmt_start.php");

	if($err=check_compulsory_fields($input_data)){
		switch($err){
			case 1: $msg = ucfirst(phr('CHECK_DATE')); break;
			case 2: $msg = ucfirst(phr('CHECK_TAXABLE')); break;
			case 3: $msg = ucfirst(phr('CHECK_DESCRIPTION')); break;
			case 4: $msg = ucfirst(phr('CHECK_NO_TYPE_ERROR'))." ".get_conf(__FILE__,__LINE__,"vendor_name"); break;
		}
		echo "<script language=\"javascript\">
			window.alert(\"".$msg."\");
			history.go(-1);
		</script>\n";
		return 1;
	}

	if(!$input_data['associated_invoice'])
		$input_data=type_specific_variation($input_data);

	unset($input_data['operation']);

	if($err=check_date($input_data)) {
		switch($err){
			case 1: $msg = ucfirst(phr('CHECK_DAY')); break;
			case 2: $msg = ucfirst(phr('CHECK_MONTH')); break;
			case 3: $msg = ucfirst(phr('CHECK_YEAR')); break;
			case 4: $msg = ucfirst(phr('CHECK_DATE')); break;
		}
		echo "<script language=\"javascript\">
			window.alert(\"".$msg."\");
			history.go(-1);
		</script>\n";
		return 2;
	}

	$input_data=format_date($input_data);
	$input_data=format_checkbox($input_data);

	$input_data=format_currency($input_data);

	$input_data=calculate_amount($input_data);

	$old_type=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'account_mgmt_main','type',$input_id);
	$old_log_to_bank=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'mgmt_types','log_to_bank',$old_type);
	$old_account_movement=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'account_mgmt_main','account_movement',$input_id);

	$table=$GLOBALS['table_prefix'].'mgmt_types';
	$res_local=mysql_db_query($_SESSION['common_db'],"SELECT * FROM $table WHERE `id`='".$input_data['type']."'");
	$row_local=mysql_fetch_array($res_local);
	$type=strtolower($row_local['name']);

	// Now we'll build the correct INSERT query, based on the fields provided
	$table=$GLOBALS['table_prefix'].'account_mgmt_main';
	$query="UPDATE $table SET ";
	for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
		$query.="`".$key."`='".$value."',";
	}
	// strips the last comma that has been put
	$query = substr ($query, 0, strlen($query)-1);
	$query.=" WHERE `id`='$input_id'";

	$res = mysql_db_query ($_SESSION['common_db'],$query);
	$num_affected=mysql_affected_rows();


	$new_log_to_bank=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'mgmt_types','log_to_bank',$input_data['type']);

	if($old_log_to_bank && !$new_log_to_bank){
		$movement_id=account_movement_delete($old_account_movement);
	} elseif($new_log_to_bank){
		$movement_id=account_movement_from_manage($input_id);
		if($movement_id) {
			$table=$GLOBALS['table_prefix'].'account_mgmt_main';
			$query2="UPDATE $table SET `account_movement`='$movement_id'";
			$query2.=" WHERE `id`='$input_id'";
			$res2 = mysql_db_query ($_SESSION['common_db'],$query2);
			$num_affected2=mysql_affected_rows();
			if ($num_affected2!=1) {
				$error=true;
			}
		} else {
			$error=true;
		}
	}


	if ($num_affected==1) {
		if($type!="fattura" && !isset($input_data['associated_invoice']))
			echo("<META HTTP-EQUIV=\"Refresh\" CONTENT=\"".get_conf(__FILE__,__LINE__,"refresh_time_management")."; URL=index.php\">");

		echo ucfirst(GLOBALMSG_RECORD_THE)." <b>".$input_data['description']."</b> ".GLOBALMSG_RECORD_EDIT_OK.". <br>\n";
	}elseif(mysql_errno()) {
		echo ucfirst(phr('ERROR'))."<br>\n";
	} else {
		echo ucfirst(GLOBALMSG_RECORD_THE)." <b>".$input_data['description']."</b> ".GLOBALMSG_RECORD_EDIT_NOT_DONE.".<br>\n";
	}

	if($type=="fattura" && $input_data['paid']) {

		$invoice_data=$input_data;
		unset($input_data);
		$data['date']=$invoice_data['date'];
		$data['debit_amount']=0;
		$data['debit_taxable_amount']=0;
		$data['debit_vat_amount']=0;

		$table=$GLOBALS['table_prefix'].'account_mgmt_main';
		$query="UPDATE $table SET ";
		for (reset ($data); list ($key, $value) = each ($data); ) {
			$query.="`".$key."`='".$value."',";
		}
		// strips the last comma that has been put
		$query = substr ($query, 0, strlen($query)-1);
		$query.=" WHERE `id`='$input_id'";
		$res = mysql_db_query ($_SESSION['common_db'],$query);

		if($payment_data["type"]==1) {
			$payment_data['bank_taxable_amount']=$invoice_data['debit_taxable_amount'];
			$payment_data['bank_vat_amount']=$invoice_data['debit_vat_amount'];
			$payment_data['cash_taxable_amount']=0;
			$payment_data['cash_vat_amount']=0;
		} elseif($payment_data["type"]==2) {
			$payment_data['bank_taxable_amount']=$invoice_data['debit_taxable_amount'];
			$payment_data['bank_vat_amount']=$invoice_data['debit_vat_amount'];
			$payment_data['cash_taxable_amount']=0;
			$payment_data['cash_vat_amount']=0;
		} elseif($payment_data["type"]==4) {
			$payment_data['cash_taxable_amount']=$invoice_data['debit_taxable_amount'];
			$payment_data['cash_vat_amount']=$invoice_data['debit_vat_amount'];
			$payment_data['bank_taxable_amount']=0;
			$payment_data['bank_vat_amount']=0;
		}

		if($invoice_data['debit_amount']<=0) $payment_data['operation']=-1;
		elseif($invoice_data['debit_amount']>0) $payment_data['operation']=1;
		$payment_data['description']=$invoice_data['description']." - Pag.";
		$payment_data['who']=$invoice_data['who'];
		$payment_data['associated_invoice']=$input_id;


		$table=$GLOBALS['table_prefix'].'account_mgmt_main';
		$query="SELECT * FROM $table WHERE `associated_invoice`='$input_id'";
		$res = mysql_db_query ($_SESSION['common_db'],$query);
		if(mysql_num_rows($res)==1){
			$row=mysql_fetch_array($res);
			$payment_id=$row["id"];

			update_data($payment_id,$payment_data);
		} else {
			insert_data($payment_data);
		}
	} elseif($type=="fattura" && !$input_data['paid']) {

		$table=$GLOBALS['table_prefix'].'account_mgmt_main';
		$query="SELECT * FROM $table WHERE `associated_invoice`='$input_id'";
		$res = mysql_db_query ($_SESSION['common_db'],$query);
		if(mysql_num_rows($res)==1){
			$row_payment=mysql_fetch_array($res);

			$invoice_data=$input_data;
			unset($input_data);
			$data['date']=$invoice_data['date'];
			if($row_payment['type']=="1" || $row_payment['type']=="2") {
				$data['debit_amount']=$row_payment['bank_amount'];
				$data['debit_taxable_amount']=$row_payment['bank_taxable_amount'];
				$data['debit_vat_amount']=$row_payment['bank_vat_amount'];
			} elseif($row_payment['type']=="4") {
				$data['debit_amount']=$row_payment['cash_amount'];
				$data['debit_taxable_amount']=$row_payment['cash_taxable_amount'];
				$data['debit_vat_amount']=$row_payment['cash_vat_amount'];
			}

			$table=$GLOBALS['table_prefix'].'account_mgmt_main';
			$query="UPDATE $table SET ";
			for (reset ($data); list ($key, $value) = each ($data); ) {
				$query.="`".$key."`='".$value."',";
			}
			// strips the last comma that has been put
			$query = substr ($query, 0, strlen($query)-1);
			$query.=" WHERE `id`='$input_id'";
			$res = mysql_db_query ($_SESSION['common_db'],$query);

			$payment_id=$row_payment['id'];

			$table=$GLOBALS['table_prefix'].'account_mgmt_main';
			$query="SELECT * FROM $table WHERE `associated_invoice`='$input_id'";
			$res = mysql_db_query ($_SESSION['common_db'],$query);
			$arr=mysql_fetch_array($res);
			if(get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'mgmt_types','log_to_bank',$arr['type'])){
				$movement_id=account_movement_delete($arr['account_movement']);
			}

			$table=$GLOBALS['table_prefix'].'account_mgmt_main';
			$query="DELETE FROM $table WHERE `id`='$payment_id'";
			$res = mysql_db_query ($_SESSION['common_db'],$query);
		}
	}


	if($type=="fattura") {
		echo "<form action=\"stock.php\" method=\"get\">\n";
		echo "<input type=\"hidden\" name=\"command\" value=\"edit\">\n";
		echo "<input type=\"hidden\" name=\"data['invoice_id']\" value=\"$input_id\">\n";
		$err=form_stock_edit($input_id);
		if (!$err)
			echo "<input type=\"submit\" value=\"".ucphr('SEND_TO_STOCK')."\">\n";

		echo "</form>\n";
	}
}
/**
 * 
 *	This function creates the query to be used later in the table creation
 *
 *	Possible query types are:
 *	1. Supplier id given
 *	2. Supplier name given
 *	3. Only shows a person type (given)
 *	4. Only shows type (given)
 *
 * @param unknown_type $orderby
 * @param unknown_type $commandto
 * @param unknown_type $query_type
 * @param unknown_type $query_value
 * @return unknown
 */
function table_general($orderby="date",$commandto,$query_type=0,$query_value=0) {


	require("./mgmt_start.php");
	if($orderby=="") $orderby="date";

	$append_income_totals=false;
	$table=$GLOBALS['table_prefix'].'account_mgmt_main';
	$query="SELECT $table.* FROM $table";
	switch($query_type){
	case 1:
		$table=$GLOBALS['table_prefix'].'account_mgmt_addressbook';
		$res=mysql_db_query($_SESSION['common_db'],"SELECT * FROM $table WHERE `id`='$query_value'");
		$row=mysql_fetch_array($res);
		$supplier_name=$row['name'];
		$query.= " WHERE `who`='".$supplier_name."'";
		$query.=" AND";
		$query.= " `date` >= ".$_SESSION['timestamp']['start']." AND `date` <= ".$_SESSION['timestamp']['end']."";
		$query.= " ORDER BY `$orderby`";

		$page="supply.php?command=showknownsupplier&id=$query_value&";
		$table_title= GLOBALMSG_RECORD_TITLE_FOR." $supplier_name";
		$table_title.= " ".ucfirst(phr('FROM_TIME'))." ".$_SESSION['time']['start']." ".phr('OF_DAY')." ".$_SESSION['date']['start']." ".phr('TO_TIME')." ".$_SESSION['time']['end']." ".phr('OF_DAY')." ".$_SESSION['date']['end']."";
		break;
	case 2:
		$page="supply.php?command=show&name=$query_value&";
		$query.= " WHERE `who`='".$query_value."'";
		$query.=" AND";
		$query.= " `date` >= ".$_SESSION['timestamp']['start']." AND `date` <= ".$_SESSION['timestamp']['end']."";
		$query.= " ORDER BY `$orderby`";

		$table_title=GLOBALMSG_RECORD_TITLE_FOR_NOT_IN_ADDRESSBOOK;
		$table_title.=" ".ucfirst(phr('FROM_TIME'))." ".$_SESSION['time']['start']." ".phr('OF_DAY')." ".$_SESSION['date']['start']." ".phr('TO_TIME')." ".$_SESSION['time']['end']." ".phr('OF_DAY')." ".$_SESSION['date']['end']."";
		break;
	case 3:
		$people_type = new mgmt_people_type($query_value);
		$type_name=$people_type -> name($_SESSION['language']);
		unset($people_type);

		$page="supply.php?supplier_type=$query_value&";
		// the following syntax would be good, but mysql doesn't support it yet
		// so we use the following one
		$table2=$GLOBALS['table_prefix'].'account_mgmt_addressbook';
		$query.= " JOIN $table2 WHERE $table.who=$table2.name AND $table2.type='".$query_value."'";
		$query.=" AND";
		$query.= " `date` >= ".$_SESSION['timestamp']['start']." AND `date` <= ".$_SESSION['timestamp']['end']."";
		$query.= " ORDER BY `$orderby`";

		$table_title = GLOBALMSG_RECORD_TITLE_FOR_TYPE." $type_name";
		$table_title.=" ".ucfirst(phr('FROM_TIME'))." ".$_SESSION['time']['start']." ".phr('OF_DAY')." ".$_SESSION['date']['start']." ".phr('TO_TIME')." ".$_SESSION['time']['end']." ".phr('OF_DAY')." ".$_SESSION['date']['end']."";
		break;
	case 4:
		$mgmt_type = new mgmt_type($query_value);
		$type_name=$mgmt_type -> name($_SESSION['language']);
		unset($mgmt_type);
		//$type_name=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'mgmt_types','name',$query_value);
		$page="db.php?show_only=$query_value&";
		// the following syntax would be good, but mysql doesn't support it yet
		// so we use the following one
		// $query.= " WHERE `who` IN (SELECT `name` FROM `account_mgmt_addressbook` WHERE `type`='".$query_value."')";
		$query.= " WHERE `type`='".$query_value."' AND `waiter_income` = '1'";
		$query.=" AND";
		$query.= " `date` >= ".$_SESSION['timestamp']['start']." AND `date` <= ".$_SESSION['timestamp']['end']."";
		$query.= " ORDER BY `$orderby`";

		$table_title=GLOBALMSG_RECORD_TITLE_INCOME_TYPE." $type_name";
		$table_title.=" ".ucfirst(phr('FROM_TIME'))." ".$_SESSION['time']['start']." ".phr('OF_DAY')." ".$_SESSION['date']['start']." ".phr('TO_TIME')." ".$_SESSION['time']['end']." ".phr('OF_DAY')." ".$_SESSION['date']['end']."";
		break;
	case 5:
		$append_income_totals=true;
		$mgmt_type = new mgmt_type($query_value);
		$type_name=$mgmt_type -> name($_SESSION['language']);
		unset($mgmt_type);
		$page="db.php?";
		// the following syntax would be good, but mysql doesn't support it yet
		// so we use the following one
		$query.= " WHERE (`type`<>'3' AND `waiter_income` <> '1' AND `date` >= ".$_SESSION['timestamp']['start']." AND `date` <= ".$_SESSION['timestamp']['end'].")
		OR (`type`<>'4' AND `waiter_income` <> '1' AND `date` >= ".$_SESSION['timestamp']['start']." AND `date` <= ".$_SESSION['timestamp']['end'].")
		OR (`type`<>'5' AND `waiter_income` <> '1' AND `date` >= ".$_SESSION['timestamp']['start']." AND `date` <= ".$_SESSION['timestamp']['end'].")";
		$query.= " ORDER BY `$orderby`";

		$table_title=GLOBALMSG_RECORD_TITLE_INCOME;
		$table_title.=" ".ucfirst(phr('FROM_TIME'))." ".$_SESSION['time']['start']." ".phr('OF_DAY')." ".$_SESSION['date']['start']." ".phr('TO_TIME')." ".$_SESSION['time']['end']." ".phr('OF_DAY')." ".$_SESSION['date']['end']."";
		break;
	case 6:
		$page="db.php?";
		$query.=" WHERE";
		$query.= " `date` >= ".$_SESSION['timestamp']['start']." AND `date` <= ".$_SESSION['timestamp']['end']."";
		$query.= " ORDER BY `$orderby`";

		$table_title=GLOBALMSG_RECORD_TITLE_ALL;
		$table_title.=" ".ucfirst(phr('FROM_TIME'))." ".$_SESSION['time']['start']." ".phr('OF_DAY')." ".$_SESSION['date']['start']." ".phr('TO_TIME')." ".$_SESSION['time']['end']." ".phr('OF_DAY')." ".$_SESSION['date']['end']."";
		break;
	default:
		$append_income_totals=true;
		$mgmt_type = new mgmt_type($query_value);
		$type_name=$mgmt_type -> name($_SESSION['language']);
		unset($mgmt_type);
		$page="db.php?";
		// the following syntax would be good, but mysql doesn't support it yet
		// so we use the following one
		$query.= " WHERE (`type`<>'3' AND `waiter_income` <> '1' AND `date` >= ".$_SESSION['timestamp']['start']." AND `date` <= ".$_SESSION['timestamp']['end'].")
		OR (`type`<>'4' AND `waiter_income` <> '1' AND `date` >= ".$_SESSION['timestamp']['start']." AND `date` <= ".$_SESSION['timestamp']['end'].")
		OR (`type`<>'5' AND `waiter_income` <> '1' AND `date` >= ".$_SESSION['timestamp']['start']." AND `date` <= ".$_SESSION['timestamp']['end'].")";
		//$query.=" AND `date` >= ".$_SESSION['timestamp']['start']." AND `date` <= ".$_SESSION['timestamp']['end']."";
		$query.= " ORDER BY `$orderby`";

		$table_title=GLOBALMSG_RECORD_TITLE_INCOME;
		$table_title.=" ".ucfirst(phr('FROM_TIME'))." ".$_SESSION['time']['start']." ".phr('OF_DAY')." ".$_SESSION['date']['start']." ".phr('TO_TIME')." ".$_SESSION['time']['end']." ".phr('OF_DAY')." ".$_SESSION['date']['end'];
		break;
	}


	$_SESSION['printable']['table_title']=$table_title;
	$_SESSION['printable']['query']=$query;

?>
	<div align="center">
		<form name="form1" action="db.php" method="GET">
		<input type="hidden" name="command" value="delete">
		<h3><?php echo $table_title; ?></h3>
		<h5><a href="printable.php" target="_blank"><?php echo ucfirst(phr('PRINTABLE_VERSION')).' ('.phr('TESTING').')'; ?></a></h5>
<?php
	$records_found=false;

	$res = mysql_db_query ($_SESSION['common_db'],$query);
	if($errno=mysql_errno()){
		$errdesc=mysql_error();
		error_msg($file,$line,"ERROR get_db_data - mysql - ".$errno." - ".$errdesc);
		echo "Mysql error ".$errno." - ".$errdesc.".<br>\n";
		echo "Called from ".$file." on line ".$line."<br>\n";
		echo "Query: ".$query.".<br>\n";
	}
	if(mysql_num_rows($res)) {
		$records_found=true;
	}


	$table=$GLOBALS['table_prefix'].'mgmt_types';
	$query_type_all="SELECT * FROM $table WHERE `is_invoice`='1' OR `is_receipt`='1' OR `is_bill`='1'";
	$res_types=mysql_db_query ($_SESSION['common_db'],$query_type_all);
	while($row_type = mysql_fetch_array ($res_types)) {
		$table=$GLOBALS['table_prefix'].'account_mgmt_main';
		$query_type="SELECT $table.* FROM $table";
		$query_type.= " WHERE (`type`='".$row_type['id']."' AND `waiter_income` = '1'  AND `date` >= ".$_SESSION['timestamp']['start']." AND `date` <= ".$_SESSION['timestamp']['end'].")";
		$res = mysql_db_query ($_SESSION['common_db'],$query_type);
		if(mysql_num_rows($res)) {
			$records_found=true;
		}
	}

	if(!$records_found){
		echo GLOBALMSG_RECORD_NONE_FOUND_PERIOD_ERROR.".<br>\n";
		echo GLOBALMSG_RECORD_CHANGE_SEARCH.".\n";
?>
		</form>
	</div>
<?php
		
		return 1;
	}

	table_header($page,$commandto);

	$i=0;

	if($append_income_totals){
		$table=$GLOBALS['table_prefix'].'mgmt_types';
		$query_type_all="SELECT * FROM $table WHERE `is_invoice`='1' OR `is_receipt`='1' OR `is_bill`='1'";
		$res_type=mysql_db_query ($_SESSION['common_db'],$query_type_all);
		while($row_type = mysql_fetch_array ($res_type)) {
			$totals_income=table_income($page,$commandto,$row_type['id']);

			$totals['cash_total']+=$totals_income['cash_total'];
			$totals['cash_vat']+=$totals_income['cash_vat'];
			$totals['bank_total']+=$totals_income['bank_total'];
			$totals['bank_vat']+=$totals_income['bank_vat'];
			$totals['debit_total']+=$totals_income['debit_total'];
			$totals['debit_vat']+=$totals_income['debit_vat'];
		}
	}

	$totals_main=table_generator($page,$commandto,$query,$command);

	$totals['cash_total']+=$totals_main['cash_total'];
	$totals['cash_vat']+=$totals_main['cash_vat'];
	$totals['bank_total']+=$totals_main['bank_total'];
	$totals['bank_vat']+=$totals_main['bank_vat'];
	$totals['debit_total']+=$totals_main['debit_total'];
	$totals['debit_vat']+=$totals_main['debit_vat'];

	table_footer($totals);

?>
		</form>
	</div>
<?php

}

function table_income($page,$commandto,$type){
	require("./mgmt_start.php");

	global $i;

	$table=$GLOBALS['table_prefix'].'account_mgmt_main';
	$query="SELECT $table.* FROM $table";
	$query.= " WHERE (`type`='$type' AND `waiter_income` = '1'  AND `date` >= ".$_SESSION['timestamp']['start']." AND `date` <= ".$_SESSION['timestamp']['end'].")";

	$res = mysql_db_query ($_SESSION['common_db'],$query);
	if(!mysql_num_rows($res)) return 1;

	while($row = mysql_fetch_array ($res)) {
		$cash_total_amount+=$row['cash_amount'];
		$cash_total_vat_amount+=$row['cash_vat_amount'];
		$bank_total_amount+=$row['bank_amount'];
		$bank_total_vat_amount+=$row['bank_vat_amount'];
		$debit_total_amount+=$row['debit_amount'];
		$debit_total_vat_amount+=$row['debit_vat_amount'];
	}

	if($errno=mysql_errno()) {
		$msg="Error in table income - ";
		$msg.='mysql: '.mysql_errno().' '.mysql_error();
		echo $msg,"<br>\n";
		error_msg(__FILE__,__LINE__,$msg);
		return 2;
	}


		if($i!=0 && $i%get_conf(__FILE__,__LINE__,"management_table_header_repeater")==0){
?>
	<tr align="center">
		<td rowspan="2"></td>
		<td rowspan="2" valign=middle><a href="<?php echo $page; ?>orderby=date&command=<?php echo $commandto; ?>"><b><?php echo ucfirst(phr('DATE')); ?></b></a></td>
		<td rowspan="2" valign=middle><a href="<?php echo $page; ?>orderby=who&command=<?php echo $commandto; ?>"><b><?php echo ucfirst(phr('WHO')); ?></b></a></td>
		<td rowspan="2" valign=middle><a href="<?php echo $page; ?>orderby=description&command=<?php echo $commandto; ?>"><b><?php echo ucfirst(phr('DESCRIPTION')); ?></b></a></td>
		<td rowspan="2" valign=middle><a href="<?php echo $page; ?>orderby=type&command=<?php echo $commandto; ?>"><b><?php echo ucfirst(phr('TYPE')); ?></b></a></td>
		<td colspan=3><b><?php echo ucfirst(phr('CASH')); ?></b></td>
		<td colspan=3><b><?php echo ucfirst(phr('BANK')); ?></b></td>
		<td colspan=2><b><?php echo ucfirst(phr('DEBTS')); ?></b></td>
	</tr>
	<tr align="center">
		<td><?php echo ucfirst(phr('INCOMINGS')); ?></td>
		<td><?php echo ucfirst(GLOBALMSG_OUTGOING_MANY); ?></td>
		<td><?php echo ucfirst(phr('TAXES')); ?></td>
		<td><?php echo ucfirst(phr('INCOMINGS')); ?></td>
		<td><?php echo ucfirst(GLOBALMSG_OUTGOING_MANY); ?></td>
		<td><?php echo ucfirst(phr('TAXES')); ?></td>
		<td><?php echo ucfirst(phr('AMOUNT')); ?></td>
		<td><?php echo ucfirst(phr('TAXES')); ?></td>
	</tr>

<?		}


		$table=$GLOBALS['table_prefix'].'mgmt_types';
		$res_local = mysql_db_query ($_SESSION['common_db'],"SELECT * FROM $table WHERE `id`='".$type."'");
		$row_local = mysql_fetch_array($res_local);
		$mgmt_type = new mgmt_type($type);
		$type_name=$mgmt_type -> name($_SESSION['language']);
		unset($mgmt_type);
		mysql_free_result($res_local);

		$description=ucfirst(phr('INCOME'))." $type_name ".ucphr('PERIOD');
		$who="";

		if($cash_total_amount>0){
			$cash_plus_amount=sprintf("%01.2f",$cash_total_amount);
			$cash_minus_amount="&nbsp;";
		} elseif($cash_total_amount<0) {
			$cash_plus_amount="&nbsp;";
			$cash_minus_amount=sprintf("%01.2f",$cash_total_amount);
		} else {
			$cash_plus_amount="&nbsp;";
			$cash_minus_amount="&nbsp;";
		}
		$cash_vat_amount=sprintf("%01.2f",$cash_total_vat_amount);

		if($bank_total_amount>0){
			$bank_plus_amount=sprintf("%01.2f",$bank_total_amount);
			$bank_minus_amount="&nbsp;";
		} elseif($bank_total_amount<0) {
			$bank_plus_amount="&nbsp;";
			$bank_minus_amount=sprintf("%01.2f",$bank_total_amount);
		} else {
			$bank_plus_amount="&nbsp;";
			$bank_minus_amount="&nbsp;";
		}
		$bank_vat_amount=sprintf("%01.2f",$bank_total_vat_amount);

		if($debit_total_amount!=0){
			$debit_amount=sprintf("%01.2f",$debit_total_amount);
		} else {
			$debit_amount="&nbsp;";
		}
		$debit_vat_amount=sprintf("%01.2f",$debit_total_vat_amount);

		echo "<tr class=\"".color_css($i)."\">\n";
		echo "<td>&nbsp</td>";
		echo "<td>".ucphr('PERIOD')."</td>\n";
		echo "<td>&nbsp</td>\n";
		echo "<td><a href=\"db.php?command=show_only&show_only=$type\">$description</a></td>\n";
		echo "<td>".$type_name."</td>\n";
		echo "<td>".$cash_plus_amount."</td>\n";
		echo "<td>".$cash_minus_amount."</td>\n";
		echo "<td>".$cash_vat_amount."</td>\n";
		echo "<td>".$bank_plus_amount."</td>\n";
		echo "<td>".$bank_minus_amount."</td>\n";
		echo "<td>".$bank_vat_amount."</td>\n";
		echo "<td>".$debit_amount."</td>\n";
		echo "<td>".$debit_vat_amount."</td>\n";
		echo "</tr>\n";
		$i++;

	$totals['cash_total']=$cash_total_amount;
	$totals['cash_vat']=$cash_total_vat_amount;
	$totals['bank_total']=$bank_total_amount;
	$totals['bank_vat']=$bank_total_vat_amount;
	$totals['debit_total']=$debit_total_amount;
	$totals['debit_vat']=$debit_total_vat_amount;

	return $totals;

}



function table_generator($page,$commandto,$query,$command){
	require("./mgmt_start.php");

	global $i;

	$res = mysql_db_query ($_SESSION['common_db'],$query);
	if(!mysql_num_rows($res)) return 1;

	if($errno=mysql_errno()) {
		$msg="Error in table generator - ";
		$msg.='mysql: '.mysql_errno().' '.mysql_error();
		echo $msg,"<br>\n";
		error_msg(__FILE__,__LINE__,$msg);
		return 2;
	}



	while($row = mysql_fetch_array ($res)) {

		if($i!=0 && $i%get_conf(__FILE__,__LINE__,"management_table_header_repeater")==0){
?>
	<tr align="center">
		<td rowspan="2"></td>
		<td rowspan="2" valign=middle><a href="<?php echo $page; ?>orderby=date&command=<?php echo $commandto; ?>"><b><?php echo ucfirst(phr('DATE')); ?></b></a></td>
		<td rowspan="2" valign=middle><a href="<?php echo $page; ?>orderby=who&command=<?php echo $commandto; ?>"><b><?php echo ucfirst(phr('WHO')); ?></b></a></td>
		<td rowspan="2" valign=middle><a href="<?php echo $page; ?>orderby=description&command=<?php echo $commandto; ?>"><b><?php echo ucfirst(phr('DESCRIPTION')); ?></b></a></td>
		<td rowspan="2" valign=middle><a href="<?php echo $page; ?>orderby=type&command=<?php echo $commandto; ?>"><b><?php echo ucfirst(phr('TYPE')); ?></b></a></td>
		<td colspan=3><b><?php echo ucfirst(phr('CASH')); ?></b></td>
		<td colspan=3><b><?php echo ucfirst(phr('BANK')); ?></b></td>
		<td colspan=2><b><?php echo ucfirst(phr('DEBTS')); ?></b></td>
	</tr>
	<tr align="center">
		<td><?php echo ucfirst(phr('INCOMINGS')); ?></td>
		<td><?php echo ucfirst(GLOBALMSG_OUTGOING_MANY); ?></td>
		<td><?php echo ucfirst(phr('TAXES')); ?></td>
		<td><?php echo ucfirst(phr('INCOMINGS')); ?></td>
		<td><?php echo ucfirst(GLOBALMSG_OUTGOING_MANY); ?></td>
		<td><?php echo ucfirst(phr('TAXES')); ?></td>
		<td><?php echo ucfirst(phr('AMOUNT')); ?></td>
		<td><?php echo ucfirst(phr('TAXES')); ?></td>
	</tr>

<?		}

		$date['year']=substr($row['date'],0,4);
		$date['month']=substr($row['date'],4,2);
		$date['day']=substr($row['date'],6,2);
		$date['hour']=substr($row['date'],8,2);
		$date['minute']=substr($row['date'],10,2);
		$date['second']=substr($row['date'],12,2);

		$description=$row['description'];
		$cash_amount=$row['cash_amount'];
		$cash_vat_amount=$row['cash_vat_amount'];
		$bank_amount=$row['bank_amount'];
		$bank_vat_amount=$row['bank_vat_amount'];
		$debit_amount=$row['debit_amount'];
		$debit_vat_amount=$row['debit_vat_amount'];
		$id=$row['id'];
		$debit=$row['debit'];
		$who=$row['who'];

		if($row['associated_invoice']){
			$show_id=$row['associated_invoice'];
			$is_payment=true;
		} else {
			$show_id=$id;
			$is_payment=false;
		}

		$mgmt_type = new mgmt_type($row['type']);
		$type=$mgmt_type -> name($_SESSION['language']);
		unset($mgmt_type);

		$table=$GLOBALS['table_prefix'].'account_mgmt_addressbook';
		$res_supplier = mysql_db_query ($_SESSION['common_db'],"SELECT * FROM $table WHERE `name`='".$row['who']."'");
		if(mysql_num_rows($res_supplier)==1){
			$row_supplier = mysql_fetch_array ($res_supplier);
			$supplier['id']=$row_supplier['id'];
			$who="<a href=\"supply.php?command=showknownsupplier&id=".$row_supplier['id']."\">".$row['who']."</a>";
		} else {
			$who="<a href=\"supply.php?command=show&name=".$row['who']."\">".$row['who']."</a>";
		}

		$cash_total_amount+=$cash_amount;
		$cash_total_vat_amount+=$cash_vat_amount;

		if($cash_amount>0){
			$cash_plus_amount=sprintf("%01.2f",$cash_amount);
			$cash_minus_amount="&nbsp;";
		} elseif($cash_amount<0) {
			$cash_plus_amount="&nbsp;";
			$cash_minus_amount=sprintf("%01.2f",$cash_amount);
		} else {
			$cash_plus_amount="&nbsp;";
			$cash_minus_amount="&nbsp;";
		}

		$bank_total_amount+=$bank_amount;
		$bank_total_vat_amount+=$bank_vat_amount;

		if($bank_amount>0){
			$bank_plus_amount=sprintf("%01.2f",$bank_amount);
			$bank_minus_amount="&nbsp;";
		} elseif($bank_amount<0) {
			$bank_plus_amount="&nbsp;";
			$bank_minus_amount=sprintf("%01.2f",$bank_amount);
		} else {
			$bank_plus_amount="&nbsp;";
			$bank_minus_amount="&nbsp;";
		}
		$debit_total_amount+=$debit_amount;
		$debit_total_vat_amount+=$debit_vat_amount;

		if($debit_amount!=0){
			$debit_amount=sprintf("%01.2f",$debit_amount);
		} else {
			$debit_amount="&nbsp;";
		}

		echo "<tr class=\"".color_css($i)."\">\n";
		if ($is_payment)
			echo "<td></td>";
		elseif ($row['internal_id']!="")
			echo "<td></td>";
		else
			echo "<td><input name=\"delete[$id]\" type=\"checkbox\"></td>\n";
		echo "<td>".$date["day"]."/".$date["month"]."/".$date["year"]."</td>\n";
		echo "<td>".$who."</td>\n";
		if($row['internal_id']!="" && $row['annulled']==1) {
			echo "<td><s><a href=\"receipt.php?command=show&id=$show_id\">$description - ".ucphr('ANNULLED_ABBR')."</a></s></td>\n";
		} elseif($row['internal_id']!="" && $row['annulled']==0) {
			echo "<td><a href=\"receipt.php?command=show&id=$show_id\">$description</a></td>\n";
		} else {
			echo "<td><a href=\"db.php?command=show&id=$show_id\">$description</a></td>\n";
		}
		echo "<td>".$type."</td>\n";
		echo "<td>".$cash_plus_amount."</td>\n";
		echo "<td>".$cash_minus_amount."</td>\n";
		echo "<td>".$cash_vat_amount."</td>\n";
		echo "<td>".$bank_plus_amount."</td>\n";
		echo "<td>".$bank_minus_amount."</td>\n";
		echo "<td>".$bank_vat_amount."</td>\n";
		echo "<td>".$debit_amount."</td>\n";
		echo "<td>".$debit_vat_amount."</td>\n";
		echo "</tr>\n";
		$i++;
	}

	$totals['cash_total']=$cash_total_amount;
	$totals['cash_vat']=$cash_total_vat_amount;
	$totals['bank_total']=$bank_total_amount;
	$totals['bank_vat']=$bank_total_vat_amount;
	$totals['debit_total']=$debit_total_amount;
	$totals['debit_vat']=$debit_total_vat_amount;

	return $totals;

}


function table_header($page,$commandto){
	require("./mgmt_start.php");
?>
<table class="mgmt_main_table" width="100%">
	<thead>
	<tr>
		<th scope=col rowspan=2></th>
		<th scope=col rowspan=2 valign=middle><a href="<?php echo $page; ?>orderby=date&command=<?php echo $commandto; ?>"><?php echo ucfirst(phr('DATE')); ?></a></th>
		<th scope=col rowspan=2 valign=middle><a href="<?php echo $page; ?>orderby=who&command=<?php echo $commandto; ?>"><?php echo ucfirst(phr('WHO')); ?></a></th>
		<th scope=col rowspan=2 valign=middle><a href="<?php echo $page; ?>orderby=description&command=<?php echo $commandto; ?>"><?php echo ucfirst(phr('DESCRIPTION')); ?></a></th>
		<th scope=col rowspan=2 valign=middle><a href="<?php echo $page; ?>orderby=type&command=<?php echo $commandto; ?>"><?php echo ucfirst(phr('TYPE')); ?></a></th>
		<th scope=colgroup colspan=3><?php echo ucfirst(phr('CASH')); ?></th>
		<th scope=colgroup colspan=3><?php echo ucfirst(phr('BANK')); ?></th>
		<th scope=colgroup colspan=3><?php echo ucfirst(phr('DEBTS')); ?></th>
	</tr>
	<tr>
		<th scope=col><?php echo ucfirst(phr('INCOMINGS')); ?></th>
		<th scope=col><?php echo ucfirst(GLOBALMSG_OUTGOING_MANY); ?></th>
		<th scope=col><?php echo ucfirst(phr('TAXES')); ?></th>
		<th scope=col><?php echo ucfirst(phr('INCOMINGS')); ?></th>
		<th scope=col><?php echo ucfirst(GLOBALMSG_OUTGOING_MANY); ?></th>
		<th scope=col><?php echo ucfirst(phr('TAXES')); ?></th>
		<th scope=col><?php echo ucfirst(phr('AMOUNT')); ?></th>
		<th scope=col><?php echo ucfirst(phr('TAXES')); ?></th>
	</tr>
	</thead>
	<tbody>
<?php

}

function table_footer($totals){
	require("./mgmt_start.php");
	if($totals['cash_total']>0){
		$cash_total_plus_amount=sprintf("%01.2f",$totals['cash_total']);
		$cash_total_minus_amount=" ";
	} elseif($totals['cash_total']<0) {
		$cash_total_plus_amount=" ";
		$cash_total_minus_amount=sprintf("%01.2f",$totals['cash_total']);
	} else {
		$cash_total_plus_amount=sprintf("%01.2f",0);
		$cash_total_minus_amount=" ";
	}
	$cash_total_vat_amount=sprintf("%01.2f",$totals['cash_vat']);

	if($totals['bank_total']>0){
		$bank_total_plus_amount=sprintf("%01.2f",$totals['bank_total']);
		$bank_total_minus_amount=" ";
	} elseif($totals['bank_total']<0) {
		$bank_total_plus_amount=" ";
		$bank_total_minus_amount=sprintf("%01.2f",$totals['bank_total']);
	} else {
		$bank_total_plus_amount=sprintf("%01.2f",0);
		$bank_total_minus_amount=" ";
	}
	$bank_total_vat_amount=sprintf("%01.2f",$totals['bank_vat']);

	if($totals['debit_total']!=0){
		$debit_total_amount=sprintf("%01.2f",$totals['debit_total']);
	} else {
		$debit_total_amount=sprintf("%01.2f",0);
	}
	$debit_total_vat_amount=sprintf("%01.2f",$totals['debit_vat']);

?>
	<tfoot>
		<tr>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td><?php echo ucfirst(phr('TOTAL')); ?></td>
		<td><?php echo $cash_total_plus_amount; ?></td>
		<td><?php echo $cash_total_minus_amount; ?></td>
		<td><?php echo $cash_total_vat_amount; ?></td>
		<td><?php echo $bank_total_plus_amount; ?></td>
		<td><?php echo $bank_total_minus_amount; ?></td>
		<td><?php echo $bank_total_vat_amount; ?></td>
		<td><?php echo $debit_total_amount; ?></td>
		<td><?php echo $debit_total_vat_amount; ?></td>
		<td> </td>
		</tr>
	</tfoot>
	</tbody>
</table>
<input type="submit" value="<?php echo ucfirst(GLOBALMSG_RECORD_DELETE_SELECTED); ?>">
<?php

}


?>
