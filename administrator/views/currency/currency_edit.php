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
<?php echo form_open('currency/save');?>
<table>
	<thead>
		<tr>
			<th colspan="2">
			<h2><?php echo lang('currency_info'); ?></h2>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?php echo form_hidden('id',$edit[0]->id) ?> <?php echo form_label(lang('name'));?> :</td>
			<td><?php echo form_input('name',$edit[0]->name); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('currency_rate'));?> :</td>
			<td><?php echo form_input('rate',$edit[0]->rate);?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('active'));?> :</td>
			<td><?php echo form_checkbox('active',1,$edit[0]->active); ?></td>
		</tr>
		<tr>
			<td><input type="submit" value="<?php echo lang('save'); ?>"></td>
			<td></td>
		</tr>
	</tbody>
</table>	
</form>
<?php } elseif( isset($newcurrency) ) {?>
<?php echo form_open('currency/addnew');?>
<table>
	<thead>
		<tr>
			<th colspan="2">
			<h2><?php echo lang('new_currency'); ?></h2>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?php echo form_hidden('id') ?> <?php echo form_label(lang('name'));?> :</td>
			<td><?php echo form_input('name'); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('currency_rate'));?> :</td>
			<td><?php echo form_input('rate');?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('active'));?> :</td>
			<td><?php echo form_checkbox('active',1); ?></td>
		</tr>
		<tr>
			<td><input type="submit" value="<?php echo lang('save'); ?>"></td>
			<td></td>
		</tr>
	</tbody>
</table>	
</form>
<?php } ?>