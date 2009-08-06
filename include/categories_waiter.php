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
define('ROOTDIR','..');

function categories_printed ($sourceid,$category) {
	$catprinted=array();
	$catprintedtext=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"catprinted",$sourceid);
	if($catprintedtext!=""){
		$catprinted = explode (" ", $catprintedtext);
	}

	// the priority has already been printed. return true
	if(in_array($category,$catprinted)) return true;

	return 0;
}

function categories_orders_present ($sourceid,$category) {
	$query = "	SELECT id
				FROM orders
				WHERE sourceid ='".$sourceid."'
				AND priority =$category
				AND deleted = 0
				AND printed IS NOT NULL
				AND dishid != ".MOD_ID."
				AND dishid != ".SERVICE_ID."
				AND suspend = 0";
	$res = common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	return mysql_num_rows($res);
}

function categories_list($data=''){
	$output = '
<table bgcolor="'.COLOR_TABLE_GENERAL.'">
';

	$query="SELECT * FROM `categories` WHERE `deleted`='0' ORDER BY id ASC";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return '';

	$i=0;
	while ($arr = mysql_fetch_array ($res)) {
		$i++;
		$catid=$arr['id'];
		$cat = new category ($catid);
		$name=ucfirst($cat->name($_SESSION['language']));

		$backcommand="order_create1";
		$bgcolor=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'categories','htmlcolor',$catid);
		$link = 'orders.php?command=dish_list&amp;data[category]='.$catid;
		if(isset($data['quantity']) && $data['quantity']) $link .= '&amp;data[quantity]='.$data['quantity'];
		if(isset($data['priority']) && $data['priority']) $link .= '&amp;data[priority]='.$data['priority'];

		if($i%2) {
			$output .= '
	<tr>';
		}

		$output .= '
		<td bgcolor="'.$bgcolor.'" onclick="redir(\''.$link.'\');return(false);">
		<a href="'.$link.'">
		<strong>'.$name.'</strong>
		</a>
		</td>';

		if(($i+1)%2) {
			$output .= '
	</tr>';
		}
	}
	$output .= '
	</tbody>
</table>';

	return $output;
}

function categories_list_pos($data=''){

	$query="SELECT * FROM `categories` WHERE `deleted`='0' ORDER BY id ASC";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return '';
	while ($arr = mysql_fetch_array ($res)) {
		$catid=$arr['id'];

		$catimg = $arr['image'];
		if(!$catimg) $catimg = IMAGE_CATEGORY_DEFAULT;

		$backcommand="order_create1";
		$bgcolor=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'categories','htmlcolor',$catid);
		$link = 'orders.php?command=dish_list&amp;data[category]='.$catid;
		if(isset($data['quantity']) && $data['quantity']) $link .= '&amp;data[quantity]='.$data['quantity'];
		if(isset($data['priority']) && $data['priority']) $link .= '&amp;data[priority]='.$data['priority'];
		
		$output .= '
			<a class="CategoryElement" href="#" onclick="loadDish(\''.$link.'\');return(false);">
				<span style="text-indent:64px;display:block;height:100%;background:url(' . $catimg . ') no-repeat 1px 3px;">
					<strong>'.$arr['name'].'</strong>
				</span>
			</a>
		';
	}
	
	return $output;
}

function letters_list_creator (){
	$invisible_show = get_conf(__FILE__,__LINE__,"invisible_show");
	if($invisible_show) {
		$query="SELECT `name` FROM `dishes`
			WHERE dishes.deleted='0'";
	} else {
		$query="SELECT `name` FROM `dishes`
			WHERE `visible`='1'
			AND dishes.deleted='0'";
	}

	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;
	$dishes_letters = array();
	while ($arr = mysql_fetch_array ($res)) {
		$name = trim($arr['name']);
		if ($name == null || strlen($name) == 0)
		$name = trim($arr['name']); //if no name in the fixed lang, use the main name
		array_push($dishes_letters, substr($name, 0, 1));
	}
	return $dishes_letters;
}

function letters_list ($data=''){
	$output = '
<table bgcolor="'.COLOR_TABLE_GENERAL.'">
';

	$output .= '
	<tr>';

	// letters
	// total 32-95
	$offset = 32;

	$col=-1;
	$color = 0;

	$dishes_letters = letters_list_creator ();

	for ($i=17;$i<=(92-$offset);$i++) {

		$letter = chr($i + $offset);
		if($letter == "'") $letter = "\'";

		if($letter =='%' ) continue;

		$bgcolor=COLOR_TABLE_GENERAL;
		//RTG: if there is some dishes begginnig with this letter
		if(in_array($letter, $dishes_letters, false)) {
			$letter= htmlentities($letter);
			$link = 'orders.php?command=dish_list&amp;data[letter]='.$letter;
				
			if(isset($data['quantity']) && $data['quantity']) $link .= '&amp;data[quantity]='.$data['quantity'];
			if(isset($data['priority']) && $data['priority']) $link .= '&amp;data[priority]='.$data['priority'];
				
			$bgcolor = color ($color++);
			$output .= '
			<td bgcolor="'.$bgcolor.'" onclick="redir(\''.$link.'\');return(false);">
			<a href="'.$link.'">
			<strong>'.$letter.'</strong>
			</a>
			</td>';
			$col++;
		} else {
			continue;
			$output .= '
			<td bgcolor="'.$bgcolor.'">
			&nbsp;
			</td>';
		}
			
		if((($col +1) % 6) == 0) {
			$color++;
			$output .= '
		</tr>
		<tr>';
		}
	}

	$output .= '
	</tr>';

	$output .= '
	</tbody>
</table>';

	return $output;
}

?>