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
<?=link_tag('../css/login.css') ?>
<meta name="language" content="<?php echo $this->lang->line('setting_short_language');?>" />
</head>
<body class="login">
<div class="Container">
<div id="Dialog">
<h1>Login</h1>
<?=form_open('login/dologin');?>
	<?php
	if(isset($message)) 
		echo '<p align="center"><strong><font color="#990000">Error, please try again!</font></strong></p>';
	?>
	<dl>
		<dt><?=lang('username') ;?>:</dt>
		<dd><input name="username" type="text" id="username" value="" /></dd>
		<dt><?=lang('password') ;?>:</dt>
		<dd><input name="password" type="password" id="password" value="" /></dd>
		<dd> <input type="submit" value="login" /></dd>
	</dl>
</form>
</div>
<dd>Powered by <a href="http://smartres.sourceforge.net/">Smart Restaurant</a></dd>
</div>
</body>
</html>
