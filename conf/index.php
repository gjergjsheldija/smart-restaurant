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
$dont_get_session_sourceid=true;

session_start();
define('ROOTDIR','..');


require(ROOTDIR."/conf/config.inc.php");
require_once(ROOTDIR."/conf/config.constants.inc.php");

require_once(ROOTDIR."/includes.php");

common_set_error_reporting ();

$link = mysql_pconnect ($cfgserver, $cfguser,$cfgpassword) or die (GLOBALMSG_DB_CONNECTION_ERROR);

$_SESSION['common_db']=$db_common;

check_db_status();

start_language ();

$dbman = new db_manager ('', '', '', $link);
if(!in_array(basename($_SERVER['SCRIPT_NAME']),$allowed_not_upgraded) && $dbman->upgrade_available()) {
	header('Location: '.ROOTDIR.'/admin/upgrade.php?command=none&data[redirected]=1');
	echo 'Upgrades available.';
	die();
}

header("Content-Language: ".$_SESSION['language']);
header("Content-type: text/html; charset=".phr('CHARSET'));

$config = new conf;
$config->name='default_language';
$config->get();
if(!$config->value) $conf_language="en";
else $conf_language=$config->value;

include(ROOTDIR."/lang/lang_".$conf_language.".php");

$jsurl=ROOTDIR."/generic.js";
$title='Smart Restaurant - Configuration';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo phr('CHARSET'); ?>">
	<title><?php echo $title; ?></title>
	<script type="text/javascript" language="JavaScript" src="<?php echo CONF_JS_URL; ?>"></script>
	<link rel="stylesheet" href="<?php echo CONF_CSS_URL; ?>" type="text/css">
	<meta http-equiv="Cache-Control" content="no-cache">
	<meta http-equiv="Expires" content="0">
	
	<!-- Using a linked stylesheet -->
	<link rel="STYLESHEET" type="text/css" href="../coolmenu.css">
	<script type="text/javascript" language="JavaScript1.2" src="../coolmenus4.js">
	</script>
	</head>
	
	<body class=mgmt_body>
<?php
	$menu = new menu();
	echo $menu -> main ();
?>
	<table><TR><TD height="20">&nbsp;</TD></TR></table>
	<center>
<?php

if(isset($_REQUEST['command'])) $command=$_REQUEST['command'];
else $command='none';

if(!access_allowed(USER_BIT_CONFIG)) $command='access_denied';

switch($command) {
	case 'access_denied':
		echo access_denied_admin();
		break;
	default:
		if(isset($_REQUEST['data']) && $_REQUEST['data']){
			$data=$_REQUEST['data'];
			if($config->set_all($data))
				echo '<font color="#FF0000">Error updating configuration table.</font><hr>';
			else
				echo '<font color="#FF0000">Configuration table updated.</font><hr>';
		}
		
		if(isset($_REQUEST['set_default']) && $_REQUEST['set_default']==1){
			$config->set_default();
		}
		
		echo '
			<form action="index.php" method="post" name="default">
			<input type="hidden" name="set_default" value="1">
			<input type="submit" value="'.ucfirst(phr('SET_DEFAULT')).'">
			</form>';
		
		echo '<form action="index.php" method="post">';
		echo '<input type="submit">';
		$config->list_table('name');
		echo '<input type="submit">';
		echo '</form>';
}

echo generating_time($inizio);

?>
</center>
</body>
</html>
