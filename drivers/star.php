<?php
/**
* My Handy Restaurant - Star printer driver
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

function driver_star($msg) {

	$msg = stri_replace ("é", chr(176), $msg);
	$msg = stri_replace ("è", chr(177), $msg);
	$msg = stri_replace ("à", chr(207), $msg);
	$msg = stri_replace ("ù", chr(192), $msg);
	$msg = stri_replace ("ò", chr(187), $msg);
	$msg = stri_replace ("ì", chr(182), $msg);

	$msg = stri_replace ('{dashes_row}',"-------------------------------",$msg);
	$msg = stri_replace ('{height_double}',chr(27).'i10',$msg);
	$msg = stri_replace ('{/height_double}',chr(27).'i00',$msg);
	$msg = stri_replace ('{size_triple}',chr(27).'i22',$msg);
	$msg = stri_replace ('{/size_triple}',chr(27).'i00',$msg);
	$msg = stri_replace ('{size_double}',chr(27).'i11',$msg);
	$msg = stri_replace ('{/size_double}',chr(27).'i00',$msg);
	$msg = stri_replace ('{size_normal}',chr(27).'i00',$msg);
	//$msg = stri_replace ('{align_center}',"\n".chr(27).chr(29)."a1",$msg);
	$msg = stri_replace ('{align_left}',"\n".chr(27).chr(29)."a0",$msg);
	$msg = stri_replace ('{highlight}',chr(27).'4',$msg);
	$msg = stri_replace ('{/highlight}',chr(27).'5',$msg);
	$msg = stri_replace ('{paper_release}','{page_cut}',$msg);
	$msg = stri_replace ('{page_cut}',"\n".chr(27).'d2',$msg);
	$msg = stri_replace ('{barcode_code39}',chr(27)."b422".chr(100),$msg);
	$msg = stri_replace ('{/barcode_code39}',chr(30),$msg);

	return $msg;
}

?>
