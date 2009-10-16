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
echo form_open_multipart('ingredient/save');?>
<table>
	<tbody>
		<tr>
			<td><?php echo form_hidden('id',$edit[0]->id) ?> <?php echo form_label(lang('name'));?> :</td>
			<td><?php echo form_input(array('name' => 'name', 'value' => $edit[0]->name,'class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('category'));?> :</td>
			<td><?php echo form_dropdown('category',$category,$edit[0]->catid,'class="field text medium"'); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('price'));?> :</td>
			<td><?php echo form_input(array('name' => 'price','value' => $edit[0]->price,'class' => 'field text small')); ?></td>
		</tr>		
		<tr>
			<td><?php echo form_label(lang('sell_price'));?> :</td>
			<td><?php echo form_input(array('name' => 'sell_price','value' => $edit[0]->sell_price,'class' => 'field text small')); ?></td>			
		</tr>
		<tr>
			<td><?php echo form_label(lang('helper'));?> :</td>
			<td><?php echo form_dropdown('stock_is_on',$stockison,$edit[0]->stock_is_on,'class="field text small"'); ?></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" value="<?php echo lang('save'); ?>" class="ui-state-default ui-corner-all float-right"></td>
		</tr>
	</tbody>
</table>	
</form>
<?php } elseif( isset($newingredient) ) {?>
<?php echo form_open_multipart('ingredient/addnew');?>
<table>
	<thead>
		<tr>
			<th colspan="2">
			<h2><?php echo lang('new_ingredient'); ?></h2>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?php echo form_hidden('id') ?> <?php echo form_label(lang('name'));?> :</td>
			<td><?php echo form_input(array('name' => 'name','class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('category'));?> :</td>
			<td><?php echo form_dropdown('category',$category,'class="field text medium"'); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('price'));?> :</td>
			<td><?php echo form_input(array('name' => 'price','class' => 'field text small')); ?></td>
		</tr>		
		<tr>
			<td><?php echo form_label(lang('sell_price'));?> :</td>
			<td><?php echo form_input(array('name' => 'sell_price','class' => 'field text small')); ?></td>			
		</tr>
		<tr>
			<td><?php echo form_label(lang('helper'));?> :</td>
			<td><?php echo form_dropdown('stock_is_on',$stockison,'class="field text small"'); ?></td>
		</tr>		
		<tr>
			<td></td>
			<td><input type="submit" value="<?php echo lang('save'); ?>" class="ui-state-default ui-corner-all float-right"></td>
		</tr>
	</tbody>
</table>	
</form>
</div>
<?php } ?>