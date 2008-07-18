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
function pdf_generator($query){
	//require("./mgmt_start.php");

	$res = mysql_db_query ($_SESSION['common_db'],$query);
	if($errno=mysql_errno()) {
		$msg="Error in pdf_printable - ";
		$msg.='mysql: '.mysql_errno().' '.mysql_error();
		error_msg(__FILE__,__LINE__,$msg);
	}

	if(!mysql_num_rows($res)) return 0;

	$i=0;
	while($row = mysql_fetch_array ($res)) {
		$date['year']=substr($row['date'],0,4);
		$date['month']=substr($row['date'],4,2);
		$date['day']=substr($row['date'],6,2);
		$date['hour']=substr($row['date'],8,2);
		$date['minute']=substr($row['date'],10,2);
		$date['second']=substr($row['date'],12,2);

		$description=$row['description'];
		$cash_amount=$row['cash_amount'];
		$cash_vat_amount=$row['cash_vat_amount'];
		$bank_amount=$row['bank_amount'];
		$bank_vat_amount=$row['bank_vat_amount'];
		$debit_amount=$row['debit_amount'];
		$debit_vat_amount=$row['debit_vat_amount'];
		$id=$row['id'];
		$debit=$row['debit'];
		$who=$row['who'];

		if($row['associated_invoice']){
			$show_id=$row['associated_invoice'];
			$is_payment=true;
		} else {
			$show_id=$id;
			$is_payment=false;
		}

		$mgmt_type = new mgmt_type($row['type']);
		$type=$mgmt_type -> name($_SESSION['language']);
		unset($mgmt_type);

		$cash_total_amount+=$cash_amount;
		$cash_total_vat_amount+=$cash_vat_amount;

		if($cash_amount>0){
			$cash_plus_amount=sprintf("%01.2f",$cash_amount);
			$cash_minus_amount=" ";
		} elseif($cash_amount<0) {
			$cash_plus_amount=" ";
			$cash_minus_amount=sprintf("%01.2f",$cash_amount);
		} else {
			$cash_plus_amount=" ";
			$cash_minus_amount=" ";
		}

		$bank_total_amount+=$bank_amount;
		$bank_total_vat_amount+=$bank_vat_amount;

		if($bank_amount>0){
			$bank_plus_amount=sprintf("%01.2f",$bank_amount);
			$bank_minus_amount=" ";
		} elseif($bank_amount<0) {
			$bank_plus_amount=" ";
			$bank_minus_amount=sprintf("%01.2f",$bank_amount);
		} else {
			$bank_plus_amount=" ";
			$bank_minus_amount=" ";
		}
		$debit_total_amount+=$debit_amount;
		$debit_total_vat_amount+=$debit_vat_amount;

		if($debit_amount!=0){
			$debit_amount=sprintf("%01.2f",$debit_amount);
		} else {
			$debit_amount=" ";
		}

		$data[]=array(
		ucfirst(phr('DATE'))=>$date["day"]."/".$date["month"]."/".$date["year"],
		ucfirst(phr('WHO'))=>$who,
		ucfirst(phr('DESCRIPTION'))=>$description,
		ucfirst(phr('TYPE'))=>$type,
		ucfirst(phr('CASH'))."\n".ucfirst(phr('INCOMINGS'))=>$cash_plus_amount,
		ucfirst(phr('CASH'))."\n".ucfirst(GLOBALMSG_OUTGOING_MANY)=>$cash_minus_amount,
		ucfirst(phr('CASH'))."\n".ucfirst(phr('TAXES'))=>$cash_vat_amount,
		ucfirst(phr('BANK'))."\n".ucfirst(phr('INCOMINGS'))=>$bank_plus_amount,
		ucfirst(phr('BANK'))."\n".ucfirst(GLOBALMSG_OUTGOING_MANY)=>$bank_minus_amount,
		ucfirst(phr('BANK'))."\n".ucfirst(phr('TAXES'))=>$bank_vat_amount,
		ucfirst(phr('DEBTS'))."\n".ucfirst(phr('AMOUNT'))=>$debit_amount,
		ucfirst(phr('DEBTS'))."\n".ucfirst(phr('TAXES'))=>$debit_vat_amount
		);

		$i++;
	}


	for($i=0;$i<2;$i++){
		if($cash_total_amount>0){
			$cash_total_plus_amount=sprintf("%01.2f",$cash_total_amount);
			$cash_total_minus_amount=" ";
		} elseif($cash_total_amount<0) {
			$cash_total_plus_amount=" ";
			$cash_total_minus_amount=sprintf("%01.2f",$cash_total_amount);
		} else {
			$cash_total_plus_amount=sprintf("%01.2f",0);
			$cash_total_minus_amount=" ";
		}
		$cash_total_vat_amount=sprintf("%01.2f",$cash_total_vat_amount);

		if($bank_total_amount>0){
			$bank_total_plus_amount=sprintf("%01.2f",$bank_total_amount);
			$bank_total_minus_amount=" ";
		} elseif($bank_total_amount<0) {
			$bank_total_plus_amount=" ";
			$bank_total_minus_amount=sprintf("%01.2f",$bank_total_amount);
		} else {
			$bank_total_plus_amount=sprintf("%01.2f",0);
			$bank_total_minus_amount=" ";
		}
		$bank_total_vat_amount=sprintf("%01.2f",$bank_total_vat_amount);

		if($debit_total_amount!=0){
			$debit_total_amount=sprintf("%01.2f",$debit_total_amount);
		} else {
			$debit_total_amount=sprintf("%01.2f",0);
		}
		$debit_total_vat_amount=sprintf("%01.2f",$debit_total_vat_amount);

	}


	$data[]=array(
	ucfirst(phr('DATE'))=>'',
	ucfirst(phr('WHO'))=>'',
	ucfirst(phr('DESCRIPTION'))=>'',
	ucfirst(phr('TYPE'))=>ucfirst(phr('TOTAL')),
	ucfirst(phr('CASH'))."\n".ucfirst(phr('INCOMINGS'))=>$cash_total_plus_amount,
	ucfirst(phr('CASH'))."\n".ucfirst(GLOBALMSG_OUTGOING_MANY)=>$cash_total_minus_amount,
	ucfirst(phr('CASH'))."\n".ucfirst(phr('TAXES'))=>$cash_total_vat_amount,
	ucfirst(phr('BANK'))."\n".ucfirst(phr('INCOMINGS'))=>$bank_total_plus_amount,
	ucfirst(phr('BANK'))."\n".ucfirst(GLOBALMSG_OUTGOING_MANY)=>$bank_total_minus_amount,
	ucfirst(phr('BANK'))."\n".ucfirst(phr('TAXES'))=>$bank_total_vat_amount,
	ucfirst(phr('DEBTS'))."\n".ucfirst(phr('AMOUNT'))=>$debit_total_amount,
	ucfirst(phr('DEBTS'))."\n".ucfirst(phr('TAXES'))=>$debit_total_vat_amount
	);

	return $data;
}

function printable_write_pdf($data,$title) {
	$pdf =& new Cezpdf('a4','landscape');
	$pdf->selectFont('../fonts/Courier.afm');
	//$pdf->selectFont(ROOTDIR.'/fonts/Times-Roman.afm');
	//$pdf->selectFont(ROOTDIR.'/fonts/Helvetica.afm');
	$pdf->ezStartPageNumbers(800,15,12,'','',1);
	$pdf->ezText("$title\n",12,array('justification'=>'center'));
	$pdf->ezTable($data);
	//$pdf->ezStopPageNumbers();
	$pdf->ezStream();
}

function table_generator_printable($query){
	require("./mgmt_start.php");

	$res = mysql_db_query ($_SESSION['common_db'],$query);
	if($errno=mysql_errno()) {
		$msg="Error in table_generator_printable - ";
		$msg.='mysql: '.mysql_errno().' '.mysql_error();
		error_msg(__FILE__,__LINE__,$msg);
	}

	$i=0;
	while($row = mysql_fetch_array ($res)) {

		if($i%get_conf(__FILE__,__LINE__,"management_printable_table_header_repeater")==0){
			if($i){
?>
	</tbody>
</table>
<div class="break"></div>

<?php
			}

?>
<table class="mgmt_printable_table" width="100%">
	<tbody>
	<tr align="center">
		<td class="mgmt_pintable_tablebg" rowspan="2" valign=middle><b><?php echo ucfirst(phr('DATE')); ?></b></td>
		<td class="mgmt_pintable_tablebg" rowspan="2" valign=middle><b><?php echo ucfirst(phr('WHO')); ?></b></td>
		<td class="mgmt_pintable_tablebg" rowspan="2" valign=middle><b><?php echo ucfirst(phr('DESCRIPTION')); ?></b></td>
		<td class="mgmt_pintable_tablebg" rowspan="2" valign=middle><b><?php echo ucfirst(phr('TYPE')); ?></b></td>
		<td class="mgmt_pintable_tablebg" colspan=3><b><?php echo ucfirst(phr('CASH')); ?></b></td>
		<td class="mgmt_pintable_tablebg" colspan=3><b><?php echo ucfirst(phr('BANK')); ?></b></td>
		<td class="mgmt_pintable_tablebg" colspan=2><b><?php echo ucfirst(phr('DEBTS')); ?></b></td>
	</tr>
	<tr align="center">
		<td class="mgmt_pintable_tablebg"><?php echo ucfirst(phr('INCOMINGS')); ?></td>
		<td class="mgmt_pintable_tablebg"><?php echo ucfirst(GLOBALMSG_OUTGOING_MANY); ?></td>
		<td class="mgmt_pintable_tablebg"><?php echo ucfirst(phr('TAXES')); ?></td>
		<td class="mgmt_pintable_tablebg"><?php echo ucfirst(phr('INCOMINGS')); ?></td>
		<td class="mgmt_pintable_tablebg"><?php echo ucfirst(GLOBALMSG_OUTGOING_MANY); ?></td>
		<td class="mgmt_pintable_tablebg"><?php echo ucfirst(phr('TAXES')); ?></td>
		<td class="mgmt_pintable_tablebg"><?php echo ucfirst(phr('AMOUNT')); ?></td>
		<td class="mgmt_pintable_tablebg"><?php echo ucfirst(phr('TAXES')); ?></td>
	</tr>

<?		}

		$date['year']=substr($row['date'],0,4);
		$date['month']=substr($row['date'],4,2);
		$date['day']=substr($row['date'],6,2);
		$date['hour']=substr($row['date'],8,2);
		$date['minute']=substr($row['date'],10,2);
		$date['second']=substr($row['date'],12,2);

		$description=$row['description'];
		$cash_amount=$row['cash_amount'];
		$cash_vat_amount=$row['cash_vat_amount'];
		$bank_amount=$row['bank_amount'];
		$bank_vat_amount=$row['bank_vat_amount'];
		$debit_amount=$row['debit_amount'];
		$debit_vat_amount=$row['debit_vat_amount'];
		$id=$row['id'];
		$debit=$row['debit'];
		$who=$row['who'];

		if($row['associated_invoice']){
			$show_id=$row['associated_invoice'];
			$is_payment=true;
		} else {
			$show_id=$id;
			$is_payment=false;
		}

		$mgmt_type = new mgmt_type($row['type']);
		$type=$mgmt_type -> name($_SESSION['language']);
		unset($mgmt_type);
		/*
		$table=$GLOBALS['table_prefix'].'mgmt_types';
		$res_local = mysql_db_query ($_SESSION['common_db'],"SELECT * FROM $table WHERE `id`='".$row['type']."'");
		$row_local = mysql_fetch_array($res_local);
		$type=$row_local['name'];
		mysql_free_result($res_local);
		*/

		$cash_total_amount+=$cash_amount;
		$cash_total_vat_amount+=$cash_vat_amount;

		if($cash_amount>0){
			$cash_plus_amount=sprintf("%01.2f",$cash_amount);
			$cash_minus_amount=" ";
		} elseif($cash_amount<0) {
			$cash_plus_amount=" ";
			$cash_minus_amount=sprintf("%01.2f",$cash_amount);
		} else {
			$cash_plus_amount=" ";
			$cash_minus_amount=" ";
		}

		$bank_total_amount+=$bank_amount;
		$bank_total_vat_amount+=$bank_vat_amount;

		if($bank_amount>0){
			$bank_plus_amount=sprintf("%01.2f",$bank_amount);
			$bank_minus_amount=" ";
		} elseif($bank_amount<0) {
			$bank_plus_amount=" ";
			$bank_minus_amount=sprintf("%01.2f",$bank_amount);
		} else {
			$bank_plus_amount=" ";
			$bank_minus_amount=" ";
		}
		$debit_total_amount+=$debit_amount;
		$debit_total_vat_amount+=$debit_vat_amount;

		if($debit_amount!=0){
			$debit_amount=sprintf("%01.2f",$debit_amount);
		} else {
			$debit_amount=" ";
		}

		echo "<tr class=\"".printable_css($i)."\">\n";
		echo "<td class=\"".printable_css($i)."\">".$date["day"]."/".$date["month"]."/".$date["year"]."</td>\n";
		echo "<td class=\"".printable_css($i)."\">".$who."</td>\n";
		if($row['internal_id']!="" && $row['annulled']==1) {
			echo "<td class=\"".printable_css($i)."\"><s>$description - ".ucphr('ANNULLED_ABBR')."</s></td>\n";
		} elseif($row['internal_id']!="" && $row['annulled']==0) {
			echo "<td class=\"".printable_css($i)."\">$description</td>\n";
		} else {
			echo "<td class=\"".printable_css($i)."\">$description</td>\n";
		}
		echo "<td class=\"".printable_css($i)."\">".$type."</td>\n";
		echo "<td class=\"".printable_css($i)."\">".$cash_plus_amount."</td>\n";
		echo "<td class=\"".printable_css($i)."\">".$cash_minus_amount."</td>\n";
		echo "<td class=\"".printable_css($i)."\">".$cash_vat_amount."</td>\n";
		echo "<td class=\"".printable_css($i)."\">".$bank_plus_amount."</td>\n";
		echo "<td class=\"".printable_css($i)."\">".$bank_minus_amount."</td>\n";
		echo "<td class=\"".printable_css($i)."\">".$bank_vat_amount."</td>\n";
		echo "<td class=\"".printable_css($i)."\">".$debit_amount."</td>\n";
		echo "<td class=\"".printable_css($i)."\">".$debit_vat_amount."</td>\n";
		echo "</tr>\n";
		$i++;
	}


	for($i=0;$i<2;$i++){
		if($cash_total_amount>0){
			$cash_total_plus_amount=sprintf("%01.2f",$cash_total_amount);
			$cash_total_minus_amount=" ";
		} elseif($cash_total_amount<0) {
			$cash_total_plus_amount=" ";
			$cash_total_minus_amount=sprintf("%01.2f",$cash_total_amount);
		} else {
			$cash_total_plus_amount=sprintf("%01.2f",0);
			$cash_total_minus_amount=" ";
		}
		$cash_total_vat_amount=sprintf("%01.2f",$cash_total_vat_amount);

		if($bank_total_amount>0){
			$bank_total_plus_amount=sprintf("%01.2f",$bank_total_amount);
			$bank_total_minus_amount=" ";
		} elseif($bank_total_amount<0) {
			$bank_total_plus_amount=" ";
			$bank_total_minus_amount=sprintf("%01.2f",$bank_total_amount);
		} else {
			$bank_total_plus_amount=sprintf("%01.2f",0);
			$bank_total_minus_amount=" ";
		}
		$bank_total_vat_amount=sprintf("%01.2f",$bank_total_vat_amount);

		if($debit_total_amount!=0){
			$debit_total_amount=sprintf("%01.2f",$debit_total_amount);
		} else {
			$debit_total_amount=sprintf("%01.2f",0);
		}
		$debit_total_vat_amount=sprintf("%01.2f",$debit_total_vat_amount);

	}
	echo "<tr>\n";
	echo "<td></td>\n";
	echo "<td></td>\n";
	echo "<td></td>\n";
	echo "<td>".ucfirst(phr('TOTAL'))."</td>\n";
	echo "<td>".$cash_total_plus_amount."</td>\n";
	echo "<td>".$cash_total_minus_amount."</td>\n";
	echo "<td>".$cash_total_vat_amount."</td>\n";
	echo "<td>".$bank_total_plus_amount."</td>\n";
	echo "<td>".$bank_total_minus_amount."</td>\n";
	echo "<td>".$bank_total_vat_amount."</td>\n";
	echo "<td>".$debit_total_amount."</td>\n";
	echo "<td>".$debit_total_vat_amount."</td>\n";
	echo "<td> </td>\n";
	echo "</tr>\n";

	echo "</tbody></table>\n";



}


?>
