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
<?=form_open('table/save');?>
<table>
	<thead>
		<tr>
			<th colspan="2">
			<h2><?=lang('info_table') ;?></h2>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=form_hidden('id',$edit[0]->id) ?> <?=form_label(lang('name'));?> :</td>
			<td><?=form_input('name',$edit[0]->name); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('order_nr'));?> :</td>
			<td><?=form_input('ordernum',$edit[0]->ordernum);?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('visible'));?> :</td>
			<td><?=form_checkbox('visible',1,$edit[0]->visible); ?></td>
		</tr>
		<tr>
			<td colspan="2">
				<?php 
				echo form_fieldset(lang('user'));
				foreach($users as $user ) {
					$exist = FALSE;
					if(stripos( $edit[0]->locktouser,$user['id'], 0) !== FALSE ) $exist = TRUE;
					echo form_checkbox('locktouser[' .$user['id']. ']',$user['id'], $exist);
					echo form_label($user['name']);
					echo '<br />';
				}
				echo form_fieldset_close();
				echo strpos($user['id'],$edit[0]->locktouser) === true;
				?>
			</td>
		</tr>		
		<tr>
			<td><input type="submit" value="<?=lang('save') ;?>"></td>
			<td></td>
		</tr>
	</tbody>
</table>	
</form>
<?php } elseif( isset($newtable) ) {?>
<?=form_open('table/addnew');?>
<table>
	<thead>
		<tr>
			<th colspan="2">
			<h2><?=lang('new_table') ;?></h2>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=form_hidden('id') ?> <?=form_label(lang('name'));?> :</td>
			<td><?=form_input('name'); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('order_nr'));?> :</td>
			<td><?=form_input('ordernum');?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('visible'));?> :</td>
			<td><?=form_checkbox('visible',1); ?></td>
		</tr>
		<tr>
			<td colspan="2">
				<?php 
				echo form_fieldset(lang('user'));
				foreach($users as $user ) {
					echo form_checkbox('locktouser[' .$user['id']. ']',$user['id'] );
					echo form_label($user['name']);
					echo '<br />';
				}
				echo form_fieldset_close();
				?>
			</td>
		</tr>
		<tr>
			<td><input type="submit" value="<?=lang('save') ;?>"></td>
			<td></td>
		</tr>
	</tbody>
</table>	
</form>
<?php } ?>