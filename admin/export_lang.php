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
require(ROOTDIR."/admin/admin_start.php");

$tmp = head_line('Administration section');
$tpl -> assign("head", $tmp);

$tpl -> set_admin_template_file ('standard');

if(!access_allowed(USER_BIT_TRANSLATION)) $command='access_denied';

switch($command) {
	case 'access_denied':
		$tmp = access_denied_admin();
		$tpl -> append ("messages", $tmp);
		// prints page generation time
		$tmp = generating_time($inizio);
		$tpl -> assign ('generating_time',$tmp);
		
		if($err=$tpl->parse()) die('error parsing template');
		$tpl -> clean();
		
		echo $tpl->getOutput();
		if(CONF_DEBUG_PRINT_PAGE_SIZE) echo $tpl -> print_size();
		break;
	case 'export':
		if(!empty($_REQUEST['language'])) $lang_local=$_REQUEST['language'];
	
		if(empty($lang_local)) $lang_local='en';
	
		$drop=true;
		if(!empty($_REQUEST['mysqldump_format'])) $mysqldump_format=true;
	
		$dbman = new db_manager ('', '', '', $link);
		$show_tables=array('categories','conf','dishes','ingreds','lang','mgmt_people_types','mgmt_types','system');
		$tables_struct_only = array ('categories','dishes','ingreds');
		
		$tmp = '';
		
		$dbman -> mhr_tables_only = true;
		if($lang_local=='all' && $_REQUEST['output']!='xml') {
			$res_lang=mysql_list_tables($_SESSION['common_db']);
			if(mysql_num_rows($res_lang)) {
				while($arr_lang=mysql_fetch_array($res_lang)) {
					if($lang_now=stristr($arr_lang[0],$GLOBALS['table_prefix'].'lang_')) {
						$lang_now= substr($lang_now,-2);
						$lang_export=$lang_now;
						$tmp .= $dbman -> db_to_sql ($db_common,$drop,$mysqldump_format,$show_tables,$lang_export,$tables_struct_only);
					}
				}
			}
		} elseif($lang_local=='all' && ($_REQUEST['output']=='xml' || $_REQUEST['output']=='xmlfile')) {
			$res_lang=mysql_list_tables($_SESSION['common_db']);
			if(mysql_num_rows($res_lang)) {
				while($arr_lang=mysql_fetch_array($res_lang)) {
					if($lang_now=stristr($arr_lang[0],$GLOBALS['table_prefix'].'lang_')) {
						$lang_now= substr($lang_now,-2);
						$lang_export=$lang_now;
						$tmp.=lang_db_to_string($lang_now);
					}
				}
			}
		} elseif($_REQUEST['output']=='xml' || $_REQUEST['output']=='xmlfile') {
			$tmp=lang_db_to_string($lang_local);
		} elseif($_REQUEST['output']!='xml') {
			$lang_export=$lang_local;
			$tmp = $dbman -> db_to_sql ($db_common,$drop,$mysqldump_format,$show_tables,$lang_export,$tables_struct_only);
		}
		
		if($_REQUEST['output']=='file'){
			// Send binary filetype HTTP header
			header ('Content-Type: application/octet-stream');
			// Send content-length HTTP header
			header ('Content-Length: '.strlen($tmp));
			// Send content-disposition with save file name HTTP header
			//header('Content-Disposition: attachment; filename="mhr_language_'.$lang_local.'.xml"');
			header('Content-Disposition: attachment; filename="mhr_language_'.$lang_local.'.sql"');
			echo $tmp;
			
		} elseif($_REQUEST['output']=='xmlfile'){
			// Send binary filetype HTTP header
			header ('Content-Type: application/octet-stream');
			// Send content-length HTTP header
			header ('Content-Length: '.strlen($tmp));
			// Send content-disposition with save file name HTTP header
			//header('Content-Disposition: attachment; filename="mhr_language_'.$lang_local.'.xml"');
			header('Content-Disposition: attachment; filename="lang_'.$lang_local.'.xml"');
			
			echo $tmp;
		} elseif ($_REQUEST['output']=='xml') {
			header('Content-type: text/plain');
			echo $tmp;
		} elseif ($_GET['output']=='text') {
			header('Content-type: text/plain');
			echo $tmp;
		} else {
			header("Content-Language: ".$_SESSION['language']);
			header("Content-type: text/html; charset=".phr('CHARSET'));
			
	
			$tmp = htmlentities($tmp);
			$tmp = nl2br($tmp);
			$tpl -> assign ('content',$tmp);
			// prints page generation time
			$tmp = generating_time($inizio);
			$tpl -> assign ('generating_time',$tmp);
			
			if($err=$tpl->parse()) echo('error parsing template'); 
			
			$tpl -> clean();
			echo $tpl -> getOutput();
		}
		break;
	default:
		$tmp = '
<form action="../admin/export_lang.php" method="GET">
	<input type="hidden" name="command" value="export">
<table>
	<tr>
	<td>'.ucphr('LANGUAGE').':</td>
	<td>
		<select name="language">';
		
		$res_lang=mysql_list_tables($_SESSION['common_db']);
		if(mysql_num_rows($res_lang)) {
			$tmp.= '
			<option value="all" selected>'.ucphr('ALL').'</option>';
			while($arr_lang=mysql_fetch_array($res_lang)) {
				if($lang_now=stristr($arr_lang[0],$GLOBALS['table_prefix'].'lang_')) {
					$lang_now= substr($lang_now,-2);
					$tmp .= '
			<option value="'.$lang_now.'">'.$lang_now.'</option>';
				}
			}
		}
		$tmp .= '
		</select>
	</td>
	</tr>
	<tr>
	<td>'.ucphr('OUTPUT_TYPE').':</td>
	<td>
		<select name="output">';
			//<option value="html" selected>'.ucphr('HTML').'</option>
		$tmp .= '
			<option value="file">'.ucphr('FILE').'</option>
			<option value="xml">'.ucphr('XML').' ('.ucphr('STRUCTURE_ONLY').')</option>
			<option value="text" selected>'.ucphr('TEXT').'</option>
			<option value="xmlfile">'.ucphr('XML').' - '.phr('FILE').'</option>
		</select>
	</td>
	</tr>
	<tr>
	<td colspan="2"><input type="checkbox" name="mysqldump_format" checked="true"> '.ucphr('MYSQLDUMP_FORMAT').'</td>
	</tr>
	<tr>
	<td colspan="2" align="center"><input type="submit"></td>
	</tr>
</table>
</form>';
	$tpl -> assign ('content',$tmp);

	// prints page generation time
	$tmp = generating_time($inizio);
	$tpl -> assign ('generating_time',$tmp);
	
	if($err=$tpl->parse()) die('error parsing template');
	
	header("Content-Language: ".$_SESSION['language']);
	header("Content-type: text/html; charset=".phr('CHARSET'));
	
	$tpl -> clean();
	echo $tpl->getOutput();
	if(CONF_DEBUG_PRINT_PAGE_SIZE) echo $tpl -> print_size();
	
	// $tpl ->list_vars();
}
?>