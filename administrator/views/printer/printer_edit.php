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
	echo form_open('printer/save');?>
<table>
	<tbody>
		<tr>
			<td><?php echo form_hidden('id',$edit[0]->id) ?> <?php echo form_label(lang('name'));?> :</td>
			<td><?php echo form_input(array('name'=>'name','value' => $edit[0]->name,'class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('system_name'));?> :</td>
			<td><?php echo form_input(array('name' =>'dest','value' => $edit[0]->dest,'class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('driver'));?> :</td>
			<td><?php echo form_dropdown('driver',$driver,$edit[0]->driver,'class="field text medium"'); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('template'));?> :</td>
			<td><?php echo form_dropdown('template',$template,$edit[0]->template,'class="field text medium"'); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('destination'));?> :</td>
			<td><?php echo form_input(array('name'=>'dest_ip','value' => $edit[0]->dest_ip,'class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('print_bill'));?> :</td>
			<td><?php echo form_checkbox('bill',1,$edit[0]->bill); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('print_invoice'));?> :</td>
			<td><?php echo form_checkbox('invoice',1,$edit[0]->invoice); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('print_receipt'));?> :</td>
			<td><?php echo form_checkbox('receipt',1,$edit[0]->receipt); ?></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" value="<?php echo lang('save'); ?>" class="ui-state-default ui-corner-all float-right"></td>
		</tr>
	</tbody>
</table>
</form>
<?php } elseif( isset($newprinter) ) {?>
<?php echo form_open('printer/addnew');?>
<table>
	<tbody>
		<tr>
			<td><?php echo form_label(lang('name'));?> :</td>
			<td><?php echo form_input(array('name' => 'name','class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('system_name'));?> :</td>
			<td><?php echo form_input(array('name' => 'dest','class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('driver'));?> :</td>
			<td><?php echo form_dropdown('driver', $driver,'class="field text medium"'); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('template'));?> :</td>
			<td><?php echo form_dropdown('template', $template,'class="field text medium"'); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('destination'));?> :</td>
			<td><?php echo form_input(array('name' => 'dest_ip','class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('print_bill'));?> :</td>
			<td><?php echo form_checkbox('bill',1); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('print_invoice'));?> :</td>
			<td><?php echo form_checkbox('invoice',1); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('print_receipt'));?> :</td>
			<td><?php echo form_checkbox('receipt',1); ?></td>
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