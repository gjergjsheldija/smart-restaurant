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

function printing_choose ($from_bill_print=false) {
	global $tpl;

	if(!takeaway_is_set($_SESSION['sourceid'])) {
		$tmp = '<font color="Red">'.ucfirst(phr('SET_TAKEAWAY_SURNAME_FIRST')).'</font>';
		$tpl -> append ('messages',$tmp);
		orders_list();
		return 0;
	}
	
	$user = new user($_SESSION['userid']);
	
	if(table_is_closed($_SESSION['sourceid']) && (!$user->level[USER_BIT_CASHIER] || $from_bill_print)) {
		table_closed_interface();
		return 0;
	}
	
	$tpl -> set_waiter_template_file ('printing');
	
	$tmp = printing_commands();
	$tpl -> append ('commands',$tmp);
	
	$tmp = navbar_empty();
	$tpl -> assign ('navbar',$tmp);
}

function printing_choose_pos ($from_bill_print=false) {
	global $tpl;

	if(!takeaway_is_set($_SESSION['sourceid'])) {
		$tmp = '<font color="Red">'.ucfirst(phr('SET_TAKEAWAY_SURNAME_FIRST')).'</font>';
		$tpl -> append ('messages',$tmp);
		orders_list();
		return 0;
	}
	
	$user = new user($_SESSION['userid']);
	
	if(table_is_closed($_SESSION['sourceid']) && (!$user->level[USER_BIT_CASHIER] || $from_bill_print)) {
		table_closed_interface_pos();
		return 0;
	}
	
	$tpl -> set_waiter_template_file ('printing');
	
	$tmp = printing_commands_pos();
	$tpl -> append ('commands',$tmp);
	
	$tmp = navbar_empty_pos();
	$tpl -> assign ('navbar',$tmp);
}

function printing_commands_pos(){
	$output='';
	
	$sourceid=$_SESSION['sourceid'];

	if(printing_orders_to_print($sourceid)){
		$output .= '<a href="orders.php?command=print_orders"><img src="'.IMAGE_PRINT.'" height=64 wdth=64><br>'.strtoupper(phr('PRINT_ORDERS')).'</a><br />'."\n";
		$output .= '<br />';
	}

	if( printing_orders_printed_category (2)){
		$output .= '<a href="orders.php?command=print_category&amp;data[category]=2"><b>'.ucfirst(phr('PRINT_GO_2')).'</a></b><br />'."\n";
		$output .= '<br />';
	}

	if(printing_orders_printed_category (3)){
		$output .= '<a href="orders.php?command=print_category&amp;data[category]=3">'.ucfirst(phr('PRINT_GO_3')).'</a><br />'."\n";
		$output .= '<br />';
	}

	$user = new user($_SESSION['userid']);
	
	if ($user->level[USER_BIT_CASHIER]) {
		$output .= '<br />
	<a href="orders.php?command=bill_reset">('.ucfirst(phr('RESET_SEPARATED')).')</a><br />
	<br />';
		if(bill_orders_to_print ($_SESSION['sourceid'])) {
			$output .= '
	<a href="orders.php?command=bill_select_all"><img src="'.IMAGE_PRINT.'"><br>'.ucfirst(phr('PRINT_BILL')).'</a><br />';
		}
	}
	
	return $output;
}

function printing_commands(){
	$output='';
	
	$sourceid=$_SESSION['sourceid'];

	if(printing_orders_to_print($sourceid)){
		$output .= '<a href="orders.php?command=print_orders">'.ucfirst(phr('PRINT_ORDERS')).'</a><br />'."\n";
		$output .= '<br />';
	}

	if( printing_orders_printed_category (2)){
		$output .= '<a href="orders.php?command=print_category&amp;data[category]=2">'.ucfirst(phr('PRINT_GO_2')).'</a><br />'."\n";
		$output .= '<br />';
	}

	if( printing_orders_printed_category (3)){
		$output .= '<a href="orders.php?command=print_category&amp;data[category]=3">'.ucfirst(phr('PRINT_GO_3')).'</a><br />'."\n";
		$output .= '<br />';
	}

	if(bill_orders_to_print ($_SESSION['sourceid'])) {
		$output .= "<a href=\"orders.php?command=bill_select\">".ucfirst(phr('PRINT_SEPARATED_BILLS'))."</a><br />\n";
	}
	
	$user = new user($_SESSION['userid']);
	
	if ($user->level[USER_BIT_CASHIER]) {
		$output .= '
	<br />
	<a href="orders.php?command=bill_reset">('.ucfirst(phr('RESET_SEPARATED')).')</a><br />
	<br />
	';
		if(bill_orders_to_print ($_SESSION['sourceid'])) {
			$output .= '
	<a href="orders.php?command=bill_select_all">'.ucfirst(phr('PRINT_BILL')).'</a><br />
	';
		}
	}
	
	return $output;
}

/**
* Number of orders to be printed
*
* This function reads all the orders associated to a given table
* that have not yet been deleted or printed and that are not suspended and quantify them.
* The returned number includes modifications orders, thus means it could be greater than real but never less than that.
*
* Note: this function will return 0 if a MySQL error occurs.
*
* @param integer $sourceid
* @return integer
*/
function printing_orders_to_print ($sourceid) {
	$query="SELECT * FROM `orders`  WHERE `sourceid`='$sourceid' AND `suspend`='0' AND `printed` IS NULL AND `deleted`='0' ORDER BY id ASC";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	return mysql_num_rows($res);
}

function printer_print_row($arr,$destid){
	$msg= '';
	$dest_language=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"language",$destid);


	if ($arr['dishid']==MOD_ID){
		$modingred=$arr['ingredid'];
		$modingrednumber= $modingred;
		
		$query = "SELECT * FROM `ingreds` WHERE id='$modingrednumber'
		AND ingreds.deleted = '0'";
		$res2=common_query($query,__FILE__,__LINE__);
		if(!$res2) return '';
		$arr2 = mysql_fetch_array ($res2);
		if ($arr2!=FALSE) {
			$ingredobj = new ingredient ($modingrednumber);
			$moddeddishname = $ingredobj -> name ($dest_language);
		}

		if ($arr['operation']==1) {
			$dishname="+";
		} elseif ($arr['operation']==-1) {
			$dishname="-";
		}

		$dishname.=" ".$moddeddishname;

		if($arr['ingred_qty']==1) {
			$dishname.=" ".lang_get($dest_language,'PRINTS_LOT');
		} elseif($arr['ingred_qty']==-1) {
			$dishname.=" ".lang_get($dest_language,'PRINTS_FEW');
		}
	} else {
		$dishobj = new dish ($arr['dishid']);
		$dishname = $dishobj -> name ($dest_language);
	}

	$extra = "";
	if ($arr['extra_care']) {
		$extra.='{size_double}';
		$extra.='{highlight}';
		$extra.=lang_get($dest_language,'PRINTS_ATTENTION')." - ".lang_get($dest_language,'PRINTS_WAIT');
		$extra.='{/highlight}';
		$extra.='{/size_double}';
	}

	if ($arr['dishid']==MOD_ID){
		$msg.='{size_double}';
		$msg.="\n    ".$dishname;
		$msg.='{/size_double}';
	} else {
		if($extra != "") {
			$msg.='{align_center}';
			$msg.="$extra\n";
		}
		$msg.="\n".'{size_double}';
		$msg.= sprintf("%-30s", $dishname ) . $arr['quantity'] . "x".$arr['price']/$arr['quantity'];
		$msg.='{/size_double}';
	}

	$msg = str_replace ("'", "", $msg);

	return $msg;
}

function printing_orders_printed_category ($category) {
	$query="SELECT * FROM `orders` WHERE `sourceid`='".$_SESSION['sourceid']."'";
	$query.=" AND `priority`=$category AND `deleted`=0 AND `printed` IS NOT NULL";
	$query.=" AND `dishid`!=".MOD_ID;
	$query.=" AND `dishid`!=".SERVICE_ID;
	$query.=" AND `suspend`=0";
	$query.=" ORDER BY `associated_id`";
	$res_ord=common_query($query,__FILE__,__LINE__);
	if(!$res_ord) return 0;
	
	return mysql_num_rows($res_ord);
}

function print_category($category){
	/*
	name:
	print_category($category)
	returns:
	0 - no error
	1 - no orders in this category printed
	2 - category already printed
	3 - template parsing error
	other - mysql error number
	*/
	$sourceid = $_SESSION['sourceid'];

	// decided to give back the possibility to print again even if already printed
	
	if(!printing_orders_printed_category($category)) return ERR_NO_ORDERS_PRINTED_CATEGORY;

	$query = "SELECT * FROM `sources` WHERE id='$sourceid'";
	$res2=common_query($query,__FILE__,__LINE__);
	if(!$res2) return ERR_MYSQL;
	
	$row2 = mysql_fetch_array ($res2);
	if ($row2!=FALSE) {
		$otablenum = $row2['name'];
		$ouserid = $row2['userid'];
		$query = "SELECT * FROM `users` WHERE id='$ouserid'";
		$res3=common_query($query,__FILE__,__LINE__);
		if(!$res3) return mysql_errno();
		
		$row3 = mysql_fetch_array ($res3);
		$ousername=$row3['name'];
	}

	switch ($category){
		case 1: $category_name="1";
				break;
		case 2: $category_name="2";
				break;
		case 3: $category_name="3";
				break;
	}
	
	$query="SELECT * FROM `dests` WHERE `deleted`='0'";
	$res_dest=common_query($query,__FILE__,__LINE__);
	if(!$res_dest) return ERR_MYSQL;
	
	while($arr_dest=mysql_fetch_array($res_dest)){
		$destid=$arr_dest['id'];
		$lang=$arr_dest['language'];

		$query="SELECT orders.extra_care, orders.quantity, orders.dishid FROM orders";
		$query.=" JOIN dishes WHERE dishes.id=orders.dishid";
		$query.=" AND orders.sourceid ='".$_SESSION['sourceid']."'";
		$query.=" AND orders.priority =$category";
		$query.=" AND orders.deleted = 0";
		$query.=" AND orders.printed IS NOT NULL";
		$query.=" AND orders.dishid != ".MOD_ID;
		$query.=" AND orders.dishid != ".SERVICE_ID;
		$query.=" AND orders.suspend = 0";
		$query.=" AND dishes.destid ='$destid'";
		$query.=" ORDER BY orders.associated_id";

		$res_ord=common_query($query,__FILE__,__LINE__);
		if(!$res_ord) return ERR_MYSQL;

		$tpl_print = new template;

		$msg="";
		$output['table']=ucfirst(lang_get($lang,'PRINTS_TABLE')).": ".$_SESSION['tablenum'];
		$tpl_print->assign("table", $output['table']);
		$user = new user($_SESSION['userid']);
		$tpl_print->assign("waiter", $user->data['name']);
		$output['priority_go']=ucfirst(lang_get($dest_language,'PRINTS_GO_WITH'))." ";
		$output['priority_go'].='{highlight}';
		$output['priority_go'].=$category_name;
		$output['priority_go'].='{/highlight}';
		$tpl_print->assign("go_priority", $output['priority_go']);
		
		$tpl_print->assign("date", printer_print_date());

		$dest_language=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"language",$destid);

		while($arr=mysql_fetch_array($res_ord)){
			$dishobj = new dish ($arr['dishid']);
			$dishname = $dishobj -> name ($dest_language);
			
			if($arr['extra_care']){
				$extra_care=" - ".'{highlight}'.ucfirst(phr('EXTRA_CARE')).'{/highlight}';
			} else {
				$extra_care = "";
			}
			$output['orders'].=$arr['quantity'].' '.$dishname.' '.$extra_care."\n";
		}
		// strips the last newline that has been put
		$output['orders'] = substr ($output['orders'], 0, strlen($output['orders'])-1);
	
		$tpl_print->assign("orders", $output['orders']);
		$tpl_print->assign("page_cut", printer_print_cut());
				
		if($err = $tpl_print->set_print_template_file($destid,'priority_go')) return $err;
		
		if($err=$tpl_print->parse()) {
			$msg="Error in ".__FUNCTION__." - ";
			$msg.='error: '.$err."\n";
			error_msg(__FILE__,__LINE__,$msg);
			echo nl2br($msg)."\n";
			return 3;
		}
		$tpl_print -> restore_curly ();
		$msg = $tpl_print->getOutput();
		$tpl_print->reset_vars();
		$output['orders']='';
		
		$msg = str_replace ("'", "", $msg);
		if(mysql_num_rows($res_ord)){
			if($err=print_line($destid,$msg)) return $err;
		}
	}

	$catprintedtext=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"catprinted",$sourceid);
	$catprintedtext.=" ".$category;
	$query = "UPDATE `sources` SET `catprinted`='$catprintedtext' WHERE `id` = '$sourceid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	return 0;
}

function print_test_page(){
	$print_tpl = new template;

	$msg="";

	$query="SELECT * FROM `dests` WHERE `deleted`='0'";
	$res_dest=common_query($query,__FILE__,__LINE__);
	if(!$res_dest) return ERR_MYSQL;
	
	while($arr_dest=mysql_fetch_array($res_dest)){
		$destid = $arr_dest['id'];

		$print_tpl -> reset_vars();
		
		$print_tpl -> string = '
		******************
		'.ucphr('PRINTER_TEST_PAGE').'
		******************
		'.ucphr('PRINTER_INTERNAL_NAME').': {tpl_print_name}
		'.ucphr('PRINTING_QUEUE').': {tpl_print_queue}
		'.ucphr('PRINTING_DRIVER').': {tpl_print_driver}
		'.ucphr('PRINTING_TEMPLATE').': {tpl_print_template}
		'.ucphr('DATE').': '.date('d F Y H:i').'
		******************
		'.ucphr('PRINTER_TEST_PAGE_END').'
		******************{end}';

		// next line is needed to make the template parser leave the line without deleting it, so that it gets to the driver level
		$print_tpl -> assign("end", '{page_cut}');
		
		$print_tpl -> assign("tpl_print_queue", $arr_dest['dest']);
		$print_tpl -> assign("tpl_print_name", $arr_dest['name']);
		$print_tpl -> assign("tpl_print_driver", $arr_dest['driver']);
		$print_tpl -> assign("tpl_print_template", $arr_dest['template']);

		if($err=$print_tpl->parse()) {
			$msg="Error in ".__FUNCTION__." - ";
			$msg.='error: '.$err."\n";
			error_msg(__FILE__,__LINE__,$msg);
			echo nl2br($msg)."\n";
			return 3;
		}
		
		$print_tpl -> restore_curly ();
		$msg = $print_tpl -> getOutput();
		
		$msg = str_replace ("'", "", $msg);
		if($err=print_line($destid,$msg)) return $err;
	}

	unset($print_tpl);
	return 0;
}

function print_line($destid,$msg){
	$debug = _FUNCTION_.' - Printing to destid '.$destid.' - line '.$msg.' '."\n"; 
	debug_msg(__FILE__,__LINE__,$debug);

	if($destid=="all") {
		$query="SELECT * FROM `dests` WHERE `bill`=0 AND `invoice`=0 AND `receipt`=0 AND `deleted`='0' ORDER BY id ASC";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
		
		if(!mysql_num_rows($res)) return ERR_NO_PRINT_DESTINATION_FOUND;
		
		while ($arr = mysql_fetch_array ($res)) {
			$destnow=$arr['id'];
			if($result = print_line($destnow,$msg)) return $result;
		}
		return 0;
	}
	
	if(CONF_DEBUG_PRINT_TICKET_DEST) {
		$destname=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests','name',$destid);
		$dest_msg.="destid: $destid";
		$dest_msg.="\ndestname: $destname";
		$msg = eregi_replace ("{[^}]*destination[^}]*}", "$dest_msg", $msg);
		$msg=preg_replace("/\{.*?".'destination'.".*?\}/",$dest_msg,$msg);
	}


	$driver=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests','driver',$destid);

	$msg = driver_apply($driver,$msg);

	if(CONF_DEBUG_PRINT_DISPLAY_MSG) {
		echo "<br>".nl2br(htmlentities($msg))."<br>\n";
	}
	
	if(CONF_DEBUG_DONT_PRINT){
		return 0;
	}

	$dest=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests','dest',$destid);
	$result = print_line_os_chooser($msg,$dest);
	if($result) {
		$error_msg='Printing error: '.$result;
		error_msg(__FILE__,__LINE__,$error_msg);
		return $result;
	}

	return 0;
}

function print_line_os_chooser($value,$dest) {
	switch(strtolower(get_conf(__FILE__,__LINE__,"printing_system"))) {
		case "win":
					$debug = _FUNCTION_.' - calling windows printing function'."\n"; 
					$res = print_line_win($value,$dest);
					break;
		default:
					$debug = _FUNCTION_.' - calling unix printing function'."\n"; 
					$res = print_line_lp($value,$dest);
					break;
	}
	debug_msg(__FILE__,__LINE__,$debug);
	return $res;
}

function print_line_lp($value,$dest) {
	$last_output_line=exec("echo '$value' | lp -d $dest 2>&1",$out_arr,$outerr);
	if($outerr) {
		$error_msg="Printing system error: ".$outerr."\n\tcomplete output: ".var_dump_string($out_arr)."\n\tlast output_line: ".$last_output_line;
		error_msg(__FILE__,__LINE__,$error_msg);
		return ERR_PRINTING_ERROR;
	}
	return 0;
}

function print_line_win($value,$dest) {
	$debug = _FUNCTION_.' - Windows Printing to dest '.$dest.' - line '.$value.' '."\n"; 
	debug_msg(__FILE__,__LINE__,$debug);
		
   $title='SmartRestaurant';
   $handle = printer_open(stripslashes($dest));
   
   if(!$handle) return ERR_COULD_NOT_OPEN_PRINTER;

	$debug = __FUNCTION__.' - Windows Printing to dest '.$dest.' - line '.$value.' '."\n"; 
	debug_msg(__FILE__,__LINE__,$debug);
	
   printer_set_option($handle, PRINTER_MODE, "RAW");
   
   $value = stri_replace ("\n","\n\r",$value);
   
   printer_start_doc($handle, $title);
   printer_start_page($handle);

   if(!printer_write($handle, $value)) return ERR_PRINTING_ERROR;

   printer_end_page($handle);
   printer_end_doc($handle);
   
   printer_close($handle);
   return 0; 
}

function print_set_printed($orderid){
	if(CONF_DEBUG_DONT_SET_PRINTED) return 0;
	
	$query = "UPDATE `orders` SET `printed` = NOW() WHERE `id` = '$orderid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	return 0;
}

function print_ticket($orderid,$deleted=false) {
	$output['orders']='';
	$tpl_print = new template;

	$query = "SELECT * FROM `orders` WHERE `id`='".$orderid."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	if(!mysql_num_rows($res)) return ERR_ORDER_NOT_FOUND;
	$arr=mysql_fetch_array($res);

	$tablenum=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"name",$arr['sourceid']);
	$priority=$arr['priority'];

	$destid=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dishes',"destid",$arr['dishid']);
	$dest=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"dest",$destid);
	$destname=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"name",$destid);
	$dest_language=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"language",$destid);
	
	if($deleted) $tpl_print -> append ("warning", strtoupper(lang_get($dest_language,'PRINTS_DELETED'))."\n");
	if(!$deleted) $tpl_print->assign("gonow", printer_print_gonow($priority,$dest_language));
	
	if (table_is_takeaway($arr['sourceid'])) {
		$takeaway_data = takeaway_get_customer_data($arr['sourceid']);
		$output['takeaway'] = ucfirst(lang_get($dest_language,'PRINTS_TAKEAWAY'))." - ";
		$output['takeaway'] .= $takeaway_data['takeaway_hour'].":".$takeaway_data['takeaway_minute']."\n";
		$output['takeaway'] .= $takeaway_data['takeaway_surname']."\n";
		$tpl_print->assign("takeaway", $output['takeaway']);
	}
	
	$table = new table($arr['sourceid']);
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
	
	$output['table']=ucfirst(lang_get($dest_language,'PRINTS_TABLE')).": ".$tablenum;
	$tpl_print->assign("table", $output['table']);
	$user = new user($_SESSION['userid']);
	$output['waiter']=ucfirst(lang_get($dest_language,'PRINTS_WAITER')).": ".$user->data['name'];
	$tpl_print->assign("waiter", $output['waiter']);
	$output['priority']=ucfirst(lang_get($dest_language,'PRINTS_PRIORITY')).": ".$priority."\n";
	$tpl_print->assign("priority", $output['priority']);
	$output['people']=ucfirst(lang_get($dest_language,'PRINTS_PEOPLE')).": ".table_people_number($arr['sourceid'])."\n";
	$tpl_print->assign("people", $output['people']);
	
	$output['orders'].=printer_print_row($arr,$destid);
	
	$query = "SELECT * FROM `orders` WHERE `associated_id`='".$orderid."' AND `id` != '".$orderid."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;
	while($arr_mods=mysql_fetch_array($res))
		$output['orders'].=printer_print_row($arr_mods,$destid);

	if(CONF_PRINT_BARCODES && $arr['dishid']!=MOD_ID){
		$output['orders'].= print_barcode($arr['associated_id']);
	}
	$tpl_print->assign("orders", $output['orders']);
	
	
	$tpl_print->assign("date", printer_print_date());
	$tpl_print->assign("page_cut", printer_print_cut());
	
	if (table_is_takeaway($arr['sourceid'])) $print_tpl_file='ticket_takeaway';
	else $print_tpl_file='ticket';
	if($err = $tpl_print->set_print_template_file($destid,$print_tpl_file)) return $err;
	
	if($err=$tpl_print->parse()) {
		$msg="Error in ".__FUNCTION__." - ";
		$msg.='error: '.$err."\n";
		echo nl2br($msg)."\n";
		error_msg(__FILE__,__LINE__,$msg);
		return ERR_PARSING_TEMPLATE;
	}
	$tpl_print -> restore_curly ();
	$msg = $tpl_print->getOutput();

	$msg = str_replace ("'", "", $msg);
	if($err= print_line($destid,$msg)) return $err;

	$err = print_set_printed($orderid);
	// there was an error setting orders as printed
	if($err) return ERR_ORDER_NOT_SET_AS_PRINTED;
	
	return 0;
}

function print_orders($sourceid){
	/*
	name:
	print_orders($sourceid)
	returns:
	0 - no error
	1 - no orders to be printed
	2 - template parsing error
	3 - error setting orders printed
	other - mysql error number
	*/
	$sourceid = $_SESSION['sourceid'];
	debug_msg(__FILE__,__LINE__,"BEGIN PRINTING");

	$query = "SELECT * FROM `orders` WHERE `sourceid`='$sourceid' AND `printed` IS NULL AND `suspend`='0' ORDER BY dest_id ASC, priority ASC, associated_id ASC, id ASC";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return mysql_errno();

	if(!mysql_num_rows($res)) return ERR_ORDER_NOT_FOUND;

	$newassociated_id="";

	$tablenum=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"name",$sourceid);

	$tpl_print = new template;
	$output['orders']='';
	$msg="";
	while ($arr = mysql_fetch_array ($res)) {

		$oldassociated_id=$newassociated_id;
		$newassociated_id=$arr['associated_id'];

		if(isset($priority)) $oldpriority=$priority;
		else $oldpriority = 0;
		
		$priority=$arr['priority'];

		if($oldassociated_id!=""){
			$olddestid=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dishes',"destid",
			get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'orders','dishid',$oldassociated_id)
			);
			$olddest=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"dest",$olddestid);
			$olddestname=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"name",$olddestid);
		} else {
			$olddestid = 0;
		}

		$destid=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dishes',"destid",
		get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'orders','dishid',$newassociated_id)
		);
		$dest=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"dest",$destid);
		$destname=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"name",$destid);
		$dest_language=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"language",$destid);

		if ($destid!=$olddestid || $priority!=$oldpriority) {
			if($destid!=$olddestid && $olddestid!="") {
				$tpl_print->assign("date", printer_print_date());
				$tpl_print->assign("gonow", printer_print_gonow($oldpriority,$dest_language));
				$tpl_print->assign("page_cut", printer_print_cut());
				
				// strips the last newline that has been put
				$output['orders'] = substr ($output['orders'], 0, strlen($output['orders'])-1);
	
				if (table_is_takeaway($sourceid)) $print_tpl_file='ticket_takeaway';
				else $print_tpl_file='ticket';
				if($err = $tpl_print->set_print_template_file($olddestid,$print_tpl_file)) return $err;
				
				if($err=$tpl_print->parse()) {
					$msg="Error in ".__FUNCTION__." - ";
					$msg.='error: '.$err."\n";
					echo nl2br($msg)."\n";
					error_msg(__FILE__,__LINE__,$msg);
					return ERR_PARSING_TEMPLATE;
				}
				$tpl_print -> restore_curly ();
				$msg = $tpl_print->getOutput();
				$tpl_print->reset_vars();
				$output['orders']='';
				
				$msg = str_replace ("'", "", $msg);
				if($outerr=print_line($olddestid,$msg)) return $outerr;
				
			} elseif($priority!=$oldpriority && $oldpriority!="") {
				$tpl_print->assign("date", printer_print_date());
				$tpl_print->assign("gonow", printer_print_gonow($oldpriority,$dest_language));
				$tpl_print->assign("page_cut", printer_print_cut());
				
				// strips the last newline that has been put
				$output['orders'] = substr ($output['orders'], 0, strlen($output['orders'])-1);
				
				if (table_is_takeaway($sourceid)) $print_tpl_file='ticket_takeaway';
				else $print_tpl_file='ticket';
				if($err = $tpl_print->set_print_template_file($destid,$print_tpl_file)) return $err;
				
				if($err=$tpl_print->parse()) {
					$msg="Error in ".__FUNCTION__." - ";
					$msg.='error: '.$err."\n";
					error_msg(__FILE__,__LINE__,$msg);
					echo nl2br($msg)."\n";
					return ERR_PARSING_TEMPLATE;
				}
				$tpl_print -> restore_curly ();
				$msg = $tpl_print->getOutput();
				$tpl_print->reset_vars();
				$output['orders']='';
				
				$msg = str_replace ("'", "", $msg);
				if($outerr=print_line($destid,$msg)) return $outerr;
			}

			if(table_is_takeaway($sourceid)) {
				$takeaway_data = takeaway_get_customer_data($sourceid);
				$output['takeaway'] = ucfirst(lang_get($dest_language,'PRINTS_TAKEAWAY'))." - ";
				$output['takeaway'] .= $takeaway_data['takeaway_hour'].":".$takeaway_data['takeaway_minute']."\n";
				$output['takeaway'] .= $takeaway_data['takeaway_surname']."\n";
				$tpl_print->assign("takeaway", $output['takeaway']);
			}
			$output['table']=ucfirst(lang_get($dest_language,'PRINTS_TABLE')).": ".$tablenum;
			$tpl_print->assign("table", $output['table']);
			$user = new user($_SESSION['userid']);
			$output['waiter']=ucfirst(lang_get($dest_language,'PRINTS_WAITER')).": ".$user->data['name'];
			$tpl_print->assign("waiter", $output['waiter']);
			$output['priority']=ucfirst(lang_get($dest_language,'PRINTS_PRIORITY')).": ".$priority."\n";
			$tpl_print->assign("priority", $output['priority']);
			$output['people']=ucfirst(lang_get($dest_language,'PRINTS_PEOPLE')).": ".table_people_number($sourceid)."\n";
			$tpl_print->assign("people", $output['people']);
			
			$table = new table($sourceid);
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
		}
		
		
		$output['orders'].=printer_print_row($arr,$destid);
		$printed_orders[]=$arr['id'];

		if ($newassociated_id!=$oldassociated_id) {
			// if we're in this function, it means that we changed associated_id id
			// and also that mods have been printed on the same sheet

			if(CONF_PRINT_BARCODES && $arr['dishid']!=MOD_ID){
				$output['orders'].= print_barcode($newassociated_id);
			}
		}
		
		if(CONF_PRINT_BARCODES && $arr['dishid']!=MOD_ID){
			$output['orders'].= print_barcode($newassociated_id);
		}
		$tpl_print->assign("orders", $output['orders']);
	}

	$destid=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dishes',"destid",
	get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'orders','dishid',$newassociated_id)
	);
	$dest=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"dest",$destid);
	$destname=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"name",$destid);
	$dest_language=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"language",$destid);
	
	if(CONF_PRINT_BARCODES){
		$tpl_print->assign("barcode", print_barcode($newassociated_id));
	}
	
	$tpl_print->assign("date", printer_print_date());
	$tpl_print->assign("gonow", printer_print_gonow($priority,$dest_language));
	$tpl_print->assign("page_cut", printer_print_cut());

	// strips the last newline that has been put
	$output['orders'] = substr ($output['orders'], 0, strlen($output['orders'])-1);
	
	if (table_is_takeaway($sourceid)) $print_tpl_file='ticket_takeaway';
	else $print_tpl_file='ticket';
	if($err = $tpl_print->set_print_template_file($destid,$print_tpl_file)) return $err;
	
	if($err=$tpl_print->parse()) {
		$err_msg="Error in ".__FUNCTION__." - ";
		$err_msg.='error: '.$err."\n";
		error_msg(__FILE__,__LINE__,$err_msg);
		echo nl2br($err_msg)."\n";
		return ERR_PARSING_TEMPLATE;
	}
	$tpl_print -> restore_curly ();
	$msg = $tpl_print->getOutput();
	$tpl_print->reset_vars();
	$output['orders']='';
	
	$msg = str_replace ("'", "", $msg);
	
	if($outerr=print_line($destid,$msg)) return $outerr;

	foreach ($printed_orders as $val)
		if($err = print_set_printed($val)) return $err;
	// there was an error setting orders as printed
	if($err) return ERR_ORDER_NOT_SET_AS_PRINTED;
	
	return 0;
}

function printer_print_gonow($priority,$dest_language) {
	// function disabled
	return '';
	if($_SESSION['catprinted'][$priority]) return ucfirst(lang_get($dest_language,'PRINTS_GO_NOW'));
	return '';
}

function printer_print_date() {
	$msg = date("j/n/Y G:i",time());
	return $msg;
}

function printer_print_cut(){
	$msg='{page_cut}';
	return $msg;
}

function print_barcode($code){
	$codedata=sprintf("%07d",$code);
	debug_msg(__FILE__,__LINE__,"INFO print_barcode - codedata: $codedata");
	$msg='{align_center}';
	$msg.='{barcode_code39}'."$codedata".'{/barcode_code39}'; //barcode CODE39

	$msg = str_replace ("'", "", $msg);
	return $msg;
}

function find_last_receipt($db,$type,$year){
	if(!$type) return 0;

	$timestart=date("Y")."0000000000";

	$table='account_mgmt_main';
	$query="SELECT * FROM $table WHERE `type`='$type' AND `internal_id`!='' AND `date`>='$timestart'";
	// CRYPTO
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	if(mysql_num_rows($res)){
		while($row=mysql_fetch_array($res)){
			$year_record=substr($row['internal_id'],6,4);
			if($year_record==$year) {
				$internal_id[]=substr($row['internal_id'],0,6);
			}
		}
	} else {
		$internal_id[0]="000000";
	}

	if (sizeof($internal_id)==0) {
		$internal_id[0]="000000";
	}
	mysql_free_result($res);
	rsort($internal_id);
	return $internal_id[0];
}

function receipt_type_waiter2mgmt($type){
	switch($type){
		case 1: $type=4; break;
		case 2: $type=3; break;
		case 3: $type=5; break;
	}
	return $type;
}


function receipt_insert($accountdb,$type){
	// CRYPTO

	// finds the last issued bill or invoice, and increments by one
	// internal invoice/receipt number format is: NNNNNNYYYY
	// where NNNNNN is the incremental number padded with 0s and YYYY is
	// the current 4 digits year.
	$last_internal_id=find_last_receipt($accountdb,$type,date("Y"));
	$internal_id=$last_internal_id+1;
	$internal_id=sprintf("%06d",$internal_id);
	$internal_id.=date("Y");

	// creates the new receipt voice in management db, to be next filled
	// with actual amount values
	$table='account_mgmt_main';
	//mizuko... added $user->data['name'] to have user name
	$user = new user($_SESSION['userid']);
	$query="INSERT INTO $table (`description`,`who`,`internal_id`,`type`,`waiter_income`) VALUES ('".ucfirst(phr('INCOME')).": $internal_id','".$user->data['name']."','$internal_id','$type','1')";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;
	$receipt_id=mysql_insert_id();
	return $receipt_id;
}

function receipt_delete($accountdb,$receipt_id){
	// deletes the receipt voice in management db
	$table='account_mgmt_main';
	$query="DELETE FROM $table WHERE `id`='".$receipt_id."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	return 0;
}


function receipt_update_amounts($accountdb,$total,$receipt_id){
	$total_total=$total['total'];
	$taxable=$total['taxable'];
	$vat=$total['tax'];
	
	$table='account_mgmt_main';
	$query="UPDATE $table SET `waiter_income` = '1',`cash_amount` = '$total_total',`cash_taxable_amount` = '$taxable',`cash_vat_amount` = '$vat' WHERE `id` = '$receipt_id'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	return 0;
}

?>