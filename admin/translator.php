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
'lang',
'conf',
'mgmt_people_types',
'mgmt_types'
);

$vars['to_check']=$to_check_translator;

$tmp = head_line(ucphr('TRANSLATIONS'));
$tpl -> assign("head", $tmp);

$tpl -> set_admin_template_file ('standard');

$tmp = ucphr('TRANSLATIONS');
$tpl -> assign("title", $tmp);

// random number-based check to be sure that no modifcation is done when reloading the page
$vars['page_reloaded']=true;
if($_REQUEST['random_check']==$_SESSION['random_check']) {
	$vars['page_reloaded']=false;
}
unset($_SESSION['random_check']);
$vars['random_check']=rand(0,1000000);
$_SESSION['random_check']=$vars['random_check'];
// end random number-based check

if(!empty($start_data['lang_left'])) $vars['lang_left']=$start_data['lang_left'];
elseif(!empty($_SESSION['lang_left'])) $vars['lang_left']=$_SESSION['lang_left'];
else $vars['lang_left']='en';

if(!empty($start_data['lang_right'])) $vars['lang_right']=$start_data['lang_right'];
elseif(!empty($_SESSION['lang_right'])) $vars['lang_right']=$_SESSION['lang_right'];
else $vars['lang_right']='en';

if(isset($start_data['search_value'])) $vars['search']=mysql_escape_string($start_data['search_value']);
elseif(!empty($_SESSION['search_value'])) $vars['search']=$_SESSION['search_value'];

if(!empty($start_data['limit_start'])) $vars['limit_start']=$start_data['limit_start'];

if(isset($start_data['items_per_page'])) $vars['items_per_page']=$start_data['items_per_page'];
elseif(!empty($_SESSION['items_per_page'])) $vars['items_per_page']=$_SESSION['items_per_page'];

if(isset($_REQUEST['devel'])) $vars['devel']=true;
else $vars['devel']=false;

if(isset($_REQUEST['new_only']) && $_REQUEST['new_only']) $vars['new_only']=1;
else $vars['new_only']=0;

if($vars['items_per_page']<1) $vars['items_per_page']=25;

$_SESSION['lang_left']=$vars['lang_left'];
$_SESSION['lang_right']=$vars['lang_right'];
$_SESSION['search_value']=$vars['search'];
$_SESSION['items_per_page']=$vars['items_per_page'];

unset($start_data['lang_left']);
unset($start_data['lang_right']);
unset($start_data['search_value']);
unset($start_data['limit_start']);
unset($start_data['items_per_page']);

if(!access_allowed(USER_BIT_TRANSLATION)) $command='access_denied';

switch($command) {
	case 'access_denied':
		$tmp = access_denied_admin();
		$tpl -> append ("messages", $tmp);
		break;
	case 'update':
		if(is_array($start_data) && !$vars['page_reloaded']) {
			$err=translator_translate ($start_data);
			if($err==-1) $tmp = '<font color="#FF0000">Some data has not been updated, because not existant in the corresponding language table.<br>
				Please use the language table checker to correct the errors</font><hr>';
			elseif($err) $tmp = '<font color="#FF0000">Error updating language tables.</font><hr>';
			else $tmp = '<font color="#FF0000">Language tables updated.</font><hr>';
			$tpl -> append ("messages", $tmp);
		}
		$tmp = '<div align="center">'.translator_form($vars).'</div>';
		$tpl -> assign ('content',$tmp);
		break;
	case 'new_lang':
		if(is_array($start_data) && !$vars['page_reloaded']) {
			$err=translator_new_language ($start_data);
			if($err==1050) $tmp = '<font color="#FF0000">Language already exists.</font><hr>';
			elseif($err) $tmp = '<font color="#FF0000">Error creating language tables.</font><hr>';
			else $tmp = '<font color="#FF0000">Language tables created.</font><hr>';
			$tpl -> append ("messages", $tmp);
		}
		
		$tmp = '<div align="center">'.translator_form($vars).'</div>';
		$tpl -> assign ('content',$tmp);
		break;
	case 'remove_lang':
		if(is_array($start_data) && !$vars['page_reloaded']) {
			$err=translator_remove_language ($start_data);
			if($err) $tmp = '<font color="#FF0000">Error removing language tables.</font><hr>';
			else $tmp = '<font color="#FF0000">Language tables removed.</font><hr>';
			$tpl -> append ("messages", $tmp);

		}
		
		$tmp = '<div align="center">'.translator_form($vars).'</div>';
		$tpl -> assign ('content',$tmp);
		break;
	case 'new_value':
		if(is_array($start_data) && !$vars['page_reloaded']) {
			$err=translator_new_lang_value($start_data['new_value']);
		}
		$tmp = '<div align="center">'.translator_form($vars).'</div>';
		$tpl -> assign ('content',$tmp);
		break;
	case 'delete_value':
		if(is_array($start_data) && !$vars['page_reloaded']) {
			if($err=translator_delete_lang_value($start_data['id'])) $tmp='ERROR: Not deleted';
			else $tmp = 'Deleted';
			$tpl -> append ('messages',$tmp);
		}
		$tmp = '<div align="center">'.translator_form($vars).'</div>';
		$tpl -> assign ('content',$tmp);
		break;
	default:
		$tmp = '<div align="center">'.translator_form($vars).'</div>';
		$tpl -> assign ('content',$tmp);
		break;
}
// prints page generation time
$tmp = generating_time($inizio);
$tpl -> assign ('generating_time',$tmp);

header("Content-Language: ".$_SESSION['language']);
header("Content-type: text/html; charset=".phr('CHARSET'));

if($err=$tpl->parse()) die('error parsing template');
$tpl -> clean();

echo $tpl->getOutput();
if(CONF_DEBUG_PRINT_PAGE_SIZE) echo $tpl -> print_size();
?>