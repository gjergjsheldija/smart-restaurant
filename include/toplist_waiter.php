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

function toplist_delete_firsts (){
	// cut out all the possible error inserted (dishid=0)
	$query = "DELETE FROM `last_orders` WHERE `dishid`='0'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;
	
	$query = "SELECT * FROM `last_orders` ORDER BY `id`";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	$num=mysql_num_rows($res);
	while($num>=CONF_TOPLIST_SAVED_NUMBER){
		$arr=mysql_fetch_array($res);
		$query = "DELETE FROM `last_orders` WHERE `id`=".$arr['id']." LIMIT 1";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;

		$query = "SELECT * FROM `last_orders` ORDER BY `id`";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
		$num=mysql_num_rows($res);
	}
	return 0;
}

function toplist_show(){
	global $tpl;

	$_SESSION['order_added']=0;

	$query = "SELECT * FROM `last_orders`";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	if(!mysql_num_rows($res)) return 1;

	while($arr=mysql_fetch_array($res)){
		$dishid=$arr['dishid'];
		if($dishid==MOD_ID || $dishid==SERVICE_ID) continue;
		if(!isset($toplist[$dishid])) $toplist[$dishid]=0;
		$toplist[$dishid]++;
	}

	if(!is_array($toplist)) return 0;
	
	arsort($toplist);
	reset ($toplist);

	$chk[1]="";
	$chk[2]="";
	$chk[3]="";

	$tmp = '
	<form action="orders.php" method="POST" name="toplist_form">
	<INPUT TYPE="HIDDEN" NAME="command" VALUE="create">
	<INPUT TYPE="HIDDEN" NAME="dishid" VALUE="0">';
	
	if(CONF_TOPLIST_HIDE_QUANTITY) {
		$tmp .= '
		<INPUT TYPE="HIDDEN" NAME="data[quantity]" VALUE="1">';
	}
	if(CONF_TOPLIST_HIDE_PRIORITY) {
		$tmp .= '
		'.ucfirst(phr('PRIORITY')).':
		<input type="radio" '.$chk[1].' name="data[priority]" value=1>1
		<input type="radio" '.$chk[2].' name="data[priority]" value=2>2
		<input type="radio" '.$chk[3].' name="data[priority]" value=3>3';
	}
	
	$tmp .= '
	<table bgcolor="'.COLOR_TABLE_GENERAL.'">
	<tbody>
		<tr align="center">
			<td colspan="4">
				<b>'.ucfirst(phr('TOPLIST')).'('.ucfirst(phr('LASTS')).' '.CONF_TOPLIST_SAVED_NUMBER.')</b>
				&nbsp;&nbsp;
				<a href="orders.php?command=set_show_toplist">
				<img src="'.ROOTDIR.'/images/fileclose.png" border="0" alt="'.ucphr('HIDE_TOPLIST').'">
				</a>
			</td>
		</tr>
';
	$i = 0;
	while ($i < get_conf(__FILE__,__LINE__,"top_list_show_top")) {
		if(list ($key, $value) = each($toplist)){
			$category=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dishes','category',$key);
			$bgcolor=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'categories','htmlcolor',$category);
			$tmp .= '
		<tr bgcolor="'.$bgcolor.'">';
			if(table_is_takeaway($_SESSION['sourceid'])) {
				$tmp .= '
			<input type="hidden" name="data[priority]" value=1>';
			} elseif(!$i && !CONF_TOPLIST_HIDE_PRIORITY) {
				$tmp .= '
			<td rowspan="'.(get_conf(__FILE__,__LINE__,"top_list_show_top")+1).'" bgcolor="'.COLOR_TABLE_GENERAL.'">
			1<input type="radio" '.$chk[1].' name="data[priority]" value=1><br />
			2<input type="radio" '.$chk[2].' name="data[priority]" value=2><br />
			3<input type="radio" '.$chk[3].' name="data[priority]" value=3><br />
			</td>';
			}
			
			if (!$i && !CONF_TOPLIST_HIDE_QUANTITY) {
				$qtydata['nolabel']=1;
				$tmp .= '
			<td rowspan="'.(get_conf(__FILE__,__LINE__,"top_list_show_top")+1).'" bgcolor="'.COLOR_TABLE_GENERAL.'">
			'.quantity_list($qtydata).'
			</td>';
			}
			
			$dishobj = new dish ($key);
			$dishname = $dishobj -> name ($_SESSION['language']);
			
			$tmp .= '
			<td>
				'.$value.'
			</td>';
			$tmp .= '
			<td valign="middle" onclick="order_select(\''.$key.'\',\'toplist_form\');">
			<a href="#" onclick="JavaScript:order_select(\''.$key.'\',\'toplist_form\'); return false;">
			'.$dishname.'
			</a>
			</td>';
			$tmp .= '
		</tr>';
		}
		$i++;
	}
	$tmp .= '
	</table>
	</form>';
	$tpl -> assign ('toplist',$tmp);
	
	return 0;
}

function toplist_insert ($dishid,$quantity){
	if(!$dishid) return 0;
	if($dishid==MOD_ID || $dishid==SERVICE_ID) return 0;
	
	for($i=0; $i<$quantity;$i++) {
		toplist_delete_firsts ();
		$query = "INSERT INTO `last_orders` (`dishid`) VALUES ('".$dishid."')";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
	}
	
	return 0;
}

function toplist_delete ($dishid,$quantity=1){
	if(!$dishid) return 0;

	$query = "DELETE FROM `last_orders` WHERE `dishid`='".$dishid."' LIMIT $quantity";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	return 0;
}

function toplist_update($dishid,$old,$new) {
	$err = 0;
	$quantity_diff = $new - $old;
	if($quantity_diff > 0) $err = toplist_insert ($dishid,abs($quantity_diff));
	if($quantity_diff < 0) $err = toplist_delete ($dishid,abs($quantity_diff));
	return $err;
}
?>