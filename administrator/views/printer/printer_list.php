<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Smart Restaurant
 *
 * An open source application to manage restaurants
 *
 * @package		SmartRestaurant
 * @author		Gjergj Sheldija
 * @copyright	Copyright (c) 2008, Gjergj Sheldija
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
<script type="text/javascript">
$( function(){
	$("table.zebra tr:even").addClass("even");
	$("table.zebra tr:odd").addClass("odd");
});
</script>
<div id="Container">
	<div class="Full">
		<div class="contentRight">
		<div class="contentLeft">
		<div class="col">
			<div class="Left">
				<h2><?=lang('printers') ?> :: <?=anchor('printer/newPrinter',lang('new_printer')) ?></h2>
					<table id="printerTable" class="zebra">
					<colgroup>
						<col style='width:5%;' />
						<col style='width:5%;' />
						<col style='width:5%;' />
						<col style='width:5%;' />
					</colgroup>
					<thead>
					<tr>
						<th><?=lang('name') ?></th>
						<th><?=lang('system_name') ?></th>
						<th><?=lang('driver') ?></th>
						<th><?=lang('action') ?></th>
					</tr>
					</thead>					
					<tbody>
					<?php foreach($query as $row): ?>
						<tr>
							<td><?=$row->name ?></td>
							<td align="right"><?=$row->dest ?></td>
							<td align="right"><?=$row->driver ?></td>
							<td align="right"><?=anchor_image('printer/edit/'.$row->id, '../images/administrator/edit.png');?> :: <?=anchor_image('printer/delete/'.$row->id , '../images/administrator/edit_remove.png');?></td>
						</tr>
					<? endforeach; ?>
					</tbody>
				</table>
			</div>
			<div class="Right">				
				<?php $this->load->view('printer/printer_edit') ?>
			</div>
        </div>
        </div>
		</div>
	</div>
</div>
<div class="ClearAll"></div>