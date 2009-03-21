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
require_once(ROOTDIR."/conf/config.constants.inc.php");

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

	$found_accounting_db=true;

	if(!$found_accounting_db) {
		$error_msg = common_header('No accounting db has been found');

		$error_msg .= GLOBALMSG_NO_ACCOUNTING_DB_FOUND."\n";
		$error_msg .= GLOBALMSG_CONFIGURE_DATABASES."\n";
		$error_msg .= common_bottom();
		error_msg(__FILE__,__LINE__,'No accounting db has been found');
		die($error_msg);
	}

	$_SESSION['common_db']=common_find_first_db($_SESSION['common_db']);

	// unsets all the waiters' _SESSION vars
	unset($_SESSION['catprinted']);

	if($_SERVER['PHP_SELF']!=$_SESSION['page']){
		$_SESSION['page']=$_SERVER['PHP_SELF'];
		unset($_SESSION['orderby']);
		unset($_SESSION['ordersort']);
	}



	if(isset($_GET['mgmt_db_number'])){
		$_SESSION['common_db']=$_GET['mgmt_db_number'];
	} elseif(isset($_POST['mgmt_db_number'])){
		$_SESSION['common_db']=$_POST['mgmt_db_number'];
	}

	if(!isset($_SESSION['common_db'])) {
		$_SESSION['common_db']=common_find_first_db();
	}



	$conf_day_end=get_conf(__FILE__,__LINE__,"day_end");
	// get the date start and end from get, post or session
	// otherwise sets it to today
	if(isset($_REQUEST['date_start'])) $_SESSION['date']['start']=$_REQUEST['date_start'];
	elseif (!isset($_SESSION['date']['start'])) $_SESSION['date']['start']=date("d/m/Y",time());
	
	if(isset($_REQUEST['hour_start'])) $_SESSION['hour_start']=$_REQUEST['hour_start'];
	elseif (!isset($_SESSION['hour_start'])) $_SESSION['hour_start']=substr($conf_day_end,0,2);
	
	if(isset($_REQUEST['minute_start'])) $_SESSION['minute_start']=$_REQUEST['minute_start'];
	elseif (!isset($_SESSION['minute_start'])) $_SESSION['minute_start']=substr($conf_day_end,2,2);
	
	if(isset($_REQUEST['date_end'])) $_SESSION['date']['end']=$_REQUEST['date_end'];
	elseif (!isset($_SESSION['date']['end'])) $_SESSION['date']['end']=date("d/m/Y",(time()+24*3600));
	if(isset($_REQUEST['hour_end'])) $_SESSION['hour_end']=$_REQUEST['hour_end'];
	elseif (!isset($_SESSION['hour_end'])) $_SESSION['hour_end']=substr($conf_day_end,0,2);
	if(isset($_REQUEST['minute_end'])) $_SESSION['minute_end']=$_REQUEST['minute_end'];
	elseif (!isset($_SESSION['minute_end'])) $_SESSION['minute_end']=substr($conf_day_end,2,2);

	// explode data string from DD/MM/YYYY to array
	list($date[2],$date[1],$date[0])=explode("/",$_SESSION['date']['start']);
	$date[3]=$_SESSION['hour_start'];
	$date[4]=$_SESSION['minute_start'];
	$date[5]='00';
	ksort($date);

	$year=$date[0];
	$month=$date[1];
	$day=$date[2];
	$hour=$date[3];
	$minute=$date[4];
	$second=$date[5];

	$time_start=mktime($hour,$minute,$second,$month,$day,$year);

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

	$_SESSION['timestamp']['start']=$timestamp_start;

	list($date[2],$date[1],$date[0])=explode("/",$_SESSION['date']['end']);
	$date[3]=$_SESSION['hour_end'];
	$date[4]=$_SESSION['minute_end'];

	$conf_day_end=get_conf(__FILE__,__LINE__,"day_end");
	$year=$date[0];
	$month=$date[1];
	$day=$date[2];
	$hour=$date[3];
	$minute=$date[4];
	$second=$date[5];

	$time_end=mktime($hour,$minute,$second,$month,$day,$year);
	
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


	$_SESSION['date']['start']=substr($timestamp_start,6,2)."/";
	$_SESSION['date']['start'].=substr($timestamp_start,4,2)."/";
	$_SESSION['date']['start'].=substr($timestamp_start,0,4);

	$_SESSION['time']['start']=substr($timestamp_start,8,2);
	$_SESSION['time']['start'].=":".substr($timestamp_start,10,2);

	$_SESSION['date']['end']=substr($timestamp_end,6,2)."/";
	$_SESSION['date']['end'].=substr($timestamp_end,4,2)."/";
	$_SESSION['date']['end'].=substr($timestamp_end,0,4);

	$_SESSION['time']['end']=substr($timestamp_end,8,2);
	$_SESSION['time']['end'].=":".substr($timestamp_end,10,2);

	if(isset($_REQUEST['command'])){
		$command=$_REQUEST['command'];
		$_SESSION['command']=$command;
	}

	if(isset($_GET['id'])){
		$start_id=$_GET['id'];
		$_SESSION['id']=$start_id;
	} elseif(isset($_POST['id'])){
		$start_id=$_POST['id'];
		$_SESSION['id']=$start_id;
	} elseif(isset($_SESSION['id'])){
		$start_id=$_SESSION['id'];
	}
	if(isset($_GET['data'])){
		$start_data=$_GET['data'];
	} elseif(isset($_POST['data'])){
		$start_data=$_POST['data'];
	}

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

	header("Content-Language: ".$_SESSION['language']);
	header("Content-type: text/html; charset=".phr('CHARSET'));

	$jsurl=ROOTDIR."/generic.js";
	
	$cal_lang_file = ROOTDIR.'/jscalendar/lang/calendar-'.strtolower($_SESSION['language']).'.js';
	if (!is_readable($cal_lang_file)) $cal_lang_file = ROOTDIR.'/jscalendar/lang/calendar-en.js';

?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
 	<html>
	<head>
	<title>Smart Restaurant - Management</title>
	<meta http-equiv="Cache-Control" content="no-cache">
	<meta http-equiv="Expires" content="0">
	<script type="text/javascript" language="JavaScript" src="<?php echo $jsurl; ?>"></script>
	</script>
	</head>
<?php if(!isset($_GET['print'])) { ?>
	<body class=mgmt_body>
<?php
	$menu = new menu();
	echo $menu -> main ();
?>
	<table><TR><TD height="20">&nbsp;</TD></TR></table>

<?php
	}
	$header_printed=2;
}

	$timestamp_start=$_SESSION['timestamp']['start'];
	$timestamp_end=$_SESSION['timestamp']['end'];


?>
