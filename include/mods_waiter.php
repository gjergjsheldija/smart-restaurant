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

function mods_set ($start_data) {
	global $tpl;

	$id= (int) $start_data['id'];
	$ord = new order($id);

	// if we're not modifying the entire order, we requantify the old one, and create a new one
	if($start_data['quantity'] < $ord->data['quantity']) {
		$err = mods_create_order($start_data);
		status_report ('CREATION',$err);
		
		$start_data['id'] = $GLOBALS['start_data']['id'];
	}
	
	if($err = mods_apply_ingreds ($start_data)) return $err;

	if($err =$ord -> price()) return $err;

	return 0;
}

function mods_apply_ingreds ($start_data) {
	global $tpl;
	$err = 0;

	$id= (int) $start_data['id'];
	$ord = new order($id);

	$ord -> ingredients_arrays();
	
	// creates a list of ingredients to be added or removed from the actual situation
	$add = array();
	$remove = array();
	if(isset($start_data['ingreds']) && is_array($start_data['ingreds']) && is_array($ord->ingredients['contained'])) {
		$add = array_diff($start_data['ingreds'], $ord->ingredients['contained']);
		$remove = array_diff($ord->ingredients['contained'], $start_data['ingreds']);
	} elseif (isset($start_data['ingreds']) && is_array($start_data['ingreds'])) {
		$add = $start_data['ingreds'];
	} elseif (is_array($ord->ingredients['contained'])) {
		$remove = $ord->ingredients['contained'];
	}

	$operation = 1;
	foreach ($add as $ingredid) {
		$err += mods_create_ingreds ($ord, $ingredid, $operation);
	}
	
	$operation = -1;
	foreach ($remove as $ingredid) {
		$err += mods_create_ingreds ($ord, $ingredid, $operation);
	}

	if (isset($start_data['ingred_qty']) && is_array($start_data['ingred_qty'])) {
		foreach ($start_data['ingred_qty'] as $ingredid => $value) {
			$err += mods_update_ingred_qty ($ord->id,$ingredid,$value);
		}
	}
	return $err;
}

function mods_update_ingred_qty ($ordid, $ingredid, $value) {
	$query ="SELECT * FROM `orders` WHERE `associated_id`='".$ordid."' AND `ingredid` = '".$ingredid."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return mysql_errno();
	
	$arr = mysql_fetch_array ($res);
	if(!$arr && $value==0) {
		return 0;
	} elseif(!$arr && $value!=0) {
		$ord = new order($ordid);
		$ord -> ingredients_arrays();

		if (in_array($ingredid,$ord->ingredients['nominal'])){
			$ord = new order($ordid);
			$operation = 0;
			$err = mods_create_ingreds ($ord, $ingredid, $operation);
			if($err) return ERR_MOD_NOT_CREATED;
			
			$query ="SELECT * FROM `orders` WHERE `associated_id`='".$ordid."' AND `ingredid` = '".$ingredid."'";
			$res=common_query($query,__FILE__,__LINE__);
			if(!$res) return mysql_errno();
			$arr = mysql_fetch_array ($res);
		}
	}

	$id= (int) $arr['associated_id'];
	$ord = new order($id);
	$ord -> ingredients_arrays();

	if($value==0 && $arr['operation']==0) {
		$query ="DELETE FROM `orders` WHERE `id`='".$arr['id']."'";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return mysql_errno();
	} elseif($arr['operation']!=-1) {
		$query ="UPDATE `orders` SET `ingred_qty`='".$value."' WHERE `id`='".$arr['id']."'";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return mysql_errno();
	}

	return 0;
}

// operates on orders.
// operation tells wether working with:
// 1: adding ingredients (extra or previously removed)
// -1: removing ingredients (removed or previously added)
function mods_create_ingreds ($ord, $ingredid, $operation) {
	$query ="SELECT * FROM `orders` WHERE `associated_id`='".$ord->id."' AND `ingredid` = '".$ingredid."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return mysql_errno();

	$antiop = $operation * -1;
	
	// if order is found and is opposite to the wanted one, we simply delete it,
	// otherwise we create the wanted order
	if(mysql_num_rows($res)) {
		$arr = mysql_fetch_array($res);
		
		if($arr['operation']==$antiop) {
			$ord_ingid = (int) $arr['id'];
			$ord_ing = new order ($ord_ingid);
			
			if(class_exists('stock_object')) {
				$stock = new stock_object;
				$stock -> silent = true;
				$stock -> remove_from_waiter($ord_ing->id,0);
			}
			
			if($err = $ord_ing -> delete()) return $err;
		}
	} else {
		$ord_ing = new order();
		
		$ord_ing -> data = $ord -> data;
		// now unsets some vars that we don't want to copy
		unset($ord_ing -> data['id']);
		unset($ord_ing -> data['associated_id']);
		unset($ord_ing -> data['price']);
		unset($ord_ing -> data['timestamp']);
		
		// set quantity to 0 to start with empty order (for stock function)
		$ord_ing -> data['quantity'] = 0;
		
		$ord_ing -> data['dishid'] = MOD_ID;
		$ord_ing -> data['ingredid'] = $ingredid;
		$ord_ing -> data['associated_id'] = $ord->id;
		
		$err = $ord_ing -> create ();
		
		$ord_ing -> data['operation'] = $operation;
		$ord_ing -> data['quantity'] = $ord -> data['quantity'];
		
		if(class_exists('stock_object')) {
			$stock = new stock_object;
			$stock -> silent = true;
			$stock -> remove_from_waiter($ord_ing->id, $ord -> data['quantity']);
		}
		
		$err += $ord_ing -> set();
		
		if($err) return 1;
	
	}
	return 0;
}

function mods_create_order ($start_data) {
	$id= (int) $start_data['id'];
	$old = new order($id);
	
	// requantify the old order
	$old->data['quantity'] = $old->data['quantity'] - $start_data['quantity'];
	
	if(class_exists('stock_object')) {
		$stock = new stock_object;
		$stock -> silent = true;
		$stock -> remove_from_waiter($old->id,$old->data['quantity']);
	}
	
	$err=$old -> set();
	if($err) return 1;			// quantity set error
	
	// creates the new order;
	$arr['quantity'] = 0;
	$newid = orders_create ($ord->data['dishid'],$arr);
	if($newid == 0) return 1;			// order not created
	
	$newid = (int) $newid;
	$new = new order($newid);
	$olddata = $old->data;				// copies old order's data
	
	// now unsets some vars that we don't want to copy
	unset($olddata['id']);
	unset($olddata['associated_id']);
	unset($olddata['price']);
	unset($olddata['timestamp']);
	
	$new -> data = $olddata;
	
	//first set() without quantity for stock function
	$new -> data['quantity'] = 0;
	$err = $new -> set();
	
	$new -> data['quantity'] = $start_data['quantity'];
	
	if(class_exists('stock_object')) {
		$stock = new stock_object;
		$stock -> silent = true;
		$stock -> remove_from_waiter($new->id,$new -> data['quantity']);
	}
	
	$err = $new -> set();

	if($err) return 1;
	
	// Now we set $start_data[id] to the new order, because we're going to work on it, and leave the old one
	$GLOBALS['start_data']['id'] = $new->id;
	
	return 0;
}

function mods_form_start ($ord) {
	$output = '<FORM ACTION="orders.php" METHOD=POST NAME="form1">'."\n";
	$output .= '<INPUT TYPE="HIDDEN" NAME="data[id]" VALUE="'.$ord->id.'">'."\n";
	$output .= '<INPUT TYPE="HIDDEN" NAME="command" VALUE="mod_set">'."\n";
	$output .= '<INPUT TYPE="HIDDEN" NAME="letter" VALUE="">'."\n";
	$output .= '<INPUT TYPE="HIDDEN" NAME="last" VALUE="1">'."\n";
	return $output;
}

function mods_quantity ($ord) {
	if(AUTOSELECT_FIRST) $autoselect=1;
	else $autoselect = $ord->data['quantity'];

	$output = ucfirst(phr('QUANTITY_TO_MODIFY')).':<br/>'."\n";
	$output .= '<SELECT NAME="data[quantity]" SIZE="4">'."\n";
	for($j=1;$j<=$ord->data['quantity'];$j++) {
		if($j==$autoselect) $selected=' selected';
		else $selected='';
		$output .= '<OPTION VALUE="'.$j.'"'.$selected.'>'.$j."\n";
	}
	$output .= '</SELECT><br/>'."\n";
	return $output;
}

function mods_list_delete ($ord) {
	$linecounter=0;
	$divider=10;
	$output = '';

	$ord -> ingredients_arrays();
	
	$output .= '
<table class="receipt-table">
	<tbody>
	<tr>
		<td></td>	
		<td><strong>' . ucfirst(phr('CONTAINED')).'</strong></td>
		<td style="text-align:center"><img src="../images/up.png" width="16" border="0" height="16"></td>
		<td style="text-align:center"><img src="../images/field.png" width="16" border="0" height="16"></td>
		<td style="text-align:center"><img src="../images/down.png" width="16" border="0" height="16"></td>
	</tr>
	';
	foreach ($ord->ingredients['contained'] as $name => $ingredid) {
		$ingr = new ingredient ($ingredid);
		$price = $ingr -> get ('price');
		
		$query ="SELECT `ingred_qty` FROM `orders` WHERE `associated_id`='".$ord->id."' AND `ingredid` = '".$ingredid."'";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return mysql_errno();
		
		$arr = mysql_fetch_array ($res);
		$qty = $arr['ingred_qty'];
			
		if ($qty==1) {
			$checked_lot = ' checked';
			$checked_normal = '';
			$checked_few = '';
		} elseif ($qty==-1) {
			$checked_lot = '';
			$checked_normal = '';
			$checked_few = ' checked';
		} else { 
			$checked_lot = '';
			$checked_normal = ' checked';
			$checked_few = '';
		}
		
		$output .= '
		<tr>
			<td>
				<input class="largerInput" type="checkbox" name="data[ingreds]['.$ingredid.']" value="'.$ingredid.'" checked>
			</td>
			<td onClick="check_elem(\'form1\',\'data[ingreds]\','.$ingredid.');return false;">
				'.ucfirst($name).' '.$price.'
			</td>
			<td>
				<input class="largerInput" type="radio" name="data[ingred_qty]['.$ingredid.']" value="1"'.$checked_lot.'>
			</td>
			<td>
				<input class="largerInput" type="radio" name="data[ingred_qty]['.$ingredid.']" value="0"'.$checked_normal.'>
			</td>
			<td>
				<input class="largerInput" type="radio" name="data[ingred_qty]['.$ingredid.']" value="-1"'.$checked_few.'>
			</td>
		</tr>';

		$linecounter++;
		$modulo=$linecounter % $divider;
		if($modulo==0){
			$output .= '
	</tbody>
</table>';
			$output .= navbar_form('form1','orders.php?command=list');
			$output .= '
<table>
	<tbody>';
		}
	}
	$output .= '
	</tbody>
</table>';
	return $output;
}

function mods_list_add ($ord,$letter='') {
	$output = '';
	
	$max_ingreds=get_conf(__FILE__,__LINE__,"max_ingreds_per_page");

	if($letter=='ALL') {
		$showall = true;
		$letter = '';
	} else $showall = false;

	if(!empty($letter)) $letter=ucfirst($letter[0]);

	$ord -> ingredients_arrays();
	if (!$showall && empty($letter) && count($ord->ingredients['available'])>$max_ingreds) return '';

	$linecounter=0;
	$divider=10;

	$output .= ucfirst(phr('AVAILABLE')).':<br/>'."\n";
	$output .= '
<table>
	<tbody>'."\n";
	foreach ($ord->ingredients['available'] as $name => $ingredid) {
		if(!empty($letter) && $name[0]!=$letter) continue;
		
		$ingr = new ingredient ($ingredid);
		if(!$ingr -> get ('visible') && !get_conf(__FILE__,__LINE__,"invisible_show")) continue;
		
		$price = $ingr -> get ('price');
		
		
		$output .= '
		<tr>
			<td><input type="checkbox" name="data[ingreds]['.$ingredid.']" value="'.$ingredid.'"></td>
			<td onClick="check_elem(\'form1\',\'data[ingreds]\','.$ingredid.');return false;">'.ucfirst($name).' '.$price.'</td>
			<td><input type="radio" name="data[ingred_qty]['.$ingredid.']" value="1"></td>
			<td onClick="check_ingredqty('.$ingredid.',0);return false;">+&nbsp;&nbsp;</td>
			<td><input type="radio" name="data[ingred_qty]['.$ingredid.']" value="0" checked></td>
			<td onClick="check_ingredqty('.$ingredid.',1);return false;">=&nbsp;&nbsp;</td>
			<td><input type="radio" name="data[ingred_qty]['.$ingredid.']" value="-1"></td>
			<td onClick="check_ingredqty('.$ingredid.',2);return false;">-</td>
		</tr>';

		$linecounter++;
		$modulo=$linecounter % $divider;
		if($modulo==0){
			$output .= '
	</tbody>
</table>';
			$output .= navbar_form('form1','orders.php?command=list');
			$output .= '
<table>
	<tbody>';
		}
	}
	$output .= '
	</tbody>
</table>';
	return $output;
}

function mods_letter_array ($ord) {
	$ord -> ingredients_arrays();
	
	$letters=array();
	foreach ($ord->ingredients['available'] as $name => $ingredid) {
		$ingr = new ingredient ($ingredid);
		if(!$ingr -> get ('visible') && !get_conf(__FILE__,__LINE__,"invisible_show")) continue;
		
		$char=ucfirst($name[0]);
		
		if(!in_array($char,$letters)) $letters[]=$char;
	}
	return $letters;
}

function mods_letter_list ($ord) {
	$char_per_line =5;
	$i=0;
	$output = '';
	
	$i++;

	$letters=mods_letter_array($ord);
	
	$output .= '		<a href="#" onclick="JavaScript:mod_set(\'ALL\'); return false;">'.ucfirst(phr('ALL')).'</a><br/>'."\n";
	foreach ($letters as $char) {
		$output .= '		<a href="#" onclick="JavaScript:mod_set(\''.$char.'\'); return false;">'.$char.'</a>&nbsp;&nbsp;'."\n";
		if(($i % $char_per_line) == 0)
			$output .= "<br/>\n";
		$i++;
	}
	return $output;
}

function mods_letter_list_pos ($form ,$ord) {
	$char_per_line =5;
	$i=0;
	$output = '';
	
	$i++;

	$letters=mods_letter_array($ord);
	
	$output .= '<a href="#" onclick="getDishStartingByLetter(\'' . $form . '\',\'ALL\'); return false;">'.ucfirst(phr('ALL')).'</a><br/>'."\n";
	foreach ($letters as $char) {
		$output .= '<a href="#" onclick="getDishStartingByLetter(\'' . $form . '\',\''.$char.'\'); return false;">'.$char.'</a>&nbsp;&nbsp;'."\n";
		if(($i % $char_per_line) == 0)
			$output .= "<br/>\n";
		$i++;
	}
	return $output;
}

function mods_list($start_data,$letter='') {
	global $tpl;
	
	$_SESSION['order_added']=0;

	$tpl->set_waiter_template_file ('modslist');

	$tmp = navbar_form('form1','orders.php?command=list');
	$tpl->assign ('navbar',$tmp);
	
	$tmp=(int) $start_data['id'];
	if(!$ord = new order($tmp)) return 1;
	
	$tmp = mods_form_start ($ord);
	$tpl->assign ('form_start',$tmp);
	
	$tmp = "</FORM>\n";
	$tpl->assign ('form_end',$tmp);

	$tmp = mods_quantity ($ord);
	$tpl->assign ('mod_quantity',$tmp);

	$tmp =mods_letter_list ($ord);
	$tpl -> assign ('mod_letters',$tmp);

	$tmp = mods_key_binds ($ord);
	$tpl -> append ('scripts',$tmp);

	$ord -> ingredients_arrays();
	
	if(!empty($ord->ingredients['contained'])) {
		$tmp = mods_list_delete ($ord);
		$tpl -> assign ('delete_list',$tmp);
	}
	
	if(!empty($ord->ingredients['available'])) {
		$tmp = mods_list_add ($ord,$letter);
		$tpl -> assign ('add_list',$tmp);
	}
}
function mods_list_pos($start_data,$letter='') {
	global $tpl;
	
	$_SESSION['order_added']=0;

	$tpl->set_waiter_template_file ('modslist_pos');

	$tmp = navbar_form_pos('form1','orders.php?command=list');
	$tpl->assign ('navbar',$tmp);
	
	$tmp=(int) $start_data['id'];
	if(!$ord = new order($tmp)) return 1;
	
	$tmp = mods_form_start ($ord);
	$tpl->assign ('form_start',$tmp);
	
	$tmp = "</FORM>\n";
	$tpl->assign ('form_end',$tmp);

	$tmp = mods_quantity ($ord);
	$tpl->assign ('mod_quantity',$tmp);

	$tmp = mods_letter_list_pos ('form1',$ord);
	$tpl->assign ('mod_letters',$tmp);

	$tmp = mods_key_binds_pos ('form1',$ord);
	$tpl->append ('scripts',$tmp);

	$ord->ingredients_arrays();
	
	if(!empty($ord->ingredients['contained'])) {
		$tmp = mods_list_delete ($ord);
		$tpl->assign ('delete_list',$tmp);
	}
	
	if(!empty($ord->ingredients['available'])) {
		$tmp = mods_list_add ($ord,$letter);
		$tpl->assign ('add_list',$tmp);
	}
	
	return 0;
}

function mods_key_binds_pos ($form, $ord) {
	$output = '
<script language="JavaScript1.2">
<!--
	if(!document.all) {
		window.captureEvents(Event.KEYUP);
	} else {
		document.onkeypress = keypressHandler;
	}
	function keypressHandler(e) {
		var e;
		if(document.all) { //it\'s IE
			e = window.event.keyCode;
		} else {
			e = e.which;
		}
		
		// key bindings
		';
		
		// letters
		$letters_arr=mods_letter_array($ord);
		for ($i=65;$i<=90;$i++) {
			$letter = chr($i);
			if(in_array($letter, $letters_arr, false)) {
				$output .= '
		if (e == '.$i.') getDishStartingByLetter(\''.$form.'\',\''.$letter.'\');';
			}
		}
		
		// numbers
		for($i=1;$i<=$ord->data['quantity'];$i++) {
			$j=$i+48;
			$output .= '
		if (e == '.$j.') select_one(\'form1\',\'data[quantity]\','.($i-1).');';
		}
		
		$output .= '
		// key bindings end
	}
	window.onkeyup = keypressHandler;
//-->
</script>';
	return $output;
}
function mods_key_binds ($ord) {
	$output = '
<script language="JavaScript1.2">
<!--
	if(!document.all) {
		window.captureEvents(Event.KEYUP);
	} else {
		document.onkeypress = keypressHandler;
	}
	function keypressHandler(e) {
		var e;
		if(document.all) { //it\'s IE
			e = window.event.keyCode;
		} else {
			e = e.which;
		}
		
		// key bindings
		';
		
		// letters
		$letters_arr=mods_letter_array($ord);
		for ($i=65;$i<=90;$i++) {
			$letter = chr($i);
			if(in_array($letter, $letters_arr, false)) {
				$output .= '
		if (e == '.$i.') mod_set(\''.$letter.'\');';
			}
		}
		
		// numbers
		for($i=1;$i<=$ord->data['quantity'];$i++) {
			$j=$i+48;
			$output .= '
		if (e == '.$j.') select_one(\'form1\',\'data[quantity]\','.($i-1).');';
		}
		
		$output .= '
		// key bindings end
	}
	window.onkeyup = keypressHandler;
//-->
</script>';
	return $output;
}

?>