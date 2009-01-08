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

define('ROOTDIR','..');
require(ROOTDIR."/manage/mgmt_funs.php");
require(ROOTDIR."/manage/mgmt_start.php");

if(!access_allowed(USER_BIT_STOCK)) $command='access_denied';

switch($command) {
	case 'access_denied':
				echo access_denied_admin();
				break;
	case "item_insert":
		if(stock_insert_item($start_data))
			echo GLOBALMSG_STOCK_ADD_ERROR.".<br>\n";
		else
			echo GLOBALMSG_STOCK_ADD_OK.".<br>\n";
		stock_new_item_form();
		stock_table();
		break;
	case "edit":		
		$err=0;

		if(is_array($start_data['quantity']['new'])){
			for (reset ($start_data['quantity']['new']); list ($key, $value) = each ($start_data['quantity']['new']); ) {
				if($value){
					$data['name']=$key;
					$data['quantity']=$value;
					$data['value'] = $start_data['value']['new'][$key];
					$data['invoice_id']=$start_data['invoice_id'];
					if(movement_insert($data)!=0){
						echo GLOBALMSG_STOCK_MOVEMENT_INSERT_ERROR.".<br>\n";
						$err=1;
					}
				}
			}
		}
		if(is_array($start_data['quantity']['edit'])){
			for (reset ($start_data['quantity']['edit']); list ($key, $value) = each ($start_data['quantity']['edit']); ) {
				$data['quantity']=$value;
				$data['invoice_id']=$start_data['invoice_id'];
				if($data['quantity']!=0) {
					if(movement_update($data,$key)!=0){
						$err=1;
						echo GLOBALMSG_STOCK_MOVEMENT_UPDATE_ERROR.": ".$err.".<br>\n";
					}
				} else {
					if(movement_delete($key)!=0){
						$err=2;
						echo GLOBALMSG_STOCK_MOVEMENT_UPDATE_ERROR.": ".$err.".<br>\n";
					}
				}
			}
		}
		if(!$err) {
			echo ucphr('STOCK_UPDATE_OK').".<br>\n";
			echo("<META HTTP-EQUIV=\"Refresh\" CONTENT=\"".get_conf(__FILE__,__LINE__,"refresh_time_management")."; URL=index.php\">");
		}
		break;
	case "list":
		stock_new_item_form();
		stock_table();
		break;
	default:
		stock_new_item_form();
		stock_table();
		break;
}

if($command!="delete") unset($_SESSION["delete"]);

echo generating_time($inizio);

?>
