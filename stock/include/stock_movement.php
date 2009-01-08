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
class stock_movement extends object {
	var $tmp;
	var $last_updated_stock_object;
	var $only_obj;
	
	function stock_movement ($id=0) {
		$this -> db = 'common';
		$this->table=$GLOBALS['table_prefix'].'stock_movements';
		$this->id=$id;
		$this -> title = ucphr('STOCK_MOVEMENTS');
		$this -> no_name = true;
		$this -> disable_new = true;
		$this->file=ROOTDIR.'/stock/index.php';
		$this->main_list_item = 'obj_id';
		$this->hide = array('obj_id','dish_id');
		$this->default_orderby = 'timestamp';
		$this->default_sort = 'desc';
		$this->fields_width=array(	'name'=>'40%',
								'dish_name'=>'40%',
								'timestamp'=>'20%');
		$this->fields_names=array(	'id'=>ucphr('ID'),
								'timestamp'=>ucphr('TIME'),
								'user_name'=>ucphr('WHO'),
								'name'=>ucphr('NAME'),
								'obj_id'=>ucphr('OBJECT'),
								'dish_name'=>ucphr('DISH'),
								'dish_quantity'=>ucphr('DISH_QUANTITY'),
								'quantity'=>ucphr('QUANTITY'),
								'unit_type'=>'',
								'value'=>ucphr('VALUE').' ['.country_conf_currency (true).']',
								'user'=>ucphr('USER'));
		$this -> fetch_data();
		
	}

	function list_link_base () {
		$this->link_base = $this->file.'?class='.get_class($this);
		if($this->category) $this->link_base.='&amp;data[category]='.$this->category;
		if($this->search) $this->link_base.='&amp;data[search]='.$this->search;
		
		if($this->only_obj) {
			$stock = new stock_object($this->only_obj);
			$ingred = new ingredient;
			$this->link_base = $ingred->file.'?class=ingredient&amp;command=edit&amp;data[id]='.$stock->data['ref_id'];
		}
		if($this->only_dish) {
			$dish = new dish($this->only_dish);
			$this->link_base = $dish->file.'?class=dish&amp;command=edit&amp;data[id]='.$dish->id;
		}
	}
	
	function list_query_all () {
		if(isset($_REQUEST['data']['only_obj']) && $_REQUEST['data']['only_obj']) $this -> only_obj = $_REQUEST['data']['only_obj'];
		if(isset($_REQUEST['data']['only_dish']) && $_REQUEST['data']['only_dish']) $this -> only_dish = $_REQUEST['data']['only_dish'];
		
		$table = "#prefix#stock_movements";
		$stock_table = "#prefix#stock_objects";
		$ingred_table = "#prefix#ingreds";
		$ingred_lang_table = "#prefix#ingreds_".$_SESSION['language'];
		$dish_table = "#prefix#dishes";
		$dish_lang_table = "#prefix#dishes_".$_SESSION['language'];
		//mizuko : added $table_users & modified tho the se username
		$table_users = "#prefix#users";
	
		$this->fields_show=array('id','timestamp','obj_id','dish_id','dish_quantity','unit_type','quantity','value','user');
		$query="SELECT
				$table.`id`,
				DATE_FORMAT($table.`timestamp`,'%e/%c/%Y %T') as 'timestamp',
				$table_users.`name` as `user_name`,
				$ingred_table.`name` as `name`,
				$table.`obj_id`,
				$dish_table.`name` as `dish_name`,
				$table.`dish_id`,
				$table.`dish_quantity`,
				$table.`unit_type`,
				$table.`quantity`,
				ROUND($table.`value`,2) as `value`
				FROM `$table`
				LEFT JOIN `$dish_table` ON $table.`dish_id`=$dish_table.id
				INNER JOIN $table_users on $table_users.`id` = $table.`user`,
				`$stock_table`,
				`$ingred_table`,
				`$ingred_lang_table`
				WHERE $stock_table.`id`=$table.`obj_id`
				AND $ingred_table.`id`=$stock_table.`ref_id`";
		//end : mizuko
		
		if(isset($this -> only_obj) && $this -> only_obj) $query .= " AND $table.`obj_id`=".$this -> only_obj;
		if(isset($this -> only_dish) && $this -> only_dish) $query .= " AND $table.`dish_id`=".$this -> only_dish;
		
		return $query;
	}
	
	function list_rows ($arr,$row) {
		global $tpl;
		global $display;

		$col=0;
		if(!$this->disable_mass_delete) {
			$display->rows[$row][$col]='<input type="checkbox" name="delete[]" value="'.$arr['id'].'">';
			$display->width[$row][$col]='1%';
			$col++;
		}
		
		$dish = new dish;
		foreach ($arr as $field => $value) {
			if(isset($this->hide) && in_array($field,$this->hide)) continue;
			
			$stock = new stock_object ($arr['obj_id']);
			$ingred = new ingredient;
			$link = $ingred->file.'?class=ingredient&amp;command=edit&amp;data[id]='.$stock->data['ref_id'];
			
			if($field=='dish_name') {
				if(!empty($value)) {
					$link = $dish->file.'?class=dish&amp;command=edit&amp;data[id]='.$arr['dish_id'];
					$display->links[$row][$col]=$link;
				} else $value = '';
			} elseif ($field=='unit_type') {
				$value = get_user_unit ($value);
			} elseif ($field=='quantity') {
				$unit = get_user_unit ($arr['unit_type']);
				$default_unit = get_default_unit ($arr['unit_type']);
				$value = convert_units ($value.' '.$default_unit, $unit);
			}
			
			
			$display->rows[$row][$col]=$value;
			if($link && $field=='name') $display->links[$row][$col]=$link;
			if($link) $display->clicks[$row][$col]='redir(\''.$link.'\');';
			$col++;
		}
	}
	
	function delete_all_from_obj ($obj_id) {
		$query="SELECT * FROM `".$this->table."` WHERE `obj_id`='".$obj_id."'";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return mysql_errno();
		
		while ($arr=mysql_fetch_array($res)) {
			$mov = new stock_movement ($arr['id']);
			$mov->silent=true;
			if($err=$mov->delete()) return $err;
		}
		return 0;
	}
	
	function check_values($input_data){
		$msg="";

		if($input_data['value']==="") {
			$msg=ucphr('CHECK_VALUE');
		}
		$input_data['value'] = eq_to_number ($input_data['value']);
		
		if($input_data['quantity']==="") {
			$msg=ucphr('CHECK_QUANTITY');
		}
		if(!isset($input_data['unit_type'])) $input_data['unit_type'] = get_unit_from_eq ($input_data['quantity']);		// should before modification of quantity
		$input_data['quantity'] = convert_units ($input_data['quantity']);
		
		$stock = new stock_object ($input_data['obj_id']);
		if((($stock->data['quantity']+$input_data['quantity'])<0) && $stock->data['quantity']>=0) {
			$msg=ucphr('CHECK_QUANTITY');
		}

		$input_data['user'] = $_SESSION['userid'];
		if ($msg) {
			echo "<script language=\"javascript\">
				window.alert(\"".$msg."\");
				window.history.go(-1);
			</script>\n";
			return -2;
		}
		//end : mizuko
		return $input_data;
	}

	function pre_delete ($input_data) {
		$this->fetch_data();
		
		$this->tmp=$this->data;
		
		$input_data['quantity']=$this->data['quantity']*-1;
		$input_data['value']=$this->data['value']*-1;
		$input_data['obj_id']=$this->data['obj_id'];
		
		$state = new stock_object ($input_data['obj_id']);
		
		// refetches data overriding cache
		$state->fetch_data(true);
		
		if(!$state->data['deleted']) {
			$state_data = array();
			$state_data['id'] = $input_data['obj_id'];
			$state_data['value'] = $this->unitary_value ($input_data);
			$state_data['quantity'] = $state->data['quantity']+$input_data['quantity'];
			$state_data['name'] = $state->data['name'];
			$state_data['ref_type'] = $state->data['ref_type'];
			$state_data['ref_id'] = $state->data['ref_id'];
			$state_data['deleted'] = $state->data['deleted'];
			$state_data['from'] = 'movement';
	
			if($err = $state->update ($state_data)) return $err;
		}
		return $input_data;
	}

	function unitary_value ($input_data) {
		if(!$this->id) return ERR_NO_STOCK_OBJECT_CHOSEN;
	
		$quantity = $input_data['quantity'];
		$total_value = $input_data['value'];
		
		$state = new stock_object ($input_data['obj_id']);
		
		$oldval = $state->data['value'];
		$oldqty = $state->data['quantity'];
		
		$newqty=$oldqty+$quantity;
		// if the stock is 0, the unitary value is set to 0
		if($newqty==0) return 0;
		
		$newtotval=$oldval*$oldqty+$total_value;
		
		$newval=$newtotval/$newqty;
		return $newval;
	}
	
	function post_insert ($input_data) {
		if(empty($input_data['obj_id'])) return ERR_NO_STOCK_OBJECT_CHOSEN;
		
		$stock = new stock_object ($input_data['obj_id']);
		
		$stock_data = array();
		$stock_data['id'] = $input_data['obj_id'];
		$stock_data['value'] = $this->unitary_value ($input_data);
		$stock_data['quantity'] = $stock->data['quantity']+$input_data['quantity'];
		$stock_data['name'] = $stock->data['name'];
		$stock_data['ref_type'] = $stock->data['ref_type'];
		$stock_data['ref_id'] = $stock->data['ref_id'];
		$stock_data['deleted'] = $stock->data['deleted'];
		$stock_data['unit_type'] = $input_data['unit_type'];
		$stock_data['from'] = 'movement';

		$stock -> silent = true;
		if($err = $stock->update ($stock_data)) return $err;
		
		$this->last_updated_stock_object = $input_data['obj_id'];
		return 0;
	}
	
	function post_insert_page ($class) {
		global  $tpl;
		
		$this->fetch_data();
		$stock = new stock_object ($this->data['obj_id']);
		
		if($stock->data['ref_type'] == TYPE_DISH) $type_class = 'dish';
		elseif($stock->data['ref_type'] == TYPE_INGREDIENT) $type_class = 'ingredient';
		
		$obj = new $type_class ($stock->data['ref_id']);
		$tmp = $obj -> form();
		$tpl -> assign("content", $tmp);
		
		if(method_exists($obj,'post_edit_page')) $obj->post_edit_page($class);
	}
	
	function post_delete_page ($class) {
		global  $tpl;
		
		$stock = new stock_object ($this->tmp['obj_id']);
		
		if($stock->data['ref_type'] == TYPE_DISH) $type_class = 'dish';
		elseif($stock->data['ref_type'] == TYPE_INGREDIENT) $type_class = 'ingredient';
		
		$obj = new $type_class ($stock->data['ref_id']);
		$tmp = $obj -> form();
		$tpl -> assign("content", $tmp);
		
		if(method_exists($obj,'post_edit_page')) $obj->post_edit_page($class);
	}
	
	function form($input_data=array()){
		if($this->id) {
			$editing=1;
			$query="SELECT * FROM `".$this->table."` WHERE `id`='".$this->id."'";
			$res=common_query($query,__FILE__,__LINE__);
			if(!$res) return mysql_errno();
			
			$arr=mysql_fetch_array($res);
		} else {
			$editing=0;
			$arr['id']=next_free_id($_SESSION['common_db'],$this->table);
		}
	$output .= '
	<div align="center">';
	
	if (!isset($input_data['obj_id'])) {
		$output .= '
		<a href="?class='.get_class($this).'">'.ucphr('BACK_TO_LIST').'.</a>';
	}
	
	$output .= '
	<table width="100%">
	<tr>
	<td>
	<fieldset>
	<legend>'.ucphr('STOCK_MOVEMENT').'</legend>

	<form action="'.$this->file.'?" name="edit_form_'.get_class($this).'" method="post">
	<input type="hidden" name="class" value="'.get_class($this).'">
	<input type="hidden" name="data[id]" value="'.$arr['id'].'">';
	
	if (isset($input_data['obj_id'])) {
		$output .= '
		<input type="hidden" name="data[obj_id]" value="'.$input_data['obj_id'].'">';
	}
	
	if($editing){
		$output .= '
		<input type="hidden" name="command" value="update">';
	} else {
	$output .= '
		<input type="hidden" name="command" value="insert">';
	}
	$output .= '
	<table>';
	
	if (!isset($input_data['obj_id'])) {
		$output .= '
			<tr>
				<td>
				'.ucphr('ID').':
				</td>
				<td>'.$arr['id'].'
				</td>
			</tr>
			<tr>
				<td>
				'.ucphr('OBJECT').':
				</td>
				<td>';
		if(!$editing){
			$output .= '
				<input type="text" name="data[obj_id]" value="'.$arr['obj_id'].'">';
		} else {
			$stock = new stock_object ($arr['obj_id']);
			if(!$stock->data['deleted']) $output .= '
				<a href="'.$stock->file.'?class=stock_object&command=edit&data[id]='.$arr['obj_id'].'">';
			$output .= '
				'.$stock->name($_SESSION['language']);
			if(!$stock->data['deleted']) $output .= '
				</a>';
		}
		$output .= '
				</td>
			</tr>';
	}
	$output .= '
		<tr>
			<td>
			'.ucphr('QUANTITY').':
			</td>
			<td>';
	if(!$editing){
		$output .= '
			<input type="text" name="data[quantity]" value="'.$arr['quantity'].'">';
	} else {
		$output .= '
			'.$arr['quantity'];
	}
	$output .= '
			</td>
		</tr>
		<tr>
			<td>
			'.ucphr('VALUE').':
			</td>
			<td>';
	if(!$editing){
		$output .= '
			<input type="text" name="data[value]" value="'.$arr['value'].'">';
	} else {
		$output .= '
			'.$arr['value'];
	}
	$output .= '
			</td>
		</tr>
		<tr>
			<td colspan=2 align="center">
			<table>
			<tr>
				<td>';
	if(!$editing){
		$output .= '
				<input type="submit" value="'.ucphr('INSERT').'">
	</form>
				</td>';
	} else {
		$output .= '
				<td>
	</form>
				</td>
				<td>
				<form action="'.$this->file.'?" name="delete_form_'.get_class($this).'" method="post">
				<input type="hidden" name="class" value="'.get_class($this).'">
				<input type="hidden" name="command" value="delete">
				<input type="hidden" name="delete[]" value="'.$this->id.'">
				<input type="submit" value="'.ucphr('DELETE').'">
				</form>
				</td>';
	}
	$output .= '
			</tr>
			</table>
			</td>
		</tr>
	</table>


	</fieldset>
	</td>
	</tr>
	</table>
	</div>';

	return $output;
	}
}

?>