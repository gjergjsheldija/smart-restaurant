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

class matrix {
	// function found on phpcoded.com (author _Alex)
	function invert ($matrix) {
		$size = count($matrix);
		
		for( $i = 0; $i < $size; $i++ ) {
			for( $j = 0; $j < $size; $j++ ) {
				$enhet[$i][$j] = 0.0;
			}
			$enhet[$i][$i] = 1.0;
		}
	
		for( $i = 0; $i <$size; $i++ ) {
			$l = $matrix[$i][$i];
			if($l==0.0) {
				break;
			} else {
				for( $j = 0; $j <= ($size-1); $j++ ) {
					$matrix[$i][$j] = $matrix[$i][$j]/$l;
					$enhet[$i][$j] = $enhet[$i][$j]/$l;
				}
				for( $a = 0; $a < $size; $a++ ) {
					if( ($a-$i) != 0 ) {
						$b = $matrix[$a][$i];
						for( $j = 0; $j < $size ; $j++ ) {
							$matrix[$a][$j] = $matrix[$a][$j] - $b*$matrix[$i][$j];
							$enhet[$a][$j] = $enhet[$a][$j] - $b*$enhet[$i][$j];
						}
					}
				}
			}
		}
		return $enhet;
	}

	function solve ($matrix, $known) {
		$size = count($matrix);
		
		$inverted=matrix::invert($matrix);
		matrix::show_matrix($matrix);
		matrix::show_matrix($inverted);
		for( $i = 0; $i < $size; $i++ ) {
			$res[$i]=0;
		}
	
		for( $i = 0; $i < $size; $i++ ) {
			for( $j = 0; $j < $size; $j++ ) {
				$res[$i]=$res[$i]+$inverted[$i][$j]*$known[$j];
			}
		}
	
		return $res;
	}

	function show_matrix($matrix){
		$size = count($matrix);
		echo "<table border=1>\n";
		for( $i = 0; $i < $size; $i++ )
		{
			echo "<tr>";
			for( $j = 0; $j < $size; $j++ )
			{
				echo "<td>".$matrix[$i][$j]. "</td>";
			}
			echo "</tr>";
		}
		echo "</table>";
	}
	
	function show_vector($matrix){
		$size = count($matrix);
		echo "<table border=1>\n";
		for( $i = 0; $i < $size; $i++ )
		{
			echo "<tr>";
			echo "<td>".$matrix[$i]. "</td>";
			echo "</tr>";
		}
		echo "</table>";
	}
	
	/*
	Use
	
	$matrix = 
	array(
		array(1,2,3),
		array(1,-2,-1),
		array(1,-2,-1)
	);
	$known=array(1,0,0);
	
	echo "<br>Matrix:<br><br>";
	show_matrix($matrix);
	
	echo "<br>Known:<br><br>";
	show_vector($known);
	
	$sol=solve($matrix,$known);
	echo "<br>Solution:<br><br>";
	show_vector($sol,$size);
	*/
}
?>