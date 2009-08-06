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

function access_connect_form ($url='') {
	$output = '';
	if(empty($url) && isset($_REQUEST['url']) && !empty($_REQUEST['url'])) $url=$_REQUEST['url'];
	
	$user = new user();
	$output .= '
	<div align="center">
	<form action="'.ROOTDIR.'/admin/connect.php" method="post" name="connect_form">
	<input type="hidden" name="command" value="connect">
	';
	if(!empty($url))
		$output .= '<input type="hidden" name="url" value="'.$url.'">'."\n";
	$output .= '<table>
		<tr><td>
			<center>
			<h4>
			'.date("j/n/Y",time()).'<br/>
			<b>'.date("G:i",time()).'</b>
			</h4>
			'.ucfirst(phr('WHO_ARE_YOU')).'<br/>
	'.$user->html_select(SHOW_ADMIN_ONLY).'
			</center>
		</td></tr>
		<tr><td>
			<center>
			'.ucfirst(phr('PASSWORD')).':<br/>
			<input type="password" name="password">
			</center>
		</td></tr>
		<tr><td>
			<center>
			<INPUT TYPE="SUBMIT" value="'.ucfirst(phr('SUBMIT')).'">
			</center>
		</td></tr>
	</table>
	</form>
	</div>
	';
	//TODO : traslate string
	if($_SESSION['userid'])
		$output = 'You are connected.<br>
		<a href="'.ROOTDIR.'/admin/connect.php?command=disconnect">Shkeputuni perpara.</a>';
	
	return $output;
}
//TODO : translate here
//mizuko : mod for pass waiter
function access_connect_form_waiter_pos ( $err, $url='') {
	switch ($err) {
		case ERR_USER_NOT_FOUND:
		case ERR_WRONG_PASSWORD:
			$loginerror = '
			  <div id="negative">
			    <table width="450" cellpadding="0" cellspacing="12">
			      <tr>
			        <td width="52"><div align="center"><img src="'.IMAGE_NEGATIVE.'" alt="negative" width="18" height="18" /></div></td>
			        <td width="388" >'.ucfirst(phr('GIVE_PASSWORD')).'</td> 
			      </tr>
			    </table>
			  </div>
			';
			break;
		case ERR_NO_USER_PROVIDED:
			$loginerror = '
			  <div id="negative">
			    <table width="450" cellpadding="0" cellspacing="12">
			      <tr>
			        <td width="52"><div align="center"><img src="'.IMAGE_NEGATIVE.'" alt="negative" width="18" height="18" /></div></td>
			        <td width="388">'.ucfirst(phr('SELECT_USER')).'</td> 
			      </tr>
			    </table>
			  </div>
			';			
			break;			
		case ERR_NO_PASSWORD:	
			$loginerror = '
			  <div id="tip">
			    <table width="450" cellpadding="0" cellspacing="12">
			      <tr>
			        <td width="52"><div align="center"><img src="'.IMAGE_ERROR.'" alt="negative" width="18" height="18"/></div></td>
			        <td width="388">'.ucfirst(phr('NO_PASSWORD_GIVEN')).'</td> 
			      </tr>
			    </table>
			  </div>
			';				
			break;		
	}
	$output = '';
	if(empty($url) && isset($_REQUEST['url']) && !empty($_REQUEST['url'])) $url=$_REQUEST['url'];
	
	$user = new user();
	$output .= '
	<div align="center">
	<form action="index.php" method="post" name="waiterpos" id="waiterpos">
	<input type="hidden" name="command" value="connect">
	<input type="hidden" id="userid" name="userid" value="">
	';
	if(!empty($url))
		$output .= '<input type="hidden" name="url" value="'.$url.'">'."\n";
		$output .= '<table>
		<tr><td>
			<center>
				<h2>'.date("j/n/Y",time()).' -  <strong>'.date("G:i",time()).'</strong></h2>'
				. $loginerror .'<br><br>'.$user->html_button_login_pos(SHOW_WAITER_ONLY).'
			</center>
		</td></tr>
		<tr><td>
			<center>
				<input type="password" name="password" id="password"> <INPUT TYPE="SUBMIT" id="loginbutton" value="'.ucfirst(phr('SUBMIT')).'">
			</center>
		</td></tr>
		<tr><td>
			<center>
			
			</center>
		</td></tr>
	</table>
	</form>
	</div>
	';
	
	return $output;
}
//end : mizuko

function access_denied_waiter () {
	global $tpl;
	$tpl -> set_waiter_template_file ('question');

	$tmp = '
	  <div id="negative">
	    <table width="450" cellpadding="0" cellspacing="12">
	      <tr>
	        <td width="52"><div align="center"><img src="'.IMAGE_NEGATIVE.'" alt="negative" width="18" height="18" /></div></td>
	        <td width="388">'.ucfirst(phr('ACCESS_DENIED')).'</td> 
	      </tr>
	    </table>
	  </div>
	';		
	$tpl -> append ('messages',$tmp);

	$tmp = navbar_empty('javascript:history.go(-1);');
	$tpl -> assign ('navbar',$tmp);
	
	return 0;
}

function access_denied_waiter_pos () {
	global $tpl;
	$tpl -> set_waiter_template_file ('question');

	$tmp='<div id="tip">
		    <table width="450" cellpadding="0" cellspacing="12">
		      <tr>
		        <td width="52"><div align="center"><img src="'.IMAGE_ERROR.'" alt="negative" width="18" height="18"/></div></td>
		        <td width="388" >'.ucfirst(phr('ACCESS_DENIED')).'</td> 
		      </tr>
		    </table>
		  </div>
		';
	$tpl -> append ('messages',$tmp);

	$tmp = navbar_empty_pos('javascript:history.go(-1);');
	$tpl -> assign ('navbar',$tmp);
	
	return 0;
}

function access_denied_admin () {
	$url = $_SERVER['REQUEST_URI'];
	$link = ROOTDIR.'/admin/connect.php?command=disconnect';
	$link .= '&url='.urlencode($url);
	$tmp='<div id="tip">
		    <table width="450" cellpadding="0" cellspacing="12">
		      <tr>
		        <td width="52"><div align="center"><img src="'.IMAGE_ERROR.'" alt="negative" width="18" height="18" /></div></td>
		        <td width="388" >'.ucfirst(phr('ACCESS_DENIED')).'<br/>'.ucfirst(phr('ACCESS_DENIED_EXPLAIN')).'</td> 
		      </tr>
		    </table>
		  </div>
		';	

	if(!isset($_SESSION['userid']) || !$_SESSION['userid']) {
		$user = new user ($_SESSION['userid']);
		$user->disconnect();
		$tmp .= access_connect_form($url);
	} else {
		$tmp='<div id="tip">
			    <table width="450" cellpadding="0" cellspacing="12">
			      <tr>
			        <td width="52"><div align="center"><img src="'.IMAGE_ERROR.'" alt="negative" width="18" height="18" /></div></td>
			        <td width="388" >'.ucfirst(phr('ACCESS_DENIED')).'<br/>'.ucfirst(phr('ACCESS_DENIED_EXPLAIN')).'<br/><a href="'.$link.'">'.ucfirst(phr('CONNECT')).'</a></td> 
			      </tr>
			    </table>
			  </div>
			';	
	}
	return $tmp;
}

function access_allowed ($level) {
	$query="SELECT `value` FROM `system` WHERE `name`='upgrade_last_key'";
	$res = common_query($query,__FILE__,__LINE__);
	if(!$res) return true;
	$arr=mysql_fetch_array($res);
	// system version is before user zones -> disable access control
	if($arr['value']<4) return true;
	
	$user = new user();
	// no user found, auth ok anyway (to allow recovering from lost passwords)
	if(!$user -> count_users()) return true;
	
	// not authenticated
	if(!isset($_SESSION['userid'])) return false;
	
	$user = new user($_SESSION['userid']);
	
	// disabled user, deny
	if($user->data['disabled']) return false;
	
	//doesn't have the flag
	if(!$user->level[$level]) return false;
	
	// the flag is waiter or cashier or any other that doesn't need a password, ok
	if($level==USER_BIT_WAITER || $level==USER_BIT_CASHIER || $level==USER_BIT_MONEY) return true;
	
	// other flags need password
	if (isset($_SESSION['passworded']) && $_SESSION['passworded']) return true;
	
	// password not set
	return false;
}
?>