<?php
/**
* My Handy Restaurant
*
* http://www.myhandyrestaurant.org
*
* My Handy Restaurant is a restaurant complete management tool.
* Visit {@link http://www.myhandyrestaurant.org} for more info.
* Copyright (C) 2003-2005 Fabio De Pascale
* 
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*
* @author		Fabio 'Kilyerd' De Pascale <public@fabiolinux.com>
* @package		MyHandyRestaurant
* @copyright		Copyright 2003-2005, Fabio De Pascale
*/
$inizio=microtime();
$dont_get_session_sourceid=true;

session_start();

require("./conf/config.inc.php");
require("./conf/config.constants.inc.php");
require("./funs_common.php");
require("./include/cache_class.php");

echo common_header('Administration');

$link = mysql_pconnect ($cfgserver, $cfguser,$cfgpassword) or die (GLOBALMSG_DB_CONNECTION_ERROR);

if(!isset($_SESSION['common_db']))
	$_SESSION['common_db']=$db_common;

if(get_conf(__FILE__,__LINE__,"default_language")=="") $conf_language="en";
else $conf_language=get_conf(__FILE__,__LINE__,"default_language");
require("./lang/lang_".$conf_language.".php");

unset_source_vars();

switch ($_REQUEST['command']){
	case 'halt0':
		echo "<FORM ACTION=\"reset.php\" METHOD=POST>\n";
		echo "<INPUT TYPE=\"HIDDEN\" NAME=\"command\" VALUE=\"halt1\">";
		echo "
		$msg_admin_confirmhalt
		<br>
		<INPUT TYPE=\"checkbox\" name=\"halt\" value=\"1\">$msg_halt<br><br>
		<br><br>
		$msg_admin_confirmreset
		<br><INPUT TYPE=\"checkbox\" name=\"reset\" value=\"1\">$msg_resetorders<br><br>

		<INPUT TYPE=\"submit\" value=\"$but_halt\"><br><br><br>
		</FORM>
		";
		break;
	case 'halt1':
		if($_POST['halt']==1){
			echo '<body bgcolor='.COLOR_BACK_OK.'>';
			if($_POST['reset']==1){
				$table=$GLOBALS['table_prefix'].'orders';
				$res = mysql_db_query($db_common,"TRUNCATE $table");

				echo "$msg_reset_orders_ok<br>";
			}
			$out=system("/sbin/shutdown -h now",$outerr);

			echo "$msg_halt_ok<br>";
		}
		break;
	case 'reset_orders0':
		echo "<FORM ACTION=\"reset.php\" METHOD=POST>\n";
		echo "<INPUT TYPE=\"HIDDEN\" NAME=\"command\" VALUE=\"reset_orders1\">";
		echo "
		$msg_admin_confirm_reset_orders

		<br><INPUT TYPE=\"checkbox\" name=\"reset\" value=\"1\">$msg_reset_orders<br><br>

		<INPUT TYPE=\"submit\" value=\"$but_reset_orders\"><br><br><br>
		</FORM>
		";
		break;
	case 'reset_orders1':
			if($_POST['reset']==1){
				$table=$GLOBALS['table_prefix'].'orders';
				$res = mysql_db_query($db_common,"TRUNCATE $table");
				echo '<body bgcolor='.COLOR_BACK_OK.'>';

				echo "$msg_reset_orders_ok<br><br>";
			}
			break;
	case 'reset_sources0':
		echo "<FORM ACTION=\"reset.php\" METHOD=POST>\n";
		echo "<INPUT TYPE=\"HIDDEN\" NAME=\"command\" VALUE=\"reset_sources1\">";
		echo "
		$msg_admin_confirm_reset_sources

		<br><INPUT TYPE=\"checkbox\" name=\"reset\" value=\"1\">$msg_reset_orders<br><br>

		<INPUT TYPE=\"submit\" value=\"$but_reset_sources\"><br><br><br>
		</FORM>
		";
		break;
	case 'reset_sources1':
			if($_POST['reset']==1){
				$table=$GLOBALS['table_prefix'].'orders';
				$res = mysql_db_query($db_common,"TRUNCATE $table");

				$table=$GLOBALS['table_prefix'].'sources';
				$query="UPDATE $table SET
				`userid` = '0'
				,`toclose` = '0'
				,`discount` = '0.00'
				,`paid` = '0'
				,`catprinted` = ''
				,`last_access_time`='0'
				,`last_access_userid`='0'
				,`takeaway_surname`=''
				,`takeaway_time`='0'
				,`customer`='0'";
				$res = mysql_db_query($db_common,$query);

				if($errno=mysql_errno()) {
					echo '<body bgcolor='.COLOR_BACK_ERROR.'>';
					echo 'Errore ',$errno,' ',mysql_error();

				} else {
					echo '<body bgcolor='.COLOR_BACK_OK.'>';
					echo "$msg_reset_sources_ok<br><br>";
				}
			}
			break;
	case 'reset_all0':
		echo "<FORM ACTION=\"reset.php\" METHOD=POST>\n";
		echo "<INPUT TYPE=\"HIDDEN\" NAME=\"command\" VALUE=\"reset_all1\">";
		echo "
		RESET ALL?

		<br><INPUT TYPE=\"checkbox\" name=\"reset\" value=\"1\">RESET ALL<br><br>

		<INPUT TYPE=\"submit\" value=\"RESET ALL\"><br><br><br>
		</FORM>
		";
		break;
	case 'reset_all1':
			if($_POST['reset']==1){
				$table=$GLOBALS['table_prefix'].'customers';
				$res = mysql_db_query($db_common,"TRUNCATE $table");
				$table=$GLOBALS['table_prefix'].'orders';
				$res = mysql_db_query($db_common,"TRUNCATE $table");
				$table=$GLOBALS['table_prefix'].'last_orders';
				$res = mysql_db_query($db_common,"TRUNCATE $table");

				$table=$GLOBALS['table_prefix'].'accounting_dbs';
				$query="SELECT * FROM `$table`";
				$res = mysql_db_query ($_SESSION['common_db'],$query);
				if($errno=mysql_errno()) {
					$msg="Error in ".__FUNCTION__." array - ";
					$msg.='mysql: '.mysql_errno().' '.mysql_error();
					echo $msg,"<br>\n";
					error_msg(__FILE__,__LINE__,$msg);
					return 1;
				}
				while($arr=mysql_fetch_array($res)) {

					$truncate=array(
					'account_account_log',
					'account_accounts',
					'account_log',
					'account_mgmt_addressbook',
					'account_mgmt_main',
					'account_receipts',
					'account_stock_log'
					);
					for (reset ($truncate); list ($key, $value) = each ($truncate); ) {
						$table_local=$GLOBALS['table_prefix'].$value;
						$query="SELECT * FROM `$table_local`";
						$res_local = mysql_db_query ($arr['db'],$query);
						if(mysql_num_rows($res_local)) {
							$query_local='TRUNCATE TABLE `'.$table_local.'`';
							$res3 = mysql_db_query($arr['db'],$query_local);
							if($errno=mysql_errno()) {
								$msg="Error in ".__FUNCTION__." - ";
								$msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
								$msg.='query: '.$query_local."\n";
								error_msg(__FILE__,__LINE__,$msg);
								echo nl2br($msg)."\n";
								return $errno;
							}
						}
					}
				}

				$table=$GLOBALS['table_prefix'].'dishes';
				$query="UPDATE $table SET `stock` = '0'";

				$table=$GLOBALS['table_prefix'].'sources';
				$query="UPDATE $table SET
				`userid` = '0'
				,`toclose` = '0'
				,`discount` = '0.00'
				,`paid` = '0'
				,`catprinted` = ''
				,`last_access_time`='0'
				,`last_access_userid`='0'
				,`takeaway_surname`=''
				,`takeaway_time`='0'
				,`customer`='0'";
				$res = mysql_db_query($db_common,$query);

				if($errno=mysql_errno()) {
					echo '<body bgcolor='.COLOR_BACK_ERROR.'>';
					echo 'Errore ',$errno,' ',mysql_error();

				} else {
					echo '<body bgcolor='.COLOR_BACK_OK.'>';
					echo "$msg_reset_sources_ok<br><br>";
				}
			}
			break;
	case 'reset_access_times0':
		echo "<FORM ACTION=\"reset.php\" METHOD=POST>\n";
		echo "<INPUT TYPE=\"HIDDEN\" NAME=\"command\" VALUE=\"reset_access_times1\">";
		echo "
		$msg_admin_confirm_reset_access_times

		<br><br><INPUT TYPE=\"checkbox\" name=\"reset\" value=\"1\">$msg_reset_access_times<br><br>

		<INPUT TYPE=\"submit\" value=\"$but_reset_access_times\"><br><br><br>
		</FORM>
		";
		break;
	case 'reset_access_times1':
			if($_POST['reset']==1){

				$table=$GLOBALS['table_prefix'].'sources';
				$query="UPDATE $table SET `last_access_time`='0'";
				$res = mysql_db_query($db_common,$query);

				if($errno=mysql_errno()) {
					echo '<body bgcolor='.COLOR_BACK_ERROR.'>';
					echo 'Errore ',$errno,' ',mysql_error();

				} else {
					echo '<body bgcolor='.COLOR_BACK_OK.'>';
					echo "$msg_reset_access_times_ok<br><br>";
				}
			}
			break;
	case 'unset_all0':
		echo "<FORM ACTION=\"reset.php\" METHOD=POST>\n";
		echo "<INPUT TYPE=\"HIDDEN\" NAME=\"command\" VALUE=\"unset_all1\">";
		echo "
		Azzera sessione dell'utente in uso.<br>
		Questa funzione dovrebbe essere usata solo per debugging e testing.

		<br><br><INPUT TYPE=\"checkbox\" name=\"reset\" value=\"1\">Azzera sessione utente.<br><br>

		<INPUT TYPE=\"submit\" value=\"Azzera\"><br><br><br>
		</FORM>
		";
		break;
	case 'unset_all_1':
			if($_POST['reset']==1){
				unset($_SESSION);
				echo '<body bgcolor='.COLOR_BACK_OK.'>';
				echo "$msg_reset_access_times_ok<br><br>";
			}
			break;

	default:
?>
	<a href="reset.php?command=reset_orders0">Reset ordini</a><br><br>
	<a href="reset.php?command=reset_sources0">Reset tavoli E ordini</a><br><br>
	<a href="reset.php?command=reset_all0">Reset totale (Azzera anche tutta la contabilit&agrave;)</a><br><br>
	<a href="reset.php?command=reset_access_times0">Reset Tempi accesso</a><br><br>
	<a href="reset.php?command=unset_all0">Reset Sessione utente</a><br><br>
	<a href="reset.php?command=halt0">spegni PC</a><br><br>
<?php
		break;
}

	echo "<a href=\"reset.php\">".ucfirst(lang_get($_SESSION['language'],'BACK_TO_TABLES'))."</a><br>\n";


echo generating_time($inizio);

?>
</center>
</body>
</html>
