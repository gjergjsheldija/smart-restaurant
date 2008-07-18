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
<?=form_open('printer/save');?>
<table>
	<thead>
		<tr>
			<th colspan="2">
			<h2><?=lang('info_printer'); ?></h2>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=form_hidden('id',$edit[0]->id) ?> <?=form_label(lang('name'));?> :</td>
			<td><?=form_input('name',$edit[0]->name); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('system_name'));?> :</td>
			<td><?=form_input('dest',$edit[0]->dest); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('driver'));?> :</td>
			<td><?=form_dropdown('driver',$driver,$edit[0]->driver); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('template'));?> :</td>
			<td><?=form_dropdown('template',$template,$edit[0]->template); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('destination'));?> :</td>
			<td><?=form_input('dest_ip',$edit[0]->dest_ip); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('print_bill'));?> :</td>
			<td><?=form_checkbox('bill',1,$edit[0]->bill); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('print_invoice'));?> :</td>
			<td><?=form_checkbox('invoice',1,$edit[0]->invoice); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('print_receipt'));?> :</td>
			<td><?=form_checkbox('receipt',1,$edit[0]->receipt); ?></td>
		</tr>
		<tr>
			<td><input type="submit" value="<?=lang('save') ?>"></td>
			<td></td>
		</tr>
	</tbody>
</table>
</form>
<?php } elseif( isset($newprinter) ) {?>
<?=form_open('printer/addnew');?>
<table>
	<thead>
		<tr>
			<th colspan="2">
			<h2><?=lang('new_printer') ?></h2>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=form_label(lang('name'));?> :</td>
			<td><?=form_input('name'); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('system_name'));?> :</td>
			<td><?=form_input('dest'); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('driver'));?> :</td>
			<td><?=form_dropdown('driver', $driver); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('template'));?> :</td>
			<td><?=form_dropdown('template', $template); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('destination'));?> :</td>
			<td><?=form_input('dest_ip'); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('print_bill'));?> :</td>
			<td><?=form_checkbox('bill',1); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('print_invoice'));?> :</td>
			<td><?=form_checkbox('invoice',1); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('print_receipt'));?> :</td>
			<td><?=form_checkbox('receipt',1); ?></td>
		</tr>
		<tr>
			<td><input type="submit" value="<?=lang('save') ?>"></td>
			<td></td>
		</tr>
	</tbody>
</table>	
</form>
<?php } ?>