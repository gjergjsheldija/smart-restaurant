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
	$this->table='conf';
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

		$query="SELECT `name` FROM `".$this->table."` WHERE `id`='".$this->id."'";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return '';
		
		$arr = mysql_fetch_array ($res);
		$name=stripslashes($arr['name']);
		
		$charset ='iso-8859-1';
		$name = html_entity_decode ($name,ENT_QUOTES,$charset);
		
		return $name;
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