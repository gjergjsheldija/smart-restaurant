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

<?php echo form_open_multipart('dish/save');?>
<table>
	<thead>
		<tr>
			<th colspan="2">
			<h2><?php echo lang('dish_info'); ?></h2><br />
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?php echo form_hidden('id',$edit[0]->id) ?> <?php echo form_label(lang('name'));?> :</td>
			<td><?php echo form_input('name',$edit[0]->name); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('price'));?> :</td>
			<td><?php echo form_input('price',$edit[0]->price); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('category'));?> :</td>
			<td><?php echo form_dropdown('category',$category,$edit[0]->catid); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('printer'));?> :</td>
			<td><?php echo form_dropdown('destid',$printer,$edit[0]->destid); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('image'));?> :</td>
			<td><?php echo form_upload('image',$edit[0]->image); ?></td>
		</tr>
		<tr>
			<td><?php echo isset($edit[0]->image) ? img('../'.$edit[0]->image) : lang('no_info'); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><input type="submit" value="<?php echo lang('save'); ?>"></td>
			<td></td>
		</tr>
	</tbody>
</table>
</form><hr>
<br /><h2><?php echo lang('ingredients'); ?></h2><br />
<!-- perberesit -->
<?php echo form_open_multipart('dish/insertIngredient');?>
<table>
<tr>
	<td><?php echo form_label(lang('ingredients'));?> :</td>
	<td><?php echo form_dropdown('ingredient_id',$ingredients); ?></td>
	<td><?php echo form_hidden('dish_id',$edit[0]->id); ?><input type="submit" value="<?php echo lang('add'); ?>"></td>
</tr>
</table>
<hr>
<table cellpadding="5">
<thead>
	<tr>
		<th class="index_table sortable"><?php echo lang('ingredient'); ?></th>
		<th class="index_table sortable"><?php echo lang('quantity'); ?></th>
		<th class="index_table sortable"><?php echo lang('uom'); ?></th>
		<th class="index_table sortable"><?php echo lang('action'); ?></th>
	</tr>
</thead>
<tbody>
	<?php foreach($ingredient_quantity as $ingredient) : ?>
		<tr>
			<td><?php echo anchor('ingredient/edit/'.$ingredient->id,$ingredient->name); ?></td>
			<td align="right"><div id="quantity_<?php echo $ingredient->object_id; ?>"><?php echo $ingredient->quantity; ?></div><?php echo form_hidden('object_id_'.$ingredient->object_id ,$ingredient->object_id); ?></td>
			<td align="right"><div id="unit_type_<?php echo $ingredient->stock_id; ?>"><?php echo $uom[$ingredient->unit_type] ;?></div><?php echo form_hidden('stock_id_'.$ingredient->stock_id,$ingredient->stock_id); ?></td>
			<td align="right"><?php echo anchor_image('dish/deleteIngredient/'.$ingredient->object_id . "-" . $edit[0]->id . "-" . $ingredient->id, '../images/administrator/edit_remove.png');?></td>
		</tr>
		<script type="text/javascript">
		jQuery().ready(function() {
		    $("#quantity_<?php echo $ingredient->object_id; ?>").editable("<?php echo base_url() . '?c=dish&m=updateIngredientQuantity';?>", { 
		        indicator : '<?php echo lang('saving') ?>',
		        submit    : 'OK',
		        submitdata: { object_id: $('input[name=object_id_<?php echo $ingredient->object_id ?>]').val() }
		    });
		    $("#unit_type_<?php echo $ingredient->stock_id; ?>").editable("<?php echo base_url() . '?c=dish&m=updateIngredientUnitType';?>", { 
		    	data	  : "{'1':'kg','2':'lt','0':'pc'}",
		        indicator : '<?php echo lang('saving') ?>',
		        type      : 'select',
		        submit    : 'OK',
		        submitdata: { stock_id: $('input[name=stock_id_<?php echo $ingredient->stock_id ?>]').val() }
		    });
		});
		</script>		
	<?php endforeach; ?>
</tbody>
</table>
</form>
</div>
</div>
<?php } elseif( isset($newdish) ) {?>
<?php echo form_open_multipart('dish/addnew');?>
<table>
	<thead>
		<tr>
			<th colspan="2">
			<h2><?php echo lang('new_dish'); ?></h2>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?php echo form_hidden('id') ?> <?php echo form_label(lang('name'));?> :</td>
			<td><?php echo form_input('name'); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('price'));?> :</td>
			<td><?php echo form_input('price'); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('category'));?> :</td>
			<td><?php echo form_dropdown('category',$category); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('printer'));?> :</td>
			<td><?php echo form_dropdown('destid',$printer); ?></td>
		</tr>
		<tr>
			<td><?php echo form_label(lang('image'));?> :</td>
			<td><?php echo form_upload('image'); ?></td>
		</tr>
		<tr>
			<td><?php echo lang('no_info'); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><input type="submit" value="<?php echo lang('save') ?>"></td>
			<td></td>
		</tr>
	</tbody>
</table>	
</form>
<?php } ?>