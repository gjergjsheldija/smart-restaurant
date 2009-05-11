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
    $("#stockForm").validate({
		rules: {
			timestamp: "required",
			invoice_id: {
			      required: true,
			      number: true
			}
		},
		messages: {
			timestamp: "<?=lang('date_missing'); ?>",
			invoice_id: "<?=lang('nr_missing'); ?>"
		}
	});
});

function addFormField() {
	
	var id = document.getElementById("id").value;	
	var image = '   <?=img('images/administrator/edit_remove.png') ?>';
	$.ajax({
		type : "POST",
		url : "<?=base_url() . '?c=stock&m=ingredientList' ?>",
		success : function(data) {
			$("#loaderimg").remove();
			$("#divInvoice").append("<div id='row" + id + "'><td><label for='txt" + id + "'><?=lang('article'); ?></label>:</td><td><select name='ingredient[]' id='ingredient" + id + "'>" + data +"</select></td><td><label for='txt" + id + "'><?=lang('quantity'); ?></label>:</td><td><input type='text' name='quantity[]' value='' maxlength='50' size='10' id='quantity" + id + "' for='number'  /></td><td><label for='txt" + id + "'><?=lang('price'); ?></label>:</td><td><input type='text' name='price[]' value='' maxlength='50' size='10' id='price" + id + "' for='number'  /></td><a href='#' onClick='removeFormField(\"#row" + id + "\"); return false;'>" + image + "</a><br><br></div>");
		},
		cache: false,
		async: false
	});
		
	$('#row' + id).highlightFade({
		speed:1000
	});
	
	id = (id - 1) + 2;
	document.getElementById("id").value = id;
}

function removeFormField(id) {
	$(id).remove();
}
</script>
	
<style type="text/css">
input.error { border: 1px dotted red; }
textarea.error { border: 1px dotted red; }
div.error { display: none; }
#stockForm label.error { width: auto; display: block; color: red;font-style: italic }
</style>
<div id="Container">
	<div class="Full">
		<div class="contentRight">
		<div class="contentLeft">
		<div class="col">
			<div class="Left">
				<?=form_open('stock/addnew',array('id' => 'stockForm'));?>
					<table>
						<thead>
							<tr>
								<th colspan="6">
								<h2><?=lang('supply'); ?></h2>
								</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><?=form_label(lang('date'));?> :</td>
								<?php
									$date = array(
							              'name'        => 'timestamp',
							              'id'          => 'timestamp',
							              'maxlength'   => '50',
							              'size'        => '10',
										  'for'			=> 'timestamp');
								?>
								<td><?=form_input($date); ?></td>
								<td><?=form_label('Nr');?> :</td>
								<?php
									$invoice_id = array(
										'id'   		  => 'invoice_id',
						                'maxlength'   => '50',
						                'size'        => '10',
										'name' 		  => 'invoice_id',
										'for'  		  => 'invoice_id');
										?>
								<td><?=form_input($invoice_id); ?></td>
								<td><?=form_label(lang('supplier'));?> :</td>
								<td><?=form_dropdown('supplier',$supplier); ?></td>				
							</tr>
							<tr>
								<td colspan="6">
									<a href="#" onClick="addFormField(); return false;"><?=img('../images/administrator/edit_add.png') ?></a>
								</td>
							</tr>
							<tr>
								<td colspan="6">
								<input type="hidden" id="id" value="1">
								<br />
								<div id="divInvoice"></div>
									<br /><br /><br />
								</td>
							</tr>
							<tr>
								<td><?=form_label(lang('paid')); ?></td>
								<td><?=form_checkbox('paid','paid', TRUE); ?></td>
								<td><?=lang('payment_type'); ?> :</td>
								<td id="type"><?=form_dropdown('type',$payment_type); ?></td>
								<td id="bank_label"><?=lang('bank_account'); ?> :</td>
								<td id="account_id"><?=form_dropdown('account_id',$bank_account); ?></td>
							</tr>
							<tr>
								<td colspan="6"><input type="submit" value="<?=lang('save'); ?>"></td>
							</tr>
						</tbody>
					</table>
				</form>
			</div>	
			</div>
			<div class="Right">				
			</div>
        </div>
        </div>
		</div>
	</div>
</div>
<div class="ClearAll"></div>