<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Smart Restaurant
 *
 * An open source application to manage restaurants
 *
 * @package		SmartRestaurant
 * @author		Gjergj Sheldija
 * @copyright	Copyright (c) 2008-2012, Gjergj Sheldija
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
	echo form_open('user/save');?>
<table>
	<tbody>
		<tr>
			<td><?php echo form_hidden('id',$edit[0]->id) ?> <?php echo form_label(lang('name'));?> :</td>
			<td><?php echo form_input(array('name'=>'name','value' => $edit[0]->name,'class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('password'));?> <?php echo form_hidden('oldpass',$edit[0]->password) ?>:</td>
			<td><?php echo form_password(array('name'=>'password','class' => 'field text medium')); ?></td>
		</tr>		
		<tr>
			<td><?php echo form_label(lang('administrator'));?> :</td>
			<?php $admin = $edit[0]->level == '1022' ? TRUE : FALSE ?>
			<td><?php echo form_checkbox('administrator','admin', $admin); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('waiter'));?> :</td>
			<?php $waiter = $edit[0]->level == '515' ? TRUE : FALSE ?>
			<td><?php echo form_checkbox('waiter','waiter', $waiter); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('access'));?> :</td>
			<td><?php echo form_dropdown('dest_type',$dest_type,$edit[0]->dest_type,'class="field text medium"'); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('template'));?> :</td>
			<td><?php echo form_dropdown('template',$template,$edit[0]->template,'class="field text medium"'); ?></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" value="<?php echo lang('save'); ?>" class="ui-state-default ui-corner-all float-right"></td>
		</tr>
	</tbody>
</table>
</form>
<?php } elseif( isset($newuser) ) {
	echo form_open('user/addnew');?>
<table>
	<tbody>
		<tr>
			<td><?php echo form_label(lang('name'));?> :</td>
			<td><?php echo form_input(array('name' => 'name','class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('password'));?>:</td>
			<td><?php echo form_password(array('name' => 'password','class' => 'field text medium')); ?></td>
		</tr>		
		<tr>
			<td><?php echo form_label(lang('administrator'));?> :</td>
			<td><?php echo form_checkbox('administrator','admin'); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('waiter'));?> :</td>
			<td><?php echo form_checkbox('waiter','waiter'); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('access'));?> :</td>
			<td><?php echo form_dropdown('dest_type',$dest_type,'class="field text medium"'); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('template'));?> :</td>
			<td><?php echo form_dropdown('template',$template,'class="field text medium"'); ?></td>
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