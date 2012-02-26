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
				<h2><?php echo lang('bank_account'); ?> :: <?php echo anchor('bankaccount/newBankMovement',lang('new_movement')) ?></h2>
			</div>
			<div class="two-column">
			<div class="column-left">
				<div class="hastable">
						<?php 
						$tmp = "";
						$total = 0;
						foreach($query->result() as $row) {	
							$total += $row->amount;
							if($tmp != $row->bankname) {
								echo '<div class="portlet">
								<div class="portlet-header">'.$row->bankname.'</div>
								<div class="portlet-content">';?>
							<table cellspacing="0">
								<thead>
									<tr>
										<th class="sortable"><?php echo lang('description'); ?></th>
										<th class="sortable"><?php echo lang('amount'); ?></th>
									</tr>
								</thead>	
						<?php		
							}						
						?>		<tr>
									<td><?php echo $row->description ?></td>
									<td align="right"><?php echo $row->amount ?></td>
								</tr>
						<?php 
							$temporary = $query->next_row();
							$tmp = $temporary->bankname;
							if($tmp != $row->bankname) {
								$tmp = $row->bankname;
								echo "</table></div></div>";
							}
						}; 
						?>
					</table>
						<div class="title" align="right"><h2><?php echo lang('total'); ?> : <?php echo $total ?></h2></div>
					</div></div>
				
				</div>
			</div>
			<div class="column-right">				
				<?php $this->load->view('bankaccount/bankmovement_edit') ?>
			</div>
        </div>
        </div>
	</div>
</div>
<div class="clearfix"></div>