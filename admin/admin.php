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
require("./admin_start.php");

$GLOBALS['end_require_time']=microtime();

if(isset($_REQUEST['class'])) $class=$_REQUEST['class'];
else $class='';


$tmp = head_line('Seksioni i Administrimit');
$tpl -> assign("head", $tmp);

$tpl -> set_admin_template_file ('menu');

$accepted_class=false;
switch($class) {
	case 'search':
		$accepted_class=true;
		break;
	case 'category':
		if(!access_allowed(USER_BIT_MENU)) $command='access_denied';
		$accepted_class=true;
		break;
	case 'autocalc':
		if(!access_allowed(USER_BIT_MENU)) $command='access_denied';
		$accepted_class=true;
		break;
/*	case 'accounting_database':
		if(!access_allowed(USER_BIT_CONFIG)) $command='access_denied';
		$accepted_class=true;
		break;*/
	case 'dish':
		if(!access_allowed(USER_BIT_MENU)) $command='access_denied';
		if($start_data['category']) {
			$cat=new category($start_data['category']);
			$cat_name=$cat->name($_SESSION['language']);
			unset($cat);
			if(!empty($cat_name)) $tmp .= ' ('.ucfirst($cat_name).')';
		}
		$accepted_class=true;
		break;
	case 'ingredient':
		if(!access_allowed(USER_BIT_MENU)) $command='access_denied';
		if($start_data['category']) {
			$cat=new category($start_data['category']);
			$cat_name=$cat->name($_SESSION['language']);
			unset($cat);
			if(!empty($cat_name)) $tmp .= ' ('.ucfirst($cat_name).')';
		}
		$accepted_class=true;
		break;
	case 'printer':
		if(!access_allowed(USER_BIT_CONFIG)) $command='access_denied';

		switch($command) {
			case 'access_denied':
				break;
			case 'test_page':
				$err=print_test_page();
				
				if(!$err) {
					$tmp = '<span class="error_msg">'.ucphr('PRINTER_TEST_SENT').'</span>';
					$tpl -> append("messages", $tmp);
				}
				break;
			default:
				$tmp = '
		<form action="?" method="POST">
		<input type="hidden" name="command" value="test_page">
		<input type="hidden" name="class" value="'.$class.'">
		<input type="submit" value="'.ucphr('PRINTERS_TEST_SEND').'">
		</form>';
				$tpl -> append("list", $tmp);
				break;
		}
		$accepted_class=true;
		break;
	case 'table':
		if(!access_allowed(USER_BIT_MENU)) $command='access_denied';
		$accepted_class=true;
		break;
	case 'user':
		if(!access_allowed(USER_BIT_USERS)) $command='access_denied';
		$accepted_class=true;
		break;
	case 'vat_rate':
		if(!access_allowed(USER_BIT_MENU)) $command='access_denied';
		$accepted_class=true;
		break;	
	case 'currencies':
		if(!access_allowed(USER_BIT_MENU)) $command='access_denied';
		$accepted_class=true;
		break;
}

if($accepted_class) {
	$obj = new $class;
	$tpl -> assign("title", $obj->title);
	$obj = new $class;
	$obj -> admin_page($class,$command,$start_data);
}

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