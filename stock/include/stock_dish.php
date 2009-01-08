<?php
/**
* My Handy Restaurant
*
* http://www.myhandyrestaurant.org
*
* My Handy Restaurant is a restaurant complete management tool.
* Visit {@link http://www.myhandyrestaurant.org} for more info.
* Copyright (C) 2003-2004 Fabio De Pascale
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

class stock_dish extends object {
	var $form_properties;
	function stock_dish($id=0) {
		$this -> db = 'common';
		$this->table=$GLOBALS['table_prefix'].'dishes';
		$this->id=$id;
		$this->fields_names=array(	'id'=>ucphr('ID'),
								'name'=>ucphr('NAME'),
								'percent'=>ucphr('PERCENT_INSERTED'),
								'category'=>ucphr('CATEGORY'));
		$this -> title = ucphr('DISHES_INGREDIENT_QUANTITIES');
		$this->file=ROOTDIR.'/stock/index.php';
		$this->fields_width=array(	'name'=>'80%','percent'=>'20%');
		
		$this -> disable_mass_delete = true;
		$this -> disable_new = true;
		$this -> flag_delete = true;
		$this -> fetch_data();
	}
	
	function list_search ($search) {
		$query = '';
		
		$table = $this->table;
		$lang_table = $table."_".$_SESSION['language'];
		
		$query="SELECT
				$table.`id`,
				$table.`name` as `name`,
				RPAD('".ucphr('INGREDIENT_QUANTITIES')."',30,' ') as `table`,
				".TABLE_STOCK_DISHES." as `table_id`
				FROM `$table`
				WHERE $table.`deleted`='0'
				AND $table.`name` LIKE '%$search%'
				AND ($table.`ingreds`<>'' OR $table.`dispingreds`<>'')
				";
		
		return $query;
	}
	
	function list_query_all () {
		$table = $this->table;
		$lang_table = $table."_".$_SESSION['language'];
		$cat_table = "#prefix#categories";
		$cat_lang_table = "#prefix#categories_".$_SESSION['language'];
		
		$query="SELECT
				$table.`id`,
				$table.`name` as `name`,";
		if(CONF_SHOW_PERCENT_INSERTED_ON_LIST) $query .= "
				RPAD('',6,' ') as `percent`,";
		$query .= "
				$table.`price`,
				$cat_table.`name` as `category`
				FROM `$table`
				LEFT JOIN `$cat_table` ON $cat_table.`id`=$table.`category`
				WHERE $table.`deleted`='0'
				AND ($table.`ingreds`<>'' OR $table.`dispingreds`<>'')
				";
		if(isset($this->category) && $this->category) $query.= " AND $table.`category`=".$this->category;
		
		return $query;
	}
	
	function list_rows ($arr,$row) {
		global $tpl;
		global $display;
		
		$col=0;
		
		$display->rows[$row][$col]='<input type="checkbox" name="edit[]" value="'.$arr['id'].'">';
		$display->width[$row][$col]='1%';
		$col++;
		
		$dish = new dish;
		foreach ($arr as $field => $value) {
			if (isset($this->allow_single_update) && in_array($field,$this->allow_single_update)) {
				$link = $this->link_base.'&amp;command=update_field&amp;data[id]='.$arr['id'].'&amp;data[field]='.$field;
				if($this->limit_start) $link .= '&amp;data[limit_start]='.$this->limit_start;
				if($this->orderby) $link.='&amp;data[orderby]='.$this->orderby;
				if($this->sort) $link.='&amp;data[sort]='.$this->sort;
				
				$display->links[$row][$col]=$link;
			} elseif (method_exists($this,'form')) $link = $dish->file.'?class=dish&amp;command=edit&amp;data[id]='.$arr['id'];
			else $link='';
			
			if($field=='percent') {
				$value = $this->percent_inserted ($arr['id'])*100;
				$value = round($value,0).' %';
			}
			
			$display->rows[$row][$col]=$value;
			if($link && $field=='name') $display->links[$row][$col]=$link;
			if($link) $display->clicks[$row][$col]='redir(\''.$link.'\');';
			
			$col++;
		}
	}
	
	function number_inserted ($dish_id) {
		$dish=new dish($dish_id);
		$dish->fetch_data();
		
		$ingreds = array_merge($dish->ingredients(),$dish->dispingredients());
		$total = count ($ingreds);
		$inserted = 0;
		foreach ($ingreds as $ingred_id) {
			$ingred = new ingredient ($ingred_id);
			
			$stock = new stock_object;
			$data['obj_id'] = $stock -> find_external ($ingred_id, TYPE_INGREDIENT);
			
			$qty = new stock_ingredient_quantity;
			if($qty_id=$qty->find ($data['obj_id'],$dish_id)) {
				$inserted++;
			}
		}
		return $inserted;
	}
		
	function percent_inserted ($dish_id) {
		$dish=new dish($dish_id);
		$dish->fetch_data();
		
		$ingreds = array_merge($dish->ingredients(),$dish->dispingredients());
		$total = count ($ingreds);
		$inserted = $this->number_inserted ($dish_id);
		$percent=$inserted/$total;
		return $percent;
	}
	
	function list_head ($arr) {
		global $tpl;
		global $display;
		
		$col=0;
		$display->rows[0][$col]='<input type="checkbox" name="all_checker" onclick="check_all(\''.$this->form_name.'\',\'edit[]\')">';
		$display->width[0][$col]='1%';
		$col++;
		
		foreach ($arr as $field => $val) {
			if(isset($this->hide) && in_array($field,$this->hide)) continue;
			
			if(isset($this->fields_names[$field])) $display->rows[0][$col]=$this->fields_names[$field];
			else $display->rows[0][$col]=$field;
			
			if($field==$this->orderby && strtolower($this->sort)=='asc') {
				$next_sort='desc';
				$display->rows[0][$col].= ' (+)';
			} else {
				$next_sort='asc';
				if($field==$this->orderby) $display->rows[0][$col].= ' (-)';
			}
			
			$link = $this->link_base.'&amp;data[orderby]='.$field.'&amp;data[sort]='.$next_sort;
			if($field=='percent') $link='';
			
			$display->links[0][$col]=$link;
			$display->clicks[0][$col]='redir(\''.$link.'\');';
			
			if(isset($this->fields_width[$field])) $display->widths[0][$col]=$this->fields_width[$field];
			$col++;
		}
	}
	
	function list_buttons () {
		if($this->count_records()) {
			$tmp .= '<table width="100%"><tr>'."\n";
			$tmp .= '<td align="left">'."\n";
			$tmp .= '<input type="hidden" name="command" value="edit">'."\n";
			$tmp .= '<input type="hidden" name="class" value="'.get_class($this).'">'."\n";
			$tmp .= '<a href="#" onClick="list_form_'.get_class($this).'.submit();return false;">'.ucphr('EDIT_QUANTITIES').'</a>'."\n";
			$tmp .= '</td><td align="right">'."\n";
			$tmp .= '&nbsp;'."\n";
			$tmp .= '</tr></table>'."\n";
		}
		return $tmp;
	}
	
	function available_quantity_array () {
		// no ingredient quantity set
		if($this->number_inserted ($this->id)==0) return -1;
		
		$dish=new dish($this->id);
		$dish->fetch_data();
		
		$avail=array();
		
		$ingreds = $dish->ingredients();
		
		foreach ($ingreds as $ingred_id) {
			$ingred = new ingredient ($ingred_id);
			
			$stock = new stock_object;
			if ($stock_id=$stock->find_external($ingred_id, TYPE_INGREDIENT)) {
				$stock = new stock_object ($stock_id);
				$qty = new stock_ingredient_quantity;
				if($qty_id=$qty->find ($stock_id,$this->id)) {
					$qty = new stock_ingredient_quantity ($qty_id);
					if($qty->data['quantity']!=0) $avail[$ingred_id] = $stock->data['quantity']/$qty->data['quantity'];
				}
			}
		}
		return $avail;
	}
	
	function ingredients_cost_array () {
		$dish=new dish($this->id);
		$dish->fetch_data();
		
		$cost=array();
		
		$ingreds = $dish->ingredients();
		
		foreach ($ingreds as $ingred_id) {
			$ingred = new ingredient ($ingred_id);
			
			$stock = new stock_object;
			if ($stock_id=$stock->find_external($ingred_id, TYPE_INGREDIENT)) {
				$stock = new stock_object ($stock_id);
				$qty = new stock_ingredient_quantity;
				if($qty_id=$qty->find ($stock_id,$this->id)) {
					$qty = new stock_ingredient_quantity ($qty_id);
					$cost[$ingred_id] = $stock->data['value']*$qty->data['quantity'];
				}
			}
		}
		return $cost;
	}
	
	function ingredients_cost () {
		$cost_arr=$this->ingredients_cost_array();
		foreach($cost_arr as $ingred_id => $val) {
			$cost=$cost+$val;
		}
		return $cost;
	}
	
	function revenue () {
		$cost=$this->ingredients_cost();
		$dish = new dish($this->id);
		$revenue=$dish->data['price']-$cost;
		return $revenue;
	}
	
	function available_quantity_form () {
		$display = new display();
		$display->highlight=false;
		
		$row=0;
		
		if(!isset($this->form_properties['show_head_dish'])) $this->form_properties['show_head_dish']=false;
		if($this->form_properties['show_head_dish']) {
			$display->show_head=true;
			$col=0;
			$display->rows[$row][$col]=ucphr('STOCK');
			$display->properties[$row][$col]='colspan="2"';
			$row++;
		}
		
		$avail=$this->available_quantity_array();
		if(!is_array($avail) && $avail<0) $qty=ucphr('NO_INGREDIENT_QUANTITY_INSERTED');
		elseif(!is_array($avail) && $avail==0) $qty=ucphr('CANNOT_PREPARE_DISH');
		elseif(is_array($avail)) {
			asort($avail,SORT_NUMERIC);
			foreach($avail as $ingred_id => $qty) {
				$qty=floor($qty);
				//exit after the first
				break;
			}
		}
		$col=0;
		if(is_numeric($qty) && $qty<CONF_STOCK_QUANTITY_ALARM) $display->properties[$row][$col]='style="color: #FF0000; font-weight: bolder"';
		$display->rows[$row][$col]=ucphr('POSSIBLE_QUANTITY');
		$col++;
		if(is_numeric($qty) && $qty<CONF_STOCK_QUANTITY_ALARM) $display->properties[$row][$col]='style="color: #FF0000; font-weight: bolder"';
		$display->rows[$row][$col]=$qty;
		$col++;
		$row++;
		
		if(is_array($avail)) {
			foreach($avail as $ingred_id => $qty) {
				$ingred = new ingredient ($ingred_id);
				$stock = new stock_object;
				if ($stock_id=$stock->find_external($ingred_id, TYPE_INGREDIENT)) {
					$stock = new stock_object ($stock_id);
					
					$col=0;
					$display->rows[$row][$col]=ucphr('LIMITING_INGREDIENT');
					$col++;
					
					
					$link=$ingred->file.'?class=ingredient&amp;command=edit&amp;data[id]='.$ingred->id;
					if($link) $display->links[$row][$col]=$link;
					if($link) $display->clicks[$row][$col]='redir(\''.$link.'\');';
					$display->rows[$row][$col]=$ingred->name($_SESSION['language']).'<br/>('.$stock -> get_user_quantity ().')';
					$col++;
					$row++;
					//exit after the first
					break;
				}
			}
		}
		
		$cost = 0;
		$cost=$this->ingredients_cost();
		$cost = round($cost,2);
		$cost_label = $cost.' '.country_conf_currency (true);
		$col=0;
		$display->rows[$row][$col]=ucphr('DISH_COST');
		$col++;
		$display->rows[$row][$col]=$cost_label;
		$col++;
		$row++;
		
		$revenue=round($this->revenue(),2);

		$col=0;
		if($revenue<0) $display->properties[$row][$col]='style="color: #FF0000; font-weight: bolder"';
		$display->rows[$row][$col]=ucphr('DISH_REVENUE');
		$col++;
		if($revenue<0) $display->properties[$row][$col]='style="color: #FF0000; font-weight: bolder"';
		$display->rows[$row][$col]=$revenue.' '.country_conf_currency (true);
		$col++;
		$row++;
		
		
		return $display->list_table();
	}
	
	function insert_ingred_quantities ($input_data) {
		foreach ($input_data as $dish_id => $ingreds) {
			foreach($ingreds as $ingred_id => $quantity) {
				unset($data);
				$data['dish_id'] = $dish_id;
				$data['quantity'] = convert_units ($quantity);
				if (empty($data['quantity'])) continue;

				$stock = new stock_object;
				$data['obj_id'] = $stock -> find_external ($ingred_id, TYPE_INGREDIENT);
				$sample = new stock_ingredient_sample;
				$sample->silent=true;
				if($err = $sample -> insert($data)) return $err;

				$qty = new stock_ingredient_quantity;
				if($qty_id=$qty->find ($data['obj_id'],$data['dish_id'])) {
					$qty = new stock_ingredient_quantity ($qty_id);
					//if ($err=$qty -> recalc ()) return $err;
					$data['id']=$qty_id;
					if ($err=$qty -> update ($data)) return $err;
				} elseif ($err=$qty -> insert ($data)) return $err;
			}
		}
		
		return 0;
	}
	
	function edit_many ($arr) {
		if(!is_array($arr)) return '';
		
		$output .= '
		<form name="edit_form_'.get_class($this).'" action="'.$this->file.'" method="post">
		<input type="hidden" name="command" value="insert_ingred_quantities">
		<input type="hidden" name="class" value="'.get_class($this).'">';
		foreach ($arr as $id) {
			$obj = new stock_dish ($id);
			$obj->form_properties =$this->form_properties;

			$obj->from_code = true;
			$output .= $obj->form ();
		}
		$output .= '
		<div align="center"><input type="submit" value="'.ucphr('UPDATE_INGREDIENT_QUANTITIES').'"></div>
		</form>'."\n";
		
		return $output;
	}
	
	
	function form () {
		if(!$this->from_code && isset($_REQUEST['edit']) && is_array($_REQUEST['edit'])) return $this->edit_many($_REQUEST['edit']);
		elseif (!$this->from_code && !isset($_REQUEST['edit'])) return $this->edit_many(array($this->id));
		
		$display = new display();
		$display->highlight=false;
		
		$dish=new dish($this->id);
		$dish->fetch_data();
		
		$ingreds = array_merge($dish->ingredients(),$dish->dispingredients());
		
		if(!isset($this->form_properties['show_name'])) $this->form_properties['show_name']=true;
		if($this->form_properties['show_name']) $output .= '<div align="left" style="font-weight: bold">'.$dish->name($_SESSION['language']).'</div>';
		
		$row=0;
		
		if(!isset($this->form_properties['show_head_dish'])) $this->form_properties['show_head_dish']=false;
		if($this->form_properties['show_head_dish']) {
			$display->show_head=true;
			$col=0;
			$display->rows[$row][$col]=ucphr('INGREDIENT_QUANTITIES');
			$display->properties[$row][$col]='colspan="2"';
			$row++;
		}
		
		foreach ($ingreds as $ingred_id) {
			$ingred = new ingredient ($ingred_id);
			
			$quantity = 0;
			$user_unit = get_user_unit (UNIT_TYPE_MASS);
			
			$default = 0;
			
			$stock = new stock_object;
			if ($stock_id=$stock->find_external($ingred_id, TYPE_INGREDIENT)) {
				$stock = new stock_object ($stock_id);
				$unit_type = $stock->data['unit_type'];
					
				$user_unit = get_user_unit ( $unit_type);
				$system_unit = get_default_unit ( $unit_type);
				
				$qty = new stock_ingredient_quantity;
				if($qty_id=$qty->find ($stock_id,$this->id)) {
					$qty = new stock_ingredient_quantity ($qty_id);
					$quantity = $qty->data['quantity'];
					$quantity = convert_units ($quantity.' '.$system_unit, $user_unit);
				}
				$prev_quantity=$quantity.' '.$user_unit;
				
				if(empty($user_unit)) $default .= ' '.get_user_unit (UNIT_TYPE_MASS);
				else $default .= ' '.$user_unit;
				
				if(!$quantity) $prev_quantity=$default;
				
				$col=0;
				$link=$ingred->file.'?class=ingredient&amp;command=edit&amp;data[id]='.$ingred->id;
				if($link) $display->links[$row][$col]=$link;
				if($link) $display->clicks[$row][$col]='redir(\''.$link.'\');';
				$display->rows[$row][$col]=ucfirst($ingred->name($_SESSION['language']));
				$display->widths[$row][$col]="100%";
				$col++;
				$display->rows[$row][$col]='<input type="text" size=7 name="data['.$this->id.']['.$ingred->id.']" value="'.$prev_quantity.'">';
				$col++;
				$row++;
			}
		}
		$output.=$display->list_table();
		return $output;
	}
}