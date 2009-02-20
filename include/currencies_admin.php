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

class currencies extends object {
	function currencies($id=0) {
		$this->db = 'common';
		$this->table = 'currencies';
		$this->id = $id;
		$this->title = ucphr('EXCHANGE');
		$this->file=ROOTDIR.'/admin/admin.php';
		$this->fields_names = array('id'=>ucphr('ID'),
									'name'=>ucphr('NAME'),
									'rate'=>ucphr('VALUE'),
									'active'=>ucphr('ACTIVE'));
		$this->fields_width=array('name'=>'95%',
								  'rate'=>'5%');
		$this->fetch_data();
	}

	function list_search ($search) {
		$query = '';
		
		$table = $this->table;
		
		$query="SELECT
				$table.`id`,
				$table.`name`,
				$table.`rate`,
				$table.`active`
				FROM `$table`
				WHERE $table.`name` LIKE '%$search%'
				";

		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return 0;
		while($arr=mysql_fetch_array($res)) {
			$ret[]=$arr['rate'];
		}
		return $ret;			
	}	
	
	function list_search_active () {
		$query = '';
		
		$table = $this->table;
		
		$query="SELECT
				$table.`id`,
				$table.`name`,
				$table.`rate`
				FROM `$table`
				WHERE $table.`active` = 1 AND $table.`name` LIKE '%$search%'
				";

		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return 0;
		$i = 0;
		while($arr=mysql_fetch_array($res)) {
			$ret[$i]['rate']=$arr['rate'];
			$ret[$i]['name']=$arr['name'];
			$i++;
		}
		return $ret;			
	}
	
	function list_query_all () {
		$table = $this->table;
		
		$query="SELECT
				$table.`id`,
				$table.`name`,
				$table.`rate`,
				$table.`active`
				 FROM `$table`
				";
		
		return $query;

	}
	
	function list_rates() {
		$ret=array();
		$query="SELECT * FROM `".$this->table."`";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return 0;
		
		while($arr=mysql_fetch_array($res)) {
			$ret[]=$arr['id'];
		}
		return $ret;
	}
	
	function check_values($input_data){
		$msg="";
		if($input_data['name']=="") {
			$msg=ucfirst(phr('CHECK_NAME'));
		}

		$input_data['rate'] = eq_to_number ($input_data['rate']);

		if($input_data['rate']!=0 && empty($input_data['rate'])) {
			$msg=ucfirst(phr('CHECK_KAMBIO'));
		}
		$input_data['rate']=str_replace (",", ".", $input_data['rate']);

		if($input_data['rate']<0) {
			$msg=ucfirst(phr('CHECK_KAMBIO'));
		}
		if(!is_numeric($input_data['rate'])) {
			$msg=ucfirst(phr('CHECK_KAMBIO'));
		}
		
		if($input_data['active'] == true)
			$input_data['active'] = 1;
		else
			$input_data['active'] = 0;
			
		if($msg){
			echo "<script language=\"javascript\">
				window.alert(\"".$msg."\");
				window.history.go(-1);
			</script>\n";
			return -2;
		}

		return $input_data;
	}

	function form(){
		
		if($this->id) {
			$editing=1;
			$query="SELECT * FROM `".$this->table."` WHERE `id`='".$this->id."'";
			$res=common_query($query,__FILE__,__LINE__);
			if(!$res) return mysql_errno();
			
			$arr=mysql_fetch_array($res);
		} else {
			$editing=0;
			$arr['id']=next_free_id($_SESSION['common_db'],$this->table);
		}
		
	$output .= '
	<div align="center">
	<a href="?class='.get_class($this).'">'.ucphr('BACK_TO_LIST').'.</a>
	<table>
	<tr>
	<td>
	<fieldset>
	<legend>'.ucphr('VALUTA').'</legend>

	<form action="?" name="edit_form_'.get_class($this).'" method="post">
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
			<td>'.$arr['id'].'
			</td>
		</tr>
		<tr>
			<td>
			'.ucphr('NAME').':
			</td>
			<td>
			<input type="text" name="data[name]" value="'.$arr['name'].'">
			</td>
		</tr>
		<tr>
			<td>
			'.ucphr('VALUTA').':
			</td>
			<td>
			<input type="text" name="data[rate]" value="'.$arr['rate'].'">
			</td>
		</tr>
		<tr>
			<td>
			'.ucphr('ACTIVE').':
			</td>
			<td>'; 
			if( $arr['active'] == 1 ) 
				$output .=  '<input type="checkbox" name="data[active]" checked ">';
			else 
				$output .=  '<input type="checkbox" name="data[active]" ">';
			
			$output .= '</td> 
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
				<form action="?" name="delete_form_'.get_class($this).'" method="post">
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

}

?>