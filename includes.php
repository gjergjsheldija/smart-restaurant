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
* @author		Gjergj Sheldija <gjergj.sheldija@gmail.com>
* @package		MyHandyRestaurant
* @copyright		Copyright 2003-2005, Fabio De Pascale
* @copyright		Copyright 2006-2009, Gjergj Sheldija
*/
require_once(ROOTDIR."/funs_common.php");

// explicitily called to be before other extended classes
require_once(ROOTDIR."/include/object_class_admin.php");

include_once(ROOTDIR."/include/manage/mgmt_funs_stats.php");
include_once(ROOTDIR."/include/manage/mgmt_funs_other.php");
include_once(ROOTDIR."/include/manage/mgmt_funs_database.php");
include_once(ROOTDIR."/include/manage/mgmt_funs_receipt.php");
include_once(ROOTDIR."/include/manage/mgmt_funs_account.php");
include_once(ROOTDIR."/include/manage/mgmt_funs_stock.php");

include_once(ROOTDIR."/include/xtemplate/xtemplate.class.php");

// includes all the files in include dir
clearstatcache();

$dir_scan=ROOTDIR.'/include';
if ($handle = opendir($dir_scan)) {
	while (false !== ($file = readdir($handle))) {
		if (is_file($dir_scan.'/'.$file) && is_readable($dir_scan.'/'.$file) && strtolower(substr($file,-4))=='.php') {
			require_once ($dir_scan.'/'.$file);
		}
	}
	closedir($handle);
}

/* Stock scanning */
$dir_scan=ROOTDIR.'/include/stock';
if ($handle = opendir($dir_scan)) {
	while (false !== ($file = readdir($handle))) {
		if (is_file($dir_scan.'/'.$file) && is_readable($dir_scan.'/'.$file) && strtolower(substr($file,-4))=='.php') {
			require_once ($dir_scan.'/'.$file);
		}
	}
	closedir($handle);
}


// includes all the printer drivers
$dir_scan=ROOTDIR.'/drivers';
clearstatcache();
if ($handle = opendir($dir_scan)) {
	while (false !== ($file = readdir($handle))) {
		if (is_file($dir_scan.'/'.$file) && is_readable($dir_scan.'/'.$file) && strtolower(substr($file,-4))=='.php') {
			include_once ($dir_scan.'/'.$file);
		}
	}
	closedir($handle);
}

?>
