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
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Smart Restaurant</title>
<link type="text/css" rel="stylesheet" href="<?php echo base_url(); ?>../min/?g=admincss" />
<link href="" rel="stylesheet" title="style" media="all" />
<script type="text/javascript" src="<?php echo base_url(); ?>../min/?g=adminjs"></script>
<?php // echo link_tag('../css/login.css') ?>
<meta name="language" content="<?php echo $this->lang->line('setting_short_language');?>" />
</head>
<body>
<div id="welcome_login" title="Administration Login">
	<p>Login to Smart Restaurant</p>
	<?php echo form_open('login/dologin',array('class' => 'forms'));?>
		<?php
		if(isset($message)) 
			echo '
			<div class="response-msg error ui-corner-all">
				<span>Error</span>
				please try again!
			</div>
			';
		?>
		<ul>
			<li>
				<label for="username" class="desc"><?php echo lang('username') ;?>:</label>
				<div><input name="username" type="text" id="username" value="" class="field text full" /></div>
			</li>
			<li>
				<label for="password" class="desc"><?php echo lang('password') ;?>:</label>
				<div><input name="password" type="password" id="password" value=""  class="field text full" /><div>
			</li>
			<li>
				<label for="language" class="desc"><?php echo lang('language') ;?>:</label>
				<div><select name="language" class="field select large"><?php echo $langDropDown;?></select></div>	
			</li>
		</ul>
	</form>
</div>
</body>
</html>
