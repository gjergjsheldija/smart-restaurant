<?php
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

	define('ROOTDIR','..');
	require(ROOTDIR."/conf/config.inc.php");
	require(ROOTDIR."/conf/config.constants.inc.php");

	header ("Expires: " . gmdate("D, d M Y H:i:s", time()) . " GMT");
	header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

	common_set_error_reporting ();
	
	/*
	database connection.
	we put it here, so that it is the very first thing done,
	and we always an available connection ready to use
	*/

	if(!$link = @mysql_pconnect ($cfgserver, $cfguser,$cfgpassword)) {
		header('Location: '.ROOTDIR.'/install/install.php');
		die ('Error connecting to the db');
	}
	
	$_SESSION['common_db']=$db_common;
	
	check_db_status();

	start_language ();

	$tpl = new template;
	
	/*
	we almost always use this command var, so we get it here
	to make it available to other functions whithout other hassle
	*/
	if(isset($_REQUEST['command'])){
		$command=$_REQUEST['command'];
	} else {
		$command='none';
	}

	if(isset($_REQUEST['data'])){
		$start_data=$_REQUEST['data'];
	}

	if($_SESSION['section']!="waiter"){
		unset_session_vars();
		$_SESSION['section']="waiter";
	}
	
	if(isset($dont_get_session_sourceid) && $dont_get_session_sourceid) {
		unset($_SESSION['sourceid']);
	}

	/*
	we check at least to have some tables in each db
	otherwise we stop execution and report an error
	*/
	$tmp['tableslist'] =  mysql_query("SHOW TABLES FROM " . $_SESSION['common_db']);
	$tmp['numtables'] = mysql_num_rows ($tmp['tableslist']);
	if($tmp['numtables']==0) die(GLOBALMSG_DB_NO_TABLES_ERROR);
	unset($tmp);
	
	if(!common_allowed_ip($_SERVER['REMOTE_ADDR'])) {
		$error_msg = common_header('IP address not authorized');
		$error_msg .= 'IP <b>'.$_SERVER['REMOTE_ADDR'].'</b> is not authorized.<br/>'."\n";
		$error_msg .= 'IP <b>'.sprintf("%u",ip2long($_SERVER['REMOTE_ADDR'])).'</b> is not authorized.'."\n";
		
		$error_msg .= common_bottom();
		die($error_msg);
	}
	
	$GLOBALS['cache_var']=new cache();

	if($res_loc=check_output_files ()) {
			$error_msg = common_header('Output files not writeable');
			$error_msg .=  navbar_empty();

			switch($res_loc) {
				case 1: $err='error file not writeable.<br>Solution: set write permission for everybody (or at least for the user running the webserver) on file error.log'; break;
				case 2: $err='error dir not writeable<br>Solution: set write permission for everybody (or at least for the user running the webserver) on the directory containing Smart Restaurant files '; break;
				case 3: $err='debug file not writeable.<br>Solution: set write permission for everybody (or at least for the user running the webserver) on file debug.log'; break;
				case 4: $err='debug dir not writeable'; break;
			}

			$error_msg .= GLOBALMSG_CONFIG_OUTPUT_FILES_NOT_WRITEABLE.'<br><br>Error #'.$res_loc.': '.$err.'<br>'."\n";
			$error_msg .= GLOBALMSG_CONFIG_SYSTEM.'<br>'."\n";
			$error_msg .= common_bottom();
			die($error_msg);
	}
	unset($res_loc);

	/*
	getting the source id.
	first we check if we already know this id, otherwise we try to catch it
	from a GET or POST feed (from tables.php) or we get it from the user SESSION

	Note: if $dont_get_session_sourceid is true, we won't get the sourceid from
	SESSION. this is useful when you want the source id to be unset.
	*/
	if(isset($start_data['sourceid'])){
		$_SESSION['sourceid']=(int) ($start_data['sourceid']);
	}
	
	if(!isset($useridnotsetisok)) $useridnotsetisok=false;

	// Waiter identification
	if (isset($_REQUEST['userid'])) {
		/*
		Case 1: we get the waiter id from a POST
		this happens when we receive data from the index.php page

		here we save into session the waiter id and name, for later use
		*/
		$_SESSION['userid']=$_REQUEST['userid'];

		start_language ();
	} elseif(!$useridnotsetisok && !$_SESSION['userid']) {
		/*
		2 case: we didn't find any POST nor any SESSION giving us userid,
		and we don't like it!

		the var $useridnotsetisok is checked because
		we assume that having not authenticated is good in some cases
		eg when the waiter has not authenticated yet (index.php)
		or in other cases when it is not required to be authenticated

		to activate this flag, just write the following line BEFORE the require line:
		// ---- example begin ----
		$useridnotsetisok=1;			//has to be before start.php requirement!!!
		require("./start.php");
		// ---- example end ----
		*/

		/*
		We stop execution, because the waiter has to authenticate first
		so we suggest him/her to authenticate on index.php page
		*/
		$error_msg = common_header('Waiter not connected');
		$error_msg .= redirect_waiter('index.php');
		$error_msg .= phr('MSG_WAITER_NOT_CONNECTED_ERROR').'<br>
	<a href="index.php">'.ucfirst(phr('CONNECT')).'</a>';
		$error_msg .= common_bottom();
		die($error_msg);
	}
	unset($res);
	unset($arr);
	
	/*
	The next if contains a primitive access control
	to avoid that 2 waiters simultaneously work on the same table

	this is done by updating a timestamp field in the sources table
	and by checking (if the waiter changed)
	if the elapsed time from the last update is > than the config lock_time
	*/
	if (isset($_SESSION['sourceid']) && $_SESSION['sourceid']){
		if(table_lock_check($_SESSION['sourceid'])) {
			$remaining_time=table_lock_remaining_time($_SESSION['sourceid']);
			
			$user = new user ($_SESSION['userid']);
			
			if($remaining_time==0) $remaining_time = 1;
			$error_msg = common_header('Tavolina ne perdorim');
			$error_msg .=  navbar_lock_retry_pos('');
	
			$error_msg .= ucfirst(phr('TABLE_ALREADY_IN_USE_ERROR')).' '.'.<br><br>'."\n";
			$error_msg .= ucfirst(phr('IF_YOU_ARE_NOT_DISCONNECT_0')).' <b>'.$user->data['name'].'</b> '.ucfirst(phr('IF_YOU_ARE_NOT_DISCONNECT_1')).'<br>'."\n";
			$error_msg .= common_bottom();
			die($error_msg);
		}
	}

	/*
	We get the printed categories flag, and write to $_SESSION['catprinted'][]
	*/
	if (isset($_SESSION['sourceid'])){
		$query="SELECT * FROM `sources` WHERE `id`='".$_SESSION['sourceid']."'";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
		
		$arr = mysql_fetch_array ($res);
		$catprinted_total=$arr['catprinted'];

		$catprinted_total=explode (" ", $catprinted_total);
		for($i=1;$i<=3;$i++){
			if (in_array ("$i", $catprinted_total)){
				$_SESSION['catprinted'][$i]=true;
			} else {
				$_SESSION['catprinted'][$i]=false;
			}
		}
		unset($res);
		unset($arr);
		unset($catprinted_total);
	}


	
	header("Content-Language: ".$_SESSION['language']);
	header("Content-type: text/html; charset=".phr('CHARSET'));
	
	$tmp = head_line('Waiters Section');
	$tpl -> assign("head", $tmp);

	if(!isset($dont_redirect_to_menu)) {
		$time_refresh=1000*get_conf(__FILE__,__LINE__,'refresh_automatic_to_menu');
		if($time_refresh) {
			$tmp = redirect_timed('tables.php',$time_refresh);
			$tpl->append("scripts", $tmp);
		}
	}
	
	$scripts = '<script language="javascript" type="text/javascript">
				function loadDish ( pageUrl ) {
						$.ajax( {
							type: "POST",
							url: pageUrl,
							data : \'\',
							success: function ( html ) {
								$( "#dishes_response" ).html(html);
							}
						} );
					}
				function dishOrder ( dishID ) {
					var priority = $("input[name=\'data[priority]\']:checked").val(); 
					var from_category = $("input[name=\'from_category\']").val(); 
					var command = $("input[name=\'command\']").val(); 
					var quantity = $("#dishquantity").val();
					
					$.ajax( {
						type: "POST",
						url: "orders.php",
						data : "dishid=" + dishID + "&data[priority]=" + priority + "&from_category=" + from_category + "&command=" + command + "&data[quantity]=" + quantity,
						success: function ( html ) {			
							$( "#receiptMenu_response" ).html(html);
						}
					} );
				}
				
				function quickDishOrder (  ) {
					var quickDishID = $("#quickdishid").val();
					var priority = $("input[name=\'data[priority]\']:checked").val(); 
					var from_category = $("input[name=\'from_category\']").val(); 
					var command = $("input[name=\'command\']").val(); 
					var quantity = $("#dishquantity").val();
					
					$.ajax( {
						type: "POST",
						url: "orders.php",
						data : "dishid=" + quickDishID + "&data[priority]=" + priority + "&from_category=" + from_category + "&command=" + command + "&data[quantity]=" + quantity,
						success: function ( html ) {			
							$( "#receiptMenu_response" ).html(html);
						}
					} );
					$("#quickdishid").val("");
					return false;
				}
				
				function modifyDishOrder ( formName ) {
					 var formUrl = $("[name=" + formName + "]").serialize();
					 $.ajax( {
						type: "POST",
						url: "orders.php",
						data : formUrl,
						success: function ( html ) {	
							$( "#receiptMenu_response" ).html(html);
							$.modal.close();
						}
					} );
					return false;
				}
				
				function separatedBills ( formName ) {
					 var formUrl = $("[name=" + formName + "]").serialize();
					 $.ajax( {
						type: "POST",
						url: "orders.php",
						data : formUrl,
						success: function ( html ) {	
							$.modal.close();
							$(html).modal({
								close: false,
								position: ["15%",],
								onClose: function (dialog) {$.modal.close();}
							})
						}
					});
					return false;
				}
								
				function applyDiscount ( formName ) {
					var formUrl = $("[name=" + formName + "]").serialize();
					var pageurl = "orders.php?" +  formUrl;
					$.modal.close();
					$.get(pageurl,  
						function(returned_data){
							$(returned_data).modal({
								close: false,
								position: ["15%",],
								onClose: function (dialog) {$.modal.close();}
						})
					});
					return false;
				}
								
				function modifyDishQuantity(dataPost) {
					$.ajax( {
						type: "POST",
						url: "orders.php",
						data : dataPost,
						success: function ( html ) {			
							$( "#receiptMenu_response" ).html(html);
						}
					} );					
				}
				
				function generalDishModifier(urldata) {
					$.ajax( {
						type: "POST",
						url: urldata,
						success: function ( html ) {			
							$("#receiptMenu_response").html(html);
							$.modal.close();
						}
					} );					
				}				
				
				function setDishSelectedQuantity (priority ) {
					if(priority == 0 )
						priority = 1
					else 
						priority = priority + 1;
					$("#dishquantity").val(priority);
				}
				
				function loadModal ( pageurl ) {
					$.get(pageurl,  
						function(returned_data){
							$(returned_data).modal({
								close: false,
								position: ["15%",],
								onClose: function (dialog) {$.modal.close();}
						})
					});
				}
				
				function lookupCustomer(inputCustomer) {
					if(inputCustomer.length == 0) {
						$("#suggestions").hide();
					} else {
						$.post("orders.php?command=customer_search", {queryString: ""+inputCustomer+""}, function(data){
							if(data.length >0) {
								$("#suggestions").show();
								$("#autoSuggestionsList").html(data);
							}
						});
					}
				} 
				
				function fillCustomer(thisValue, customer) {
					//alert(thisValue);
					//alert(customer);
					$.ajax( {
						type: "POST",
						url: thisValue,
						success: function ( html ) {			
							$("#inputCustomer").val(customer);
							setTimeout("$(\'#suggestions\').hide();", 200);
						}
					} );
				}	
							
				</script>'; 
	
	$tpl->append("scripts", $scripts);
	if(isset($_SESSION['sourceid']) && $_SESSION['sourceid']) $tmp = table_people_number_line ($_SESSION['sourceid']);
	$tpl -> assign("people_number", $tmp);
?>
