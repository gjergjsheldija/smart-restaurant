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
		$this->table='dishes';
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
		$cat_table = "categories";
		$cat_lang_table = "categories_".$_SESSION['language'];
		
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
	
}