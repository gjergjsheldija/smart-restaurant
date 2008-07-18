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

class conf {
	var $id;
	var $table;
	var $name;
	var $value;

	function conf($name='') {
	$this->table='#prefix#conf';
		if($name) $this->name=$name;
		$this->get();
	}

	function exists() {
		if(!$this->name) return 0;
		$query="SELECT `name` FROM `".$this->table."` WHERE name='".$this->name."'";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return 0;
		
		return mysql_num_rows($res);
	}

	function get () {
		if(!$this->exists()) return 0;

		$query="SELECT * FROM ".$this->table." WHERE `name`='".$this->name."'";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return 0;
		
		$arr = mysql_fetch_array ($res);
		$this->value=$arr["value"];
		$this->id=$arr['id'];
		return $arr["value"];
	}

	function set () {
		if(!$this->exists()) return 1;

		$query="UPDATE ".$this->table." SET `value`='".$this->value."' WHERE `name`='".$this->name."'";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return mysql_errno();
		
		return 0;
	}

	function set_default () {
		$query="SELECT * FROM ".$this->table;
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
		
		while($arr = mysql_fetch_array ($res)) {
			if($arr['defaultval']=='') continue;
			
			$this->id=$arr['id'];
			$this->name=$arr['name'];
			$this->value=$arr['defaultval'];
			if($err=$this -> set()) return $err;
		}
		return 0;
	}
	
	function name($lang='') {
		if(!$this->exists()) return 0;

/*		if($lang) {
			$lang_table=$this->table."_".$lang;
			$query="SELECT `table_name` FROM `".$lang_table."` WHERE `table_id`='".$this->id."'";
			$res=common_query($query,__FILE__,__LINE__);
			if(!$res) return '';
			
			$arr = mysql_fetch_array ($res);
			$name=stripslashes($arr['table_name']);
			
			$charset = lang_get($lang,'CHARSET');
			if($charset=='CHARSET' || empty($charset)) $charset='iso-8859-1';
			$name = html_entity_decode ($name,ENT_QUOTES,$charset);
			
			return $name;
		}*/

		$query="SELECT `name` FROM `".$this->table."` WHERE `id`='".$this->id."'";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return '';
		
		$arr = mysql_fetch_array ($res);
		$name=stripslashes($arr['name']);
		
		$charset ='iso-8859-1';
		$name = html_entity_decode ($name,ENT_QUOTES,$charset);
		
		return $name;
	}

	function list_table($orderby='') {
		$query="SELECT * FROM ".$this->table;
		if($orderby) $query.=" ORDER BY $orderby";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return 0;
		
		echo '<table class="'.css_line_admin(-1).'">'."\n";

		$i=0;
		while($arr = mysql_fetch_array ($res)) {
			$lang=new conf('default_language');
			$language=$lang->value;
			
			if(isset($_SESSION['language']) && !empty($_SESSION['language'])) $language=$_SESSION['language'];
			
			$this->id=$arr['id'];
			$description=$this->name($language);
			echo '	<tr class="'.css_line_admin($i).'">
		<td>'.$description;
			if($arr['defaultval']!='' && !$arr['bool']) echo '<br />('.ucfirst(lang_get($language,'DEFAULT')).': '.$arr['defaultval'].')';
			elseif($arr['defaultval']!='' && $arr['bool']) {
				if ($arr['defaultval']) $def = 'On';
				else $def = 'Off';
				echo '<br />('.ucfirst(lang_get($language,'DEFAULT')).': '.$def.')';
			}
			echo '</td>
		<td>';
		if($arr['bool']) {
			if($arr['value']){
				$offselected='';
				$onselected=' selected';
			} else {
				$offselected=' selected';
				$onselected='';
			}
			echo '<select name="data['.$arr['name'].']">
			<option value="1"'.$onselected.'>On</option>
			<option value="0"'.$offselected.'>Off</option>
			</select>';
		} elseif($arr['name']=='default_language') {
			
			echo '<select name="data['.$arr['name'].']">'."\n";
			
			$lang_dir=ROOTDIR.'/lang';
			$langs_files = list_languages($lang_dir);
			$langs_db = list_db_languages();
			
			foreach ($langs_db as $key => $value) {
				if(in_array($value, $langs_files)) {
					if($arr['value']==$value) $selected=' selected';
					else $selected='';
				
					echo '<option value="'.$value.'"'.$selected.'>'.$value.'</option>'."\n";
				}
			}
			
			echo '</select>'."\n";
		} elseif($arr['name']=='printing_system') {
			$selected=array();
			$selected['lp'] = '';
			$selected['win'] = '';
			
			$selected[$arr['value']]=' selected';
			
			echo '<select name="data['.$arr['name'].']">'."\n";
			echo '<option value="lp"'.$selected['lp'].'>Lp / Cupsys</option>'."\n";
			echo '<option value="win"'.$selected['win'].'>Win</option>'."\n";
			echo '</select>'."\n";
		} elseif($arr['name']=='country') {
			$country = new country;
			
			echo $country -> list_all_conf ($arr['name'],$arr['value']);
		} elseif($arr['name']=='default_priority') {
			$selected=array();
			for ($i=1;$i<4;$i++) $selected[$i]='';
			$selected[$arr['value']]=' selected';
			
			echo '<select name="data['.$arr['name'].']">'."\n";
			echo '<option value="0">None</option>'."\n";
			for ($i=1;$i<4;$i++) echo '<option value="'.$i.'"'.$selected[$i].'>'.$i.'</option>'."\n";
			echo '</select>'."\n";
		} else {
			echo '<input type="text" name="data['.$arr['name'].']" size="30" value="'.$arr['value'].'">';
		}
		echo '</td>
	</tr>
			';
		$i++;
		}
		echo '</table>'."\n";
	}

	function set_all($input_data) {
		for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
			$this->name=$key;
			$this->value=$value;
			if($this->set()) return 1;
		}
		return 0;
	}
}
?>