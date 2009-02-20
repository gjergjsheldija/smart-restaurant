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

class object {
	var $table;
	var $id;
	var $db;
	var $fields_show;
	var $fields_boolean;
	var $show_empty_name=false;
	var $show_category_list = false;
	var $multilang;
	var $data;
	var $limit_start;
	var $orderby;
	var $sort;
	var $search;
	var $disable_mass_delete;
	var $flag_delete;
	var $no_name;
	var $referring_name;
	var $file;
	var $silent;
	var $main_list_item = 'name';
	var $disable_new;
	var $fields_width;
	var $fields_names;
	var $form_properties;
	
	var $title;
	var $number_found;
	var $global_search;
	var $newvalue;

	function fetch_data ($override_cache=false) {
		$cache = new cache ();
		if(!$override_cache) {
			if($this->db=='common' && $cache_out=$cache -> get ($this->table,$this->id,'ALL')) {
				$this->data = $cache_out;
				return 0;
			}
		}
		
		$query="SELECT * FROM `".$this->table."` WHERE id='".$this->id."'";
		if($this->db=='common') $res = common_query($query,__FILE__,__LINE__);
		else $res = accounting_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
		
		$arr = mysql_fetch_assoc ($res);
		$this->data = $arr;
		
		$cache->set ($this->table,$this->id,'ALL',$arr);
		
		return 0;
	}
	
	function count_records () {
		$query="SELECT * FROM `".$this->table."`";
		if($this->db=='common') $res = common_query($query,__FILE__,__LINE__);
		else $res = accounting_query($query,__FILE__,__LINE__);
		if(!$res) return 0;
		
		return mysql_num_rows($res);
	}
	
	function exists($override_cache=false) {
		if(!$override_cache) {
			$cache = new cache ();
			if($this->db=='common' && $cache_out=$cache -> get ($this->table,$this->id,'id')) return $cache_out;
		}
		
		$query="SELECT `id` FROM `".$this->table."` WHERE id='".$this->id."'";
		if($this->flag_delete) $query .= " AND `deleted`='0'";
		
		if($this->db=='common') $res = common_query($query,__FILE__,__LINE__);
		else $res = accounting_query($query,__FILE__,__LINE__);
		if(!$res) return 0;
		
		$ret = mysql_num_rows($res);
		
		$cache -> set ($this->table,$this->id,'id',$ret);
		
		return $ret;
	}

	function name($lang='',$newname='') {
		if($this->id==SERVICE_ID) return phr('SERVICE_FEE');
	
		if(isset($this->no_name) && $this->no_name) return '';
		
		if(isset($this->referring_name) && $this->referring_name) {
			$this->fetch_data();
			$obj_type=$this->data['ref_type'];
			if($obj_type==TYPE_DISH) $obj = new dish ($this->data['ref_id']);
			elseif($obj_type==TYPE_INGREDIENT) $obj = new ingredient ($this->data['ref_id']);
			else return $this->data['name'];
			
			return $obj -> name ($lang);
		}

		if($newname) $this->set_name($lang,$newname);

		$lang_table_found=false;
		if($lang) {
			$lang_table=$this->table."_".$lang;
			
			$tables=common_list_tables();
			if(in_array($lang_table,$tables)) $lang_table_found=true;
			
		}
		
		if($lang_table_found) {
			$cache = new cache ();
			
			$query="SELECT `name` FROM `".$this->table."` WHERE `id`='".$this->id."'";
			if($this->db=='common') $res = common_query($query,__FILE__,__LINE__);
			else $res = accounting_query($query,__FILE__,__LINE__);
			if(!$res) return '';
			
			$arr = mysql_fetch_array ($res);

			if(!empty($arr['name']) || $this->show_empty_name) {
				$ret=$arr['name'];
				$ret=stripslashes($ret);
				
				$charset = lang_get($lang,'CHARSET');
				if($charset=='CHARSET' || empty($charset)) $charset='iso-8859-1';
				$ret = html_entity_decode ($ret,ENT_QUOTES,$charset);
			
				$cache -> set ($lang_table,$this->id,'name',$ret);
				return $ret;
			}
		}

		$cache = new cache ();
		if($this->db=='common' && $cache_out=$cache -> get ($this->table,$this->id,'name')) return $cache_out;
		
		 /* RTG: if this object has a name column on table, we already have if in data array
		I have seen this is not always true: for example, when construct a generic vat rate
		(without data), so I put this like a conditional */
		
		if ($data && $this->data['name']) {
			$ret=$this->data['name'];
		} else {
			$query="SELECT `name` FROM `".$this->table."` WHERE `id`='".$this->id."'";
			if($this->db=='common') $res = common_query($query,__FILE__,__LINE__);
			else $res = accounting_query($query,__FILE__,__LINE__);
			if(!$res) return '';
			
			$arr = mysql_fetch_array ($res);
			$ret=$arr['name'];
		}
		$ret=stripslashes($ret);

		$cache -> set ($this->table,$this->id,'name',$ret);
		return $ret;
	}

	function set_name($lang='',$newname='') {
		if(!$this->exists()) return 0;

		if(!$newname) $this->name($lang);

		if($lang) {
			$lang_table=$this->table."_".$lang;
			$table=$lang_table;
			$query="UPDATE `".$lang_table."` SET `table_name`='".$newname."' WHERE `table_id`='".$this->id."'";
		} else {
			$table=$this->table;
			$query="UPDATE `".$this->table."` SET `name`='".$newname."' WHERE id='".$this->id."'";
		}

		$cache = new cache (); $cache -> flush ($table,$this->id);

		if($this->db=='common') $res = common_query($query,__FILE__,__LINE__);
		else $res = accounting_query($query,__FILE__,__LINE__);
		if(!$res) return mysql_errno();
		
		return 0;
	}

	function get($what=''){

		if(empty($what)) return '';
		
		$cache = new cache ();
		if($this->db=='common' && $cache_out=$cache -> get ($this->table,$this->id,$what)) return $cache_out;
		
		$query="SELECT $what FROM `".$this->table."` WHERE id='".$this->id."'";
		if($this->db=='common') $res = common_query($query,__FILE__,__LINE__);
		else $res = accounting_query($query,__FILE__,__LINE__);
		if(!$res) return '';
		
		$arr = mysql_fetch_array ($res);
		$ret=$arr[$what];
		$ret=stripslashes($ret);
		
		$this->$what=$ret;
		$cache -> set ($this->table,$this->id,$what,$ret);
		return $ret;
	}

	function set($what,$new){
		if(!$this->exists()) return 1;

		$cache = new cache ();
		$cache -> flush ($this->table,$this->id);
		
		$query="UPDATE `".$this->table."` SET $what='".$new."' WHERE id='".$this->id."'";
		if($this->db=='common') $res = common_query($query,__FILE__,__LINE__);
		else $res = accounting_query($query,__FILE__,__LINE__);
		if(!$res) return mysql_errno();
		
		return 0;
	}

	function delete($input_data=array()) {
		global $tpl;
		$cache = new cache (); $cache -> flush ($this->table,$this->id);
		
		if(method_exists($this,'pre_delete')) {
			$input_data=$this->pre_delete($input_data);
		}
		if(!is_array($input_data)) return $input_data;

		$name=$this->name();

		// Now we'll build the correct UPDATE query, based on the fields provided
		if(isset($this->flag_delete) && $this->flag_delete)
			$query="UPDATE `".$this->table."` SET `deleted`='1'";
		else
			$query="DELETE FROM `".$this->table."` ";
		
		$query.=" WHERE `id`='".$this->id."'";
		if($this->db=='common') $res = common_query($query,__FILE__,__LINE__);
		else $res = accounting_query($query,__FILE__,__LINE__);
		if(!$res) return mysql_errno();
		
		if(method_exists($this,'post_delete')) {
			$input_data=$this->post_delete($input_data);
			if(!is_array($input_data)) return $input_data;
		}
		
		if(!$this->silent) {
			$tmp = GLOBALMSG_RECORD_THE.' <b>'.$name.'</b> '.GLOBALMSG_RECORD_DELETE_OK.'<br/>';
			$tpl -> append("messages", $tmp);
		}

		return 0;
	}

	function insert($input_data) {
		global $tpl;
		$cache = new cache (); $cache -> flush ($this->table,$this->id);
		
		if(is_array($input_data)) {
			foreach($input_data as $key => $value) {
				if(!is_array($value)) $input_data[$key]=addslashes($input_data[$key]);
			}
		}
		if(method_exists($this,'check_values')) $input_data=$this->check_values($input_data);
		
		if(method_exists($this,'pre_insert')) {
			$input_data=$this->pre_insert($input_data);
		}

		if(!is_array($input_data)) return $input_data;

		// Now we'll build the correct INSERT query, based on the fields provided
		$query="INSERT INTO `".$this->table."` (";
		for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
			$query.="`".$key."`,";
		}
		// strips the last comma that has been put
		$query = substr ($query, 0, -1);
		$query.=") VALUES (";
		for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
			$query.="'".$value."',";
		}
		// strips the last comma that has been put
		$query = substr ($query, 0, -1);
		$query.=")";

		if($this->db=='common') {
			$res = common_query($query,__FILE__,__LINE__);
		}else{
			$res = accounting_query($query,__FILE__,__LINE__);
		}

		if(!$res) {
			$errno=mysql_errno();
			if($errno==1062){
				$msg="ID already used. Insert another ID.";
				$tmp = "<script language=\"javascript\">
				window.alert(\"".$msg."\");
				history.go(-1);
				</script>\n";
				$tpl -> assign("scripts", $tmp);
			}
			return $errno;
		}
		
		$num_affected=mysql_affected_rows();
		$inserted_id = mysql_insert_id();
		if(!$this->silent) {
			$tmp = GLOBALMSG_RECORD_THE.' <b>'.$input_data['name'].'</b> '.GLOBALMSG_RECORD_ADD_OK.'<br/>';
			$tpl -> append("messages", $tmp);
		}
		
		$this->id=$inserted_id;
	
		if(method_exists($this,'post_insert')) {
			$input_data=$this->post_insert($input_data);
			if(!is_array($input_data)) return $input_data;
		}
		
		return 0;
	}

	function update($input_data) {

		global $tpl;
		$cache = new cache (); $cache -> flush ($this->table,$this->id);
		
		if(is_array($input_data)) {
			foreach($input_data as $key => $value) {
				if(!is_array($value)) $input_data[$key]=addslashes($input_data[$key]);
			}
		}
		
		if(method_exists($this,'check_values')) $input_data=$this->check_values($input_data);

		if(method_exists($this,'pre_update')) {
			$input_data=$this->pre_update($input_data);
		}

		if(!is_array($input_data)) return $input_data;

		// Now we'll build the correct UPDATE query, based on the fields provided
		$query="UPDATE `".$this->table."` SET ";
		for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
			$query.="`".$key."`='".$value."',";
		}
		// strips the last comma that has been put
		$query = substr ($query, 0, -1);
		$query.=" WHERE `id`='".$input_data['id']."'";

		if($this->db=='common') $res = common_query($query,__FILE__,__LINE__);
		else $res = accounting_query($query,__FILE__,__LINE__);
		if(!$res) return mysql_errno();

		if(!$this->silent) {
			$tmp = "\n".GLOBALMSG_RECORD_THE.' <b>'.$input_data['name'].'</b> '.GLOBALMSG_RECORD_EDIT_OK.'<br/>';
			$tpl -> append("messages", $tmp);
		}

		if(method_exists($this,'post_update')) {
			$input_data=$this->post_update($input_data);
			if(!is_array($input_data)) return $input_data;
		}
		
		return 0;

	}
	
	function update_field ($field) {
		if(!$this->id) return ERR_NO_ORDER_CHOSEN;
		
		if(!isset($this->allow_single_update)) return ERR_NOT_ALLOWED_TO_CHANGE_FIELD;
		if(!in_array($field,$this->allow_single_update)) return ERR_NOT_ALLOWED_TO_CHANGE_FIELD;
		
		$this->fetch_data();
		$input_data=$this->data;
		$new_value = $this->data[$field] ? 0 : 1;
		
		if($err = $this->set ($field,$new_value)) return $err;
		return 0;
	}
	
	function translations_set ($input_data) {
		$root_table=$this->table;
		for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
	
			if($lang_now=stristr($key,$root_table.'_')) {
				$lang_now= substr($lang_now,-2);
				$table=''.$key;
	
				$cache = new cache (); $cache -> flush ($table,$this->id);
				
				$query="SELECT * FROM `$table` WHERE `table_id`='".$input_data['id']."'";
				if($this->db=='common') $res = common_query($query,__FILE__,__LINE__);
				else $res = accounting_query($query,__FILE__,__LINE__);
				if(!$res) return ERR_MYSQL;
				
				$charset = lang_get($lang_now,'CHARSET');
				$value = htmlentities ($value,ENT_QUOTES,$charset);
				
				if($arr=mysql_fetch_array($res)) {
					$query="UPDATE `$table` SET `table_name`='$value' WHERE `id`='".$arr['id']."'";
				} else {
					$query="INSERT INTO `$table` ( `table_id` , `table_name` ) VALUES ( '".$input_data['id']."' , '$value' )";
				}
				if($this->db=='common') $res = common_query($query,__FILE__,__LINE__);
				else $res = accounting_query($query,__FILE__,__LINE__);
				if(!$res) return ERR_MYSQL;
				
				$table=str_replace('','',$table);
			}
		}
	
		return 0;
	}
	
	function translations_delete ($input_id) {
		// don't delete translations
		if(isset($this->flag_delete) && $this->flag_delete) return 0;
		
		$res_lang=mysql_list_tables($_SESSION['common_db']);
		while($arr_lang=mysql_fetch_array($res_lang)) {
			if($lang_now=stristr($arr_lang[0],$this->table.'_')) {
				$lang_now= substr($lang_now,-2);
				$table=$arr_lang[0];
	
				$cache = new cache (); $cache -> flush ($table,$this->id);
				
				$query="SELECT * FROM `$table` WHERE `table_id`=$input_id";
				if($this->db=='common') $res = common_query($query,__FILE__,__LINE__);
				else $res = accounting_query($query,__FILE__,__LINE__);
				if(!$res) return ERR_MYSQL;
	
				if($arr=mysql_fetch_array($res)) {
					$query="DELETE FROM `$table` WHERE `id`='".$arr['id']."'";
					if($this->db=='common') $res = common_query($query,__FILE__,__LINE__);
					else $res = accounting_query($query,__FILE__,__LINE__);
					if(!$res) return ERR_MYSQL;
					
				}
			}
		}
	
		return 0;
	}
	
	function list_table () {
		global $tpl;
		global $display;
		
		$output = '';
		
		$display = new display;
		$display->show_head=true;
		
		// the correct query for this object
		$query = $this->list_query_all ();

		if(empty($query)) return '';
		
		// Other query data (search, orderby, sort)
		$query .= $this->list_standard_query_edit ();
		
		// first query run to get number of rows
		if($this->db=='common') $res = common_query($query,__FILE__,__LINE__);
		else $res = accounting_query($query,__FILE__,__LINE__);
		if(!$res) return '';

		$this->num_total=mysql_num_rows($res);
		
		$query .= $this->list_calc_limits ();
		$this->list_navbar ();
	
		if(empty($this->search) && !$this->count_records()) return '';
		if(!$this->num_total) return '';

		$this->form_name = 'list_form_'.get_class($this);
		
		// query run to get head
		if($this->db=='common') $res = common_query($query,__FILE__,__LINE__);
		else $res = accounting_query($query,__FILE__,__LINE__);
		if(!$res) return '';
		
		$arr = mysql_fetch_assoc ($res);
		$this->list_head ($arr);

		// query run to get head
		if($this->db=='common') $res = common_query($query,__FILE__,__LINE__);
		else $res = accounting_query($query,__FILE__,__LINE__);
		if(!$res) return '';
		
		$row = 1;						// $row[0] is the head
		while($arr=mysql_fetch_assoc($res)) {
			$this->list_rows ($arr,$row);
			$row++;
		}
		
		$tmp .= $this->list_form_start();
		
		$tmp .= $display -> list_table ();
		
		$tmp .= $this->list_buttons ();
		
		$tmp .= '</form>';

		$tpl -> append("list", $tmp);
		return $tmp;
	}
	
	function list_form_start () {
 		$tmp = '<form name="'.$this->form_name.'" action="'.$this->file.'?" method="post">'."\n";
		return $tmp;
	}
	
	function list_buttons () {
		if(!$this->disable_mass_delete && $this->count_records()) {
			$link = $this->file.'?class='.get_class($this).'&command=delete&delete=all';
			
			$tmp .= '<table width="100%"><tr>'."\n";
			$tmp .= '<td align="left">'."\n";
			$tmp .= '<input type="hidden" name="command" value="delete">'."\n";
			$tmp .= '<input type="hidden" name="class" value="'.get_class($this).'">'."\n";
			$tmp .= '<a href="#" onClick="list_form_'.get_class($this).'.submit();return false;">'.ucphr('DELETE_SELECTED').'</a>'."\n";
			$tmp .= '</td><td align="right">'."\n";
			$tmp .= '<a href="'.$link.'">'.ucphr('DELETE_ALL').'</a>'."\n";
			$tmp .= '</tr></table>'."\n";
		}
		return $tmp;
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
			
			if (isset($this->allow_single_update) && in_array($field,$this->allow_single_update)) {
				$link = $this->link_base.'&amp;command=update_field&amp;data[id]='.$arr['id'].'&amp;data[field]='.$field;
				if($this->limit_start) $link .= '&amp;data[limit_start]='.$this->limit_start;
				if($this->orderby) $link.='&amp;data[orderby]='.$this->orderby;
				if($this->sort) $link.='&amp;data[sort]='.$this->sort;
				
				$display->links[$row][$col]=$link;
			} elseif (method_exists($this,'form')) $link = $this->file.'?class='.get_class($this).'&amp;command=edit&amp;data[id]='.$arr['id'];
			else $link='';
			
			$display->rows[$row][$col]=$value;
			if($link && $field=='name') $display->links[$row][$col]=$link;
			if($link) $display->clicks[$row][$col]='redir(\''.$link.'\');';
			$col++;
		}
	}
		
	function list_head ($arr) {
		global $tpl;
		global $display;

		$col=0;
		if(!$this->disable_mass_delete) {
			$display->rows[0][$col]='<input type="checkbox" name="all_checker" onclick="check_all(\''.$this->form_name.'\',\'delete[]\')">';
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
	
	function list_calc_limits () {
		$query = '';
		$number_per_page = get_conf(__FILE__,__LINE__,'rows_per_page');
		
		if($this -> limit_start>=$this->num_total) $this -> limit_start=$this->num_total-$number_per_page;
		if(empty($this -> limit_start) || $this -> limit_start<0) $this -> limit_start=0;
		/*if(!$this->global_search) */
		$query.=" LIMIT ".$this -> limit_start.' , '.$number_per_page;
		return $query;
	}
	
	function list_link_base () {
		$this->link_base = $this->file.'?class='.get_class($this);
		if($this->category) $this->link_base.='&amp;data[category]='.$this->category;
		if($this->search) $this->link_base.='&amp;data[search]='.$this->search;
	}
	
	function list_navbar () {
		global $tpl;
		$number_per_page = get_conf(__FILE__,__LINE__,'rows_per_page');
		
		$last_shown=$this -> limit_start+$number_per_page;
		if($last_shown>$this->num_total) $last_shown=$this->num_total;
		
		$first_shown=$this -> limit_start+1;
		if($first_shown>$this->num_total) $first_shown=$this->num_total;
		
		$this->list_link_base();
		
		if($this->num_total>$number_per_page) {
			$prev=$this -> limit_start-$number_per_page;
			$next=$last_shown;
			$first=0;
			$last=$this->num_total-$number_per_page;
			$remaining=$this->num_total-$last_shown;
			
			$link = $this->link_base.'&amp;data[limit_start]='.$prev;
			if($this -> limit_start>0) {
				$link = $this->link_base.'&amp;data[limit_start]='.$first;
				if($this->orderby) $link.='&amp;data[orderby]='.$this->orderby;
				if($this->sort) $link.='&amp;data[sort]='.$this->sort;
				$output = '';
				$output .= '<a href="'.$link.'"><img border=0 src="'.ROOTDIR.'/images/start.png" alt="&lt;&lt;"></a>';
				$output .= '&nbsp;&nbsp;';
				$link = $this->link_base.'&amp;data[limit_start]='.$prev;
				$output .= '<a href="'.$link.'"><img border=0 src="'.ROOTDIR.'/images/back.png" alt="&lt;"></a>';
				$tpl -> assign("navbar_prev", $output);
			}
		}
		
		
		if (is_object($father) && $father!=$this) {
			$father -> number_found += $num_total;
			$num_display = $father -> number_found;
		} else $num_display = $this->num_total;
		
		
		$output = '';
		$output .= '<b>'.$num_display.'</b> '.phr('NUM_RECORDS_FOUND');
		$tpl -> assign("navbar_found_top", $output);
		if($num_display) $tpl -> assign("navbar_found_bottom", $output);
		
		$output = '';
		if($first_shown > 1 || $last_shown<$this->num_total)
			$output .= '('.ucfirst(phr('NOW_SHOWING_RECORDS')).' '.phr('FROM').' '.$first_shown.' '.phr('TO').' '.$last_shown.')';
		$tpl -> assign("navbar_showing", $output);
		
		
		if($this->num_total>$number_per_page) {
			if($remaining>0) {
				$link = $this->link_base.'&amp;data[limit_start]='.$next;
				if($this->orderby) $link.='&amp;data[orderby]='.$this->orderby;
				if($this->sort) $link.='&amp;data[sort]='.$this->sort;
				
				$output = '';
				$output .= '<a href="'.$link.'"><img border=0 src="'.ROOTDIR.'/images/forward.png" alt="&gt;"></a>';
				$output .= '&nbsp;&nbsp;';
				$link = $this->link_base.'&amp;data[limit_start]='.$last;
				$output .= '<a href="'.$link.'"><img border=0 src="'.ROOTDIR.'/images/finish.png" alt="&gt;&gt;"></a>';
				$tpl -> assign("navbar_next", $output);
			}
		}
	}
	
	function list_standard_query_edit () {
		$lang_table = $this->table."_".$_SESSION['language'];
		$query = '';
		
		if(empty($this->search) && isset($this->flag_delete) && $this->flag_delete) $query.=" AND ".$this->table.".deleted='0'";
		
		if(empty($this->orderby)) {
			if (isset($this->default_orderby)) $this->orderby=$this->default_orderby;
			else $this->orderby='name';
			if (isset($this->default_sort)) $this->sort=$this->default_sort;
			else $this->sort='asc';
		}
		
		if($this->orderby) {
			$query.=" ORDER BY `".$this->orderby."`";
			if(strtolower($this->sort)=='asc' || strtolower($this->sort)=='desc') $query.=" ".$this->sort;
		}
		
		return $query;
	}
	
	
	
	function commands($class) {
		global $tpl;
		
		$records_found=false;
		$query="SELECT * FROM `".$this->table."`";
		if($this->db=='common') $res = common_query($query,__FILE__,__LINE__);
		else $res = accounting_query($query,__FILE__,__LINE__);
		if(!$res) return '';

		if(!mysql_num_rows($res)){
			$tmp = ucfirst(phr('ERROR_NONE_FOUND')).".<br>\n";
			$tpl -> assign("search", $tmp);
		} else $records_found=true;

		$allow_new=false;
		$allow_catlist=true;
		
		if ($class=='category') {
			$vat = new vat_rate();
			if($vat -> count_records ()) $allow_new=true;
		} elseif (!$this->disable_new) $allow_new=true;

		if(!isset($_REQUEST['data']['global_search'])) {
			$local_check ='';
			$glob_check = ' checked';
		} elseif(isset($_REQUEST['data']['global_search']) && $_REQUEST['data']['global_search']==1) {
			$local_check ='';
			$glob_check = ' checked';
			$allow_new=false;
			$allow_catlist=false;
		} elseif(isset($_REQUEST['data']['global_search']) && $_REQUEST['data']['global_search']==0) {
			$local_check = ' checked';
			$glob_check = '';
		}
		
		if($allow_catlist && $this -> show_category_list) {
			$cat = new category();
			$tmp = $cat -> show_page_list ($class);
			$tpl -> assign("categories", $tmp);
		}
		
		if($allow_new && method_exists($this,'form')) {
			$tmp = '
	<a href="'.$this->file.'?command=new&amp;class='.$class.'">'.ucphr('INSERT_NEW').'</a><br/>';
			$tpl -> assign("newrecord", $tmp);
		}
		
		$tmp = '
	<form name="search" action="'.$this->file.'">
	<img src="'.ROOTDIR.'/images/find.png" alt="'.ucphr('SEARCH').'">
	<input type="hidden" name="class" value="'.$class.'">
	<input type="hidden" name="data[orderby]" value="'.$this->orderby.'">
	<input type="hidden" name="data[sort]" value="'.$this->sort.'">
	<input type="hidden" name="data[category]" value="'.$_REQUEST['category'].'">
	<input type="text" onChange="document.search.submit()" name="data[search]" size="10" value="'.$this->search.'"><br/>';
	
	$tmp .= '
	<input type="hidden" name="data[global_search]" value="1">';
	
	$tmp .= '
	<br/>
	</form>';
		$tpl -> assign("search", $tmp);

		return 0;
	}

	function commands_horizontal ($class) {
		global $tpl;
		
		if(!get_class($this)=='search') {
			$records_found=false;
			$query="SELECT * FROM `".$this->table."`";
			if($this->db=='common') $res = common_query($query,__FILE__,__LINE__);
			else $res = accounting_query($query,__FILE__,__LINE__);
			if(!$res) return '';

			if(!mysql_num_rows($res)){
				$tmp = ucfirst(phr('ERROR_NONE_FOUND')).".<br>\n";
				$tpl -> assign("search", $tmp);
			} else $records_found=true;
		}

		$allow_new=false;
		$allow_catlist=true;
		
		if ($class=='category') {
			$vat = new vat_rate();
			if($vat -> count_records ()) $allow_new=true;
		} elseif (!$this->disable_new) $allow_new=true;

		if(!isset($_REQUEST['data']['global_search'])) {
			$local_check ='';
			$glob_check = ' checked';
		} elseif(isset($_REQUEST['data']['global_search']) && $_REQUEST['data']['global_search']==1) {
			$local_check ='';
			$glob_check = ' checked';
			$allow_new=false;
			$allow_catlist=false;
		} elseif(isset($_REQUEST['data']['global_search']) && $_REQUEST['data']['global_search']==0) {
			$local_check = ' checked';
			$glob_check = '';
		}
		
		if($allow_new && method_exists($this,'form')) {
			$tmp = '
	<a href="'.$this->file.'?command=new&amp;class='.$class.'">'.ucphr('INSERT_NEW').'</a> ';
			$tpl -> assign("newrecord", $tmp);
		}
		
		$tmp = '
	<form name="search" action="'.$this->file.'">
	<input type="hidden" name="class" value="'.$class.'">
	<input type="text" onChange="document.search.submit()" name="data[search]" size="10" value="'.$this->search.'">
	<input type="image" src="'.ROOTDIR.'/images/find_small.png" alt="'.ucphr('SEARCH').'">';
	
	$tmp .= '
	<input type="hidden" name="data[global_search]" value="1">';
	
	$tmp .= '
	</form>';
		$tpl -> assign("search", $tmp);

		return 0;
	}

	function admin_pre_list() {
		if(isset($_REQUEST['data']['limit_start']) && !empty($_REQUEST['data']['limit_start']))
			$this -> limit_start=$_REQUEST['data']['limit_start'];
		if(isset($_REQUEST['data']['orderby']) && !empty($_REQUEST['data']['orderby']))
			$this -> orderby = $_REQUEST['data']['orderby'];
		if(isset($_REQUEST['data']['sort']) && !empty($_REQUEST['data']['sort']))
			$this -> sort= $_REQUEST['data']['sort'];
		if(isset($_REQUEST['data']['search']) && !empty($_REQUEST['data']['search']))
			$this -> search= $_REQUEST['data']['search'];
		if(isset($_REQUEST['data']['category']) && !empty($_REQUEST['data']['category']))
			$this->category=$_REQUEST['data']['category'];
	}
	
	function admin_list_page ($class) {
		global $tpl;
		$output = '';
	
		$this->admin_pre_list();
		
		if(!empty($this -> search) && (strlen($this -> search)<MIN_SEARCH_LENGTH)) {
			$tmp = ucphr('SEARCH_TERM_TOO_SHORT');
			if(!$this->silent) $tpl -> append ('messages',$tmp);
			unset($this->search);
			unset($_REQUEST['data']['global_search']);
			$this -> commands_horizontal($class);
			return 0;
		} elseif(empty($this -> search)) {
			unset($_REQUEST['data']['global_search']);
		}
		
		$this -> commands_horizontal($class);
		
		if(isset($_REQUEST['data']['global_search']) && $_REQUEST['data']['global_search']) {
			$tpl -> assign ('title',ucphr('SEARCH_RESULTS'));
			
			$this -> search_list ($obj);
		} else {
			$this ->list_table($_REQUEST['data']['category'],$this);
		} 
		
		return 0;
	}
	
	function search_list () {
		global $tpl;
		
		$search= new search;
		$search -> global_search = true;
		//$this ->limit_start=0;
		$search ->orderby=$this->orderby;
		$search ->sort=$this->sort;
		$search ->search=$this->search;
		$search -> list_table();
		
	}
	
	function admin_page ($class,$command,$start_data) {
		global $tpl;
		if(defined('SECURITY_STOP')) $command='access_denied';
		
		switch($command) {
			case 'access_denied':
				if(!$this->silent) {
					$tmp = access_denied_admin();
					$tpl -> append("messages", $tmp);
				}
				break;
			case 'new':
				$tpl -> set_admin_template_file ('standard');

				$obj = new $class;
				$tmp = $obj -> form();
				$tpl -> assign("content", $tmp);
				break;
			case 'insert':
				$obj = new $class;
				if(!$obj -> insert($start_data)) {
					if(method_exists($obj,'post_insert_page')) $obj->post_insert_page($class);
					else $obj -> admin_list_page($class);
				}
				break;
			case 'edit':
				if(!isset($this->templates['edit'])) $this->templates['edit']='menu';
				$tpl -> set_admin_template_file ($this->templates['edit']);
				$obj = new $class($start_data['id']);
				$tmp = $obj -> form();
				$tpl -> assign("content", $tmp);
				
				if(method_exists($obj,'post_edit_page')) $obj->post_edit_page($class);
				break;
			case 'update':
				$obj = new $class($start_data['id']);
				if($err=$obj -> update($start_data)) {
					if(!$this->silent) {
						$tmp = '<span class="error_msg">Error updating: '.$err.'</span><br>';
						$tpl -> append("messages", $tmp);
					}
				}
				if(method_exists($obj,'post_update_page')) $obj->post_update_page($class);
				else $obj -> admin_list_page($class);
				break;
			case 'update_field':
				$obj = new $class($start_data['id']);
				if(method_exists($obj,'update_field')) {
					if($err=$obj -> update_field($start_data['field'])) {
						if(!$this->silent) {
							$tmp = '<span class="error_msg">Error updating: '.$err.'</span><br>';
							$tpl -> append("messages", $tmp);
						}
					}
				}
				$obj -> admin_list_page($class);
				break;
			case "delete":
				if(isset($_GET['deleteconfirm'])){
					$deleteconfirm=$_GET['deleteconfirm'];
				} elseif(isset($_POST['deleteconfirm'])){
					$deleteconfirm=$_POST['deleteconfirm'];
				}
				if($deleteconfirm){
					$tpl -> set_admin_template_file ('menu');
					$delete=$_SESSION["delete"];
					unset($_SESSION["delete"]);
	
					if(is_array($delete)) {
					for (reset ($delete); list ($key, $value) = each ($delete); ) {
						$obj = new $class($value);
						if($err=$obj -> delete($start_data)) {
							if(!$this->silent) {
								$tmp = '<span class="error_msg">Error deleting: '.$err.'</span><br>';
								$tpl -> append("messages", $tmp);
							}
						}

						unset($rate);
					}
					}
					
					if(count($delete)==1) {
						if(method_exists($obj,'post_delete_page')) $obj->post_delete_page($class);
						else $obj -> admin_list_page($class);
					} else {
						$obj = new $class();
						$obj -> admin_list_page($class);
					}
				} else {
					$tpl -> set_admin_template_file ('standard');
					if(isset($_REQUEST['delete'])){
						$delete=$_REQUEST['delete'];
					}
	
					if(is_array($delete) || $delete=='all'){
						if($delete=='all') {
							$query = "SELECT `id` FROM ".$this->table;
							if($this->flag_delete) $query .= " WHERE `deleted`=0";
							if($this->db=='common') $res = common_query($query,__FILE__,__LINE__);
							else $res = accounting_query($query,__FILE__,__LINE__);
							if(!$res) return ERR_MYSQL;
							
							$delete_all = true;
							unset($delete);
							while ($arr = mysql_fetch_array($res)) {
								$delete[]=$arr['id'];
							}
						}
						$tmp = '<div align=center>';
						if($delete_all) $tmp .= ucphr('DELETE_ALL_CONFIRM');
						else $tmp .= ucphr('DELETE_RECORD_CONFIRM');
						$tmp .= ' ('.count($delete).' '.ucphr('RECORDS').')';
						$tmp .= "<br>\n";
						$tmp .= ucphr('ACTION_IS_DEFINITIVE').".<br><br>\n";
							$_SESSION["delete"]=$delete;
							if(!$delete_all) {
								for (reset ($delete); list ($key, $value) = each ($delete); ) {
									$obj = new $class($value);
									if(!$obj->no_name) {
										$description=$obj -> name($_SESSION['language']);
										unset($obj);
										$tmp .= "<LI>".$description."</LI>";
									}
								}
							}
							
							$tmp .= '
		<table>
			<tr>
				<td>
					<form action="'.$this->file.'?" method="GET">
					<input type="hidden" name="class" value="'.$class.'">
					<input type="hidden" name="command" value="delete">
					<input type="hidden" name="deleteconfirm" value="1">';
					
							foreach($start_data as $key => $value)
								$tmp .= '
					<input type="hidden" name="data['.$key.']" value="'.$value.'">';

							$tmp .= '
					<input type="submit" value="'.ucphr('YES').'">
					</form>
				</td>
				<td>
					<form action="'.$this->file.'?" method="GET">
					<input type="hidden" name="class" value="'.$class.'">
					<input type="submit" onclick="history.go(-1);return false;" value="'.ucphr('NO').'">
					</form>
				</td>
			</tr>
		</table>';
							$tmp .= '</div>';
							$tpl -> assign("content", $tmp);
					} else {
						if(!$this->silent) {
							$tmp = '<span class="error_msg">'.ucphr('NO_RECORD_SELECTED').'.</span><br>';
							$tpl -> append("messages", $tmp);
						}
					}

				}
				break;
			case 'stop':
				break;
			default:
				$obj = new $class;
				$obj -> admin_list_page($class);
				break;
		}
		
		if($command!="delete") unset($_SESSION["delete"]);
	}
}

class mgmt_people_type extends object {
	function mgmt_people_type($id=0) {
		$this -> db = 'common';
		$this->table='mgmt_people_types';
		$this->id=$id;
		$this -> fetch_data();
	}
}

class mgmt_type extends object {
	function mgmt_type($id=0) {
		$this -> db = 'common';
		$this->table='mgmt_types';
		$this->id=$id;
		$this -> fetch_data();
	}
}


?>