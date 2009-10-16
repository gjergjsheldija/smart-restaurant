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
	<div class="title title-spacing"><h3><?php echo lang('dishes'); ?></h3></div>
		<table>
			<thead>
				<tr>
					<th><?php echo lang('name'); ?></th>
					<th><?php echo lang('quantity'); ?></th>
					<th><?php echo lang('uom'); ?></th>
				</tr>
			</thead>
	<?php 
	$tmp = "";
	foreach($dish_list->result() as $row) {	?>
			<tr>
				<td><?php echo anchor('dish/edit/'.$row->id,$row->name); ?></td>					
				<td align="right"><?php echo $row->quantity; ?></td>
				<td align="right"><?php echo $row->unit_type == '2' ? 'lt' : 'kg' ;; ?></td>
			</tr>
	<?php 
		$row = $query->next_row();
	}; 
	?>
	</table>
<?php } ?>
