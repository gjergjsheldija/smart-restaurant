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
//stock supply specific js
function addFormField() {
	
	var id = document.getElementById("id").value;	
	var image = '<?php echo img("../images/administrator/edit_remove.png") ?>';
	$.ajax({
		type : "POST",
		url : "<?php echo base_url() . '?c=stock&m=ingredientList' ?>",
		dataType: "html", 
		success : function(data) {
			$("#divInvoice").append(
					"<tr id='row" + id + "'>" +
					"	<td></td>" +
					"	<td><select name='ingredient[]' id='ingredient" + id + "'>" + data + "</select></td>" +
					"	<td colspan='2'><input type='text' name='quantity[]' value='' id='quantity" + id + "' for='number'></td>" +
					"	<td><input type='text' name='price[]' value='' id='price" + id + "' for='number'></td>" +
					"	<td><a href='#' onClick='removeFormField(\"#row" + id + "\"); return false;'>" + image + "</a></td>" +
					"</tr>"
					);},
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
<div id="page-wrapper">
	<div id="main-wrapper">
		<div id="main-content">
			<div class="title title-spacing">
				<?php echo form_open('stock/addnew',array('id' => 'stockForm'));?>
			</div>
			<div class="two-column">
				<div class="hastable">
				<table cellspacing="0">
						<thead>
							<tr>
								<th colspan="6">
								<h2><?php echo lang('supply'); ?></h2>
								</th>
							</tr>
						</thead>
						<tbody id="divInvoice">
							<tr>
								<td><?php echo form_label(lang('date'));?> :</td>
								<?php
									$date = array(
							              'name'        => 'timestamp',
							              'id'          => 'timestamp',
										  'for'			=> 'timestamp',
							  			  'class' 		=> 'field text small');
								?>
								<td><?php echo form_input($date); ?></td>
								<td><?php echo form_label('Nr');?> :</td>
								<?php
									$invoice_id = array(
										'id'   		  => 'invoice_id',
										'name' 		  => 'invoice_id',
										'for'  		  => 'invoice_id',
							  			'class'		  => 'field text small');
										?>
								<td><?php echo form_input($invoice_id); ?></td>
								<td><?php echo form_label(lang('supplier'));?> :</td>
								<td><?php echo form_dropdown('supplier',$supplier); ?></td>				
							</tr>
							<tr>
								<td><?php echo form_label(lang('paid')); ?></td>
								<td><?php echo form_checkbox('paid','paid', TRUE); ?></td>
								<td><?php echo lang('payment_type'); ?> :</td>
								<td id="type"><?php echo form_dropdown('type',$payment_type); ?></td>
								<td id="bank_label"><?php echo lang('bank_account'); ?> :</td>
								<td id="account_id"><?php echo form_dropdown('account_id',$bank_account); ?></td>
							</tr>
							<tr>
								<td><a href="#" onClick="addFormField(); return false;"><?php echo img('../images/administrator/edit_add.png') ?></a></td>
								<td><?php echo lang('article'); ?></td>
								<td colspan="2"><?php echo lang('quantity'); ?></td>
								<td colspan="2"><?php echo lang('price'); ?></td>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="6"><input type="hidden" id="id" value="1"><input type="submit"  class="ui-state-default ui-corner-all float-right" value="<?php echo lang('save'); ?>"></td>
							</tr>
						</tfoot>
					</table>
				</form>
			</div>	
			</div>
        </div>
        </div>
		</div>
<div class="clearfix"></div>