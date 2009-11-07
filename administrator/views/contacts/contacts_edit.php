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
	echo form_open('contacts/save');?>
<table>
	<tbody>
		<tr>
			<td><?php echo form_hidden('id',$edit[0]->id) ?> <?php echo form_label(lang('name'));?> :</td>
			<td><?php echo form_input(array('name' => 'name','value' => $edit[0]->name,'class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('address'));?> :</td>
			<?php
				$address = array(
					'rows' => '5',
					'cols' => '38',
					'value'=> $edit[0]->address,
					'name' => 'address',
					'class' => 'field textarea small'
				);
			?>
			<td><?php echo form_textarea($address); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('tel'));?> :</td>
			<td><?php echo form_input(array('name'=>'telephone','value' => $edit[0]->telephone,'class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('fax'));?> :</td>
			<td><?php echo form_input(array('name'=>'fax','value' => $edit[0]->fax,'class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('bank_account'));?> :</td>
			<td><?php echo form_input(array('name'=>'bank_account','value'=>$edit[0]->bank_account,'class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('abi'));?> :</td>
			<td><?php echo form_input(array('name'=>'abi','value' => $edit[0]->abi,'class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('cab'));?> :</td>
			<td><?php echo form_input(array('name' => 'cab','value' => $edit[0]->cab,'class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('type'));?> :</td>
			<td><?php echo form_dropdown('type',$conttype,$edit[0]->type); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('note'));?> :</td>
			<?php
				$note = array(
					'rows' => '5',
					'cols' => '38',
					'value'=> $edit[0]->note,
					'name' => 'note',
					'class' => 'field textarea small'
				);
			?>
			<td><?php echo form_textarea($note); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('email'));?> :</td>
			<td><?php echo form_input(array('name' => 'email','value' => $edit[0]->email,'class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('web'));?> :</td>
			<td><?php echo form_input(array('name' => 'web','value' => $edit[0]->web,'class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" value="<?php echo lang('save'); ?>" class="ui-state-default ui-corner-all float-right"></td>
		</tr>
	</tbody>
</table>	
</form>
<?php } elseif( isset($newcontact) ) {
	echo form_open('contacts/addnew');?>
<table>
	<tbody>
		<tr>
			<td><?php echo form_label(lang('name'));?> :</td>
			<td><?php echo form_input(array('name' => 'name','class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('address'));?> :</td>
			<?php
				$address = array(
					'rows' => '5',
					'cols' => '38',
					'name' => 'address',
					'class' => 'field textarea small'
				);
			?>
			<td><?php echo form_textarea($address); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('tel'));?> :</td>
			<td><?php echo form_input(array('name' => 'telephone','class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('fax'));?> :</td>
			<td><?php echo form_input(array('name' => 'fax','class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('bank_account'));?> :</td>
			<td><?php echo form_input(array('name' => 'bank_account','class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('abi'));?> :</td>
			<td><?php echo form_input(array('name' => 'abi','class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('cab'));?> :</td>
			<td><?php echo form_input(array( 'name' => 'cab','class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('type'));?> :</td>
			<td><?php echo form_dropdown('type',$conttype); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('note'));?> :</td>
			<?php
				$note = array(
					'rows' => '5',
					'cols' => '38',
					'name' => 'note',
					'class' => 'field textarea small'
				);
			?>
			<td><?php echo form_textarea($note); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('email'));?> :</td>
			<td><?php echo form_input(array('name' => 'email','class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('web'));?> :</td>
			<td><?php echo form_input(array('name' => 'web','class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" value="<?php echo lang('save'); ?>" class="ui-state-default ui-corner-all float-right"></td>
		</tr>
	</tbody>
</table>	
</form>
<?php } ?>
</div>