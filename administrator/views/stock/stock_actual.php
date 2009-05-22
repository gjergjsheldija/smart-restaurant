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
						<col style='width:20%;' />
						<col style='width:10%;' />
						<col style='width:10%;' />
						<col style='width:10%;' />
						<col style='width:10%;' />
						<col style='width:10%;' />
						<col style='width:10%;' />
						<col style='width:10%;' />
						<col style='width:10%;' />
					</colgroup>				
					<thead>
						<tr>
							<th><?=lang('name'); ?></th>
							<th align="right"><?=lang('uom'); ?></th>
							<th align="right"><?=lang('quantity'); ?></th>
							<th align="right"><?=lang('sell_price'); ?></th>
							<th align="right"><?=lang('buy_price'); ?></th>
							<th align="right"><?=lang('value_price_sell'); ?></th>
							<th align="right"><?=lang('value_price_buy'); ?></th>
							<th align="right"><?=lang('total'); ?></th>
							<th align="right"><?=lang('destid'); ?></th>
						</tr>
					</thead>
				<?php 
					$value_price_sell = $value_price_buy = 0; 
					foreach($stock_actual->result() as $row) { 
					$value_price_sell += $row->value_price_sell;
					$value_price_buy += $row->value_price_buy;
				?>
					<tr>
						<td><?=$row->name;?></td>
						<td align="right"><?=$uom[$row->uom];?></td>
						<td align="right"><?=$row->quantity;?></td>
						<td align="right"><?=$row->sell_price;?></td>
						<td align="right"><?=$row->buy_price;?></td>
						<td align="right"><?=$row->value_price_sell;?></td>
						<td align="right"><?=$row->value_price_buy;?></td>
						<td align="right"><?=$row->total;?></td>
						<td align="right"><?=$warehouse[$row->destid];?></td>
					</tr>
				<?php }?>	
				<tr><td colspan="9"></td></tr>
				<tr><td colspan="9" align="right"><strong><?=lang('total_value_sell'); ?> : <?=$value_price_sell; ?></strong></td></tr>
				<tr><td colspan="9" align="right"><strong><?=lang('total_value_buy'); ?> : <?=$value_price_buy; ?></strong></td></tr>
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
