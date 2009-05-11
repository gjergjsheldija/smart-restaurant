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
<ul class="adxm menu">
	<li><a href="<?=$this->config->site_url();?>">Home</a></li>
	<li><a href="#"><?=lang('general') ?> +</a>
		<ul>
			<li><a href="<?=$this->config->site_url();?>/category"><?=lang('categories') ?></a></li>
			<li><a href="<?=$this->config->site_url();?>/dish"><?=lang('dishes') ?></a></li>
			<li><a href="<?=$this->config->site_url();?>/ingredient"><?=lang('ingredients') ?></a></li>
			<li class="separation"></li>
			<li><a href="<?=$this->config->site_url();?>/table"><?=lang('tables') ?></a></li>
			<li><a href="<?=$this->config->site_url();?>/currency"><?=lang('currency') ?></a></li>
		</ul>
	</li>
	<li><a href="#"><?=lang('system') ?> +</a>
		<ul>
			<li><a href="<?=$this->config->site_url();?>/user"><?=lang('users') ?></a></li>
			<li><a href="<?=$this->config->site_url();?>/printer"><?=lang('printers') ?></a></li>
			<li><a href="<?=$this->config->site_url();?>/configuration"><?=lang('configuration') ?></a></li>
			<li><a href="<?=$this->config->site_url();?>/translator"><?=lang('translation') ?></a></li>
		</ul>
	</li>
	<li><a href="#"><?=lang('contacts') ?> +</a>
		<ul>
			<li><a href="<?=$this->config->site_url();?>/contacts"><?=lang('contacts') ?></a></li>
		</ul>
	</li>
	<li><a href="#"><?=lang('account') ?> +</a>
		<ul>
			<li><a href="<?=$this->config->site_url();?>/account/index/ap"><?=lang('ap') ?></a></li>
			<li><a href="<?=$this->config->site_url();?>/account/index/ar"><?=lang('ar') ?></a></li>
			<li class="separation"></li>
			<li><a href="<?=$this->config->site_url();?>/account/report_actual"><?=lang('actual_state') ?></a></li>
			<li><a href="<?=$this->config->site_url();?>/account/report_movement"><?=lang('movements') ?></a></li>
			<li><a href="<?=$this->config->site_url();?>/account/report_waiter"><?=lang('waiter_income') ?></a></li>
			<li><a href="<?=$this->config->site_url();?>/account/report_sector"><?=lang('tot_by_sect') ?></a></li>			
		</ul>
	</li>
	<li><a href="#"><?=lang('bank'); ?> +</a>
		<ul>
			<li><a href="<?=$this->config->site_url();?>/bankaccount/index/account"><?=lang('bank_account'); ?></a></li>
			<li><a href="<?=$this->config->site_url();?>/bankaccount/index/movement"><?=lang('movements'); ?></a></li>
			<li class="separation"></li>			
			<li><a href="<?=$this->config->site_url();?>/bankaccount/report_actual"><?=lang('actual_state'); ?></a></li>
			<li><a href="<?=$this->config->site_url();?>/bankaccount/report_movement"><?=lang('movements'); ?></a></li>
		</ul>
	</li>
	<li><a href="#"><?=lang('stock'); ?> +</a>
		<ul>
			<li><a href="<?=$this->config->site_url();?>/stock/index/add"><?=lang('supply'); ?></a></li>	
			<li class="separation"></li>		
			<li><a href="<?=$this->config->site_url();?>/stock/report_actual"><?=lang('inventory'); ?></a></li>
			<li><a href="<?=$this->config->site_url();?>/stock/report_movement"><?=lang('movements'); ?></a></li>
		</ul>
	</li>
	<li><a href="<?=$this->config->site_url();?>/login/doLogout"><?=lang('log_out'); ?></a></li>
</ul>
</div>