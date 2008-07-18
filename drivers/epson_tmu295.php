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

// epson dot matrix printer used for bills
function driver_epson_tmu295($msg) {

	$msg = stri_replace ('{init}',chr(0x1B)."@",$msg);
	$msg = stri_replace ('{feed_reverse}',chr(0x1B).'F1',$msg);
	$msg = stri_replace ('{feed_reverse2}',chr(0x1B)."f".chr(0).chr(10),$msg);
	$msg = stri_replace ('{no_paper_print_disabler}',chr(0x1B)."c4".chr(48),$msg);
	//$msg = stri_replace ('{unknown}',chr(0x1B)."D".chr(2).chr(4).chr(20).chr(0),$msg);

	$msg = stri_replace ('{tab_define}','',$msg);

	$msg = stri_replace ('{dashes_row}',"-------------------------------",$msg);
	$msg = stri_replace ('{paper_release}',"\n\n".chr(0x0c).chr(27)."q",$msg);
	$msg = stri_replace ('{height_double}',chr(27).'h1',$msg);
	$msg = stri_replace ('{/height_double}',chr(27).'h0',$msg);
	$msg = stri_replace ('{align_center}',"\n".chr(27).chr(29).'a1',$msg);
	$msg = stri_replace ('{align_left}',"\n".chr(27).chr(29).'a0',$msg);

	return $msg;
}

/*
// wrong data, don't modify it
function driver_epson_bill_old($msg) {

	$msg = stri_replace ('{init}',chr(0x1B)."@",$msg);
	$msg = stri_replace ('{feed_reverse}',chr(0x1B).'F1',$msg);
	$msg = stri_replace ('{feed_reverse2}',chr(0x1B)."f".chr(0).chr(10),$msg);
	$msg = stri_replace ('{no_paper_print_disabler}',chr(0x1B)."c4".chr(48),$msg);
	$msg = stri_replace ('{unknown}',chr(0x1B)."D".chr(2).chr(4).chr(20).chr(0),$msg);

	return $msg;
}
*/
?>
