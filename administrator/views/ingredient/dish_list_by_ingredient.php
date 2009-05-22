<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Smart Restaurant
 *
 * An open source application to manage restaurants
 *
 * @package		SmartRestaurant
 * @author		Gjergj Sheldija
 * @copyright	Copyright (c) 2008-2009, Gjergj Sheldija
 * @license		http://www.gnu.org/licenses/gpl.txt
 * @since		Version 1.0
 * @filesource
 * 
 *  Smart Restaurant is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, version 3 of the License.
 *
 *	Smart Restaurant is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.

 *	You should have received a copy of the GNU General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 */
?>
<?php if(isset($dish_list)) { ?>
	<h2><?=lang('dishes') ?></h2>
	<div class="basic" style="float:left;"  id="secondtable">
		<table class="zebra">
			<colgroup>
				<col style='width:600%;' />
				<col style='width:20%;' />
				<col style='width:20%;' />
			</colgroup>
			<thead>
				<tr>
					<th><?=lang('name'); ?></th>
					<th><?=lang('quantity'); ?></th>
					<th><?=lang('uom'); ?></th>
				</tr>
			</thead>
	<?php 
	$tmp = "";
	foreach($dish_list->result() as $row) {	?>
			<tr>
				<td><?=anchor('dish/edit/'.$row->id,$row->name); ?></td>					
				<td align="right"><?=$row->quantity; ?></td>
				<td align="right"><?=$row->unit_type == '2' ? 'lt' : 'kg' ;; ?></td>
			</tr>
	<?php 
		$row = $query->next_row();
	}; 
	?>
	</table>
<?php } ?>
