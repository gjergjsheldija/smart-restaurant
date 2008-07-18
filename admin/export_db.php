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
require(ROOTDIR."/admin_start.php");

$tmp = head_line('Administration section');
$tpl -> assign("head", $tmp);

$tpl -> set_admin_template_file ('standard');

if(!access_allowed(USER_BIT_CONFIG)) $command='access_denied';

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
		if(!empty($_GET['db'])) $db=$_GET['db'];
		
		if(empty($db)) $db=$_SESSION['common_db'];
		
		$dbman = new db_manager ('', '', '', $link);
		
		if(!empty($_GET['drop'])) $drop=true;
		if(!empty($_GET['mysqldump_format'])) $mysqldump_format=true;
		if(!empty($_GET['mhr_tables_only'])) $dbman -> mhr_tables_only=true;
		else $dbman -> mhr_tables_only=false;
		
		if($_GET['output']=='file'){
			$tmp = $dbman -> db_to_sql ($db,$drop,$mysqldump_format);
			
			// Send binary filetype HTTP header
			header ('Content-Type: application/octet-stream');
			// Send content-length HTTP header
			header ('Content-Length: '.strlen($tmp));
			// Send content-disposition with save file name HTTP header
			header('Content-Disposition: attachment; filename="mhr_backup_'.$db.'.sql"');
			echo $tmp;
		} elseif ($_GET['output']=='text') {
			header('Content-type: text/plain');
			
			$tmp = $dbman -> db_to_sql ($db,$drop,$mysqldump_format);
			echo $tmp;
		} elseif ($_GET['output']=='xml') {
			header('Content-type: text/plain');
			$tmp=$dbman->dbstruct_to_xml($db);
			echo $tmp;
		} else {
			header("Content-Language: ".$_SESSION['language']);
			header("Content-type: text/html; charset=".phr('CHARSET'));

			$tmp = '';
			$tmp = $dbman -> db_to_sql ($db,$drop,$mysqldump_format);
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
		$tmp = ucphr('DATABASE');
		$tpl -> assign("title", $tmp);

		$tmp = '
<form action="'.ROOTDIR.'/admin/export_db.php" method="GET">
	<input type="hidden" name="command" value="export">
<table>
	<tr>
	<td>'.ucphr('DATABASE').':</td>
	<td>
		<select name="db">
		<option value="'.$_SESSION['common_db'].'" selected>'.ucfirst(phr('MAIN')).': '.$_SESSION['common_db'].'</option>';
		
		$table=$GLOBALS['table_prefix'].'accounting_dbs';
		$query="SELECT * FROM `$table`";
		$res_accounting_dbs = mysql_db_query ($_SESSION['common_db'],$query);
		if($errno=mysql_errno()) {
			$msg="Error in ".__FUNCTION__." - ";
			$msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
			$msg.='query: '.$query."\n";
			echo nl2br($msg)."\n";
			error_msg(__FILE__,__LINE__,$msg);
		}
		while($arr_accounting_dbs=mysql_fetch_array($res_accounting_dbs)) {
			$tmp .= '<option value="'.$arr_accounting_dbs['db'].'">'.ucphr('ACCOUNTING').': '.$arr_accounting_dbs['db'].'</option>';
		}
		
		$tmp .= '</select>
	</td>
	</tr>
	<tr>
	<td>'.ucphr('OUTPUT_TYPE').':</td>
	<td>
		<select name="output">';
			// <option value="html" selected>'.ucphr('HTML').'</option>
		$tmp .= '
			<option value="file">'.ucphr('FILE').'</option>
			<option value="xml">'.ucphr('XML').' ('.ucphr('STRUCTURE_ONLY').')</option>
			<option value="text" selected>'.ucphr('TEXT').'</option>
		</select>
	</td>
	</tr>
	<tr>
	<td colspan="2"><input type="checkbox" name="drop" checked="true"> '.ucphr('DROP_TABLES').'</td>
	</tr>
	<tr>
	<td colspan="2"><input type="checkbox" name="mysqldump_format" checked="true"> '.ucphr('MYSQLDUMP_FORMAT').'</td>
	</tr>
	<tr>
	<td colspan="2"><input type="checkbox" name="mhr_tables_only" checked="true"> '.ucphr('MHR_TABLES_ONLY').'</td>
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