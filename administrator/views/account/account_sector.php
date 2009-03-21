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
			<div class="Left">
				<h2><?=lang('tot_by_sect'); ?></h2>
				<?=form_open('account/report_sector');?>
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
						<td align="right"><?=anchor_image(site_url()."/account/report_sector_pdf/" . $tmp_from . "_" . $tmp_to,'../images/administrator/save-pdf.png') ?></td>
					</tr>
				</table>
				<br /><br />		
							<?php 
							$tmp = "";
							$totalSector = array();
							foreach($sector_movements->result() as $row) {	
								if($tmp != $row->waiter) {
									echo '<br><div align="left"><h3>' . $row->waiter.'</h3></div>';?>
								<table width="300" class="zebra">
									<colgroup>
										<col style='width:15%;' />
										<col style='width:10%;' />
									</colgroup>
									<thead>
										<tr>
											<th  align="left"><?=lang('sector'); ?></th>
											<th  align="right"><?=lang('amount'); ?></th>
										</tr>
									</thead>	
							<?php		
								}						
							?>		<tr>
										<td align="left"><?=$row->name ?></td>
										<td align="right"><?=$row->shuma ?></td>
									</tr>
							<?php 
								if(!isset($totalSector[$row->name]))
									$totalSector[$row->name] = $row->shuma;
								else	
									$totalSector[$row->name] += $row->shuma;
								$kot = $sector_movements->next_row();
								$tmp = $kot->waiter;
								if($tmp != $row->waiter) {
									$tmp = $row->waiter;
									echo "</table>";
								}
							}; ?>
						</table>
						<br>
						<div align="left"><strong><?=lang('tot_by_sect'); ?></strong><hr width="300">
						<table width="300">
						<?php foreach($totalSector as $sector => $value) {
							echo '<tr><td align="left">' . $sector . '</td><td align="right">' . $value . '</td></tr>';
						}
						echo '<tr><td align="left"><strong>' . lang('total') . ': </strong></td><td align="right"><strong>' . array_sum($totalSector)  . ' Lek</strong></td></tr>';
						?>
						</table>
				</form>
			</div>	
			</div>
			<div class="Right">	
				<br><br><br><br><br><br>
				<div align="left"><strong><?=lang('tot_dish'); ?></strong><hr width="300">
				<table width="300" class="zebra">
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
<div class="ClearAll"></div>
