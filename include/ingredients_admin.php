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

class ingredient extends object {
	var $temp_lang;

	function ingredient($id=0) {
		$this -> db = 'common';
		$this->table=$GLOBALS['table_prefix'].'ingreds';
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
		$cat_table = "#prefix#categories_".$_SESSION['language'];
		$stock_table = "#prefix#stock_objects";
		
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
		
		$query="SELECT #prefix#dishes.id, #prefix#dishes#lang#.table_id, #prefix#dishes#lang#.table_name FROM `#prefix#dishes`";
		$query .= " JOIN `#prefix#dishes#lang#` WHERE #prefix#dishes#lang#.table_id=#prefix#dishes.id";
		$query .= " AND (`ingreds` LIKE '% ".$this->id." %'";
		$query .= " OR `ingreds` LIKE '".$this->id." %'";
		$query .= " OR `ingreds` LIKE '".$this->id."'";
		$query .= " OR `ingreds` LIKE '% ".$this->id."')";
		if(!$show_deleted) $query .= " AND #prefix#dishes.deleted='0'";
		$query .= " ORDER BY #prefix#dishes#lang#.table_name ASC";
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
		
		$query="SELECT #prefix#dishes.id, #prefix#dishes#lang#.table_id, #prefix#dishes#lang#.table_name FROM `#prefix#dishes`";
		$query .= " JOIN `#prefix#dishes#lang#` WHERE table_id=#prefix#dishes.id";
		$query .= " AND (`dispingreds` LIKE '% ".$this->id." %'";
		$query .= " OR `dispingreds` LIKE '".$this->id." %'";
		$query .= " OR `dispingreds` LIKE '".$this->id."'";
		$query .= " OR `dispingreds` LIKE '% ".$this->id."')";
		if(!$show_deleted) $query .= " AND #prefix#dishes.deleted='0'";
		$query .= " ORDER BY #prefix#dishes#lang#.table_name ASC";
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

	function pre_insert($input_data) {
		for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
			if(stristr($key,'ingreds_')) {
				$this->temp_lang[$key]=$value;
				unset ($input_data[$key]);
			}
		}

		return $input_data;
	}

	function post_insert($input_data) {
		if(is_array($this->temp_lang)) {
			for (reset ($this->temp_lang); list ($key, $value) = each ($this->temp_lang); ) {
				$input_data[$key]=$this->temp_lang[$key];
			}
		}

		$input_data['id']=$this->id;
		
		if($err=$this->translations_set($input_data)) return $err;
		
		return $input_data;
	}
	
	function post_edit_page ($class) {
		if(class_exists('stock_object')) {
			$stock = new stock_object;
			if ($stock_id=$stock->find_external($this->id, TYPE_INGREDIENT)) {
				$mov = new stock_movement();
				$mov -> only_obj = $stock_id;
				$mov -> admin_list_page('stock_movement');
			}
		}
		return 0;
	}
	
	function pre_delete($input_data) {
		if(!$this->id) return 1;
		if(!$this->exists()) return 2;

		if($lang_del=$this->translations_delete($this->id)) return $lang_del;

		$connected = $this -> find_connected_dishes (true);
		
		if(is_array($connected['included'])) {
		foreach ($connected['included'] as $key => $value) {
			$dish = new dish ($key);
			if($err=$dish -> ingredient_remove($this->id)) return $err;
		}
		}
		if(is_array($connected['available'])) {
		foreach ($connected['available'] as $key => $value) {
			$dish = new dish ($key);
			if($err=$dish -> ingredient_remove($this->id)) return $err;
		}
		}
		
		$stock = new stock_object;
		if ($stock_id=$stock->find_external($this->id, TYPE_INGREDIENT)) {
			$stock = new stock_object($stock_id);
			$stock->silent=true;
			if($err=$stock->delete()) return $err;
		}
		
		return $input_data;
	}
	
	function pre_update($input_data) {
		if(!$this->id) return 1;

		if($err=$this->translations_set($input_data)) return $err;

		for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
			if(stristr($key,'ingreds_')) {
				unset ($input_data[$key]);
			}
		}
		
		return $input_data;
	}
	
	function check_values($input_data){
		$msg="";
		
		$name_found=false;
		for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
			if(stristr($key,'ingreds_') && trim($value)!='') {
				$name_found=$key;
			}
		}
		if($input_data['name']=="" && !$name_found) {
			$msg=ucfirst(phr('CHECK_NAME'));
		} elseif ($input_data['name']=="") {
			$input_data['name']=$input_data[$name_found];
		}
		
		$input_data['price'] = eq_to_number ($input_data['price']);
		if($input_data['price']==="") {
			$msg=ucfirst(phr('CHECK_PRICE'));
		}
		
		$input_data['sell_price'] = eq_to_number ($input_data['sell_price']);
		if($input_data['sell_price']==="") {
			$msg=ucfirst(phr('CHECK_PRICE'));
		}
	
		if($msg){
			echo "<script language=\"javascript\">
				window.alert(\"".$msg."\");
				history.go(-1);
			</script>\n";
			return -2;
		}

		if(!$input_data['override_autocalc'])
			$input_data['override_autocalc']=0;
		if(!$input_data['visible'])
			$input_data['visible']=0;

		$input_data['price']=str_replace (",", ".", $input_data['price']);
		$input_data['price']=round ($input_data['price'],2);		
		$input_data['sell_price']=str_replace (",", ".", $input_data['sell_price']);
		$input_data['sell_price']=round ($input_data['sell_price'],2);
		return $input_data;
	}

	function form($input_data=''){
		if($_REQUEST['data']['show_names']) $input_data['show_names']=true;
		
		if($this->id) {
			$editing=1;
			$query="SELECT * FROM `".$this->table."` WHERE `id`='".$this->id."'";
			$res=common_query($query,__FILE__,__LINE__);
			if(!$res) return mysql_errno();
			
			$arr=mysql_fetch_array($res);
		} else {
			$editing=0;
			$arr['id']=next_free_id($_SESSION['common_db'],$this->table);
			$arr['visible']=1;
		}
	$output .= '
	<div align="center">
	<a href="'.$this->file.'?class='.get_class($this).'">'.ucphr('BACK_TO_LIST').'.</a>';
	
	if($editing) {
		$output .= '
	<table>
	<tr valign="top">
	<td>';
	}
	$output .= '
	<table>
	<tr>
	<td>
	<fieldset>
	<legend>'.ucphr('INGREDIENT').'</legend>

	<form action="?" name="edit_form_'.get_class($this).'" method="post">
	<input type="hidden" name="class" value="'.get_class($this).'">
	<input type="hidden" name="data[id]" value="'.$arr['id'] .'">';
	if($editing){
		$output .= '
	<input type="hidden" name="command" value="update">';
	} else {
		$output .= '
	<input type="hidden" name="command" value="insert">';
	}
	$output .= '
	<table>';
	if($editing){
		$output .= '
	<tr>
		<td colspan=2>';
		$dishes=$this->find_connected_dishes(false,true);
		if (!empty($dishes['included']) && is_array($dishes['included'])) {
			ksort($dishes['included']);
			$output .= ucphr('INCLUDED').': ';
			foreach ($dishes['included'] as $key2 => $value2) {
				$output.=ucfirst($value2).", ";
			}
			$output=substr($output,0,-2);
			$output .= '';
		}
		if (!empty($dishes['available']) && is_array($dishes['available'])) {
			ksort($dishes['available']);
			$output .= '<br/>'.ucphr('AVAILABLE').': ';
			foreach ($dishes['available'] as $key2 => $value2) {
				$output.=ucfirst($value2).", ";
			}
			$output=substr($output,0,-2);
			$output .= '';
		}

		$output .= '</td>
	</tr>';
	}
	
	
	
	$output .= '
		<tr>
			<td>
			'.ucphr('ID').':
			</td>
			<td>
			'.$arr['id'].'
			</td>
		</tr>
		<tr>
			<td>
			'.ucphr('CATEGORY').':
			</td>
			<td>
			<select name="data[category]">
				<option value="0"';
	if(0==$arr['category']) $output .= ' selected';
	$output .= '>'.ucphr('ALL').'</option>';
	
	$query="SELECT * FROM `#prefix#categories` WHERE `deleted`='0'";
	$res_type=common_query($query,__FILE__,__LINE__);
	if(!$res_type) return ERR_MYSQL;
	while($arr_type=mysql_fetch_array($res_type)){
		$output .= '
				<option value="'.$arr_type['id'].'"';
		if($arr_type['id']==$arr['category']) $output .= ' selected';
		$output .= '>';
	$categ = new category($arr_type['id']);
	$descr=$categ->name($_SESSION['language']);
	unset($categ);
	$output .= ucfirst($descr); 
	$output .= '</option>';
	}
	$output .= '
			</select>
			</td>
		</tr>';
	
	if(!$editing || $input_data['show_names']) {
		$output .= '
		<tr>
			<td>
			'.ucphr('INGREDIENT_CODE').':
			</td>
			<td>
			<input type="text" name="data[name]" value="'.htmlentities($arr['name']).'"> (<a href="'.$this->file.'?class='.get_class($this).'&amp;command=edit&amp;data[id]='.$this->id.'&amp;data[show_names]=0">'.ucphr('HIDE_NAMES').'</a>)
			</td>
		</tr>';

	
		$res_lang=mysql_list_tables($_SESSION['common_db']);
		while($arr_lang=mysql_fetch_array($res_lang)) {
			if($lang_now=stristr($arr_lang[0],$GLOBALS['table_prefix'].'ingreds_')) {
				$lang_now= substr($lang_now,-2);
	
				if($editing) {
					$ingred = new ingredient ($this->id);
					$lang_name = $ingred -> name ($lang_now);
				}
		
				$output .= '
		<tr>
			<td>'.ucphr('NAME').' ('.$lang_now.')</td>
			<td><input type="text" name="data[ingreds_'.$lang_now.']" value="'.$lang_name.'"></td>
		</tr>';
			}
		}
	} else {
		$output .= '
		<tr>';

		$output .= '
			<input type="hidden" name="data[name]" value="'.htmlentities($arr['name']).'">';
		$res_lang=mysql_list_tables($_SESSION['common_db']);
		while($arr_lang=mysql_fetch_array($res_lang)) {
			if($lang_now=stristr($arr_lang[0],$GLOBALS['table_prefix'].'ingreds_')) {
				$lang_now= substr($lang_now,-2);
				$ingred = new ingredient ($this->id);
				$lang_name = $ingred -> name ($lang_now);
				$output .= '
			<input type="hidden" name="data[ingreds_'.$lang_now.']" value="'.$lang_name.'">';
			}
		}
				
		$output .= '
			<td>'.ucphr('NAME').' ('.$_SESSION['language'].')</td>
			<td>'.$this->name($_SESSION['language']).' (<a href="'.$this->file.'?class='.get_class($this).'&amp;command=edit&amp;data[id]='.$this->id.'&amp;data[show_names]=1">'.ucphr('SHOW_NAMES').'</a>)</td>
		</tr>';
		
	}
	
	$output .= '
		<tr>
			<td>
			'.ucphr('PRICE').':
			</td>
			<td>
			<input type="text" name="data[price]" value="'.$arr['price'].'">
			</td>
		</tr>
		<tr>
			<td>
			'.ucphr('SELL_PRICE').':
			</td>
			<td>
			<input type="text" name="data[sell_price]" value="'.$arr['sell_price'].'">
			</td>
		</tr>		
		<tr>
			<td colspan="2">
			<input type="checkbox" name="data[override_autocalc]" value="1"';
	if($arr['override_autocalc']) $output .= ' checked';
	$output .= '>'.ucphr('INGREDIENT_OVERRIDE_AUTOCALC').' '.help_sticky('INGREDIENT_OVERRIDE_AUTOCALC').'
			</td>
		</tr>';
		
		$output .= '
		<tr>
			<td colspan="2">
			<input type="checkbox" name="data[visible]" value="1"';
	if($arr['visible']) $output .= ' checked';
	$output .= '>'.ucphr('VISIBLE_TO_WAITERS').' '.help_sticky('VISIBLE_TO_WAITERS').'
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
				<input type="submit" value="'.ucphr('UPDATE').'">
	</form>
				</td>
				<td>
				<form action="?" name="delete_form_'.get_class($this).'" method="post">
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

	if(!$editing) {
		return $output;
	}
	
	$output .= '
	</td>
	<td>';
	
	if(class_exists('stock_object')) {
		$stock = new stock_object;
		$stock_id=$stock->find_external($this->id, TYPE_INGREDIENT);
		
		if($stock_id) {
			$obj = new stock_object($stock_id);
			$obj_data['no_back_to_list']=true;
			$obj_data['vertical']=true;
			$output .= $obj -> form($obj_data);
		} else {
			$output .= '
			<a href="'.ROOTDIR.'/stock/index.php?class=stock_object&command=create_from_external&data[ref_id]='.$this->id.'&data[ref_type]='.TYPE_INGREDIENT.'">'.ucphr('CREATE_ASSOCIATED_STOCK').'</a>';
		}
	}
	
	$output .= '
	</td>
	</tr>
	</table>
	';
	
	return $output;
	}
}

?>