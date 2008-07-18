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

// list of all the available commands
// in alphabetical order
function driver_manufacturer_model($msg) {

	$msg = stri_replace ('{align_center}','',$msg);
	$msg = stri_replace ('{align_left}','',$msg);
	$msg = stri_replace ('{barcode_code39}','',$msg);
	$msg = stri_replace ('{/barcode_code39}','',$msg);
	$msg = stri_replace ('{dashes_row}','',$msg);
	$msg = stri_replace ('{feed_reverse}','',$msg);
	$msg = stri_replace ('{feed_reverse2}','',$msg);
	$msg = stri_replace ('{init}','',$msg);
	$msg = stri_replace ('{height_double}','',$msg);
	$msg = stri_replace ('{/height_double}','',$msg);
	$msg = stri_replace ('{highlight}','',$msg);
	$msg = stri_replace ('{/highlight}','',$msg);
	$msg = stri_replace ('{no_paper_print_disabler}','',$msg);
	$msg = stri_replace ('{page_cut}','',$msg);
	$msg = stri_replace ('{paper_release}','',$msg);
	$msg = stri_replace ('{size_double}','',$msg);
	$msg = stri_replace ('{/size_double}','',$msg);
	$msg = stri_replace ('{size_normal}','',$msg);
	$msg = stri_replace ('{size_triple}','',$msg);
	$msg = stri_replace ('{/size_triple}','',$msg);
	$msg = stri_replace ('{tab_define}','',$msg);
	$msg = stri_replace ('{unknown}','',$msg);

	return $msg;
}

?>
