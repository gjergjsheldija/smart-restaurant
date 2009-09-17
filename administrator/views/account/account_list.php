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
	jQuery().ready(function(){
		jQuery('#tabelapare').accordion({
				header: 'div.mytitle',
			    active: false, 
			    autoheight: true,
			    fillSpace: true,  
			    alwaysOpen: false
		});
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
			<?php if($acctype == 'ap') { ?>
				<h2><?php echo lang('ap'); ?> :: <?php echo anchor('account/newAccount/ap',lang('new_ap')) ?></h2>
			<?php } elseif($acctype == 'ar') { ?>
				<h2><?php echo lang('ar'); ?> :: <?php echo anchor('account/newAccount/ar',lang('new_ar')) ?></h2>
			<?php } ?>
				<div class="basic" style="float:left;"  id="tabelapare">
						<?php 
						$tmp = "";
						$total = 0;
						foreach($query->result() as $row) {	
							$total += $row->cash_amount;
							$total += $row->bank_amount;
							if($tmp != $row->who) {
								echo '<div class="mytitle">'.$row->who.'</div>';?>
							<table class="zebra">
								<colgroup>
									<col style='width:65%;' />
									<col style='width:15%;' />
									<col style='width:10%;' />
									<col style='width:10%;' />
								</colgroup>
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
							$kot = $query->next_row();
							$tmp = $kot->who;
							if($tmp != $row->who) {
								$tmp = $row->who;
								echo "</table>";
							}
						}; 
						?>
					</table>
					<hr>
				<div align="right"><strong><?php echo lang('total')?> : <?php echo $total ?></strong></div>					
			</div>	
			</div>
			<div class="Right">				
				<?php $this->load->view('account/account_edit') ?>
			</div>
        </div>
        </div>
		</div>
	</div>
</div>
<div class="ClearAll"></div>