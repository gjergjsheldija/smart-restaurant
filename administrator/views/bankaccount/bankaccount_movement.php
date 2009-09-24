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
	$('input[name=date_from]').datepicker({dateFormat:'yy-mm-dd',changeMonth: true, changeYear: true });
	$('input[name=date_to]').datepicker({dateFormat:'yy-mm-dd',changeMonth: true, changeYear: true });
});	
</script>	
<div id="Container">
	<div class="Full">
		<div class="contentRight">
		<div class="contentLeft">
		<div class="col">
			<h2><?php echo lang('movements'); ?></h2>
			<?php echo form_open('bankaccount/report_movement');?>
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
						<td><?php echo lang('from'); ?> : <?php echo form_input($date_from) ?></td>
						<td><?php echo lang('to'); ?> : <?php echo form_input($date_to) ?></td>
						<td><input type="submit" value="<?php echo lang('show'); ?>"></td>
						<td align="right"><?php echo anchor_image(site_url()."/bankaccount/report_movement_pdf/" . $tmp_from . "_" . $tmp_to,'../images/administrator/save-pdf.png') ?></td>
					</tr>
				</table>
				<br /><br />
					<?php 
					$tmp = "";
					$total_in = 0;
					$total_out = 0;	
					$partial_in = 0;
					$partial_out = 0;						
					foreach($bankaccount_actual->result() as $row) {	
						if($row->amount > 0 ) {
							$total_in += $row->amount;
							$partial_in += $row->amount;
						} else {
							$total_out += $row->amount;
							$partial_out += $row->amount;
						}
						
						if($tmp != $row->bankname) {
							echo '<br><div align="left"><h3>' . $row->bankname.'</h3></div>';?>
						<table width="100%" class="zebra">
							<colgroup>
								<col style='width:15%;' />
								<col style='width:20%;' />
								<col style='width:10%;' />
								<col style='width:10%;' />
								<col style='width:10%;' />
							</colgroup>
							<thead>
								<tr>
									<th><?php echo lang('date'); ?></th>
									<th><?php echo lang('account'); ?></th>
									<th><?php echo lang('reason'); ?></th>
									<th><?php echo lang('in'); ?></th>
									<th><?php echo lang('out'); ?></th>
								</tr>
							</thead>	
					<?php		
						}						
					?>		<tr>
								<td align="left"><?php echo $row->timestamp?></td>
								<td align="left"><?php echo $row->accountname?></td>
								<td align="left"><?php echo $row->description?></td>
								<td align="right"><?php echo $row->amount > '0' ? $row->amount : '-'?></td>
								<td align="right"><?php echo $row->amount < '0' ? $row->amount : '-'?></td>
							</tr>
					<?php 									
						$temporary = $bankaccount_actual->next_row();
						$tmp = $temporary->bankname;
						if($tmp != $row->bankname) {
							$tmp = $row->bankname;
							echo "<tr><td colspan='5' align='right'></td></tr>";
							echo "<tr><td colspan='5' align='right'><strong>" .lang('total'). " : " . ($partial_in + $partial_out) . "</strong></td></tr>";
							$partial_in = 0;
							$partial_out = 0;
							echo "</table>";
						}
					}; ?>
					<tr><td colspan="5" align="right"><strong><?php echo lang('total'); ?> : <?php echo $partial_in + $partial_out ?></strong></td></tr>							
				</table>			
				<table width="100%">
					<tr><td colspan="5"><hr></td></tr>
					<tr><td colspan="2"></td><td align="right"><?php echo lang('in'); ?></td><td align="right"><?php echo lang('out'); ?></td><td align="right"><?php echo lang('diff'); ?></td></tr>
					<tr>
						<td colspan="2" align="right"><strong><?php echo lang('total'); ?> : </strong></td>
						<td align="right"><strong><?php echo $total_in ?></strong></td>
						<td align="right"><strong><?php echo $total_out ?></strong></td>
						<td align="right"><strong><?php echo $total_in + $total_out?></strong></td>
					</tr>	
				</table>		
			</form>
        </div>
        </div>
		</div>
	</div>
</div>
<div class="ClearAll"></div>
