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

require(ROOTDIR."/conf/config.inc.php");
require(ROOTDIR."/conf/config.constants.inc.php");

global $header_printed;

if(!$header_printed){
	// session_start();

	header ("Expires: " . gmdate("D, d M Y H:i:s", time()) . " GMT");
	header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

	common_set_error_reporting ();

	if(isset($_SESSION['section']) && $_SESSION['section']!="admin"){
		unset_session_vars();
		$_SESSION['section']="admin";
	}

	if(!$link = @mysql_pconnect ($cfgserver, $cfguser,$cfgpassword)) {
		header('Location: '.ROOTDIR.'/install.php');
		die ('Error connecting to the db');
	}
	
	$_SESSION['common_db']=$db_common;
	
	check_db_status();

	start_language ();

	$tpl = new template;

	$dbman = new db_manager ('', '', '', $link);
	if($dbman->upgrade_available()) {
		if(CONF_FORCE_UPGRADE && !in_array(basename($_SERVER['SCRIPT_NAME']),$allowed_not_upgraded)) {
			header('Location: '.ROOTDIR.'/admin/upgrade.php?command=none&data[redirected]=1');
			echo 'Upgrades available.';
			die();
		}
		$tmp = '<font color="red">'.ucphr('UPGRADES_AVAILABLE').'<br/><a href="'.ROOTDIR.'/admin/upgrade.php?command=none&data[redirected]=1">'.ucphr('CLICK_HERE_TO_UPGRADE').'</a></font><br/>'."\n";
		$tpl -> append("messages", $tmp);
	}
	
	unset($_SESSION['catprinted']);

	if(isset($_REQUEST['command'])) $command=$_REQUEST['command'];
	else $command='none';

	if(isset($_REQUEST['id'])){
		$start_id=$_REQUEST['id'];
		$_SESSION['id']=$start_id;
	} elseif(isset($_SESSION['id'])){
		$start_id=$_SESSION['id'];
	}

	if(isset($_REQUEST['data'])){
		$start_data=$_REQUEST['data'];
	} else $start_data = array();
	
	if(!$dont_display_menu) {
		$menu = new menu();
		$tmp = $menu -> main ();
		$tpl -> assign("menu", $tmp);
	}
	$header_printed=2;
}

?>
