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
session_start();

define('ROOTDIR','..');
require_once(ROOTDIR."/includes.php");
require(ROOTDIR."/admin/admin_start.php");

$tmp = head_line(ucphr('RESTORE'));
$tpl -> assign("head", $tmp);

$tpl -> set_admin_template_file ('standard');

if(ini_get("magic_quotes_runtime")) { ini_set("magic_quotes_runtime","0"); }
if(ini_get("magic_quotes_gpc")) { ini_set("magic_quotes_gpc", "0"); } 

if(isset($_REQUEST['devel'])) $vars['devel']=true;
else $vars['devel']=false;

if(!access_allowed(USER_BIT_CONFIG)) $command='access_denied';

set_time_limit(120);

switch($command) {
	case 'access_denied':
		$tmp = access_denied_admin();
		$tpl -> append ("messages", $tmp);
		break;
	case 'resume':
		$simulate_only = $_SESSION['restore_sql']['simulate_only'];
		$dbman = new db_manager ('', '', '', $link);
		$err=$dbman->upgrade_resume();
		$output = htmlentities($output);
		$output = nl2br($output);
		if($simulate_only)
			$output='<center>'.ucphr('SIMULATION_ACTIVE').'<hr></center>'.$output;
			
		if(!$err) {
			$output='<center><font color="#FF0000">'.ucphr('RESTORE_OK').'</font><hr></center>'.$output;
			$output.='<br/><center><hr><font color="#FF0000">'.ucphr('RESTORE_OK').'</font>';
			$output .= '<br/><a href="'.ROOTDIR.'/admin/restore.php">'.ucphr('BACK_TO_MAIN')."</a>\n";
		} elseif ($err==ERR_SQL_CONTINUING) {
			$output .= "
<script>window.parent.document.location.href='".basename($_SERVER['SCRIPT_NAME'])."?command=resume';</script>
location: ".basename($_SERVER['SCRIPT_NAME'])."?command=resume<br>\n";
		} else {
			$output .= 'Error '.$err.' raised<br>';
			$output .= '<br/><a href="'.ROOTDIR.'/admin/restore.php">'.ucphr('BACK_TO_MAIN')."</a>\n";
		}
		$tpl -> assign ('content',$output);
		break;
	case 'restore':
		$dbman = new db_manager ('', '', '', $link);
		
		$verbosity=$_REQUEST['verbosity'];
		if(!empty($_REQUEST['simulate'])) $simulate=true;
		else $simulate=false;
		
		if(isset($_REQUEST['db_destination']) && !empty($_REQUEST['db_destination']))
			$dbman -> db_destination=$_REQUEST['db_destination'];
		
		$err=0;
		$restored=false;
		if(is_array($_FILES['userfile']) && !$_FILES['userfile']['error'] && is_file($file=$_FILES['userfile']['tmp_name'])) {
			$file=$_FILES['userfile']['tmp_name'];
			if(is_readable($file)) {
				$err=$dbman->upgrade_from_file($file,$verbosity,$simulate);
				$restored=true;
			}
		} elseif(!empty($_REQUEST['string'])) {
			$string=$_REQUEST['string'];
			$restored=true;
			$err=$dbman->upgrade_from_string($string,$verbosity,$simulate);
		}
		
		$output = htmlentities($output);
		$output = nl2br($output);
		if($simulate)
			$output='<center>'.ucphr('SIMULATION_ACTIVE').'<hr></center>'.$output;
			
		if(!$err && $restored) {
			$output='<center><font color="#FF0000">'.ucphr('RESTORE_OK').'</font><hr></center>'.$output;
			$output.='<br/><center><hr><font color="#FF0000">'.ucphr('RESTORE_OK').'</font>';
		} elseif ($err==ERR_SQL_CONTINUING) {
			$output .= "
<script>window.parent.document.location.href='".basename($_SERVER['SCRIPT_NAME'])."?command=resume';</script>
location: ".basename($_SERVER['SCRIPT_NAME'])."?command=resume<br>\n";
		}
		$output .= '<br/><a href="'.ROOTDIR.'/admin/restore.php?command=none">'.ucphr('BACK_TO_MAIN')."</a>\n";
		$tpl -> assign ('content',$output);
		break;
	default:
		$dbman = new db_manager ('', '', '', $link);
		
		$tmp = $dbman -> restore_form($vars);
		$tpl -> assign ('content',$tmp);
}

// prints page generation time
$tmp = generating_time($inizio);
$tpl -> assign ('generating_time',$tmp);

if($err=$tpl->parse()) die('error parsing template');
$tpl -> clean();

header("Content-Language: ".$_SESSION['language']);
header("Content-type: text/html; charset=".phr('CHARSET'));

echo $tpl->getOutput();
if(CONF_DEBUG_PRINT_PAGE_SIZE) echo $tpl -> print_size();
?>