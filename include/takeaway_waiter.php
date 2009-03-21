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

function takeaway_is_set ($sourceid) {
	$tbl = new table ($sourceid);
	if (!$tbl -> exists ()) return 0;
	
	if( $tbl -> is_empty()) return 1;
	
	if(!$tbl -> data['takeaway']) return 1;
	
	$surname = trim($tbl -> data['takeaway_surname']);
	if (empty($surname)) return 0;

	return 1;
}

function takeaway_check_values($input_data){
	global $tpl;
	
	$query="SELECT * FROM `sources` WHERE `id`='".$_SESSION['sourceid']."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;
	$arr = mysql_fetch_array($res);
	if(!$arr) return ERR_TABLE_NOT_FOUND;

	$takeaway_surname_old=$arr['takeaway_surname'];
	$takeaway_time=$arr['takeaway_time'];

	foreach ($input_data as $key => $value) $input_data[$key] =trim ($value);
	
	$takeaway_time_int=(int) $takeaway_time;
	if($takeaway_time_int &&
	empty($input_data['takeaway_year']) &&
	empty($input_data['takeaway_month']) &&
	empty($input_data['takeaway_day']) &&
	empty($input_data['takeaway_minute']) &&
	empty($input_data['takeaway_hour'])) {
		$input_data['takeaway_year'] = substr($takeaway_time,0,4);
		$input_data['takeaway_month'] = substr($takeaway_time,4,2);
		$input_data['takeaway_day'] = substr($takeaway_time,6,2);
		$input_data['takeaway_hour']=substr($takeaway_time,8,2);
		$input_data['takeaway_minute']=substr($takeaway_time,10,2);
	}

	if($takeaway_time_int==0 &&
	empty($input_data['takeaway_year']) &&
	empty($input_data['takeaway_month']) &&
	empty($input_data['takeaway_day']) &&
	empty($input_data['takeaway_minute']) &&
	empty($input_data['takeaway_hour'])) {
		$input_data['takeaway_day'] = date("d",time());
		$input_data['takeaway_month'] = date("m",time());
		$input_data['takeaway_year'] = date("Y",time());
		$input_data['takeaway_hour'] = date("H",time());
		$input_data['takeaway_minute'] = date("i",time());
	}

	$msg="";
	if(!isset($input_data['customer']) &&
	$takeaway_surname_old!=$input_data['takeaway_surname']) {
		$input_data['customer']=0;
	}

	if(empty($input_data['takeaway_year']) ||
	empty($input_data['takeaway_month']) ||
	empty($input_data['takeaway_day'])) {
		$msg=ucfirst(phr('CHECK_DATE'));
	}
	if(empty($input_data['takeaway_minute'])) {
		$msg=ucfirst(phr('CHECK_MINUTE'));
	}
	if(empty($input_data['takeaway_hour'])) {
		$msg=ucfirst(phr('CHECK_HOUR'));
	}
	if(empty($input_data['takeaway_surname'])) {
		$msg=ucfirst(phr('CHECK_SURNAME'));
	}
	if(!checkdate( $input_data['takeaway_month'], $input_data['takeaway_day'], $input_data['takeaway_year'])) {
		$msg=ucfirst(phr('CHECK_DATE'));
	}
	if($input_data['takeaway_minute']<0 || $input_data['takeaway_minute']>59) {
		$msg=ucfirst(phr('CHECK_MINUTE'));
	}
	if($input_data['takeaway_hour']<0 || $input_data['takeaway_hour']>23) {
		$msg=ucfirst(phr('CHECK_HOUR'));
	}

	$time_requested = mktime (
		$input_data['takeaway_hour'],
		$input_data['takeaway_minute'],
		0,
		$input_data['takeaway_month'],
		$input_data['takeaway_day'],
		$input_data['takeaway_year']
	);
	$time_now = time();

	$time_tolerance=60*5;
	
	if($time_requested<($time_now-$time_tolerance)) {
		$msg=ucfirst(phr('CHECK_TIME_BEFORE_NOW'));
	}

	if($msg){
		$msg='<font color="Red">'.$msg.'</font>';
		$tpl -> append ('messages',$msg);
		return -1;
	}

	$input_data['takeaway_time'] = sprintf ("%04d", $input_data['takeaway_year']);
	$input_data['takeaway_time'] .= sprintf ("%02d", $input_data['takeaway_month']);
	$input_data['takeaway_time'] .= sprintf ("%02d", $input_data['takeaway_day']);
	$input_data['takeaway_time'] .= sprintf ("%02d", $input_data['takeaway_hour']);
	$input_data['takeaway_time'] .= sprintf ("%02d", $input_data['takeaway_minute']);
	$input_data['takeaway_time'] .= '00';
	unset($input_data['takeaway_year']);
	unset($input_data['takeaway_month']);
	unset($input_data['takeaway_day']);
	unset($input_data['takeaway_hour']);
	unset($input_data['takeaway_minute']);

	return $input_data;
}

function takeaway_set_customer_data($sourceid,$input_data){
	$input_data=takeaway_check_values($input_data);
	if(!is_array($input_data)) return $input_data;

	// Now we'll build the correct UPDATE query, based on the fields provided
	$query="UPDATE `sources` SET ";
	for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
		$query.="`".$key."`='".$value."',";
	}
	// strips the last comma that has been put
	$query = substr ($query, 0, strlen($query)-1);
	$query.=" WHERE `id`='$sourceid'";

	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	return 0;
}

function takeaway_get_customer_data($sourceid){
	$query="SELECT * FROM `sources` WHERE `id`=$sourceid";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;
	
	$arr = mysql_fetch_array($res);
	if(!$arr) return ERR_TABLE_NOT_FOUND;

	$data['takeaway_surname']=$arr['takeaway_surname'];
	$data['customer']=$arr['customer'];
	$takeaway_time=$arr['takeaway_time'];
	
	if($takeaway_time) {
		$data['takeaway_year'] = substr($takeaway_time,0,4);
		$data['takeaway_month'] = substr($takeaway_time,4,2);
		$data['takeaway_day'] = substr($takeaway_time,6,2);
		$data['takeaway_hour']=substr($takeaway_time,8,2);
		$data['takeaway_minute']=substr($takeaway_time,10,2);
	}
	// some data is found. we return it.
	if($takeaway_time && !empty($data['takeaway_surname'])) {
		return $data;
	}

	// we create a dataset with the actual time
	$data['takeaway_day'] = date("d",time());
	$data['takeaway_month'] = date("m",time());
	$data['takeaway_year'] = date("Y",time());
	$data['takeaway_hour'] = date("H",time());
	$data['takeaway_minute'] = date("i",time());

	return $data;
}

function takeaway_form() {
	global $tpl;
	$data=takeaway_get_customer_data($_SESSION['sourceid']);

	$tmp = customer_search_form();
	$tmp .= '
	<form action="orders.php" method="post" name="form_takeaway">
		<input type="hidden" name="command" value="set_customer">
		<table>
		<tbody>
		<tr>
		<td>
			'.ucfirst(phr('SURNAME')).':
		</td>
		<td valign="center">
		';
	if(get_conf(__FILE__,__LINE__,'takeaway_allow_unknown_customer')) {
		$tmp .= '
			<input name="data[takeaway_surname]" type="text" value="'.$data['takeaway_surname'].'" maxlength="255" size="10">';
	} else {
		$tmp .= '
			<input name="data[takeaway_surname]" type="hidden" value="'.$data['takeaway_surname'].'">
			<b>'.$data['takeaway_surname'].'</b>';
	}
	if ($data['customer']) {
		$cust_id = $data['customer'];
		$cust = New customer ($cust_id);
		
		$tmp .= '<a href="orders.php?command=customer_edit_form&amp;data[id]='.$data['customer'].'"><img src="'.IMAGE_PERSON.'" width="22" height="22" border="0"></a>';
		$tmp .= '
		</td>
		</tr>
		<tr>
		<td>
			'.ucfirst(phr('NAME')).'
		</td>
		<td>
			'.$cust -> data['name'].'
		</td>
		</tr>	
		<tr>
		<td>
			'.ucfirst(phr('ADDRESS')).'
		</td>
		<td>
			'.$cust -> data['address'].'
		</td>
		</tr>	
		<tr>
		<td>
			'.ucfirst(phr('CITY')).'
		</td>
		<td>
			'.$cust -> data['city'].'
		</td>
		</tr>	
		<tr>
		<td>
			'.ucfirst(phr('ZIP')).'
		</td>
		<td>
			'.$cust -> data['zip'].'
		</td>
		</tr>	
		<tr>
		<td>
			'.ucfirst(phr('PHONE')).'
		</td>
		<td>
			'.$cust -> data ['phone'].'
		</td>
		</tr>	
		<tr>
		<td>
			'.ucfirst(phr('MOBILE')).'
		</td>
		<td>
			'.$cust -> data ['mobile'].'
		</td>
		</tr>
		<tr>
		<td>
			'.ucfirst(phr('EMAIL')).'
		</td>
		<td>
			'.$cust -> data['email'].'
		</td>
		</tr>	
		<tr>
		<td>
			'.ucfirst(phr('VAT_ACCOUNT')).'
		</td>
		<td>
			'.$cust -> data['vat_account'].'
		</td>
		</tr>	
		';
	} else {
	$tmp .= '
		</td>
		</tr>';
	}
	$tmp .= '
		<tr>
		<td>
			'.ucfirst(phr('TIME')).':
		</td>
		<td>
			<input name="data[takeaway_hour]" type="text" value="'.$data['takeaway_hour'].'" maxlength="2" size="2">
			:
			<input name="data[takeaway_minute]" type="text" value="'.$data['takeaway_minute'].'" maxlength="2" size="2">
		</td>
		</tr>
		<tr>
		<td>
			'.ucfirst(phr('DATE')).':
		</td>
		<td>
			<input name="data[takeaway_day]" type="text" value="'.$data['takeaway_day'].'" maxlength="2" size="2">
			/
			<input name="data[takeaway_month]" type="text" value="'.$data['takeaway_month'].'" maxlength="2" size="2">
			/
			<input name="data[takeaway_year]" type="text" value="'.$data['takeaway_year'].'" maxlength="4" size="4">
		</td>
		</tr>
		<tr>
		<td colspan="2" align="center">
			<input type="image" src="'.IMAGE_OK.'" alt="'.ucfirst(phr('TAKEAWAY_SUBMIT')).'" border=0 width="32" height="32">
		</td>
		</tr>
		</tbody>
		</table>
	</form>
	';
	$tpl -> assign ('takeaway',$tmp);
	
	return 0;
}
?>