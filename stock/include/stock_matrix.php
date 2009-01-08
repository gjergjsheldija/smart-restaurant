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

class stock_matrix extends object {
	var $objects;
	var $dishes;
	var $matrix;
	var $size;
	
	function calculate_ingredient_quantities () {
		$this->objects = $this->create_objects_vector('obj_id');
		$this->dishes = $this->create_objects_vector('dish_id');
		$this->size = max(count($this->objects),count($this->dishes));
		$this->matrix = $this->create_ingredient_use_matrix();
		$this->samples = $this->create_ingredient_samples_vector();
		
		$sol=matrix::solve($this->matrix,$this->samples);
		
		echo var_dump_table($this->objects);
		echo var_dump_table($this->dishes);
		matrix::show_matrix($this->matrix);
		echo var_dump_table($this->samples);
		echo 'solution: '.var_dump_table($sol);
	
		return 0;
	}

	function create_objects_vector ($var) {
		$query = "SELECT DISTINCT `$var` as elem FROM #prefix#stock_movements";
		$res = common_query($query,__FILE__,__LINE__);
		if(!$res) return 0;
		
		$i=0;
		while($arr = mysql_fetch_array ($res)) {
			if(!$arr['elem']) continue;
			
			$objects[$arr['elem']]=$i;
			$i++;
		}
		
		return $objects;
	}
	
	function create_ingredient_use_matrix () {
		if(!is_array($this->objects)) return 0;
		if(!is_array($this->dishes)) return 0;
		$matrix = array ();
		
		$size=$this->size;
		
		for($i=0;$i<$size;$i++) {
			for($j=0;$j<$size;$j++) {
				$matrix [$i][$j]=0;
			}
		}
		
		$query = "SELECT `obj_id`,`dish_id`,`dish_quantity` FROM #prefix#stock_movements";
		$res = common_query($query,__FILE__,__LINE__);
		if(!$res) return 0;
	
		while($arr = mysql_fetch_array ($res)) {
			$row = $this->objects[$arr['obj_id']];
			$col = $this->dishes[$arr['dish_id']];
			
			$matrix [$row][$col] = $matrix [$row][$col] + $arr['dish_quantity'];
		}
		return $matrix;
	}
	
	function create_ingredient_samples_vector () {
		if(!is_array($this->objects)) return 0;
		
		foreach($this->objects as $key=>$row_number) {
			$query = "SELECT `timestamp`,`quantity` FROM #prefix#stock_samples WHERE `obj_id`='".$key."' ORDER BY `timestamp` DESC LIMIT 1";
			$res = common_query($query,__FILE__,__LINE__);
			if(!$res) return 0;
			if($arr = mysql_fetch_array ($res)) $quantity_end = $arr['quantity'];
			else $quantity_end = 0;
			
			$query = "SELECT `timestamp`,`quantity` FROM #prefix#stock_samples WHERE `obj_id`='".$key."' ORDER BY `timestamp` ASC LIMIT 1";
			$res = common_query($query,__FILE__,__LINE__);
			if(!$res) return 0;
			if($arr = mysql_fetch_array ($res)) $quantity_start = $arr['quantity'];
			else $quantity_start = 0;
			
			$samples[$row_number]=$quantity_start-$quantity_end;
		}
		
		return $samples;
	}
	
	function save_ingredient_quantities () {
		
		return 0;
	}
}

?>