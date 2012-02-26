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

function hideThis(obj) { 
        $('#obj'+obj).slideToggle("slow");
        return false;
}
</script>	
<div id="page-wrapper">
	<div id="main-wrapper">
		<div id="main-content">
			<div class="title title-spacing">
			<h2><?php echo lang('waiter_income'); ?></h2>
			</div>
			<div class="two-column">
			<div class="hastable">
			<?php echo form_open('account/report_waiter');?>
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
					<td align="right"><?php echo anchor_image(site_url()."/account/report_waiter_pdf/" . $tmp_from . "_" . $tmp_to,'../images/administrator/pdf.png') ?></td>
				</tr>
			</table>
			<table cellspacing="0">
				<thead>
					<tr>
						<th><?php echo lang('date'); ?></th>
						<th><?php echo lang('who'); ?></th>
						<th><?php echo lang('reason'); ?></th>
						<th><?php echo lang('type'); ?></th>
						<th><?php echo lang('in'); ?></th>
						<th><?php echo lang('out'); ?></th>
					</tr>
				</thead>
				<?php
				$total_in = 0;
				$total_out = 0;
				$billid = -1;
				$accid = -1;
				foreach($account_movements as $am => $account_movement ) {
					foreach($account_movement as $ai => $account_item ) {
						if($billid != $account_movement['accid']) {
							$billid = $account_movement['accid'];
							if($account_movement['cash_amount'] > 0 )
								$total_in += $account_movement['cash_amount'];
							else
								$total_out += $account_movement['cash_amount'];
							?>
							<tr onClick="hideThis('<?php echo $billid;?>')" id="hide<?php echo $billid;?>" style="font-weight:bold;border-bottom:0.1em solid #000000;border-top:0.1em solid #000000;">
								<td align="left"><?php echo $account_movement['date']?></td>
								<td align="left"><?php echo $account_movement['who']?></td>
								<td align="left"><?php echo $account_movement['description']?></td>
								<td align="left"><?php echo $account_movement['name']?></td>
								<td align="right"><?php echo $account_movement['cash_amount'] > '0' ? $account_movement['cash_amount'] : '-'?></td>
								<td align="right"><?php echo $account_movement['cash_amount'] < '0' ? $account_movement['cash_amount'] : '-'?></td>
							</tr>
							<tr><td colspan="6">
							<?php 
						} else {
							if(is_array($account_item)) {
								echo '<div id="obj'.$billid.'" style="display:none"><table cellspacing="0">';
								foreach($account_item as $dish ) {
								?>
								<tr>
									<td align="left"></td>
									<td align="left"></td>
									<td align="left"><?php echo $dish['dishname']?></td>
									<td align="right"><?php echo $dish['dishprice']?></td>
									<td align="right"><?php echo $dish['dishqty']?></td>
									<td align="right"><?php echo $dish['dishprice']*$dish['dishqty']?></td>
								</tr>
				<?php 			}
								echo "</table></div></td></tr>";
							}
						}
					} 
				} 
				?>
				</td></tr>
				<tr>
					<td colspan="3" align="right"><div class="title"><h2><?php echo lang('total'); ?> : </h2></div></td>
					<td align="right"><div class="title"><h2><?php echo $total_in ?></h2></div></td>
					<td align="right"><div class="title"><h2><?php echo $total_out ?></h2></div></td>
					<td align="right"><div class="title"><h2><?php echo $total_in -(-$total_out)?></h2></div></td>
				</tr>	
			</table>
			</form>
			</div>
        </div>
        </div>
	</div>
</div>
<div class="clearfix"></div>
