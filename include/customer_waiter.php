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
* @copyright	Copyright 2006-2012, Gjergj Sheldija
*/

class customer extends object {
	function customer($id=0) {
		$this->table='customers';
		$this->id=$id;
		$this -> fetch_data();
	}
}


function customer_search_page_pos($data) {
	
	global $tpl;
	$tpl->clearTemplate();

	$tmp = customer_list_pos($data);
	return $tmp;
}

function customer_search_page($data=array()) {
	global $tpl;
	
	if(customer_recognize ($data['surname'])) return 0;
	
	$tpl -> set_waiter_template_file ('standard');
	
	$tmp = navbar_empty('orders.php');
	$tpl -> assign ('navbar',$tmp);
	
	$tmp = '';
	$tmp .= customer_search_form();
	$tmp .= '
	<a href="orders.php?command=customer_insert_form">'.ucfirst(phr('INSERT_NEW')).'</a><br>
	';
	$tmp .= customer_list($data['surname']);
	$tpl -> assign ('content',$tmp);
	
	return 0;
}

function customer_insert_page() {
	global $tpl;
	
	$tpl -> set_waiter_template_file ('standard');
	
	$tmp = navbar_form('form1','orders.php');
	$tpl -> assign ('navbar',$tmp);
	
	$tmp = '';
	$tmp .= customer_insert_form();
	$tpl -> assign ('content',$tmp);
	
	return 0;
}

function customer_edit_page($data) {
	global $tpl;
	
	$tpl -> set_waiter_template_file ('standard');
	
	$tmp = navbar_form('form1','orders.php');
	$tpl -> assign ('navbar',$tmp);
	
	$tmp = '';
	$tmp .= customer_edit_form($data);
	$tpl -> assign ('content',$tmp);
	
	return 0;
}

function customer_check_values($input_data){
	global $tpl;
	
	foreach ($input_data as $key => $value) $input_data[$key] =trim ($value);
	
	$msg="";
	if(empty($input_data['surname'])) {
		$msg=ucfirst(phr('CHECK_SURNAME')).'<br/>';
		$err = ERR_TAKEAWAY_CHECK_SURNAME;
	}

	if($msg){
		$msg='<font color="Red">'.$msg.'</font>';
		$tpl -> append ('messages',$msg);
		return $err;
	}

	return $input_data;
}

function customer_list_table_head() {
	$msg = '
	<table cellspacing="2" bgcolor="'.COLOR_TABLE_GENERAL.'">
	<thead>
	<th>'.ucfirst(phr('ID')).'</th>
	<th>'.ucfirst(phr('SURNAME')).'</th>
	<th>'.ucfirst(phr('PHONE')).'</th>
	<th>'.ucfirst(phr('ADDRESS')).'</th>
	<th>'.ucfirst(phr('EMAIL')).'</th>
	<th></th>
	</thead>
	<tbody>
	';
	return $msg;
}

function customer_list_table_bottom() {
	$msg .= '</tbody>
	</table>
	';
	return $msg;
}

function customer_recognize ($term='') {
	global $tpl;
	
	$term=trim($term);
	
	if(empty($term)) return 0;
	
	$query = "SELECT * FROM `customers`";
	$query .= " WHERE `surname` LIKE '%$term%'";
	$query .= " OR `phone` LIKE '%$term%'";
	$query .= " OR `address` LIKE '%$term%'";
	$query .= " OR `email` LIKE '%$term%'";
	$query .= " OR `vat_account` LIKE '%$term%'";
	$query .= " ORDER BY `surname` ASC";

	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	if(mysql_num_rows ($res)==1) {
		$arr = mysql_fetch_array ($res);
		
		$data['takeaway_surname'] = $arr['surname'];
		$data['customer'] = $arr['id'];
		
		$err=takeaway_set_customer_data($_SESSION['sourceid'],$data);
		status_report ('TAKEAWAY_DATA',$err);
		
		orders_list();

		return 1;
	}

	return 0;
}

function customer_list_pos($term='') {
	global $tpl;
	
	$term=trim($term);
	
	$query = "SELECT * FROM `customers`";
	if(!empty($term)) {
		$query .= " WHERE `surname` LIKE '%$term%'";
		$query .= " OR `phone` LIKE '%$term%'";
		$query .= " OR `address` LIKE '%$term%'";
		$query .= " OR `email` LIKE '%$term%'";
		$query .= " OR `vat_account` LIKE '%$term%'";
	}
	$query .= " ORDER BY `surname` ASC";

	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	if(!mysql_num_rows ($res)) {
		$msg = ucphr('ERR_NO_CUSTOMER_FOUND');
		$msg='<font color="Red">'.$msg.'</font>';
		$tpl -> append ('messages',$msg);
		return '';
	}
	while ($arr = mysql_fetch_array ($res)) {
		$msg .= '
		<li onClick="fillCustomer( \'orders.php?command=set_customer&data[takeaway_surname]='.$arr['surname'].'&data[customer]='.$arr['id'].'\', \''.$arr['name'].' '.$arr['surname'].' \' )">
			'.$arr['name'].' '.$arr['surname'].'
		</li>
		';		
	}

	return $msg;
}
function customer_list($term='') {
	global $tpl;
	
	$term=trim($term);
	
	$query = "SELECT * FROM `customers`";
	if(!empty($term)) {
		$query .= " WHERE `surname` LIKE '%$term%'";
		$query .= " OR `phone` LIKE '%$term%'";
		$query .= " OR `address` LIKE '%$term%'";
		$query .= " OR `email` LIKE '%$term%'";
		$query .= " OR `vat_account` LIKE '%$term%'";
	}
	$query .= " ORDER BY `surname` ASC";

	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	if(!mysql_num_rows ($res)) {
		$msg = ucphr('ERR_NO_CUSTOMER_FOUND');
		$msg='<font color="Red">'.$msg.'</font>';
		$tpl -> append ('messages',$msg);
		return '';
	}

	$msg .= customer_list_table_head();

	while ($arr = mysql_fetch_array ($res)) {
		$msg .= customer_list_row($arr);
	}
	$msg .= customer_list_table_bottom();

	return $msg;
}

function customer_list_row($arr) {
	$msg = '
	<tr>
		<td>'.$arr['id'].'</td>
		<td><a href="orders.php?command=set_customer&amp;data[takeaway_surname]='.$arr['surname'].'&amp;data[customer]='.$arr['id'].'">'.$arr['surname'].'</a></td>
		<td>'.$arr['phone'].'</td>
		<td>'.$arr['address'].'</td>
		<td>'.$arr['email'].'</td>
		<td><a href="orders.php?command=customer_edit_form&amp;data[id]='.$arr['id'].'">'.ucfirst(phr('EDIT')).'</a></td>
	</tr>
	';

	return $msg;
}

function customer_search_form() {
	$msg = '
	<form action="orders.php" method="post" name="form_search">
		<input type="hidden" name="command" value="customer_search">
		<table>
		<tr>
		<td>
			<a href="orders.php?command=customer_insert_form">
			<input type="image" src="'.IMAGE_NEW.'" alt="'.ucfirst(phr('CUSTOMER_INSERT')).'" border=0 width="24" height="24">
			</a>
		</td>
		<td>
			<input name="data[surname]" type="text" value="'.$data['surname'].'" maxlength="255" size="10">
		</td>
		<td>
			<input type="image" src="'.IMAGE_FIND.'" alt="'.ucfirst(phr('SEARCH')).'" border=0 width="22" height="22">
		</td>
		</tr>
		</table>
	</form>
	';
	return $msg;
}

function customer_insert($input_data){
	$input_data=customer_check_values($input_data);
	if(!is_array($input_data)) return $input_data;

	// Now we'll build the correct INSERT query, based on the fields provided
	$query="INSERT INTO `customers` (";
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
	$query = substr ($query, 0, strlen($query)-1);
	$query.=")";

	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	return 0;
}

function customer_edit($input_data){
	$input_data=customer_check_values($input_data);
	if($input_data<0) return $input_data;

	// Now we'll build the correct UPDATE query, based on the fields provided
	$query="UPDATE `customers` SET ";
	for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
		$query.="`".$key."`='".$value."',";
	}
	// strips the last comma that has been put
	$query = substr ($query, 0, strlen($query)-1);
	$query.=" WHERE `id`='".$input_data['id']."'";

	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;
	
	return 0;
}

function customer_insert_form() {
	$msg = '
	<form action="orders.php" method="post" name="form1">
		<input type="hidden" name="command" value="customer_insert">
	';
	$msg .= customer_form_data(0);
	$msg .= '</form>
	';
	return $msg;
}

function customer_edit_form($data) {
	global $tpl;
	
	$query="SELECT * FROM `customers` WHERE `id`='".$data['id']."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	if(!mysql_num_rows ($res)) {
		$msg = ucphr('ERR_NO_CUSTOMER_FOUND');
		$msg='<font color="Red">'.$msg.'</font>';
		$tpl -> append ('messages',$msg);
		return '';
	}

	$arr=mysql_fetch_array ($res);
	$msg = '
	<form action="orders.php" method="post" name="form1">
		<input type="hidden" name="command" value="customer_edit">
		<input type="hidden" name="data[id]" value="'.$arr['id'].'">
	';
	$msg .= customer_form_data($arr);
	$msg .= '</form>
	';
	return $msg;
}

function customer_form_data($data) {
	$msg = '
		<table>
		<tr>
		<td>
			'.ucfirst(phr('SURNAME')).':
		</td>
		<td>
			<input name="data[surname]" type="text" value="'.$data['surname'].'" maxlength="255" size="10">
		</td>
		</tr>
		<tr>
		<td>
			'.ucfirst(phr('NAME')).':
		</td>
		<td>
			<input name="data[name]" type="text" value="'.$data['name'].'" maxlength="255" size="10">
		</td>
		</tr>
		<tr>
		<td>
			'.ucfirst(phr('ADDRESS')).':
		</td>
		<td>
			<input name="data[address]" type="text" value="'.$data['address'].'" maxlength="255" size="10">
		</td>
		</tr>
		<tr>
		<td>
			'.ucfirst(phr('CITY')).':
		</td>
		<td>
			<input name="data[city]" type="text" value="'.$data['city'].'" maxlength="255" size="10">
		</td>
		</tr>
		<tr>
		<td>
			'.ucfirst(phr('ZIP_CODE')).':
		</td>
		<td>
			<input name="data[zip]" type="text" value="'.$data['zip'].'" maxlength="255" size="10">
		</td>
		</tr>
		<tr>
		<td>
			'.ucfirst(phr('VAT_ACCOUNT')).':
		</td>
		<td>
			<input name="data[vat_account]" type="text" value="'.$data['vat_account'].'" maxlength="255" size="10">
		</td>
		</tr>
		<tr>
		<td>
			'.ucfirst(phr('PHONE')).':
		</td>
		<td>
			<input name="data[phone]" type="text" value="'.$data['phone'].'" maxlength="255" size="10">
		</td>
		</tr>
		<tr>
		<td>
			'.ucfirst(phr('MOBILE')).':
		</td>
		<td>
			<input name="data[mobile]" type="text" value="'.$data['mobile'].'" maxlength="255" size="10">
		</td>
		</tr>
		<tr>
		<td>
			'.ucfirst(phr('EMAIL')).':
		</td>
		<td>
			<input name="data[email]" type="text" value="'.$data['email'].'" maxlength="255" size="10">
		</td>
		</tr>
		</table>
	';
	return $msg;
}

?>