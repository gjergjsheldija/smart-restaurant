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
<div id="Header">
<ul id="topnav">
	<li><a href="<?php echo $this->config->site_url();?>">Home</a></li>
	<li><a href="#"><?php echo lang('general') ?> +</a>
		<span>
			<a href="<?php echo $this->config->site_url();?>/category"><?php echo lang('categories') ?></a> | 
			<a href="<?php echo $this->config->site_url();?>/dish"><?php echo lang('dishes') ?></a> | 
			<a href="<?php echo $this->config->site_url();?>/ingredient"><?php echo lang('ingredients') ?></a> | 
			<!--  <li class="separation"></li>  -->
			<a href="<?php echo $this->config->site_url();?>/table"><?php echo lang('tables') ?></a> | 
			<a href="<?php echo $this->config->site_url();?>/currency"><?php echo lang('currency') ?></a>
		</span>
	</li>
	<li><a href="#"><?php echo lang('system') ?> +</a>
		<span>
			<a href="<?php echo $this->config->site_url();?>/user"><?php echo lang('users') ?></a> | 
			<a href="<?php echo $this->config->site_url();?>/printer"><?php echo lang('printers') ?></a> | 
			<a href="<?php echo $this->config->site_url();?>/configuration"><?php echo lang('configuration') ?></a> | 
			<a href="<?php echo $this->config->site_url();?>/translator"><?php echo lang('translation') ?></a>
		</span>
	</li>
	<li><a href="#"><?php echo lang('contacts') ?> +</a>
		<span>
			<a href="<?php echo $this->config->site_url();?>/contacts"><?php echo lang('contacts') ?></a>
		</span>
	</li>
	<li><a href="#"><?php echo lang('account') ?> +</a>
		<span>
			<a href="<?php echo $this->config->site_url();?>/account/index/ap"><?php echo lang('ap') ?></a> | 
			<a href="<?php echo $this->config->site_url();?>/account/index/ar"><?php echo lang('ar') ?></a> | 
			<!--  <li class="separation"></li>  -->
			<a href="<?php echo $this->config->site_url();?>/account/report_actual"><?php echo lang('actual_state') ?></a> | 
			<a href="<?php echo $this->config->site_url();?>/account/report_movement"><?php echo lang('movements') ?></a> | 
			<a href="<?php echo $this->config->site_url();?>/account/report_waiter"><?php echo lang('waiter_income') ?></a> | 
			<a href="<?php echo $this->config->site_url();?>/account/report_sector"><?php echo lang('tot_by_sect') ?></a>			
		</span>
	</li>
	<li><a href="#"><?php echo lang('bank'); ?> +</a>
		<span>
			<a href="<?php echo $this->config->site_url();?>/bankaccount/index/account"><?php echo lang('bank_account'); ?></a> | 
			<a href="<?php echo $this->config->site_url();?>/bankaccount/index/movement"><?php echo lang('movements'); ?></a> | 
			<!-- <li class="separation"></li>  -->			
			<a href="<?php echo $this->config->site_url();?>/bankaccount/report_actual"><?php echo lang('actual_state'); ?></a> | 
			<a href="<?php echo $this->config->site_url();?>/bankaccount/report_movement"><?php echo lang('movements'); ?></a>
		</span>
	</li>
	<li><a href="#"><?php echo lang('stock'); ?> +</a>
		<span>
			<a href="<?php echo $this->config->site_url();?>/stock/index/add"><?php echo lang('supply'); ?></a>	 | 
			<!--  <li class="separation"></li>  -->		
			<a href="<?php echo $this->config->site_url();?>/stock/report_actual"><?php echo lang('inventory'); ?></a> | 
			<a href="<?php echo $this->config->site_url();?>/stock/report_movement"><?php echo lang('movements'); ?></a>
		</span>
	</li>
	<li style="float:right;"><a href="<?php echo $this->config->site_url();?>/login/doLogout"><?php echo lang('log_out'); ?></a></li>
</ul>
</div>