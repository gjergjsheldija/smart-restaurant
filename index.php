<?php
/**
 * Smart Restaurant
 *
 * An open source application to manage restaurants
 *
 * @package		SmartRestaurant
 * @author		Gjergj Sheldija
 * @copyright	Copyright (c) 2008-2009, Gjergj Sheldija
 * @license		http://www.gnu.org/licenses/gpl.txt
 * @since		Version 1.0
 * @filesource
 * 
 *  Smart Restaurant is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, version 3 of the License.
 *
 *	Smart Restaurant is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.

 *	You should have received a copy of the GNU General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 */

$inizio=microtime();
$useridnotsetisok=1;			//has to be before start.php requirement!!!
define('ROOTDIR','.');

session_start();

require_once(ROOTDIR."/includes.php");

require(ROOTDIR."/conf/config.inc.php");
require(ROOTDIR."/conf/config.constants.inc.php");

common_set_error_reporting ();

if(isset($_SESSION['section']) && $_SESSION['section']!="admin"){
	unset_session_vars();
	$_SESSION['section']="admin";
}

header ("Expires: " . gmdate("D, d M Y H:i:s", time()) . " GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

if(!$link = @mysql_pconnect ($cfgserver, $cfguser, $cfgpassword)) {
	die('Error connecting to the db');
}

$_SESSION['common_db']=$db_common;

date_default_timezone_set(get_conf(__FILE__,__LINE__,"default_timezone"));

check_db_status(true);

start_language ();

if (floor(phpversion()) < 5) {
	die ('Smart Restaurant requires PHP version 5 or higher.  After you have satisfied this, you can try re-installing.');
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<script type="text/javascript" language="JavaScript" src="./generic.js"></script>
<link rel="stylesheet" href="./css/styles.css" type="text/css">
<title>SmartRes - (c) 2006 - 2008-2010 Smart Restaurant</title>
<meta name="HandheldFriendly" content="True">
</head>
<body class="login">
<div class="Container">
<div id="Dialog">
  <table>
    <tr>
      <td rowspan="3" valign="bottom"><img src="./images/home_menu/new-logo.png" width="300" height="200" /></td>
      <td valign="bottom"><a href="waiter/"><img src="./images/home_menu/pda_alt.png" width="64" height="64" style="text-decoration: none;border-style: none;"/></a></td>
      <td valign="bottom"><a href="waiter/">waiter</a>
      <br />palm access point of sale</td>
    </tr>
    <tr>
      <td valign="bottom"><a href="pos/"><img src="./images/home_menu/setup_assistant.png" width="64" height="64" style="text-decoration: none;border-style: none;"/></a></td>
      <td valign="bottom"><a href="pos/">pos</a>      
      <br />touch screen pos</td>
    </tr>
    <tr>
      <td valign="bottom"><a href="administrator"><img src="./images/home_menu/blockdevice.png" width="64" height="64" style="text-decoration: none;border-style: none;"/></a></td>
      <td valign="bottom"><a href="administrator">administrator</a>
      <br />stock, account, bank management</td>
    </tr>
  </table>
</div>
<dd>Powered by <a href="http://smartres.sourceforge.net/">Smart Restaurant</a></dd>
</div>
<?php
	if (CONF_DEBUG) {
		$servrd=$_SERVER['HTTP_USER_AGENT'];
	}
?>
</body>
</html>
