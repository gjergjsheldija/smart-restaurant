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
<div id="page-wrapper">
	<div id="main-wrapper">
		<div id="main-content">
			<div class="title title-spacing">
				<h2><?php echo lang('actual_state'); ?>&nbsp;&nbsp;&nbsp;<?php echo anchor_image(site_url()."/bankaccount/report_actual_pdf/",'../images/administrator/pdf.png') ?></h2>
			</div>
			<div class="two-column">
			<div class="column-left">
				<div class="hastable">
				<table cellspacing="0">
					<thead>
						<tr>
							<th><?php echo lang('bank'); ?></th>
							<th><?php echo lang('account'); ?></th>
							<th><?php echo lang('account_nr'); ?></th>
							<th align="right"><?php echo lang('amount'); ?></th>
						</tr>
					</thead>
				<?php 
					$total = 0; 
					foreach($bankaccount_actual->result() as $row) { 
					$total += $row->amount;
				?>
					<tr>
						<td><?php echo $row->bank;?></td>
						<td><?php echo $row->name;?></td>
						<td><?php echo $row->number;?></td>
						<td align="right"><?php echo $row->amount;?></td>
					</tr>
				<?php } ?>	
				<tr><td colspan="4" align="right"><div class="title title-spacing"><h3><?php echo lang('total'); ?> : <?php echo $total; ?></h3></div></td></tr>
				</table>
			</div>	
			</div>
        </div>
        </div>
	</div>
</div>
<div class="clearfix"></div>
