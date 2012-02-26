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
			<?php if($acctype == 'ap') { ?>
				<h2><?php echo lang('ap'); ?> :: <?php echo anchor('account/newAccount/ap',lang('new_ap')) ?></h2>
			<?php } elseif($acctype == 'ar') { ?>
				<h2><?php echo lang('ar'); ?> :: <?php echo anchor('account/newAccount/ar',lang('new_ar')) ?></h2>
			<?php } ?>
			</div>
			<div class="two-column">
			<div class="column-left">
				<div class="hastable">
						<?php 
						$tmp = "";
						$total = 0;
						foreach($query->result() as $row) {	
							$total += $row->cash_amount;
							$total += $row->bank_amount;
							if($tmp != $row->who) {
								echo '<div class="portlet">
								<div class="portlet-header">'.$row->who.'</div>
								<div class="portlet-content">';?>
							<table cellspacing="0">
								<thead>
									<tr>
										<th><?php echo lang('date'); ?></th>
										<th><?php echo lang('cash'); ?></th>
										<th><?php echo lang('bank'); ?></th>
										<th><?php echo lang('paid'); ?></th>
									</tr>
								</thead>	
						<?php		
							}						
						?>		<tr>
									<td><?php echo $row->date ?></td>
									<td align="right"><?php echo $row->cash_amount ?></td>									
									<td align="right"><?php echo $row->bank_amount ?></td>									
									<td align="right"><?php echo $row->paid == 1 ? lang('yes') : lang('no')?></td>									
								</tr>
						<?php 
							$temporary = $query->next_row();
							$tmp = $temporary->who;
							if($tmp != $row->who) {
								$tmp = $row->who;
								echo "</table></div></div>";
							}
						}; 
						?>
					</table><hr><div class="title" align="right"><h2><?php echo lang('total')?> : <?php echo $total ?></h2></div>	</div></div>
			</div>	
			</div>
			<div class="column-right">				
				<?php $this->load->view('account/account_edit') ?>
			</div>
        </div>
        </div>
	</div>
</div>
<div class="clearfix"></div>