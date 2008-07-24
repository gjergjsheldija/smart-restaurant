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

define('ROOTDIR','..');
require(ROOTDIR."/manage/mgmt_funs.php");
require(ROOTDIR."/manage/mgmt_start.php");

if(!access_allowed(USER_BIT_ACCOUNTING)) $command='access_denied';

switch($command) {
	case 'access_denied':
				echo access_denied_admin();
				break;
	default:
		echo "<meta http-equiv=\"Cache-Control\" content=\"no-cache\" />\n";
		echo "<meta http-equiv=\"Expires\" content=\"0\" />\n";
main_header('vat.php');
?>
	<div align="center"><h3><?php echo ucfirst(phr('VAT_CALCULATION')); ?></h3></div>
<?php


		if(vat_report())
			echo GLOBALMSG_RECORD_NONE_FOUND_PERIOD_ERROR.".<br>\n\n";
		break;
}

if($command!="delete") unset($_SESSION["delete"]);

echo "<br><a href=\"#\" onclick=\"javascript:history.go(-1); return false\">".ucfirst(phr('GO_BACK'))."</a><br>\n";
echo "<br><a href=\"index.php\">".ucfirst(phr('GO_MAIN_REPORT'))."</a><br>";

//echo "<br><a href=\"".$_SESSION['lastpage']."\">Torna indietro</a>\n";

echo generating_time($inizio);

?>
