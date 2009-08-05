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
class search extends object {
	function search ($id=0) {
		$this -> db = 'common';
		$this -> title = ucphr('SEARCH_RESULTS');
		$this->file=ROOTDIR.'/admin/admin.php';
		$this->fields_width=array(	'name'=>'85%');
		$this->fields_names=array(	'id'=>ucphr('ID'),
								'name'=>ucphr('NAME'),
								'table'=>ucphr('TYPE'));
		$this->hide=array('table_id');
		$this -> disable_new = true;
		$this->disable_mass_delete=true;
	}
	

	function list_buttons () {
		$obj = new stock_dish;
		$tmp .= '<table width="100%"><tr>'."\n";
		$tmp .= '<td align="left">'."\n";
		$tmp .= '<input type="hidden" name="command" value="edit">'."\n";
		$tmp .= '<input type="hidden" name="class" value="'.get_class($obj).'">'."\n";
		$tmp .= '<a href="#" onClick="list_form_'.get_class($obj).'.submit();return false;">'.ucphr('EDIT_QUANTITIES').'</a>'."\n";
		$tmp .= '</td><td align="right">'."\n";
		$tmp .= '&nbsp;'."\n";
		$tmp .= '</tr></table>'."\n";
		return $tmp;
	}
	
	function list_query_all () {
		global $tpl;
		$tpl -> assign ('title',$this -> title);



		if(access_allowed(USER_BIT_MENU)) {
			$obj = new dish ();
			if(method_exists($obj,'list_search')) {
				$query .= $obj->list_search ($this->search);
				$query .= ' UNION ALL ';
			}
		}
		if(access_allowed(USER_BIT_MENU)) {
			$obj = new ingredient ();
			if(method_exists($obj,'list_search')) {
				$query .= $obj->list_search ($this->search);
				$query .= ' UNION ALL ';
			}
		}
		if(access_allowed(USER_BIT_MENU)) {
			$obj = new category ();
			if(method_exists($obj,'list_search')) {
				$query .= $obj->list_search ($this->search);
				$query .= ' UNION ALL ';
			}
		}
		if(access_allowed(USER_BIT_MENU)) {
			$obj = new table ();
			if(method_exists($obj,'list_search')) {
				$query .= $obj->list_search ($this->search);
				$query .= ' UNION ALL ';
			}
		}
		if(access_allowed(USER_BIT_USERS)) {
			$obj = new user ();
			if(method_exists($obj,'list_search')) {
				$query .= $obj->list_search ($this->search);
				$query .= ' UNION ALL ';
			}
		}
		if(access_allowed(USER_BIT_MENU)) {
			$obj = new vat_rate ();
			if(method_exists($obj,'list_search')){
				$query .= $obj->list_search ($this->search);
				$query .= ' UNION ALL ';
			}
		}
		if(access_allowed(USER_BIT_CONFIG)) {
			$obj = new printer ();
			if(method_exists($obj,'list_search')) {
				$query .= $obj->list_search ($this->search);
				$query .= ' UNION ALL ';
			}
		}
		if(!empty($query)) $query=substr($query,0,-11);			// strips out last UNION ALL

		return $query;
	}
	
	function list_head ($arr) {
		global $tpl;
		global $display;
		
		$col=0;
		if(!$this->disable_mass_delete) {
			$display->rows[0][$col]='<input type="checkbox" name="all_checker" onclick="check_all(\''.$this->form_name.'\',\'delete[]\')">';
			$display->width[0][$col]='1%';
			$col++;
		} elseif ($arr['table_id']==TABLE_DISHES) {
			$display->rows[0][$col]='<input type="checkbox" name="all_checker" onclick="check_all(\''.$this->form_name.'\',\'edit[]\')">';
			$display->width[0][$col]='1%';
			$col++;
		} else {
			$display->rows[0][$col]='&nbsp;';
			$display->width[0][$col]='1%';
			$col++;
		}
		
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
			
			$display->links[0][$col]=$link;
			$display->clicks[0][$col]='redir(\''.$link.'\');';
			
			if(isset($this->fields_width[$field])) $display->widths[0][$col]=$this->fields_width[$field];
			$col++;
		}
	}
	
	function list_rows ($arr,$row) {
		global $tpl;
		global $display;
		
		$col=0;
		if(!$this->disable_mass_delete) {
			$display->rows[$row][$col]='<input type="checkbox" name="delete[]" value="'.$arr['id'].'">';
			$display->width[$row][$col]='1%';
			$col++;
		} elseif ($arr['table_id']==TABLE_DISHES) {
			$dish = new dish ($arr['id']);
			if(count($dish->ingredients()) || count($dish->dispingredients())) {
				$display->rows[$row][$col]='<input type="checkbox" name="edit[]" value="'.$arr['id'].'">';
			} else {
				$display->rows[$row][$col]='&nbsp;';
			}
			$display->width[$row][$col]='1%';
			$col++;
		} else {
			$display->rows[$row][$col]='&nbsp;';
			$display->width[$row][$col]='1%';
			$col++;
		}
		
		foreach ($arr as $field => $value) {
			if(isset($this->hide) && in_array($field,$this->hide)) continue;
			
			switch($arr['table_id']) {
				case TABLE_INGREDIENTS: $obj=new ingredient; break;
				case TABLE_DISHES: $obj=new dish; break;
				case TABLE_CATEGORIES: $obj=new category; break;
				case TABLE_TABLES: $obj=new table; break;
				case TABLE_USERS: $obj=new user; break;
				case TABLE_VAT_RATES: $obj=new vat_rate; break;
				case TABLE_PRINTERS: $obj=new printer; break;
				case TABLE_STOCK_OBJECTS: $obj=new stock_object; break;
				case TABLE_STOCK_DISHES: $obj=new dish; break;
			}
			
			
			if(method_exists($obj,'form')) $link = $obj->file.'?class='.get_class($obj).'&amp;command=edit&amp;data[id]='.$arr['id'];
			else $link='';
			
			$display->rows[$row][$col]=$value;
			if($link && $field=='name') $display->links[$row][$col]=$link;
			if($link) $display->clicks[$row][$col]='redir(\''.$link.'\');';
			$col++;
		}
	}
		
	
}

?>