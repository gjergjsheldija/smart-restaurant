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

class printer extends object {
	function printer($id=0) {
		$this -> db = 'common';
		$this->table='dests';
		$this->id=$id;
		$this->fields_names=array(	'id'=>ucphr('ID'),
								'name'=>ucphr('NAME'),
								'dest'=>ucphr('QUEUE'),
								'driver'=>ucphr('DRIVER'),
								'bill'=>ucphr('BILL'),
								'invoice'=>ucphr('INVOICE'),
								'receipt'=>ucphr('RECEIPT'),
								'template'=>ucphr('TEMPLATE'));
		$this -> title = ucphr('PRINTERS');
		$this->file=ROOTDIR.'/admin/admin.php';
		$this->allow_single_update = array ('bill','invoice','receipt');
		$this->flag_delete = true;
		$this->fields_boolean=array('bill','invoice','receipt');
		$this->fields_width=array('name'=>'100%');
		$this -> fetch_data();
	}

	function list_search ($search) {
		$query = '';
		
		$table = $this->table;
		
		$query="SELECT
				$table.`id`,
				$table.`name`,
				RPAD('".ucphr('PRINTERS')."',30,' ') as `table`,
				".TABLE_PRINTERS." as `table_id`
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
				$table.`dest`,
				$table.`driver`,
				IF($table.`bill`='0','".ucphr('NO')."','".ucphr('YES')."') as `bill`,
				IF($table.`invoice`='0','".ucphr('NO')."','".ucphr('YES')."') as `invoice`,
				IF($table.`receipt`='0','".ucphr('NO')."','".ucphr('YES')."') as `receipt`,
				$table.`template`
				 FROM `$table`
				 WHERE `deleted`='0'
				";
		
		return $query;
	}
	
	function check_values($input_data){

		$msg="";
		if($input_data['name']=="") {
			$msg=ucfirst(phr('CHECK_NAME'));
		}

		if($input_data['template']=="") {
			$msg=ucfirst(phr('CHECK_TEMPLATE'));
		}
		
		if($msg){
			echo "<script language=\"javascript\">
				window.alert(\"".$msg."\");
				window.history.go(-1);
			</script>\n";
			return 2;
		}

	if(!$input_data['bill'])
		$input_data['bill']=0;

	if(!$input_data['invoice'])
		$input_data['invoice']=0;

	if(!$input_data['receipt'])
		$input_data['receipt']=0;


		return $input_data;
	}


}

?>