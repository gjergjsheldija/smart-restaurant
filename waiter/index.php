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
$inizio=microtime();			// has to be before start.php requirement!!!
session_start();

define('ROOTDIR','..');
$useridnotsetisok=1;			// has to be before start.php requirement!!!
$dont_redirect_to_menu=true;

require_once(ROOTDIR."/includes.php");
require_once(ROOTDIR."/waiter/waiter_start.php");

unset_source_vars();

$tpl -> set_waiter_template_file ('authentication');

// code to allow zaurus opera to reconnect (it doesn't disconnect users properly)
$opera_zaurus=false;
if(stristr($_SERVER['HTTP_USER_AGENT'],'opera') && stristr($_SERVER['HTTP_USER_AGENT'],'embedix')) $opera_zaurus=true;

if (!isset($_SESSION['userid']) || $opera_zaurus) {
	$user = new user();
	$tmp = '
<form action="tables.php" method="post">
<table>
	<tr><td>
		<center>
		<h4>
		'.date("j/n/Y",time()).'<br/>
		<b>'.date("G:i",time()).'</b>
		</h4>
		'.ucfirst(phr('WHO_ARE_YOU')).'<br/>
'.$user->html_select(SHOW_WAITER_ONLY).'
		</center>
	</td></tr>
	<tr><td>
		<center>
		<INPUT TYPE="SUBMIT" value="'.ucfirst(phr('SUBMIT')).'">
		</center>
	</td></tr>
</table>
</form>
';
	$tpl -> assign ('waiter_selection',$tmp);
} else {
	$tmp = redirect_waiter('tables.php');
	$tpl -> append ('scripts',$tmp);

	$tmp = '<font color="green">'.ucfirst(phr('ALREADY_CONNECTED')).'<br/><a href="tables.php">'.ucfirst(phr('GO_ON')).'</a></font>'."\n";
	$tpl -> append ('messages',$tmp);
}

// prints page generation time
$tmp = generating_time($inizio);
$tpl -> assign ('generating_time',$tmp);

if($err=$tpl->parse()) return $err; 

$tpl -> clean();
$output = $tpl->getOutput();

// prints everything to screen
echo $output;
if(CONF_DEBUG_PRINT_PAGE_SIZE) echo $tpl -> print_size();
?>
