<?php
/**
* My Handy Restaurant
*
* http://www.myhandyrestaurant.org
*
* My Handy Restaurant is a restaurant complete management tool.
* Visit {@link http://www.myhandyrestaurant.org} for more info.
* Copyright (C) 2003-2004 Fabio De Pascale
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
* @copyright	Copyright 2003-2005, Fabio De Pascale
*/

function navbar_trash($form='',$show_abort='',$start_data) {
	$msg = '
	<table>
		<tr>
			<td width=35>
				<a href="tables.php"><img src="'.IMAGE_MENU.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0 width="32" height="32"></a>
			</td>
			<td width=35>
				<a href="orders.php?command=list"><img src="'.IMAGE_SOURCE.'" alt="'.ucfirst(phr('BACK_TO_TABLE')).'" border=0 width="32" height="32"></a>
			</td>
			<td width=35>
			</td>
			<td width=35>
				<a href="orders.php?command=ask_delete&amp;data[id]='.$start_data['id'].'">
				<img src="'.IMAGE_TRASH.'" alt="'.ucfirst(phr('REMOVE')).'" border=0>
				</a>
			</td>
			<td width=35>
			';
	if(!empty($show_abort))
		$msg .= '<a href="'.$show_abort.'"><img src="'.IMAGE_NO.'" alt="'.ucfirst(phr('NO')).'" border=0 width="32" height="32"></a>';

	$msg .= '
			</td>
			<td width=35>
			';
	if(!empty($form))
		$msg .= '<a href="#" onclick="JavaScript:document.'.$form.'.submit(); return false"><img src="'.IMAGE_OK.'" alt="'.ucfirst(phr('BACK_TO_TABLE')).'" border=0 width="64" height="64"></a>';
		
	$msg .= '
			</td>
		</tr>
	</table>
	';
	return $msg;
}

function navbar_trash_pos($form='',$show_abort='',$start_data) {
	$msg = '
	<table>
		<tr>
			<td width="35" onclick="generalDishModifier(\'orders.php?command=ask_delete&data[id]='.$start_data['id'].'\');">
					<img src="'.IMAGE_TRASH.'" alt="'.ucfirst(phr('REMOVE')).'" border=0>
			</td>
			<td width="35">
				<a href="#" onclick="modifyDishOrder(\''.$form.'\'); return false">
					<img src="'.IMAGE_OK.'" alt="'.ucfirst(phr('BACK_TO_TABLE')).'" border=0 width="64" height="64">
				</a>
			</td>
		</tr>
	</table>
	';
	return $msg;
}

function navbar_empty($show_abort='') {
	$msg = '
	<table>
		<tr>
			<td width=35>
				<a href="tables.php"><img src="'.IMAGE_MENU.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0 width="32" height="32"></a>
			</td>
			<td width=35>
				<a href="orders.php?command=list"><img src="'.IMAGE_SOURCE.'" alt="'.ucfirst(phr('BACK_TO_TABLE')).'" border=0 width="32" height="32"></a>
			</td>
			<td width=35>
			</td>
			<td width=35>
			</td>
			<td width=35>
			';
	if(!empty($show_abort))
		$msg .= '<strong><a href="'.$show_abort.'"><img src="'.IMAGE_NO.'" alt="'.ucfirst(phr('NO')).'" border=0 width="32" height="32"></a></strong>';

	$msg .= '
			</td>
			<td width=35>
			</td>
		</tr>
	</table>
	';
	return $msg;
}

function navbar_empty_pos($show_abort='') {
	$msg = '
	<table>
		<tr>
			<td width=35>
				<a href="tables.php"><img src="'.IMAGE_MENU.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0 width="64" height="64"></a>
			</td>
			<td width=35>
				<a href="orders.php?command=list"><img src="'.IMAGE_SOURCE.'" alt="'.ucfirst(phr('BACK_TO_TABLE')).'" border=0 width="64" height="64"></a>
			</td>
			<td width=35>
			</td>
			<td width=35>
			</td>
			<td width=35>
			';
	if(!empty($show_abort))
		$msg .= '<a href="'.$show_abort.'"><img src="'.IMAGE_NO.'" alt="'.ucfirst(phr('NO')).'" border=0 width="64" height="64"></a>';

	$msg .= '
			</td>
			<td width=35>
			</td>
		</tr>
	</table>
	';
	return $msg;
}

function navbar_with_printer($show_abort='') {
	$msg = '
	<table>
		<tr>
			<td width=35>
				<a href="tables.php"><img src="'.IMAGE_MENU.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0 width="32" height="32"></a>
			</td>
			<td width=35>
				<a href="orders.php?command=list"><img src="'.IMAGE_SOURCE.'" alt="'.ucfirst(phr('BACK_TO_TABLE')).'" border=0 width="32" height="32"></a>
			</td>
			<td width=35><a href="orders.php?command=print_orders"><img src="'.IMAGE_PRINT.'" alt="'.ucfirst(phr('PRINT')).'" border=0 width="32" height="32"></a>
			</td>
			<td width=35>
			</td>
			<td width=35>
			';
	if(!empty($show_abort))
		$msg .= '<a href="'.$show_abort.'"><img src="'.IMAGE_NO.'" alt="'.ucfirst(phr('NO')).'" border=0 width="32" height="32"></a>';

	$msg .= '
			</td>
			<td width=35>
			</td>
		</tr>
	</table>
	';
	return $msg;
}

function navbar_with_printer_pos($show_abort='') {
	$msg = '
	<table>
		<tr>
			<td width=35>
				<a href="tables.php"><img src="'.IMAGE_MENU.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0 width="64" height="64"></a>
			</td>
			<td width=35>
				<a href="orders.php?command=list"><img src="'.IMAGE_SOURCE.'" alt="'.ucfirst(phr('BACK_TO_TABLE')).'" border=0 width="64" height="64"></a>
			</td>
			<td width=35><a href="orders.php?command=print_orders"><img src="'.IMAGE_PRINT.'" alt="'.ucfirst(phr('PRINT')).'" border=0 width="64" height="64"></a>
			</td>
			<td width=35>
			</td>
			<td width=35>
			';
	if(!empty($show_abort))
		$msg .= '<a href="'.$show_abort.'"><img src="'.IMAGE_NO.'" alt="'.ucfirst(phr('NO')).'" border=0 width="64" height="64"></a>';

	$msg .= '
			</td>
			<td width=35>
			</td>
		</tr>
	</table>
	';
	return $msg;
}

function navbar_tables_only() {
	$msg = '
	<table>
		<tr>
			<td width=35>
				<a href="tables.php"><img src="'.IMAGE_MENU.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0 width="32" height="32"></a>
			</td>
			<td width=35>
			</td>
			<td width=35>
			</td>
			<td width=35>
			</td>
			<td width=35>
			</td>
			<td width=35>
			</td>
		</tr>
	</table>
	';
	return $msg;
}

function navbar_tables_only_pos() {
	$msg = '
	<table>
		<tr>
			<td width=35>
				<a href="tables.php"><img src="'.IMAGE_MENU.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0 width="64" height="64"></a>
			</td>
			<td width=35>
			</td>
			<td width=35>
			</td>
			<td width=35>
			</td>
			<td width=35>
			</td>
			<td width=35>
			</td>
		</tr>
	</table>
	';
	return $msg;
}

function navbar_lock_retry_pos($show_abort='') {
	$msg = '
	<table>
		<tr>
			<td width=35>
				<a href="tables.php"><img src="'.IMAGE_MENU.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0 width="64" height="64"></a>
			</td>
			<td width=35>
			</td>
			<td width=35>
			</td>
			<td width=35>
			</td>
			<td width=35>
			';
	if(!empty($show_abort))
		$msg .= '<a href="'.$show_abort.'"><img src="'.IMAGE_NO.'" alt="'.ucfirst(phr('NO')).'" border=0 width="64" height="64"></a>';

	$msg .= '
			</td>
			<td width=35>
				<a href="orders.php"><img src="'.IMAGE_OK.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0 width="64" height="64"></a>
			</td>
		</tr>
	</table>
	';
	return $msg;
}

function navbar_lock_retry($show_abort='') {
	$msg = '
	<table>
		<tr>
			<td width=35>
				<strong><a href="tables.php"><img src="'.IMAGE_MENU.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0 width="64" height="64"></a></strong>&nbsp;&nbsp;&nbsp;&nbsp;
			</td>
			<td width=35>
			</td>
			<td width=35>
			</td>
			<td width=35>
			</td>
			<td width=35>
			';
	if(!empty($show_abort))
		$msg .= '<strong><a href="'.$show_abort.'"><img src="'.IMAGE_NO.'" alt="'.ucfirst(phr('NO')).'" border=0 width="32" height="32"></a></strong>';

	$msg .= '
			</td>
			<td width=35>
				<strong><a href="tables.php"><img src="'.IMAGE_MENU.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0 width="64" height="64"></a></strong>&nbsp;&nbsp;&nbsp;&nbsp;
			</td>
		</tr>
	</table>
	';
	return $msg;
}

function navbar_menu_pos($show_abort='') {
	$msg = '
	<table>
		<tr>
			<td width=35>
				<a href="tables.php"><img src="'.IMAGE_MENU.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0 width="64" height="64"></a>
			</td>
			<td width=35>
			</td>
			<td width=35>
			</td>
			<td width=35>
			</td>
			<td width=35>
			';
	if(!empty($show_abort))
		$msg .= '<a href="'.$show_abort.'"><img src="'.IMAGE_NO.'" alt="'.ucfirst(phr('NO')).'" border=0 width="64" height="64"></a>';

	$msg .= '
			</td>
			<td width=35>
				<a href="tables.php"><img src="'.IMAGE_OK.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0 width="64" height="64"></a>
			</td>
		</tr>
	</table>
	';
	return $msg;
}

function navbar_menu($show_abort='') {
	$msg = '
	<table>
		<tr>
			<td width=35>
				<a href="tables.php"><img src="'.IMAGE_MENU.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0 width="32" height="32"></a>
			</td>
			<td width=35>
			</td>
			<td width=35>
			</td>
			<td width=35>
			</td>
			<td width=35>
			';
	if(!empty($show_abort))
		$msg .= '<a href="'.$show_abort.'"><img src="'.IMAGE_NO.'" alt="'.ucfirst(phr('NO')).'" border=0 width="32" height="32"></a>';

	$msg .= '
			</td>
			<td width=35>
				<a href="tables.php"><img src="'.IMAGE_OK.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0 width="32" height="32"></a>
			</td>
		</tr>
	</table>
	';
	return $msg;
}

function navbar_form($form,$show_abort='') {
	$msg = '
	<table>
		<tr>
			<td width=35>
				<strong><a href="tables.php"><img src="'.IMAGE_MENU.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0 width="64" height="64"></a></strong>&nbsp;&nbsp;&nbsp;&nbsp;
			</td>
			<td width=35>
				<strong><a href="orders.php?command=list"><img src="'.IMAGE_SOURCE.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0 width="64" height="64"></a></strong>&nbsp;&nbsp;&nbsp;&nbsp;
			</td>
			<td width=35>
			</td>
			<td width=35>
			</td>
			<td width=35>
			';
	if(!empty($show_abort))
		$msg .= '<strong><a href="'.$show_abort.'"><img src="'.IMAGE_NO.'" alt="'.ucfirst(phr('NO')).'" border=0 width="64" height="64"></a></strong>';

	$msg .= '
			</td>
			<td width=35>
				<strong><a href="#" onclick="JavaScript:document.'.$form.'.submit(); return false;"><img src="'.IMAGE_OK.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0 width="64" height="64"></a></strong>
			</td>
		</tr>
	</table>
	';
	return $msg;
}

function navbar_form_pos($form,$show_abort='') {
	$msg .= '<table><tr>
			<td>
				<a href="#" onclick="modifyDishOrder(\''.$form.'\'); return false">
					<img src="'.IMAGE_OK.'" alt="'.ucfirst(phr('BACK_TO_TABLE')).'" border=0 width="64" height="64">
				</a>
			</td>
		</tr>
	</table>
	';
	return $msg;
}

function command_bar_table_horizontal(){
	$output = '
	<table>
		<tr>
			<td width=35>
				<strong><a href="tables.php"><img src="'.IMAGE_MENU.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0 width="10" height="10"></a></strong>&nbsp;&nbsp;&nbsp;&nbsp;
			</td>
			<td width=35>
			</td>
			<td width=35>
			</td>
			<td width=35>
				<strong><a href="orders.php?command=close_confirm"><img src="'.IMAGE_CLOSE.'" alt="'.ucfirst(phr('CLOSE_TABLE')).'" border=0 width="32" height="32"></a></strong>&nbsp;&nbsp;&nbsp;&nbsp;
			</td>
			<td width=35>
			</td>
			<td width=35>
				<strong><a href="orders.php?command=printing_choose"><img src="'.IMAGE_PRINT.'" alt="'.ucfirst(phr('PRINT')).'" border=0 width="32" height="32"></a></strong>&nbsp;&nbsp;&nbsp;&nbsp;
			</td>
		</tr>
	</table>
	';
	return $output;
}

function command_bar_table_horizontal_pos(){
	$output = '
	<table>
		<tr>
			<td width=35>
				<a href="tables.php"><img src="'.IMAGE_MENU.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0 width="64" height="64"></a>
			</td>
			<td width=35>
			</td>
			<td width=35>
			</td>
			<td width=35>
				<a href="orders.php?command=close_confirm"><img src="'.IMAGE_CLOSE.'" alt="'.ucfirst(phr('CLOSE_TABLE')).'" border=0 width="64" height="64"></a>
			</td>
			<td width=35>
			</td>
			<td width=35>
				<a href="orders.php?command=printing_choose"><img src="'.IMAGE_PRINT.'" alt="'.ucfirst(phr('PRINT')).'" border=0 width="64" height="64"></a>
			</td>
		</tr>
	</table>
	';
	return $output;
}

function command_bar_table_vertical(){
	$output = '
	<table>
		<tr>
			<td>
				<strong><a href="tables.php"><img src="'.IMAGE_MENU.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0 width="64" height="64"></a></strong>&nbsp;&nbsp;&nbsp;&nbsp;
			</td>
			<td>
				<strong><a href="orders.php?command=printing_choose"><img src="'.IMAGE_PRINT.'" alt="'.ucfirst(phr('PRINT')).'" border=0 width="64" height="64"></a></strong>&nbsp;&nbsp;&nbsp;&nbsp;
			</td>
			<td width=35>
				<strong><a href="orders.php?command=close_confirm"><img src="'.IMAGE_CLOSE.'" alt="'.ucfirst(phr('CLOSE_TABLE')).'" border=0 width="64" height="64"></a></strong>&nbsp;&nbsp;&nbsp;&nbsp;
			</td>
		</tr>
	</table>
	';
	return $output;
}

function command_bar_table_vertical_pos(){
	$output = '<span class="rounded">
		<a href="tables.php"><img src="'.IMAGE_MENU.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0 width="64" height="64"></a>
		<a href="orders.php?command=printing_choose"><img src="'.IMAGE_PRINT.'" alt="'.ucfirst(phr('PRINT')).'" border=0 width="64" height="64"></a>
		<a href="orders.php?command=close_confirm"><img src="'.IMAGE_CLOSE.'" alt="'.ucfirst(phr('CLOSE_TABLE')).'" border=0 width="64" height="64"></a>
	</span>';
	return $output;
}
?>