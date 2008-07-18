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

class dish_ingredient extends object {
	function dish_ingredient ($id=0) {
		$this -> db = 'common';
		$this->table=$GLOBALS['table_prefix'].'dishes_ingredients';
		$this->id=$id;
		$this -> fetch_data();
	}
	
	function find ($dish_id,$ingred_id) {
		$query = "SELECT `id` FROM `".$this->table."`
				WHERE `dish`='".$dish_id."'
				AND `ingredient`='".$ingred_id."'
				LIMIT 1";
		
		$res = common_query($query,__FILE__,__LINE__);
		if(!$res) return 0;
		
		if($arr=mysql_fetch_assoc ($res)) return $arr['id'];
		return 0;
	}
}

?>