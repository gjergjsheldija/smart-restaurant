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
<div id="page-wrapper">
	<div id="main-wrapper">
		<div id="main-content">
		<div class="title title-spacing">
			<h2><?php echo lang('users'); ?> :: <?php echo anchor('user/newUser',lang('new_user')) ?></h2>
		</div>
		<div class="two-column">
			<div class="column-left">
				<div class="hastable">
				<table cellspacing="0">
					<thead>
					<tr>
						<th><?php echo lang('name'); ?></th>
						<th><?php echo lang('role'); ?></th>
						<th><?php echo lang('access'); ?></th>
						<th><?php echo lang('action'); ?></th>
					</tr>
					</thead>					
					<tbody>
					<?php foreach($query as $row): ?>
						<tr>
							<td><?php echo $row->name ?></td>
							<td align="right"><?php echo ($row->level  == 515)  ? lang('waiter') : lang('administrator')?></td>
							<td align="right"><?php echo $row->dest_type ?></td>
							<td align="right"><?php echo anchor_image('user/edit/'.$row->id, '../images/administrator/application_edit.png');?> :: <?php echo anchor_image('user/delete/'.$row->id , '../images/administrator/cross.png');?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				</div>			
        	</div>
        	<div class="column-right">		
				<?php $this->load->view('user/user_edit') ?>
			</div>
        </div>
        </div>
	</div>
</div>
<div class="clearfix"></div>