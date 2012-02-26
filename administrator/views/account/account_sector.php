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
<script type="text/javascript">
$( function(){
	$('input[name=date_from]').datepicker({dateFormat:'yy-mm-dd',changeMonth: true, changeYear: true });
	$('input[name=date_to]').datepicker({dateFormat:'yy-mm-dd',changeMonth: true, changeYear: true });
});	
</script>	
<div id="page-wrapper">
	<div id="main-wrapper">
		<div id="main-content">
			<div class="title title-spacing">
				<h2><?php echo lang('tot_by_sect'); ?></h2>
			</div>
			<div class="two-column">
			<div class="column-left">
				<div class="hastable">			
				<?php echo form_open('account/report_sector');?>
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
						<td align="right"><?php echo anchor_image(site_url()."/account/report_sector_pdf/" . $tmp_from . "_" . $tmp_to,'../images/administrator/pdf.png') ?></td>
					</tr>
				</table>
							<?php 
							$tmp = "";
							$totalSector = array();
							foreach($sector_movements->result() as $row) {	
								if($tmp != $row->waiter) {
									echo '<div class="title title-spacing"><h3>' . $row->waiter.'</h3></div>';?>
								<table cellspacing="0">
									<thead>
										<tr>
											<th  align="left"><?php echo lang('sector'); ?></th>
											<th  align="right"><?php echo lang('amount'); ?></th>
										</tr>
									</thead>	
							<?php		
								}						
							?>		<tr>
										<td align="left"><?php echo $row->name ?></td>
										<td align="right"><?php echo $row->shuma ?></td>
									</tr>
							<?php 
								if(!isset($totalSector[$row->name]))
									$totalSector[$row->name] = $row->shuma;
								else	
									$totalSector[$row->name] += $row->shuma;
								$temporary = $sector_movements->next_row();
								$tmp = $temporary->waiter;
								if($tmp != $row->waiter) {
									$tmp = $row->waiter;
									echo "</table>";
								}
							}; ?>
						</table>
						<br>
						<div align="left"><div class="title title-spacing"><h3><?php echo lang('tot_by_sect'); ?></h3></div>
						<table cellspacing="0">
						<thead>
							<tr>
								<th  align="left"><?php echo lang('sector'); ?></th>
								<th  align="right"><?php echo lang('amount'); ?></th>
							</tr>
						</thead>	
						<?php foreach($totalSector as $sector => $value) {
							echo '<tr><td align="left">' . $sector . '</td><td align="right">' . $value . '</td></tr>';
						}
						echo '<tr><td align="left"><strong>' . lang('total') . ': </strong></td><td align="right"><strong>' . array_sum($totalSector)  . ' Lek</strong></td></tr>';
						?>
						</table>
				</form>
				</div></div>
				</div>
			<div class="column-right">	
				<div class="title title-spacing"><h3><?php echo lang('tot_dish'); ?></h3></div>
				<div class="hastable">	
				<table cellspacing="0">
				<?php 
				foreach( $sector_numdish as $dish ) {
					echo '<tr><td align="left">' . $dish['name'] . '</td><td align="right">' . $dish['numdish'] . '</td></tr>';
				}
				?>
				</table>
				</div>
			</div>
        </div>
        </div>
	</div>
</div>
<div class="clearfix"></div>
