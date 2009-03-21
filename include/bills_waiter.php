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

function bill_orders_to_print ($sourceid) {
	$query="SELECT * FROM `orders` WHERE `sourceid`='$sourceid' AND `deleted`=0 AND `printed` IS NOT NULL ORDER BY `associated_id` ASC";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;
	
	if(!mysql_num_rows($res)) return 0;
	
	while($arr=mysql_fetch_array($res)){
		$topay = $arr['quantity'] - $arr['paid'];
		if ($topay > 0) return 1;
	}
	return 0;
}

function write_log_item($item_id,$quantity,$price,$receipt_id) {
	/*
	name:
	write_log_item($item_id,$quantity,$price,$receipt_id)
	returns:
	0 - no error
	1 - Order record not found
	2 - Waiter not found
	3 - log writing error
	other - mysql error number
	*/
	// next line is not necessary, due to automatic mySQL filling when no value is provided
	// $log["datetime"] = date("Y-m-d H:i:s",time()); 	// human format
	// $log["datetime"] = date("YmdHis",time()); 		// timestamp format

	$query="SELECT * FROM `orders` WHERE `id`='$item_id'";
	$res_item=common_query($query,__FILE__,__LINE__);
	if(!$res_item) return mysql_errno();

	if(mysql_num_rows($res_item))
		$arr_item=mysql_fetch_array($res_item);
	else {
		$msg='Error in '.__FUNCTION__.' - Order record not found: order id: '.$item_id.'.';
		echo nl2br($msg)."\n";
		error_msg(__FILE__,__LINE__,$msg);
		return 1;
	}
	debug_msg(__FILE__,__LINE__,__FUNCTION__.' - id: '.$arr_item['dishid']);

	$log['waiter']='NotAssigned';
	$table = new table($arr_item['sourceid']);
	if($table->data['userid']) $log["waiter"]=$table->data['userid'];

	if($arr_item==0 || $arr_item['deleted']==1)  return 0;
	
	if($arr_item['dishid'] == MOD_ID && $arr_item['operation']==0) return 0;
	
	$dishid=$arr_item['dishid'];
	$log["quantity"]=$quantity;
	$log["price"]=$price;
	$log["payment"]=$receipt_id;
	if($dishid != MOD_ID) {
		$log["dish"]=$arr_item['dishid'];
		$log["ingredient"]="";
		$log["operation"]=0;
		$log["destination"]=$arr_item['dest_id'];
		$dish = new dish ($arr_item['dishid']);
		$log['category'] = $dish->data['category'];
	} elseif ($dishid==MOD_ID) {
		$ingred = new ingredient ($arr_item['ingredid']);
		$log['category'] = $ingred->data['category'];
		
		$log["dish"]="";
		$log["ingredient"]=$arr_item['ingredid'];
		$log["operation"]=$arr_item['operation'];

		$associated_orderid=$arr_item['associated_id'];
		$associated_dishid=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],"orders","dishid",$associated_orderid);
		$log["destination"]=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],"dishes","destid",$associated_dishid);
	}elseif ($dishid==SERVICE_ID){
		$log["dish"]=SERVICE_ID;
		$log["ingredient"]="";
		$log["operation"]=0;
		$log["category"]=0;
		$log["destination"]=0;
	}

	$log_table="account_log";

	$query="INSERT INTO `$log_table` (";
	for (reset ($log); list ($key, $value) = each ($log); ) {
		$value = str_replace ("'", "\'", $value);
		$query.="`".$key."`,";
	}
	// strips the last comma that has been put
	$query = substr ($query, 0, strlen($query)-1);
	$query.=") VALUES (";
	for (reset ($log); list ($key, $value) = each ($log); ) {
		$value = str_replace ("'", "\'", $value);
		$query.="'".$value."',";
	}
	// strips the last comma that has been put
	$query = substr ($query, 0, strlen($query)-1);
	$query.=")";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;
	
	return 0;
}

function bill_check_keep_separated(){
	if(isset($_REQUEST['keep_separated'])) 
		$keep_separated=$_REQUEST['keep_separated'];
	else 
		$keep_separated=0;

	if(!$keep_separated){
		unset($_SESSION['separated']);
		unset($_SESSION['type']);
		unset($_SESSION['account']);
		unset($_SESSION['discount']);
	}

	return $keep_separated;
}

function bill_check_empty(){
	$empty=true;

	if(is_array($_SESSION['separated'])){
		for (reset ($_SESSION['separated']); list ($key, $value) = each ($_SESSION['separated']); ) {
			if(!$_SESSION['separated'][$key]['special']
				&& $_SESSION['separated'][$key]['topay']){
				$empty=false;
			}
		}
	}
	return $empty;
}

function bill_print(){
	/*
	name:
	bill_print()
	returns:
	0 - no error
	1 - Printer not found for output tyoe
	2 - No order selected
	3 - Printing error
	other - mysql error number
	*/
	// type: 	0: reserved
	//			1: bill
	//			2. invoice
	//			3. receipt
	//	we have to translate them to the mgmt_type values in order to be correctely
	//	written and read in the log
	//	mgmt_type:	3: invoice
	//				4: bill
	//				5: receipt
	global $tpl;
	global $output_page;
	$output['orders']='';	
	$output_page = '';
	//connect to printer by client IP
	$clientip = "";
	if(isset($clientip)) unset($clientip);
	$clientip=getenv('REMOTE_ADDR');
	//end:connect to printer by client IP
	if($_SESSION['bill_printed']) return 0;
	$_SESSION['bill_printed']=1;

	$type = $_SESSION['type'];
	$keep_separated = bill_check_keep_separated();
	$type = receipt_type_waiter2mgmt($type);

	// CRYPTO
	if(!bill_check_empty()) {
		$receipt_id=receipt_insert($_SESSION['account'],$type);
	}
	$printing_enabled=$arr['print_bill'];

	$tpl_print = new template;

	switch ($_SESSION['type']) {
		case 1:
			$query="SELECT * FROM `dests` WHERE `bill`='1' AND `deleted`='0'";
			$template_type='bill';
			break;
		case 2:
			$query="SELECT * FROM `dests` WHERE `invoice`='1' AND `deleted`='0'";
			$template_type='invoice';
			break;
		case 3:
			$query="SELECT * FROM `dests` WHERE `receipt`='1' AND `deleted`='0'";
			$template_type='receipt';
			break;
		default:
			$query="SELECT * FROM `dests` WHERE `bill`='1' AND `deleted`='0'";
			$template_type='bill';
	}
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	//connect to printer by client IP
	while ($row = mysql_fetch_array($res) ) {
		if ($row['dest_ip']=='') {
			if ($row['dest']!='') {
				$destid=$row['id'];
				$dest_language=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"language",$destid);
			} else {
				return ERR_PRINTER_NOT_FOUND_FOR_SELECTED_TYPE;
			}
		}elseif ($row['dest']!='' && $row['dest_ip']!='') {
				$ippart = explode("|",$row['dest_ip']);
				if(in_array($clientip,$ippart)){
					$destid=$row['id'];
					break;
				}
			$dest_language=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"language",$destid);
		} else {
			return ERR_PRINTER_NOT_FOUND_FOR_SELECTED_TYPE;
		}
	}
	if($err = $tpl_print->set_print_template_file($destid,$template_type)) return $err;

	// reset the counter and the message to be sent to the printer
	$total=0;
	$msg="";

	$tablenum=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"name",$_SESSION['sourceid']);
	$output['table'] = ucfirst(lang_get($dest_language,'PRINTS_TABLE'))." $tablenum \n";
	$tpl_print->assign("table", $output['table']);
	
	// writes the table num to video
	$output_page .= ucfirst(phr('TABLE_NUMBER')).": $tablenum     ";

	$table = new table($_SESSION['sourceid']);
	$table->fetch_data(true);
	if ($cust_id=$table->data['customer']) {
		$cust = new customer ($cust_id);
		$output['customer']=ucfirst(lang_get($dest_language,'CUSTOMER')).": ".$cust -> data ['surname'].' '.$cust -> data ['name'];
		$tpl_print->assign("customer_name", $output['customer']);
		$output['customer']=$cust -> data ['address'];
		$tpl_print->assign("customer_address", $output['customer']);
		$output['customer']=$cust -> data ['zip'];
		$tpl_print->assign("customer_zip_code", $output['customer']);
		$output['customer']=$cust -> data ['city'];
		$tpl_print->assign("customer_city", $output['customer']);
		$output['customer']=ucfirst(lang_get($dest_language,'VAT_ACCOUNT')).": ".$cust -> data ['vat_account'];
		$tpl_print->assign("customer_vat_account", $output['customer']);
	}
	
	if(bill_check_empty()) {
		return ERR_NO_ORDER_SELECTED;
	}

	//mizuko : swap qty with name
	$output_page .= "<table bgcolor=\"".COLOR_TABLE_GENERAL."\">
	<thead>
	<tr>
	<th scope=col>".ucfirst(phr('NAME'))."</th>
	<th scope=col>".ucfirst(phr('QUANTITY_ABBR'))."</th>
	<th scope=col>".ucfirst(phr('PRICE'))."</th>
	</tr>
	</thead>
	<tbody>";


	$class=COLOR_ORDER_PRINTED;

	ksort($_SESSION['separated']);

	// the next for prints the list and the chosen dishes
	for (reset ($_SESSION['separated']); list ($key, $value) = each ($_SESSION['separated']); ) {
		$output['orders'] .= bill_print_row($key,$value,$destid);
	}
	$tpl_print -> assign("orders", $output['orders']);

	if($_SESSION['discount']['type']=="amount"
	|| $_SESSION['discount']['type']=="percent") {
		$output['discount']=bill_print_discount($receipt_id,$destid);
		$tpl_print->assign("discount", $output['discount']);
	}

	$total = bill_calc_vat();
	$total_discounted = bill_calc_discount($total);
	// updates the receipt value, has to be before print totals!
	receipt_update_amounts($_SESSION['account'],$total_discounted,$receipt_id);
	
	$output['total'] = bill_print_total($receipt_id,$destid);
	$tpl_print -> assign("total", $output['total']);
	if( SHOW_CHANGE == 1 ) {
		$output['change'] = bill_print_change($total_discounted['total']);
		$tpl_print -> assign("change",$output['change']);
	}
	//mizuko
	$user = new user($_SESSION['userid']);
	$output['waiter']=ucfirst(lang_get($dest_language,'PRINTS_WAITER')).": ".$user->data['name'];
	$tpl_print->assign("waiter", $output['waiter']);
	$tpl_print->assign("date", printer_print_date());	
	//end mizuko
	
	$output_page .= "
	</tbody>
	</table>";

	$output['receipt_id'] = bill_print_receipt_id ($receipt_id,$destid);
	$tpl_print -> assign ("receipt_id", $output['receipt_id']);
	
	$output['taxes'] = bill_print_taxes ($receipt_id,$destid);
	$tpl_print -> assign("taxes", $output['taxes']);
	
	if($err = $tpl_print -> parse ()) {
		$msg="Error in ".__FUNCTION__." - ";
		$msg.='error: '.$err."\n";
		error_msg (__FILE__,__LINE__,$msg);
		echo nl2br ($msg)."\n";
		return ERR_PARSING_TEMPLATE;
	}
	
	$tpl_print -> restore_curly ();
	$msg = $tpl_print -> getOutput ();

	$msg = str_replace ("'", "", $msg);

	if($printing_enabled) {
		if($err = print_line($arr['id'],$msg)) {
			// the process is stopped so we delete the created receipt
			receipt_delete($_SESSION['account'],$receipt_id);
			return $err;
		}
	}

	ksort($_SESSION['separated']);

	// sets the log
	for (reset ($_SESSION['separated']); list ($key, $value) = each ($_SESSION['separated']); ) {
		if($err_logger=bill_logger($key,$receipt_id)){
			debug_msg(__FILE__,__LINE__,__FUNCTION__.' - receipt_id: '.$receipt_id.' - logger return code: '.$err_logger);
		} else {
			debug_msg(__FILE__,__LINE__,__FUNCTION__.' - receipt_id: '.$receipt_id.' - logged');
		}
	}
	
	return 0;
}

function bill_logger($item_id,$receipt_id){
	$topay=$_SESSION['separated'][$item_id]['topay'];
	if(!$topay) return 1;

	$orderid=$item_id;
	
	$query="SELECT * FROM `orders` WHERE `id`='$orderid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return mysql_errno();
	
	$arr=mysql_fetch_array($res);
	$price=$arr["price"]/$arr["quantity"]*$topay;
	$oldpaid=$arr["paid"];
	$newpaid=$oldpaid+$topay;

	if($newpaid<0) $newpaid=0;

	$query = "UPDATE `orders` SET `paid` = '$newpaid' WHERE `id` = '$orderid'";
	$resupd=common_query($query,__FILE__,__LINE__);
	if(!$resupd) return mysql_errno();
	
	if($log_error=write_log_item($orderid,$topay,$price,$receipt_id)) {
		$msg = 'Error in '.__FUNCTION__.' - ';
		$msg .= 'Logging Error: '.$log_error;
		echo nl2br($msg)."\n";
		error_msg(__FILE__,__LINE__,$msg);
		return 2;
	}

	$query="SELECT * FROM `orders` WHERE `associated_id`='$orderid' AND  `id`!='$orderid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return mysql_errno();
	
	while($arr=mysql_fetch_array($res)) {
		$price=$arr["price"]/$arr["quantity"]*$topay;
		
		$query = "UPDATE `orders` SET `paid` = '$newpaid' WHERE `id` = '".$arr['id']."'";
		$resupd=common_query($query,__FILE__,__LINE__);
		if(!$resupd) return mysql_errno();
		
		if($log_error=write_log_item($arr['id'],$topay,$price,$receipt_id)) {
			$msg = 'Error in '.__FUNCTION__.' - ';
			$msg .= 'Logging Error: '.$log_error;
			echo nl2br($msg)."\n";
			error_msg(__FILE__,__LINE__,$msg);
			return 2;
		}
	}

	return 0;
}

function bill_order_get_modifications($orderid,$lang='') {
	$max_chars=5;
	$show_priced_only = true;

	if(empty($lang)) $lang=$_SESSION['language'];

	// selects all the mods that have operation != 0, so that actually could have a price
	$query="SELECT * FROM `orders` WHERE `associated_id`='$orderid' AND `id`!='$orderid' AND `operation`!='0'";
	if($show_priced_only) $query .= " AND `price`!='0'";
	
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return mysql_errno();

	if(!mysql_num_rows($res)) return 0;

	echo $name."<br>\n";
	while($arr=mysql_fetch_array($res)) {
		if($arr['operation']==1) $name='+';
		elseif($arr['operation']==-1) $name='-';
		//$name.=' ';
		$ingredobj = new ingredient ($arr['ingredid']);
		$modname = $ingredobj -> name ($lang);
		
		$name.=substr($modname,0,$max_chars);
		$name.='.';
		$mods[]=$name;
	}
	return $mods;
}

function bill_print_row ($key,$value,$destid){
	global $output_page;
	$msg='';
	
	$dest_language=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"language",$destid);
	$dishobj = new dish ($_SESSION['separated'][$key]['dishid']);
	$name = $dishobj -> name ($dest_language);
	$mods=bill_order_get_modifications($key,$dest_language);

	if($_SESSION['separated'][$key]['dishid']==SERVICE_ID) {
		$name=ucfirst(lang_get($dest_language,'SERVICE_FEE'));
	}

	$class=COLOR_ORDER_PRINTED;

	if($_SESSION['separated'][$key]['extra_care']){
		$classextra=COLOR_ORDER_EXTRACARE;
	} else {
		$classextra=$class;
	}

	if(!$_SESSION['separated'][$key]['special']	&& $_SESSION['separated'][$key]['topay']){
		
		//mizuko : swap qty with name
		//mizuko : to much spaces here
		$msg.= sprintf("%-30s", $name );
		$msg.=$_SESSION['separated'][$key]['topay'];
		//price by quantity 
		//13.11.2006
		$msg.="x".sprintf("%0.2f",$_SESSION['separated'][$key]['finalprice'])/$_SESSION['separated'][$key]['topay'];
		
		//mizuko: get rid of the currency name
		//$msg.="   ".country_conf_currency()." ".sprintf("%0.2f",$_SESSION['separated'][$key]['finalprice']);
		$msg.="   ".sprintf("%0.2f",$_SESSION['separated'][$key]['finalprice']);
		$msg.="\n";

		if($mods) {
			$msg.=" \t";
			for (reset ($mods); list ($key2, $value2) = each ($mods); ) {
				$msg.=$value2;
				$msgmods.=$value2;
			}
			$msg.="\n";
		}

		$output_page .= "<tr bgcolor=\"$class\">\n";
		$output_page .= "
		<td bgcolor=\"$class\">";
		$output_page .= $_SESSION['separated'][$key]['topay'];
		$output_page .= "</td>
		<td bgcolor=\"$class\">".$name."</td>
		<td bgcolor=\"$class\">";
		$output_page .= sprintf("%0.2f",$_SESSION['separated'][$key]['finalprice']);
		$output_page .= "</td>
		</tr>
		";

		if($mods) {
			$output_page .= "<tr bgcolor=\"$class\">\n";
			$output_page .= "
			<td bgcolor=\"$class\">&nbsp;</td>
			<td bgcolor=\"$class\">".$msgmods."</td>
			<td bgcolor=\"$class\">&nbsp;</td>
			</tr>
			";
		}
	}

	return $msg;
}

function bill_print_discount($receipt_id,$destid) {
	global $output_page;

	$dest_language=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"language",$destid);
	
	$msg="";
	$class=COLOR_ORDER_PRINTED;

	for (reset ($_SESSION['separated']); list ($key, $value) = each ($_SESSION['separated']); ) {
		$total+=$_SESSION['separated'][$key]['finalprice'];
	}

	if($_SESSION['discount']['type']=="amount") {
		$discount_value=$_SESSION['discount']['amount'];
		$total_discounted=$total+$discount_value;
		$discount_label="";
		$discount_number=-$_SESSION['discount']['amount'];
	}
	elseif($_SESSION['discount']['type']=="percent") {
		$discount_value=$total/100*$_SESSION['discount']['percent'];
		$total_discounted=$total-$discount_value;
		$discount_label=$_SESSION['discount']['percent'].' %';
		$discount_number=$total/100*$_SESSION['discount']['percent'];
	} else {
		return $msg;
	}
	$total_discounted=round($total_discounted,2);
	$discount_number=round($discount_number,2);

	if(!$discount_number) return $msg;

	$err = write_log_discount($discount_value,$receipt_id);
	$err = discount_save_to_source($discount_value);

	$msg.="\t".ucfirst(lang_get($dest_language,'PRINTS_DISCOUNT'))." ".$discount_label;
	$msg.=" \t".country_conf_currency()." ".sprintf("%0.2f",$discount_number);

	$output_page .= '
	<tr bgcolor="'.$class.'">
	<td></td>
	<td>'.ucfirst(phr('DISCOUNT')).' '.$discount_label.'</td>
	<td>'.sprintf("%0.2f",$discount_number).'</td>
	</tr>'."<br/>\n";

	return $msg;
}

function bill_calc_vat() {
	// calculates the taxes amounts for each selected order
	$_SESSION['vat']=array();
	
	// scans all the orders that have a final price != 0
	for (reset ($_SESSION['separated']); list ($key, $value) = each ($_SESSION['separated']); ) {
		if($_SESSION['separated'][$key]['finalprice']) {
			$dishid=$_SESSION['separated'][$key]['dishid'];
			$price=$_SESSION['separated'][$key]['finalprice'];
			$dish_cat=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dishes',"category",$dishid);
			$vat_rate_id=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'categories',"vat_rate",$dish_cat);
			$vat_rate=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'vat_rates',"rate",$vat_rate_id);
			$taxable=$price/($vat_rate+1);
			$tax=$taxable*$vat_rate;
			
			// creates the vat array with tax, taxable and total divided per vat type
			$_SESSION['vat'][$vat_rate_id]['taxable']+=$taxable;
			$_SESSION['vat'][$vat_rate_id]['tax']+=$tax;
			$_SESSION['vat'][$vat_rate_id]['total']+=$taxable+$tax;
		}
	}
	
	// adds the human readable info (name and rate) to each vat type
	for (reset ($_SESSION['vat']); list ($key, $value) = each ($_SESSION['vat']); ) {
			$vat_rate_name=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'vat_rates',"name",$key);
			$vat_rate=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'vat_rates',"rate",$key);
			$_SESSION['vat'][$key]['name']=$vat_rate_name;
			$_SESSION['vat'][$key]['rate']=$vat_rate;
	}
	
	// prepares the return array
	$ret['taxable']=0;
	$ret['tax']=0;
	
	
	for (reset ($_SESSION['vat']); list ($key, $value) = each ($_SESSION['vat']); ) {
		// rounds each value
		$_SESSION['vat'][$key]['taxable']=round($_SESSION['vat'][$key]['taxable'],2);
		$_SESSION['vat'][$key]['tax']=round($_SESSION['vat'][$key]['tax'],2);
		$_SESSION['vat'][$key]['total']=round($_SESSION['vat'][$key]['total'],2);
		
		// sums all the values from the vat array to get totals and return them
		$ret['taxable']+=$_SESSION['vat'][$key]['taxable'];
		$ret['tax']+=$_SESSION['vat'][$key]['tax'];
		$ret['total']+=$_SESSION['vat'][$key]['total'];
	}

	return $ret;
}

function bill_calc_discount($total) {
	// calculates a mean vat rate
	if($total['taxable']) $mean_vat_rate=$total['total']/$total['taxable']-1;
	else $mean_vat_rate=0;
	
	// assign the total discount amount
	if($_SESSION['discount']['type']=="amount") {
		$disc_total=$_SESSION['discount']['amount'];
	}
	elseif($_SESSION['discount']['type']=="percent") {
		$disc_total=$total['total']/100*$_SESSION['discount']['percent'];
	} else {
		$disc_total=0;
		//return $total;
	}
	
	// assigns taxes on the discount
	$disc_taxable=$disc_total/($mean_vat_rate+1);
	$disc_tax=$disc_total-$disc_taxable;
	
	
	for (reset ($_SESSION['vat']); list ($key, $value) = each ($_SESSION['vat']); ) {
		// corrects the tax, taxable and total values for each vat rate, by subracting a weighted part of the discount
		if ($total['tax'] != 0) {
			$_SESSION['vat'][$key]['tax']=$_SESSION['vat'][$key]['tax']-abs($_SESSION['vat'][$key]['tax']/$total['tax']*$disc_tax);
		} else {
			$_SESSION['vat'][$key]['tax']=0;
		}
		if ($total['taxable'] != 0) {
			$_SESSION['vat'][$key]['taxable']=$_SESSION['vat'][$key]['taxable']-abs($_SESSION['vat'][$key]['taxable']/$total['taxable']*$disc_taxable);
		} else {
			$_SESSION['vat'][$key]['taxable']=0;
		}
		if ($total['total'] != 0) {
			$_SESSION['vat'][$key]['total']=$_SESSION['vat'][$key]['total']-abs($_SESSION['vat'][$key]['total']/$total['total']*$disc_total);
		} else {
			$_SESSION['vat'][$key]['total']=0;
		}
		
		// rounds everything
		$_SESSION['vat'][$key]['taxable']=round($_SESSION['vat'][$key]['taxable'],2);
		$_SESSION['vat'][$key]['tax']=round($_SESSION['vat'][$key]['tax'],2);
		$_SESSION['vat'][$key]['total']=round($_SESSION['vat'][$key]['total'],2);
	}
	
	$total['total']=$total['total']-abs($disc_total);
	$total['taxable']=$total['taxable']-abs($disc_taxable);
	$total['tax']=$total['tax']-abs($disc_tax);
	
	$total['total']=round($total['total'],2);
	$total['total']=sprintf("%0.2f",$total['total']);
	$total['taxable']=round($total['taxable'],2);
	$total['taxable']=sprintf("%0.2f",$total['taxable']);
	$total['tax']=round($total['tax'],2);
	$total['tax']=sprintf("%0.2f",$total['tax']);

	return $total;
}

function bill_print_change($total) {
	global $output_page;
	$currencies = new currencies();
	$curr_value = $currencies->list_search_active();
	
	for($i = 0; $i < count($curr_value); $i++ ) {
		$msg.= sprintf("%-30s",$curr_value[$i]['name'])." " . round( $total/$curr_value[$i]['rate'], 2)."\n";
	}
	
	return $msg;	
	
}

function bill_print_total($receipt_id,$destid) {
	global $output_page;
		
	$dest_language=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"language",$destid);
	
	$msg="";
	$class=COLOR_ORDER_PRINTED;

	$table='account_mgmt_main';
	$query="SELECT * FROM $table WHERE `id`='$receipt_id'";
		$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	$arr=mysql_fetch_array($res);
	$total_discounted=$arr['cash_amount'];
	
	$output_page .= '<tr>
	<td></td>
	<td>'.ucfirst(phr('TOTAL')).'</td>
	<td>'.$total_discounted.'</td>
	</tr>'."\n";

	$msg.= sprintf("%-30s",ucfirst(lang_get($dest_language,'PRINTS_TOTAL'))).country_conf_currency()." $total_discounted";

	return $msg;
}

function bill_print_taxes($receipt_id,$destid) {
	$msg="";
	
	$dest_language=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"language",$destid);

	$table='account_mgmt_main';
	$query="SELECT * FROM $table WHERE `id`='$receipt_id'";
	
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;
	
	$arr=mysql_fetch_array($res);
	$taxable=$arr['cash_taxable_amount'];
	$vat_total=$arr['cash_vat_amount'];

	$msg.="\t\t".ucfirst(lang_get($dest_language,'PRINTS_TAXABLE'))." \t".country_conf_currency()." $taxable";
	
	for (reset ($_SESSION['vat']); list ($key, $value) = each ($_SESSION['vat']); ) {
		$vat_rate_name=$_SESSION['vat'][$key]['name'];
		$vat_rate=$_SESSION['vat'][$key]['rate']*100;
		$vat_local=$_SESSION['vat'][$key]['tax'];
		$vat_local=sprintf("%0.2f",$vat_local);
		$msg.="\n\t\t".ucfirst(lang_get($dest_language,'PRINTS_TAX'))." ".$vat_rate_name." (".$vat_rate."%) \t".country_conf_currency()." $vat_local";
	}
	$msg.="\n\t\t".ucfirst(lang_get($dest_language,'PRINTS_TAX_TOTAL'))." \t".country_conf_currency()." $vat_total";
	
	return $msg;
}

function bill_print_receipt_id($receipt_id,$destid) {
	$msg="";
	
	$dest_language=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"language",$destid);

	$table='account_mgmt_main';
	$query="SELECT * FROM $table WHERE `id`='$receipt_id'";

	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	
	$arr=mysql_fetch_array($res);
	$internal_id=$arr['internal_id'];
	$type=$arr['type'];

	if($type==3){
		$msg.=ucfirst(lang_get($dest_language,'PRINTS_INVOICE'))." ".ucfirst(lang_get($dest_language,'PRINTS_NUMBER_ABBR')).": $internal_id";
	} elseif($type==4) {
		$msg.=ucfirst(lang_get($dest_language,'PRINTS_BILL'))." ".ucfirst(lang_get($dest_language,'PRINTS_NUMBER_ABBR')).": $internal_id";
	} elseif($type==5) {
		$msg.=ucfirst(lang_get($dest_language,'PRINTS_RECEIPT'))." ".ucfirst(lang_get($dest_language,'PRINTS_NUMBER_ABBR')).": $internal_id";
	}
	
	return $msg;
}

function bill_quantity($id,$operation){
	$query="SELECT * FROM `orders` WHERE `id`=$id";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;
	
	$arr=mysql_fetch_array($res);

	$max_quantity=$arr['quantity']-$arr['paid'];

	if($operation==1 && $_SESSION['separated'][$id]['topay']>=$max_quantity) return 1;
	if($operation==-1 && $_SESSION['separated'][$id]['topay']<=0) return 2;

	if($operation==1)
		$_SESSION['separated'][$id]['topay']++;
	elseif($operation==-1)
		$_SESSION['separated'][$id]['topay']--;

	return 0;
}

function bill_clear_prices($sourceid){
	// clears the price of every product,
	// so that adding the mod prices starts from zero instead of the precedent price
	$query="SELECT * FROM `orders` WHERE `sourceid`='".$sourceid."' AND `deleted`=0 AND `printed` IS NOT NULL ORDER BY `associated_id` ASC";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;
	
	if(!mysql_num_rows($res)) return ERR_NO_ORDER_FOUND;
	while($arr=mysql_fetch_array($res)){
		$id=$arr['id'];
		$_SESSION['separated'][$id]['price']=0;
	}
	return 0;
}

function bill_select(){
	/*
	name:
	bill_select()
	returns:
	0 - no error
	1 - generic dish with no price found
	2 - internal error clearing prices
	3 - 
	other - mysql error number
	*/
	global $tpl;

	if(!bill_orders_to_print ($_SESSION['sourceid'])) {
		printing_choose(true);
		return 0;
	}
	
	$tpl -> set_waiter_template_file ('bill_select');

	$tmp = navbar_form('form_type','orders.php?command=printing_choose');
	$tpl -> assign ('navbar',$tmp);

	$_SESSION['bill_printed']=0;
	if(order_found_generic_not_priced($_SESSION['sourceid'])) return ERR_GENERIC_ORDER_NOT_PRICED_FOUND;

	$keep_separated = bill_check_keep_separated();

	if($err=bill_clear_prices($_SESSION['sourceid'])) return $err;
	if($err=bill_save_session($_SESSION['sourceid'])) return $err;
	$tmp = bill_method_selector();
	$tpl -> assign ('method',$tmp);
	
	$tmp = bill_type_selection($_SESSION['sourceid']);
	$tpl -> assign ('type',$tmp);
	
	$tmp = discount_form_javascript($_SESSION['sourceid']);
	$tpl -> assign ('discount',$tmp);
	
	$tmp = bill_show_list();
	$tpl -> assign ('orders',$tmp);

	return 0;
}

function bill_select_pos(){
	/*
	name:
	bill_select_pos()
	returns:
	0 - no error
	1 - generic dish with no price found
	2 - internal error clearing prices
	3 - 
	other - mysql error number
	*/
	global $tpl;

	if(!bill_orders_to_print ($_SESSION['sourceid'])) {
		printing_choose_pos(true);
		return 0;
	}
	
	$tpl->set_waiter_template_file ('bill_select_pos');

	$script = "<script language=\"JavaScript\" type=\"text/javascript\">
		$(document).ready(function(){
			$('#tabContent>li:gt(0)').hide();
			$('#tabsNav li:first').addClass('active');
			$('#tabsAndContent #tabsNav li').bind('click', function() {
				$('li.active').removeClass('active');
				$(this).addClass('active');
				var target = $('a', this).attr('href');
				$(target).slideDown(\"fast\").siblings().slideUp(\"fast\");
				return false;
			});
		});	
	</script>";
	$tpl->assign ('script',$script);
	
	$_SESSION['bill_printed']=0;
	if(order_found_generic_not_priced($_SESSION['sourceid'])) return ERR_GENERIC_ORDER_NOT_PRICED_FOUND;

	$keep_separated = bill_check_keep_separated();
	
	if($keep_separated == 1) {
		$tmp = navbar_separatebills_pos('form_type','orders.php?command=printing_choose');
		$tpl->assign ('navbar',$tmp);
	} else {
		$tmp = navbar_form_pos('form_type','orders.php?command=printing_choose');
		$tpl->assign ('navbar',$tmp);		
	}
	
	
	if($err=bill_clear_prices($_SESSION['sourceid'])) return $err;
	if($err=bill_save_session($_SESSION['sourceid'])) return $err;
	$tmp = bill_method_selector();
	$tpl -> assign ('method',$tmp);
	
	$tmp = bill_type_selection_pos($_SESSION['sourceid']);
	$tpl -> assign ('type',$tmp);
	
	$tmp = discount_form_javascript($_SESSION['sourceid']);
	$tpl -> assign ('discount',$tmp);
	
	$tmp = bill_show_list();
	$tpl -> assign ('orders',$tmp);

	return 0;
}


function bill_method_selector(){
	if(!$_SESSION['select_all']){
		$output = '
		<a href="orders.php?sourceid='.$_SESSION['sourceid'].'&amp;command=bill_select_all">'.ucfirst(phr('SELECT_ALL')).'</a>'."\n";
	} else {
		$output = '
		<a href="#" onClick="$.modal.close();loadModal(\'orders.php?command=bill_select\');">'.ucfirst(phr('SEPARATED_BILLS')).'</a>'."\n";
	}
	return $output;
}

function bill_show_list(){
	/*
	prints on the page the list of dishes based on the waiter session
	gets the sourceid var from start.php
	*/

	$output = '';
	$output .= '<table bgcolor="'.COLOR_TABLE_GENERAL.'">';

	$output .= '<thead>
	<tr>
	<th scope=col>'.ucfirst(phr('NAME')).'</th>
	<th scope=col>'.ucfirst(phr('QUANTITY_ABBR')).'</th>
	<th scope=col></th>
	<th scope=col>'.ucfirst(phr('PRICE')).'</th>
	<th scope=col> </th>
	<th scope=col> </th>
	</tr>
	</thead>
	<tbody>';


	$class=COLOR_ORDER_PRINTED;

	ksort($_SESSION['separated']);

	// the next for prints the list and the chosen dishes
	for (reset ($_SESSION['separated']); list ($key, $value) = each ($_SESSION['separated']); ) {
		if($_SESSION['separated'][$key]['extra_care']){
			$classextra=COLOR_ORDER_EXTRACARE;
		} else {
			$classextra=$class;
		}

		$_SESSION['separated'][$key]['finalprice']=$_SESSION['separated'][$key]['price']/$_SESSION['separated'][$key]['quantity']*$_SESSION['separated'][$key]['topay'];


		$output .= '
		<tr bgcolor="'.$class.'">
		<td bgcolor="'.$class.'">
		';
		if(!$_SESSION['separated'][$key]['special'])
			$output .= $_SESSION['separated'][$key]['topay'].' / '.$_SESSION['separated'][$key]['max_quantity'];
			
		$output .= '
		</td>
		<td bgcolor="'.$class.'">'.$_SESSION['separated'][$key]['name'].'</td>
		<td bgcolor="'.$classextra.'">
		';
		if($_SESSION['separated'][$key]['extra_care'])
			$output .= ucfirst(phr('EXTRA_CARE_ABBR'));
		$output .= '
		</td>
		<td bgcolor="'.$class.'">
		';
		if(!$_SESSION['separated'][$key]['special']){
			$output .= sprintf("%0.2f",$_SESSION['separated'][$key]['finalprice']);
		}
		$output .= '
		</td>
		<td bgcolor="'.$class.'">
		';
		if(!$_SESSION['separated'][$key]['special'] && !$_SESSION['select_all']){
			if($_SESSION['separated'][$key]['topay']<$_SESSION['separated'][$key]['max_quantity']){
				$output .= '
				<a href="#" onClick="$.modal.close();loadModal(\'orders.php?command=bill_quantity&keep_separated=1&orderid='.$key.'&operation=1&rndm='.rand(0,100000).'\');">
					<img src="'.IMAGE_PLUS.'" alt="'.ucfirst(phr('PLUS')).' ('.ucfirst(phr('ADD')).')" border=0>
				</a>';
			}
		}
		$output .= '
		</td>
		<td bgcolor='.$class.'>
		';
		if(!$_SESSION['separated'][$key]['special'] && !$_SESSION['select_all']){
			if($_SESSION['separated'][$key]['topay']>0){
				$output .= '
				<a href="#" onClick="$.modal.close();loadModal(\'orders.php?command=bill_quantity&amp;keep_separated=1&amp;orderid='.$key.'&amp;operation=-1&amp;rndm='.rand(0,100000).'\');">
					<img src="'.IMAGE_MINUS.'" alt="'.ucfirst(phr('MINUS')).' ('.ucfirst(phr('ADD')).')" border=0>
				</a>';
			}
		}
		$output .= '
		</td>
		</tr>
		';
	}

	$output .= bill_total();

	$output .= '
	</table>';

	return $output;
}

function bill_save_session($sourceid){
	/*
	Takes every single dish and saves the following info in the waiter session
	saved data:
	price 	(in case of mod adds the price to the associated dish
			and sets 0 to the actual dish)
	name
	special (1 if mod, 0 if not mod)
	quantity
	max_quantity (the max available to pay quantity)
	extra_care
	topay 	(the quantity that the customers asks to pay
			it is set to max_quantity if the customer pays the full bill)
	*/

	$query="SELECT * FROM `orders` WHERE `sourceid`='$sourceid' AND `deleted`=0 AND `printed` IS NOT NULL ORDER BY `associated_id` ASC";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	if(!mysql_num_rows($res)) return ERR_NO_ORDER_FOUND;

	while($arr=mysql_fetch_array($res)){
		$id=$arr['id'];
		$associated_id=$arr['associated_id'];
		debug_msg(__FILE__,__LINE__,"separated order select - associated id (associated_id): ".$arr['associated_id']);
		if(!isset($_SESSION['separated'][$id]['topay'])) {
			$_SESSION['separated'][$id]['topay']=0;
		}

		$_SESSION['separated'][$id]['quantity']=$arr['quantity'];
		if(order_is_mod($id)==2) {
			$_SESSION['separated'][$associated_id]['price']+=$arr['price'];
			$_SESSION['separated'][$id]['price']=0;
			$ingredobj = new ingredient ($arr['ingredid']);
			$modname = $ingredobj -> name ($_SESSION['language']);
			$_SESSION['separated'][$id]['name']="&nbsp;&nbsp;&nbsp;".ucfirst($modname);
			$_SESSION['separated'][$id]['special']=true;
			debug_msg(__FILE__,__LINE__,"separated order select - found mod: ".$_SESSION['separated'][$id]['name']);
			debug_msg(__FILE__,__LINE__,"separated order select -    added price: ".$arr['price']);
		} else {
			$_SESSION['separated'][$id]['max_quantity']=$arr['quantity']-$arr['paid'];
			$_SESSION['separated'][$id]['price']+=$arr['price'];
			$_SESSION['separated'][$id]['dishid']=$arr['dishid'];
			$dishobj = new dish ($arr['dishid']);
			$_SESSION['separated'][$id]['name'] = ucfirst($dishobj -> name ($_SESSION['language']));
			$_SESSION['separated'][$id]['extra_care']=$arr['extra_care'];
			$_SESSION['separated'][$id]['special']=false;
			if($_SESSION['select_all'])
				$_SESSION['separated'][$id]['topay']=$_SESSION['separated'][$id]['max_quantity'];
		}

		if(order_is_mod($id)==1) {
			$_SESSION['separated'][$id]['name']=ucfirst(phr('SERVICE_FEE'));
		}
	}
	return 0;
}


function bill_type_selection_pos($sourceid){
	/*
	sets the bill/invoice type in waiter's session environment
	types:
	1. bill
	2. invoice
	3. receipt
	*/
	for($i=1;$i<=3;$i++) $chk[$i]='';

	if(isset($_SESSION['type'])){
		$type=$_SESSION['type'];
	} else {
		$type=1; // if type is not set, it automatically sets it to 1;
		if(table_is_takeaway($_SESSION['sourceid'])) {
			$type=3; // if type is not set and table is takeaway type is set to 3;
		}
		$_SESSION['type']=$type;
	}

	// Next is a micro-form to set a discount in percent value
	$output = '
	<form action="orders.php" NAME="form_type" method=post>
	<input type="hidden" name="command" VALUE="bill_print">
	<INPUT TYPE="HIDDEN" NAME="keep_separated" VALUE="1">
	<div align="center">
		'.ucfirst(phr('ACCOUNT')).': 
		<table>
			<tr>
				<td rowspan="3">'.ucfirst(phr('TYPE')).':</td>
				<td><input type="radio" name="type" value="1" '.$chk[1].'> '.ucfirst(phr('BILL')).'</td></tr>
			<tr>
				<td><input type="radio" name="type" value="2" '.$chk[2].'> '.ucfirst(phr('INVOICE')).'</td>
			</tr>
			<tr>
				<td><input type="radio" name="type" value="3" '.$chk[3].'> '.ucfirst(phr('RECEIPT')).'</td>
			</tr>
		</table>
	</div>
	</form>
	';	

	$table = new table ($_SESSION['sourceid']);
	$table->fetch_data(true);
	if($cust_id=$table->data['customer']) {
		$cust = new customer ($cust_id);
		$tmp = ucphr('CUSTOMER').': '.$cust->data['surname'];
		$tmp .= ' <a href="orders.php?command=customer_search">'.ucphr('EDIT').'</a>/';
		$tmp .= '<a href="orders.php?command=set_customer&amp;data[customer]=0">'.ucphr('REMOVE').'</a>';
		$tmp .= '<br/>';
	} else {
		//$tmp = '<a href="orders.php?command=customer_search">'.ucfirst(phr('INSERT_CUSTOMER_DATA')).'</a><br/>';
		$tmp = '<input type="text" size="20" value="" id="inputCustomer" onkeyup="lookupCustomer(this.value);" onblur="fillCustomer();" />';
	}
	$output .= $tmp;
	
	return $output;
}
function bill_type_selection($sourceid){
	/*
	sets the bill/invoice type in waiter's session environment
	types:
	1. bill
	2. invoice
	3. receipt
	*/
	for($i=1;$i<=3;$i++) $chk[$i]='';

	if(isset($_SESSION['type'])){
		$type=$_SESSION['type'];
	} else {
		$type=1; // if type is not set, it automatically sets it to 1;
		if(table_is_takeaway($_SESSION['sourceid'])) {
			$type=3; // if type is not set and table is takeaway type is set to 3;
		}
		$_SESSION['type']=$type;
	}

	// Next is a micro-form to set a discount in percent value
	$output = '
	<form action="orders.php" NAME="form_type" method=post>
	<input type="hidden" name="command" VALUE="bill_print">
	<INPUT TYPE="HIDDEN" NAME="keep_separated" VALUE="1">
	<div align="center">
		'.ucfirst(phr('ACCOUNT')).': 
		<table>
			<tr>
				<td rowspan="3">'.ucfirst(phr('TYPE')).':</td>
				<td><input type="radio" name="type" value="1" '.$chk[1].'> '.ucfirst(phr('BILL')).'</td></tr>
			<tr>
				<td><input type="radio" name="type" value="2" '.$chk[2].'> '.ucfirst(phr('INVOICE')).'</td>
			</tr>
			<tr>
				<td><input type="radio" name="type" value="3" '.$chk[3].'> '.ucfirst(phr('RECEIPT')).'</td>
			</tr>
		</table>
	</div>
	</form>
	';	

	$table = new table ($_SESSION['sourceid']);
	$table->fetch_data(true);
	if($cust_id=$table->data['customer']) {
		$cust = new customer ($cust_id);
		$tmp = ucphr('CUSTOMER').': '.$cust->data['surname'];
		$tmp .= ' <a href="orders.php?command=customer_search">'.ucphr('EDIT').'</a>/';
		$tmp .= '<a href="orders.php?command=set_customer&amp;data[customer]=0">'.ucphr('REMOVE').'</a>';
		$tmp .= '<br/>';
	} else {
		$tmp = '<a href="orders.php?command=customer_search">'.ucfirst(phr('INSERT_CUSTOMER_DATA')).'</a><br/>';
	}
	$output .= $tmp;
	
	return $output;
}

function bill_type_set($type){
	if(empty($type)) {
		$type=1;
	}
	$_SESSION['type']=$type;
	return 0;
}

function bill_total(){
	$output = '';
	$total = 0;
	$class = COLOR_TABLE_TOTAL;
	$currencies = new currencies();
	$curr_value = $currencies->list_search_active();
	
	for (reset ($_SESSION['separated']); list ($key, $value) = each ($_SESSION['separated']); ) {
		$total+=$_SESSION['separated'][$key]['finalprice'];
	}
	$output .= '
		<tr bgcolor='.$class.'>
		<td bgcolor='.$class.'></td>
		<td bgcolor='.$class.'>'.ucfirst(phr('TOTAL')).'</td>
		<td bgcolor='.$class.'></td>
		<td bgcolor='.$class.'>'.sprintf("%0.2f",$total).'</td>
		<td bgcolor='.$class.'></td>
		<td bgcolor='.$class.'></td>
		</tr>
		';
	  
	if( SHOW_CHANGE == 1) {
		//mizuko:needed to show exchange...
		for($i = 0; $i < count($curr_value); $i++ ) {
			$output .= '
				<tr bgcolor='.$class.'>
					<td bgcolor='.$class.'></td>
					<td bgcolor='.$class.'>'.$curr_value[$i]['name'].'</td>
					<td bgcolor='.$class.'></td>
					<td bgcolor='.$class.'>'.sprintf("%0.2f",$total/$curr_value[$i]['rate']).'</td>
					<td bgcolor='.$class.'></td>
					<td bgcolor='.$class.'></td>
				</tr>
				';
		}
	}
	if(!isset($_SESSION['discount']) || !isset($_SESSION['discount']['type']) || empty($_SESSION['discount']['type'])) return $output;
	
	if($_SESSION['discount']['type']=="amount") {
		$total_discounted=$total+$_SESSION['discount']['amount'];
		$discount_label="";
		$discount_number=-$_SESSION['discount']['amount'];
	} elseif($_SESSION['discount']['type']=="percent") {
		$total_discounted=$total-$total/100*$_SESSION['discount']['percent'];
		$discount_label=$_SESSION['discount']['percent'].' %';
		$discount_number=$total/100*$_SESSION['discount']['percent'];
	}

	$output .= '
		<tr bgcolor='.$class.'>
		<td bgcolor='.$class.'></td>
		<td bgcolor='.$class.'>&nbsp;'.ucphr('DISCOUNT').' '.$discount_label.'</td>
		<td bgcolor='.$class.'></td>
		<td bgcolor='.$class.'>-'.sprintf("%0.2f",$discount_number).'</td>
		<td bgcolor='.$class.'></td>
		<td bgcolor='.$class.'></td>
		</tr>
		<tr bgcolor='.$class.'>
		<td bgcolor='.$class.'></td>
		<td bgcolor='.$class.'>'.ucphr('TOTAL').'</td>
		<td bgcolor='.$class.'></td>
		<td bgcolor='.$class.'>'.sprintf("%0.2f",$total_discounted).'</td>
		<td bgcolor='.$class.'></td>
		<td bgcolor='.$class.'></td>
		</tr>
		';
	return $output;
}

function bill_reset_confirm() {
	global $tpl;

	$tpl -> set_waiter_template_file ('question');

	$tmp = navbar_form('form1','orders.php?command=printing_choose');
	$tpl -> assign ('navbar',$tmp);
	
	$tmp = ucfirst(phr('RESET_SEPARATED_EXPLAIN')).'
		<br>
		<br><br>

	<FORM ACTION="orders.php" METHOD=POST name="form1">
	<INPUT TYPE="HIDDEN" NAME="command" VALUE="bill_reset">
	<INPUT TYPE="checkbox" name="reset" value="1">
	'.ucfirst(phr('RESET_SEPARATED')).'<br><br>
	</FORM>
	';
	$tpl -> assign ('question',$tmp);
	return 0;
} 

function bill_reset_confirm_pos() {
	global $tpl;

	$tpl -> set_waiter_template_file ('question');

	$tmp = navbar_form_pos('form1','orders.php?command=printing_choose');
	$tpl -> assign ('navbar',$tmp);
	
	$tmp = ucfirst(phr('RESET_SEPARATED_EXPLAIN')).'
		<br>
		<br><br>

	<FORM ACTION="orders.php" METHOD=POST name="form1">
	<INPUT TYPE="HIDDEN" NAME="command" VALUE="bill_reset">
	<INPUT TYPE="checkbox" name="reset" value="1">
	'.ucfirst(phr('RESET_SEPARATED')).'<br><br>
	</FORM>
	';
	$tpl -> assign ('question',$tmp);
	return 0;
}

function bill_reset($sourceid) {
	$query= "UPDATE `orders` SET `paid` = '0' WHERE `sourceid` = '$sourceid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return mysql_errno();

	return 0;
}

?>
