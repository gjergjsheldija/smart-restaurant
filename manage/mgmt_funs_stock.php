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

function stock_insert_item($data){
	$data['category']=0;
	$data['stock_is_on']=1;

	$table='dishes';
	$query="INSERT INTO $table (";
	for (reset ($data); list ($key, $value) = each ($data); ) {
		$query.="`".$key."`,";
	}
	// strips the last comma that has been put
	$query = substr ($query, 0, strlen($query)-1);
	$query.=") VALUES (";
	for (reset ($data); list ($key, $value) = each ($data); ) {
		$query.="'".$value."',";
	}
	// strips the last comma that has been put
	$query = substr ($query, 0, strlen($query)-1);	$query.=")";
	echo $query;
	
	$res = mysql_db_query ($_SESSION['common_db'],$query);
	$num_affected=mysql_affected_rows();

	if($num_affected!=1) return 1;

	return 0;
}

function movement_insert($data){
	$table='account_stock_log';
	$query="INSERT INTO $table (";
	for (reset ($data); list ($key, $value) = each ($data); ) {
		$query.="`".$key."`,";
	}
	// strips the last comma that has been put
	$query = substr ($query, 0, strlen($query)-1);
	$query.=") VALUES (";
	for (reset ($data); list ($key, $value) = each ($data); ) {
		$query.="'".$value."',";
	}
	// strips the last comma that has been put
	$query = substr ($query, 0, strlen($query)-1);	$query.=")";
	$res = mysql_db_query ($_SESSION['common_db'],$query);
	$num_affected=mysql_affected_rows();

	if($num_affected!=1) return 1;

	set_stock($data['name'],$data['quantity'], $data['value']);

	return 0;
}

function movement_update($data,$id){
	if(!is_array($data)) return 0;

	$table='account_stock_log';
	$query="SELECT * FROM $table WHERE `id`='$id'";

	$res = mysql_db_query ($_SESSION['common_db'],$query);
	if($errno=mysql_errno()) {
		$msg="Error in ".__FUNCTION__." - ";
		$msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
		$msg.='query: '.$query."\n";
		echo nl2br($msg)."\n";
		error_msg(__FILE__,__LINE__,$msg);
		return $errno;
	}
	$row=mysql_fetch_array($res);
	mysql_free_result($res);

	$oldquantity=$row['quantity'];

	if(isset($data['date'])) {
		$data['timestamp']=$data['date']['year'];
		$data['timestamp'].=$data['date']['month'];
		$data['timestamp'].=$data['date']['day'];
		$data['timestamp'].=$data['date']['hour'];
		$data['timestamp'].=$data['date']['minute'];
		$data['timestamp'].=$data['date']['second'];

		$oldtimestamp=$row['timestamp'];
		if($oldquantity==$data['quantity'] && $oldtimestamp==$data['timestamp']) return 0;

		unset($data['date']);
	}

	if($oldquantity==$data['quantity']) return 0;
	$table='account_stock_log';
	$query="UPDATE $table SET ";
	if(is_array($data)){
		for (reset ($data); list ($key, $value) = each ($data); ) {
			$query.="`".$key."`='".$value."',";
		}
	}

	// strips the last comma that has been put
	$query = substr ($query, 0, strlen($query)-1);
	$query.=" WHERE `id`='$id'";

	$res2 = mysql_db_query ($_SESSION['common_db'],$query);
	$num_affected = mysql_affected_rows();


	if($errno=mysql_errno()) {
		$msg="Error in ".__FUNCTION__." - ";
		$msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
		$msg.='query: '.$query."\n";
		echo nl2br($msg)."\n";
		error_msg(__FILE__,__LINE__,$msg);
		return $errno;
	}

	$table='account_stock_log';
	$query="SELECT * FROM $table WHERE `id`='".$id."'";
	$res = mysql_db_query ($_SESSION['common_db'],$query);
	if($errno=mysql_errno()) {
		$msg="Error in ".__FUNCTION__." - ";
		$msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
		$msg.='query: '.$query."\n";
		echo nl2br($msg)."\n";
		error_msg(__FILE__,__LINE__,$msg);
		return $errno;
	}
	$row=mysql_fetch_array($res);

	$newstock=$row['name'];
	$newquantity=$data['quantity'];
	//new buing proce
	$newvalue = $data['value'];
	$diffquantity=$newquantity-$oldquantity;
	
	
	//sets the new stock quantity
	set_stock($newstock, $diffquantity);

	return 0;
}

function movement_invoice_delete($invoice_id){
	$table='account_stock_log';
	$query="SELECT * FROM $table WHERE `invoice_id`='$invoice_id'";
	$res = mysql_db_query ($_SESSION['common_db'],$query);
	while($row=mysql_fetch_array($res)){

		$oldstock=$row['name'];
		$oldquantity=$row['quantity'];
		$diffquantity=0-$oldquantity;

		set_stock($oldstock,$diffquantity);
	}
	$table='account_stock_log';
	$query="DELETE FROM $table WHERE `invoice_id`='$invoice_id'";
	$res = mysql_db_query ($_SESSION['common_db'],$query);
	return 0;
}

/**
 *  updates the sqock quantity
 *  change also the buing proce
 *
 * @param  $dishname
 * @param  $diffquantity
 * @param  $value
 * @author mizuko
 */
function set_stock($dishname,$diffquantity, $value){

	$dishname = str_replace ("'", "\'", $dishname);
	$table='stock_objects';
	$query="SELECT * FROM $table WHERE `name`='".$dishname."'";
	$res=mysql_db_query($_SESSION['common_db'],$query);

	if(!mysql_num_rows($res)) return 2;

	$row=mysql_fetch_array($res);
	mysql_free_result($res);

	if(!$row['stock_is_on']) return 1;
	$oldstock=$row['stock'];
	$newstock=$oldstock+$diffquantity;
	$table='dishes';
	$query="UPDATE $table SET `stock`= '".$newstock."', value = '". $newvalue ."' WHERE `id`='".$row['id']."'";
	$res=mysql_db_query($_SESSION['common_db'],$query);
	return 0;
}


?>
