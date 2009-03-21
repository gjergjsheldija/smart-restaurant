<?php
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

$inizio=microtime();			// has to be before start.php requirement!!!
session_start();

define('ROOTDIR','..');
$useridnotsetisok=1;			// has to be before start.php requirement!!!
$dont_redirect_to_menu=true;

require_once(ROOTDIR."/includes.php");
require_once(ROOTDIR."/pos/waiter_start.php");

unset_source_vars();

$tpl -> set_waiter_template_file ('authentication');
$user = new user ($_SESSION['userid']);
$err = $user->connect_pos();
$script = '	<script language="javascript" type="text/javascript">
	function setFocus() {
		document.waiterpos.password.select();
		document.waiterpos.password.focus();
	}; 
	</script>'; 
$tpl->assign('script',$script);
switch ($err) {
	case '600':
	case '601':
	case '602':
	case '603':				
			$tmp = access_connect_form_waiter_pos($err);
			$tpl->assign ('waiter_selection',$tmp);
		break;
	default:
		$tmp = redirect_waiter('tables.php');
		$tpl->append('scripts',$tmp);
		
		$tmp = '
		  <div id="positive">
		    <table width="450" cellpadding="0" cellspacing="12">
		      <tr>
		        <td width="52"><div align="center"><img src="'.IMAGE_POSITIVE.'" alt="positive" width="18" height="18" /></div></td>
		        <td width="388">'.ucfirst(phr('ALREADY_CONNECTED')).'</td> 
		        <td width="388"><a href="tables.php">'.ucfirst(phr('GO_ON')).'</a></td> 
		      </tr>
		    </table>
		  </div>
		';			
		$tpl->append ('messages',$tmp);
		break;
}
// prints page generation time
$tmp = generating_time($inizio);
$tpl->assign ('generating_time',$tmp);

if($err=$tpl->parse()) return $err; 

$tpl->clean();
$output = $tpl->getOutput();

// prints everything to screen
echo $output;

if(CONF_DEBUG_PRINT_PAGE_SIZE) echo $tpl->print_size();
?>
