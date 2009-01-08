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
class autocalc extends object {
	function autocalc($id=0) {
		$this -> db = 'common';
		$this->table=$GLOBALS['table_prefix'].'autocalc';
		$this->id=$id;
		$this -> title = ucphr('AUTOCALC_LEVELS');
		$this->file=ROOTDIR.'/admin/admin.php';
		$this -> fields_names = array(	'id'=>ucphr('ID'),
									'name'=>ucphr('NAME'),
									'quantity'=>ucphr('QUANTITY'),
									'price'=>ucphr('PRICE'));
		$this->fields_width=array('name'=>'100%');
		
		$this->disable_mass_delete=true;

		$this -> fetch_data();
	}

	function list_query_all () {
		$table = $this->table;
		
		$query="SELECT
				$table.`id`,
				IF($table.`name`=0,'".ucphr('DEFAULT')."',$table.`name`) as `name`,
				IF($table.`quantity`=0,'".ucphr('DEFAULT')."',$table.`quantity`) as `quantity`,
				$table.`price`
				 FROM `$table`
				";
		
		return $query;
	}
	
	function check_values($input_data){

		$msg="";
		if($input_data['quantity']=="") {
			$msg=ucfirst(phr('CHECK_QUANTITY'));
		}

		$input_data['quantity']=(int) $input_data['quantity'];
		
		$input_data['price'] = eq_to_number ($input_data['price']);
		
		if($input_data['price']=="") {
			$msg=ucfirst(phr('CHECK_PRICE'));
		}

		$input_data['price']=(float) $input_data['price'];
		if($input_data['price']<0) {
			$msg=ucfirst(phr('CHECK_PRICE'));
		}
		
		$input_data['name']=$input_data['quantity'];
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
			
			if($arr['quantity'] == $this->max_quantity()) $allow_delete=true;
			else $allow_delete=false;
		} else {
			$editing=0;
			$arr['id']=next_free_id($_SESSION['common_db'],$this->table);
			$arr['price']=sprintf("%0.2f",0);
			$arr['quantity'] = $this->max_quantity()+1;
		}
		$output .= '
	<div align="center">
	<a href="?class='.get_class($this).'">'.ucphr('BACK_TO_LIST').'.</a>
	<table>
	<tr>
	<td>
	<fieldset>
	<legend>'.ucphr('AUTOCALC').'</legend>

	<form action="?" name="edit_form_'.get_class($this).'" method="post">
	<input type="hidden" name="class" value="'.get_class($this).'">';
		
		if($editing){
			$output .= '
	<input type="hidden" name="command" value="update">
	<input type="hidden" name="data[id]" value="'.$this->id.'">';
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
			<td>'.$arr['id'].'<input type="hidden" name="data[id]" value="'.$arr['id'].'">
			</td>
		</tr>
		<tr>
			<td>'.ucphr('QUANTITY_AUTOCALC_EXPLAIN').':
			</td>
			<td>';
			
		if($arr['quantity']==0) $name=ucphr('DEFAULT');
		else $name=$arr['quantity'];
		$output .= '
			<input type="hidden" name="data[quantity]" value="'.$arr['quantity'].'">'.$name.'
			</td>
		</tr>
		<tr>
			<td>
			'.ucphr('PRICE').':
			</td>
			<td>
			<input type="text" name="data[price]" value="'.$arr['price'].'">
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
				<td>';
			if($allow_delete) {
				$output .= '
				<form action="?" name="delete_form_'.get_class($this).'" method="post">
				<input type="hidden" value="'.get_class($this).'">
				<input type="hidden" name="class" value="'.get_class($this).'">
				<input type="hidden" name="command" value="delete">
				<input type="hidden" name="delete[]" value="'.$this->id.'">
				<input type="submit" value="'.ucfirst(phr('DELETE')).'">
				</form>';
			}
			$output .= '
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
	
	function max_quantity () {
		$query="SELECT * FROM `#prefix#autocalc`";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return 0;
		
		if(mysql_num_rows($res)==0) return -1;
		
		while($arr = mysql_fetch_array($res)) {
			$autocalc [$arr['quantity']] = $arr['price'];
		}
		
		// quantity not found, we look for the highest quantiy available,
		// then add the remaining price (based on the 0 quantity record)
		$keys = array_keys ($autocalc);
		$maxquantity = max($keys);
		
		return $maxquantity;
	}
}

?>