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
				<h2><?php echo lang('actual_state') ?></h2>
			</div>
			<div class="two-column">
			<div class="column-left">
				<div class="hastable">
				<table cellspacing="0">
					<tr>
						<td><?php echo lang('total_in') ?></td>
						<td align="right"><?php echo $ar[0]['total_ar']; ?></td>
					</tr>
					<tr>
						<td><?php echo lang('total_out') ?></td>
						<td align="right"><?php echo $ap[0]['total_ap']; ?></td>
					</tr>
					<tr><td colspan="2"><hr></td></tr>	
					<tr>
						<td><?php echo lang('diff') ?></td>
						<td align="right"><?php echo $ar[0]['total_ar'] - (-$ap[0]['total_ap']); ?></td>
					</tr>	
				</table>
			</div>	
			</div>
        </div>
        </div>
	</div>
</div>
<div class="clearfix"></div>
