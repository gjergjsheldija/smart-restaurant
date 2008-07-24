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
function vat_report(){
	require("./mgmt_start.php");

	$i=0;
	$table=$GLOBALS['table_prefix'].'account_mgmt_main';
	$query="SELECT * FROM $table";
	$query.=" WHERE `date`>=$timestamp_start AND `date`<=$timestamp_end";
	$query.=" order by `id`";

	$res=mysql_db_query($_SESSION['common_db'],$query);
	if(!mysql_num_rows($res)) return 1;

	while($row=mysql_fetch_array($res)){
		$vat_total+=$row['cash_vat_amount'];
		$vat_total+=$row['bank_vat_amount'];

		$vat_total_with_debit+=$row['cash_vat_amount'];
		$vat_total_with_debit+=$row['bank_vat_amount'];
		$vat_total_with_debit+=$row['debit_vat_amount'];
	}

	echo "
	Per il periodo indicato si devono pagare:<br><b>".country_conf_currency(true)." ".$vat_total."</b>
	 (escluse le fatture non pagate)<br>
	oppure<br>
	<b>".country_conf_currencies(true)." ".$vat_total_with_debit."</b>  (incluse le fatture non pagate) di IVA<br><br>
	";

	return 0;
}

?>
