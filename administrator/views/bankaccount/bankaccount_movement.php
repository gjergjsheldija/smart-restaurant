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
			<h2><?php echo lang('movements'); ?></h2>
			</div>
			<div class="two-column">
			<div class="hastable">			
			<?php echo form_open('bankaccount/report_movement');?>
			<?php
			$tmp_from = isset($dt_from) ? $dt_from : date('Y-m-d');
			$tmp_to = isset($dt_to) ? $dt_to : date('Y-m-d');
			$date_from = array(
			              'name'        => 'date_from',
			              'id'          => 'date_from',
			              'size'        => '8',
						  'value'		=> $tmp_from,
						  'class' 		=> 'field text small');
			
			$date_to = array(
			              'name'        => 'date_to',
			              'id'          => 'date_to',
			              'size'        => '8',
						  'value'		=> $tmp_to,
						  'class' 		=> 'field text small');
			?>
				<table cellspacing="0">
					<tr>
						<td><?php echo lang('from'); ?> : <?php echo form_input($date_from) ?></td>
						<td><?php echo lang('to'); ?> : <?php echo form_input($date_to) ?></td>
						<td><input type="submit" value="<?php echo lang('show'); ?>" class="ui-state-default ui-corner-all float-right"></td>
						<td align="right"><?php echo anchor_image(site_url()."/bankaccount/report_movement_pdf/" . $tmp_from . "_" . $tmp_to,'../images/administrator/pdf.png') ?></td>
					</tr>
				</table>
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
							echo '<div class="portlet">
								<div class="portlet-header">' . $row->bankname.'</div>
								<div class="portlet-content">';?>
						<table cellspacing="0">
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
							echo "<tr><td colspan='5' align='right'><div class='page-title'><h1>" .lang('total'). " : " . ($partial_in + $partial_out) . "</h1></div></td></tr>";
							$partial_in = 0;
							$partial_out = 0;
							echo "</table></div></div>";
						}
					}; ?>
					<tr><td colspan="5" align="right"><div class="page-title"><h1><?php echo lang('total'); ?> : <?php echo $partial_in + $partial_out ?></h1></div></td></tr>							
				</table></div></div>		
				<div class="hastable">			
				<table cellspacing="0">
					<tr>
						<td colspan="2"></td>
						<td align="right"><?php echo lang('in'); ?></td>
						<td align="right"><?php echo lang('out'); ?></td>
						<td align="right"><?php echo lang('diff'); ?></td></tr>
					<tr>
						<td colspan="2" align="right"><div class="title"><h2><?php echo lang('total'); ?> : </h2></div></td>
						<td align="right"><div class="title"><h2><?php echo $total_in ?></h2></div></td>
						<td align="right"><div class="title"><h2><?php echo $total_out ?></h2></div></td>
						<td align="right"><div class="title"><h2><?php echo $total_in + $total_out?></h2></div></td>
					</tr>	
				</table>		
				</div>		
			</form>
			</div>
        </div>
        </div>
	</div>
</div>
<div class="clearfix"></div>
