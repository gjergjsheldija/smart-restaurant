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

$inizio=microtime();

define('ROOTDIR','..');
require(ROOTDIR."/include/manage/mgmt_funs.php");
require(ROOTDIR."/conf/config.inc.php");
require(ROOTDIR."/conf/config.constants.inc.php");

session_start();
	
header ("Expires: " . gmdate("D, d M Y H:i:s", time()) . " GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

common_set_error_reporting ();

$_SESSION['common_db']=$db_common;
$link = mysql_pconnect ($cfgserver, $cfguser,$cfgpassword) or die (GLOBALMSG_DB_CONNECTION_ERROR);

start_language ();

if(isset($_SESSION['printable']['table_title'])){
	$title=$_SESSION['printable']['table_title'];
}
if(isset($_SESSION['printable']['query'])){
	$query=$_SESSION['printable']['query'];
}

if(!access_allowed(USER_BIT_ACCOUNTING)) $command='access_denied';

switch($command) {
	case 'access_denied':
				echo access_denied_admin();
				break;
	default:
		if($data=pdf_generator($query))
			printable_write_pdf($data,$title);
		else {
			die(GLOBALMSG_RECORD_NONE_FOUND_ERROR.'. <a href="#" onclick="javascript:window.close(); return false">'.ucfirst(phr('GO_BACK')).'</a>');
		}
}

?>
