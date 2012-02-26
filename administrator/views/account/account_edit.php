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
	//date and stuff
	$('input[name=date]').datepicker({dateFormat:'yy-mm-dd',changeMonth: true, changeYear: true });
	$('input[name=payment_date]').datepicker({dateFormat:'yy-mm-dd',changeMonth: true, changeYear: true });

	//hide show the bank account depending on the payment type	
	$('#payment_type > select')
		.change( function() { 
			if($(this).val() == 3) {
				$('#bank_label').hide();			
				$('#bank_account').hide();
			} else if($(this).val() != 3) {
				$('#bank_label').show();
				$('#bank_account').show();			
			}
	});		

	//final form filling check
    $("#accountsForm").validate({
		rules: {
			number: "required",
			date: "required",
			description: {
				required: true,
				minlength: 2
			},
			amount: {
			      required: true,
			      number: true
			}
		},
		messages: {
			number: "<?php echo lang('nr_missing'); ?>",
			date: "<?php echo lang('date_missing'); ?>",
			description: "<?php echo lang('desc_missing'); ?>",
			amount: "<?php echo lang('amount_missing'); ?>",
		}
	});
});		
</script>	
<style type="text/css">
 label.error { width: auto; display: block; color: red;font-style: italic }
</style>
<div class="hastable">
<?php if( isset($newaccount) ) { 
	if($acctype == 'ap') {
		echo form_open('account/addnew/ap',array('id' => 'accountsForm'));
	}elseif($acctype == 'ar'){
		echo form_open('account/addnew/ar',array('id' => 'accountsForm'));
	}
?>
	<table>
		<thead>
			<tr>
				<td colspan="4">
				<h2>
					<?php if($acctype == 'ap')
					   	echo lang('ap');
					   elseif($acctype == 'ar')
					   	echo lang('ar');
					?>
				</h2>
				</td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?php echo form_label('Nr');?> :</td>
				<?php
				$number = array(
			              'name'        => 'number',
			              'id'          => 'number',
			              'maxlength'   => '50',
			              'size'        => '10',
						  'for'			=> 'number');
				?>
				<td><?php echo form_input(array('name' => 'number','class' => 'field text medium')); ?></td>
				<td><?php echo form_label(lang('date'));?> :</td>
				<?php
				$date = array(
			              'name'        => 'date',
			              'id'          => 'date',
			              'maxlength'   => '50',
			              'size'        => '10',
						  'for'			=> 'date');
				?>
				<td><?php echo form_input(array('name' => 'date','class' => 'field text medium')); ?></td>
			</tr>
			<tr>
				<td><?php echo form_label(lang('customer'));?> :</td>
				<td colspan="3"><?php echo form_dropdown('who',$person,'class="field text medium"'); ?></td>
			</tr>
			<tr>
				<td><?php echo form_label(lang('description'));?> :</td>
				<?php
				$description = array(
						'rows' => '5',
						'id'   => 'description',
						'cols' => '30',
						'name' => 'description',
						'for'  => 'number',
						'class'=> 'field textarea small'	);
						?>
				<td colspan="3"><?php echo form_textarea($description); ?></td>
			</tr>
			<tr>
			<?php
			$amount = array(
		              'name'        => 'amount',
		              'id'          => 'amount',
		              'maxlength'   => '50',
		              'size'        => '10',
					  'for'			=> 'amount');
			?>
				<td><?php echo form_label(lang('amount'));?> :</td>
				<td colspan="3"><?php echo form_input(array('name' => 'amount','class' => 'field text small')); ?></td>
			</tr>
			<tr>
				<td><?php echo lang('paid'); ?></td>
				<td><?php echo form_checkbox(array('name' => 'paid', 'value' => 'paid','class' => 'field checkbox')); ?></td>
				<td><?php echo lang('payment_date'); ?></td>
				<?php
				$payment_date = array(
		              'name'        => 'payment_date',
		              'id'          => 'payment_date',
		              'maxlength'   => '50',
		              'size'        => '10',
					  'class' => 'field text medium');
				?>
				<td colspan="2"><?php echo form_input($payment_date); ?></td>
			</tr>
			<tr>
				<td><?php echo lang('payment_type'); ?></td>
				<td id="payment_type"><?php echo form_dropdown('payment_type',$payment_type,'class="field text medium"'); ?></td>
				<td id="bank_label"><?php echo lang('bank_account') ?> :</td>
				<td id="bank_account"><?php echo form_dropdown('bank_account',$bank_account,'class="field text medium"'); ?></td>
			</tr>
			<tr>
				<td colspan="4"><input type="submit" value="<?php echo lang('save'); ?>" class="ui-state-default ui-corner-all float-right"></td>
			</tr>
		</tbody>
	</table>
</form>
<div class="hastable">
<?php } ?>