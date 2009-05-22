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

class stock_object extends object {
	var $name;
	var $stock_edit_mode;
	var $tmp;
	
	function stock_object ($id=0) {
		$this -> db = 'common';
		$this->table='stock_objects';
		$this->id=$id;
		$this -> title = ucphr('STOCK');
		$this->referring_name = true;
		$this->flag_delete = true;
		$this->fields_names=array(	'id'=>ucphr('ID'),
								'name'=>ucphr('NAME'),
								'ref_type'=>ucphr('TYPE'),
								'ref_id'=>ucphr('INGREDIENT'),
								'unit_type'=>'',
								'quantity'=>ucphr('QUANTITY'),
								'value'=>ucphr('VALUE').' ['.country_conf_currency (true).']');
		$this->fields_width=array(	'name'=>'75%');
		$this->hide=array(	'ref_id');
		$this->file=ROOTDIR.'/stock/index.php';
		$this -> disable_new = true;
		$this -> fetch_data();
	}

	function list_query_all () {
		$table = "stock_objects";
		$ingred_table = "ingreds";
		$ingred_lang_table = "ingreds_".$_SESSION['language'];

		$query="SELECT
				$table.`id`,
				$ingred_table.`name` as `name`,
				$table.`ref_id`,
				$table.`unit_type`,
				$table.`quantity`,
				$table.`value`
				 FROM `$table`
				 JOIN `$ingred_table`
				WHERE $table.`deleted`='0'
				AND $ingred_table.`id`=$table.`ref_id`";
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
		foreach ($arr as $field => $value) {
			if(isset($this->hide) && in_array($field,$this->hide)) continue;
			
			if ($field=='unit_type') {
				$value = get_user_unit ($value);
			} elseif ($field=='quantity') {
				$unit = get_user_unit ($arr['unit_type']);
				$default_unit = get_default_unit ($arr['unit_type']);
				$value = convert_units ($value.' '.$default_unit, $unit);
			}
			
			if (isset($this->allow_single_update) && in_array($field,$this->allow_single_update)) {
				$link = $this->link_base.'&amp;command=update_field&amp;data[id]='.$arr['id'].'&amp;data[field]='.$field;
				if($this->limit_start) $link .= '&amp;data[limit_start]='.$this->limit_start;
				if($this->orderby) $link.='&amp;data[orderby]='.$this->orderby;
				if($this->sort) $link.='&amp;data[sort]='.$this->sort;
				
				$display->links[$row][$col]=$link;
			} else $link = ROOTDIR.'/admin/admin.php?class=ingredient&amp;command=edit&amp;data[id]='.$arr['ref_id'];
			
			$display->rows[$row][$col]=$value;
			if($link && $field=='name') $display->links[$row][$col]=$link;
			if($link) $display->clicks[$row][$col]='redir(\''.$link.'\');';
			$col++;
		}
	}
	
	function set_unit_type ($type) {
		$this -> fetch_data();
		$out = $this->data;
		$out['unit_type']=$type;
		return($this->update($out));
	}
	
	function check_values($input_data){
		$msg="";
		if($this->data['ref_id']) {
			$input_data['ref_id']=$this->data['ref_id'];
			$input_data['name']=$this->data['name'];
		}
		if($this->data['ref_type']) $input_data['ref_type']=$this->data['ref_type'];
		
		if(!isset($input_data['value'])) $input_data['value']=$this->data['value'];
		if(!isset($input_data['unit_type'])) $input_data['unit_type']=$this->data['unit_type'];
		if(!isset($input_data['deleted'])) $input_data['deleted']=$this->data['deleted'];
		
		if(!$input_data['ref_id'] && $input_data['name']=="") {
			$msg=ucfirst(phr('CHECK_NAME'));
		}

		$input_data['quantity'] = eq_to_number ($input_data['quantity']);
		if($input_data['quantity']==="") {
			$msg=ucphr('CHECK_QUANTITY');
		}
		
		if($msg){
			echo "<script language=\"javascript\">
				window.alert(\"".$msg."\");
				window.history.go(-1);
			</script>\n";
			return -2;
		}

		return $input_data;
	}

	function get_id () {
		return $this->id;
	}
	
	function get_ref_type () {
		return $this->data['ref_type'];   
	}
	
	function get_ref_id () {
		return $this->data['ref_id'];   
	}
	
	function get_value () {
		return $this->data['value'];   
	}
	
	function get_quantity () {
		return $this->data['quantity'];   
	}
	
	function find_external ($obj_id, $obj_type) {
		$query = "SELECT * FROM stock_objects WHERE `ref_type`='".$obj_type."' AND `ref_id`='".$obj_id."' AND `deleted`='0'";
		$res = common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
	
		while($arr = mysql_fetch_array ($res)) {
			return $arr['id'];
		}
		return 0;
	}

	function sync_external ($obj_type) {
		if($obj_type==TYPE_DISH) $query = "SELECT * FROM dishes WHERE `deleted`='0'";
		elseif($obj_type==TYPE_INGREDIENT) $query = "SELECT * FROM ingreds WHERE `deleted`='0'";
		$res = common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
		
		while($arr = mysql_fetch_array ($res)) {
			$err=$this->create_from_external($arr['id'],$obj_type);
			if ($err && $err != ERR_OBJECT_ALREADY_EXISTS) return $err;
		}
		return 0;
	}
	

	function create_from_external ($obj_id, $obj_type) {
		if($obj_type==TYPE_DISH) {
			$obj = new dish($obj_id);
			foreach($obj -> ingredients() as $ingredid){
				$err=$this->create_from_external($ingredid, TYPE_INGREDIENT);
				if($err && $err!=ERR_OBJECT_ALREADY_EXISTS) return $err;
			}
			foreach($obj -> dispingredients() as $ingredid){
				$err=$this->create_from_external($ingredid, TYPE_INGREDIENT);
				if($err && $err!=ERR_OBJECT_ALREADY_EXISTS) return $err;
			}
		}
		elseif($obj_type==TYPE_INGREDIENT)  $obj = new ingredient($obj_id);

		else return ERR_NO_TYPE_SPECIFIED;

		if($this->find_external($obj_id, $obj_type)) return ERR_OBJECT_ALREADY_EXISTS;

		$input_data['name']=$obj->name();
		$input_data['ref_type']=$obj_type;
		$input_data['ref_id']=$obj_id;

		$err=$this->insert ($input_data);

		return $err;
	}
	
	function remove_from_waiter ($order_id, $new_quantity) {
		$order_id = (int) $order_id;
		$order = new order ($order_id);
		$dish_id = $order->data['dishid'];
		
		if($dish_id==MOD_ID) {
			$arr_ingreds[] = $order->data['ingredid'];
			$diffquantity = $order->data['quantity'] - $new_quantity;
			
			$order_id = (int) $order->data['associated_id'];
			
			$order = new order ($order_id);
			$dish_id = $order->data['dishid'];
		} else {
			$order->ingredients_arrays ();
			$arr_ingreds = $order->ingredients['contained'];
			$diffquantity = $order->data['quantity'] - $new_quantity;
		}
		
		if(!$diffquantity) return 0;
		
		
		// movements for all the contained ingredients
		foreach ($arr_ingreds as $ingred_id) {
			$stock_id = $this->find_external($ingred_id, TYPE_INGREDIENT);
			
			// object not found in stock
			if(!$stock_id) continue;
			
			$mov = new stock_movement ();
			$mov_data['obj_id'] = $stock_id;
			$mov_data['dish_id'] = $dish_id;
			$mov_data['dish_quantity'] = $diffquantity;
			
			$stock = new stock_object ($stock_id);
			$ingred_quantity = $stock->get_ingredient_quantity ($dish_id);
			$mov_data['quantity'] = $mov_data['dish_quantity'] * $ingred_quantity;
			
			$mov_data['value'] = $stock->data['value'] * $mov_data['quantity'];
			$mov_data['unit_type'] = $stock->data['unit_type'] ;
			
			$mov->silent=true;
			if($err = $mov -> insert ($mov_data)) return $err;
		}
		return 0;
	}
	
	function get_user_quantity () {
		$unit_type = $this->data['unit_type'];
				
		$user_unit = get_user_unit ( $unit_type);
		$system_unit = get_default_unit ( $unit_type);
			
		$quantity = $this->data['quantity'];
		$quantity = convert_units ($quantity.' '.$system_unit, $user_unit);

		$res=$quantity.' '.$user_unit;
		return $res;
	}
	
	function get_ingredient_quantity ($dish_id) {
		$obj_id = $this->id;
		
		if(!$obj_id || !$dish_id) return ERR_NO_STOCK_OBJECT_CHOSEN;
		
		$qty = new stock_ingredient_quantity;
		if($qty_id=$qty->find ($obj_id,$dish_id)) {
			$qty = new stock_ingredient_quantity ($qty_id);
			return $qty->data['quantity'];
		}
		
		return 0;
	}
	
	function pre_update ($input_data) {
		switch($input_data['from']) {
			case 'movement':
				break;
			case 'waiter':
				// updates the data
				$this->fetch_data();
				
				// this value is the one given by the automatic calculator, so it is a total value and should be unset and NOT saved in stock_objects, but oonly passed to stock_movements
				$mov_data['value']=$input_data['value'];
				unset($input_data['value']);
				
				$mov_data['dish_id']=$input_data['dish_id'];
				unset($input_data['dish_id']);
				
				$mov_data['dish_quantity']=$input_data['dish_quantity'];
				unset($input_data['dish_quantity']);
				
				$diff = $input_data['quantity'] - $this->data['quantity'];
				if($diff) {
					$mov = new stock_movement ();
					$mov_data['from_object_interface'] = true;
					$mov_data['obj_id'] = $this->id;
					$mov_data['quantity'] = $diff;
					$mov -> silent = true;
					$mov -> insert ($mov_data);
				}
				break;
			default:
				break;
		}
		
		if(isset($input_data['from'])) unset($input_data['from']);

		return $input_data;
	}
	
	function post_delete ($input_data) {
		$mov = new stock_movement;
		$err = $mov->delete_all_from_obj ($this->id);
		return $err;
	}
	
	function insert_sample ($quantity) {
		$query="INSERT INTO `stock_samples` (`obj_id`,`quantity`) VALUES ('".$this->id."','".$quantity."')";

		$res = common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
		
		return 0;
	}
	
	function post_edit_page ($class) {
		$this -> admin_list_page($class);
	}
	
	function pre_delete ($input_data) {
		$this->fetch_data();
		$this->tmp=$this->data;
		return $input_data;
	}
	
	function post_insert_page ($class) {
		global  $tpl;
		$tpl -> set_admin_template_file ('menu');
		
		$this->fetch_data();
		if($this->data['ref_type'] == TYPE_DISH) $type_class = 'dish';
		elseif($this->data['ref_type'] == TYPE_INGREDIENT) $type_class = 'ingredient';
		
		$obj = new $type_class ($this->data['ref_id']);
		$tmp = $obj -> form();
		$tpl -> assign("content", $tmp);
	}
	
	function post_delete_page ($class) {
		global  $tpl;
		$tpl -> set_admin_template_file ('menu');
		
		if($this->tmp['ref_type'] == TYPE_DISH) $type_class = 'dish';
		elseif($this->tmp['ref_type'] == TYPE_INGREDIENT) $type_class = 'ingredient';
		
		$obj = new $type_class ($this->tmp['ref_id']);
		$tmp = $obj -> form();
		$tpl -> assign("content", $tmp);
		
	}
	
}

?>