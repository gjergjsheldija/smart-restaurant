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

class ingredient extends object {
	var $temp_lang;

	function ingredient($id=0) {
		$this->table='ingreds';
		$this->id=$id;
		$this->fields_names=array(	'id'=>ucphr('ID'),
								'name'=>ucphr('NAME'),
								'category'=>ucphr('CATEGORY'),
								'price'=>ucphr('PRICE'),
								'quantity'=>ucphr('QUANTITY'),
								'unit_type'=>'',
								'value'=>ucphr('UNITARY_VALUE').' ['.country_conf_currency (true).']',
								'override_autocalc',
								'visible'=>ucphr('VISIBLE'),
								'sell_price'=>ucphr('SELL_PRICE'));
		$this->fields_boolean=array('override_autocalc','visible');
		$this->fields_width=array(	'name'=>'100%');
		$this->allow_single_update = array ('override_autocalc','visible');
		
		$this -> title = ucphr('INGREDIENTS');
		$this->file=ROOTDIR.'/admin/admin.php';
		$this->flag_delete = true;
		$this -> show_category_list = true;
		$this -> fetch_data();
	}
	
	function list_search ($search) {
		$query = '';
		
		$table = $this->table;
		$lang_table = $table."_".$_SESSION['language'];
	
		$query="SELECT
				$table.`id`,
				$lang_table.`table_name` as `name`,
				RPAD('".ucphr('INGREDIENTS')."',30,' ') as `table`,
				".TABLE_INGREDIENTS." as `table_id`
				FROM `$table`
				 JOIN `$lang_table` ON $lang_table.`table_id`=$table.`id`
				WHERE $table.`deleted`='0'
				AND ($lang_table.`table_name` LIKE '%$search%' OR $table.`name` LIKE '%$search%')
				";

		return $query;
	}
	
	/**
	 * query that lists all products in the  Sync Ingredients
	 * @author mizuko
	 */
	function list_query_all () {
		$table = $this->table;
		$lang_table = $table."_".$_SESSION['language'];
		$cat_table = "categories_".$_SESSION['language'];
		$stock_table = "stock_objects";
		
		$query="SELECT
				$table.`id`,
				IF($lang_table.`table_name`='' OR $lang_table.`table_name` IS NULL,$table.`name`,$lang_table.`table_name`) as `name`,
				$cat_table.`table_name` as `category`,
				$table.`price`,
				IF($table.`visible`='0','".ucphr('NO')."','".ucphr('YES')."') as `visible`,
				$stock_table.`unit_type`,
				$stock_table.`quantity`,
				$table.`sell_price`,
				ROUND($stock_table.`value`,2) as `value`
				FROM `$table`
				LEFT JOIN `$lang_table` ON $lang_table.`table_id`=$table.`id`
				LEFT JOIN `$cat_table` ON $cat_table.`table_id`=$table.`category`
				LEFT JOIN `$stock_table` ON $stock_table.`ref_id`=$table.`id` AND $stock_table.`deleted`='0' AND $stock_table.`ref_type`='".TYPE_INGREDIENT."'
				WHERE $table.`deleted`='0'
				";
		if(isset($this->category) && $this->category) $query.= " AND $table.`category`=".$this->category;

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
			if (isset($this->allow_single_update) && in_array($field,$this->allow_single_update)) {
				$link = $this->link_base.'&amp;command=update_field&amp;data[id]='.$arr['id'].'&amp;data[field]='.$field;
				if($this->limit_start) $link .= '&amp;data[limit_start]='.$this->limit_start;
				if($this->orderby) $link.='&amp;data[orderby]='.$this->orderby;
				if($this->sort) $link.='&amp;data[sort]='.$this->sort;
				
				$display->links[$row][$col]=$link;
			} elseif (method_exists($this,'form')) $link = $this->file.'?class='.get_class($this).'&amp;command=edit&amp;data[id]='.$arr['id'];
			else $link='';
			
			if($field=='name' && CONF_SHOW_SUMMARY_ON_LIST) {
				$ingred = new ingredient($arr['id']);

				$value .= '<span class="admin_ingreds_list">';
				
				$dishes=$ingred->find_connected_dishes();
				if (!empty($dishes['included']) && is_array($dishes['included'])) {
					$value .= '<br/>'.ucphr('INCLUDED').': ';
					foreach ($dishes['included'] as $key2 => $value2) {
						$value.=ucfirst($value2).", ";
					}
					$value=substr($value,0,-2);
					$value .= '';
				}
				if (!empty($dishes['available']) && is_array($dishes['available'])) {
					$value .= '<br/>'.ucphr('AVAILABLE').': ';
					foreach ($dishes['available'] as $key2 => $value2) {
						$value.=ucfirst($value2).", ";
					}
					$value=substr($value,0,-2);
					$value .= '';
				}
				$value .= '</span>';
			} elseif ($field=='unit_type') {
				$value = get_user_unit ($arr['unit_type']);
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
	
	function find_connected_dishes ($show_deleted=false,$link=false) {
		$output = array();
		
		$query="SELECT dishes.id, dishes.table_id, dishes.table_name FROM `dishes`";
		$query .= " JOIN `dishes` WHERE dishes.table_id=dishes.id";
		$query .= " AND (`ingreds` LIKE '% ".$this->id." %'";
		$query .= " OR `ingreds` LIKE '".$this->id." %'";
		$query .= " OR `ingreds` LIKE '".$this->id."'";
		$query .= " OR `ingreds` LIKE '% ".$this->id."')";
		if(!$show_deleted) $query .= " AND dishes.deleted='0'";
		$query .= " ORDER BY dishes.table_name ASC";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
		
		while($arr=mysql_fetch_array($res)) {
			$dish = new dish($arr['id']);
					$tmp = '';
					if($link) $tmp .= '<a href="'.$this->file.'?class=dish&command=edit&data[id]='.$dish->id.'">';
					$tmp .= $dish->name($_SESSION['language']); 
					if($link) $tmp .= '</a>';
					$output['included'][$dish->id] = $tmp;
		}
		
		$query="SELECT dishes.id, dishes.table_id, dishes.table_name FROM `dishes`";
		$query .= " JOIN `dishes` WHERE table_id=dishes.id";
		$query .= " AND (`dispingreds` LIKE '% ".$this->id." %'";
		$query .= " OR `dispingreds` LIKE '".$this->id." %'";
		$query .= " OR `dispingreds` LIKE '".$this->id."'";
		$query .= " OR `dispingreds` LIKE '% ".$this->id."')";
		if(!$show_deleted) $query .= " AND dishes.deleted='0'";
		$query .= " ORDER BY dishes.table_name ASC";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
		
		while($arr=mysql_fetch_array($res)) {
			$dish = new dish($arr['id']);

			$ingreds=$dish->dispingredients($arr['dispingreds']);
			if (!empty($ingreds) && is_array($ingreds)) {
				if(in_array($this->id,$ingreds)) {
					$tmp = '';
					if($link) $tmp .= '<a href="'.$this->file.'?class=dish&command=edit&data[id]='.$dish->id.'">';
					$tmp .= $dish->name($_SESSION['language']); 
					if($link) $tmp .= '</a>';
					$output['available'][$dish->id] = $tmp;
				}
			}
		}

		return $output;
	}
}

?>