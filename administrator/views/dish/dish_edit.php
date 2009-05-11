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

<?=form_open_multipart('dish/save');?>
<table>
	<thead>
		<tr>
			<th colspan="2">
			<h2><?=lang('dish_info'); ?></h2><br />
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=form_hidden('id',$edit[0]->id) ?> <?=form_label(lang('name'));?> :</td>
			<td><?=form_input('name',$edit[0]->name); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('price'));?> :</td>
			<td><?=form_input('price',$edit[0]->price); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('category'));?> :</td>
			<td><?=form_dropdown('category',$category,$edit[0]->catid); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('printer'));?> :</td>
			<td><?=form_dropdown('destid',$printer,$edit[0]->destid); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('image'));?> :</td>
			<td><?=form_upload('image',$edit[0]->image); ?></td>
		</tr>
		<tr>
			<td><?=isset($edit[0]->image) ? img('../'.$edit[0]->image) : lang('no_info'); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><input type="submit" value="<?=lang('save'); ?>"></td>
			<td></td>
		</tr>
	</tbody>
</table>
</form><hr>
<br /><h2><?=lang('ingredients'); ?></h2><br />
<!-- perberesit -->
<?=form_open_multipart('dish/insertIngredient');?>
<table>
<tr>
	<td><?=form_label(lang('ingredients'));?> :</td>
	<td><?=form_dropdown('ingredient_id',$ingredients); ?></td>
	<td><?=form_hidden('dish_id',$edit[0]->id); ?><input type="submit" value="<?=lang('add'); ?>"></td>
</tr>
</table>
<hr>
<table cellpadding="5">
<thead>
	<tr>
		<th class="index_table sortable"><?=lang('ingredient'); ?></th>
		<th class="index_table sortable"><?=lang('quantity'); ?></th>
		<th class="index_table sortable"><?=lang('uom'); ?></th>
		<th class="index_table sortable"><?=lang('action'); ?></th>
	</tr>
</thead>
<tbody>
	<?php foreach($ingredient_quantity as $ingredient) : ?>
		<tr>
			<td><?=$ingredient->name; ?></td>
			<td align="right"><div id="quantity_<?=$ingredient->object_id; ?>"><?=$ingredient->quantity; ?></div><?=form_hidden('object_id_'.$ingredient->object_id ,$ingredient->object_id); ?></td>
			<td align="right"><div id="unit_type_<?=$ingredient->stock_id; ?>"><?=$ingredient->unit_type == '2' ? 'lt' : 'kg' ;?></div><?=form_hidden('stock_id_'.$ingredient->stock_id,$ingredient->stock_id); ?></td>
			<td align="right"><?=anchor_image('dish/deleteIngredient/'.$ingredient->object_id . "-" . $edit[0]->id . "-" . $ingredient->id, 'images/administrator/edit_remove.png');?></td>
		</tr>
		<script type="text/javascript">
		jQuery().ready(function() {
		    $("#quantity_<?=$ingredient->object_id; ?>").editable("<?=base_url() . '?c=dish&m=updateIngredientQuantity';?>", { 
		        indicator : '<?=lang('saving') ?>',
		        submit    : 'OK',
		        submitdata: { object_id: $('input[@name=object_id_<?=$ingredient->object_id ?>]').val() }
		    });
		    $("#unit_type_<?=$ingredient->stock_id; ?>").editable("<?=base_url() . '?c=dish&m=updateIngredientUnitType';?>", { 
		    	data	  : "{'1':'kg','2':'lt'}",
		        indicator : '<?=lang('saving') ?>',
		        type      : 'select',
		        submit    : 'OK',
		        submitdata: { stock_id: $('input[@name=stock_id_<?=$ingredient->stock_id ?>]').val() }
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
<?=form_open_multipart('dish/addnew');?>
<table>
	<thead>
		<tr>
			<th colspan="2">
			<h2><?=lang('new_dish'); ?></h2>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=form_hidden('id') ?> <?=form_label(lang('name'));?> :</td>
			<td><?=form_input('name'); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('price'));?> :</td>
			<td><?=form_input('price'); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('category'));?> :</td>
			<td><?=form_dropdown('category',$category); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('printer'));?> :</td>
			<td><?=form_dropdown('destid',$printer); ?></td>
		</tr>
		<tr>
			<td><?=form_label(lang('image'));?> :</td>
			<td><?=form_upload('image'); ?></td>
		</tr>
		<tr>
			<td><?=lang('no_info'); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><input type="submit" value="<?=lang('save') ?>"></td>
			<td></td>
		</tr>
	</tbody>
</table>	
</form>
<?php } ?>