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

class category extends object {
	var $temp_lang;

	function category($id=0) {
		$this -> db = 'common';
		$this->table=$GLOBALS['table_prefix'].'categories';
		$this->id=$id;
		$this->flag_delete = true;
		$this->fields_names=array(	'id'=>ucphr('ID'),
								'name'=>ucphr('NAME'),
								'vat_rate'=>ucphr('VAT_RATE'),
								'priority'=>ucphr('PRIORITY'),
								'image'=>ucphr('IMAGE'));
		$this->fields_width=array(	'name'=>'80%',
								'vat_rate'=>'20%');
		$this -> title = ucphr('CATEGORIES');
		$this->file=ROOTDIR.'/admin/admin.php';
		$this -> fetch_data();
	}

}
?>