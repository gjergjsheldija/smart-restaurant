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

class category extends object {
	var $temp_lang;

	function category($id=0) {
		$this -> db = 'common';
		$this->table='categories';
		$this->id=$id;
		$this->flag_delete = true;
		$this->fields_names=array(	'id'=>ucphr('ID'),
								'name'=>ucphr('NAME'),
								'vat_rate'=>ucphr('VAT_RATE'),
								'priority'=>ucphr('PRIORITY'),
								'image'=>ucphr('IMAGE'));
		$this->fields_width=array(	'name'=>'80%',
								'vat_rate'=>'20%');
		$this -> title = ucphr('CATEGORIES');
		$this->file=ROOTDIR.'/admin/admin.php';
		$this -> fetch_data();
	}


	

	
	function remove_connected_dishes () {
		$query="SELECT id
		FROM `dishes`
		WHERE category='".$this->id."'";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
		while($arr=mysql_fetch_array($res)) {
			$dish = new dish($arr['id']);
			$dish -> set ('category','0');
		}
		return 0;
	}
	
	function remove_connected_ingreds () {
		$query="SELECT id
		FROM `ingreds`
		WHERE category='".$this->id."'";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
		while($arr=mysql_fetch_array($res)) {
			$dish = new ingredient($arr['id']);
			$dish -> set ('category','0');
		}
		return 0;
	}
	
	function pre_insert($input_data) {
		for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
			if(stristr($key,'categories_')) {
				$this->temp_lang[$key]=$value;
				unset ($input_data[$key]);
			}
		}

		return $input_data;
	}

	function post_insert($input_data) {
		global $tpl;
		if(is_array($this->temp_lang)) {
			for (reset ($this->temp_lang); list ($key, $value) = each ($this->temp_lang); ) {
				$input_data[$key]=$this->temp_lang[$key];
			}
		}

		$input_data['id']=$this->id;
		
		if($err=$this->translations_set($input_data)) return $err;
		
		$menu = new menu();
		$tmp = $menu -> main ();
		$tpl -> assign("menu", $tmp);

		return $input_data;
	}

	function pre_delete($input_data) {
		if(!$this->id) return 1;
		if(!$this->exists()) return 2;

		if($lang_del=$this->translations_delete($this->id)) return $lang_del;
		
		if($err = $this -> remove_connected_dishes ()) return $err;
		if($err = $this -> remove_connected_ingreds ()) return $err;

		return $input_data;
	}
	
	
	function check_values($input_data){
		$msg="";
		
		if(!isset($input_data['htmlcolor'])) $input_data['htmlcolor']=$_REQUEST['htmlcolor'];
		
		$name_found=false;
		for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
			if(stristr($key,'categories_') && trim($value)!='') {
				$name_found=$key;
			}
		}
		if($input_data['name']=="" && !$name_found) {
			$msg=ucfirst(phr('CHECK_NAME'));
		} elseif ($input_data['name']=="") {
			$input_data['name']=$input_data[$name_found];
		}
		
		
		if($input_data['htmlcolor']=="") {
			$msg=ucfirst(phr('CHECK_COLOR'));
		} elseif($input_data['htmlcolor'][0]!="#") {
			$msg=ucfirst(phr('CHECK_COLOR_BEGIN'));
		}
	
		if(strlen($input_data['htmlcolor'])!=7) $msg=ucfirst(phr('CHECK_COLOR'));
	
		if($input_data['name']=="") {
			$msg=ucfirst(phr('CHECK_NAME'));
		}

		if(!empty($msg)){
			echo "<script language=\"javascript\">
				window.alert(\"".$msg."\");
				window.history.go(-1);
			</script>\n";
			echo nl2br($msg);
			return -2;
		}

		return $input_data;
	}
	

	function show_page_list ($class) {
		$query="SELECT * FROM `".$this->table."` WHERE `deleted`='0'";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return '';
	
		if(!mysql_num_rows($res)) $output .= ucfirst(phr('ERROR_NONE_FOUND_CATEGORY')).".<br>\n";
	
		$cat=new category;
	
		$output .= '
	<table>
		<tbody>
			<tr>
				<td><a href="?class='.$class.'">'.ucphr('CATEGORIES_SHOW_ALL').'</a></td>
			</tr>';
			
		while($arr=mysql_fetch_array($res)){
	
			$cat->id=$arr['id'];
			$output .= '
			<tr>
				<td><a href="?data[category]='.$arr['id'].'&amp;class='.$class.'">'.ucfirst($cat->name($_SESSION['language'])).'</a></td>
			</tr>';
		}
		unset($cat);
		$output .= '
		</tbody>
	</table>';
		return $output;
	}
}

function categories_html_color_row ($bit) {
	$size= 10;

	// $output = '<tr>'."\n";
	for ($i=200;$i<261;$i=$i+6){
		if($i>255) $i=255;
		
		$more=$i+150;
		if($more>255) $more=255;
		$less=$i-150;
		if($less<0) $less=0;
		
		switch($bit) {
			case 1:
				$color='#'.sprintf("%02x",0).sprintf("%02x",$i).sprintf("%02x",0);
				break;
			case 2:
				$color='#'.sprintf("%02x",200).sprintf("%02x",$more).sprintf("%02x",$i);
				break;
			case 3:
				$color='#'.sprintf("%02x",$i).sprintf("%02x",$i).sprintf("%02x",0);
				break;
			case 4:
				$color='#'.sprintf("%02x",0).sprintf("%02x",0).sprintf("%02x",$i);
				break;
			case 5:
				$color='#'.sprintf("%02x",$more).sprintf("%02x",$i).sprintf("%02x",$less);
				break;
			case 6:
				$color='#'.sprintf("%02x",0).sprintf("%02x",$i).sprintf("%02x",$i);
				break;
			case 7:
				$color='#'.sprintf("%02x",$more).sprintf("%02x",$more).sprintf("%02x",$i);
				break;
			case 8:
				$color='#'.sprintf("%02x",200).sprintf("%02x",200).sprintf("%02x",$i);
				break;
			case 9:
				$color='#'.sprintf("%02x",$more).sprintf("%02x",$i).sprintf("%02x",$i);
				break;
			case 10:
				$color='#'.sprintf("%02x",$less).sprintf("%02x",$i).sprintf("%02x",$more);
				break;
			case 11:
				$color='#'.sprintf("%02x",$i).sprintf("%02x",0).sprintf("%02x",$i);
				break;
			case 12:
				$color='#'.sprintf("%02x",$i).sprintf("%02x",$more).sprintf("%02x",$more);
				break;
			case 13:
				$color='#'.sprintf("%02x",$i).sprintf("%02x",0).sprintf("%02x",0);
				break;
			default:
				$color='#'.sprintf("%02x",$i).sprintf("%02x",$i).sprintf("%02x",$i);
				break;
		}
		$link = 'color_select(\''.$color.'\');';
		$output .= '<td class="color_table_cell" onclick="'.$link.'" bgcolor="'.$color.'">&nbsp;</td>'."\n";
	}
	return $output;
}

function categories_html_color_table () {
	$output = '<table>'."\n";
	for ($i=1;$i<15;$i++) {
		$output .= '<tr>'."\n";
		$output .= categories_html_color_row($i);
		$i++;
		$output .= categories_html_color_row($i);
		$output .= '<tr>'."\n";
	}
	$output .= '</table>'."\n";
	return $output;
}

function admin_categories_names_array(){
	$query="SELECT * FROM `categories`
	WHERE `deleted`='0'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return mysql_errno();

	if(!mysql_num_rows($res)){
		echo ucfirst(phr('ERROR_NONE_FOUND')).".<br>\n";
		return 2;
	}

	while($arr=mysql_fetch_array($res)){
		$catnames[$arr['id']]=ucfirst($arr['name']);
	}
	return $catnames;
}

?>