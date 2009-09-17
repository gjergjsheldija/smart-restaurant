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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<link type="text/css" rel="stylesheet" href="<?php echo base_url(); ?>../min/?g=admincss" />
<script type="text/javascript" src="<?php echo base_url(); ?>../min/?g=adminjs"></script>
</head>
<body>

<?php echo $this->load->view('menu');?>
<?php if(isset($body)) echo $body; else echo '';?>
    <div id="footer"><hr>
      <a id="smartpowered" href="http://smartres.sourceforge.net/"><img src="<?php echo $this->config->site_url();?>/../../images/administrator/logo_menu.png" alt="logo" height="35" width="60"></a>
      <p class="left">
      <br />
        Powered by <a href="http://smartres.sourceforge.net/"><strong>Smart Restaurant</strong></a>.
      </p>
      <p class="right"></p>
    </div>
</body>
</html>