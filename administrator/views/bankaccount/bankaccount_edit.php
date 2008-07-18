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
<?php if( isset($edit) ) { ?>
<?=form_open('bankaccount/save');?>
<table>
	<thead>
		<tr>
			<th colspan="2">
			<h2><?=lang('bank_account_info'); ?></h2>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=form_hidden('id',$edit[0]->id) ?> <?=form_label(lang('name'));?> :</td>
			<td><?=form_input('name',$edit[0]->name); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('bank'));?> :</td>
			<td><?=form_dropdown('bank',$bankname,$edit[0]->bank); ?></td>
		</tr>		
		<tr>
			<td><?=form_label(lang('account'));?> :</td>
			<td><?=form_input('number',$edit[0]->number); ?></td>
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
			<td><?=form_label(lang('cin'));?> :</td>
			<td><?=form_input('cin',$edit[0]->cin); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('iban'));?> :</td>
			<td><?=form_input('iban',$edit[0]->iban); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('amount'));?> :</td>
			<td><?=form_input('amount',$edit[0]->amount); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('currency'));?> :</td>
			<td><?=form_input('currency',$edit[0]->currency); ?></td>
		</tr>
		<tr>
			<td><input type="submit" value="<?=lang('save') ?>"></td>
			<td></td>
		</tr>
	</tbody>
</table>	
</form>
<?php } elseif( isset($newbankaccount) ) {?>
<?=form_open('bankaccount/addnew');?>
<table>
	<thead>
		<tr>
			<th colspan="2">
			<h2><?=lang('new_bank_account'); ?></h2>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=form_label(lang('name'));?> :</td>
			<td><?=form_input('name'); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('bank'));?> :</td>
			<td><?=form_dropdown('bank',$bankname); ?></td>
		</tr>		
		<tr>
			<td><?=form_label(lang('account'));?> :</td>
			<td><?=form_input('number'); ?></td>
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
			<td><?=form_label(lang('cin'));?> :</td>
			<td><?=form_input('cin'); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('iban'));?> :</td>
			<td><?=form_input('iban'); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('amount'));?> :</td>
			<td><?=form_input('amount'); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('currency'));?> :</td>
			<td><?=form_input('currency'); ?></td>
		</tr>
		<tr>
			<td><input type="submit" value="<?=lang('save'); ?>"></td>
			<td></td>
		</tr>
	</tbody>
</table>	
</form>
<?php } ?>