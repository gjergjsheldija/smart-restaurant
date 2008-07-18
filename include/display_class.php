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

class display {
	var $show_head;
	
	var $widths;
	var $links;
	var $clicks;
	var $highlight=true;
	
	function list_table () {
		$rows = $this->rows;
		if(empty($rows)) return '';
	
		$row = 0;
		
		$num_rows = count ($rows);
		
		$o = '';
		$o .= '
<table cellspacing="0" width="100%" class="admin_table">';
	
		// HEAD
		if ($this->show_head) {
			$o .= '
	<thead>
		<tr class="admin_th">';
			foreach($rows[$row] as $col => $value) {
				$o .= '
			<th';
				if(isset($this->widths[$row][$col]) && !empty ($this->widths[$row][$col])) {
					$o .= ' width="'.$this->widths[$row][$col].'"';
				}
				if(isset($this->clicks[$row][$col]) && !empty ($this->clicks[$row][$col])) {
					$o .= ' onclick="'.$this->clicks[$row][$col].'"';
				}
				if(isset($this->properties[$row][$col]) && !empty ($this->properties[$row][$col])) {
					$o .= ' '.$this->properties[$row][$col].'"';
				}
				$o .= '>';
				
				if(isset($this->links[$row][$col]) && !empty ($this->links[$row][$col])) {
					$o .= '<a href="'.$this->links[$row][$col].'">';
				}
				
				$o .= $rows[$row][$col];
				
				if(isset($this->links[$row][$col]) && !empty ($this->links[$row][$col])) {
					$o .= '</a>';
				}
				$o .= '</th>';
			}
			
			$o .= '
		</tr>
	</thead>';
		$row++;
		}
		
		// END OF HEAD
		
		// BODY
		$o .= '
	<tbody>';

		$suff = 0;
		
		while($row<$num_rows) {
		$o .= '
		<tr';
		if($this->highlight) {
			$o .= ' onMouseOver="change_class(this,\'admin_tr_highlight\');" onMouseOut="change_class(this,\'admin_tr_'.$suff.'\');"';
		}
		$o .= ' class="admin_tr_'.$suff.'">';
		foreach($rows[$row] as $col => $value) {
			$o .= '
			<td class="admin_td_'.$suff.'"';
			if(isset($this->widths[$row][$col]) && !empty ($this->widths[$row][$col])) {
				$o .= ' width="'.$this->widths[$row][$col].'"';
			}
			if(isset($this->clicks[$row][$col]) && !empty ($this->clicks[$row][$col])) {
				$o .= ' onclick="'.$this->clicks[$row][$col].'"';
			}
			if(isset($this->properties[$row][$col]) && !empty ($this->properties[$row][$col])) {
				$o .= ' '.$this->properties[$row][$col].'"';
			}
			$o .= '>';
			
			if(isset($this->links[$row][$col]) && !empty ($this->links[$row][$col])) {
				$o .= '<a href="'.$this->links[$row][$col].'">';
			}
			
			$o .= $rows[$row][$col];
			
			if(isset($this->links[$row][$col]) && !empty ($this->links[$row][$col])) {
				$o .= '</a>';
			}
			
			$o .= '</td>';
		}
		
		$o .= '
		</tr>';
		
		if($suff) $suff=0;
		else $suff=1;
		
		$row++;
		}
		
		
		$o .= '
	</tbody>
</table>';
		return $o;
	}


}
?>