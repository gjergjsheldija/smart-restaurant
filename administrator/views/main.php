<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Smart Restaurant
 *
 * An open source application to manage restaurants
 *
 * @package		SmartRestaurant
 * @author		Gjergj Sheldija
 * @copyright	Copyright (c) 2008, Gjergj Sheldija
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

<script type="text/javascript" src="<?=base_url(); ?>../min/?b=smartres/trunk/js&amp;f=jquery.js,ColorPicker.js,jquery.easing.js,jquery.dimensions.js,ui.core.js,ui.accordion.js,ui.tabs.js,ui.datepicker.js,jquery.jeditable.js,jquery.cluetip.js,jquery.validate.js,jquery.metadata.js,jquery.form.js,jquery.highlightFade.js"></script>
<link type="text/css" rel="stylesheet" href="<?=base_url(); ?>../min/?b=smartres/trunk/css&amp;f=stylesheet.css,ColorPicker.css,menu.css,ui.tabs.css,ui.datepicker.css,jquery.cluetip.css" />
</head>
<body>

<?=$this->load->view('menu');?>
<?php if(isset($body)) echo $body; else echo '';?>
<br /><br /><br />
<p align="center"><strong>Copyright (c) 2008 Smart Restaurant</strong></p>
</body>
</html>