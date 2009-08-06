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
* @author		Gjergj Sheldija <gjergj.sheldija@gmail.com>
* @package		MyHandyRestaurant
* @copyright	Copyright 2003-2005, Fabio De Pascale
*/

function order_last_modified_mods () {
	if (!$deleted
	&& $arr['printed']==NULL
	&& $arr['dishid']!=MOD_ID
	&& $arr['dishid']!=SERVICE_ID) {
		$link = 'orders.php?command=listmods&amp;data[id]='.$arr['associated_id'];
		$output .= '
		<td bgcolor="'.$class.'" onclick="redir(\''.$link.'\');">
			<a href="'.$link.'">+ -</a>
		</td>';


	}
}

function order_last_modified_links () {
	$ret=array();

	$query="SELECT * FROM `orders`WHERE `sourceid`='".$_SESSION['sourceid']."' AND `id`=`associated_id` ORDER BY `timestamp` DESC LIMIT 1";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;
	$arr = mysql_fetch_array($res);

	if((!$arr['printed'] && $arr['dishid']!=MOD_ID) || $arr['dishid']==SERVICE_ID){
		$link = 'orders.php?command=ask_delete&data[id]='.$arr['id'];
		if($arr['suspend']) $link .= '&data[suspend]=1';
		if($arr['extra_care']) $link .= '&data[extra_care]=1';
		$ret[0]=$link;

		for($i=1;$i<10;$i++) {
			$link = 'orders.php?command=update&data[quantity]='.$i.'&data[id]='.$arr['id'];
			if($arr['suspend']) $link .= '&data[suspend]=1';
			if($arr['extra_care']) $link .= '&data[extra_care]=1';
			$ret[$i]=$link;
		}

		$newquantity=$arr['quantity']+1;
		$link = 'orders.php?command=update&data[quantity]='.$newquantity.'&data[id]='.$arr['id'];
		if($arr['suspend']) $link .= '&data[suspend]=1';
		if($arr['extra_care']) $link .= '&data[extra_care]=1';
		$ret[10]=$link;

		if($arr['quantity']>1){
			$newquantity=$arr['quantity']-1;
			$link = 'orders.php?command=update&data[quantity]='.$newquantity.'&data[id]='.$arr['id'];
			if($arr['suspend']) $link .= '&data[suspend]=1';
			if($arr['extra_care']) $link .= '&data[extra_care]=1';
			$ret[11]=$link;
		} elseif($arr['quantity']==1 && CONF_ALLOW_EASY_DELETE){
			$link = 'orders.php?command=ask_delete&data[id]='.$arr['id'];
			if($arr['suspend']) $link .= '&data[suspend]=1';
			if($arr['extra_care']) $link .= '&data[extra_care]=1';
			$ret[11]=$link;
		} else $ret[11]='';
	} else {
		for($i=1;$i<10;$i++) $ret[$i]='';
		$ret[10]='';
		$ret[11]='';
	}

	$ret[-1]='tables.php';

	return $ret;
}

function order_get_last_modified() {
	$query="SELECT `id` FROM `orders`WHERE `sourceid`='".$_SESSION['sourceid']."' AND `id`=`associated_id` ORDER BY `timestamp` DESC LIMIT 1";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;
	$arr = mysql_fetch_array($res);
	return $arr['id'];
}

function dish_list ($start_data) {
	global $tpl;

	$_SESSION['order_added']=0;

	$tpl->set_waiter_template_file ('dishlist');

	$tmp = navbar_empty();
	if(printing_orders_to_print($_SESSION['sourceid'])) $tmp = navbar_with_printer();
	else $tmp = navbar_empty();
	$tpl->assign ('navbar',$tmp);


	if(	get_conf(__FILE__,__LINE__,"show_summary") &&
		isset($_SESSION['go_back_to_cat']) &&
		$_SESSION['go_back_to_cat']) {
		$tbl = new table ($_SESSION['sourceid']);
		if($last_mod = order_get_last_modified()) {
			$mods=get_conf(__FILE__,__LINE__,"show_mods_in_summary");
			$tbl->list_orders ('last_order',$last_mod,$mods);
		}
	}

	if(isset($start_data['category'])){
		$tmp = dishlist_form_start($back_to_cat);
		$tpl -> assign ('formstart',$tmp);
		$tmp = dishlist_form_end();
		$tpl -> assign ('formend',$tmp);
		$tmp = dishlist_back_to_cat();
		$tpl -> assign ('back_to_cat',$tmp);
		$tmp = priority_radio($start_data);
		$tpl -> assign ('priority',$tmp);
		$tmp = quantity_list($start_data);
		$tpl -> assign ('quantity',$tmp);
		$tmp = dishes_list_cat ($start_data);
		$tpl -> assign ('dishes_list',$tmp);

		$tmp = keys_dishlist_cat ();
		$tpl -> append ('scripts',$tmp);
	} elseif (isset($start_data['letter'])){
		$tmp = dishlist_form_start(false);
		$tpl -> assign ('formstart',$tmp);
		$tmp = dishlist_form_end();
		$tpl -> assign ('formend',$tmp);
		$tmp = priority_radio($start_data);
		$tpl -> assign ('priority',$tmp);
		$tmp = quantity_list($start_data);
		$tpl -> assign ('quantity',$tmp);
		$tmp = dishes_list_letter ($start_data);
		$tpl -> assign ('dishes_list',$tmp);

		$tmp = keys_dishlist_letters ();
		$tpl -> append ('scripts',$tmp);
	} elseif (isset($start_data['search'])){
		$tmp = dishlist_form_start(false);
		$tpl -> assign ('formstart',$tmp);
		$tmp = dishlist_form_end();
		$tpl -> assign ('formend',$tmp);
		$tmp = priority_radio($start_data);
		$tpl -> assign ('priority',$tmp);
		$tmp = quantity_list($start_data);
		$tpl -> assign ('quantity',$tmp);
		$tmp = dishes_list_search ($start_data);
		$tpl -> assign ('dishes_list',$tmp);

		$tmp = keys_dishlist_letters ();
		$tpl -> append ('scripts',$tmp);
	} elseif (isset($start_data['idsystem'])){
		$tmp = dishlist_form_start(false);
		$tpl -> assign ('formstart',$tmp);
		$tmp = dishlist_form_end();
		$tpl -> assign ('formend',$tmp);
		$tmp = priority_radio($start_data);
		$tpl -> assign ('priority',$tmp);
		$tmp = quantity_list($start_data);
		$tpl -> assign ('quantity',$tmp);
		$tmp = input_dish_id ($start_data);
		$tpl -> assign ('dishes_list',$tmp);
	} else {
		$tmp = categories_list($start_data);
		$tpl -> assign ('categories',$tmp);

		$tmp = ucfirst(phr('ERROR_NO_CATEGORY_SELECTED'))."<br/>\n";
		$tpl -> append ('messages',$tmp);
	}
	return 0;
}

function dish_list_pos ($start_data) {
	global $tpl;

	$_SESSION['order_added']=0;

	$tpl -> set_waiter_template_file ('dishlist');

	$tmp = navbar_empty_pos();
	if(printing_orders_to_print($_SESSION['sourceid'])) $tmp = navbar_with_printer_pos();
	else $tmp = navbar_empty_pos();
	$tpl -> assign ('navbar',$tmp);


	if( get_conf(__FILE__,__LINE__,"show_summary") &&
	isset($_SESSION['go_back_to_cat']) &&
	$_SESSION['go_back_to_cat'])
	{
		$tbl = new table ($_SESSION['sourceid']);
		if($last_mod = order_get_last_modified()) {
			$mods=get_conf(__FILE__,__LINE__,"show_mods_in_summary");
			$tbl->list_orders_pos ('last_order',$last_mod,$mods);
		}
	}

	if(isset($start_data['category'])){
		$tmp = dishlist_form_start($back_to_cat);
		$tpl -> assign ('formstart',$tmp);
		$tmp = dishlist_form_end();
		$tpl -> assign ('formend',$tmp);
		$tmp = dishlist_back_to_cat();
		$tpl -> assign ('back_to_cat',$tmp);
		$tmp = priority_radio($start_data);
		$tpl -> assign ('priority',$tmp);
		$tmp = quantity_list($start_data);
		$tpl -> assign ('quantity',$tmp);
		$tmp = dishes_list_cat_pos ($start_data);
		$tpl -> assign ('dishes_list',$tmp);

	} elseif (isset($start_data['letter'])){
		$tmp = dishlist_form_start(false);
		$tpl -> assign ('formstart',$tmp);
		$tmp = dishlist_form_end();
		$tpl -> assign ('formend',$tmp);
		$tmp = priority_radio($start_data);
		$tpl -> assign ('priority',$tmp);
		$tmp = quantity_list($start_data);
		$tpl -> assign ('quantity',$tmp);
		$tmp = dishes_list_letter ($start_data);
		$tpl -> assign ('dishes_list',$tmp);

	} elseif (isset($start_data['search'])){
		$tmp = dishlist_form_start(false);
		$tpl -> assign ('formstart',$tmp);
		$tmp = dishlist_form_end();
		$tpl -> assign ('formend',$tmp);
		$tmp = priority_radio($start_data);
		$tpl -> assign ('priority',$tmp);
		$tmp = quantity_list($start_data);
		$tpl -> assign ('quantity',$tmp);
		$tmp = dishes_list_search ($start_data);
		$tpl -> assign ('dishes_list',$tmp);

	} elseif (isset($start_data['idsystem'])){
		$tmp = dishlist_form_start(false);
		$tpl -> assign ('formstart',$tmp);
		$tmp = dishlist_form_end();
		$tpl -> assign ('formend',$tmp);
		$tmp = priority_radio($start_data);
		$tpl -> assign ('priority',$tmp);
		$tmp = quantity_list($start_data);
		$tpl -> assign ('quantity',$tmp);
		$tmp = input_dish_id ($start_data);
		$tpl -> assign ('dishes_list',$tmp);
	} else {
		$tmp = categories_list_pos($start_data);
		$tpl -> assign ('categories',$tmp);

		$tmp = ucfirst(phr('ERROR_NO_CATEGORY_SELECTED'))."<br/>\n";
		$tpl->append ('messages',$tmp);
	}
	return 0;
}

function order_fast_dishid_form () {
	$data['nolabel']=1;
	$data['priority']=1;
	$tmp = dishlist_form_start(false);
	$tmp .= priority_radio($data);
	$tmp .= quantity_list($data);	
	$tmp .= input_dish_id ($start_data);
	$tmp .= dishlist_form_end();
	return $tmp;
}

function order_price_modify($id) {
	global $tpl;

	$tpl -> set_waiter_template_file ('question');

	$tmp = navbar_form('form1','orders.php');
	$tpl -> assign ('navbar',$tmp);

	$query="SELECT * FROM `orders` WHERE `id`=$id";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	$arr = mysql_fetch_array($res);
	if(!$arr) return ERR_ORDER_NOT_FOUND;

	$dishid=$arr['dishid'];
	$generic=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dishes',"generic",$dishid);
	$pricetot=$arr['price'];
	$quantity=$arr['quantity'];
	$price=$pricetot;
	$price=sprintf("%01.2f",$price);

	$tmp='';
	if($generic) $tmp .= ucfirst(phr('GENERIC_PRICE_DESCRIPTION'))."<br/>\n";
	$tmp .= ucfirst(phr('GENERIC_PRICE_INSTRUCTION')).' '.ucfirst(phr('UPDATE_PRICE')).'<br />
	<form action="orders.php" method="post" name="form1">
	<input type="hidden" name="command" value="update">
	<input type="hidden" name="data[id]" value="'.$id.'">
	<input type="hidden" name="data[quantity]" value="'.$quantity.'">
	<input type="text" size="8" maxlength="8" name="data[price]" value="'.$price.'"><br/>
	<input type="submit" value="'.ucfirst(phr('UPDATE_PRICE')).'">
	</form>
	<br/>';
	$tpl -> assign ('question',$tmp);

	return 0;
}

function order_is_mod($id){
	/*
	 Return codes:
	 0. no valid record or it's not MOD_ID
	 1. found SERVICE_ID
	 2. found MOD_ID
	 */
	$query="SELECT * FROM `orders` WHERE `id`=$id";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	if(!mysql_num_rows($res)) return 0;

	$arr=mysql_fetch_array($res);
	$dishid=$arr['dishid'];

	if($dishid==SERVICE_ID) return 1;
	if($dishid==MOD_ID) return 2;

	return 0;
}

function order_has_mods($id){
	$query="SELECT * FROM `orders` WHERE `associated_id`='$id' AND `id`!='$id'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	return mysql_num_rows($res);
}

function order_find_incrementable ($dishid,$priority){
	$query="SELECT * FROM `orders`
	 WHERE `sourceid`='".$_SESSION['sourceid']."'
	AND `dishid`='$dishid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	while ($arr = mysql_fetch_array($res)) {
		if(!order_has_mods($arr['id'])
		&& $arr['priority']==$priority
		&& $arr['suspend']==0
		&& $arr['extra_care']==0
		&& $arr['printed']==NULL
		&& $arr['deleted']==0) return $arr['id'];
	}

	return 0;
}


function order_found_generic_not_priced($sourceid){
	$query="SELECT * FROM `orders`
			JOIN `dishes`
			WHERE dishes.id=orders.dishid
			AND dishes.generic='1'
			AND orders.sourceid = '".$sourceid."'
			AND orders.price = '0'
			AND orders.printed IS NOT NULL
			AND orders.deleted='0'";

	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	return mysql_num_rows($res);
}

function price_calc ($num,$correction=0) {
	if ($num<1) return 0;

	$autocalc = new autocalc ();
	$maxquantity = $autocalc -> max_quantity();
	// no value is set
	if($maxquantity==-1) return 0;

	$autocalc = array();

	$query="SELECT * FROM `autocalc`";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	while($arr = mysql_fetch_array($res)) {
		$autocalc [$arr['quantity']] = $arr['price'];
	}

	ksort($autocalc);

	$maxquantity = $maxquantity - $correction;

	foreach($autocalc as $key => $value) {
		$newindex=$key-$correction;
		if($newindex<1 && $key!=0)
		unset($autocalc[$key]);
		elseif($key!=0 && $newindex!=$key) {
			$autocalc[$newindex]=$autocalc[$key];
			//echo '$autocalc['.$newindex.']: '.$autocalc[$newindex].'<br>';
			unset($autocalc[$key]);
		}
	}
	// quantity found, it is in the array
	if(array_key_exists($num,$autocalc)) {
		ksort($autocalc);

		$sum=0;
		foreach($autocalc as $key => $value) {
			if($key<=$num && $key>0)
			$sum += $value;
		}
		// echo '$sum: '.$sum.'<br>';
		return $sum;
	}

	// quantity not found, we look for the highest quantiy available,
	// then add the remaining price (based on the 0 quantity record)

	$sum=0;
	foreach($autocalc as $key => $value) {
		if($key!=0)
		$sum += $value;
	}

	$sum += $autocalc [0] * ($num-$maxquantity);

	return $sum;
}

function orders_create ($dishid,$input_data=array()) {
	global $tpl;

	if(!isset($input_data['quantity'])) {
		$input_data['quantity']=1;
		echo '<br>quantity not set!';
	}

	$existing = 0;
	if(isset($input_data['priority']) && $input_data['priority']) $existing = order_find_incrementable ($dishid,$input_data['priority']);
	if($existing) {
		// the order already exists, so updates the old one instead of creating a new identical
		$existing= (int) $existing;
		$ord = new order($existing);
		$data_old = $ord -> data;
		$data_old['quantity'] = $data_old['quantity'] + $input_data['quantity'];
		if($err = orders_update ($data_old)) return 0;

		return $ord -> id;
	}

	$ord = new order();
	$ord -> prepare_default_array ($dishid);

	if($err = $ord -> create ()) return 0;

	// insert all the modules interfaces for order creation here
	toplist_insert ($dishid, $input_data['quantity']);

	//don't move stock from here!
	if(class_exists('stock_object') && isset($input_data['quantity'])) {
		$stock = new stock_object;
		$stock -> silent = true;
		$stock -> remove_from_waiter($ord->id,$input_data['quantity']);
	}

	// end interfaces

	$ord -> data['associated_id']=$ord->id;
	$ord -> data['quantity']=$input_data['quantity'];
	if(isset($input_data['priority']) && $input_data['priority']) $ord -> data['priority']=$input_data['priority'];

	if($dishid==SERVICE_ID) $ord -> data['priority']=0;

	if($err = $ord -> set()) return 0;

	return $ord->id;
}

function orders_ask_delete ($start_data) {
	global $tpl;

	$tpl -> set_waiter_template_file ('question');

	$tmp = navbar_form('form1','orders.php?command=list');
	$tpl -> assign ('navbar',$tmp);

	$ord = new order ((int) $start_data['id']);

	if ($ord -> data['dishid'] == SERVICE_ID) $dishname = ucfirst(phr('SERVICE_FEE'));
	else {
		$dish = new dish ($ord -> data['dishid']);
		$dishname = $dish -> name ($_SESSION ['language']);
	}

	$tmp = '
	<form action="orders.php" method="post" name="form1">
	<input type="hidden" name="command" value="delete">
	<input type="hidden" name="data[id]" value="'.$start_data['id'].'">
	'.ucfirst(phr('ASK_DELETE_CONFIRMATION')).'<br/>
	<b>'.$dishname.'</b>
	
	</form>';
	$tpl -> assign ('question',$tmp);
}



function orders_ask_substitute ($start_data) {
	global $tpl;

	$tpl -> set_waiter_template_file ('question');

	$tmp = navbar_form('form1','orders.php?command=list');
	$tpl -> assign ('navbar',$tmp);

	$ord = new order ((int) $start_data['id']);

	if ($ord -> data['dishid'] == SERVICE_ID) $dishname = ucfirst(phr('SERVICE_FEE'));
	else {
		$dish = new dish ($ord -> data['dishid']);
		$dishname = $dish -> name ($_SESSION ['language']);
	}

	$tmp = '
	<form action="orders.php" method="post" name="form1">
	<input type="hidden" name="command" value="substitute">
	<input type="hidden" name="data[id]" value="'.$start_data['id'].'">
	'.ucfirst(phr('SUBSTITUTE_ASK')).'<br/>
	<b>'.$dishname.'</b>
	
	</form>';
	$tpl -> assign ('question',$tmp);
}
function orders_ask_substitute_pos ($start_data) {
	global $tpl;

	$tpl -> set_waiter_template_file ('question_pos');

	$tmp = navbar_form('form1','orders.php?command=list');
	$tpl -> assign ('navbar',$tmp);

	$ord = new order ((int) $start_data['id']);

	if ($ord -> data['dishid'] == SERVICE_ID) $dishname = ucfirst(phr('SERVICE_FEE'));
	else {
		$dish = new dish ($ord -> data['dishid']);
		$dishname = $dish -> name ($_SESSION ['language']);
	}

	$tmp = '
	<form action="orders.php" method="post" name="form1">
	<input type="hidden" name="command" value="substitute">
	<input type="hidden" name="data[id]" value="'.$start_data['id'].'">
	'.ucfirst(phr('SUBSTITUTE_ASK')).'<br/>
	<b>'.$dishname.'</b>
	
	</form>';
	$tpl -> assign ('question',$tmp);
}

function orders_get_data ($start_data) {
	$id = (int) $start_data['id'];
	$ord = new order($id);

	$ret = $ord -> data;

	unset($ord);
	return $ret;
}

function orders_edit_pos ($start_data,$fee_destroyer=false) {
	global $tpl;

	$tpl->set_waiter_template_file ('edit_pos');

	$ordid = (int) $start_data['id'];
	$ord = new order ($ordid);
	if (!$ord->exists ()) return ERR_ORDER_NOT_FOUND;

	//gjergji : $fee_destroyer what is ?
	if($fee_destroyer) $tmp = navbar_form('form1','orders.php?command=delete&amp;data[silent]=1&amp;data[id]='.$start_data['id']);
	else $tmp = navbar_trash_pos('form1','orders.php?command=list',$start_data);
	$tpl->assign ('navbar',$tmp);

	orders_edit_printed_info ($ord);

	//gjergji : don't need subsitute because it 
	//shotcuts to delete
	if($ord->data['dishid'] != SERVICE_ID) orders_edit_substitute ($ord);

	if($ord->data['dishid'] != SERVICE_ID && $ord->data['printed']) {
		$tmp = navbar_trash('','orders.php?command=list',$start_data);
		$tpl->assign ('navbar',$tmp);
		return 0;
	}

	orders_edit_form ($ord);

	orders_edit_quantity ($ord);
	orders_edit_dish_name ($ord);

	if($ord->data['dishid'] == SERVICE_ID) return 0;

	orders_edit_priority ($ord);
	orders_edit_extra_care ($ord);
	orders_edit_suspend ($ord);
	return 0;
}
function orders_edit ($start_data,$fee_destroyer=false) {
	global $tpl;

	$tpl->set_waiter_template_file ('edit');

	$ordid = (int) $start_data['id'];
	$ord = new order ($ordid);
	if (!$ord->exists ()) return ERR_ORDER_NOT_FOUND;

	if($fee_destroyer) $tmp = navbar_form('form1','orders.php?command=delete&amp;data[silent]=1&amp;data[id]='.$start_data['id']);
	else $tmp = navbar_trash('form1','orders.php?command=list',$start_data);
	$tpl->assign ('navbar',$tmp);

	orders_edit_printed_info ($ord);

	if($ord->data['dishid'] != SERVICE_ID) orders_edit_substitute ($ord);

	if($ord->data['dishid'] != SERVICE_ID && $ord->data['printed']) {
		$tmp = navbar_trash('','orders.php?command=list',$start_data);
		$tpl->assign ('navbar',$tmp);
		return 0;
	}

	orders_edit_form ($ord);

	orders_edit_quantity ($ord);
	orders_edit_dish_name ($ord);

	if($ord->data['dishid'] == SERVICE_ID) return 0;

	orders_edit_priority ($ord);
	orders_edit_extra_care ($ord);
	orders_edit_suspend ($ord);
	return 0;
}

function orders_edit_printed_info ($ord) {
	global $tpl;

	if ($ord -> data['dishid'] == SERVICE_ID) return 0;
	if($ord->data['printed']==NULL) return 0;

	$print_time= substr($ord->data['printed'],-8,5);
	$tmp = ucphr('ORDER_PRINTED_AT').' '.$print_time;
	$tmp .= '<br/>(<b>'.orders_print_elapsed_time ($ord,false).'</b> '.phr('ORDER_PRINTED_MINS_AGO').')';
	$tpl -> assign ('print_info',$tmp);

	return 0;
}

function orders_print_elapsed_time ($ord,$string=false) {
	if ($ord -> data['dishid'] == SERVICE_ID) return -1;
	if($ord->data['printed']==NULL) return -1;
	if($ord->data['deleted']) return -1;

	$elapsed_time=time() - strtotime($ord->data['printed']);
	$elapsed_time = round($elapsed_time/60,0);

	// number is requested, so we return minutes
	if(!$string) return $elapsed_time;

	// return string with associated description
	if ($elapsed_time>60) {
		$hrs=floor($elapsed_time/60);
		$mins=$elapsed_time-($hrs*60);
		$mins=sprintf("%02d",$mins);
		$elapsed_time=$hrs.':'.$mins;
	} else {
		$elapsed_time.=' '.phr('ORDER_PRINTED_MINS_AGO_ABBR');
	}

	return $elapsed_time;
}

function orders_edit_dish_name ($ord) {
	global $tpl;

	if ($ord->data['dishid'] == SERVICE_ID) {
		$tmp = ucfirst(phr('SERVICE_FEE'));
	}else {
		$dish = new dish ($ord -> data['dishid']);
		$tmp = $dish -> name ($_SESSION ['language']);
	}
	
	$tpl->assign ('dishname',$tmp);
	return 0;
}

function orders_edit_suspend ($ord) {
	global $tpl;

	$checked='';
	if ($ord -> data['suspend']) $checked=" checked";
	$tmp = '<input type="checkbox" name="data[suspend]" value="1"'.$checked.' id="suspend">';
	$tmp .= '<label for="suspend">' . ucfirst(phr('SUSPEND_PRINT')). '</label>';
	$tpl->assign ('suspend',$tmp);

	return 0;
}

function orders_edit_extra_care ($ord) {
	global $tpl;

	$checked='';
	if ($ord->data['extra_care']) $checked=" checked";

	$tmp = '<input type="checkbox" name="data[extra_care]" value="1"'.$checked.' id="extracare">';
	$tmp .= '<label for="extracare">' . ucfirst(phr('EXTRA_CARE')) . '</label>';

	$tpl->assign ('extra_care',$tmp);
	return 0;
}

function orders_edit_substitute ($ord) {
	global $tpl;

	$tmp = '<a href="orders.php?command=ask_substitute&amp;data[id]='.$ord->data['id'].'">'.ucfirst(phr('SUBSTITUTE')).'</a>';
	$tpl -> assign ('substitute',$tmp);

	return 0;
}

function orders_edit_quantity ($ord=0) {
	global $tpl;

	$tmp = ucfirst(phr('QUANTITY')).':<br>
	<select name="data[quantity]" size="9">';
	for ($i=1; $i<=MAX_QUANTITY; $i++) {
		if ($ord -> data['quantity'] == $i) $selected = ' selected';
		else $selected = '';

		$tmp .= '
		<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
	}
	$tmp .= '
	</select>';
	$tpl -> assign ('quantity',$tmp);
	return 0;
}

function orders_edit_priority ($ord) {
	global $tpl;

	$tmp = ucfirst(phr('PRIORITY')).':<br>
	<select name="data[priority]" size="3">';
	for ($i=1; $i<=3; $i++) {
		if ($ord -> data['priority'] == $i) $selected = ' selected';
		else $selected = '';

		$tmp .= '
		<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
	}
	$tmp .= '
	</select>';
	$tpl -> assign ('priority',$tmp);
	return 0;
}

function orders_edit_form ($ord) {
	global $tpl;

	$tmp = '
	<form action="orders.php" method="POST" name="form1">
	<input type="hidden" name="command" VALUE="update">
	<input type="hidden" name="data[id]" VALUE="'.$ord->id.'">';
	$tpl -> assign ('form_start',$tmp);

	$tmp = '
	</form>';
	$tpl -> assign ('form_end',$tmp);

	return 0;
}

function orders_update ($start_data) {
	global $tpl;

	$id= (int) $start_data['id'];
	$ord = new order($id);

	if (!isset($start_data['suspend'])) $start_data['suspend'] = 0;
	if (!isset($start_data['extra_care'])) $start_data['extra_care'] = 0;

	if (isset($start_data['price'])) $start_data['price'] = eq_to_number ($start_data['price']);

	// forces extra_care = 1 for generic dishes
	$dishid=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],"orders","dishid",$start_data['id']);
	$generic=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],"dishes","generic",$dishid);
	if($generic && $start_data['price']==0) $start_data['extra_care'] = '1';
	elseif($generic && $start_data['price']) $start_data['extra_care'] = '0';

	// toplist update code
	if(!isset($start_data['quantity'])) $start_data['quantity']=0;

	// insert all the modules interfaces for order creation here
	toplist_update ($ord -> data['dishid'], $ord->data['quantity'], $start_data['quantity']);
	if(class_exists('stock_object')) {
		$stock = new stock_object;
		$stock -> silent = true;
		$stock -> remove_from_waiter($id,$start_data['quantity']);
	}
	// end interfaces

	// real update
	$ord->data=$start_data;
	$err = $ord -> set();

	unset($ord);
	return $err;
}

function orders_delete ($start_data) {
	global $tpl;

	$id = (int) $start_data['id'];
	$ord = new order($id);

	if(!$ord -> data['deleted'] &&
		$ord -> data['printed'] &&
		$ord -> data['dishid'] != SERVICE_ID) {
		if($err = print_ticket($id,true)) return $err;
	}

	if (CONF_DEBUG_DONT_DELETE) return 0;

	// was as follows, but it's better to never delete an order if the table is still open
	if ($ord -> data['dishid'] != SERVICE_ID) {
		$start_data['deleted']=1;
		$start_data['paid']=1;
		$start_data['suspend']=0;
		$start_data['printed']='0000-00-00 00:00:00';
		$start_data['price']=0;
		$err = orders_update ($start_data);
	} else {
		// insert all the modules interfaces for order creation here
		toplist_delete($ord -> data['dishid'],$ord -> data['quantity']);
		if(class_exists('stock_object')) {
			$stock = new stock_object;
			$stock -> silent = true;
			$stock -> remove_from_waiter($id,0);
		}

		$err = $ord -> delete();
	}

	unset($ord);
	return $err;
}

function orders_service_fee_exists(){
	$query="SELECT `id` FROM `orders` WHERE `dishid` = '".SERVICE_ID."' AND `sourceid`='".$_SESSION['sourceid']."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	if (!mysql_num_rows($res)) return 0;

	$arr = mysql_fetch_array ($res);

	return $arr['id'];
}

function orders_service_fee_questions () {
	$id=orders_service_fee_exists ();
	$created=false;
	if(!$id) {
		$data['quantity']=get_conf(__FILE__,__LINE__,"default_quantity");
		$id = orders_create (SERVICE_ID,$data);
		$created =true;
	}

	$start_data['id']=$id;
	orders_edit ($start_data,$created);
}

function orders_list_pos ($showOrdersOnly = false) {
	global $tpl;
	$table = new table ($_SESSION['sourceid']);
	$user = new user($_SESSION['userid']);
		
	// use session to decide wether to show the orders list or not
	// TODO: add get_conf here
	if(!isset($_SESSION['show_orders_list_pos'])) $_SESSION['show_orders_list_pos']=false;
	$show_orders = true;
	unset($_SESSION['select_all']);
	$_SESSION['go_back_to_cat']=0;

	//ajax hack :(
	if($showOrdersOnly == true ) {
		echo $table->list_orders_only_pos();
		return 0;	
	}
	
	if(table_is_closed($_SESSION['sourceid']) && !$user->level[USER_BIT_CASHIER]) {
		table_closed_interface_pos();
		return 0;
	}

	$_SESSION['order_added']=0;
	
	$tpl->set_waiter_template_file ('orders_pos');
	if(table_is_takeaway ($_SESSION['sourceid'])) {
		$tpl->set_waiter_template_file ('orders_takeaway');
		takeaway_form();
	}

	$table->fetch_data(true);
	if (!orders_service_fee_exists () && get_conf(__FILE__,__LINE__,'service_fee_use')) {
		$tmp = '<a href="orders.php?command=create&amp;dishid='.SERVICE_ID.'">'.ucfirst(phr('CREATE_SERVICE_FEE')).'</a><br/>';
		$tpl -> append ('commands',$tmp);
	}

	$associated_waiter = table_is_associated ();
	if ($user->level[USER_BIT_CASHIER] && table_is_closed($_SESSION['sourceid'])) {
		$tmp = '<a href="orders.php?command=reopen_confirm">'.ucfirst(phr('REOPEN_TABLE')).'</a><br/>';
		$tpl->append ('commands',$tmp);
	}
	if ($_SESSION['show_orders_list_pos'] == false) {
		$image = '<img src="'.IMAGE_SHOW_ORDERS.'" height=48 width=48><br>';
		$desc=ucfirst(phr('SHOW_ORDERS'));
	} else {
		$image = '<img src="'.IMAGE_HIDE_ORDERS.'" height=48 width=48><br>';
		$desc=ucfirst(phr('HIDE_ORDERS'));
	}
	$tmp = '<a href="orders.php?command=set_show_orders">'.$image.$desc.'</a><br/>';
	$tpl->append('commands',$tmp);


	$tmp = categories_list_pos();
	$tpl->assign ('categories',$tmp);

	
	 $tmp = letters_list();
	 $tpl->assign ('letters',$tmp);
	 

	if(CONF_FAST_ORDER){
		$tmp = order_fast_dishid_form ();
		$fast_order_id = '<span class="rounded">' . $tmp . '</span>';
		$tpl->assign ('fast_order_id',$fast_order_id);
	} else {
	// activate scripts
		$tmp = keys_orders ();
		$tpl->append ('scripts',$tmp);
	}
	
	// use session to decide wether to show the orders list or not
	if(!isset($_SESSION['show_toplist'])) $_SESSION['show_toplist']=get_conf(__FILE__,__LINE__,"top_list_show_top");
	if($_SESSION['show_toplist']) {
		toplist_show();
	} elseif(get_conf(__FILE__,__LINE__,"top_list_show_top")) {
		$tmp = '<a href="orders.php?command=set_show_toplist">'.ucphr('SHOW_TOPLIST').'</a><br/>';
		$tpl->assign ('toplist',$tmp);
	}
	
	$tmp = command_bar_table_vertical_pos();
	$tpl->assign('vertical_navbar',$tmp);

	if($show_orders) {
		$table->list_orders_pos ();
	}

	if (get_conf(__FILE__,__LINE__,"show_summary")) {
		$query="SELECT * FROM `orders`WHERE `sourceid`='".$_SESSION['sourceid']."' AND `id`=`associated_id` ORDER BY `timestamp` DESC LIMIT 1";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
		$arr = mysql_fetch_array($res);
		$mods=get_conf(__FILE__,__LINE__,"show_mods_in_summary");
		$table->list_orders_pos ('last_order',$arr['id'],$mods);
	}
	return 0;
}

function orders_list () {
	global $tpl;

	// use session to decide wether to show the orders list or not
	// TODO: add get_conf here
	if(!isset($_SESSION['show_orders_list'])) $_SESSION['show_orders_list']=false;
	$show_orders=$_SESSION['show_orders_list'];

	unset($_SESSION['select_all']);

	$_SESSION['go_back_to_cat']=0;

	$user = new user($_SESSION['userid']);

	if(table_is_closed($_SESSION['sourceid']) && !$user->level[USER_BIT_CASHIER]) {
		table_closed_interface();
		return 0;
	}

	$_SESSION['order_added']=0;
	$tpl -> set_waiter_template_file ('orders');

	if(table_is_takeaway ($_SESSION['sourceid'])) {
		$tpl -> set_waiter_template_file ('orders_takeaway');
		takeaway_form();
	}

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
		$tpl -> append ('commands',$tmp);
		
	if (!orders_service_fee_exists () && get_conf(__FILE__,__LINE__,'service_fee_use')) {
		$tmp = '<a href="orders.php?command=create&amp;dishid='.SERVICE_ID.'">'.ucfirst(phr('CREATE_SERVICE_FEE')).'</a><br/>';
		$tpl -> append ('commands',$tmp);
	}

	$associated_waiter = table_is_associated ();
	if (get_conf(__FILE__,__LINE__,"disassociation_allow")
		&& $associated_waiter && ($associated_waiter == $_SESSION ['userid'] || $user->level[USER_BIT_CASHIER] )
		) {
		$tmp = '<a href="orders.php?command=dissociate">'.ucfirst(phr('DISSOCIATE')).'</a><br/>';
		$tpl -> append ('commands',$tmp);
		}
		
	if ($user->level[USER_BIT_CASHIER]) {
		$tmp = '<a href="orders.php?command=ask_move">'.ucfirst(phr('MOVE_TABLE')).'</a><br/>';
		$tpl -> append ('commands',$tmp);
		}
		
	if ($user->level[USER_BIT_CASHIER] && table_is_closed($_SESSION['sourceid'])) {
		$tmp = '<a href="orders.php?command=reopen_confirm">'.ucfirst(phr('REOPEN_TABLE')).'</a><br/>';
		$tpl -> append ('commands',$tmp);
	}
	if ($_SESSION['show_orders_list']==false) {
		$desc=ucfirst(phr('SHOW_ORDERS'));
	} else  {
		$desc=ucfirst(phr('HIDE_ORDERS'));
	}
	$tmp = '<a href="orders.php?command=set_show_orders">'.$desc.'</a><br/>';
	$tpl -> append ('commands',$tmp);


	$tmp = categories_list();
	$tpl -> assign ('categories',$tmp);

	
	 $tmp = letters_list();
	 $tpl -> assign ('letters',$tmp);
	 

	if(CONF_FAST_ORDER){
		$tmp = order_fast_dishid_form ();
		$tpl -> assign ('fast_order_id',$tmp);
	} else {
		$tmp = keys_orders ();
		$tpl -> append ('scripts',$tmp);
	}

	// use session to decide wether to show the orders list or not
	if(!isset($_SESSION['show_toplist'])) $_SESSION['show_toplist']=get_conf(__FILE__,__LINE__,"top_list_show_top");
	if($_SESSION['show_toplist']) {
		toplist_show();
	} elseif(get_conf(__FILE__,__LINE__,"top_list_show_top")) {
		$tmp = '<a href="orders.php?command=set_show_toplist">'.ucphr('SHOW_TOPLIST').'</a><br/>';
		$tpl -> assign ('toplist',$tmp);
	}

	$tmp = command_bar_table_vertical();
	$tpl -> assign ('vertical_navbar',$tmp);

	if($show_orders) {
		$table -> list_orders ();
	}

	if (get_conf(__FILE__,__LINE__,"show_summary")) {
		$query="SELECT * FROM `orders`WHERE `sourceid`='".$_SESSION['sourceid']."' AND `id`=`associated_id` ORDER BY `timestamp` DESC LIMIT 1";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
		$arr = mysql_fetch_array($res);
		$mods=get_conf(__FILE__,__LINE__,"show_mods_in_summary");
		$table -> list_orders ('last_order',$arr['id'],$mods);
	}
	return 0;
}

function dishlist_form_start ($back_to_cat=false) {
	$output = '
	<form action="orders.php" method="POST" name="order_form" id="order_form_dishes" onSubmit="quickDishOrder();return false;">
	<input type="hidden" name="command" value="create">
	<input type="hidden" name="dishid" id="form_order_dishid" value="0">
	<input type="hidden" name="from_category" value="1">';
	return $output;
}

function dishlist_form_end () {
	$output = '
	</form>
	';
	return $output;
}

function dishlist_back_to_cat () {
	if(get_conf(__FILE__,__LINE__,'creation_back_to_category')) $back_to_cat_chk=' checked';
	elseif($_SESSION['go_back_to_cat']) $back_to_cat_chk=' checked';
	else $back_to_cat_chk='';

	$output = '
	<INPUT TYPE="checkbox" class="poscheck" NAME="back_to_cat" VALUE="1"'.$back_to_cat_chk.'> '.ucphr('COME_BACK_HERE');
	return $output;


}

function priority_radio ($data) {
	// code modded from get to post. !!CHECK THIS!!

	if(table_is_takeaway($_SESSION['sourceid'])) {
		$output = '
	<input type="hidden" name="data[priority]" value=1>';
		return $output;
	}

	if((!isset($data['priority']) || !$data['priority']) && $data['category']) {
		$cat = new category ($data['category']);
		if ($cat->data['priority']) $data['priority']=$cat->data['priority'];
		elseif ($tmp=get_conf(__FILE__,__LINE__,"default_priority")) $data['priority']=$tmp;
	}

	for ($i=1;$i<4;$i++) $chk[$i]="";
	if(isset($data['priority'])) $chk[$data['priority']]="checked";

	$output = '
	'.ucfirst(phr('PRIORITY')).':
	<label for="priority1">1</label><input type="radio" '.$chk[1].' name="data[priority]" id="priority1" value="1"></input>
	<label for="priority2">2</label><input type="radio" '.$chk[2].' name="data[priority]" id="priority2" value="2"></input>
	<label for="priority3">3</label><input type="radio" '.$chk[3].' name="data[priority]" id="priority3" value="3"></input>
	';
	return $output;	
}

function quantity_list ($data=array()) {
	// code modded from get to post. !!CHECK THIS!!
	$tmp = '';

	$default_quantity=get_conf(__FILE__,__LINE__,"default_quantity");
	$selected_qty = $default_quantity;

	if (isset($data['quantity']) && $data['quantity']>0) $selected_qty = $data['quantity'];

	if (!isset($data['nolabel']) || !$data['nolabel']) $tmp = ucfirst(phr('QUANTITY')).':';
	$tmp .= '
	<input type="hidden" id="dishquantity" value="1">
	<select class="pos" name="data[quantity]" onChange="setDishSelectedQuantity(this.selectedIndex)" size="1">';
	for ($i=1; $i<=MAX_QUANTITY; $i++) {
		if ($i==$selected_qty) $selected = ' selected';
		else $selected = '';

		$tmp .= '
		<option class="pos" value="'.$i.'"'.$selected.'>'.$i.'</option>';
	}
	$tmp .= '
	</select>';
	return $tmp;
}

function dishes_list_cat ($data){
	$output = '';

	$cat = new category($data['category']);

	if ($data['category']<=0) {
		if(get_conf(__FILE__,__LINE__,"invisible_show"))
		$query="SELECT dishes.*
			FROM `dishes`
			WHERE dishes.deleted='0'
			ORDER BY category ASC, name ASC";
		else
		$query="SELECT dishes.*
			FROM `dishes` 
			WHERE `visible`='1' 
			AND dishes.deleted='0'
			ORDER BY category ASC, name ASC";
	} else {
		if(get_conf(__FILE__,__LINE__,"invisible_show")) $query="SELECT dishes.* FROM `dishes` WHERE category='".$data['category']."' ORDER BY name ASC";
		else $query="SELECT dishes.*
			FROM `dishes`
			WHERE category='".$data['category']."'
			AND `visible`='1'
			AND dishes.deleted='0'
			ORDER BY name ASC";

		$class=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],"categories","htmlcolor",$data['category']);
		$dishcat=$data['category'];
	}
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return '';
	
	$output .= '<table bgcolor="'.COLOR_TABLE_GENERAL.'">
	<tr>
	<th scope=col>'.ucfirst(phr('ID')).'</th>
	<th scope=col>'.ucfirst(phr('NAME')).'</th>
	<th scope=col>'.country_conf_currency (true).'</th>
	</tr>
	';

	// ascii letter A
	$i=65;
	unset($GLOBALS['key_binds_letters']);

	while ($arr = mysql_fetch_array ($res)) {
		$dishid = $arr['id'];
		$dishname = $arr['name'];
		if ($dishname == null || strlen(trim($dishname)) == 0)
		$dishname = $arr['name'];

		$dishprice = $arr['price'];

		if($data['category']<=0) {
			$dishcat = $arr['category'];
			debug_msg(__FILE__,__LINE__,"dishcat: $dishcat");
			$class=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'categories',"htmlcolor",$dishcat);
			debug_msg(__FILE__,__LINE__,"class: $class");
		}

		if ($dishcat>0){
			// letters array follows
			if($i<91) {
				$GLOBALS['key_binds_letters'][$i]=$dishid;
				$letter = chr($i);
				$i++;
			} else $letter='';
		
			$output .= '
			<tr>
				<td bgcolor="'.$class.'">'.$letter.'</td>
				<td bgcolor="'.$class.'" onclick="order_select('.$dishid.',\'order_form\'); return false;"><a href="#" onclick="JavaScript:order_select('.$dishid.',\'order_form\'); return false;">'.$dishname.'</a></td>
				<td bgcolor="'.$class.'">'.$dishprice.'</td>
			</tr>';
		}
	}
	$output .= '
	</table>';

	return  $output;
}

function dishes_list_cat_pos ($data){
	$output = '';

	$cat = new category($data['category']);

	if ($data['category']<=0) {
		if(get_conf(__FILE__,__LINE__,"invisible_show"))
		$query="SELECT dishes.*
			FROM `dishes`
			WHERE dishes.deleted='0'
			ORDER BY category ASC, name ASC";
		else
		$query="SELECT dishes.*, categories.image
			FROM `dishes` 
			WHERE `visible`='1' 
			AND dishes.deleted='0'
			ORDER BY category ASC, name ASC";
	} else {
		if(get_conf(__FILE__,__LINE__,"invisible_show")) {
			$query="SELECT dishes.*, categories.image as imgcat
					FROM categories, dishes 
					WHERE category='".$data['category']."'";
		} else { 
			$query="SELECT dishes.*, categories.image as imgcat
			FROM categories, dishes 
			WHERE categories.id = '".$data['category']."' AND  
			category='".$data['category']."'
			AND `visible`='1'
			AND dishes.deleted='0'";
		}
		
		$class=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],"categories","htmlcolor",$data['category']);
		$dishcat=$data['category'];
	}
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return '';
	//$output .= '<table bgcolor="'.COLOR_TABLE_GENERAL.'">';
	$output .= '<table>';

	// ascii letter A
	$i=65;
	unset($GLOBALS['key_binds_letters']);

	$cikel=-1;
	while ($arr = mysql_fetch_array ($res)) {
		$cikel++;
		$dishid = $arr['id'];
		$dishname = $arr['name'];
		if ($dishname == null || strlen(trim($dishname)) == 0)
		$dishname = $arr['name'];

		$dishprice = $arr['price'];
		$image = (isset($arr['image']) ? $arr['image'] : IMAGE_DISH_DEFAULT);
		if($data['category']<=0) {
			$dishcat = $arr['category'];
			debug_msg(__FILE__,__LINE__,"dishcat: $dishcat");
			$class=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'categories',"htmlcolor",$dishcat);
			debug_msg(__FILE__,__LINE__,"class: $class");
		}

		if (!$cikel%6) {
			$output .= '<tr>';
		}

		if ($dishcat>0){
			// letters array follows
			if($i<91) {
				$GLOBALS['key_binds_letters'][$i]=$dishid;
				$letter = chr($i);
				$i++;
			} else $letter='';
				
			$output .= '
			<td onclick="dishOrder('.$dishid.'); return false;">
				<a href="#" class="buttonDish">
						<img src="'.$image.'" >
						<br />
						'.wordwrap(strtoupper($dishname),25,"<br/>\n").'
					</a>
			</td>';
				
			if(!(($cikel+1)%6)) {
				$output .= '</tr>';
			}

		}
	}
	$output .= '
	</table>';

	return  $output;
}

function input_dish_id ($data){
	$output = '';

	//$output .= ucphr('DISH').': <input onChange="document.order_form.submit()" type="text" name="dishid" id="dishid" value="" size="6" maxlength="6">';
	$output .= ucphr('DISH').': <input type="text" name="dishid" id="quickdishid" value="" size="6" maxlength="6">';

	return  $output;
}

function dishes_list_letter ($data){
	$output = '';

	$letter = $data['letter'][0];
	if($letter=='\\') $letter=$data['letter'][0].$data['letter'][1];

	if(empty($letter)) return '';

	if(get_conf(__FILE__,__LINE__,"invisible_show")) {
		$query="SELECT dishes.*,
		FROM `dishes`
		WHERE `name` LIKE '".$letter."%' 
		AND dishes.deleted='0'
		ORDER BY name ASC";
	} else {
		$query="SELECT dishes.*
		FROM `dishes`
		WHERE `name` LIKE '".$letter."%'
		AND `visible`='1'
		AND dishes.deleted='0'
		ORDER BY name ASC";
	}
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return '';

	$output .= '<table bgcolor="'.COLOR_TABLE_GENERAL.'">
	<tr>
	<th scope=col>'.ucfirst(phr('ID')).'</th>
	<th scope=col>'.ucfirst(phr('NAME')).'</th>
	<th scope=col>'.country_conf_currency (true).'</th>
	</tr>
	';

	// ascii letter A
	$i=65;
	unset($GLOBALS['key_binds_letters']);

	while ($arr = mysql_fetch_array ($res)) {
		$class=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],"categories","htmlcolor",$arr['category']);
		$dishcat=$arr['category'];
		$dishid = $arr['id'];

		//RTG: no more queries, please
		// k: i'm sorry, but we have to do that, because of other charsets!
		$dishobj = new dish ($arr['id']);
		$dishname = $dishobj -> name ($_SESSION['language']);

		$dishname = $arr['name'];
		if ($dishname == null || strlen(trim($dishname)) == 0)
			$dishname = $arr['name'];

		if(strtolower($dishname{0})!=strtolower($letter)) continue;
		$dishprice = $arr['price'];

		if ($dishcat>0){
			// letters array follows
			if($i<91) {
				$GLOBALS['key_binds_letters'][$i]=$dishid;
				$local_letter = chr($i);
				$i++;
			} else $local_letter='';
				
			$output .= '<tr>
			<td bgcolor="'.$class.'">'.$local_letter.'</td>';
				
			$output .= '<td bgcolor="'.$class.'" onclick="order_select('.$dishid.',\'order_form\'); return false;"><a href="#" onclick="JavaScript:order_select('.$dishid.',\'order_form\'); return false;">'.$dishname.'</a></td>';
			$output .= '<td bgcolor="'.$class.'">'.$dishprice.'</td>
			</tr>';
		}
	}
	$output .= '
	</table>';

	return  $output;
}

function dishes_list_search ($data){
	$output = '';

	$search = strtolower(trim($data['search']));

	if(empty($search)) return '';

	$query="SELECT dishes.*
	FROM `dishes`
	WHERE (LCASE(`name`) LIKE '".$search."%'
		OR LCASE(`name`) LIKE '% ".$search."%'
		)";
	if(!get_conf(__FILE__,__LINE__,"invisible_show")) {
		$query .= "AND `visible`='1'";
	}
	$query .= "
	AND dishes.deleted='0'
	ORDER BY name ASC";

	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return '';

	$output .= '<table bgcolor="'.COLOR_TABLE_GENERAL.'">
	<tr>
	<th scope=col>'.ucfirst(phr('ID')).'</th>
	<th scope=col>'.ucfirst(phr('NAME')).'</th>
	<th scope=col>'.country_conf_currency (true).'</th>
	</tr>
	';

	// ascii letter A
	$i=65;
	unset($GLOBALS['key_binds_letters']);

	while ($arr = mysql_fetch_array ($res)) {
		$class=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],"categories","htmlcolor",$arr['category']);
		$dishcat=$arr['category'];
		$dishid = $arr['id'];

		$dishobj = new dish ($arr['id']);
		$dishname = $dishobj -> name ($_SESSION['language']);

		if ($dishname == null || strlen(trim($dishname)) == 0)
			$dishname = $arr['name'];

		$dishprice = $arr['price'];

		if ($dishcat>0){
			// letters array follows
			if($i<91) {
				$GLOBALS['key_binds_letters'][$i]=$dishid;
				$local_letter = chr($i);
				$i++;
			} else $local_letter='';
				
			$output .= '<tr>
			<td bgcolor="'.$class.'">'.$local_letter.'</td>';
				
			$output .= '<td bgcolor="'.$class.'" onclick="order_select('.$dishid.',\'order_form\'); return false;"><a href="#" onclick="JavaScript:order_select('.$dishid.',\'order_form\'); return false;">'.$dishname.'</a></td>';
			$output .= '<td bgcolor="'.$class.'">'.$dishprice.'</td>
			</tr>';
		}
	}
	$output .= '
	</table>';

	return  $output;
}

function order_priority_class($priority){
	$classpriority='#FFFFFF';
	switch($priority){
		case 1:
			if($_SESSION['catprinted'][1]){
				$classpriority=COLOR_ORDER_PRIORITY_PRINTED;
			} else {
				$classpriority=COLOR_ORDER_PRIORITY_1;
			}
			break;
		case 2:
			if($_SESSION['catprinted'][2]){
				$classpriority=COLOR_ORDER_PRIORITY_PRINTED;
			} else {
				$classpriority=COLOR_ORDER_PRIORITY_2;
			}
			break;
		case 3:
			if($_SESSION['catprinted'][3]){
				$classpriority=COLOR_ORDER_PRIORITY_PRINTED;
			} else {
				$classpriority=COLOR_ORDER_PRIORITY_3;
			}
			break;
	}
	return $classpriority;
}

function order_extra_msg($extra_care){
	if ($extra_care) {
		$extra_msg = ucfirst(phr('EXTRA_CARE_ABBR'));
	} else {
		$extra_msg = "";
	}
	return $extra_msg;
}

function order_extra_class($extra_care,$class){
	if ($extra_care) {
		$classextra=COLOR_ORDER_EXTRACARE;
	} else {
		$classextra=$class;
	}
	return $classextra;
}

function order_printed_class($printed,$suspended){
	if ($printed) {
		$class=COLOR_ORDER_PRINTED;
	} elseif ($printed==NULL && $suspended==0) {
		$class=COLOR_ORDER_TO_PRINT;
	} elseif ($suspended==1) {
		$class=COLOR_ORDER_SUSPENDED;
	}
	return $class;
}

function order_print_time_class($orderid){
	$orderid= (int)$orderid;
	$ord = new order ($orderid);
	$elapsed = orders_print_elapsed_time ($ord);
	if($elapsed<1) return '';

	$level=100/CONF_COLOUR_PRINTED_MAX_TIME*$elapsed;
	$level=round($level,0);
	if($level>255) $level=255;
	if($level<0) $level=0;
	$level=255-$level;
	$level = sprintf("%02x",$level);
	switch(strtolower(CONF_COLOUR_PRINTED_COLOUR)) {
		case 'red': $class='#'.'FF'.$level.$level; break;
		case 'green': $class='#'.$level.'FF'.$level; break;
		case 'blue': $class='#'.$level.$level.'FF'; break;
		case 'magenta': $class='#'.'FF'.$level.'FF'; break;
		case 'cyan': $class='#'.$level.'FF'.'FF'; break;
		case 'yellow': $class='#'.'FF'.'FF'.$level; break;
		case 'grey': $class='#'.$level.$level.$level; break;
		default: $class='#'.'FF'.'FF'.$level; break;
	}
	return $class;
}



?>