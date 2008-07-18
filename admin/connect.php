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

// if(function_exists('apd_set_pprof_trace')) apd_set_pprof_trace();

$inizio=microtime();
$dont_display_menu=true;
session_start();
define('ROOTDIR','..');
require_once(ROOTDIR."/includes.php");
require(ROOTDIR."/admin/admin_start.php");

$tpl -> set_admin_template_file ('standard');

switch($command) {
	case 'disconnect':
		$user = new user ($_SESSION['userid']);
		$user->disconnect();
		$tmp = access_connect_form();
		$tpl -> assign("content", $tmp);
		break;
	case 'connect':
		$user = new user ($_SESSION['userid']);
		$err = $user -> connect ();
		if (!$err) {
			$tmp = 'ok<br>';
			$tpl -> append("messages", $tmp);
			$tmp = ucphr('CONNECT');
			$tpl -> assign("title", $tmp);
			if(isset($_REQUEST['url']) && !empty($_REQUEST['url'])) {
				$tmp = redirect_timed($_REQUEST['url'],0);
				$tpl -> append("scripts", $tmp);
			}
		}
		else {
			$tmp = 'error: '.error_get($err).'<br>';
			$tpl -> append("messages", $tmp);
		}
		
		break;
	default:
		$tmp = access_connect_form();
		$tpl -> assign("content", $tmp);
		break;
}

$_SESSION['common_db']=$db_common;

$tmp = head_line('Connection');
$tpl -> assign("head", $tmp);
$tmp = show_logo();
$tpl -> assign("logo", $tmp);
$tmp = ucphr('CONNECT');
$tpl -> assign("title", $tmp);

$menu = new menu();
$tmp = $menu -> main ();
$tpl -> assign("menu", $tmp);
$tmp = ucphr('CONNECT');
$tpl -> assign("title", $tmp);

// prints page generation time
$tmp = generating_time($inizio);
$tpl -> assign ('generating_time',$tmp);

if($err=$tpl->parse()) return $err; 

$tpl -> clean();
$output = $tpl->getOutput();

header("Content-Language: ".$_SESSION['language']);
header("Content-type: text/html; charset=".phr('CHARSET'));

 //$tpl ->list_vars();

// prints everything to screen
echo $output;
$license = <<<EOT
	Copyright &#169; 2003-2008 Gjergj Sheldija	
EOT;

echo $license;
if(CONF_DEBUG_PRINT_PAGE_SIZE) echo $tpl -> print_size();
?>
