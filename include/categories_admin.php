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

	function list_search ($search) {
		$query = '';
		
		$table = $this->table;
		$lang_table = $table."_".$_SESSION['language'];

		$query="SELECT
				$table.`id`,
				$table.`name` as `name`,
				RPAD('".ucphr('CATEGORIES')."',30,' ') as `table`,
				".TABLE_CATEGORIES." as `table_id`
				FROM `$table`
				WHERE $table.`deleted`='0'
				AND $table.`name` LIKE '%$search%'";
		
		return $query;
	}
	
	function list_query_all () {
		$table = "categories";
		$lang_table = "categories_".$_SESSION['language'];
		$table_vat = "vat_rates";
		
		$query="SELECT
				$table.`id`,
				$table.`name` as `name`,
				$table_vat.`name` as `vat_rate`,
				$table.`priority`,
				$table.`image`
				FROM `$table`
				LEFT JOIN `$table_vat` ON $table_vat.`id`=$table.`vat_rate`
				WHERE $table.`deleted`='0'
				";
		
		return $query;
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
	
	function pre_update($input_data) {
		if(!$this->id) return 1;
		if(!$this->exists()) return 2;

		if($err=$this->translations_set($input_data)) return $err;

		for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
			if(stristr($key,'categories_')) {
				unset ($input_data[$key]);
			}
		}
		if($_FILES['data']['name']['image'] != '' ) {
			$input_data['image'] = substr(IMAGE_UPLOAD,2) . $_FILES['data']['name']['image'];
			move_uploaded_file($_FILES['data']['tmp_name']['image'], IMAGE_UPLOAD . $_FILES['data']['name']['image']);
		}
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
	
	function form($input_data=''){
		if($_REQUEST['data']['show_names']) $input_data['show_names']=true;
		
		if($this->id) {
			$editing=1;
			$query="SELECT * FROM `".$this->table."` WHERE `id`='".$this->id."'";
			$res=common_query($query,__FILE__,__LINE__);
			if(!$res) return mysql_errno();
			
			$arr=mysql_fetch_array($res);
		} else {
			$editing=0;
			$arr['id']=next_free_id($_SESSION['common_db'],$this->table);
			$arr['htmlcolor']='#FFFFFF';
		}
	
		$output .= '
	<div align="center">
	<a href="?class='.get_class($this).'">'.ucphr('BACK_TO_LIST').'.</a>
	<table>
	<tr>
	<td>
	<fieldset>
	<legend>'.ucphr('CATEGORY').'</legend>
	
	<form action="?" name="edit_form_'.get_class($this).'" method="post" enctype="multipart/form-data">
	<input type="hidden" name="class" value="'.get_class($this).'">
	<input type="hidden" name="data[id]" value="'.$arr['id'].'">';
		if($editing){
			$output .= '
	<input type="hidden" name="command" value="update">';
		} else {
			$output .= '
	<input type="hidden" name="command" value="insert">';
		}
		$output .= '
	<table>
		<tr>
			<td>
			'.ucphr('ID').':
			</td>
			<td>
			'.$arr['id'].'
			</td>
		</tr>';
		
	if(!$editing || $input_data['show_names']) {
		$output .= '
		<tr>
			<td>
			'.ucphr('NAME').':
			</td>
			<td>
			<input type="text" name="data[name]" value="'.htmlentities($arr['name']).'"> (<a href="'.$this->file.'?class='.get_class($this).'&amp;command=edit&amp;data[id]='.$this->id.'&amp;data[show_names]=0">'.ucphr('HIDE_NAMES').'</a>)
			</td>
		</tr>';

	
		$res_lang=mysql_list_tables($_SESSION['common_db']);
		while($arr_lang=mysql_fetch_array($res_lang)) {
			if($lang_now=stristr($arr_lang[0],$this->table.'_')) {
				$lang_now= substr($lang_now,-2);
	
				if($editing) {
					$ingred = new category ($this->id);
					$lang_name = $ingred -> name ($lang_now);
				}
		
				$output .= '
		<tr>
			<td>'.ucphr('NAME').' ('.$lang_now.')</td>
			<td><input type="text" name="data[categories_'.$lang_now.']" value="'.$lang_name.'"></td>
		</tr>';
			}
		}
	} else {
		$output .= '
		<tr>';

		$output .= '
			<input type="hidden" name="data[name]" value="'.htmlentities($arr['name']).'">';
		$res_lang=mysql_list_tables($_SESSION['common_db']);
		while($arr_lang=mysql_fetch_array($res_lang)) {
			if($lang_now=stristr($arr_lang[0],$this->table.'_')) {
				$lang_now= substr($lang_now,-2);
				$cat = new category ($this->id);
				$lang_name = $cat -> name ($lang_now);
				$output .= '
			<input type="hidden" name="data[categories_'.$lang_now.']" value="'.$lang_name.'">';
			}
		}
				
		$output .= '
			<td>'.ucphr('NAME').' ('.$_SESSION['language'].')</td>
			<td>'.$this->name($_SESSION['language']).' (<a href="'.$this->file.'?class='.get_class($this).'&amp;command=edit&amp;data[id]='.$this->id.'&amp;data[show_names]=1">'.ucphr('SHOW_NAMES').'</a>)</td>
		</tr>';
		
	}
		
		$output .= '
		<tr>
			<td colspan=2>
			'.ucphr('CATEGORY_COLOR').':
			<table id="tabcolor"><tbody><tr id="trcolor">
			<td width="10px" height="10px" id="tdcolor"  bgcolor="'.$arr['htmlcolor'].'">&nbsp;</td>
			<td><input type="text" name="htmlcolor" maxlength="7" value="'.htmlentities($arr['htmlcolor']).'"></td>
			</tr>
		</tbody>
	</table>';
			
		$output .= categories_html_color_table();
			
		$output .= '
			</td>
		</tr>
		<tr>
			<td>
				Select Image for this category :
			</td>
			<td>
				<input type="file" name="data[image]" value="'.$arr['image'].'"/>
			</td>
		</tr>
		<tr>
			<td>
			'.ucphr('PRIORITY_DEFAULT').':
			</td>
			<td>
			<select name="data[priority]">';
			
		for($i=0;$i<4;$i++) {
			$description = $i;
			if(!$i) $description = ucfirst(phr('NONE'));

			if($arr['priority']==$i) $selected=' selected';
			else $selected='';
			
			$output .= '
			<option value="'.$i.'"'.$selected.'>'.$description.'</option>';
		}
		
		$output .= '
			</select>
			</td>
		</tr>
		<tr>
			<td>
			'.ucphr('VAT_RATE').':
			</td>
			<td>
			<select name="data[vat_rate]">';
			
		$rate = new vat_rate();
		$rates_list=$rate->list_rates();
		for (reset ($rates_list); list ($key, $value) = each ($rates_list); ) {
			$rate -> id= $value;
			$description=$rate -> name();
			if($arr['vat_rate']==$value) $selected=' selected';
			else $selected='';

			$output .= '
			<option value="'.$value.'"'.$selected.'>'.$description.'</option>';
		}
		unset($rate);
		$output .= '
			</select>
			</td>
		</tr>
		<tr>
			<td colspan=2 align="center">
			<table>
			<tr>
				<td>';
				
		if(!$editing){
			$output .= '
				<input type="submit" value="'.ucphr('INSERT').'">
	</form>
				</td>';
				
		} else {
			$output .= '
				<td>
				<input type="submit" value="'.ucphr('UPDATE').'">
	</form>
				</td>
				<td>
				<form action="?" name="delete_form_'.get_class($this).'" method="get">
				<input type="hidden" name="class" value="'.get_class($this).'">
				<input type="hidden" name="command" value="delete">
				<input type="hidden" name="delete[]" value="'.$this->id.'">
				<input type="submit" value="'.ucphr('DELETE').'">
				</form>
				</td>';
		}
		$output .= '
			</tr>
			</table>
			</td>
		</tr>
	</table>
	
	
	</fieldset>
	</td>
	</tr>
	</table>
	</div>';
	
	return $output;	
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