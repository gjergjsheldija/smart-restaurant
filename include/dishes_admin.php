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
* @copyright	Copyright 2006-2009, Gjergj Sheldija
*/

class dish extends object {
	function dish($id=0) {
		$this->table='dishes';
		$this->id=$id;
		$this->fields_names=array(	'id'=>ucphr('ID'),
								'name'=>ucphr('NAME'),
								'destid'=>ucphr('PRINTER'),
								'price'=>ucphr('PRICE'),
								'category'=>ucphr('CATEGORY'),
								'autocalc'=>ucphr('AUTOCALC'),
								'generic'=>ucphr('GENERIC'),
								'visible'=>ucphr('VISIBLE'));
		$this -> title = ucphr('DISHES');
		$this->file=ROOTDIR.'/admin/admin.php';
		$this->fields_width=array(	'name'=>'100%');
		$this->allow_single_update = array ('autocalc','generic','visible');
		$this->templates['edit']='dish_edit';
		
		$this -> show_category_list = true;
		$this->flag_delete = true;
		$this -> multilang = true;
		$this -> fetch_data();
	}
	
	function sync_all_dishes_ingredients () {
		$query = "SELECT `id` FROM `dishes`";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return 0;
		
		while($arr=mysql_fetch_array($res)){
			$dish=new dish($arr['id']);
			if($err=$dish->sync_dish_ingredients()) return $err;
		}
		return 0;
	}
	
	/**
	* search a dish by name
	* if found 1 returns id
	* if no found returns 0
	* if found many returns -1
	*/
	function search_name_rows ($search) {
	
		$query="SELECT dishes.id, 
		dishes.table_name
		FROM `dishes`
		JOIN `dishes` ON dishes.id=dishes.table_id
		WHERE (LCASE(`table_name`) LIKE '".$search."%'
			OR LCASE(`name`) LIKE '".$search."%'
			OR LCASE(`table_name`) LIKE '% ".$search."%'
			OR LCASE(`name`) LIKE '% ".$search."%'
			)";
		if(!get_conf(__FILE__,__LINE__,"invisible_show")) {
			$query .= "AND `visible`='1'";
		}
		$query .= "
		AND dishes.deleted='0'
		ORDER BY table_name ASC";
	
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return '';
		
		$num=mysql_num_rows($res);
		if($num>1) return -1;
		elseif($num==1) {
			$arr=mysql_fetch_assoc($res);
			return $arr['id'];
		}
		
		return 0;
	}
	
	function sync_dish_ingredients () {
		$this->fetch_data();
		
		$ing = new dish_ingredient;
		$ing->silent=true;
		
		$data['dish']=$this->id;
		$data['type']=INGRED_TYPE_AVAILABLE;
		foreach($this->ingredients() as $ingred_id) {
			if($ing->find ($this->id,$ingred_id)) continue;
			
			$data['ingredient']=$ingred_id;
			if($err=$ing->insert($data)) return $err;
		}
		
		$data['type']=INGRED_TYPE_INCLUDED;
		foreach($this->dispingredients() as $ingred_id) {
			if($ing->find ($this->id,$ingred_id)) continue;
			
			$data['ingredient']=$ingred_id;
			if($err=$ing->insert($data)) return $err;
		}
		
		return 0;
	}
	
	function list_search ($search) {
		$query = '';
		
		$table = $this->table;
		$lang_table = $table."_".$_SESSION['language'];
		
		$query="SELECT
				$table.`id`,
				$lang_table.`table_name` as `name`,
				RPAD('".ucphr('DISHES')."',30,' ') as `table`,
				".TABLE_DISHES." as `table_id`
				FROM `$table`
				LEFT JOIN `$lang_table` ON $lang_table.`table_id`=$table.`id`
				WHERE $table.`deleted`='0'
				AND ($lang_table.`table_name` LIKE '%$search%' OR $table.`name` LIKE '%$search%')
				";

		return $query;
	}
	
	function list_query_all () {
		$table = $this->table;
		$lang_table = $table."_".$_SESSION['language'];
		$cat_table = "categories";
		$cat_lang_table = "categories_".$_SESSION['language'];
		$printer_table = "dests";
		
		$query="SELECT
				$table.`id`,
				IF($lang_table.`table_name`='' OR $lang_table.`table_name` IS NULL,$table.`name`,$lang_table.`table_name`) as `name`,
				$printer_table.`name` as `destid`,
				$table.`price`,
				IF($cat_lang_table.`table_name`='' OR $cat_lang_table.`table_name` IS NULL,$cat_table.`name`,$cat_lang_table.`table_name`) as `category`,
				IF($table.`autocalc`='0','".ucphr('NO')."','".ucphr('YES')."') as `autocalc`,
				IF($table.`generic`='0','".ucphr('NO')."','".ucphr('YES')."') as `generic`,
				IF($table.`visible`='0','".ucphr('NO')."','".ucphr('YES')."') as `visible`
				FROM `$table`
				LEFT JOIN `$lang_table` ON $lang_table.`table_id`=$table.`id`
				LEFT JOIN `$cat_table` ON $cat_table.`id`=$table.`category`
				LEFT JOIN `$cat_lang_table` ON $cat_lang_table.`table_id`=$table.`category`
				LEFT JOIN `$printer_table` ON $printer_table.`id`=$table.`destid`
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
				$dish = new dish($arr['id']);
				
				$value .= '<span class="admin_ingreds_list">';
				
				$ingreds=$dish->ingredients_names();
				if (!empty($ingreds) && is_array($ingreds)) {
					$value .= '<br/>'.ucphr('INCLUDED').': ';
					foreach ($ingreds as $key2 => $value2) {
						$value.=ucfirst($value2).", ";
					}
					$value=substr($value,0,-2);
					$value .= '';
				}
				$ingreds=$dish->dispingredients_names();
				if (!empty($ingreds) && is_array($ingreds)) {
					$value .= '<br/>'.ucphr('AVAILABLE').': ';
					foreach ($ingreds as $key2 => $value2) {
						$value.=ucfirst($value2).", ";
					}
					$value=substr($value,0,-2);
					$value .= '';
				}
				$value .= '</span>';
			}
			
			$display->rows[$row][$col]=$value;
			if($link && $field=='name') $display->links[$row][$col]=$link;
			if($link) $display->clicks[$row][$col]='redir(\''.$link.'\');';
			
			$col++;
		}
	}
	
	function pre_insert($input_data) {
		if(!is_array($input_data)) return $input_data;
		for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
			if(stristr($key,'dishes')) {
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
	
	function pre_delete($input_data) {
		if(!$this->id) return 1;
		if(!$this->exists()) return 2;

		if($lang_del=$this->translations_delete($this->id)) return $lang_del;

		$stock = new stock_object;
		if ($stock_id=$stock->find_external($this->id, TYPE_DISH)) {
			$stock = new stock_object($stock_id);
			$stock->silent=true;
			if($err=$stock->delete()) return $err;
		}
		
		return $input_data;
	}
	
	function pre_update($input_data) {
		if(!$this->id) return 1;
		if(!$this->exists()) return 2;

		if($err=$this->translations_set($input_data)) return $err;

		for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
			if(stristr($key,'dishes')) {
				unset ($input_data[$key]);
			}
		}
		
		return $input_data;
	}
	
	function ingredient_remove ($ingredid) {
		$ingreds = $this->get('ingreds');
		$ingreds = trim($ingreds);
		if(!empty($ingreds)) {
			$ingreds = explode (" ", $ingreds);
			foreach($ingreds as $key => $value) if($value==$ingredid) unset ($ingreds[$key]);
			$ingreds = implode (" ", $ingreds);
			if($err = $this -> set ('ingreds',$ingreds)) return $err;
		}
		
		$ingreds = $this->get('dispingreds');
		$ingreds = trim($ingreds);
		if(!empty($ingreds)) {
			$ingreds = explode (" ", $ingreds);
			foreach($ingreds as $key => $value) if($value==$ingredid) unset ($ingreds[$key]);
			$ingreds = implode (" ", $ingreds);
			if($err = $this -> set ('dispingreds',$ingreds)) return $err;
		}
		return 0;
	}
	
	//RTG: included for performance, better than generic get that imply one query
	//see use in
	function getPrice() {
		return $this->data['price'];   
	}
	
	function getGeneric() {
		return $this->data['generic'];   
	}
	
	function getAutocalc() {
		return $this->data['autocalc'];   
	} 
	
	function ingredients_names() {
		$output=array();
		$ingreds = $this->get('ingreds');
		$ingreds = trim($ingreds);
		if(empty($ingreds)) return $output;
		$ingreds = explode (" ", $ingreds);
		for (reset ($ingreds); list ($key, $value) = each ($ingreds); ) {
			$local = new ingredient($value);
			if($local->data['deleted']) continue;
			$output[$value]=$local->name($_SESSION['language']);
		}
		return $output;
	}
	
	function dispingredients_names() {
		$output=array();
		$ingreds = $this->get('dispingreds');
		$ingreds = trim($ingreds);
		if(empty($ingreds)) return $output;
		$ingreds = explode (" ", $ingreds);
		for (reset ($ingreds); list ($key, $value) = each ($ingreds); ) {
			$local = new ingredient($value);
			if($local->data['deleted']) continue;
			$output[$value]=$local->name($_SESSION['language']);
		}
		return $output;
	}
	
	function dispingredients($arr='') {
		$output=array();
		if(empty($arr)) $ingreds = $this->data['dispingreds'];
		else $ingreds = $arr;
		
		$ingreds = trim($ingreds);
		if(empty($ingreds)) return array();
		
		$output = explode (" ", $ingreds);
		sort($output);
		return $output;
	}
	
	function ingredients($arr='') {
		$output=array();
		if(empty($arr)) $ingreds = $this->data['ingreds'];
		else $ingreds = $arr;
		$ingreds = trim($ingreds);
		if(empty($ingreds)) return array();
		
		$output = explode (" ", $ingreds);
		sort($output);
		return $output;
	}
	
	function possible_ingredients() {
		$all=array();
		$ingreds=array();
		$dispingreds=array();
		$ingreds = $this->ingredients();
		$dispingreds = $this->dispingredients();
		
		$not_poss = array_merge($ingreds,$dispingreds);
		
		$query = "SELECT * FROM `ingreds`";
		$query .= " WHERE `category` = '".$this->data['category']."' OR `category` = '0'";
		$query .= " AND `deleted` = '0'";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return 0;
		
		while($arr=mysql_fetch_array($res)){
			$all[]=$arr['id'];
		}
		
		$poss = array_diff($all,$not_poss);
		sort($poss);
		return $poss;
	}

}

?>