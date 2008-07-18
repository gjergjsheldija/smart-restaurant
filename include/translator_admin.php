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

class lang extends object {
	var $translations;
	var $show_lang;
	var $search;
	var $new_only;
	var $silent;
	var $random_check;

	function find_translations () {
		$res_lang=mysql_list_tables($_SESSION['common_db']);
		while($arr_lang=mysql_fetch_array($res_lang)) {
			if($lang_now=stristr($arr_lang[0],$this->table.'_')) {
				$lang_now= substr($lang_now,-2);
				$lang_array[]=$arr_lang[0];
			}
		}
		$this->translations=$lang_array;
	}

	function list_names($limit_start=0,$number_per_page=5,$devel=false,$orderby='name') {
		$tmp = '';
		$this->show_lang=array_unique($this->show_lang);
		
		$query="SELECT ".$this->table.".id,
		".$this->table.".name\n";
		foreach($this->show_lang as $key => $value) {
			$query.=", ".$this->table."_".$value.".table_name";
		}
		$query .= " FROM ".$this->table."\n";
		foreach($this->show_lang as $key => $value) {
			$query.=" JOIN ".$this->table."_".$value."\n";
		}
		
		$where_done = false;
		foreach($this->show_lang as $key => $value) {
			if(!$where_done) {
				$query .= " WHERE";
				$where_done = true;
			} else $query .= "AND";
			$query.=" ".$this->table.".id"."=".$this->table."_".$value.".table_id\n";
		}
		if(!empty($this->search)) {
			$query.=" AND (".$this->table.".name LIKE '%".$this->search."%'";
			foreach($this->show_lang as $key => $value) {
				$query.=" OR ".$this->table."_".$value.".table_name LIKE '%".$this->search."%'";
			}
			$query .= ")";
		}
		if($this -> new_only) {
			foreach($this->show_lang as $key => $value) {
				if(!$and_done) {
					$query .= " AND (";
					$and_done = true;
				} else $query .= " OR";
				
				$query.=" ".$this->table."_".$value.".table_name = ''\n";
			}
			$query .= ')';
		}
		
		if(!empty($orderby)) $query.=" ORDER BY ".$this->table.".".$orderby;
		
//  echo nl2br($query);
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return 1;

		$num_total=mysql_num_rows($res);

		if($limit_start>=$num_total) $limit_start=$num_total-$number_per_page;
		
		if(empty($limit_start) || $limit_start<0) $limit_start=0;
		$query.=" LIMIT ".$limit_start.' , '.$number_per_page;
		
		if(strstr($this->table,'lang'))
			$tmp .= '<input type="hidden" name="data[limit_start]" value="'.$limit_start.'">'."\n";

		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return 1;

 // echo nl2br($query);
		
		$last_shown=$limit_start+$number_per_page;
		if($last_shown>$num_total) $last_shown=$num_total;
		
		$first_shown=$limit_start+1;
		if($first_shown>$num_total) $first_shown=$num_total;
		
		if(!$this->silent) {
			$tmp .= '<br/>'.$this->table.': <b>'.$num_total.'</b> records found.<br/>';
			if($first_shown > 1 || $last_shown<$num_total)
				$tmp .= 'Now showing records from '.$first_shown.' to '.$last_shown.'.<br/>';
		}
		
		if($num_total>$number_per_page) {
			$prev=$limit_start-$number_per_page;
			$next=$last_shown;
			$remaining=$num_total-$last_shown;
			
			$link = '?data[limit_start]='.$prev;
			if($devel) $link .= '&devel';
			if($this -> new_only) $link .= '&new_only=1';
			if($limit_start>0) $tmp .= '<a href="'.$link.'">&lt;&lt;</a>';
			
			$tmp .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			
			$link = '?data[limit_start]='.$next;
			if($devel) $link .= '&devel';
			if($this -> new_only) $link .= '&new_only=1';
			if($remaining>0) $tmp .= '<a href="'.$link.'">&gt;&gt;</a>';
			$tmp .= "<br/>\n";
		}
//var_dump_table($this -> translations);
		$tmp .= "\n".'<table class="'.css_line_admin(-1).'">'."\n";

		$tmp .= '<thead>'."\n";
		$tmp .= '	<tr>'."\n";
		$tmp .= '		<th>ID</th>'."\n";
		$tmp .= '		<th>Name</th>'."\n";

		foreach($this->show_lang as $key2 => $lang_now) {
			$tmp .= '		<th>'.ucfirst($lang_now).'</th>'."\n";
		}
		$tmp .= '	</tr>'."\n";
		$tmp .= '</thead>'."\n";

		$tmp .= '<tbody>'."\n";

		$this->show_empty_name=true;
		
		$i=0;
		while($arr = mysql_fetch_array ($res)) {
			$cols=0;
			
			$tmp .= '	<tr class="'.css_line_admin($i).'">'."\n";
			$id=$arr['id'];
			$this -> id = $id;

			$tmp .= '		<td>';
			$tmp .= $arr['id'];
			if($devel) $tmp .= '<br/><a href="'.ROOTDIR.'/admin/translator.php?command=delete_value&amp;data[id]='.$arr['id'].'&amp;devel&amp;random_check='.$this -> random_check.'">'.ucphr('DELETE').'</a>';
			$tmp .= '</td>'."\n";
			$tmp .= '		<td>'.$arr['name'].'</td>'."\n";
			$cols++;
			
			//var_dump_table($arr);
			foreach($this->show_lang as $key => $lang_now) {
				$cols++;
				
				$lang_table = $this->table."_".$lang_now;
				
				if($this -> new_only) $lang_name = $this -> name ($lang_now);
				else $lang_name = stripslashes($arr[$cols]);

				$charset = lang_get($lang_now,'CHARSET');
				if(empty($charset)||$charset=='CHARSET') $charset='iso-8859-1';
				$lang_name = html_entity_decode ($lang_name,ENT_QUOTES,$charset);
				
				if(empty($lang_name)) $alert='<table><tr><td bgcolor="#FF0000" width="100" height="20">Missing Translation!</td></tr></table>';
				else $alert='';
				$tmp .= '		<td>'.$alert.'<textarea rows="2" cols="50" name="data['.$lang_table.']['.$id.']">'.$lang_name.'</textarea></td>'."\n";
			}
			
			$tmp .= '	</tr>'."\n";
			$i++;
			if(($i%10)==0) {
				$tmp .= '<tr><td colspan="'.$cols.'" align="center"><input type="submit" value="update all"></td></tr>'."\n";
			}
		}
		$tmp .= '</tbody>
</table>'."\n";
		
		if($num_total>$number_per_page) {
			$prev=$limit_start-$number_per_page;
			$next=$last_shown;
			$remaining=$num_total-$last_shown;
			
			$link = '?data[limit_start]='.$prev;
			if($devel) $link .= '&devel';
			if($this -> new_only) $link .= '&new_only=1';
			if($limit_start>0) $tmp .= '<a href="'.$link.'">&lt;&lt;</a>';
			
			$tmp .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			
			$link = '?data[limit_start]='.$next;
			if($devel) $link .= '&devel';
			if($this -> new_only) $link .= '&new_only=1';
			if($remaining>0) $tmp .= '<a href="'.$link.'">&gt;&gt;</a>';
			$tmp .= "<br/>\n";
		}
		return $tmp;
	}
}

class lang_type extends lang {
	function lang_type($table) {
		$this -> db = 'common';
		$this->table=$GLOBALS['table_prefix'].$table;

		$this -> find_translations();
	}
}

function translator_form ($vars) {
	$tmp = '';
	$tmp .= '<form action="?" method="get" name="displaylang_form">'."\n";
	if($vars['devel']) $tmp .= '<input type="hidden" name="devel" value="1">'."\n";
	$tmp .= '<input type="hidden" name="random_check" value="'.$vars['random_check'].'">'."\n";
	
	$tmp .= '<table>'."\n";
	$tmp .= '<tr>'."\n";
	$tmp .= '<td>'."\n";

	$tmp .= 'Languages: '."\n";
	$tmp .= '<select name="data[lang_left]" onChange="displaylang_form.submit();">';
	
	$langs=list_db_languages();
	sort($langs);
	
	if(!in_array($vars['lang_left'],$langs)) $vars['lang_left'] = reset($langs);
	if(!in_array($vars['lang_right'],$langs)) $vars['lang_right'] = reset($langs);
	
	foreach ($langs as $value) {
		if($vars['lang_left']==$value) $selected=' selected';
		else $selected='';
	
		$tmp .= '<option value="'.$value.'"'.$selected.'>'.$value.'</option>'."\n";
	}
	$tmp .= '</select>';
	
	$tmp .= '<select name="data[lang_right]" onChange="displaylang_form.submit();">';
	foreach ($langs as $value) {
		if($vars['lang_right']==$value) $selected=' selected';
		else $selected='';
	
		$tmp .= '<option value="'.$value.'"'.$selected.'>'.$value.'</option>'."\n";
	}
	$tmp .= '</select>';
	
	$tmp .= '</td>'."\n";
	$tmp .= '<td>'."\n";
	$tmp .= 'Search: '."\n";
	$tmp .= '<input type="text" onChange="displaylang_form.submit();" name="data[search_value]" value="'.$vars['search'].'">';
	
	$tmp .= '</td>'."\n";
	$tmp .= '<td>'."\n";
	if ($vars['new_only']) $checked=' checked';
	else $checked='';
	$tmp .= '<input type="checkbox" onChange="displaylang_form.submit();return true;" name="new_only" value="1"'.$checked.'> Empty only'."\n";
	
	$tmp .= '</td>'."\n";
	$tmp .= '<td>'."\n";
	$tmp .= 'Items per page: '."\n";
	$tmp .= '<input type="text" onChange="displaylang_form.submit();" size="4" name="data[items_per_page]" value="'.$vars['items_per_page'].'">';
	$tmp .= '</td>'."\n";
	$tmp .= '</tr>'."\n";
	$tmp .= '</form>'."\n";
	$tmp .= '</table>'."\n";
	
	$tmp .= '<input type="hidden" name="data[lang][]" value="'.$vars['lang_left'].'">'."\n";
	$tmp .= '<input type="hidden" name="data[lang][]" value="'.$vars['lang_right'].'">'."\n";

	if($vars['devel']) {
		$tmp .= '<form action="?" method="post" name="new_value">'."\n";
		$tmp .= '<input type="hidden" name="devel" value="1">'."\n";
		$tmp .= '<input type="hidden" name="random_check" value="'.$vars['random_check'].'">'."\n";
		$tmp .= '<input type="hidden" name="command" value="new_value">'."\n";
		$tmp .= 'New language value: '."\n";
		$tmp .= '<input type="text" name="data[new_value]">';
		$tmp .= '<input type="submit" value="Add">'."\n";
		$tmp .= '</form>'."\n";
		
		$tmp .= '<form action="?" method="post" name="remove_lang">'."\n";
		$tmp .= '<input type="hidden" name="devel" value="1">'."\n";
		$tmp .= '<input type="hidden" name="random_check" value="'.$vars['random_check'].'">'."\n";
		$tmp .= '<input type="hidden" name="command" value="remove_lang">'."\n";
		$tmp .= 'Remove language: '."\n";
		$tmp .= '<select name="data[remlang]">';
		$langs=list_db_languages();
		for (reset ($langs); list ($key, $value) = each ($langs); ) {
			$tmp .= '<option value="'.$value.'">'.$value.'</option>'."\n";
		}
		$tmp .= '</select>';
		$tmp .= '<input type="submit" value="Remove">'."\n";
		$tmp .= '</form>'."\n";
		$tmp .= '<form action="?" method="post" name="newlang_form">'."\n";
		if($vars['devel']) $tmp .= '<input type="hidden" name="devel" value="1">'."\n";
		if($vars['new_only']) $tmp .= '<input type="hidden" name="new_only" value="1">'."\n";
		$tmp .= '<input type="hidden" name="command" value="new_lang">'."\n";
		$tmp .= '<input type="hidden" name="random_check" value="'.$vars['random_check'].'">'."\n";
		$tmp .= 'Insert a new language<br>(use only if you really want to create a new language set, creates many tables!): <input type="text" name="data[newlang]" maxlength="2" size="2" value="en">'."\n";
		$tmp .= '<input type="submit" value="insert">'."\n";
		$tmp .= '</form>'."\n";
	}
	
	
	$checker_link='checker.php?devel&amp;data[lang][]='.$vars['lang_left'].'&amp;data[lang][]='.$vars['lang_right'];
	if(CONF_TRANSLATE_ALWAYS_CHECK_TABLES && checker_check_only (true)) {
		$tmp .= '<hr>
		<font color="#FF0000">Errors found in the language tables!</font><br />
		<a href="'.$checker_link.'">Check the language tables<br/>
		(Extended check for translators and developers)</a>
		<hr>
		';
	} else {
		$tmp .= '<hr>
		<a href="'.$checker_link.'">Check the language tables before translating.
		Click here to do the translators check!</a>
		<hr>
		';
	}
	
	
	
	$tmp .= '<form action="?" method="post" name="translator_form">'."\n";
	if($vars['devel']) $tmp .= '<input type="hidden" name="devel" value="1">'."\n";
	if($vars['new_only']) $tmp .= '<input type="hidden" name="new_only" value="1">'."\n";
	$tmp .= '<input type="hidden" name="command" value="update">'."\n";
	$tmp .= '<input type="hidden" name="random_check" value="'.$vars['random_check'].'">'."\n";
	
	$lang_obj = new lang_type('lang');
	$lang_obj -> show_lang = array($vars['lang_left'],$vars['lang_right']);
	$lang_obj -> silent = true;
	$lang_obj -> search = 'CHARSET';
	$tmp .= $lang_obj -> list_names(0,1,$vars['devel']);
	$tmp .= '<input type="submit" value="update all">'."\n";
	
	for (reset ($vars['to_check']); list ($key, $value) = each ($vars['to_check']); ) {
		$lang_obj = new lang_type($value);
		$lang_obj -> show_lang = array($vars['lang_left'],$vars['lang_right']);
		$lang_obj -> search = $vars['search'];
		$lang_obj -> new_only = $vars['new_only'];
		$lang_obj -> random_check = $vars['random_check'];
		$tmp .= $lang_obj -> list_names($vars['limit_start'],$vars['items_per_page'],$vars['devel']);
		$tmp .= '<input type="submit" value="update all">'."\n";
	}
	$tmp .= '</form>'."\n";
	return $tmp;
}

function translator_new_language ($input_data,$silent=true) {
	if(strlen($input_data['newlang'])!=2) return 1;

	$lang=strtolower($input_data['newlang']);

	global $to_check_admin;

	// Now we'll build the correct UPDATE query, based on the fields provided
	for (reset ($to_check_admin); list ($key, $local_table) = each ($to_check_admin); ) {
		$table='#prefix#'.$local_table.'_'.$lang;
		$query="CREATE TABLE `".$_SESSION['common_db']."`.`".$table."` (
					`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
					`table_id` int( 11 ) NOT NULL default '0',
					`table_name` text NOT NULL ,
					PRIMARY KEY ( `id` )
					) TYPE = MYISAM";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return mysql_errno();
		
		debug_msg(__FILE__,__LINE__,"created table $table");
		if(!$silent) echo "created table $table<br/>";
		
		
		$query='';
		switch($local_table) {
			case 'lang':
				$query="CREATE INDEX `table_id` ON `".$table."` (table_id)";
				break;
			case 'dishes':
				$query="CREATE INDEX `table_id` ON `".$table."` (table_id)";
				break;
			case 'ingreds':
				$query="CREATE INDEX `table_id` ON `".$table."` (table_id)";
				break;
		}
		
		if(strlen($query)) {
			$res=common_query($query,__FILE__,__LINE__);
			if(!$res) return mysql_errno();
		}
	}

	$langs[]=$lang;
	for (reset ($to_check_admin); list ($key, $local_table) = each ($to_check_admin); ) {
		checker_check_and_correct($local_table,$silent,$langs);
		debug_msg(__FILE__,__LINE__,"checked table $local_table");
		if(!$silent) echo "checked table $local_table<br/>";
	}
	return 0;
}

function translator_remove_language ($input_data) {
	if(strlen($input_data['remlang'])!=2) return 1;

	$lang=strtolower($input_data['remlang']);

	global $to_check_admin;

	for (reset ($to_check_admin); list ($key, $local_table) = each ($to_check_admin); ) {
		$table='#prefix#'.$local_table.'_'.$lang;
		$query="DROP TABLE `".$_SESSION['common_db']."`.`".$table."`";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
		
		debug_msg(__FILE__,__LINE__,"removed table $table");
	}
	return 0;
}

function translator_new_lang_value($new_value) {
	$new_value = trim ($new_value);
	$table_now=$GLOBALS['table_prefix'].'lang';
	$query="SELECT * FROM `".$table_now."` WHERE `name`='".$new_value."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return mysql_errno();
	
	if(mysql_num_rows($res)==0) {
		$lang_obj = new lang_type('lang');
		$transl = $lang_obj -> translations;
		$data=array('name' => $new_value);
	
		$err=$lang_obj -> insert($data);
		if($err) return $err;
		$table_id=$lang_obj->id;
		
		$data=array('table_id' => $table_id);
		for (reset ($transl); list ($key, $lang_now) = each ($transl); ) {
			$lang_obj -> table = $lang_now;
			$query="SELECT * FROM `".$lang_obj -> table."` WHERE `table_id`='".$table_id."'";
			$res2=common_query($query,__FILE__,__LINE__);
			if(!$res2) return mysql_errno();

			if(mysql_num_rows($res2)==0) {
				$lang_obj->silent=true;
				$err=$lang_obj -> insert($data);
				if($err) return $err;
				$lang_obj->silent=false;
			}
		}
	}
	return 0;
}

function translator_delete_lang_value ($id) {
	$query="DELETE FROM `#prefix#lang` WHERE `id`='".$id."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return mysql_errno();
	
	$lang_obj = new lang_type('lang');
	$transl = $lang_obj -> translations;
	foreach ($transl as $table_now) {
		$query="DELETE FROM `".$table_now."` WHERE `table_id`='".$id."'";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return mysql_errno();
	}
	return 0;
}

function translator_translate ($input_data) {
		// Now we'll build the correct UPDATE query, based on the fields provided
		if(is_array($input_data)) {
		for (reset ($input_data); list ($local_table, $table_data) = each ($input_data); ) {
			$lang = substr ($local_table,-2);
			$charset = lang_get($lang,'CHARSET');
			if(empty($charset) || $charset=='CHARSET') $charset='iso-8859-1';
			
			if(is_array($table_data)) {
			for (reset ($table_data); list ($table_id, $new_value) = each ($table_data); ) {
			
				$query="SELECT `table_name` FROM `".$local_table."` WHERE `table_id`='".$table_id."'";
				
				$res=common_query($query,__FILE__,__LINE__);
				if(!$res) return mysql_errno();
				
				$arr=mysql_fetch_array($res);
				$compare_value=$arr['table_name'];
				
				$new_value = htmlentities ($new_value,ENT_QUOTES,$charset);
				
				//$compare_value = mysql_real_escape_string($compare_value);
				//$new_value = mysql_real_escape_string($new_value);
				
				$compare_value = addslashes($compare_value);
				$new_value = addslashes($new_value);

				if($compare_value!=$new_value) {
					$query="UPDATE `".$local_table."` SET ";
					$query.="`table_name`='".$new_value."'";
					$query.=" WHERE `table_id`='$table_id'";
					
					$res=common_query($query,__FILE__,__LINE__);
					if(!$res) return mysql_errno();
					
					if(!mysql_affected_rows())
						$not_found[$local_table][] = $table_id;
				}
			}
			}
		}
		}
	if(is_array($not_found)) {
		//var_dump_table($not_found);
		return -1;
	}

	return 0;
}

?>