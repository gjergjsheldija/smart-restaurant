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
echo form_open_multipart('category/save');?>
<table>
	<tbody>
		<tr>
			<td><?php echo form_hidden('id',$edit[0]->id) ?> <?php echo form_label(lang('name'));?> :</td>
			<td><?php echo form_input(array('name' => 'name','value' => $edit[0]->name,'class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('color'));?> :</td>
			<td><?php echo form_input(array('name'=>'htmlcolor', 'id'=>'picker1','size'=>'7','value'=>$edit[0]->htmlcolor,'class' => 'field text small'));?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('image'));?> :</td>
			<td><?php echo form_upload(array('name' => 'image','value' => $edit[0]->image,'class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td></td>
			<td><?php echo isset($edit[0]->image) ? img('../'.$edit[0]->image) : lang('no_info'); ?></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" value="<?php echo lang('save'); ?>" class="ui-state-default ui-corner-all float-right"></td>
		</tr>
	</tbody>
</table>	
</form>
<?php } elseif( isset($newcat) ) {?>
<?php echo form_open_multipart('category/addnew');?>
<table>
	<thead>
		<tr>
			<th colspan="2">
			<h2><?php echo lang('new_category'); ?></h2>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?php echo form_hidden('id') ?> <?php echo form_label(lang('name'));?> :</td>
			<td><?php echo form_input(array('name' => 'name','class' => 'field text medium')); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('color'));?> :</td>
			<td><?php echo form_input(array('name'=>'htmlcolor', 'id'=>'picker1','size'=>'7','class' => 'field text small'));?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('image'));?> :</td>
			<td><?php echo form_upload(array('name' => 'image','class' => 'field text medium')); ?></td>
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