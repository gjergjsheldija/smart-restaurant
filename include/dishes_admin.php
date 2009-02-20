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
	
		$query="SELECTdishes.id, 
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

	function post_edit_page ($class) {
		return 0;
	}
	
	function check_values($input_data){
		global $tpl;
		$msg="";
		
		$name_found=false;
		for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
			if(stristr($key,'dishes') && trim($value)!='') {
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
			$msg=ucphr('CHECK_PRICE');
		}
	
		if($msg){
			$tmp = "<script language=\"javascript\">
				window.alert(\"".$msg."\");
				history.go(-1);
			</script>\n";
			$tpl -> append ('scripts',$tmp);

			return -2;
		}
	
		if(is_array($input_data['ingreds']))
			$input_data['ingreds']=implode (" ", $input_data['ingreds']);
		else
			$input_data['ingreds']="";
		if(is_array($input_data['dispingreds']))
			$input_data['dispingreds']=implode (" ", $input_data['dispingreds']);
		else
			$input_data['dispingreds']="";
	
		$input_data['price']=str_replace (",", ".", $input_data['price']);
		$input_data['price']=round ($input_data['price'],2);
	
	
		if(!$input_data['autocalc'])
			$input_data['autocalc']=0;
		if(!$input_data['stock_is_on'])
			$input_data['stock_is_on']=0;
		if(!$input_data['generic'])
			$input_data['generic']=0;
		if(!$input_data['visible'])
			$input_data['visible']=0;
	
	
		return $input_data;
	}

	function form ($input_data=array()) {
		return $this->form_new($input_data);
		return $this->form_old($input_data);
	}
	
	function form_new ($input_data=array()) {
		global $tpl;
		if($_REQUEST['data']['show_names']) $input_data['show_names']=true;
		$this -> commands_horizontal(get_class($this));
		
		$display = new display();
		$display->highlight=false;
		$display->show_head=true;
		$output = '';
		
		if($this->id) {
			$editing=1;
			$query="SELECT * FROM `".$this->table."` WHERE `id`='".$this->id."'";
			$res=common_query($query,__FILE__,__LINE__);
			if(!$res) return mysql_errno();
			
			$arr=mysql_fetch_array($res);
			$ingreds = explode (" ", $arr['ingreds']);
			$dispingreds = explode (" ", $arr['dispingreds']);
	
			$catnames=admin_categories_names_array();
			if(!is_array($catnames)){
				$catnames="";
			}
		} else {
			$editing=0;
			$arr['id']=next_free_id($_SESSION['common_db'],$this->table);
			$arr['visible']=1;
		}
		
		
		$row = 0;
		$col = 0;
		/*************************************************
		Head
		*************************************************/
		$desc = ucphr('DISH');
		if($editing) $desc.=' - '.$this->name($_SESSION['language']);
		if ($editing && !$input_data['show_names']) {
			$desc .=' (<a href="'.$this->file.'?class='.get_class($this).'&amp;command=edit&amp;data[id]='.$this->id.'&amp;data[show_names]=1">'.ucphr('SHOW_NAMES').'</a>)';
		} else {
			$desc .=' (<a href="'.$this->file.'?class='.get_class($this).'&amp;command=edit&amp;data[id]='.$this->id.'&amp;data[show_names]=0">'.ucphr('HIDE_NAMES').'</a>)';
		}
		$display->properties[$row][$col]='colspan=2';
		$display->rows[$row][$col]=$desc;
		$col++;
		$row++;
		$col=0;
		
		/*************************************************
		Names
		*************************************************/
		if(!$editing || $input_data['show_names']) {
			$display->rows[$row][$col]=ucphr('DISH_CODE');
			$col++;
			$display->rows[$row][$col]='<input type="text" name="data[name]" value="'.htmlentities($arr['name']).'">';
			$col++;
			$row++;
			$col=0;
			
			$res_lang=mysql_list_tables($_SESSION['common_db']);
			while($arr_lang=mysql_fetch_array($res_lang)) {
				if($lang_now=stristr($arr_lang[0],'dishes')) {
					$lang_now= substr($lang_now,-2);
		
					if($editing) {
						$dish = new dish ($arr['id']);
						$lang_name = $dish -> name ($lang_now);
					}
		
					$display->rows[$row][$col]=ucphr('NAME').' ('.$lang_now.')';
					$col++;
					$display->rows[$row][$col]='<input type="text" name="data[dishes'.$lang_now.']" value="'.$lang_name.'">';
					$col++;
					$row++;
					$col=0;
				}
			}
		}
		
		
		/*************************************************
		Price
		*************************************************/
		$display->rows[$row][$col]=ucphr('PRICE');
		$display->widths[$row][$col]="50%";
		$col++;
		$display->rows[$row][$col]='<input type="text" name="data[price]" value="'.$arr['price'].'">';
		$col++;
		$row++;
		$col=0;
		
		/*************************************************
		Category
		*************************************************/
		$select = '
		<select name="data[category]">';
		$query_local="SELECT * FROM `categories` WHERE `deleted`='0' ORDER BY `name`";
		$res_local=common_query($query_local,__FILE__,__LINE__);
		if(!$res_local) return ERR_MYSQL;
		while($arr_local=mysql_fetch_array($res_local)){
			if($arr['category']==$arr_local['id']) $selected=" selected";
			else $selected="";
			$select .= '
				<option value="'.$arr_local['id'].'"'.$selected.'>';
			$categ = new category($arr_local['id']);
			$descr=$categ->name($_SESSION['language']);
			unset($categ);
			
			$select .= ucfirst($descr).'</option>';
		}
		$select .= '
		</select>';
		$display->widths[$row][$col]='100%';
		$display->rows[$row][$col]=ucphr('CATEGORY');
		$col++;
		$display->rows[$row][$col]=$select;
		$col++;
		$row++;
		$col=0;
		
		/*************************************************
		Print destination
		*************************************************/
		$select = '
		<select name="data[destid]">';
		$table='dests';
		$query_local="SELECT * FROM `$table`";
		$query_local.=" WHERE `deleted`='0'";
		$query_local.=" ORDER BY `name`";
		$res_local=common_query($query_local,__FILE__,__LINE__);
		if(!$res_local) return ERR_MYSQL;
		while($arr_local=mysql_fetch_array($res_local)){
			if($arr['destid']==$arr_local['id']) $selected=" selected";
			else $selected="";
			$select .= '
			<option value="'.$arr_local['id'].'"'.$selected.'>'.ucfirst($arr_local['name']).'</option>';
		}
		$select .= '
		</select>';
		$display->rows[$row][$col]=ucphr('PRINT_DESTINATION');
		$col++;
		$display->rows[$row][$col]=$select;
		$col++;
		$row++;
		$col=0;
		
		/*************************************************
		Autocalc
		*************************************************/
		if($arr['autocalc']) $checked = ' checked';
		else $checked='';
		$display->properties[$row][$col]='valign="baseline"';
		$display->rows[$row][$col]=ucphr('DISH_AUTOMATIC_CALCULATOR').' '.help_sticky('DISH_AUTOMATIC_CALCULATOR');
		$col++;
		$display->rows[$row][$col]='<input type="checkbox" name="data[autocalc]" value="1"'.$checked.'>';
		$col++;
		$row++;
		$col=0;
		
		
		/*************************************************
		Autocalc skip
		*************************************************/
		$select = '
		<select name="data[autocalc_skip]">';
		$query_local="SELECT * FROM `autocalc` ORDER BY `quantity`";
		$res_local=common_query($query_local,__FILE__,__LINE__);
		if(!$res_local) return ERR_MYSQL;
		while($arr_local=mysql_fetch_array($res_local)){
			if($arr['autocalc_skip']==$arr_local['quantity']) $selected=" selected";
			else $selected="";	
			$select .= '
			<option value="'.$arr_local['quantity'].'"'.$selected.'>'.ucfirst($arr_local['quantity']).'</option>';
		}
		$select .= '
		</select>';
		$display->rows[$row][$col]=ucphr('AUTOCALC_START_VALUE');
		$col++;
		$display->rows[$row][$col]=$select;
		$col++;
		$row++;
		$col=0;
		
		/*************************************************
		Generic
		*************************************************/
		if($arr['generic']) $checked = ' checked';
		else $checked='';
		//$display->properties[$row][$col]='colspan=2';
		$display->rows[$row][$col]=ucphr('DISH_GENERIC');
		$col++;
		$display->rows[$row][$col]='<input type="checkbox" name="data[generic]" value="1"'.$checked.'>';
		$col++;
		$row++;
		$col=0;
		
		/*************************************************
		Visible
		*************************************************/
		if($arr['visible']) $checked = ' checked';
		else $checked='';
		//$display->properties[$row][$col]='colspan=2';
		$display->rows[$row][$col]=ucphr('VISIBLE_TO_WAITERS');
		$col++;
		$display->rows[$row][$col]='<input type="checkbox" name="data[visible]" value="1"'.$checked.'>';
		$col++;
		$row++;
		$col=0;
		
		/*************************************************
		Ingredients
		*************************************************/
		$display2 = new display;
		$display2->highlight=false;
		$display2->show_head=true;
		if($editing){
			$dish = new dish($this->id);
			
			$all=array();
			$included=array();
			$available=array();
			
			$all=$dish->possible_ingredients();
			$included = $dish -> ingredients ();
			$available = $dish -> dispingredients ();
			
			$all=admin_dishes_get_name_array($all);
			$included=admin_dishes_get_name_array($included);
			$available=admin_dishes_get_name_array($available);
			$ingreds_rows=10;
			$ingreds_data = '
		<table border=0>
		<tr>
		<td rowspan=2>
		'.ucphr('ALL').':<br/>
		<select name="all[]" multiple size='.$ingreds_rows.'>';
			foreach($all as $local_id=>$name) {
				$ingreds_data .= '
			<option value="'.$local_id.'">'.$name.'</option>';
			}
			$ingreds_data .= '
		</select>
		</td>';
		
			$ingreds_data .= '
		<td>
			<a href="#" onclick="javascript:move(\'edit_form_'.get_class($this).'\',\'all[]\',\'data[ingreds][]\');return false;">
			<img border="0" src="'.ROOTDIR.'/images/right.png" onclick="javascript:move(\'edit_form_'.get_class($this).'\',\'all[]\',\'data[ingreds][]\');return false;" alt="&lt;-">
			</a>
			<br/>
			<a href="#" onclick="javascript:move(\'edit_form_'.get_class($this).'\',\'data[ingreds][]\',\'all[]\');return false;">
			<img border="0" src="'.ROOTDIR.'/images/left.png" onclick="javascript:move(\'edit_form_'.get_class($this).'\',\'data[ingreds][]\',\'all[]\');return false;" alt="&lt;-">
			</a>
		</td>';
	
			$ingreds_data .= '
		<td>
		'.ucphr('INCLUDED').':<br/>
		<select name="data[ingreds][]" multiple size='.((int)($ingreds_rows/2)).'>';
			foreach($included as $local_id=>$name) {
				$ingreds_data .= '
			<option value="'.$local_id.'">'.$name.'</option>';
			}
			$ingreds_data .= '
		</select>
		</td>';
	
			$ingreds_data .= '
		</tr>
		<tr>';
	
			$ingreds_data .= '
		<td>
			<a href="#" onclick="javascript:move(\'edit_form_'.get_class($this).'\',\'all[]\',\'data[dispingreds][]\');return(false);">
			<img border="0" src="'.ROOTDIR.'/images/right.png" onclick="javascript:move(\'edit_form_'.get_class($this).'\',\'all[]\',\'data[dispingreds][]\');return(false);" alt="-&gt;">
			</a>
			<br/>
			<a href="#" onclick="javascript:move(\'edit_form_'.get_class($this).'\',\'data[dispingreds][]\',\'all[]\');return(false);">
			<img border="0" src="'.ROOTDIR.'/images/left.png" onclick="javascript:move(\'edit_form_'.get_class($this).'\',\'data[dispingreds][]\',\'all[]\');return(false);" alt="&lt;-">
			</a>
		</td>';
	
			$ingreds_data .= '
		<td>
		'.ucphr('AVAILABLE').':<br/>
		<select name="data[dispingreds][]" multiple size='.((int)($ingreds_rows/2)).'>';
			foreach($available as $local_id=>$name) {
				$ingreds_data .= '
			<option value="'.$local_id.'">'.$name.'</option>';
			}
			$ingreds_data .= '
		</select>
		</td>';
			$ingreds_data .= '
		</tr>
		</table>';
		}
		$row=0;
		$col=0;
		$display2->rows[$row][$col]=ucphr('INGREDIENTS');
		$row++;
		$col=0;
		$display2->properties[$row][$col]='align="center"';
		$display2->rows[$row][$col]=$ingreds_data;
		
		/*************************************************
		Page
		*************************************************/
		$tpl->append('title',' - '.$this->name($_SESSION['language']));
		
		$output .= '
	<form action="'.$this->file.'?" name="edit_form_'.get_class($this).'" method="post">
	<input type="hidden" name="class" value="'.get_class($this).'">
	<input type="hidden" name="data[id]" value="'.$arr['id'].'">';
		if($editing){
			$output .= '
	<input type="hidden" name="command" value="update">';
		} else {
			$output .= '
	<input type="hidden" name="command" value="insert">';
		}
		
			if ($editing && !$input_data['show_names']) {
				$output .= '
	<input type="hidden" name="data[name]" value="'.htmlentities($arr['name']).'">';
				$res_lang=mysql_list_tables($_SESSION['common_db']);
				while($arr_lang=mysql_fetch_array($res_lang)) {
					if($lang_now=stristr($arr_lang[0],'dishes')) {
						$lang_now= substr($lang_now,-2);
						$ingred = new dish ($this->id);
						$lang_name = $ingred -> name ($lang_now);
						$output .= '
	<input type="hidden" name="data[dishes'.$lang_now.']" value="'.$lang_name.'">';
					}
				}
		}

		$output.=$display->list_table();
		$output.=$display2->list_table();
	
		/*************************************************
		Form buttons
		*************************************************/
		$output.='
		<table width="100%">
			<tr>';

		if(!$editing) {
			$output .= '
				<td align="center">
				<input type="submit" value="'.ucphr('INSERT').'">
	</form>
				</td>';
		} else {
			$link='javascript:select_all(\'edit_form_'.get_class($this).'\',\'data[ingreds][]\');select_all(\'edit_form_'.get_class($this).'\',\'data[dispingreds][]\');';
			$output .= '
				<td align="right">
				<input type="submit" onclick="'.$link.'" value="'.ucphr('UPDATE').'">
	</form>
				</td>
				<td align="left">
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
			</table>';
			
			
		if(!$editing) {
			$display->properties[$row][$col]='colspan=2 align="center"';
			$display->rows[$row][$col]='<input type="submit" value="'.ucphr('INSERT').'"></form>';
			$col++;
			$row++;
			$col=0;
		} else {
			$link='javascript:select_all(\'edit_form_'.get_class($this).'\',\'data[ingreds][]\');select_all(\'dish_form\',\'data[dispingreds][]\');';
			$display->properties[$row][$col]='align="right"';
			$display->rows[$row][$col]='<input type="submit" onclick="'.$link.'" value="'.ucphr('UPDATE').'"></form>';
			$col++;
			$display->properties[$row][$col]='align="left"';
			$display->rows[$row][$col]='
		<form action="'.$this->file.'?" name="delete_form_'.get_class($this).'" method="post">
		<input type="hidden" name="class" value="'.get_class($this).'">
		<input type="hidden" name="command" value="delete">
		<input type="hidden" name="delete[]" value="'.$this->id.'">
		<input type="submit" value="'.ucphr('DELETE').'">
		</form>';
			$col++;
			$row++;
			$col=0;
		}
		
		$tpl->assign('dish_data',$output);
		
		if(!$editing) return $output;
		
		if(class_exists('stock_movement')) {
			$mov = new stock_movement();
			$mov -> only_dish = $this->id;
			$mov->admin_pre_list();
			$tmp = $mov -> list_table();
			$tpl->assign('moviments',$tmp);
		}
		
		if(class_exists('stock_object')) {
			if(count($this->ingredients()) || count($this->dispingredients())) {
				$obj = new stock_dish ($this->id);
				$obj -> form_properties['show_name'] = false;
				$obj -> form_properties['show_head_dish'] = true;
				$tmp = $obj->available_quantity_form();
				$tpl->assign('stock_data',$tmp);
				
				$tmp = $obj->form();
				$tpl->assign('ingredients_quantities',$tmp);
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

function admin_dishes_get_name_array($array) {
	if(!is_array($array)) return 0;
	$out=array();
	
	foreach($array as $local_id) {
		$ingred = new ingredient ($local_id);
		$name = $ingred->name($_SESSION['language']);
		$name = ucfirst($name);
		
		if($ingred->data['category']==0) $catname = ucphr('ALL');
		else {
			$cat = new category ($ingred->data['category']);
			$catname = $cat -> name($_SESSION['language']);
		}
		
		$out[$local_id]=$name.' ('.$catname.')';
		asort($out);
	}
	return $out;
}

?>