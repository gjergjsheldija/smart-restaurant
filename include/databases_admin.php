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


				$res=mysql_db_query ($value,$query);			// pass the query sting to MySQL,

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

}

?>