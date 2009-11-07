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
				<h2><?php echo lang('contacts'); ?> :: <?php echo anchor('contacts/newContact',lang('new_contact')) ?></h2>
			</div>
			<div class="two-column">
			<div class="column-left">
				<div class="hastable">			
						<?php 
						$tmp = "";
						foreach($query->result() as $row) {	
							if($tmp != $row->contacttype) {
								echo '<div class="portlet">
								<div class="portlet-header">'.lang($row->contacttype).'</div>
								<div class="portlet-content">';?>
							<table cellspacing="0">
								<thead>
									<tr>
										<th><?php echo lang('name'); ?></th>
										<th><?php echo lang('action'); ?>&nbsp;&nbsp;</th>
									</tr>
								</thead>	
						<?php		
							}						
						?>		<tr>
									<td><?php echo $row->name ?></td>
									<td><?php echo anchor_image('contacts/edit/'.$row->contact_id, '../images/administrator/edit.png');?> :: <?php echo anchor_image('contacts/delete/'.$row->contact_id , '../images/administrator/edit_remove.png');?></td>
								</tr>
						<?php 
							$temp = $query->next_row();
							$tmp = $temp->contacttype;
							if($tmp != $row->contacttype) {
								$tmp = $row->contacttype;
								echo "</table></div></div>";
							}
						}; 
						?>
					</table></div></div>
				</div>
			</div>
			<div class="column-right">				
				<?php $this->load->view('contacts/contacts_edit') ?>
			</div>
        </div>
        </div>
	</div>
</div>
<div class="clearfix"></div>