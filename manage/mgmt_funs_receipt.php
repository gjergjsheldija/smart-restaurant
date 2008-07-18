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

function delete_receipt_rows($delete){
	require("./mgmt_start.php");

// next is only a ref line. to be deleted.
//DELETE FROM `account_mgmt_main` WHERE `id`='25' OR `id`='26';
	$firstline=1;
	$counter=0;

	if(!is_array($delete)){
		echo GLOBALMSG_RECORD_DELETE_NONE.".<br>\n";
		return 1;
	}
	$table=$GLOBALS['table_prefix'].'account_mgmt_main';
	$query="DELETE FROM $table WHERE ";
	for (reset ($delete); list ($key, $value) = each ($delete); ) {
		if($firstline) {
			$query.="`id`='".$key."'";
			$firstline=0;
		} else {
			$query.=" OR `id`='".$key."'";
		}
		$table=$GLOBALS['table_prefix'].'account_mgmt_main';
		$res=mysql_db_query($_SESSION['common_db'],"SELECT * FROM $table WHERE `id`='$key'");
		$row=mysql_fetch_array($res);
		$description[$key]=$row['internal_id'];
		$counter++;
	}
	// echo "<br>Query SQL:<br>".$query."<br>\n";

	$res = mysql_db_query ($_SESSION['common_db'],$query);
	$num_affected=mysql_affected_rows();

	if ($num_affected!=0 && $counter>1) {
		echo("<META HTTP-EQUIV=\"Refresh\" CONTENT=\"".get_conf(__FILE__,__LINE__,"refresh_time_management")."; URL=index.php\">");
		$msg = GLOBALMSG_RECORD_THE_MANY." <b>";
		for (reset ($delete); list ($key, $value) = each ($delete); ) {
			$msg.=$description[$key];
			$msg.=", ";
		}
		$msg = substr($msg,0,strlen($msg)-2);

		$msg.="</b> ".GLOBALMSG_RECORD_DELETE_OK_MANY.". <br>\n";
	} elseif ($num_affected!=0 && $counter==1) {
		echo("<META HTTP-EQUIV=\"Refresh\" CONTENT=\"".get_conf(__FILE__,__LINE__,"refresh_time_management")."; URL=index.php\">");
		$msg = GLOBALMSG_RECORD_THE." <b>";
		for (reset ($delete); list ($key, $value) = each ($delete); ) {
			$msg.=$description[$key];
			$msg.=", ";
		}
		$msg = substr($msg,0,strlen($msg)-2);

		$msg.="</b> ".GLOBALMSG_RECORD_DELETE_OK.". <br>\n";

	}elseif(mysql_errno()) {
		echo ucfirst(phr('ERROR')).".<br>\n";
		return 1;
	} else {
		echo GLOBALMSG_RECORD_DELETE_NONE.".<br>\n";
	}
	echo $msg;
	return 0;
}

function annul_receipt_rows($delete){
	require("./mgmt_start.php");

// next is only a ref line. to be deleted.
//DELETE FROM `account_mgmt_main` WHERE `id`='25' OR `id`='26';
	$firstline=1;
	$counter=0;

	if(!is_array($delete)){
		echo GLOBALMSG_RECORD_DELETE_NONE.".<br>\n";
		return 1;
	}

	$table=$GLOBALS['table_prefix'].'account_mgmt_main';
	$query="UPDATE $table SET `annulled`='1' WHERE ";
	for (reset ($delete); list ($key, $value) = each ($delete); ) {
		if($firstline) {
			$query.="`id`='".$key."'";
			$firstline=0;
		} else {
			$query.=" OR `id`='".$key."'";
		}
		$table=$GLOBALS['table_prefix'].'account_mgmt_main';
		$res=mysql_db_query($_SESSION['common_db'],"SELECT * FROM $table WHERE `id`='$key'");
		$row=mysql_fetch_array($res);
		$description[$key]=$row['internal_id'];
		$counter++;
	}
// echo "<br>Query SQL:<br>".$query."<br>\n";

	$res = mysql_db_query ($_SESSION['common_db'],$query);
	$num_affected=mysql_affected_rows();

	if ($num_affected!=0 && $counter>1) {
		echo("<META HTTP-EQUIV=\"Refresh\" CONTENT=\"".get_conf(__FILE__,__LINE__,"refresh_time_management")."; URL=index.php\">");
		$msg = GLOBALMSG_RECORD_THE_MANY." <b>";
		for (reset ($delete); list ($key, $value) = each ($delete); ) {
			$msg.=$description[$key];
			$msg.=", ";
		}
		$msg = substr($msg,0,strlen($msg)-2);

		$msg.="</b> ".GLOBALMSG_RECORD_DELETE_OK_MANY.". <br>\n";
	} elseif ($num_affected!=0 && $counter==1) {
		echo("<META HTTP-EQUIV=\"Refresh\" CONTENT=\"".get_conf(__FILE__,__LINE__,"refresh_time_management")."; URL=index.php\">");
		$msg = GLOBALMSG_RECORD_THE." <b>";
		for (reset ($delete); list ($key, $value) = each ($delete); ) {
			$msg.=$description[$key];
			$msg.=", ";
		}
		$msg = substr($msg,0,strlen($msg)-2);

		$msg.="</b> ".GLOBALMSG_RECORD_DELETE_OK.". <br>\n";

	}elseif(mysql_errno()) {
		echo ucfirst(phr('ERROR')).".<br>\n";
		return 1;
	} else {
		echo GLOBALMSG_RECORD_DELETE_NONE.".<br>\n";
	}
	echo $msg;
	return 0;
}

function delete_log_rows($delete){
	require("./mgmt_start.php");

// next is only a ref line. to be deleted.
//DELETE FROM `account_mgmt_main` WHERE `id`='25' OR `id`='26';
	$firstline=1;
	$counter=0;

	if(!is_array($delete)){
		echo GLOBALMSG_RECORD_DELETE_NONE.".<br>\n";
		return 1;
	}
	$table=$GLOBALS['table_prefix'].'account_log';
	$query="DELETE FROM $table WHERE ";
	for (reset ($delete); list ($key, $value) = each ($delete); ) {
		if($firstline) {
			$query.="`payment`='".$key."'";
			$firstline=0;
		} else {
			$query.=" OR `payment`='".$key."'";
		}
		$description[$key]=$key;
		$counter++;
	}
	// echo "<br>Query SQL:<br>".$query."<br>\n";

	$res = mysql_db_query ($_SESSION['common_db'],$query);
	$num_affected=mysql_affected_rows();

	if ($num_affected!=0 && $counter>1) {
		echo("<META HTTP-EQUIV=\"Refresh\" CONTENT=\"".get_conf(__FILE__,__LINE__,"refresh_time_management")."; URL=index.php\">");
		$msg = GLOBALMSG_RECORD_THE_MANY." <b>";
		for (reset ($delete); list ($key, $value) = each ($delete); ) {
			$msg.=$description[$key];
			$msg.=", ";
		}
		$msg = substr($msg,0,strlen($msg)-2);

		$msg.="</b> ".GLOBALMSG_RECORD_DELETE_OK_FROM_LOG_MANY
		.". <br>\n";
	} elseif ($num_affected!=0 && $counter==1) {
		echo("<META HTTP-EQUIV=\"Refresh\" CONTENT=\"".get_conf(__FILE__,__LINE__,"refresh_time_management")."; URL=index.php\">");
		$msg = GLOBALMSG_RECORD_THE_MANY." <b>";
		for (reset ($delete); list ($key, $value) = each ($delete); ) {
			$msg.=$description[$key];
			$msg.=", ";
		}
		$msg = substr($msg,0,strlen($msg)-2);

		$msg.="</b> ".GLOBALMSG_RECORD_DELETE_OK_FROM_LOG.". <br>\n";

	}elseif(mysql_errno()) {
		echo ucfirst(phr('ERROR')).".<br>\n";
		return 1;
	} else {
		echo GLOBALMSG_RECORD_DELETE_NONE.".<br>\n";
	}
	echo $msg;
	return 0;
}

function table_receipt($orderby){
	echo "<form name=\"form1\" action=\"receipt.php\" method=\"post\">\n";
	echo "<input type=\"hidden\" name=\"command\" value=\"delete\">\n";

	if($orderby==""){
		$orderby="timestamp";
	}
	echo "<table bgcolor=\"".color(-1)."\">\n";
	echo "<thead ><tr>
	<td></td>
	<td><a href=\"receipt.php?command=list&orderby=id\">".ucfirst(phr('ID'))."</a></td>
	<td><a href=\"receipt.php?command=list&orderby=internal_id\">".ucphr('INTERNAL_RECEIPT_ID')."o</a></td>
	<td><a href=\"receipt.php?command=list&orderby=timestamp\">".ucfirst(phr('DATE'))."</a></td>
	<td>".ucfirst(phr('TIME'))."</td>
	<td><a href=\"receipt.php?command=list&orderby=amount\">".ucfirst(phr('AMOUNT'))."</a></td>
	<td><a href=\"receipt.php?command=list&orderby=type\">".ucfirst(phr('TYPE'))."</a></td>
	<td><a href=\"receipt.php?command=list&orderby=annulled\">".ucfirst(phr('NOTE'))."</a></td>
	</tr></thead>
	<tbody>\n";

	$i=0;
	$table=$GLOBALS['table_prefix'].'account_receipts';
	$query="SELECT * FROM $table ORDER BY `$orderby`";
	$res=mysql_db_query($_SESSION['common_db'],$query);
	if(mysql_num_rows($res)){
		while($row=mysql_fetch_array($res)){
			$date=substr($row['timestamp'],6,2)."/";
			$date.=substr($row['timestamp'],4,2)."/";
			$date.=substr($row['timestamp'],0,4);
			$time=substr($row['timestamp'],8,2).":";
			$time.=substr($row['timestamp'],10,2).":";
			$time.=substr($row['timestamp'],12,2);

			switch($row['type']){
				case 1: $type="Ricevuta"; break;
				case 2: $type="Fattura"; break;
				case 3: $type="Scontrino"; break;
			}

			$color=color($i);
			if($row['annulled']){
				echo "<tr bgcolor=\"$color\">
				<td><input name=\"delete[".$row['id']."]\" type=\"checkbox\"></td>
				<td><s>".$row['id']."</s></td>
				<td><s><a href=\"receipt.php?command=show&id=".$row['id']."\">".$row['internal_id']."</a></s></td>
				<td><s>".$date."</s></td>
				<td><s>".$time."</s></td>
				<td><s>".$row['amount']."</s></td>
				<td><s>".$type."</s></td>
				<td><s>".ucphr('ANNULLED')."</s></td>
				</tr>\n";
			} else {
				echo "<tr bgcolor=\"$color\">
				<td><input name=\"delete[".$row['id']."]\" type=\"checkbox\"></td>
				<td>".$row['id']."</td>
				<td><a href=\"receipt.php?command=show&id=".$row['id']."\">".$row['internal_id']."</a></td>
				<td>".$date."</td>
				<td>".$time."</td>
				<td>".$row['amount']."</td>
				<td>".$type."</td>
				<td></td>
				</tr>\n";
			}
			$i++;
		}
	}
	echo "</tbody></table>";
	echo "<input type=\"submit\" value=\"".GLOBALMSG_RECORD_DELETE_SELECTED."\">";

	echo "</form>\n";

}

function show_receipt($id){
	if(!$id) return 1;
	$table=$GLOBALS['table_prefix'].'account_mgmt_main';
	$query="SELECT * FROM $table WHERE `id`='$id'";
	$res=mysql_db_query($_SESSION['common_db'],$query);
	$row=mysql_fetch_array($res);
	if($row['annulled']){
			switch($row['type']){
				case 4: echo ucphr('RECEIPT_ANNULLED_OK').".<br>\n"; break;
				case 3: echo ucphr('INVOICE_ANNULLED_OK').".<br>\n"; break;
				case 5: echo ucphr('BILL_ANNULLED_OK').".<br>\n"; break;
			}
		echo GLOBALMSG_RECORD_DELETE_OK_FROM_LOG_MANY_2.".<br><br>";
		echo "<form name=\"form1\" action=\"receipt.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"command\" value=\"delete\">\n";
		echo "<input type=\"hidden\" name=\"delete[".$id."]\" value=\"1\">\n";
		echo "<input type=\"submit\" value=\"".GLOBALMSG_RECORD_DELETE."\">";
		echo "</form>\n";
		return 0;
	}

	$table=$GLOBALS['table_prefix'].'account_log';
	$query="SELECT * FROM $table WHERE `payment`='$id'";
	$res=mysql_db_query($_SESSION['common_db'],$query);
	if(!mysql_num_rows($res)) return 2;

	echo "<table bgcolor=\"".color(-1)."\">\n";
	echo "<thead ><tr>
	<td>".ucfirst(phr('QUANTITY'))."</a></td>
	<td>".ucfirst(phr('DESCRIPTION'))."</td>
	<td>".ucfirst(phr('AMOUNT'))."</td>
	</tr></thead>
	<tbody>\n";

	$i=0;
	while($row=mysql_fetch_array($res)){
		if($row['operation']==1){
			$ingred = new ingredient($row['ingredient']);
			$description="    ".ucphr('PLUS')." ".$ingred->name($_SESSION['language']);
			unset($ingred);
		} elseif($row['operation']==-1){
			$ingred = new ingredient($row['ingredient']);
			$description="    ".ucphr('MINUS')." ".$ingred->name($_SESSION['language']);
			unset($ingred);
		} else {
			$dish = new dish($row['dish']);
			$description=$dish->name($_SESSION['language']);
			unset($dish);
		}
		$total+=$row['price'];

		$color=color($i);
		echo '<tr bgcolor="',$color,'">
		<td>',$row['quantity'],'</td>
		<td>',$description,'</td>
		<td align="right">',$row['price'],'</td>
		</tr>
		';
		$i++;
	}
	echo '<tr bgcolor="',color(-1),'">
	<td></td>
	<td>'.ucfirst(phr('TOTAL')).'</td>
	<td align="right">',sprintf("%0.2f",$total),'</td>
	</tr>
	';
	echo "</tbody></table>";
	echo "<form name=\"form1\" action=\"receipt.php\" method=\"post\">\n";
	echo "<input type=\"hidden\" name=\"command\" value=\"delete\">\n";
	echo "<input type=\"hidden\" name=\"delete[".$id."]\" value=\"1\">\n";
	echo "<input type=\"submit\" value=\"".GLOBALMSG_RECORD_DELETE."\">";
	echo "</form>\n";
	echo "<form name=\"form1\" action=\"receipt.php\" method=\"post\">\n";
	echo "<input type=\"hidden\" name=\"command\" value=\"annul\">\n";
	echo "<input type=\"hidden\" name=\"annul[".$id."]\" value=\"1\">\n";
	echo "<input type=\"submit\" value=\"".ucphr('ANNULL')."\">";
	echo "</form>\n";

}




?>
