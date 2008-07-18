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

$to_check_user=array(
'ingreds',
'dishes',
'categories'
);

$to_check_admin=array(
'conf',
'ingreds',
'dishes',
'categories',
'lang',
'mgmt_people_types',
'mgmt_types'
);

$to_check_translator=array(
'conf',
'lang',
'mgmt_people_types',
'mgmt_types'
);

$tpl -> set_admin_template_file ('standard');

$tmp = head_line('Changelog');
$tpl -> assign("head", $tmp);

$tmp = ucphr('TRANSLATIONS_CHECKER');
$tpl -> assign("title", $tmp);

if(isset($_REQUEST['devel'])) $to_check=$to_check_admin;
else $to_check=$to_check_user;

$tmp = '';
if(!access_allowed(USER_BIT_TRANSLATION)) $command='access_denied';

switch($command) {
	case 'access_denied':
		$tmp = access_denied_admin();
		$tpl -> append ("messages", $tmp);
		break;
	default:
		$lang_array=array();
		for (reset ($to_check); list ($key, $value) = each ($to_check); ) {
			$table=$value;
			
			$query="SHOW TABLES";
			$res_lang=common_query($query,__FILE__,__LINE__);
			while($arr_lang=mysql_fetch_array($res_lang)) {
				// checks if this a language table
				if($lang_now=stristr($arr_lang[0],$GLOBALS['table_prefix'].$table.'_')) {
					$lang_now= substr($lang_now,-2);
					if(isset($start_data['lang']) && in_array($lang_now,$start_data['lang'])) $lang_array[]=$arr_lang[0];
					elseif(!isset($start_data['lang'])) $lang_array[]=$arr_lang[0];
				}
			}
		}
		
		// checks the data and tell us if some error has been found
		$found_error=false;
		foreach ($lang_array as $key => $value) {
			
			$lang_table=$value;
			$table=substr($lang_table,0,-3);
		
			$corrections[$lang_table]=checker_table($lang_table);
		
			if(is_array($corrections[$lang_table]) || $corrections[$lang_table]!=0) {
				$found_error=true;
			}
		}
		
		$tmp .= '
		<div align="center">';
		if($found_error && !isset($_REQUEST['correct'])) {
			$tmp .= '
			<form name="form_correct" action="'.$_SERVER['PHP_SELF'].'" method="POST">
			<input type="hidden" name="correct" value="1">';
			
			if(isset($_REQUEST['devel'])) $tmp .= '
			<input type="hidden" name="devel" value="1">';
			foreach($start_data['lang'] as $value) {
				$tmp .= '
			<input type="hidden" name="data[lang][]" value="'.$value.'">';
			}
			$tmp .= '
			<input type="submit" value="Correct the errors automatically"><br />
			</form>';
		}
		
		if(isset($_REQUEST['devel'])) $tmp .= '
		<a href="'.ROOTDIR.'/admin/translator.php">Go to translators\' page.</a><br/>';
		
		$tmp .= '
		<form name="form_general" action="'.$recheck_url.'" method="POST">';
		if(isset($_REQUEST['devel'])) {
			$tmp .= '
		<input type="hidden" name="devel" value="1">';
		}
		if(isset($start_data['lang'])) foreach($start_data['lang'] as $value) {
			$tmp .= '
		<input type="hidden" name="data[lang][]" value="'.$value.'">';
		}
		$tmp .= '
		<input type="submit" value="Check the tables again"><br />
		</form>
		</div>';
		
		// corrects the errors
		if(isset($_REQUEST['correct'])) {
			foreach ($lang_array as $key => $value) {
				$lang_table=$value;
				$table=substr($lang_table,0,-3);
		
				$tmp .= "
				<br />Correcting table <b>$lang_table</b>... ";
		
				if(is_array($corrections[$lang_table])) {
					$tmp .= checker_corrector($lang_table,$corrections[$lang_table]);
				}
								
				//if(is_array($corrections[$lang_table])) checker_report($lang_table,1);
				if($corrections[$lang_table]==0) $tmp .= "Table is ok";
			}
		} elseif(!isset($_REQUEST['correct'])) {
		// reports the errors
			foreach ($lang_array as $key => $value) {
				$lang_table=$value;
				$table=substr($lang_table,0,-3);
		
				$tmp .= "
				<br />Checking table <b>$lang_table</b>... ";
		
				if(is_array($corrections[$lang_table])) {
					$tmp .= checker_report($lang_table,$corrections[$lang_table]);
				}
				elseif($corrections[$lang_table]==0) $tmp .= "Table is ok";
			}
		}
		
		$tmp .= '
		<center>';
		if($found_error && !isset($_REQUEST['correct'])) {
			$tmp .= '<br />
			<form name="form_correct" action="'.$_SERVER['PHP_SELF'].'" method="POST">
			<input type="hidden" name="correct" value="1">
			';
			if(isset($_REQUEST['devel'])) {
				$tmp .= '<input type="hidden" name="devel" value="1">
				';
			}
			foreach($start_data['lang'] as $value) {
				$tmp .= '
			<input type="hidden" name="data[lang][]" value="'.$value.'">';
			}
			$tmp .= '<input type="submit" value="Correct the errors automatically"><br />
			</form>
			';
		}
		
		$tmp .= '<br />
		<form name="form_general" action="'.$recheck_url.'" method="POST">
		';
		if(isset($_REQUEST['devel'])) {
			$tmp .= '<input type="hidden" name="devel" value="1">
			';
		}
		if(isset($start_data['lang'])) foreach($start_data['lang'] as $value) {
			$tmp .= '
		<input type="hidden" name="data[lang][]" value="'.$value.'">';
		}
		$tmp .= '
		<input type="submit" value="Check the tables again"><br />
		</form>
		';
		break;
}

$tpl -> assign ('content',$tmp);

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
if(CONF_DEBUG_PRINT_PAGE_SIZE) echo $tpl -> print_size();
?>