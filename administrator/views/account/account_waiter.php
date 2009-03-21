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
jQuery().ready(function() {
	$('input[@name=date_from]').datepicker({formatDate:'yyyy-mm-dd'});
	$('input[@name=date_to]').datepicker({formatDate:'yyyy-mm-dd'});
});	
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
			<h2><?=lang('waiter_income'); ?></h2>
			<?=form_open('account/report_waiter');?>
			<?php
			$tmp_from = isset($dt_from) ? $dt_from : date('Y-m-d');
			$tmp_to = isset($dt_to) ? $dt_to : date('Y-m-d');
			$date_from = array(
			              'name'        => 'date_from',
			              'id'          => 'date_from',
			              'size'        => '8',
						  'value'		=> $tmp_from);
			
			$date_to = array(
			              'name'        => 'date_to',
			              'id'          => 'date_to',
			              'size'        => '8',
						  'value'		=> $tmp_to);
			?>
			<table>
				<tr>
					<td><?=lang('from'); ?> : <?=form_input($date_from) ?></td>
					<td><?=lang('to'); ?> : <?=form_input($date_to) ?></td>
					<td><input type="submit" value="<?=lang('show'); ?>"></td>
					<td align="right"><?=anchor_image(site_url()."/account/report_waiter_pdf/" . $tmp_from . "_" . $tmp_to,'../images/administrator/save-pdf.png') ?></td>
				</tr>
			</table>
			<br /><br />
			<table width="100%" class="zebra">
				<colgroup>
					<col style='width:15%;' />
					<col style='width:20%;' />
					<col style='width:20%;' />
					<col style='width:10%;' />
					<col style='width:10%;' />
					<col style='width:10%;' />
				</colgroup>			
				<thead>
					<tr>
						<th><?=lang('date'); ?></th>
						<th><?=lang('who'); ?></th>
						<th><?=lang('reason'); ?></th>
						<th><?=lang('type'); ?></th>
						<th><?=lang('in'); ?></th>
						<th><?=lang('out'); ?></th>
					</tr>
				</thead>
				<?php
				$total_in = 0;
				$total_out = 0;
				foreach($account_movements  as $account_movement) : 
					if($account_movement['cash_amount'] > 0 )
						$total_in += $account_movement['cash_amount'];
					else
						$total_out += $account_movement['cash_amount'];
				?>
				<tr>
					<td align="left"><?=$account_movement['date']?></td>
					<td align="left"><?=$account_movement['who']?></td>
					<td align="left"><?=$account_movement['description']?></td>
					<td align="left"><?=$account_movement['name']?></td>
					<td align="right"><?=$account_movement['cash_amount'] > '0' ? $account_movement['cash_amount'] : '-'?></td>
					<td align="right"><?=$account_movement['cash_amount'] < '0' ? $account_movement['cash_amount'] : '-'?></td>
				</tr>
				<?php endforeach; ?>
				<tr><td colspan="6"></td></tr>
				<tr>
					<td colspan="3" align="right"><strong><?=lang('total'); ?> : </strong></td>
					<td align="right"><strong><?=$total_in ?></strong></td>
					<td align="right"><strong><?=$total_out ?></strong></td>
					<td align="right"><strong><?=$total_in -(-$total_out)?></strong></td>
				</tr>	
			</table>
			</form>
        </div>
        </div>
		</div>
	</div>
</div>
<div class="ClearAll"></div>
