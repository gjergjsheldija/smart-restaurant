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
<?php if( isset($edit) ) { ?>
<?=form_open('contacts/save');?>
<table>
	<thead>
		<tr>
			<th colspan="2">
			<h2><?=lang('contact_info'); ?></h2>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=form_hidden('id',$edit[0]->id) ?> <?=form_label(lang('name'));?> :</td>
			<td><?=form_input('name',$edit[0]->name); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('address'));?> :</td>
			<?php
				$address = array(
					'rows' => '5',
					'cols' => '38',
					'value'=> $edit[0]->address,
					'name' => 'address'
				);
			?>
			<td><?=form_textarea($address); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('tel'));?> :</td>
			<td><?=form_input('telephone',$edit[0]->telephone); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('fax'));?> :</td>
			<td><?=form_input('fax',$edit[0]->fax); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('bank_account'));?> :</td>
			<td><?=form_input('bank_account',$edit[0]->bank_account); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('abi'));?> :</td>
			<td><?=form_input('abi',$edit[0]->abi); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('cab'));?> :</td>
			<td><?=form_input('cab',$edit[0]->cab); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('type'));?> :</td>
			<td><?=form_dropdown('type',$conttype,$edit[0]->type); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('note'));?> :</td>
			<?php
				$note = array(
					'rows' => '5',
					'cols' => '38',
					'value'=> $edit[0]->note,
					'name' => 'note'
				);
			?>
			<td><?=form_textarea($note); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('email'));?> :</td>
			<td><?=form_input('email',$edit[0]->email); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('web'));?> :</td>
			<td><?=form_input('web',$edit[0]->web); ?></td>
		</tr>
		<tr>
			<td><input type="submit" value="<?=lang('save') ?>"></td>
			<td></td>
		</tr>
	</tbody>
</table>	
</form>
<?php } elseif( isset($newcontact) ) {?>
<?=form_open('contacts/addnew');?>
<table>
	<thead>
		<tr>
			<th colspan="2">
			<h2><?=lang('new_contact'); ?></h2>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=form_label(lang('name'));?> :</td>
			<td><?=form_input('name'); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('address'));?> :</td>
			<?php
				$address = array(
					'rows' => '5',
					'cols' => '38',
					'name' => 'address'
				);
			?>
			<td><?=form_textarea($address); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('tel'));?> :</td>
			<td><?=form_input('telephone'); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('fax'));?> :</td>
			<td><?=form_input('fax'); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('bank_account'));?> :</td>
			<td><?=form_input('bank_account'); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('abi'));?> :</td>
			<td><?=form_input('abi'); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('cab'));?> :</td>
			<td><?=form_input('cab'); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('type'));?> :</td>
			<td><?=form_dropdown('type',$conttype); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('note'));?> :</td>
			<?php
				$note = array(
					'rows' => '5',
					'cols' => '38',
					'name' => 'note'
				);
			?>
			<td><?=form_textarea($note); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('email'));?> :</td>
			<td><?=form_input('email'); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('web'));?> :</td>
			<td><?=form_input('web'); ?></td>
		</tr>
		<tr>
			<td><input type="submit" value="<?=lang('save'); ?>"></td>
			<td></td>
		</tr>
	</tbody>
</table>	
</form>
<?php } ?>