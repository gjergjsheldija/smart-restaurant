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
<div class="hastable">
<?php if( isset($edit) ) { 
echo form_open('bankaccount/save');?>
<table>
	<thead>
		<tr>
			<th colspan="2">
			<h2><?php echo lang('bank_account_info'); ?></h2>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?php echo form_hidden('id',$edit[0]->id) ?> <?php echo form_label(lang('name'));?> :</td>
			<td><?php echo form_input(array('name'=>'name','value' => $edit[0]->name,'class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('bank'));?> :</td>
			<td><?php echo form_dropdown('bank',$bankname,$edit[0]->bank,'class="field text medium"'); ?></td>
		</tr>		
		<tr>
			<td><?php echo form_label(lang('account'));?> :</td>
			<td><?php echo form_input(array('name' => 'number','value' => $edit[0]->number,'class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('abi'));?> :</td>
			<td><?php echo form_input(array('name' => 'abi','value' => $edit[0]->abi,'class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('cab'));?> :</td>
			<td><?php echo form_input(array('name' => 'cab','value' => $edit[0]->cab,'class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('cin'));?> :</td>
			<td><?php echo form_input(array('name' => 'cin','value' => $edit[0]->cin,'class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('iban'));?> :</td>
			<td><?php echo form_input(array('name' => 'iban','value' => $edit[0]->iban,'class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('amount'));?> :</td>
			<td><?php echo form_input(array('name' => 'amount','value' => $edit[0]->amount,'class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('currency'));?> :</td>
			<td><?php echo form_input(array('name' => 'currency','value' => $edit[0]->currency,'class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" value="<?php echo lang('save'); ?>" class="ui-state-default ui-corner-all float-right"></td>
		</tr>
	</tbody>
</table>	
</form>
<?php } elseif( isset($newbankaccount) ) {?>
<?php echo form_open('bankaccount/addnew');?>
<table>
	<thead>
		<tr>
			<th colspan="2">
			<h2><?php echo lang('new_bank_account'); ?></h2>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?php echo form_label(lang('name'));?> :</td>
			<td><?php echo form_input(array('name'=>'name','class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('bank'));?> :</td>
			<td><?php echo form_dropdown('bank',$bankname,'class="field text medium"'); ?></td>
		</tr>		
		<tr>
			<td><?php echo form_label(lang('account'));?> :</td>
			<td><?php echo form_input(array('name' => 'number','class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('abi'));?> :</td>
			<td><?php echo form_input(array('name' => 'abi','class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('cab'));?> :</td>
			<td><?php echo form_input(array('name' => 'cab','class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('cin'));?> :</td>
			<td><?php echo form_input(array('name' => 'cin','class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('iban'));?> :</td>
			<td><?php echo form_input(array('name' => 'iban','class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('amount'));?> :</td>
			<td><?php echo form_input(array('name' => 'amount','class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('currency'));?> :</td>
			<td><?php echo form_input(array('name' => 'currency','class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" value="<?php echo lang('save'); ?>" class="ui-state-default ui-corner-all float-right"></td>
		</tr>
	</tbody>
</table>	
</form>
</div>
<?php } ?>