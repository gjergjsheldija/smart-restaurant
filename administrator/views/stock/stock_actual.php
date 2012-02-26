<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Smart Restaurant
 *
 * An open source application to manage restaurants
 *
 * @package		SmartRestaurant
 * @author		Gjergj Sheldija
 * @copyright	Copyright (c) 2008-2012, Gjergj Sheldija
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
<div id="page-wrapper">
	<div id="main-wrapper">
		<div id="main-content">
			<div class="title title-spacing">
				<h2><?php echo lang('actual_state'); ?>&nbsp;&nbsp;&nbsp;<?php echo anchor_image(site_url()."/stock/report_actual_pdf/",'../images/administrator/pdf.png') ?></h2>
			</div>
				<?php echo form_open('stock/report_actual');?>
			<div class="two-column">
			<div class="column-left">
				<div class="hastable">
				<table cellspacing="0">
					<thead>
						<tr>
							<td><?php echo lang('name'); ?></th>
							<td><?php echo lang('total'); ?></td>
							<td><?php echo lang('uom'); ?></td>
							<td><?php echo lang('quantity'); ?></td>
							<td><?php echo lang('sell_price'); ?></td>
							<td><?php echo lang('buy_price'); ?></td>
							<td><?php echo lang('value_price_sell'); ?></td>
							<td><?php echo lang('value_price_buy'); ?></td>
							<td><?php echo lang('destid'); ?></td>
						</tr>
					</thead>
				<?php 
					$value_price_sell = $value_price_buy = 0; 
					foreach($stock_actual->result() as $row) { 
					$value_price_sell += $row->value_price_sell;
					$value_price_buy += $row->value_price_buy;
				?>
					<tr>
						<td><?php echo $row->name;?></td>
						<td><?php echo $row->total;?></td>
						<td><?php echo $uom[$row->uom];?></td>
						<td><?php echo $row->quantity;?></td>
						<td><?php echo $row->sell_price;?></td>
						<td><?php echo $row->buy_price;?></td>
						<td><?php echo $row->value_price_sell;?></td>
						<td><?php echo $row->value_price_buy;?></td>
						<td><?php echo $warehouse[$row->destid];?></td>
					</tr>
				<?php }?>	
				<tr>
					<td colspan="9">
						<div class="title" align="right">
							<h2><?php echo lang('total_value_sell'); ?> : <?php echo $value_price_sell; ?></h2><br />
							<h2><?php echo lang('total_value_buy'); ?> : <?php echo $value_price_buy; ?></h2>
						</div>
					</td>
				</tr>
				</table>
			</div>	
			</div>
        </div>
        </div>
	</div>
</div>
<div class="clearfix"></div>