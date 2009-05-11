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
<?=form_open('user/save');?>
<table>
	<thead>
		<tr>
			<th colspan="2">
			<h2><?=lang('user_info');?></h2>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=form_hidden('id',$edit[0]->id) ?> <?=form_label(lang('name'));?> :</td>
			<td><?=form_input('name',$edit[0]->name); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('password'));?> <?=form_hidden('oldpass',$edit[0]->password) ?>:</td>
			<td><?=form_password('password'); ?></td>
		</tr>		
		<tr>
			<td><?=form_label(lang('administrator'));?> :</td>
			<?php $admin = $edit[0]->level == '1022' ? TRUE : FALSE ?>
			<td><?=form_checkbox('administrator','admin', $admin); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('waiter'));?> :</td>
			<?php $waiter = $edit[0]->level == '515' ? TRUE : FALSE ?>
			<td><?=form_checkbox('waiter','waiter', $waiter); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('access'));?> :</td>
			<td><?=form_dropdown('dest_type',$dest_type,$edit[0]->dest_type); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('template'));?> :</td>
			<td><?=form_dropdown('template',$template,$edit[0]->template); ?></td>
		</tr>
		<tr>
			<td><input type="submit" value="<?=lang('save');?>"></td>
			<td></td>
		</tr>
	</tbody>
</table>
</form>
<?php } elseif( isset($newuser) ) {?>
<?=form_open('user/addnew');?>
<table>
	<thead>
		<tr>
			<th colspan="2">
			<h2><?=lang('new_user');?></h2>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=form_label(lang('name'));?> :</td>
			<td><?=form_input('name'); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('password'));?>:</td>
			<td><?=form_password('password'); ?></td>
		</tr>		
		<tr>
			<td><?=form_label(lang('administrator'));?> :</td>
			<td><?=form_checkbox('administrator','admin'); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('waiter'));?> :</td>
			<td><?=form_checkbox('waiter','waiter'); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('access'));?> :</td>
			<td><?=form_dropdown('dest_type',$dest_type); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('template'));?> :</td>
			<td><?=form_dropdown('template',$template); ?></td>
		</tr>
		<tr>
			<td><input type="submit" value="<?=lang('save') ?>"></td>
			<td></td>
		</tr>
	</tbody>
</table>	
</form>
<?php } ?>