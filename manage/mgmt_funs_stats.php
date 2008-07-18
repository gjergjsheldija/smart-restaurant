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

function statistics_show(){
	$timer=0;
	require("./mgmt_start.php");

	$table=$GLOBALS['table_prefix'].'account_log';
	$query = "SELECT * FROM $table";
	$query .= " WHERE `datetime`>=".$_SESSION['timestamp']['start'] ;
	$query .= " AND `datetime`<=".$_SESSION['timestamp']['end'];

	$inizio=microtime();
	$res = mysql_db_query ($_SESSION['common_db'],$query);
	$fine=microtime();
	$timer+=elapsed_time($inizio,$fine);

	$inizio=microtime();
	//totals by sector, waiter, stats on dishes sold
	while($row = mysql_fetch_array ($res)) {
		$fine=microtime();
		$timer+=elapsed_time($inizio,$fine);

/*		print_r($row);
		echo "<br>";*/
		
		$price=$row['price'];
		$totals['revenue']+=$price;
		$totals['number']++;

		$quantity=$row['quantity'];

		$waiter=strtolower($row['waiter']);
		if(empty($waiter)) $waiter='undefined';
		$waiters['revenue'][$waiter]+=$price;
		
		
		$payment=$row['payment'];
		if($payment!=0){
			$payments['number'][$payment]++;
		}
		//money by destination
		$destination=strtolower($row['destination']);
		$destinations['revenue'][$destination]+=$price;
		
		
/*		print_r($destinations['revenue']);
		echo" - ";
		print_r($destinations['revenue'][$destination]);
		echo "<br>";*/
		
		//money by dishes
		$dish=strtolower($row['dish']);
		if($dish!="") {
			$dishes['number'][$dish]+=$quantity;
			$dishes['revenue'][$dish]+=$price;
		}

		$ingredient=strtolower($row['ingredient']);
		$oper=$row['operation'];
		if($ingredient!="" && $oper==1) {
			$ingredsplus['number'][$ingredient]+=$quantity;
			$ingredsplus['revenue'][$ingredient]+=$price;
		} elseif($ingredient!="" && $oper==-1) {
			$ingredsminus['number'][$ingredient]+=$quantity;
			$ingredsminus['revenue'][$ingredient]+=$price;
		}

		$inizio=microtime();
	}
	$fine=microtime();
	$timer+=elapsed_time($inizio,$fine);

	//mizuko
	echo "<b><a href=\"#\" onClick='printPage();'>Printo Faqen</a></b>";
	//end mizuko
	
/*	//waiters
	if(is_array($waiters['revenue'])) {
		echo "<br><br>".ucfirst(phr('STATS_TOTAL_WAITERS')).":<br>";
		ksort($destinations['revenue']);
		echo "<table>\n";
		for (reset ($waiters['revenue']); list ($key, $value) = each ($waiters['revenue']); ) {
			if($key) {
				if(is_numeric($key)) {
					$user = new user($key);
					$name = $user -> name ();
				 } else $name=ucfirst($key);
				$value=sprintf("%01.2f",$value);
				echo "<tr><td>$name</td><td>$value ".country_conf_currencies(true)."</td></tr>\n";
			}
		}
		echo "</table>\n\n\n";
	}*/
	
	echo "<br><br>".ucfirst(phr('STATS_TOTAL_WAITERS')).":<br><br>";
	
	$querykamerier = "SELECT waiter, destination,sum(price) as `shuma` FROM $table";
	$querykamerier .= " WHERE `datetime`>=".$_SESSION['timestamp']['start'] ;
	$querykamerier .= " AND `datetime`<=".$_SESSION['timestamp']['end'];
	$querykamerier .= " GROUP BY waiter, destination";

	$reskamerier = mysql_db_query ($_SESSION['common_db'],$querykamerier);
	
	echo "<table>\n";

	while( $row = mysql_fetch_array ($reskamerier) ) {

		echo "<tr>\n";
		
		//waiter
		if ($kamerieri != $row['waiter'] ) {

			if($totalikamerier > 0) {
				echo "</tr><tr><td><b>Totali</b></td><td></td><td><b>".sprintf("%01.2f",$totalikamerier) ."</b> ".country_conf_currencies(true)."</td></tr><tr>\n";
			}
			
			if(is_numeric($row['waiter'])) {
				$user = new user($row['waiter']);
				$name = $user -> name ();
				echo "<td>" . $name . "</td>";
			} 
			$totalikamerier = 0;
		} else echo "<td>&nbsp;</td>";
		
		//destination
		if(is_numeric($row['destination'])) {
			$dest = new printer($row['destination']);
			$name = $dest -> name ();
			echo "<td>" . $name. "</td>";;

		} else echo "<td>&nbsp;</td>";
		
		//sum
		echo "<td>" . $row['shuma']. country_conf_currencies(true)."</td>";;

		$totalikamerier+=$row['shuma'];
		
		echo "</tr>";

		$kamerieri = $row['waiter'];	
	}

	if($totalikamerier > 0) {
		echo "</tr><tr><td><b>Totali</b></td><td></td><td><b>".sprintf("%01.2f",$totalikamerier) ."</b> ".country_conf_currencies(true)."</td></tr><tr>\n";
	}	

	echo "</table>\n";
	
	// sector
	if(is_array($destinations['revenue'])) {
		echo "<br><br>".ucfirst(GLOBALMSG_STATS_TOTAL_DEPTS).":<br>";
		ksort($destinations['revenue']);
		echo "<table>\n";
		for (reset ($destinations['revenue']); list ($key, $value) = each ($destinations['revenue']); ) {
			if($key) {
				if(is_numeric($key)) {
					$dest = new printer($key);
					$name = $dest -> name ();
				 } else $name=ucfirst($key);
				$value=sprintf("%01.2f",$value);
				echo "<tr><td>$name</td><td>$value ".country_conf_currencies(true)."</td></tr>\n";
			}
		}
		echo "</table>\n";
	}
	
	//total
	$totals['revenue']=sprintf("%01.2f",$totals['revenue']);
	if($totals['revenue']) {
		if ($dataset!="total") {
			echo "<br><br>".ucfirst(GLOBALMSG_STATS_TOTAL_DEPTS).":<br>";
		} else {
			echo "<br><br>".ucfirst(GLOBALMSG_STATS_TOTAL_DEPTS).":<br>";
		}
		echo "<b>".$totals['revenue']."</b> ".country_conf_currencies(true);
	}	
	
	//ingredients
	if(is_array($dishes['number'])) {
		echo "<br><br>".ucfirst(GLOBALMSG_STATS_DISHES_ORDERED).":<br>";
		ksort($dishes['number']);
		//krsort($dishes_global);
		//asort($dishes_global);
		//arsort($dishes_global);
		echo "<table>\n";
		for (reset ($dishes['number']); list ($key, $value) = each ($dishes['number']); ) {
			if($key) {
				if(is_numeric($key)) {
					if($key==SERVICE_ID) {
						$dishname = ucphr('SERVICE_FEE');
					} elseif($key==DISCOUNT_ID) {
						$dishname = ucphr('DISCOUNT');
					} else {
						$dish = new dish($key);
						$dishname = $dish -> name ($_SESSION['language']);
					}
				 } else $dishname=ucfirst($key);
				 
				$dishquantity=$value;
				$dishprice=sprintf("%01.2f",$dishes['revenue'][$key]);
				echo "<tr><td align=right>$dishquantity</td><td>$dishname</td><td align=right>$dishprice</td></tr>\n";
			}
		}
		echo "</table>\n";
	}

	if(is_array($ingredsplus['number'])) {
		echo "<br><br>".ucfirst(GLOBALMSG_STATS_INGREDIENTS_ADDED).":<br>";
		ksort($ingredsplus['number']);
		echo "<table>\n";
		for (reset ($ingredsplus['number']); list ($key, $value) = each ($ingredsplus['number']); ) {
			if($key) {
				if(is_numeric($key)) {
					$ingred = new ingredient($key);
					$ingredname = $ingred -> name ($_SESSION['language']);
				 } else $ingredname=ucfirst($key);
				echo "<tr><td>$value</td><td>$ingredname</td><td>".sprintf("%01.2f",$ingredsplus['revenue'][$key])." ".country_conf_currencies(true)."</td></tr>\n";
			}
		}
		echo "</table>\n";
	}
	
	if(is_array($ingredsminus['number'])) {
		echo "<br><br>".ucfirst(GLOBALMSG_STATS_INGREDIENTS_REMOVED).":<br>";
		ksort($ingredsminus['number']);
		echo "<table>\n";
		for (reset ($ingredsminus['number']); list ($key, $value) = each ($ingredsminus['number']); ) {
			if($key) {
				if(is_numeric($key)) {
					$ingred = new ingredient($key);
					$ingredname = $ingred -> name ($_SESSION['language']);
				 } else $ingredname=ucfirst($key);
				echo "<tr><td>$value</td><td>$ingredname</td><td>".sprintf("%01.2f",$ingredsminus['revenue'][$key])." ".country_conf_currencies(true)."</td></tr>\n";
			}
		}
		echo "</table>\n";
	}
	

/*	echo "<hr><b>".$totals['number']."</b> ".GLOBALMSG_STATS_RECORDS_SCANNED.".<br>
	<b>".round($timer,5)."</b> ".GLOBALMSG_STATS_MYSQL_TIME.".<br>
	<hr>";
	echo "<br><br>";*/
}

?>
