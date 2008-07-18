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

// custom s'print-S thermal printer
// 24 cols (52 mm paper roll)
function driver_custom_sprints($msg) {

	$msg = stri_replace ('{paper_release}','{page_cut}',$msg);
	$msg = stri_replace ('{size_triple}','{size_double}',$msg);
	$msg = stri_replace ('{/size_triple}','{/size_double}',$msg);

	$msg = stri_replace ('{init}',"\n",$msg);
	$msg = stri_replace ('{height_double}',"\n".chr(0x02),$msg);
	$msg = stri_replace ('{/height_double}',"\n".chr(0x04),$msg);
	$msg = stri_replace ('{size_double}',"\n".chr(0x03),$msg);
	$msg = stri_replace ('{/size_double}',"\n".chr(0x04),$msg);
	$msg = stri_replace ('{page_cut}',"\n--CUT_PAGE--\n",$msg);
	$msg = stri_replace ('{dashes_row}',"------------------------",$msg);
	$msg = stri_replace ('{barcode_code39}',chr(0x1B)."N".chr(0x1B).'cC'.chr(0x50).chr(0x3C).chr(0x14).chr(0x06)."SPRINT",$msg);
	$msg = stri_replace ('{/barcode_code39}',"\n",$msg);

	return $msg;
}

?>