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

class accounting_database extends object {
	var $db_name;
	var $create_db;
	
	function accounting_database($id=0) {
		$this -> db = 'common';
		$this->table=$GLOBALS['table_prefix'].'accounting_dbs';
		$this->id=$id;
		$this -> title = ucphr('ACCOUNTING_DATABASES');
		$this->file=ROOTDIR.'/admin/admin.php';
		
		$this->disable_mass_delete=true;
		
		$this->fields_names=array(	'id'=>ucphr('ID'),
								'name'=>ucphr('NAME'),
								'db'=>ucphr('DATABASE'),
								'print_bill'=>ucphr('PRINT_BILL'));
		$this->fields_width=array(	'name'=>'60%',
								'db'=>'20%',
								'print_bill'=>'20%');
		$this->fields_boolean=array('print_bill');
		$this->allow_single_update = array ('print_bill');
		$this -> fetch_data();
	}
	
	function list_query_all () {
		$table = $this->table;
		
		$query="SELECT
				$table.`id`,
				$table.`name`,
				$table.`db`,
				IF($table.`print_bill`='0','".ucphr('NO')."','".ucphr('YES')."') as `print_bill`
				 FROM `$table`
				";
		
		return $query;
	}
	
	function pre_insert($input_data) {
		// var_dump_table($input_data);
		$this -> db_name = $input_data['db'];
		$this -> create_db = $input_data['create_db'];
		if(isset($input_data['create_db'])) unset($input_data['create_db']);
		
		if($this->create_db) {
			$query="CREATE DATABASE IF NOT EXISTS `".$this->db_name."`";
			mysql_query ($query);
			if($errno=mysql_errno()){
				$errdesc=mysql_error();
				$msg = 'Error creating database '.$this->db_name."\n";
				$msg .= 'Mysql error: '.$errno.' - '.$errdesc."\n";
				$msg .= ' '.$query;
				error_msg(__FILE__,__LINE__,$msg);
				echo nl2br($msg);
				return ERR_MYSQL;
			}
		}
		// var_dump_table($input_data);
		return $input_data;
	}

	function post_insert($input_data) {
		if(!$this->id) return 1;
		//if(!$this->exists()) return 2;

		$query="SELECT * FROM `".$this->table."` WHERE `id`='".$this->id."'";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return mysql_errno();
		
		$arr=mysql_fetch_array($res);

		$this->db_name=$arr['db'];

		if($err=$this->fill_database()) return $err;

		return $input_data;
	}

	function fill_database() {
		require(ROOTDIR."/conf/config.constants.inc.php");
		$file=ROOTDIR.'/'.$location['account']['struct'];

		// Snippet from Nikolai Chuvakhin (nc@iname.com)
		// posted on the usenet:
		// http://groups.google.it/groups?hl=it&lr=&ie=UTF-8&selm=32d7a63c.0303262250.16c657e4%40posting.google.com
		// edited from Fabio De Pascale

		$value=$this->db_name;

		if(empty($value)) return 1;

		$fp = fopen ($file, 'r');						// Open the dump file.
		$query = '';										// Create an empty query sting
		while (! feof ($fp)){								// While the file lasts,
			$line = fgets ($fp, 1024*1024);						// read it line by line.

			if (($line[0] == '#')
				or ($line[0] == '-')						// If the line is a comment
				or (trim ($line) == '')) {					// or an empty line,
				continue;									// forget it.
			} else {										// If neither,
				$query .= $line;							// add it to the query string.
			}

			if (strstr ($line,';')) {						// If this is the end of a query,
				$query = str_replace (';', ' ', $query);	// remove the semicolon,

	//echo $query."<br ><br>\n";

				$res=mysql_db_query ($value,$query);			// pass the query sting to MySQL,
				//$res=mysql_query ($query);			// pass the query sting to MySQL,

	//echo mysql_errno();

				if($errno=mysql_errno()){
					$msg= 'Error filling database '.$value."\n";
					$msg.= 'Mysql error: '.$errno.' - '.mysql_error()."\n";
					$msg.= 'Query: '.$query."\n";
					echo nl2br($msg);
					error_msg(__FILE__,__LINE__,$msg);
					return $errno;
				}
				$query = '';								// and start a new query string.
			}
		}
	return 0;

	}

	function pre_delete($input_data) {
		if(!$this->id) return 1;
		if(!$this->exists()) return 2;

		$query="SELECT * FROM `".$this->table."` WHERE `id`='".$this->id."'";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return mysql_errno();
		
		$arr=mysql_fetch_array($res);
		
		$this->db_name=$arr['db'];

		if($input_data['delete_tables']) {
			if($err=$this->delete_tables()) return $err;
		}

		if($input_data['delete_db']) {
			if($this->db_name==$_SESSION['common_db']) {
				if($err=$this->delete_tables()) return $err;
				return $input_data;
			}

			$query='DROP DATABASE `'.$this->db_name.'`';
	//echo $query;
			mysql_query ($query);
			if($errno=mysql_errno()){
				$errdesc=mysql_error();
				$msg = 'Error deleting database '.$this->db_name."\n";
				$msg .= 'Mysql error: '.$errno.' - '.$errdesc."\n";
				$msg .= ' '.$query;
				error_msg(__FILE__,__LINE__,$msg);
				echo nl2br($msg);
				return 1;
			}
		}

		return $input_data;
 }

	function delete_tables() {
		if(empty($this->db_name)) return 1;

		$query='DROP TABLE `'.$GLOBALS['table_prefix'].'account_account_log`,`'.$GLOBALS['table_prefix'].'account_accounts`,`'.$GLOBALS['table_prefix'].'account_log`,`'.$GLOBALS['table_prefix'].'account_mgmt_addressbook`,`'.$GLOBALS['table_prefix'].'account_mgmt_main`,`'.$GLOBALS['table_prefix'].'account_receipts`,`'.$GLOBALS['table_prefix'].'account_stock_log`';

		mysql_db_query ($this->db_name,$query);
		if($errno=mysql_errno()){
			$errdesc=mysql_error();
			$msg = 'Error deleting database '.$this->db_name."\n";
			$msg .= 'Mysql error: '.$errno.' - '.$errdesc."\n";
			$msg .= ' '.$query;
			error_msg(__FILE__,__LINE__,$msg);
			echo nl2br($msg);
			return 1;
		}

		return 0;
 }

	function check_values($input_data){
		$msg="";
		if($input_data['name']=="") {
			$msg=ucfirst(phr('CHECK_NAME'));
		}

		$this->id=$input_data['id'];

		if($input_data['db']=="") {
			$msg=ucfirst(phr('CHECK_DATABASE_NAME'));
		}

		if(is_numeric($input_data['db'])) {
			$msg=ucfirst(phr('CHECK_DATABASE_NAME'));
		}
		
		if(!empty($msg)){
			echo "<script language=\"javascript\">
				window.alert(\"".$msg."\");
				window.history.go(-1);
			</script>\n";
			echo nl2br($msg);
			return -2;
		}

		if(!$input_data['print_bill']) $input_data['print_bill']=0;
	
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
	<legend>'.ucphr('ACCOUNTING_DATABASE').'</legend>

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
			<td>
			'.$arr['id'].'
			</td>
		</tr>
		<tr>
			<td>
			'.ucphr('NAME_INTERNAL_DATABASE').':
			</td>
			<td>
			<input type="text" name="data[name]" value="'.htmlentities($arr['name']).'">
			</td>
		</tr>
		<tr>
			<td>
			'.ucphr('DATABASE_NAME').':
			</td>
			<td>';

	if($editing) {
		$output .= '
			<select name="data[db]">';
			
			$db_list = mysql_list_dbs();

			while ($arr_dbs = mysql_fetch_array($db_list)) {
				if($arr['db']==$arr_dbs[0]) $selected=' selected';
				else $selected='';

				$output .= '
			<option value="'.$arr_dbs[0].'"'.$selected.'>'.htmlentities($arr_dbs[0]).'</option>';
			}
		$output .= '
			</select>';
	} else {
		$output .= '
			<input type="text" name="data[db]" value="'.htmlentities($arr['db']).'">';
	}
	$output .= '
			</td>
		</tr>
		<tr>
			<td colspan="2">
			<input type="checkbox" name="data[print_bill]" value="1"';
	if(!$editing || ($editing && $arr['print_bill'])) $output .= ' checked';
	$output .= '>'.ucphr('PRINT_BILL_EXPLAIN').'
			</td>
		</tr>';
	if($editing){
		$output .= '
		<tr>
			<td colspan="2">
			<font color="#FF0000">
			'.ucphr('DATABASE_DELETE_WARNING').'
			</font>
			</td>
		</tr>';
	}
	$output .= '
		<tr>
			<td colspan=2 align="center">';
	if(!$editing){
		$output .= '
				<input type="hidden" name="data[create_db]" value="1">
				<input type="submit" value="'.ucphr('INSERT').'">
	</form>
			</td>';
	} else {
		$output .= '
				<input type="submit" value="'.ucphr('UPDATE').'">
	</form>
				</td>
			</tr>
			<tr>
				<td colspan=2>
				<form action="?" name="delete_form_'.get_class($this).'" method="post">
				<input type="hidden" name="class" value="'.get_class($this).'">
				<input type="hidden" name="command" value="delete">
				<input type="hidden" name="delete[]" value="'.$this->id.'">
				<input type="checkbox" name="data[delete_db]" value="1">'.ucphr('DELETE_DATABASE').'<br />
				<input type="checkbox" name="data[delete_tables]" value="1">'.ucphr('DELETE_DATABASE_TABLES').'<br />
				</td>
			</tr>
			<tr>
				<td colspan=2 align="center">
				<input type="submit" value="'.ucphr('DELETE').'">
				</form>
				</td>';
	}
	$output .= '
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