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
* @copyright	Copyright 2006-2012, Gjergj Sheldija
*/

class vat_rate extends object {
	function vat_rate($id=0) {
		$this -> db = 'common';
		$this->table='vat_rates';
		$this->id=$id;
		$this -> title = ucphr('VAT_RATES');
		$this->file=ROOTDIR.'/admin/admin.php';
		$this -> fields_names = array(	'id'=>ucphr('ID'),
									'name'=>ucphr('NAME'),
									'rate'=>ucphr('VALUE'));
		$this->fields_width=array(	'name'=>'95%',
								'rate'=>'5%');
		$this -> fetch_data();
	}

	function list_search ($search) {
		$query = '';
		
		$table = $this->table;
		
		$query="SELECT
				$table.`id`,
				$table.`name`,
				RPAD('".ucphr('VAT_RATES')."',30,' ') as `table`,
				".TABLE_VAT_RATES." as `table_id`
				FROM `$table`
				WHERE $table.`name` LIKE '%$search%'
				";
		
		return $query;
	}
	
	function list_query_all () {
		$table = $this->table;
		
		$query="SELECT
				$table.`id`,
				$table.`name`,
				CONCAT(ROUND($table.`rate`*100,0),' %') as `rate`
				 FROM `$table`
				";
		
		return $query;
	}
	
	function list_rates() {
		$ret=array();
		$query="SELECT * FROM `".$this->table."`";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return 0;
		
		while($arr=mysql_fetch_array($res)) {
			$ret[]=$arr['id'];
		}
		return $ret;
	}
	
}

?>