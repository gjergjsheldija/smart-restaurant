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

class stock_ingredient_quantity extends object {
	function stock_ingredient_quantity ($id=0) {
		$this -> db = 'common';
		$this->table=$GLOBALS['table_prefix'].'stock_ingredient_quantities';
		$this->id=$id;
		$this->fields_names=array(	'id'=>ucphr('ID'));
		$this -> title = ucphr('INGREDIENTS');
		$this->file=ROOTDIR.'/stock/index.php';
		$this->fields_width=array(	'name'=>'80%');
		
		$this -> disable_mass_delete = true;
		$this -> fetch_data();
	}
	
	function find ($obj_id,$dish_id) {
		$query = "SELECT `id` FROM `".$this->table."`
				WHERE `obj_id`='".$obj_id."'
				AND `dish_id`='".$dish_id."'";
		
		if($this->db=='common') $res = common_query($query,__FILE__,__LINE__);
		else $res = accounting_query($query,__FILE__,__LINE__);
		if(!$res) return 0;
		
		if($arr=mysql_fetch_assoc ($res)) return $arr['id'];
		return 0;
	}
	
	function get_all () {
		$this->fetch_data();
		
		$query = "SELECT `quantity` FROM `#prefix#stock_ingredient_samples`
				WHERE `obj_id`='".$this->data['obj_id']."'
				AND `dish_id`='".$this->data['dish_id']."'";
		
		if($this->db=='common') $res = common_query($query,__FILE__,__LINE__);
		else $res = accounting_query($query,__FILE__,__LINE__);
		if(!$res) return 0;
		
		while($arr=mysql_fetch_assoc ($res)) {
			$out[] = $arr['quantity'];
		}
		
		return $out;
	}
	
	function average ($arr) {
		$num = count($arr);
		$sum = 0;
		foreach($arr as $val) $sum = $sum+$val;
		
		$avg = $sum/$num;
		return $avg;
	}
	
	function recalc () {
		$arr=$this->get_all();
		$input_data=$this->data;
		unset($input_data['timestamp']);
		
		$input_data['quantity']=$this->average($arr);
		
		if($ret=$this->update($input_data)) return $ret;
		return 0;
	}
}
?>