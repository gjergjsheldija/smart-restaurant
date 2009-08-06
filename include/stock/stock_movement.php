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
		$this->table='stock_movements';
		$this->id=$id;
		$this -> title = ucphr('STOCK_MOVEMENTS');
		$this -> no_name = true;
		$this -> disable_new = true;
		$this->file=ROOTDIR.'/include/stock/index.php';
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
	
	function list_query_all () {
		if(isset($_REQUEST['data']['only_obj']) && $_REQUEST['data']['only_obj']) $this -> only_obj = $_REQUEST['data']['only_obj'];
		if(isset($_REQUEST['data']['only_dish']) && $_REQUEST['data']['only_dish']) $this -> only_dish = $_REQUEST['data']['only_dish'];
		
		$table = "stock_movements";
		$stock_table = "stock_objects";
		$ingred_table = "ingreds";
		$ingred_lang_table = "ingreds_".$_SESSION['language'];
		$dish_table = "dishes";
		$dish_lang_table = "dishes".$_SESSION['language'];
		//mizuko : added $table_users & modified tho the se username
		$table_users = "users";
	
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
			$msg= ucphr('CHECK_QUANTITY');
		}

		$input_data['user'] = $_SESSION['userid'];
		if ($msg) {
			echo "<script language=\"javascript\">
				window.alert(\"".$msg."\");
			</script>\n";
			return -2;
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
	
}

?>