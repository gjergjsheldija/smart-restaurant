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
jQuery().ready(function() {
	$('input[@name=timestamp]').datepicker({formatDate:'yyyy-mm-dd'});
});	
jQuery().ready(function() {
    $("#accountsForm").validate({
		rules: {
			timestamp: "required",
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
			timestamp: "<?=lang('date_missing'); ?>",
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

<?php if( isset($newbankmovement) ) { ?>
<?=form_open('bankaccount/addnewMovement',array('id' => 'accountsForm'));?>
	<table>
		<thead>
			<tr>
				<th colspan="2">
				<h2><?=lang('movements'); ?></h2>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?=form_label(lang('date'));?> :</td>
				<?php
				$date = array(
			              'name'        => 'timestamp',
			              'id'          => 'date',
			              'maxlength'   => '50',
			              'size'        => '10',
						  'for'			=> 'date');
				?>
				<td><?=form_input($date); ?></td>
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
				<td colspan="3"><?=form_input($amount); ?>(<?=lang('negative_value') ?>)</td>
			</tr>
			<tr>
				<td><?=lang('payment_type'); ?> :</td>
				<td id="type"><?=form_dropdown('type',$payment_type); ?></td>
				<td id="bank_label"><?=lang('account'); ?> :</td>
				<td id="account_id"><?=form_dropdown('account_id',$bank_account); ?></td>
			</tr>
			<tr>
				<td><input type="submit" value="<?=lang('save'); ?>"></td>
				<td></td>
			</tr>
		</tbody>
	</table>
</form>
<?php } ?>