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
* @copyright	Copyright 2006-2009, Gjergj Sheldija
*/

require(ROOTDIR."/conf/config.inc.php");
require(ROOTDIR."/conf/config.constants.inc.php");

global $header_printed;

if(!$header_printed){
	session_start();

	header ("Expires: " . gmdate("D, d M Y H:i:s", time()) . " GMT");
	header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

	common_set_error_reporting ();
	
	if($_SESSION['section']!="manage"){
		unset_session_vars();
		$_SESSION['section']="manage";
	}

	$link = mysql_pconnect ($cfgserver, $cfguser,$cfgpassword) or die (GLOBALMSG_DB_CONNECTION_ERROR);

	/*
	we check at least to have some tables in each db
	otherwise we stop execution and report an error
	TODO: link to db installation page in the error msg
	*/
	$tableslist = mysql_list_tables ($db_common,$link);
	$numtables = mysql_num_rows ($tableslist);
	if($numtables==0) die(GLOBALMSG_DB_NO_TABLES_ERROR);

	$_SESSION['common_db']=$db_common;

	start_language ();

	if(!common_allowed_ip($_SERVER['REMOTE_ADDR'])) {
		$error_msg = common_header('IP address not authorized');
		$error_msg .= 'IP <b>'.$_SERVER['REMOTE_ADDR'].'</b> is not authorized.<br/>'."\n";
		$error_msg .= 'IP <b>'.sprintf("%u",ip2long($_SERVER['REMOTE_ADDR'])).'</b> is not authorized.'."\n";
		
		$error_msg .= common_bottom();
		die($error_msg);
	}

	if($res_loc=check_output_files()) {
			$error_msg = common_header('Output files not writeable');
			$error_msg .=  navbar_empty();

			switch($res_loc) {
				case 1: $err='error file not writeable'; break;
				case 2: $err='error dir not writeable'; break;
				case 3: $err='debug file not writeable'; break;
				case 4: $err='debug dir not writeable'; break;
			}

			$error_msg .= GLOBALMSG_CONFIG_OUTPUT_FILES_NOT_WRITEABLE.'<br><br>(err '.$res_loc.': '.$err.')<br>'."\n";
			$error_msg .= GLOBALMSG_CONFIG_SYSTEM.'<br>'."\n";
			$error_msg .= common_bottom();
			die($error_msg);
	}
	unset($res_loc);
	
	// unsets all the waiters' _SESSION vars
	unset($_SESSION['catprinted']);

	
	if(isset($_REQUEST['mgmt_db_number'])){
		$_SESSION['common_db']=$_REQUEST['mgmt_db_number'];
	} elseif(!isset($_SESSION['common_db'])) {
		$_SESSION['common_db']=common_find_first_db();
	}


	/*
	we almost always use this command var, so we get it here
	to make it available to other functions whithout other hassle
	*/
	if(isset($_REQUEST['command'])){
		$command=$_REQUEST['command'];
	} else {
		$command='none';
	}

	if(isset($_REQUEST['data'])){
		$start_data=$_REQUEST['data'];
	}
	
	header("Content-Language: ".$_SESSION['language']);
	header("Content-type: text/html; charset=".phr('CHARSET'));

	$tpl = new template;

	$tmp = head_line('Management section');
	$tpl -> assign("head", $tmp);

	$menu = new menu();
	$tmp = $menu -> main ();
	$tpl -> append("scripts", $tmp);
	
	$header_printed=2;
}

?>
