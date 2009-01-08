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
// if(function_exists('apd_set_pprof_trace')) apd_set_pprof_trace();

// has to be before start.php to be precise timer
$inizio=microtime();
session_start();

define('ROOTDIR','..');
require_once(ROOTDIR."/includes.php");
require_once(ROOTDIR."/waiter/waiter_start.php");

$GLOBALS['end_require_time']=microtime();

//mizuko : ctrl that 2 user not on the same table
$tbl = new table ($_SESSION['sourceid']);
if($tbl -> data ['userid'] != $_SESSION['userid'] && $tbl -> data ['userid'] != '0'){
	$user = new user($_SESSION['userid']);
	$error_msg = common_header('Tavolina eshte ne perdorim');
	$error_msg .=  navbar_lock_retry('');
	
	$error_msg .= ucfirst(phr('TABLE_ALREADY_IN_USE_ERROR')).'  '.'.<br><br>'."\n";
	$error_msg .= ucfirst(phr('IF_YOU_ARE_NOT_DISCONNECT_0')).' <b>'.$user->data['name'].'</b> '.ucfirst(phr('IF_YOU_ARE_NOT_DISCONNECT_1')).'<br>'."\n";
	$error_msg .= common_bottom();
	die($error_msg);
}
//end : mizuko

if(!access_allowed(USER_BIT_WAITER) && !access_allowed(USER_BIT_CASHIER)) {
	$command='access_denied';
};

// if no command has been given, tries to infer one
if(empty($command) || $command=="none")
	$command=table_suggest_command($_SESSION['sourceid']);

$table = new table($_SESSION['sourceid']);
if (!$table -> exists() && $command!='access_denied') {
	$tmp = 'table doesn\'t exist.<br/>'."\n";
	$tpl -> append ('messages',$tmp);
	
	$tmp = navbar_menu();
	$tpl -> assign ('navbar',$tmp);

	$command = 'none';
}

$tpl -> set_waiter_template_file ('orders');

// command selection
switch ($command){
	case 'access_denied':
				access_denied_waiter();
				break;
	case 'create':
				$list = array();
				$dishid=trim($_REQUEST['dishid']);
				
				if(empty($start_data['quantity'])) $start_data['quantity']=get_conf(__FILE__,__LINE__,"default_quantity");
				
				if((!isset($start_data['priority']) || !$start_data['priority']) && $dishid != SERVICE_ID) {
					$tmp = '<b><font color="Red">'.ucfirst(phr('INSERT_PRIORITY'))."</font></b><br>\n";
					$tpl -> append ('messages',$tmp);

					$tmp = navbar_empty('javascript:history.go(-1);');
					$tpl -> assign ('navbar',$tmp);
					break;
				}
				
				// autosearch
				// the user provided a text instead of a number,
				// we look for dish
				if($dishid!='' && !is_numeric($dishid)) {
					$dish=new dish;
					$num=$dish->search_name_rows($dishid);
					// -1 means found many, go to dish list
					if($num == -1) { 
						$list['quantity']=$start_data['quantity'];
						$list['priority']=$start_data['priority'];
						$list['search']=$dishid;
						dish_list($list);
						break;
					} elseif ($num == 0) {
					// found none
						$tmp = '<b><font color="Red">'.ucfirst(phr('ERROR_NONE_FOUND'))."</font></b><br>\n";
						$tpl -> append ('messages',$tmp);
	
						orders_list ();
						break;
					} else {
					// found one, we directly assume that's the dish the user wanted
						$dishid=$num;
					}
				}
				
				$dish=new dish($dishid);
				if(!$dish->exists()) {
					$tmp = '<b><font color="Red">'.ucfirst(phr('DISH_DOES_NOT_EXIST'))."</font></b><br>\n";
					$tpl -> append ('messages',$tmp);

					orders_list ();
					break;
				}

				if($dishid) $id = orders_create ($dishid,$start_data);

				
				if($id) $err=0;
				else $err=ERR_UNKNOWN;
				
				status_report ('CREATION',$err);

				if(isset($_REQUEST['from_category']) && $_REQUEST['from_category']) {
					if (isset($_REQUEST['back_to_cat']) && $_REQUEST['back_to_cat']) $back_to_cat = true;
					else $back_to_cat = false;
				}
				if($back_to_cat) {
					$_SESSION['go_back_to_cat'] = 1;
					$dish = new dish ($dishid);
					$list['category'] = $dish -> data['category'];
					$list['priority'] = $start_data['priority'];
					
					dish_list($list);
				} else {
					$_SESSION['go_back_to_cat'] = 0;
					orders_list ();
				}
				break;
	case 'edit':
				orders_edit ($start_data);
				break;
	case 'update':
				if ($start_data['quantity']==0) {
					$err = orders_delete ($start_data);

					status_report ('DELETION',$err);

					orders_list ();
					break;
				}
				$err = orders_update ($start_data);
				status_report ('UPDATE',$err);

				$last_mod = order_get_last_modified();
				if($last_mod && isset($_SESSION['go_back_to_cat']) && $_SESSION['go_back_to_cat']) {
					$ord = new order ((int) $last_mod);
					
					$dish = new dish ($ord ->  data['dishid']);
					$list['category'] = $dish -> data['category'];
					$list['priority'] = $ord -> data['priority'];
					
					dish_list($list);
				} else {
					orders_list ();
				}
				break;
	case 'price_modify':
				order_price_modify($start_data['id']);
				break;
	case 'dish_list':
				// set to zero so last modified order is not displaid
				$_SESSION['go_back_to_cat'] = 0;
				dish_list($start_data);
				break;
	case 'set_show_orders':
				if (!isset($_SESSION['show_orders_list'])) $_SESSION['show_orders_list']=false;
				else $_SESSION['show_orders_list'] = !$_SESSION['show_orders_list'];
				orders_list();
				break;
	case 'set_show_toplist':
				if (!isset($_SESSION['show_toplist'])) $_SESSION['show_toplist']=get_conf(__FILE__,__LINE__,"top_list_show_top");
				else $_SESSION['show_toplist'] = !$_SESSION['show_toplist'];
				orders_list();
				break;
	case 'ask_delete':
				orders_ask_delete ($start_data);
				break;
	case 'delete':
				if(isset($start_data['silent'])) {
					$silent=$start_data['silent'];
					unset($start_data['silent']);
				} else $silent=false;
				
				$err = orders_delete ($start_data);

				if(!$silent) {
					status_report ('DELETION',$err);
				}
			
				orders_list ();
				break;
	case 'ask_substitute':
				orders_ask_substitute ($start_data);
				break;
	case 'substitute':
				$saved_data = orders_get_data ($start_data);
				
				$err = orders_delete ($start_data);
				
				status_report ('DELETION',$err);
				
				$start_data['quantity'] = $saved_data['quantity'];
				$start_data['priority'] = $saved_data['priority'];
				dish_list($start_data);
				break;
	case 'listmods':
				if(isset($_REQUEST['letter'])) $letter=$_REQUEST['letter']{0};
				else $letter='';
				
				if(!isset($_SESSION['go_back_to_cat'])) $_SESSION['go_back_to_cat']=0;
				
				mods_list ($start_data,$letter);
				break;
	case 'mod_set':
				$err = mods_set ($start_data);
				status_report ('MODS_SETTING',$err);

				if($_REQUEST['last']) {
					$last_mod = order_get_last_modified();
					if($last_mod && isset($_SESSION['go_back_to_cat']) && $_SESSION['go_back_to_cat']) {
						$ord = new order ((int) $last_mod);
						
						$dish = new dish ($ord ->  data['dishid']);
						$list['category'] = $dish -> data['category'];
						$list['priority'] = $ord -> data['priority'];
						
						dish_list($list);
					} else {
						orders_list ();
					}
				}
				else {
					if(isset($_REQUEST['letter']) && $_REQUEST['letter']=='ALL') $letter='ALL';
					elseif(isset($_REQUEST['letter'])) $letter=$_REQUEST['letter']{0};
					else $letter='';
					
					mods_list ($start_data,$letter);
				}
				break;
	case 'list':
				orders_list ();
				break;
	case 'ask_move':
				$tpl -> set_waiter_template_file ('tables');
				
				$tmp = navbar_empty('javascript:history.go(-1);');
				$tpl -> assign ('navbar',$tmp);
				
				$user = new user($_SESSION['userid']);
				if($user->level[USER_BIT_CASHIER]) $cols=get_conf(__FILE__,__LINE__,'menu_tables_per_row_cashier');
				else $cols=get_conf(__FILE__,__LINE__,'menu_tables_per_row_waiter');

				$table = new table ($_SESSION['sourceid']);
				$table -> move_list_tables ($cols);
				break;
	case 'move':
				$newtable = $start_data['id'];
	
				if (!$newtable) {
					orders_list ();
					break;
				}
				
				$table = new table ($_SESSION['sourceid']);
				$err = $table -> move ($newtable);
				
				status_report ('MOVEMENT',$err);
				
				if (!$err) $_SESSION['sourceid'] = $newtable;
				orders_list ();
				
				break;
	case 'service_fee':
				orders_service_fee_questions ();
				break;
	case 'ask_association':
				table_ask_association ();
				break;
	case 'associate':
				$err = table_associate ();
				status_report ('ASSOCIATION',$err);
				
				if(get_conf(__FILE__,__LINE__,"service_fee_use")) orders_service_fee_questions ();
				else orders_list ();
				
				break;
	case 'dissociate':
				$err = table_dissociate ();
				status_report ('DISSOCIATION',$err);
				
				if(!$err) {
					$redirect = redirect_waiter('tables.php');
					$tpl -> append ('scripts',$redirect);
				}
				
				orders_list ();
				break;
	case 'set_customer':
				if(table_is_takeaway($_SESSION['sourceid'])) {
					$err = takeaway_set_customer_data ($_SESSION['sourceid'],$start_data);
					status_report ('TAKEAWAY_DATA',$err);
				} else {
					$err = table_set_customer ($_SESSION['sourceid'],$start_data);
					status_report ('CUSTOMER',$err);
				}
				
				if (isset($_SESSION['select_all'])) {
					$err=bill_select();
					if($err) error_display($err);
				} else orders_list();
				
				break;
	case 'customer_insert_form':
				customer_insert_page();
				break;
	case 'customer_edit_form':
				customer_edit_page($start_data);
				break;
	case 'customer_search':
				customer_search_page($start_data);
				break;
	case 'customer_list':
				customer_search_page();
				break;
	case 'customer_insert':
				$err=customer_insert($start_data);
				status_report ('INSERT',$err);
				
				customer_search_page();
				break;
	case 'customer_edit':
				$err = customer_edit($start_data);
				status_report ('UPDATE',$err);
				
				customer_search_page();
				break;
	case 'bill_select':
				$_SESSION['select_all']=0;
				$err=bill_select();
				if($err) error_display($err);
				break;
	case 'bill_select_all':
				$_SESSION['select_all']=1;
				$err = bill_select();
				if($err) error_display($err);
				break;
	case 'bill_discount':
				if(isset($_REQUEST['discount_type'])) {
					$discount_type=$_REQUEST['discount_type'];
					$err = apply_discount($discount_type);
				} else $err=1;

				status_report ('DISCOUNT',$err);

				$err = bill_select();
				if($err) error_display($err);
				break;
	case 'bill_quantity':
				$_SESSION['select_all']=0;
		
				if(isset($_REQUEST['orderid']))$orderid=$_REQUEST['orderid'];
				else $orderid=0;
				
				if(isset($_REQUEST['operation'])) $operation=$_REQUEST['operation'];
				else $operation=0;
				
				if($orderid==0 || $operation==0) $err =1;
				else $err += bill_quantity($orderid,$operation);
		
				status_report ('QUANTITY_UPDATE',$err);
				
				$err= bill_select();
				if($err) error_display($err);
				break;
	case 'bill_print':
				if(isset($_REQUEST['type'])) $type=$_REQUEST['type'];
				//if(isset($_REQUEST['account'])) $account=$_REQUEST['account'];
				
				if(!bill_type_set($type) /*&& !bill_account_set($account) */) {
					$err = bill_print();
					
					status_report ('BILL_PRINT',$err);
					if(!$err) {
						// this allows bill_select to forget precedent selection
						$_REQUEST['keep_separated']=0;
					}
				}
				
				bill_select();
				break;
	case 'bill_reset':
				if(isset($_REQUEST['reset'])){
					$err=bill_reset($_SESSION['sourceid']);
					status_report ('BILL_RESET',$err);
					
					if($err) {
						printing_choose();
					} else {
						orders_list ();
					}
				} else {
					bill_reset_confirm();
				}
				break;
	case 'print_orders':
				$err=print_orders($_SESSION['sourceid']);
				status_report ('ORDERS_PRINT',$err);
				if (!$err) {
					orders_list ();
				} else {
					printing_choose();
				}
				break;
	case 'print_category':
				$category=(int) $start_data['category'];
				$err=print_category($category);
				status_report ('CATEGORY_PRINT',$err);
				if (!$err) {
					orders_list ();
				} else {
					printing_choose();
				}
				
				break;
	case 'printing_choose':
				printing_choose();
				break;
	case 'reopen_confirm';
				table_reopen_confirm ();
				break;
	case 'reopen':
				$err = table_reopen($_SESSION['sourceid']);
				status_report ('REOPEN',$err);
				
				orders_list ();
				break;
	case 'close_confirm':
				table_ask_close();
				break;
	case 'close':
				$err = table_close($_SESSION['sourceid']);
				status_report ('CLOSE',$err);
				if (!$err) {
					table_closed_interface();
				} else {
					orders_list ();
				}
				
				break;
	case 'closed':
				table_closed_interface();
				break;
/*	case 'pay':
				$err = table_pay($start_data['paid']);
				status_report ('PAYMENT',$err);
				
				table_closed_interface();
				break;
	case 'clear':
				$err = table_clear();
				status_report ('CLEARING',$err);
				if (!$err) {
					table_cleared_interface();
				} else {
					table_closed_interface();
				}
				break;*/
	//mizuko : begin modifikimi pageses
	case 'pay':
				$err = table_pay($start_data['paid']);
				status_report ('PAYMENT',$err);
				
				$err = table_clear();
				status_report ('CLEARING',$err);				
				
				table_cleared_interface();
				break;
	case 'clear':

				if (!$err) {
					table_cleared_interface();
				} else {
					table_closed_interface();
				}
				break;
	// mizuko : end modifikimi i pageses
	case 'none':
				break;
	default:
				orders_list ();
				break;
}
// this line is already in waiter_start, but it's here repeated because of possible modifications from waiter start till now
$tmp = table_people_number_line ($_SESSION['sourceid']);
$tpl -> assign("people_number", $tmp);

// html closing stuff and disconnect line
$tmp = disconnect_line();
$tpl -> assign ('logout',$tmp);

// prints page generation time
$tmp = generating_time($inizio);
$tpl -> assign ('generating_time',$tmp);

if($err=$tpl->parse()) return $err; 

$tpl -> clean();
echo $tpl->getOutput();

//echo 'cache:<br>'.$GLOBALS['cache_var']->show();

//$tpl ->list_vars();

if(CONF_DEBUG_PRINT_PAGE_SIZE) echo $tpl -> print_size();
?>