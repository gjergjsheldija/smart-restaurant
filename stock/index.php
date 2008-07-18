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

$GLOBALS['end_require_time']=microtime();

if(isset($_REQUEST['class'])) $class=$_REQUEST['class'];
else $class='stock_object';

$tmp = head_line('Stock management');
$tpl -> assign("head", $tmp);

$tpl -> set_admin_template_file ('menu');

$accepted_class=false;
switch($class) {
	case 'stock_object':
		if(!access_allowed(USER_BIT_STOCK)) {
			define('SECURITY_STOP',true);
			$command='access_denied';
		}
		$accepted_class=true;
		switch ($command) {
			case 'access_denied':
				break;
			case 'sync_dishes':
				$obj = new $class;
				if($err=$obj -> sync_external(TYPE_DISH)) {
					$tmp = '<span class="error_msg">Error syncronizing: '.$err.'</span><br>';
					$tpl -> append("messages", $tmp);
				}
				break;
			case 'sync_ingredients':
				$obj = new $class;
				if($err=$obj -> sync_external(TYPE_INGREDIENT)) {
					$tmp = '<span class="error_msg">Error syncronizing: '.$err.'</span><br>';
					$tpl -> append("messages", $tmp);
				}
				$class='ingredient';
				break;
			case 'create_from_external':
				$obj = new $class;
				echo "fiksim kot tesh";
				if($err=$obj -> create_from_external($start_data['ref_id'],$start_data['ref_type'])) {
					$tmp = '<span class="error_msg">Error creating: '.$err.'</span><br>';
					$tpl -> append("messages", $tmp);
				}
				if(!$err) {
					if(method_exists($obj,'post_insert_page')) $obj->post_insert_page($class);
					else $obj -> admin_list_page($class);
				} else $obj -> admin_list_page($class);
				
				$command='stop';
				break;
		}
		break;
	case 'stock_movement':
		if(!access_allowed(USER_BIT_STOCK)) $command='access_denied';
		$accepted_class=true;
		break;
	case 'stock_dish':
		if(!access_allowed(USER_BIT_STOCK)) $command='access_denied';
		$accepted_class=true;
		switch ($command) {
			case 'access_denied':
				break;
			case 'insert_ingred_quantities':
				$obj = new $class;
				if($err=$obj -> insert_ingred_quantities($start_data)) {
					$tmp = '<span class="error_msg">Error inserting data: '.$err.'</span><br>';
					$tpl -> append("messages", $tmp);
				}
				break;
		}
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
$license = '<dd>Powered by <a href="http://smartres.sourceforge.net/">Smart Restaurant</a></dd>';

echo $license;
if(CONF_DEBUG_PRINT_PAGE_SIZE) echo $tpl -> print_size();
?>