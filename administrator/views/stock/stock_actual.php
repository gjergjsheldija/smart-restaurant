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
				<h2><?=lang('actual_state'); ?>&nbsp;&nbsp;&nbsp;<?=anchor_image(site_url()."/stock/report_actual_pdf/",'images/administrator/save-pdf.png') ?></h2>
				<?=form_open('stock/report_actual');?>
				<br /><br />
				<table class="zebra">
					<colgroup>
						<col style='width:15%;' />
						<col style='width:20%;' />
						<col style='width:10%;' />
						<col style='width:10%;' />
					</colgroup>				
					<thead>
						<tr>
							<th><?=lang('name'); ?></th>
							<th align="right"><?=lang('quantity'); ?></th>
							<th align="right"><?=lang('uom'); ?></th>
							<th align="right"><?=lang('amount'); ?></th>
						</tr>
					</thead>
				<?php 
					$total = 0; 
					foreach($stock_actual->result() as $row) { 
					$total += $row->value;
				?>
					<tr>
						<td><?=$row->name;?></td>
						<td align="right"><?=$row->quantity;?></td>
						<td align="right"><?php 
						switch( $row->unit_type ) {
							case 0;
								echo 'cp';
								break;
							case 1;
								echo 'kg';
								break;
							case 2;
								echo 'lt';
								break;
						}
						?></td>
						<td align="right"><?=$row->value;?></td>
					</tr>
				<?php } ?>	
				<tr><td colspan="4"></td></tr>
				<tr><td colspan="4" align="right"><strong><?=lang('total'); ?> : <?=$total; ?></strong></td></tr>
				</table>
				</form>
			</div>	
			</div>
			<div class="Right"></div>
        </div>
        </div>
		</div>
	</div>
</div>
<div class="ClearAll"></div>
