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


function table_set_customer($sourceid,$input_data){
	if(!isset($input_data['customer'])) return ERR_CUSTOMER_NOT_SPECIFIED;
	
	$query="UPDATE `sources` SET `customer`='".$input_data['customer']."' WHERE `id`='$sourceid'";

	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	return 0;
}



/**
* Marks a table as paid
*
* @param integer $paid The value to be set to the paid field in sources table
* @return integer error code
*/
function table_pay($paid){
	$sourceid = $_SESSION['sourceid'];
	if(order_found_generic_not_priced($sourceid)) return ERR_GENERIC_ORDER_NOT_PRICED_FOUND;

	$total=table_total($_SESSION['sourceid']);
	if(!access_allowed(USER_BIT_MONEY) && $total!=0) {
		access_denied_waiter();
		return ERR_ACCESS_DENIED;
	}
	
	$query = "UPDATE `sources` SET `paid` = '$paid' WHERE `id` = '$sourceid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	return 0;
} 

function table_pay_pos($paid){
	$sourceid = $_SESSION['sourceid'];
	if(order_found_generic_not_priced($sourceid)) return ERR_GENERIC_ORDER_NOT_PRICED_FOUND;

	$total=table_total($_SESSION['sourceid']);
	if(!access_allowed(USER_BIT_MONEY) && $total!=0) {
		access_denied_waiter_pos();
		return ERR_ACCESS_DENIED;
	}
	
	$query = "UPDATE `sources` SET `paid` = '$paid' WHERE `id` = '$sourceid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	return 0;
}

/**
* Clear a table
*
* When a table is cleared all the associated orders are deleted and all the table properties resetted
*
* @return integer error code
*/
function table_clear(){
	$sourceid = $_SESSION['sourceid'];
	if(order_found_generic_not_priced($sourceid)) return ERR_GENERIC_ORDER_NOT_PRICED_FOUND;

	$query = "DELETE FROM `orders` WHERE `sourceid`='$sourceid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	$query="UPDATE `sources` SET
	`userid` = '0'
	,`toclose` = '0'
	,`discount` = '0.00'
	,`paid` = '0'
	,`catprinted` = ''
	,`last_access_time`='0'
	,`last_access_userid`='0'
	,`takeaway_surname`=''
	,`takeaway_time`='0'
	,`customer`='0'
	WHERE `id` = '$sourceid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	return 0;
}


/**
* Interface for closed tables
*
* Template: closed_table.tpl
* Assigns: navbar, pay, clear, total.
*
* @return integer error code
*/
function table_closed_interface() {
	global $tpl;

	if(bill_orders_to_print ($_SESSION['sourceid'])) {
		$_SESSION['select_all']=1;
		$err=bill_select();
		if($err) error_display($err);
		return 0;
	}
	
	$tpl -> set_waiter_template_file ('closed_table');
	
	$paid=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"paid",$_SESSION['sourceid']);
	$total=table_total($_SESSION['sourceid']);
	$discount=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources','discount',$_SESSION['sourceid']);

	if ($total == 0 && $paid==0) {
		$err = table_pay(1);
		status_report ('PAYMENT',$err);
		
		$paid=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"paid",$_SESSION['sourceid']);
	}
		
	$tmp = navbar_tables_only();
	$user = new user($_SESSION['userid']);
	if($user->level[USER_BIT_CASHIER]) $tmp = navbar_empty();
	$tpl -> assign ('navbar',$tmp);
	
	$tmp = '
		'.ucfirst(phr('TABLE_TOTAL_DISCOUNTED')).': <b>'.country_conf_currency(true).' '.$total.'</b>
	';
	if($discount!=0) {
		$discount=sprintf("%01.2f",abs($discount));
		$tmp .= '
		 ('.ucfirst(phr('DISCOUNT')).': '.country_conf_currency(true).' '.$discount.')';
	}
	$tmp .= '<br />'."\n";
	$tpl -> assign ('total',$tmp);

	if($paid){
		$tmp = '
		<FORM ACTION="orders.php" METHOD=POST>
		<INPUT TYPE="HIDDEN" NAME="command" VALUE="clear">
		'.ucfirst(phr('PAID_ALREADY')).'<br/>
		'.ucfirst(phr('EMPTY_TABLE_EXPLAIN')).'
		<INPUT TYPE="submit" value="'.ucfirst(phr('EMPTY_TABLE_BUTTON')).'">
		</FORM>
		';
		$tmp .= '<br />'."\n";
		$tpl -> assign ('clear',$tmp);
	}

	// user is not allowed to pay, so don't display the button
	if(!access_allowed(USER_BIT_MONEY)) return 0;
	
	$tmp = '
		<FORM ACTION="orders.php" METHOD=POST>
		<INPUT TYPE="HIDDEN" NAME="command" VALUE="pay">
		'.ucfirst(phr('PAID_ASK')).'<br/>
		';
	if ($paid){
		$tmp .= '
		<INPUT TYPE="hidden" name="data[paid]" value="0">
		<INPUT TYPE="submit" value="'.ucfirst(phr('NOT_PAID_BUTTON')).'">
		<br/><br/>';
	} else {
		$tmp .= '
		<INPUT TYPE="hidden" name="data[paid]" value="1">
		<INPUT TYPE="submit" value="'.ucfirst(phr('PAID_BUTTON')).'">
		<br/><br/>';
	}
	$tmp .= '
		</FORM>';
	$tmp .= '<br />'."\n";
	$tpl -> assign ('pay',$tmp);

	return 0;
}

function table_closed_interface_pos() {
	global $tpl;

	if(bill_orders_to_print ($_SESSION['sourceid'])) {
		$_SESSION['select_all']=1;
		$err=bill_select_pos();
		if($err) error_display($err);
		return 0;
	}
	
	$tpl -> set_waiter_template_file ('closed_table');
	
	$paid=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"paid",$_SESSION['sourceid']);
	$total=table_total($_SESSION['sourceid']);
	$discount=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources','discount',$_SESSION['sourceid']);

	if ($total == 0 && $paid==0) {
		$err = table_pay(1);
		status_report ('PAYMENT',$err);
		
		$paid=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"paid",$_SESSION['sourceid']);
	}
		
	$tmp = navbar_tables_only_pos();
	$user = new user($_SESSION['userid']);
	if($user->level[USER_BIT_CASHIER]) $tmp = navbar_empty_pos();
	$tpl -> assign ('navbar',$tmp);
	
	$tmp = '
		'.ucfirst(phr('TABLE_TOTAL_DISCOUNTED')).': <b>'.country_conf_currency(true).' '.$total.'</b>
	';
	if($discount!=0) {
		$discount=sprintf("%01.2f",abs($discount));
		$tmp .= '
		 ('.ucfirst(phr('DISCOUNT')).': '.country_conf_currency(true).' '.$discount.')';
	}
	$tmp .= '<br />'."\n";
	$tpl -> assign ('total',$tmp);

	if($paid){
		$tmp = '
		<FORM ACTION="orders.php" METHOD=POST>
		<INPUT TYPE="HIDDEN" NAME="command" VALUE="clear">
		'.ucfirst(phr('PAID_ALREADY')).'<br/>
		'.ucfirst(phr('EMPTY_TABLE_EXPLAIN')).'
		<INPUT TYPE="submit" value="'.ucfirst(phr('EMPTY_TABLE_BUTTON')).'">
		</FORM>
		';
		$tmp .= '<br />'."\n";
		$tpl -> assign ('clear',$tmp);
	}

	// user is not allowed to pay, so don't display the button
	if(!access_allowed(USER_BIT_MONEY)) return 0;
	
	$tmp = '
		<FORM ACTION="orders.php" METHOD=POST>
		<INPUT TYPE="HIDDEN" NAME="command" VALUE="pay">
		'.ucfirst(phr('PAID_ASK')).'<br/>
		';
	if ($paid){
		$tmp .= '
		<INPUT TYPE="hidden" name="data[paid]" value="0">
		<INPUT TYPE="submit" value="'.ucfirst(phr('NOT_PAID_BUTTON')).'">
		<br/><br/>';
	} else {
		$tmp .= '
		<INPUT TYPE="hidden" name="data[paid]" value="1">
		<INPUT TYPE="submit" value="'.ucfirst(phr('PAID_BUTTON')).'">
		<br/><br/>';
	}
	$tmp .= '
		</FORM>';
	$tmp .= '<br />'."\n";
	$tpl -> assign ('pay',$tmp);

	return 0;
}

/**
* Interface for cleared tables
*
* Template: question.tpl
* Assigns: navbar, question, .
*
* @return integer error code
*/
function table_cleared_interface() {
	global $tpl;
	
	$tpl -> set_waiter_template_file ('question');
	
	$tmp = navbar_menu();
	$tpl -> assign ('navbar',$tmp);
	
	$tmp = '
	'.ucfirst(phr('TABLE_HAS_BEEN_CLEARED'));
	$tpl -> assign ('question',$tmp);

	$redirect = redirect_waiter('tables.php');
	$tpl -> append ('scripts',$redirect);
	
	return 0;
}

function table_cleared_interface_pos() {
	global $tpl;
	
	$tpl -> set_waiter_template_file ('question');
	
	$tmp = navbar_menu_pos();
	$tpl -> assign ('navbar',$tmp);
	
	$tmp = '
	'.ucfirst(phr('TABLE_HAS_BEEN_CLEARED'));
	$tpl -> assign ('question',$tmp);

	$redirect = redirect_waiter('tables.php');
	$tpl -> append ('scripts',$redirect);
	
	return 0;
}

/**
* Closes a table
*
* @param integer $sourceid
* @return integer error code
*/
function table_close($sourceid){
	global $tpl;

	$query = "SELECT * FROM `sources` WHERE `id` = '$sourceid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;
	if(!mysql_num_rows($res)) return ERR_TABLE_NOT_FOUND;
			
	$query = "UPDATE `sources` SET `toclose`='1' WHERE `id` = '$sourceid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	$print=false;
	if(get_conf(__FILE__,__LINE__,"print_remaining_tickets_anyway")) {
		$print=true;
	} elseif (table_is_takeaway($sourceid) && get_conf(__FILE__,__LINE__,"print_remaining_tickets_if_takeaway")) {
		$print=true;
	}
	
	if($print && printing_orders_to_print($sourceid)){
		$err = print_orders($sourceid);
		status_report ('ORDERS_PRINT',$err);
	}
	
	return 0;
}

/**
* Table closing confirmation page
*
* Template: question.tpl
* Assigns: question, navbar.
*
* @return integer error code
*/
function table_ask_close() {
	global $tpl;
	
	if(!takeaway_is_set($_SESSION['sourceid'])) {
		$tmp = '<font color="Red">'.ucfirst(phr('SET_TAKEAWAY_SURNAME_FIRST')).'</font>';
		$tpl -> append ('messages',$tmp);
		orders_list();
		return 0;
	}
	
	if(table_is_closed($_SESSION['sourceid'])) {
		table_closed_interface();
		return 0;
	}
	$tpl -> set_waiter_template_file ('question');

	$tmp = '
	<FORM ACTION="orders.php" METHOD=POST name="form1">
	<INPUT TYPE="HIDDEN" NAME="command" VALUE="close">
	'.ucfirst(phr('CLOSE_TABLE_ASK')).'
	</FORM>
	';
	$tpl -> assign ('question',$tmp);

	$tmp = navbar_form('form1','orders.php?command=list');
	$tpl -> assign ('navbar',$tmp);

	return 0;
}

function table_ask_close_pos() {
	global $tpl;
	
	if(!takeaway_is_set($_SESSION['sourceid'])) {
		$tmp = '<font color="Red">'.ucfirst(phr('SET_TAKEAWAY_SURNAME_FIRST')).'</font>';
		$tpl -> append ('messages',$tmp);
		orders_list_pos();
		return 0;
	}
	
	if(table_is_closed($_SESSION['sourceid'])) {
		table_closed_interface_pos();
		return 0;
	}
	$tpl -> set_waiter_template_file ('question');

	$tmp = '
	<FORM ACTION="orders.php" METHOD=POST name="form1">
	<INPUT TYPE="HIDDEN" NAME="command" VALUE="close">
	'.ucfirst(phr('CLOSE_TABLE_ASK')).'
	</FORM>
	';
	$tpl -> assign ('question',$tmp);

	$tmp = navbar_form_pos('form1','orders.php?command=list');
	$tpl -> assign ('navbar',$tmp);

	return 0;
}

/**
* Table reopening confirmation page
*
* Template: question.tpl
* Assigns: question, navbar.
*
* @return integer error code
*/
function table_reopen_confirm() {
	global $tpl;
	
	$tpl -> set_waiter_template_file ('question');

	$tmp = '
	<FORM ACTION="orders.php" METHOD=POST name="form1">
	<INPUT TYPE="HIDDEN" NAME="command" VALUE="reopen">
	'.ucfirst(phr('REOPEN_TABLE_ASK')).'
	</FORM>
	';
	$tpl -> assign ('question',$tmp);

	$tmp = navbar_form('form1','orders.php?command=none');
	$tpl -> assign ('navbar',$tmp);

	return 0;
}

function table_reopen_confirm_pos() {
	global $tpl;
	
	$tpl -> set_waiter_template_file ('question');

	$tmp = '
	<FORM ACTION="orders.php" METHOD=POST name="form1">
	<INPUT TYPE="HIDDEN" NAME="command" VALUE="reopen">
	'.ucfirst(phr('REOPEN_TABLE_ASK')).'
	</FORM>
	';
	$tpl -> assign ('question',$tmp);

	$tmp = navbar_form_pos('form1','orders.php?command=none');
	$tpl -> assign ('navbar',$tmp);

	return 0;
}

/**
* Table reopening
*
* @return integer error code
*/
function table_reopen($sourceid) {
	global $tpl;
	
	$query = "SELECT * FROM `sources` WHERE `id` = '$sourceid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;
	if(!mysql_num_rows($res)) return ERR_TABLE_NOT_FOUND;
			
	$query = "UPDATE `sources` SET `toclose`='0',`paid`='0' WHERE `id` = '$sourceid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	return 0;
}

/**
* Calculates total with discounts
*
* This function reads all the orders associated to a given table
* and sums their price field values.
* After this the value is formatted with 2 decimal places and returned.
*
* The returned value includes discounts.
*
* Note: this function will return 0 if a MySQL error occurs.
*
* @param integer $sourceid
* @return string Total value formatted
*/
function table_total($sourceid){
	$total=table_total_without_discount($sourceid);
	
	$query="SELECT * FROM `sources` WHERE `id`='".$sourceid."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;
	
	$arr=mysql_fetch_array($res);
	$discount=$arr["discount"];

	$total=$total+$discount;
	$total=sprintf("%01.2f",$total);

	return $total;
}

/**
* Calculates total without discount
*
* This function reads all the orders associated to a given table
* and sums their price field values.
* After this the value is formatted with 2 decimal places and returned.
*
* Note: this function will return 0 if a MySQL error occurs.
*
* @param integer $sourceid
* @return string Total value formatted
*/
function table_total_without_discount($sourceid){
	$total=0;

	$query ="SELECT * FROM `orders` WHERE `sourceid`='$sourceid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	while ($arr = mysql_fetch_array ($res)) {
		$total=$total+$arr['price'];
	}
	$total=sprintf("%01.2f",$total);

	return $total;
}

/**
* Association confirmation page
*
* Creates navigation bar with home, abort to service fee or orders, ok button.
* Also displays message asking for association.
*
* Template: question.tpl
* Assigns: question, navbar.
*
* @return integer error code
*/
function table_ask_association() {
	global $tpl;
	
	if (table_is_takeaway($_SESSION['sourceid'])) {
		$err = table_associate ();
		status_report ('ASSOCIATION',$err);
		
		orders_list ();
		return 0;
	}

	
	$tpl -> set_waiter_template_file ('question');

	$tmp = '
	<table>
		<tr>
			<td width=35>
				<a href="tables.php"><img src="'.IMAGE_MENU.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0></a>
			</td>
			<td width=35>
			</td>
			<td width=35>
			</td>
			<td width=35>
			</td>
			<td width=35>';
	if(get_conf(__FILE__,__LINE__,"service_fee_use"))
		$tmp .= '
				<a href="orders.php?command=service_fee"><img src="'.IMAGE_NO.'" alt="'.ucfirst(phr('NO')).'" border=0></a>';
	else 	$tmp .= '
				<a href="orders.php"><img src="'.IMAGE_NO.'" alt="'.ucfirst(phr('NO')).'" border=0></a>';
	$tmp .= '
			</td>
			<td width=35>
				<a href="orders.php?command=associate"><img src="'.IMAGE_YES.'" alt="'.ucfirst(phr('YES')).'" border=0></a>
			</td>
		</tr>
	</table>
	';
	$tpl -> assign ('navbar',$tmp);
	
	$tmp = ucfirst(phr('ASSOCIATE_ASK')).'<br/>'."\n";
	$tpl -> assign ('question',$tmp);

	return 0;
}

function table_ask_association_pos() {
	global $tpl;
	
	if (table_is_takeaway($_SESSION['sourceid'])) {
		$err = table_associate ();
		status_report ('ASSOCIATION',$err);
		
		orders_list ();
		return 0;
	}

	
	$tpl -> set_waiter_template_file ('question');

	$tmp = '
	<table>
		<tr>
			<td width=35>
				<a href="tables.php"><img src="'.IMAGE_MENU.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0></a>
			</td>
			<td width=35>
			</td>
			<td width=35>
			</td>
			<td width=35>
			</td>
			<td width=35>';
	if(get_conf(__FILE__,__LINE__,"service_fee_use"))
		$tmp .= '
				<a href="orders.php?command=service_fee"><img src="'.IMAGE_NO.'" alt="'.ucfirst(phr('NO')).'" border=0></a>';
	else 	$tmp .= '
				<a href="orders.php"><img src="'.IMAGE_NO.'" alt="'.ucfirst(phr('NO')).'" border=0></a>';
	$tmp .= '
			</td>
			<td width=35>
				<a href="orders.php?command=associate"><img src="'.IMAGE_YES.'" alt="'.ucfirst(phr('YES')).'" border=0></a>
			</td>
		</tr>
	</table>
	';
	$tpl -> assign ('navbar',$tmp);
	
	$tmp = ucfirst(phr('ASSOCIATE_ASK')).'<br/>'."\n";
	$tpl -> assign ('question',$tmp);

	return 0;
}

/**
* Associates table
*
* This function checks if the opened table is already associated to a waiter.
* If this is not the case, it associates it to the working waiter.
*
* @return integer error code
*/
function table_associate(){
	// another waiter already is associated to the source
	if (table_is_associated()) return ERR_TABLE_ALREADY_ASSOCIATED;

	$query = "UPDATE `sources` SET `userid` = '".$_SESSION['userid']."' WHERE `id` = '".$_SESSION['sourceid']."'";
	$res=common_query ($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL_ERROR;

	return 0;
}

/**
* Checks if a table is associated to a waiter
*
* Returns the userid field value of the currently opened table.
* It returns zero on mysql error, too (false not associated).
*
* @return integer Waiterid field value (0 if not associated)
*/
function table_is_associated() {
	$query = "SELECT * FROM `sources` WHERE `id`='".$_SESSION['sourceid']."'";
	$res=common_query ($query,__FILE__,__LINE__);
	if(!$res) return 0;
	
	$arr = mysql_fetch_array($res);
	return $arr['userid'];
}

/**
* Checks if a table is closed
*
* It returns zero on mysql error, too (false not closed).
*
* @param integer $sourceid
* @return integer
*/
function table_is_closed($sourceid) {
	if($cache_out=$GLOBALS['cache_var'] -> get ('sources',$sourceid,'toclose')) return $cache_out;
		
	$query = "SELECT `toclose` FROM `sources` WHERE `id`='".$sourceid."'";
	$res=common_query ($query,__FILE__,__LINE__);
	if(!$res) return 0;
	
	$arr = mysql_fetch_array($res);
	$GLOBALS['cache_var'] -> set ('sources',$sourceid,'toclose',$arr['toclose']);
	return $arr['toclose'];
}

/**
* Dissociates a table from its waiter
*
* First check if it is allowed to dissociate tables (disassociation_allow conf value)
* If dissociation is allowed update table sources setting userid to 0.
*
* @return integer error code
*/
function table_dissociate(){
	if (!get_conf(__FILE__,__LINE__,"disassociation_allow")) return ERR_NOT_ALLOWED_TO_DISSOCIATE;

	$query = "UPDATE `sources` SET `userid` = '0' WHERE `id` = '".$_SESSION['sourceid']."'";
	$res=common_query ($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	return 0;
}

/**
* Check if the given table is takeaway
*
* This function reads the takeaway field value in the sources table and returns it.
*
* @param integer $table_id
* @return integer takeaway value from sources table
*/
function table_is_takeaway($table_id) {
	$takeaway=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"takeaway",$table_id);

	return $takeaway;
}

/**
* Check if the given table is takeaway
*
* This function reads the takeaway field value in the sources table and returns it.
*
* @param integer $table_id
* @return integer takeaway value from sources table
*/
function table_exists($sourceid) {
	if($cache_out=$GLOBALS['cache_var'] -> get ('sources',$sourceid,'id')) return $cache_out;

	$query="SELECT `toclose` FROM `sources` WHERE `id`='$sourceid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;
	
	$arr=mysql_fetch_assoc($res);
	
	$GLOBALS['cache_var'] -> set ('sources',$sourceid,'toclose',$arr['toclose']);
	return mysql_num_rows($res);
}

/**
* Suggests a command for orders.php page
*
* this function analyse the following elements:
* table open/closed
* Waiter cashier or not
* Table is paid
* Waiter is associated to the table
* Table is empty
* 
* And suggests a proper command to be executed.
*
* @param integer $sourceid
* @return string command
*/
function table_suggest_command($sourceid) {
	$tbl = new table ($sourceid);
	
	$paid=$tbl -> data['paid'];
	$tableclosed=$tbl -> data['toclose'];
	$owneruserid=$tbl -> data ['userid'];
	$empty = $tbl -> is_empty();

	$user = new user($_SESSION['userid']);
	
	if ($tableclosed && $user->level[USER_BIT_CASHIER] && !$paid){
		$command="list";
	} elseif ($tableclosed && $user->level[USER_BIT_CASHIER] && $paid){
		$command="list";
	} elseif ($tableclosed && !$user->level[USER_BIT_CASHIER] && !$paid) {
		$command="closed";
	} elseif ($tableclosed && !$user->level[USER_BIT_CASHIER] && $paid) {
		$command="closed";
	} elseif ($_SESSION['userid']==$owneruserid){
		$command="list";
	} elseif(!$empty && $owneruserid==0) {
		if(get_conf(__FILE__,__LINE__,'association_automatic')) $command="associate";
		else $command="ask_association";
	} elseif ($owneruserid==0 && !$tableclosed && $empty) {
		if(get_conf(__FILE__,__LINE__,'association_automatic')) $command="associate";
		else $command="ask_association";
	} else {
		$command="list";
	}
	return $command;
}

/**
* Creates the top line (table - people)
*
* First the table name is taken out from source table,
* then it looks for the service fee orders associated to the table and sums them.
* This second step is active only if the conf value service_fee_use is not zero
* Returns the line formatted like: Table TABLE [- NUMBER people]
*
* @param integer $sourceid
* @return string top_line_string
*/
function table_people_number_line ($sourceid) {
	$output = '';
	$query="SELECT * FROM `sources` WHERE `id`='".$sourceid."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return '';

	if ($arr=mysql_fetch_array ($res)) {
		$_SESSION['tablenum']=$arr['name'];

		if(categories_orders_present ($sourceid,2) && !categories_printed ($sourceid,2)) $toprint_2=true;
		if(categories_orders_present ($sourceid,3) && !categories_printed ($sourceid,3)) $toprint_3=true;
		
		$output .= '<table><tr>';
		
		if($toprint_2) $output .= '<td align="left" width="10" bgcolor="'.COLOR_ORDER_PRIORITY_2.'">&nbsp;&nbsp;</td>';
		
		$output .= '<td align="center">';
		$output .= ucfirst(phr('TABLE'))." ".$_SESSION['tablenum'];

		if($sourceid && get_conf(__FILE__,__LINE__,'service_fee_use')) {
			$service_quantity=table_people_number($sourceid);
			$output .= " - $service_quantity ".ucfirst(phr('PEOPLE'));
		}
		
		$output .= '</td>';
		
		if($toprint_3) $output .= '<td align="right" width="10" bgcolor="'.COLOR_ORDER_PRIORITY_3.'">&nbsp;&nbsp;</td>';
		
		$output .= '</tr></table>';
		
		//$output .= "<br/>\n";
	}
	unset($res);
	unset($arr);
	unset($service_quantity);

	return $output;
}

function table_people_number ($sourceid) {
	$query="SELECT SUM(quantity) as quantity FROM `orders` WHERE `sourceid`='".$sourceid."' AND `dishid`='".SERVICE_ID."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	if($arr = mysql_fetch_array ($res)) $qty = $arr['quantity'];
	if(!$qty) $qty = 0;
	
	return $qty;
}

/**
* Calculates the remaining time before a table is unlocked
*
* When a table is accessed by anyone, it cannot be accessed anymore for a certain period of time defined in
* the lock_time configuration value.
*
* This function gets the last access time from the db table sources (field last_access_time) and subtracts it
* from the current timestamp.
* Compating the result with the confugred lock_time gives you the remaining lock time.
*
* The returned time is in seconds and cannot be less than zero.
*
* @param integer $sourceid
* @return integer Remaining time in seconds
*/
function table_lock_remaining_time($sourceid) {
	$timestamp_now=date("YmdHis",time());
	$lock_time=get_conf(__FILE__,__LINE__,"lock_time");
	
	$query="SELECT `last_access_time` FROM `sources` WHERE `id`='".$sourceid."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;
	
	$arr=mysql_fetch_array($res);

	$last_access_time=$arr['last_access_time'];

	$elapsed_time=$timestamp_now-$last_access_time;
	
	$remaining_time=$lock_time-$elapsed_time;
	
	if ($remaining_time<0) $remaining_time=0;
	
	return $remaining_time;
}


/**
* Checks if the table is locked
*
* This function makes cross check to authorize a user to open a table.
*
* The cross check consists of user id check, table owner, lock time expiration.
* It also corrects wrong last_access_time values in the future, by setting them now.
*
* @param integer $sourceid
* @return integer 0 if table is not locked, other on locke table or on mysql error
*/
function table_lock_check($sourceid) {

	$timestamp_now=date("YmdHis",time());
	$lock_time=get_conf(__FILE__,__LINE__,"lock_time");
	
	$query="SELECT * FROM `sources` WHERE `id`='".$sourceid."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;
	
	$arr=mysql_fetch_array($res);

	$last_access_time=$arr['last_access_time'];
	$last_access_userid=$arr['last_access_userid'];

	$elapsed_time=$timestamp_now-$last_access_time;

	if($elapsed_time<0){
		// The signed time is in the future, we correct it by setting it as if lock time has expired.
		
		$query="UPDATE `sources` SET `last_access_time` = NULL , `last_access_userid` = '".$_SESSION['userid']."' WHERE `id` = '".$sourceid."' LIMIT 1";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
	
	} elseif($elapsed_time>$lock_time){
		//lock time has expired, we just sign the table as ours.
		
		$query="UPDATE `sources` SET `last_access_time` = NULL , `last_access_userid` = '".$_SESSION['userid']."' WHERE `id` = '".$sourceid."' LIMIT 1";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;

	} elseif($elapsed_time<=$lock_time && $last_access_userid==$_SESSION['userid']){
		// lock time has not yet expired, but the waiter is the owner of the table,
		// so we just update the lock_time.
		
		$query="UPDATE `sources` SET `last_access_time` = NULL WHERE `id` = '".$sourceid."' LIMIT 1";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;

	} elseif($elapsed_time<=$lock_time && $last_access_userid!=$_SESSION['userid']){
		// lock time has not yet expired, and the waiter is not the owner.
		// we return ERR_TABLE_IS_LOCKED, to say that there was an errror.
		
		return ERR_TABLE_IS_LOCKED;
	}

	return 0;
}

function tables_list_all($cols=1,$show=0,$quiet=true){
	/*
	$show possible values:
	0 shows all but takeaway
	1 shows takeaway
	2 shows mine
	*/
	
	$output = '';
	
	if(!$quiet) {
		$query = "SELECT * FROM `sources`";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return '';
		if(!mysql_num_rows ($res)) return ucphr('NO_TABLE_FOUND')."<br/>\n";
	}
	
	switch($show) {
		case 0:
			$query = "SELECT `sources`.`id`, `name`, `userid`, `toclose`,`locktouser`, `sources`.`paid`, `orders`.`id` AS `order` FROM `sources` LEFT JOIN `orders` ON `sourceid`=`sources`.`id` WHERE `takeaway` = '0'";
			$query .= " AND `visible` = '1'";
			$query .= " AND ( `locktouser` = '" . $_SESSION['userid'] . "' ) ";	
			$query .= " AND ( `userid` = '" . $_SESSION['userid'] . "' OR `userid` = '0' )";						
			$query .= " GROUP BY `sources`.`id` ASC";
			$query .= " ORDER BY `sources`.`ordernum` ASC";
			break;
		case 1:
			$query = "SELECT `sources`.`id`, `name`, `userid`, `toclose`,`locktouser`,  `sources`.`paid`, `orders`.`id` AS `order` FROM `sources` LEFT JOIN `orders` ON `sourceid`=`sources`.`id` WHERE `takeaway` = '1'";
			$query .= " AND `visible` = '1'";			
			$query .= " AND ( `locktouser`  = '" . $_SESSION['userid'] . "' ) ";		
			$query .= " AND ( `userid` = '" . $_SESSION['userid'] . "' OR `userid` = '0' )";					
			$query .= " GROUP BY `sources`.`id` ASC";
			$query .= " ORDER BY `sources`.`ordernum` ASC";
			break;
		case 2:
			$query="SELECT `sources`.`id`, `name`, `userid`, `toclose`,`locktouser`,  `sources`.`paid`, `orders`.`id` AS `order` FROM `sources` LEFT JOIN `orders` ON `sourceid`=`sources`.`id` WHERE `userid`='".$_SESSION['userid']."'";
			$query .= " AND `visible` = '1'";
			$query .= " AND ( `locktouser`  = '" . $_SESSION['userid'] . "' ) ";	
			$query .= " AND ( `userid` = '" . $_SESSION['userid'] . "' OR `userid` = '0' )";						
			$query .= " GROUP BY `sources`.`id` ASC";
			$query .= " ORDER BY `sources`.`ordernum` ASC";
			break;
	}
	
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return '';
	
	$therearerecords=mysql_num_rows ($res);
	
	if(!$therearerecords) return '';
	
	$queryUser = "SELECT `users`.name FROM `users` ";
	$queryUser .="WHERE `users`.id NOT IN ";
	$queryUser .="( SELECT `sources`.userid FROM `sources` ) ";
	$queryUser .="AND level = '515' AND deleted = '0' AND `users`.id = ". $_SESSION['userid'];
	
	$resUser=common_query($queryUser,__FILE__,__LINE__);
	if(!$resUser) return '';
	
	$therearerecordsUser=mysql_num_rows ($resUser);	
	
	switch($show) {
	case 0:
		$output .= ucfirst(phr('ALL_TABLES'));
		break;
	case 1:
		$output .= ucfirst(phr('TAKEAWAY_TABLES'));
		break;
	case 2:
		$output .= ucfirst(phr('MY_TABLES'));
		break;
	}

	$output .= ':
<table cellspacing="2" bgcolor="'.COLOR_TABLE_GENERAL.'" width="100%">
	<tbody>'."\n" ;
	while ($arr = mysql_fetch_array ($res)) {
		$output .= '	<tr>'."\n";
		for ($i=0;$i<$cols;$i++){

			$output .= tables_list_cell($arr);
			if($i != ($cols - 1)) {
				$arr = mysql_fetch_array ($res);
			}
		}
		$output .= '	</tr>'."\n";
	}
	$output .= '	</tbody>
</table>'."\n";

	if($therearerecordsUser) {
		$output .='<p align="left"><a href="../pos/dailyincome.php"><img src="../images/newclose.png"></a></p>';	
	}	
	
	return $output;
} 

function tables_list_all_pos($cols=1,$show=0,$quiet=true){
	/*
	$show possible values:
	0 shows all but takeaway
	1 shows takeaway
	2 shows mine
	*/
	$output = '';
	
	if(!$quiet) {
		$query = "SELECT * FROM `sources`";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return '';
		if(!mysql_num_rows ($res)) return ucphr('NO_TABLE_FOUND')."<br/>\n";
	}
	
	switch($show) {
		case 0:
			$query = "SELECT `sources`.`id`, `name`, `userid`, `toclose`,`locktouser`, `sources`.`paid`, `orders`.`id` AS `order` FROM `sources` LEFT JOIN `orders` ON `sourceid`=`sources`.`id` WHERE `takeaway` = '0'";
			$query .= " AND `visible` = '1'";
			$query .= " AND ( `locktouser` = '" . $_SESSION['userid'] . "' ) ";							
			$query .= " AND ( `userid` = '" . $_SESSION['userid'] . "' OR `userid` = '0' )";							
			$query .= " GROUP BY `sources`.`id` ASC";
			$query .= " ORDER BY `sources`.`ordernum` ASC";
			break;
		case 1:
			$query = "SELECT `sources`.`id`, `name`, `userid`, `toclose`,`locktouser`,  `sources`.`paid`, `orders`.`id` AS `order` FROM `sources` LEFT JOIN `orders` ON `sourceid`=`sources`.`id` WHERE `takeaway` = '1'";			
			$query .= " AND `visible` = '1'";
			$query .= " AND ( `locktouser` = '" . $_SESSION['userid'] . "' ) ";
			$query .= " AND ( `userid` = '" . $_SESSION['userid'] . "' OR `userid` = '0' )";											
			$query .= " GROUP BY `sources`.`id` ASC";
			$query .= " ORDER BY `sources`.`ordernum` ASC";
			break;
		case 2:
			$query="SELECT `sources`.`id`, `name`, `userid`, `toclose`,`locktouser`,  `sources`.`paid`, `orders`.`id` AS `order` FROM `sources` LEFT JOIN `orders` ON `sourceid`=`sources`.`id` WHERE `userid`='".$_SESSION['userid']."'";
			$query .= " AND `visible` = '1'";
			$query .= " AND ( `locktouser` = '" . $_SESSION['userid'] . "' ) ";	
			$query .= " AND ( `userid` = '" . $_SESSION['userid'] . "' OR `userid` = '0' )";										
			$query .= " GROUP BY `sources`.`id` ASC";
			$query .= " ORDER BY `sources`.`ordernum` ASC";
			break;
	}
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return '';
	
	$therearerecords=mysql_num_rows ($res);
	
	if(!$therearerecords) return '';
	
	switch($show) {
	case 0:
		$output .= '<div class="tabbertab">';
		$output .= '<h4>'. ucfirst(phr('ALL_TABLES')) . '</h4>';
		break;
	case 1:
		$output .= '<div class="tabbertab">';		
		$output .= '<h4>'. ucfirst(phr('TAKEAWAY_TABLES')) . '</h4>';
		break;
	case 2:
		$output .= '<div class="tabbertab tabbertabdefault">';			
		$output .= '<h4>'. ucfirst(phr('MY_TABLES')) . '</h4>';
		break;
	}

	$output .= ':
	<table cellspacing="2" bgcolor="'.COLOR_TABLE_GENERAL.'" width="100%">
	<tbody>'."\n";
	while ($arr = mysql_fetch_array ($res)) {
		$output .= '	<tr>'."\n";
		for ($i=0;$i<$cols;$i++){

			$output .= tables_list_cell_pos($arr);
			if($i != ($cols - 1)) {
				$arr = mysql_fetch_array ($res);
			}
		}
		$output .= '	</tr>'."\n";
	}
	$output .= '	</tbody>
	</table>
	</div>'."\n";
	
	return $output;
}

function waiter_income_pos() {
	

		
	$queryUser = "SELECT `users`.name FROM `users` ";
	$queryUser .="WHERE `users`.id NOT IN ";
	$queryUser .="( SELECT `sources`.userid FROM `sources` ) ";
	$queryUser .="AND level = '515' AND deleted = '0' AND `users`.id = ". $_SESSION['userid'];
	
	$resUser=common_query($queryUser,__FILE__,__LINE__);
	if(!$resUser) return '';
	
	$therearerecordsUser=mysql_num_rows ($resUser);	
	
	if($therearerecordsUser) {
		$user = new user($_SESSION['userid']);
		$dateStart = date('Y-m-d') . " 00:00:01";
		$dateEnd = date('Y-m-d') . " 23:59:00";

		$userName = $user->data['name'];
		
		$table='account_mgmt_main';
		$queryMoney = "SELECT date, who, description, cash_amount ";
		$queryMoney .= "FROM `account_mgmt_main`";
		$queryMoney .= "WHERE who = '" . $userName . "' AND ";
		$queryMoney .= "date > '" . $dateStart . "' AND date < '" . $dateEnd . "'";

		$resMoney=common_query($queryMoney,__FILE__,__LINE__);
		if(!$resMoney) return '';

		$therearerecordsMoney=mysql_num_rows ($resMoney); 

		if($therearerecordsMoney) {		
			$output = '<div class="tabbertab">';		
			$output .= '<h2>'. ucfirst(phr('INCOME')) . '</h2>';			
			$output .= '<h2>' . ucfirst(phr('TOTAL_WAITER_INCOME')) . ' ' . $userName .'<br>'
					. ucfirst(phr('FROM')) . ' ' . $dateStart .' '
					. ucfirst(phr('TO')) . ' ' . $dateEnd .'</h2>
				<table id="table_income">
				<thead>
					<tr>
						<th height="31" width="100"><strong>'. ucfirst(phr('TIME')) .'</strong></th>
						<th width="145"><div align="right"><strong>'. ucfirst(phr('BILL')) .'</strong></div></th>
						<th width="80"><div align="right"><strong>'. ucfirst(phr('AMOUNT')) .'</strong></div></th>
					</tr>
				</thead>
				<tbody>';
				while ($arr = mysql_fetch_array ($resMoney)) { 
					$output .= '	
					 <tr>
					  	<td width="200"><div align="left">'.$arr['date'] .'</div></td>
					    <td width="145"><div align="right">'. $arr['description'].'</div></td>
					    <td width="80"><div align="right">'. $arr['cash_amount'] .'</div></td>
					  </tr>';
					$amounTotal+=$arr['cash_amount'];
				} 
				$output .= '
					<thead>
						<tr>
							<th><div align="left">'.ucfirst(phr('TOTAL_WAITER_INCOME')) . ' ' . $userName . '</div></th>
							<th></th>
							<th><div align="right"><strong>'. $amounTotal.'</strong></div></th>
						</tr>
					</thead>';
				$output .= '</tbody></table></div>';
		}
	}
	return $output;	
	
}

function tables_list_cell($row){
	$output = '';

	$sourceid=$row['id'];
	$tablenum=$row['name'];
	$owneruserid=$row['userid'];
	$tableclosed=$row['toclose'];
	$paid=$row['paid'];
	//RTG: in the master query we get the num of the order associated to this table,
	//if any, so we don't need create a table object just to see if is empty.
	$empty = $row['order']==null || $row['order'] == 0;

	$user = new user($_SESSION['userid']);
	
	if ($user->level[USER_BIT_CASHIER] && order_found_generic_not_priced($sourceid)){
		$msg=ucfirst(phr('GENERIC_NOT_PRICED'));
		$class=COLOR_TABLE_GENERIC_NOT_PRICED;
	} elseif ($tableclosed && $user->level[USER_BIT_CASHIER] && !$paid){
		$msg=ucfirst(phr('CLOSED'));
		$class=COLOR_TABLE_CLOSED_OPENABLE;
	} elseif ($tableclosed && $user->level[USER_BIT_CASHIER] && $paid){
		$msg=ucfirst(phr('PAID'));
		$class=COLOR_TABLE_CLOSED_OPENABLE;
	} elseif ($tableclosed && !$user->level[USER_BIT_CASHIER] && !$paid) {
		$msg=ucfirst(phr('CLOSED'));
		$class=COLOR_TABLE_NOT_OPENABLE;
	} elseif ($tableclosed && !$user->level[USER_BIT_CASHIER] && $paid) {
		$msg=ucfirst(phr('PAID'));
		$class=COLOR_TABLE_NOT_OPENABLE;
	} elseif ($_SESSION['userid']==$owneruserid){
		$msg=ucfirst(phr('OPEN'));
		$class=COLOR_TABLE_MINE;
		if(categories_orders_present ($sourceid,2) && !categories_printed ($sourceid,2)) $class=COLOR_ORDER_PRIORITY_2;
		if(categories_orders_present ($sourceid,3) && !categories_printed ($sourceid,3)) $class=COLOR_ORDER_PRIORITY_2;
	} elseif(!$empty && $owneruserid==0) {
		$msg=ucfirst(phr('TO_BE_ASSOCIATED'));
		$class=COLOR_TABLE_CLOSED_OPENABLE;
	} elseif ($owneruserid==0 && !$tableclosed && $empty) {
		$msg=ucfirst(phr('FREE'));
		$class=COLOR_TABLE_FREE;
	} else {
		$user = new user ($owneruserid);
		$ownerusername=$user->data['name'];
		unset($user);

		$msg=$ownerusername;
		$class=COLOR_TABLE_OTHER;
	}
	
	if($sourceid){
		$link = 'orders.php?data[sourceid]='.$sourceid;
		if(isset($command) && !empty($command)) $link .= '&amp;command='.$command;
		
		$msg= strtoupper($msg);
		
		$output .= '
		<td bgcolor="'.$class.'" onclick="redir(\''.$link.'\');return(false);">
			<a href="'.$link.'">
			<strong>'.$tablenum.'
			'.$msg.'</strong></a>
		</td>'."\n";
	
	} else {
		$output .= '
		<td bgcolor="'.COLOR_TABLE_FREE.'">
		&nbsp;
		</td>'."\n";
	}
	return $output;
} 

function tables_list_cell_pos($row){
	$output = '';

	$sourceid=$row['id'];
	$tablenum=$row['name'];
	$owneruserid=$row['userid'];
	$tableclosed=$row['toclose'];
	$paid=$row['paid'];
	//RTG: in the master query we get the num of the order associated to this table,
	//if any, so we don't need create a table object just to see if is empty.
	$empty = $row['order']==null || $row['order'] == 0;

	$user = new user($_SESSION['userid']);
	
	if ($user->level[USER_BIT_CASHIER] && order_found_generic_not_priced($sourceid)){
		$msg=ucfirst(phr('GENERIC_NOT_PRICED'));
		$class=COLOR_TABLE_GENERIC_NOT_PRICED;
	} elseif ($tableclosed && $user->level[USER_BIT_CASHIER] && !$paid){
		$msg=ucfirst(phr('CLOSED'));
		$class=COLOR_TABLE_CLOSED_OPENABLE;
	} elseif ($tableclosed && $user->level[USER_BIT_CASHIER] && $paid){
		$msg=ucfirst(phr('PAID'));
		$class=COLOR_TABLE_CLOSED_OPENABLE;
	} elseif ($tableclosed && !$user->level[USER_BIT_CASHIER] && !$paid) {
		$msg=ucfirst(phr('CLOSED'));
		$class=COLOR_TABLE_NOT_OPENABLE;
	} elseif ($tableclosed && !$user->level[USER_BIT_CASHIER] && $paid) {
		$msg=ucfirst(phr('PAID'));
		$class=COLOR_TABLE_NOT_OPENABLE;
	} elseif ($_SESSION['userid']==$owneruserid){
		$msg=ucfirst(phr('OPEN'));
		$class=COLOR_TABLE_MINE;
		if(categories_orders_present ($sourceid,2) && !categories_printed ($sourceid,2)) $class=COLOR_ORDER_PRIORITY_2;
		if(categories_orders_present ($sourceid,3) && !categories_printed ($sourceid,3)) $class=COLOR_ORDER_PRIORITY_2;
	} elseif(!$empty && $owneruserid==0) {
		$msg=ucfirst(phr('TO_BE_ASSOCIATED'));
		$class=COLOR_TABLE_CLOSED_OPENABLE;
	} elseif ($owneruserid==0 && !$tableclosed && $empty) {
		$msg=ucfirst(phr('FREE'));
		$class=COLOR_TABLE_FREE;
	} else {
		$user = new user ($owneruserid);
		$ownerusername=$user->data['name'];
		unset($user);

		$msg=$ownerusername;
		$class=COLOR_TABLE_OTHER;
	}
	
	if($sourceid){
		$link = 'orders.php?data[sourceid]='.$sourceid;
		if(isset($command) && !empty($command)) $link .= '&amp;command='.$command;
		
		$msg= strtoupper($msg);
		
		$output .= '
		<td bgcolor="'.$class.'" onclick="redir(\''.$link.'\');return(false);">
			<a href="'.$link.'"><img src="'.IMAGE_TABLE.'"><br>
			<strong>'.$tablenum.'
			'.$msg.'</strong></a>
		</td>'."\n";
	
	} else {
		$output .= '
		<td bgcolor="'.COLOR_TABLE_FREE.'">
		&nbsp;
		</td>'."\n";
	}
	return $output;
}

function table_there_are_orders($sourceid){
	$query = "SELECT * FROM `orders` WHERE `sourceid`='$sourceid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;
	
	return mysql_num_rows($res);
}

?>
