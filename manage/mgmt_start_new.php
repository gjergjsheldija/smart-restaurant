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

require(ROOTDIR."/conf/config.inc.php");
require(ROOTDIR."/conf/config.constants.inc.php");

global $header_printed;

if(!$header_printed){
	session_start();

	header ("Expires: " . gmdate("D, d M Y H:i:s", time()) . " GMT");
	header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

	common_set_error_reporting ();
	
	if($_SESSION['section']!="manage"){
		unset_session_vars();
		$_SESSION['section']="manage";
	}

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

	start_language ();

	if(!common_allowed_ip($_SERVER['REMOTE_ADDR'])) {
		$error_msg = common_header('IP address not authorized');
		$error_msg .= 'IP <b>'.$_SERVER['REMOTE_ADDR'].'</b> is not authorized.<br/>'."\n";
		$error_msg .= 'IP <b>'.sprintf("%u",ip2long($_SERVER['REMOTE_ADDR'])).'</b> is not authorized.'."\n";
		
		$error_msg .= common_bottom();
		die($error_msg);
	}
	
	if(!find_accounting_db()) {
		$error_msg = common_header('No accounting db has been found');
		$error_msg .=  navbar_empty();

		$error_msg .= GLOBALMSG_NO_ACCOUNTING_DB_FOUND."<br><br>\n";
		$error_msg .= GLOBALMSG_CONFIGURE_DATABASES."\n";
		$error_msg .= common_bottom();
		error_msg(__FILE__,__LINE__,'No accounting db has been found');
		die($error_msg);
	}

	if($res_loc=check_output_files()) {
			$error_msg = common_header('Output files not writeable');
			$error_msg .=  navbar_empty();

			switch($res_loc) {
				case 1: $err='error file not writeable'; break;
				case 2: $err='error dir not writeable'; break;
				case 3: $err='debug file not writeable'; break;
				case 4: $err='debug dir not writeable'; break;
			}

			$error_msg .= GLOBALMSG_CONFIG_OUTPUT_FILES_NOT_WRITEABLE.'<br><br>(err '.$res_loc.': '.$err.')<br>'."\n";
			$error_msg .= GLOBALMSG_CONFIG_SYSTEM.'<br>'."\n";
			$error_msg .= common_bottom();
			die($error_msg);
	}
	unset($res_loc);
	
	// unsets all the waiters' _SESSION vars
	unset($_SESSION['catprinted']);

	
	if(isset($_REQUEST['mgmt_db_number'])){
		$_SESSION['common_db']=$_REQUEST['mgmt_db_number'];
	} elseif(!isset($_SESSION['common_db'])) {
		$_SESSION['common_db']=common_find_first_db();
	}

	/*****************************************************************
	Time stuff begin	
	*****************************************************************/
	
	// get the date start and end from get, post or session
	// otherwise sets it to today
	
	/*
	if(isset($_REQUEST['date_start'])){
		$_SESSION['date']['start']=$_REQUEST['date_start'];
	} elseif (!isset($_SESSION['date']['start'])) {
		$_SESSION['date']['start']=date("d/m/Y",time());
	}
	if(isset($_REQUEST['date_end'])){
		$_SESSION['date']['end']=$_REQUEST['date_end'];
	} elseif (!isset($_SESSION['date']['end'])) {
		$_SESSION['date']['end']=date("d/m/Y",time());
	}

	// explode data string from DD/MM/YYYY to array
	list($date[2],$date[1],$date[0])=explode("/",$_SESSION['date']['start']);
	ksort($date);

	$conf_day_end=get_conf(__FILE__,__LINE__,"day_end");
	$year=$date[0];
	$month=$date[1];
	$day=$date[2];
	$hour=substr($conf_day_end,0,2);
	$minute=substr($conf_day_end,2,2);
	$second=substr($conf_day_end,4,2);

	$time_start=mktime($hour,$minute,$second,$month,$day,$year);
	// if we're in the just past working day, subtract 24 hours to start day
	if($_REQUEST['formdata']==true) {
		if((date("His",time())<=$conf_day_end) && (date("His",time())>='000000'))
			$time_start=$time_start-24*3600;
	}

	$time_start_arr[2]=date("j",$time_start);
	$time_start_arr[1]=date("n",$time_start);
	$time_start_arr[0]=date("Y",$time_start);
	$time_start_arr[3]=date("H",$time_start);
	$time_start_arr[4]=date("i",$time_start);
	$time_start_arr[5]=date("s",$time_start);

	// begins writing of the timestamp string
	$timestamp_start="";
	for ($i=0;$i<6;$i++) {
		if($i=="0"){
			$timestamp_start.=sprintf("%04d",$time_start_arr[$i]);
		} else {
			$timestamp_start.=sprintf("%02d",$time_start_arr[$i]);
		}
	}
	//$timestamp_start.=get_conf(__FILE__,__LINE__,"day_end");

	$_SESSION['timestamp']['start']=$timestamp_start;

	list($date[2],$date[1],$date[0])=explode("/",$_SESSION['date']['end']);

	$conf_day_end=get_conf(__FILE__,__LINE__,"day_end");
	$year=$date[0];
	$month=$date[1];
	$day=$date[2];
	$hour=substr($conf_day_end,0,2);
	$minute=substr($conf_day_end,2,2);
	$second=substr($conf_day_end,4,2);

	$time_end=mktime($hour,$minute,$second,$month,$day,$year);
	// if we're not in the just past working day, add 24 hours to end day
	if($_REQUEST['formdata']==true) {
		if((date("His",time())<=$conf_day_end) && (date("His",time())>='000000')) {
		} else $time_end=$time_end+(3600*24);
	}
	$time_end_arr[2]=date("j",$time_end);
	$time_end_arr[1]=date("n",$time_end);
	$time_end_arr[0]=date("Y",$time_end);
	$time_end_arr[3]=date("H",$time_end);
	$time_end_arr[4]=date("i",$time_end);
	$time_end_arr[5]=date("s",$time_end);

	$timestamp_end="";
	for ($i=0;$i<6;$i++) {
		if($i=="0"){
			$timestamp_end.=sprintf("%04d",$time_end_arr[$i]);
		} else {
			$timestamp_end.=sprintf("%02d",$time_end_arr[$i]);
		}
	}

	$_SESSION['timestamp']['end']=$timestamp_end;

	//debug_msg(__FILE__,__LINE__,"$timestamp_start -> $timestamp_end");

	$_SESSION['date']['start']=substr($timestamp_start,6,2)."/";
	$_SESSION['date']['start'].=substr($timestamp_start,4,2)."/";
	$_SESSION['date']['start'].=substr($timestamp_start,0,4);

	$_SESSION['time']['start']=substr($timestamp_start,8,2);
	$_SESSION['time']['start'].=":".substr($timestamp_start,10,2);
	//$_SESSION['time']['start'].=":".substr($timestamp_start,12,2);


	$_SESSION['date']['end']=substr($timestamp_end,6,2)."/";
	$_SESSION['date']['end'].=substr($timestamp_end,4,2)."/";
	$_SESSION['date']['end'].=substr($timestamp_end,0,4);

	$_SESSION['time']['end']=substr($timestamp_end,8,2);
	$_SESSION['time']['end'].=":".substr($timestamp_end,10,2);
	//$_SESSION['time']['end'].=":".substr($timestamp_end,12,2);

	*/
	/*****************************************************************
	Time stuff end	
	*****************************************************************/

	/*
	we almost always use this command var, so we get it here
	to make it available to other functions whithout other hassle
	*/
	if(isset($_REQUEST['command'])){
		$command=$_REQUEST['command'];
	} else {
		$command='none';
	}

	if(isset($_REQUEST['data'])){
		$start_data=$_REQUEST['data'];
	}



	/*
	if(isset($_GET['payment_data_date_day'])){
		// We need this to avoid warnings
		$payment_data=array(
		"date" => array("day" => 0,"month" => 0,"year" => 0),
		"type" => 0
		);
		$payment_data["date"]["day"]=$_GET['payment_data_date_day'];
	} elseif(isset($_POST['payment_data_date_day'])){
		// We need this to avoid warnings
		$payment_data=array(
		"date" => array("day" => 0,"month" => 0,"year" => 0),
		"type" => 0
		);
		$payment_data['date']['day']=$_POST['payment_data_date_day'];
	}
	if(isset($_GET['payment_data_date_month'])){
		$payment_data['date']['month']=$_GET['payment_data_date_month'];
	} elseif(isset($_POST['payment_data_date_month'])){
		$payment_data['date']['month']=$_POST['payment_data_date_month'];
	}
	if(isset($_GET['payment_data_date_year'])){
		$payment_data['date']['year']=$_GET['payment_data_date_year'];
	} elseif(isset($_POST['payment_data_date_year'])){
		$payment_data['date']['year']=$_POST['payment_data_date_year'];
	}
	if(isset($_GET['payment_data_type'])){
		$payment_data['type']=$_GET['payment_data_type'];
	} elseif(isset($_POST['payment_data_type'])){
		$payment_data['type']=$_POST['payment_data_type'];
	}
	if(isset($_GET['payment_data_account_id'])){
		$payment_data['account_id']=$_GET['payment_data_account_id'];
	} elseif(isset($_POST['payment_data_account_id'])){
		$payment_data['account_id']=$_POST['payment_data_account_id'];
	}

	if($_SERVER['REQUEST_URI']!=$_SESSION['actualpage']){
		$_SESSION['lastpage']=$_SESSION['actualpage'];
		$_SESSION['actualpage']=$_SERVER['REQUEST_URI'];
	}
	*/
	
	header("Content-Language: ".$_SESSION['language']);
	header("Content-type: text/html; charset=".phr('CHARSET'));

	$tpl = new template;

	$tmp = head_line('Management section');
	$tpl -> assign("head", $tmp);

/*
	?>
<?php if(!isset($_GET['print'])) { ?>
	<body class=mgmt_body>
<?php
*/
	$menu = new menu();
	$tmp = $menu -> main ();
	$tpl -> append("scripts", $tmp);
	
	$header_printed=2;
}
/*
	$timestamp_start=$_SESSION['timestamp']['start'];
	$timestamp_end=$_SESSION['timestamp']['end'];
*/

?>
