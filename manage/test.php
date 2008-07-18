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

define('ROOTDIR','..');
require_once(ROOTDIR."/includes.php");
require(ROOTDIR."/conf/config.inc.php");
require(ROOTDIR."/conf/config.constants.inc.php");

session_start();

header('Content-type: text/plain');

$link = mysql_pconnect ($cfgserver, $cfguser,$cfgpassword) or die (GLOBALMSG_DB_CONNECTION_ERROR);

/*
we check at least to have some tables in each db
otherwise we stop execution and report an error
TODO: link to db installation page in the error msg
*/
$tableslist = mysql_list_tables ($db_common,$link);
$numtables = mysql_num_rows ($tableslist);
if($numtables==0) die(GLOBALMSG_DB_NO_TABLES_ERROR);

$_SESSION['common_db']=$db_common;

//if($err=lang_read_all()) die('error reading lang files: '.$err);
lang_read_all();

if(get_conf(__FILE__,__LINE__,"default_language")=="") $conf_language="en";
else $conf_language=get_conf(__FILE__,__LINE__,"default_language");
require(ROOTDIR."/lang/lang_".$conf_language.".php");
$_SESSION['language']=$conf_language;

if($_GET['lang']) $lang=$_GET['lang'];
else {
	reset($_GET);
	list ($lang, $value) = each ($_GET);
}
if(!$lang) $lang='en';

$string=lang_db_to_string($lang);

echo $string;

/*
$filename=ROOTDIR.'/lang/language.xml';

$err=lang_to_file($string,$filename);
if($err) echo '<br>error issued: '.$err;
else echo '<br>file '.$filename.' writed successfully.';
*/


?>