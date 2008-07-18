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
			<h2><?=lang('movements'); ?></h2>
			<?=form_open('account/report_movement');?>
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
						<td align="right"><?=anchor_image(site_url()."/account/report_movement_pdf/" . $tmp_from . "_" . $tmp_to,'../img/save-pdf.png') ?></td>
					</tr>
				</table>
				<br /><br />
					<?php 
					$tmp = "";
					$total_in = 0;
					$total_out = 0;	
					$partial_in = 0;
					$partial_out = 0;						
					foreach($account_movements->result() as $row) {	
						if($row->cash_amount > 0 ) {
							$total_in += $row->cash_amount;
							$partial_in += $row->cash_amount;
						} else {
							$total_out += $row->cash_amount;
							$partial_out += $row->cash_amount;
						}
						
						if($tmp != $row->who) {
							echo '<br><div align="left"><h3><strong>' . $row->who.'</strong></h3></div>';?>
						<table width="100%" class="zebra">
							<colgroup>
								<col style='width:15%;' />
								<col style='width:20%;' />
								<col style='width:10%;' />
								<col style='width:10%;' />
								<col style='width:10%;' />
								<col style='width:5%;' />
							</colgroup>
							<thead>
								<tr>
									<th><?=lang('date'); ?></th>
									<th><?=lang('reason'); ?></th>
									<th><?=lang('type'); ?></th>
									<th><?=lang('in'); ?></th>
									<th><?=lang('out'); ?></th>
									<th><?=lang('paid'); ?></th>
								</tr>
							</thead>	
					<?php		
						}						
					?>		<tr>
								<td align="left"><?=$row->date?></td>
								<td align="left"><?=$row->description?></td>
								<td align="left"><?=$row->name?></td>
								<td align="right"><?=$row->cash_amount > '0' ? $row->cash_amount : '-'?></td>
								<td align="right"><?=$row->cash_amount < '0' ? $row->cash_amount : '-'?></td>
								<td align="right"><?=$row->debit == '1' ? lang('yes') : lang('no')?></td>
							</tr>
					<?php 									
						$kot = $account_movements->next_row();
						$tmp = $kot->who;
						if($tmp != $row->who) {
							$tmp = $row->who;
							echo "<tr><td colspan='6' align='right'></td></tr>";
							echo "<tr><td colspan='6' align='right'><strong>" . lang('total'). " : " . ($partial_in -(-$partial_out)) . "</strong></td></tr>";
							$partial_in = 0;
							$partial_out = 0;
							echo "</table>";
						}
					}; ?>
					<tr><td colspan="6" align="right"><strong><?=lang('total'); ?> : <?php echo ($partial_in -(-$partial_out)) ?></strong></td></tr>							
				</table>			
				<table width="100%">
					<tr><td colspan="6"><hr></td></tr>
					<tr><td colspan="3"></td><td align="right"><?=lang('in'); ?></td><td align="right"><?=lang('out'); ?></td><td align="right"><?=lang('diff'); ?></td></tr>
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
