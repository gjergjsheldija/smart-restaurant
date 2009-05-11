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
<script type="text/javascript">
jQuery().ready(function() {
	$('input[@name=date_from]').datepicker({formatDate:'yyyy-mm-dd'});
	$('input[@name=date_to]').datepicker({formatDate:'yyyy-mm-dd'});
});	
</script>	
<div id="Container">
	<div class="Full">
		<div class="contentRight">
		<div class="contentLeft">
		<div class="col">
			<div class="Left">
				<h2><?=lang('actual') ?></h2>
				<?=form_open('account/report_actual');?>
				<br /><br />
				<table>
					<tr>
						<td><?=lang('total_in') ?></td>
						<td align="right"><?=$ar[0]['total_ar']; ?></td>
					</tr>
					<tr>
						<td><?=lang('total_out') ?></td>
						<td align="right"><?=$ap[0]['total_ap']; ?></td>
					</tr>
					<tr><td colspan="2"><hr></td></tr>	
					<tr>
						<td><?=lang('diff') ?></td>
						<td align="right"><?=$ar[0]['total_ar'] - (-$ap[0]['total_ap']); ?></td>
					</tr>	
				</table>
				</form>
			</div>	
			</div>
			<div class="Right"></div>
        </div>
        </div>
		</div>
	</div>
</div>
<div class="ClearAll"></div>
