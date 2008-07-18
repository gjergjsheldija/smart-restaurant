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

/*
we check at least to have some tables in each db
otherwise we stop execution and report an error
TODO: link to db installation page in the error msg
*/

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
	case 'compare':
		$parser = new xml_parser;
		$dbman = new db_manager ('', '', '', $link);
		
		$tmp='';
		$tmp .= "#\n";
		$tmp .= "# My Handy Restaurant\n";
		$tmp .= "# SQL upgrade code generator\n";
		$tmp .= "#\n";
		$tmp .= "\n";
		
		for($i=0;$i<2;$i++) {
			if($i==0) {
				$base_db=$_GET['base_common'];
				$target_db=$_GET['target_common'];
				$database_type='common';
			} elseif($i==1) {
				$base_db=$_GET['base_account'];
				$target_db=$_GET['target_account'];
				$database_type='account';
			}
			
			if(!empty($_GET['mysqldump_format'])) $mysqldump_format=true;
		
			$tmp .= "\n";
			$tmp .= "#\n";
			$tmp .= "# Do not modify the following line\n";
			$tmp .= "# Database_type: ".$database_type."\n";
			$tmp .= "#\n";
			$tmp .= "\n";
			
			$tmp .= "\n";
			$tmp .= "#\n";
			$tmp .= "# Compare report tree\n";
			$tmp .= "#\n";
			$tmp .= "\n";
			$cmp_arr =compare_to_array ($base_db,$target_db,$link);
			$tmp .= printa($cmp_arr,true);
			
			$tmp .= "\n";
			$tmp .= "#\n";
			$tmp .= "# Upgrade SQL code\n";
			$tmp .= "#\n";
			$tmp .= "\n";
			$tmp .= correct_db_to_sql($base_db,$cmp_arr,$link)."\n";
			$show_tables=array('conf','countries','lang','mgmt_people_types','mgmt_types','system');
			$drop=true;
			$dbman -> mhr_tables_only = true;
			$tmp .= $dbman -> db_to_sql ($base_db,$drop,$mysqldump_format,$show_tables);
			
			if($_GET['show_dbs']) {
				$tmp .= "\n";
				$tmp .= "#\n";
				$tmp .= "# Database $base_db description\n";
				$tmp .= "#\n";
				$tmp .= "\n";
				$xml = $dbman -> dbstruct_to_xml ($base_db);
				$base_arr=xml_to_array ($xml,$link);
				//$tmp .= printa($base_arr);
				
				$tmp .= "\n";
				$tmp .= "#\n";
				$tmp .= "# Database $target_db description\n";
				$tmp .= "#\n";
				$tmp .= "\n";
				$xml = $dbman -> dbstruct_to_xml ($target_db);
				$target_arr=xml_to_array ($xml,$link);
				//$tmp .= printa($target_arr);
			}
		}
		
		if($_GET['output']=='file'){
			// Send binary filetype HTTP header
			header ('Content-Type: application/octet-stream');
			// Send content-length HTTP header
			header ('Content-Length: '.strlen($tmp));
			// Send content-disposition with save file name HTTP header
			header('Content-Disposition: attachment; filename="mhr_backup_'.$target_db.'.sql"');
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
<div align="center">
<form action="'.ROOTDIR.'/admin/compare_db.php" method="GET">
	<input type="hidden" name="command" value="compare">
<table>
	<tr>
	<td colspan="4" align="center"><b>'.ucphr('DATABASE_COMMON').'</b></td>
	</tr>
	<tr>
	<td>'.ucphr('DATABASE_FROM').':</td>
	<td>
		<select name="base_common">';
		$tmp .= list_dbs_select();
		$tmp .= '
		</select>
	</td>
	<td>'.ucphr('DATABASE_TO').':</td>
	<td>
		<select name="target_common">';
		$tmp .= list_dbs_select();
		$tmp .= '
		</select>
	</td>
	</tr>
	<tr><TD>&nbsp;</TD></tr>
	<tr>
	<td colspan="4" align="center"><b>'.ucphr('ACCOUNTING_DATABASE').'</b></td>
	</tr>
	<tr>
	<td>'.ucphr('DATABASE_FROM').':</td>
	<td>
		<select name="base_account">';
		$tmp .= list_dbs_select();
		$tmp .= '
		</select>
	</td>
	<td>'.ucphr('DATABASE_TO').':</td>
	<td>
		<select name="target_account">';
		$tmp .= list_dbs_select();
		$tmp .= '
		</select>
	</td>
	</tr>
	<tr>
	<td colspan="2" align="right">'.ucphr('OUTPUT_TYPE').':</td>
	<td colspan="2">
		<select name="output">
			<option value="html" selected>'.ucphr('HTML').'</option>
			<option value="file">'.ucphr('FILE').'</option>
			<option value="text">'.ucphr('TEXT').'</option>
		</select>
	</td>
	</tr>
	<tr>
	<td colspan="4" align="center"><input type="checkbox" name="mysqldump_format"> '.ucphr('MYSQLDUMP_FORMAT').'</td>
	</tr>
	<tr>
	<td colspan="4" align="center"><input type="submit"></td>
	</tr>
</table>
</form>
</div>';
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





function list_dbs_select () {
	$tmp = '';
	$res_dbs = mysql_list_dbs();
	if($errno=mysql_errno()) {
		$msg="Error in ".__FUNCTION__." - ";
		$msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
		$msg.='query: '.$query."\n";
		echo nl2br($msg)."\n";
		error_msg(__FILE__,__LINE__,$msg);
	}
	while($arr_dbs=mysql_fetch_array($res_dbs)) {
		$tmp .= '<option value="'.$arr_dbs[0].'">'.$arr_dbs[0].'</option>';
	}
	return $tmp;
}


function table_add ($query) {
	return $query.';';
}

function table_drop ($table) {
	$query = 'DROP TABLE `'.$table.'`;';
	return $query;
}

function field_add ($table,$string) {
	$query = 'ALTER TABLE `'.$table.'` ADD ';
	$query.=$string.';';

	return $query;
}

function field_alter ($table,$field,$string) {
	$query = 'ALTER TABLE `'.$table.'` CHANGE `'.$field.'` ';
	$query.=$string.';';
	return $query;
}

function field_index ($table,$field) {
	$query = 'ALTER TABLE `'.$table.'` ADD INDEX (`'.$field.'`);';
	return $query;
}

function field_drop ($table,$field) {
	$query = 'ALTER TABLE `'.$table.'` DROP `'.$field.'`;';
	return $query;
}

function compare_to_array ($base_db,$target_db,$link) {
	$parser = new xml_parser;
	$dbman = new db_manager ('', '', '', $link);

	$xml_base=$dbman->dbstruct_to_xml($base_db);
	$xml_target=$dbman->dbstruct_to_xml($target_db);

	$cmp=$dbman -> xml_compare($xml_base,$xml_target);
	$cmp_xml = $dbman->arr_to_xml ($cmp);
	
	//printa($cmp_xml);
	$cmp_tree = $parser -> xml_to_tree($cmp_xml,0);
	
	$newarr=$parser->even_remover ($cmp_tree);
	
	$out=$newarr['MyHandyRestaurant']['data'];
	
	return $out;
}

function correct_db_to_sql($base_db,$cmp_array,$link) {
	
	$out='';
	
	$parser = new xml_parser;
	$dbman = new db_manager ('', '', '', $link);
	
	$xml=$dbman->dbstruct_to_xml($base_db);
	$db_base_arr = xml_to_array($xml,$link);

	if(is_array($cmp_array['add']['tables'])) {
	for (reset ($cmp_array['add']['tables']); list ($tkey, $tvalue) = each ($cmp_array['add']['tables']); ) {
		if ($cmp_array['add']['tables'][$tkey]['add']) {
			$query=$db_base_arr['tables'][$tkey]['string'];
			$out .= table_add ($query)."\n";
		}
		if (is_array($cmp_array['add']['tables'][$tkey]['fields'])) {
		for (reset ($cmp_array['add']['tables'][$tkey]['fields']); list ($fkey, $fvalue) = each ($cmp_array['add']['tables'][$tkey]['fields']); ) {
			if ($cmp_array['add']['tables'][$tkey]['fields'][$fkey]['add']) {
				$query=$db_base_arr['tables'][$tkey]['fields'][$fkey]['string'];
				$out .= field_add ($tkey,$query)."\n";
			}
		}
		}
	}
	}

	if(is_array($cmp_array['alter']['tables'])) {
	for (reset ($cmp_array['alter']['tables']); list ($tkey, $tvalue) = each ($cmp_array['alter']['tables']); ) {
		if (is_array($cmp_array['alter']['tables'][$tkey]['fields'])) {
		for (reset ($cmp_array['alter']['tables'][$tkey]['fields']); list ($fkey, $fvalue) = each ($cmp_array['alter']['tables'][$tkey]['fields']); ) {
			if ($cmp_array['alter']['tables'][$tkey]['fields'][$fkey]['alter']) {
				$query=$db_base_arr['tables'][$tkey]['fields'][$fkey]['string'];
				$out .= field_alter ($tkey,$fkey,$query)."\n";
			}
			if ($cmp_array['alter']['tables'][$tkey]['fields'][$fkey]['index']) {
				$out .= field_index ($tkey,$fkey)."\n";
			}
		}
		}
	}
	}

	if(is_array($cmp_array['remove']['tables'])) {
	for (reset ($cmp_array['remove']['tables']); list ($tkey, $tvalue) = each ($cmp_array['remove']['tables']); ) {
		if ($cmp_array['remove']['tables'][$tkey]['remove']) {
			$query=$db_base_arr['tables'][$tkey]['string'];
			$out .= '# '.table_drop ($tkey)."\n";
		}
		if (is_array($cmp_array['remove']['tables'][$tkey]['fields'])) {
		for (reset ($cmp_array['remove']['tables'][$tkey]['fields']); list ($fkey, $fvalue) = each ($cmp_array['remove']['tables'][$tkey]['fields']); ) {
			if ($cmp_array['remove']['tables'][$tkey]['fields'][$fkey]['remove']) {
				$out .= '# '.field_drop ($tkey,$fkey)."\n";
			}
		}
		}
	}
	}

	return $out;
}

function xml_to_array ($xml,$link) {
	$dbman = new db_manager ('', '', '', $link);
	$parser = new xml_parser;
	$tree = $parser -> xml_to_tree($xml,0);
	$db_arr=$dbman -> tree_to_array ($tree);
	return $db_arr;
}

?>