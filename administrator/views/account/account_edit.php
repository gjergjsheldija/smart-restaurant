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
	$('input[@name=date]').datepicker({formatDate:'yyyy-mm-dd'});
	$('input[@name=payment_date]').datepicker({formatDate:'yyyy-mm-dd'});
});
jQuery().ready(function() {
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
});		
jQuery().ready(function() {
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
			number: "<?=lang('nr_missing'); ?>",
			date: "<?=lang('date_missing'); ?>",
			description: "<?=lang('desc_missing'); ?>",
			amount: "<?=lang('amount_missing'); ?>",
		}
	});
});
</script>	
<style type="text/css">
input.error { border: 1px dotted red; }
textarea.error { border: 1px dotted red; }
div.error { display: none; }
#accountsForm label.error { width: auto; display: block; color: red;font-style: italic }
</style>

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
				<th colspan="2">
				<h2>
					<? if($acctype == 'ap')
					   	echo lang('ap');
					   elseif($acctype == 'ar')
					   	echo lang('ar');
					?>
				</h2>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?=form_label('Nr');?> :</td>
				<?php
				$number = array(
			              'name'        => 'number',
			              'id'          => 'number',
			              'maxlength'   => '50',
			              'size'        => '10',
						  'for'			=> 'number');
				?>
				<td><?=form_input($number); ?></td>
				<td><?=form_label(lang('date'));?> :</td>
				<?php
				$date = array(
			              'name'        => 'date',
			              'id'          => 'date',
			              'maxlength'   => '50',
			              'size'        => '10',
						  'for'			=> 'date');
				?>
				<td><?=form_input($date); ?></td>
			</tr>
			<tr>
				<td><?=form_label(lang('customer'));?> :</td>
				<td><?=form_dropdown('who',$person); ?></td>
			</tr>
			<tr>
				<td><?=form_label(lang('description'));?> :</td>
				<?php
				$description = array(
						'rows' => '5',
						'id'   => 'description',
						'cols' => '30',
						'name' => 'description',
						'for'  => 'number');
						?>
				<td colspan="3"><?=form_textarea($description); ?></td>
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
				<td><?=form_label(lang('amount'));?> :</td>
				<td colspan="3"><?=form_input($amount); ?></td>
			</tr>
			<tr>
				<td><?=lang('paid'); ?></td>
				<td><?=form_checkbox('paid','paid'); ?></td>
				<td><?=lang('payment_date'); ?></td>
				<?php
				$payment_date = array(
		              'name'        => 'payment_date',
		              'id'          => 'payment_date',
		              'maxlength'   => '50',
		              'size'        => '10');
				?>
				<td colspan="2"><?=form_input($payment_date); ?></td>
			</tr>
			<tr>
				<td><?=lang('payment_type'); ?></td>
				<td id="payment_type"><?=form_dropdown('payment_type',$payment_type); ?></td>
				<td id="bank_label"><?=lang('bank_account') ?> :</td>
				<td id="bank_account"><?=form_dropdown('bank_account',$bank_account); ?></td>
			</tr>
			<tr>
				<td><input type="submit" value="<?=lang('save'); ?>"></td>
				<td></td>
			</tr>
		</tbody>
	</table>
</form>
<?php } ?>