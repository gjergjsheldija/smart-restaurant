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
<div id="header">
	<div id="top-menu">
		<a class="set_theme" id="default" href="javascript:void(0);" style="font-weight: bold;" title="Default Theme">Default</a> 
		| <a class="set_theme" id="light_blue" href="javascript:void(0);" title="Light Blue Theme">Light Blue</a>
		<span>
			<a href="javascript:void(0);" title="Fluid Layout" id="fluid_layout"><b>Fluid Layout</b></a>
			<a href="javascript:void(0);" title="Fixed Layout" id="fixed_layout"><b>Fixed Layout</b></a>
		</span>
		<span>Logged in as <a href="#" title="Logged in as admin">admin</a></span>
		| <a class="tooltip" href="<?php echo $this->config->site_url();?>/login/doLogout" title="<?php echo lang('log_out'); ?>"><?php echo lang('log_out'); ?></a>
	</div>
	<div id="sitename">
		<a href="<?php echo $this->config->site_url();?>" class="logo float-left" title="Administration">Administration</a>
	</div>
	<ul id="navigation" class="sf-navbar">
		<li><a href="<?php echo $this->config->site_url();?>">Home</a></li>
		<li><a href="#"><?php echo lang('general') ?></a>
			<ul>
				<li><a href="<?php echo $this->config->site_url();?>/category"><?php echo lang('categories') ?></a></li>
				<li><a href="<?php echo $this->config->site_url();?>/dish"><?php echo lang('dishes') ?></a></li> 
				<li><a href="<?php echo $this->config->site_url();?>/ingredient"><?php echo lang('ingredients') ?></a></li> 
				<li><a href="<?php echo $this->config->site_url();?>/table"><?php echo lang('tables') ?></a></li> 
				<li><a href="<?php echo $this->config->site_url();?>/currency"><?php echo lang('currency') ?></a></li>
			</ul>
		</li>
		<li><a href="#"><?php echo lang('system') ?></a>
			<ul>
				<li><a href="<?php echo $this->config->site_url();?>/user"><?php echo lang('users') ?></a></li>
				<li><a href="<?php echo $this->config->site_url();?>/printer"><?php echo lang('printers') ?></a></li> 
				<li><a href="<?php echo $this->config->site_url();?>/configuration"><?php echo lang('configuration') ?></a></li> 
				<li><a href="<?php echo $this->config->site_url();?>/translator"><?php echo lang('translation') ?></a></li>
			</ul>
		</li>
		<li><a href="#"><?php echo lang('contacts') ?></a>
			<ul>
				<li><a href="<?php echo $this->config->site_url();?>/contacts"><?php echo lang('contacts') ?></a></li>
			</ul>
		</li>
		<li><a href="#"><?php echo lang('account') ?></a>
			<ul>
				<li><a href="<?php echo $this->config->site_url();?>/account/index/ap"><?php echo lang('ap') ?></a></li>
				<li><a href="<?php echo $this->config->site_url();?>/account/index/ar"><?php echo lang('ar') ?></a></li>
				<li><a href="<?php echo $this->config->site_url();?>/account/report_actual"><?php echo lang('actual_state') ?></a></li> 
				<li><a href="<?php echo $this->config->site_url();?>/account/report_movement"><?php echo lang('movements') ?></a></li>
				<li><a href="<?php echo $this->config->site_url();?>/account/report_waiter"><?php echo lang('waiter_income') ?></a></li>
				<li><a href="<?php echo $this->config->site_url();?>/account/report_sector"><?php echo lang('tot_by_sect') ?></a></li>			
			</ul>
		</li>
		<li><a href="#"><?php echo lang('bank'); ?></a>
			<ul>
				<li><a href="<?php echo $this->config->site_url();?>/bankaccount/index/account"><?php echo lang('bank_account'); ?></a></li>
				<li><a href="<?php echo $this->config->site_url();?>/bankaccount/index/movement"><?php echo lang('movements'); ?></a></li> 
				<li><a href="<?php echo $this->config->site_url();?>/bankaccount/report_actual"><?php echo lang('actual_state'); ?></a></li>
				<li><a href="<?php echo $this->config->site_url();?>/bankaccount/report_movement"><?php echo lang('movements'); ?></a></li>
			</ul>
		</li>
		<li><a href="#"><?php echo lang('stock'); ?></a>
			<ul>
				<li><a href="<?php echo $this->config->site_url();?>/stock/index/add"><?php echo lang('supply'); ?></a></li>
				<li><a href="<?php echo $this->config->site_url();?>/stock/report_actual"><?php echo lang('inventory'); ?></a></li>
				<li><a href="<?php echo $this->config->site_url();?>/stock/report_movement"><?php echo lang('movements'); ?></a></li>
			</ul>
		</li>
	</ul>
</div>