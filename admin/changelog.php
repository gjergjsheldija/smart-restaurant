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
session_start();
define('ROOTDIR','..');
require_once(ROOTDIR."/includes.php");
require_once(ROOTDIR."/admin/admin_start.php");

if(isset($_REQUEST['devel'])) $devel=true;
else $devel=false;

$output ='';

if($devel) header('Content-type: text/plain');
else {
	header("Content-Language: ".$_SESSION['language']);
	header("Content-type: text/html; charset=".phr('CHARSET'));
}

$tpl -> set_admin_template_file ('standard');

$tmp = head_line('Changelog');
$tpl -> assign("head", $tmp);

switch($command) {
	case 'reset_menu':
		$tmp = show_logo();
		$tpl -> assign("logo", $tmp);
		$tmp = ucphr('ABOUT_MHR');
		$tpl -> assign("title", $tmp);
		$sys = new system;
		if($err = $sys->reset_all_menu_data ()) $tmp = 'ERROR RESETTING MENU';
		else $tmp = 'RESET OK';
		$tpl -> append("messages", $tmp);
		
		break;
	case 'info':
		$tmp = show_logo();
		$tpl -> assign("logo", $tmp);
		$sys = new system;
		$tmp = $sys->info ($dbman);
		$tpl -> append("content", $tmp);
		
		$tmp = ucphr('ABOUT_MHR');
		$tpl -> assign("title", $tmp);
		$sys = new system;
		break;
	default:
		$file='../docs/changelog';
		if(!$devel) {
			$tmp = show_logo();
			$tpl -> assign("logo", $tmp);

			$tmp = ucphr('CHANGELOG');
			$tpl -> assign("title", $tmp);
		}
			
		
		$sys = new system;
		$output =$sys -> changelog($file);
	
		if(!$devel) $output .= '<br/><a href="?devel">HTML</a>';
		
		$tpl -> append("content", $output);
}

if(!$devel) {
	// prints page generation time
	$tmp = generating_time($inizio);
	$tpl -> assign ('generating_time',$tmp);
	
	if($err=$tpl->parse()) return $err; 
	
	$tpl -> clean();
	$output = $tpl->getOutput();
	header("Content-Language: ".$_SESSION['language']);
	header("Content-type: text/html; charset=".phr('CHARSET'));

}
// prints everything to screen
echo $output;
?>
