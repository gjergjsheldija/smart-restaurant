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

class db_manager {
	var $table;
	var $field;
	var $depth;
	
	var $mhr_tables_only;
	
	var $link;
	var $database;
	var $error;
	var $host;

	function db_manager ($host, $user, $password, $link=NULL) {
		if($link){
			$this->link = $link;
		}else{
			$this->link = mysql_pconnect($host, $user, $password);
			if($errno=mysql_errno()) {
				$msg="Error in ".__FUNCTION__." - ";
				$msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
				$msg.='connection to db'."\n";
				echo nl2br($msg)."\n";
				return $errno;
			}
		}
		$this->host=$host;
		
		// default values
		$this -> db_destination = '';
		$this -> mhr_tables_only = false;
		return 0;
	}
	
	function select_db ($database) {
		if(mysql_select_db($database)) {
			$this -> database = $database;
			return 0;
		} elseif($errno=mysql_errno()) {
			$msg="Error in ".__FUNCTION__." - ";
			$msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
			$msg.='connection to db'."\n";
			echo nl2br($msg)."\n";
			return $errno;
		}
	}
	
	function upgrade_available () {
/*		$upgrades=list_upgrades(ROOTDIR.'/upgrade');
		
		// sort in reverse to find upgrade to be done asap
		rsort($upgrades);
		for (reset ($upgrades); list ($key, $value) = each ($upgrades); ) {
			$filename=$value;
			if($this->upgrade_upgrade_to_do ($filename)) return true;
		}
		*/
		return false;
	}
	
	function upgrade_upgrade_to_do ($filename) {
		$upgrade_str = $filename;
		$upgrade_str=eregi_replace("^mhr_",'',$upgrade_str);
		$upgrade_str=eregi_replace("\.sql$",'',$upgrade_str);

		list($upgrade_id,$from_version,$expected_version)=explode('_',$upgrade_str,3);
		
		$query = "SELECT * FROM `#prefix#system` WHERE `name`='upgrade_last_key'";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
		
		$arr=mysql_fetch_array($res);
		$last_done=(int) $arr['value'];
		$to_do=(int) $upgrade_id;
		
		if($to_do>$last_done) return true;
		
		return false;
	}
	
	function upgrade_get_last_ok () {
		$query = "SELECT * FROM `#prefix#system` WHERE `name`='upgrade_last_key'";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return 0;
		
		$arr=mysql_fetch_array($res);
		$last_done=(int) $arr['value'];
		
		return $last_done;
	}
	
	function upgrade_set_last_ok ($filename) {
		// name example:
		// mhr_0003_0.8.4-beta1_0.8.4-beta2.sql
		
		$upgrade_str = $filename;
		$upgrade_str=eregi_replace("^mhr_",'',$upgrade_str);
		$upgrade_str=eregi_replace("\.sql$",'',$upgrade_str);

		list($upgrade_id,$from_version,$expected_version)=explode('_',$upgrade_str,3);
		
		$query = "SELECT * FROM `#prefix#system` WHERE `name`='version'";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
		
		$arr=mysql_fetch_array($res);
		if($arr['value']!=$expected_version) return ERR_UNEXPECTED_VERSION_NUMBER;

		$query = "UPDATE `#prefix#system` SET `value`='".$upgrade_id."' WHERE `name`='upgrade_last_key' LIMIT 1";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
		
		return 0;
	}
	
	function upgrade_from_file($file,$verbose=0,$simulate_only=0){
		$fp = fopen ($file, 'r');						// Open the dump file.
		while (! feof ($fp)){								// While the file lasts,
			$arr[] = fgets ($fp, 1024*1024);						// read it line by line.
		}
		
		$_SESSION['restore_sql']['verbose']=$verbose;
		$_SESSION['restore_sql']['simulate_only']=$simulate_only;
		$_SESSION['restore_sql']['db_destination']=$this -> db_destination;
		$_SESSION['restore_sql']['array']=$arr;
		for (reset ($arr); list ($key, $line) = each ($arr); ) {
			$bytes_total=$bytes_total+strlen($line);
		}
		$_SESSION['restore_sql']['total_bytes']=$bytes_total;
		$_SESSION['restore_sql']['done_bytes']=0;
		
		$err= $this->upgrade_from_array($arr,$verbose,$simulate_only);
		return $err;
	}
	
	function upgrade_from_string($string,$verbose=0,$simulate_only=0){
		$string=str_replace("\'","'",$string);
		$arr=explode("\n",$string);
		
		$_SESSION['restore_sql']['verbose']=$verbose;
		$_SESSION['restore_sql']['simulate_only']=$simulate_only;
		$_SESSION['restore_sql']['db_destination']=$this -> db_destination;
		$_SESSION['restore_sql']['array']=$arr;
		for (reset ($arr); list ($key, $line) = each ($arr); ) {
			$bytes_total=$bytes_total+strlen($line);
		}
		$_SESSION['restore_sql']['total_bytes']=$bytes_total;
		$_SESSION['restore_sql']['done_bytes']=0;
		
		$err= $this->upgrade_from_array($arr,$verbose,$simulate_only);
		return $err;
	}
	
	function upgrade_resume(){
		if(!isset($_SESSION['restore_sql']['array']) || empty($_SESSION['restore_sql']['array'])) return ERR_SQL_UPGRADE_VARIABLE_NOT_AVAILABLE;
		
		$verbose = $_SESSION['restore_sql']['verbose'];
		$simulate_only = $_SESSION['restore_sql']['simulate_only'];
		$this -> db_destination = $_SESSION['restore_sql']['db_destination'];
		
		$arr = $_SESSION['restore_sql']['array'];
		$err = $this->upgrade_from_array($arr,$verbose,$simulate_only);
		return $err;
	}
	
	function upgrade_from_array($arr,$verbose,$simulate_only){
		// Snippet from Nikolai Chuvakhin (nc@iname.com)
		// posted on the usenet:
		// http://groups.google.it/groups?hl=it&lr=&ie=UTF-8&selm=32d7a63c.0303262250.16c657e4%40posting.google.com
		// edited from Fabio De Pascale
		
		global $output;
		
		$start=microtime();
		
		$db_type='common';
		$query = '';						// Create an empty query sting
		for (reset ($arr); list ($key, $line) = each ($arr); ) {
			$trimmedline=trim($line);
			
			if(eregi("#[^:]*database_type[^:]*:",$line)) {
				$line = eregi_replace ("#[^:]*database_type[^:]*:", "", $line);
				//$line = fgets ($fp, 1024*1024);						// read it line by line.
				$db_type=trim($line);
				$db_type=strtolower($db_type);
//echo 'found db: '.$db_type."<br />\n";
				continue;
			} elseif (($trimmedline[0] == '#')
				or ($trimmedline[0] == '-')						// If the line is a comment
				or ($trimmedline == '')) {					// or an empty line,
				continue;									// forget it.
			} else {										// If neither,
				$query .= $line;							// add it to the query string.
			}

			if (eregi (';$',$trimmedline)) {						// If this is the end of a query,
				$query = eregi_replace (";$", "", $query); // remove the semicolon,
				//$query = str_replace (';', '', $query);	// remove the semicolon,

				$tmp_arr=explode(' ',trim($query));
				$tmp=trim($query);
				
				if(eregi("^INSERT",$query)) {
					$data_val = str_replace ("\\r\\n", "\r\n", $data_val);
				}

				$query_type='';
				if(eregi("^INSERT",$tmp)) {
					$query_type='INSERT';
					$active_table=$tmp_arr[2];
					if(!$first_insert_printed[$active_table]) {
						if($verbose && !empty($this -> db_destination)) $output .=  "\nInserting data in db ".$this -> db_destination." table ".$active_table;
						elseif($verbose) $output .=  "\nInserting data in db type $db_type table ".$active_table;
						$first_insert_printed[$active_table]=true;
					 }
					 // #[^:]*database_type[^:]*:
				} elseif(eregi("^ALTER TABLE[^ADD(\s)INDEX].*ADD INDEX",$tmp)) {
					$query_type='ALTER TABLE INDEX';
					$active_table=$tmp_arr[2];
					$active_field=$tmp_arr[5];
					if($verbose) $output .=  "\nAdding index $active_field in db type $db_type table ".$active_table;
				} elseif(eregi("^ALTER TABLE[^ADD].*ADD",$tmp)) {
					$query_type='ALTER TABLE ADD';
					$active_table=$tmp_arr[2];
					$active_field=$tmp_arr[4];
					if($verbose) $output .=  "\nAdding field $active_field in db type $db_type table ".$active_table;
				} elseif(eregi("^ALTER TABLE[^DROP].*DROP",$tmp)) {
					$query_type='ALTER TABLE ADD';
					$active_table=$tmp_arr[2];
					$active_field=$tmp_arr[4];
					if($verbose) $output .=  "\nDeleting field $active_field in db type $db_type table ".$active_table;
				} elseif(eregi("^ALTER TABLE[^CHANGE].*CHANGE",$tmp)) {
					$query_type='ALTER TABLE CHANGE';
					$active_table=$tmp_arr[2];
					$active_field=$tmp_arr[4];
					if($verbose) $output .=  "\nModifying field $active_field in db type $db_type table ".$active_table;
				} elseif(eregi("^ALTER TABLE[^RENAME].*RENAME",$tmp)) {
					$query_type='ALTER TABLE RENAME';
					$active_table=$tmp_arr[2];
					$active_field=$tmp_arr[4];
					if($verbose) $output .=  "\nRenaming table $active_table in db type $db_type to table ".$active_field;
				} elseif(eregi("^CREATE TABLE",$tmp)) {
					$query_type='CREATE TABLE';
					$active_table=$tmp_arr[2];
					if($verbose) $output .=  "\nAdding table $active_table in db type $db_type";
				} elseif(eregi("^DROP TABLE IF EXISTS",$tmp)) {
					$query_type='DROP TABLE';
					$active_table=$tmp_arr[4];
					if($verbose) $output .=  "\nDeleting table $active_table in db type $db_type";
				} elseif(eregi("^DROP TABLE",$tmp)) {
					$query_type='DROP TABLE';
					$active_table=$tmp_arr[2];
					if($verbose) $output .=  "\nDeleting table $active_table in db type $db_type";
				} else {
					if($verbose==1) $output .=  "\nQuery in db type $db_type: ".trim($query);
				}
				if($verbose==2) $output .=  "\nQuery in db type $db_type: ".trim($query);
				
				//takes away the last semicolon
				$query = trim($query);
				if(substr($query,-1)==';') $query = substr($query,0,(strlen($query)-1));
				
				
				if (!empty($this -> db_destination)) {
//echo "sent to ".$_SESSION['common_db'].":<br/ >\n$query<br />\n";
					$db=$this -> db_destination;
					if(!$simulate_only) {
						if(!empty($db))
							$res = mysql_db_query($db,$query);
						else $res = mysql_query($query);
					}
					$errno=mysql_errno();
					$tmp=$this->upgrade_error_handler($errno,$db,$query);
					if(is_int($tmp) && $tmp!=0) {
						$msg="Error in ".__FUNCTION__." - ";
						$msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
						$msg.='query: '.$query."\n";
						echo nl2br($msg)."\n";
						error_msg(__FILE__,__LINE__,$msg);
						return $errno;
					} elseif(is_int($tmp) && $tmp==0) {
						if($verbose && $query_type) $output.=".";
					} elseif(!is_int($tmp)) {
						if($verbose) $output.=$tmp."\n";
					}
					
					unset($tmp);
				} elseif($db_type=='account') {
/*					$table = $GLOBALS['table_prefix'].'accounting_dbs';
					$query_local = "SELECT * FROM `$table`";*/
					$res_local = mysql_db_query($_SESSION['common_db'],$query_local);
					while($arr_db = mysql_fetch_array($res_local)) {
//echo "sent to ".$arr_db['db'].":<br/ >\n$query<br />\n";
						
						// db not found, skip it
						if(!mysql_list_tables($arr['db'])) continue;
						
						$db=$arr_db['db'];

						if(!$simulate_only)
							$res = mysql_db_query($db,$query);
						$errno=mysql_errno();
						$tmp=$this->upgrade_error_handler($errno,$db,$query);
						if(is_int($tmp) && $tmp!=0) {
							$msg="Error in ".__FUNCTION__." - ";
							$msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
							$msg.='query: '.$query."\n";
							echo nl2br($msg)."\n";
							error_msg(__FILE__,__LINE__,$msg);
							return $errno;
						} elseif(is_int($tmp) && $tmp==0) {
							if($verbose && $query_type) $output.=".";
						} elseif(!is_int($tmp)) {
							if($verbose) $output.=$tmp."\n";
						}
						
						unset($tmp);
					}
				} elseif ($db_type=='common') {
//echo "sent to ".$_SESSION['common_db'].":<br/ >\n$query<br />\n";
					$db=$_SESSION['common_db'];
					if(!$simulate_only) {
						if(!empty($db))
							$res = mysql_db_query($db,$query);
						else $res = mysql_query($query);
					}
					$errno=mysql_errno();
					$tmp=$this->upgrade_error_handler($errno,$db,$query);
					if(is_int($tmp) && $tmp!=0) {
						$msg="Error in ".__FUNCTION__." - ";
						$msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
						$msg.='query: '.$query."\n";
						echo nl2br($msg)."\n";
						error_msg(__FILE__,__LINE__,$msg);
						return $errno;
					} elseif(is_int($tmp) && $tmp==0) {
						if($verbose && $query_type) $output.=".";
					} elseif(!is_int($tmp)) {
						if($verbose) $output.=$tmp."\n";
					}
					
					unset($tmp);
				}
				
				$last_query_done=$query;
				$query = '';								// and start a new query string.
			}
			
			$_SESSION['restore_sql']['done_bytes']=$_SESSION['restore_sql']['done_bytes']+strlen($_SESSION['restore_sql']['array'][$key]);
			unset($_SESSION['restore_sql']['array'][$key]);	// unsets the line just completed
			if(CONF_SQL_RESUME_ENABLED && elapsed_time($start,microtime())>1) {
				$percent = $_SESSION['restore_sql']['done_bytes']/$_SESSION['restore_sql']['total_bytes'];
				$percent = round($percent*100,2);
				$output .= "
Done ".$_SESSION['restore_sql']['done_bytes'].'/'.$_SESSION['restore_sql']['total_bytes']." ($percent %)\n
\n";
				return ERR_SQL_CONTINUING;
			}
		}
		//if($verbose) echo $output;
		unset($_SESSION['restore_sql']);
		
		return 0;
	}
	
	function upgrade_error_handler($error,$db,$query) {
		/*

		*/
		$output=''."\n";
		switch($error) {
			case 1050:
				$output .= "\n".'Table already exists - db '.$db.', continuing.'."\n";
				break;
			case 1051:
				$output .= "\n".'Table doesn\'t exist and cannot be deleted - db '.$db.', continuing.'."\n";
				break;
			case 1054:
				$output .= "\n".'Field doesn\'t exists - db '.$db.', continuing.'."\n";
				break;
			case 1060:
				$output .= "\n".'Field already exists (duplicated column name) - db '.$db.', continuing.'."\n";
				break;
			case 1062:
				$output .= "\n".'Row already exists (duplicated key) - db '.$db.', continuing.'."\n";
				break;
			case 1091:
				$output .= "\n".'Field doesn\'t exist and cannot be deleted - db '.$db.', continuing.'."\n";
				break;
			case 1146:
				$output .= "\n".'The table doesn\'t exist - db '.$db.', continuing.'."\n";
				break;
			default:
				return $error;
		}
		$output.='[query: '.trim($query).']';
		
		return $output;
	}
	
	function arr_to_xml ($arr) {
		$out='';
		$GLOBALS['depth']=2;
		$out=
'<?xml version="1.0"?>
<MyHandyRestaurant>
	<type> database_compare </type>
	<data>
';
		$out .= $this ->arr_to_xml_childs($arr);
		$out.=
'	</data>
</MyHandyRestaurant>';
		$GLOBALS['depth']=0;
		return $out;
	}

	function arr_to_xml_childs($arr) {
		for (reset ($arr); list ($key, $value) = each ($arr); ) {
			if (is_array($value)) {
				for($i=0; $i<$GLOBALS['depth']; $i++) {
					$out.="\t";
				}
				$GLOBALS['depth']++;
				$out .= "<$key>\n";
				$out .= $this -> arr_to_xml_childs ($arr[$key]);
				$GLOBALS['depth']--;
				for($i=0; $i<$GLOBALS['depth']; $i++) {
					$out.="\t";
				}
				$out .= "</$key>\n";
			} else {
				for($i=0; $i<$GLOBALS['depth']; $i++) {
					$out.="\t";
				}
				$out .= "<$key> $value </$key>\n";
			}
		}
		return $out;
	}
	
	function xml_compare($base_db,$target_db) {
		$out=array();
	
		$parse = new xml_parser;

		$tree1 = $parse -> xml_to_tree($base_db,0);
		$base_arr=$this -> tree_to_array ($tree1);

		$tree2 = $parse -> xml_to_tree($target_db,0);
		$target_arr=$this -> tree_to_array ($tree2);
		
		// var_dump_table($target_arr);
		for (reset ($base_arr['tables']); list ($bkey, $bvalue) = each ($base_arr['tables']); ) {
			$this -> table = $bkey;
			if(array_key_exists($bkey, $target_arr['tables'])) {
				$tmp = $this -> compare_table($base_arr['tables'][$bkey],$target_arr['tables'][$bkey]);
				$out=array_merge_recursive($out,$tmp);
			} else {
				$out['add']['tables'][$this -> table]['add']='1';
			}
		}
		for (reset ($target_arr['tables']); list ($tkey, $tvalue) = each ($target_arr['tables']); ) {
			$this -> table = $tkey;
			if(array_key_exists($tkey, $base_arr['tables'])) {
			} else {
				$out['remove']['tables'][$this -> table]['remove']='1';
			}
		}
		return $out;
	}
	
	function compare_table ($base_arr, $target_arr) {
		for (reset ($base_arr['fields']); list ($bkey, $bvalue) = each ($base_arr['fields']); ) {
			$this -> field = $bkey;
			if(array_key_exists($bkey, $target_arr['fields'])) {
				$tmp = $this -> compare_field ($base_arr['fields'][$bkey],$target_arr['fields'][$bkey]);
				//echo '$out['found']['tables']['.$this -> table.']['fields']['.$bkey.']['add']=1'."\n";
				$out=array_merge_recursive ($out,$tmp);
			} else {
				$out['add']['tables'][$this -> table]['fields'][$bkey]['add']='1';
				//echo '$out['add']['tables']['.$this -> table.']['fields']['.$bkey.']['add']=1'."\n";
			}
		}
		for (reset ($target_arr['fields']); list ($tkey, $tvalue) = each ($target_arr['fields']); ) {
			$this -> field = $tkey;
			if(array_key_exists($tkey, $base_arr['fields'])) {
			} else {
				$out['remove']['tables'][$this -> table]['fields'][$tkey]['remove']='1';
				//echo '$out['add']['tables']['.$this -> table.']['fields']['.$bkey.']['add']=1'."\n";
			}
		}
		return $out;
	}

	function compare_field ($base_arr, $target_arr) {
		for (reset ($base_arr); list ($bkey, $bvalue) = each ($base_arr); ) {
			if($bkey!='key' && $base_arr[$bkey]!=$target_arr[$bkey]) {
				$out['alter']['tables'][$this -> table]['fields'][$this -> field]['alter'] = $target_arr['string'].' -> '.$base_arr['string'].' ($bkey:'.$bkey.': '.$base_arr[$bkey].'!='.$target_arr[$bkey].')';
			}
			if($bkey=='key' && $base_arr[$bkey]!=$target_arr[$bkey]) {
				// find a way to add keys here
				$out['alter']['tables'][$this -> table]['fields'][$this -> field]['index'] = 'Create index on this field';
			}
		}
		return $out;
	}

	function is_mhr_table ($table) {
		if(!$this->mhr_tables_only) return true;
		
		$prefix_len = strlen($GLOBALS['table_prefix']);
		$tbl_prefix = substr($table,0,$prefix_len);
		if($tbl_prefix==$GLOBALS['table_prefix']) return true;
		
		return false;
	}
	
	function dbstruct_to_xml($db) {
		$this->database=$db;
	
		$query="SHOW TABLES FROM `".$db."`";
		$res_tables=mysql_db_query($db,$query,$this->link);
		if($errno=mysql_errno()) {
			$msg="Error in ".__FUNCTION__." - ";
			$msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
			$msg.='query: '.$query."\n";
			echo nl2br($msg)."\n";
			error_msg(__FILE__,__LINE__,$msg);
			return 0;
		}
		
		$numtables = mysql_num_rows ($res_tables);
		if($numtables==0) return 0;
		
		$out =
'<?xml version="1.0"?>
<ResPlus>
	<type> database </type>
	<data>
';
		$out .= "\t\t<database>\n";
		$out .= "\t\t\t<name> $db </name>\n";
		if($db==$_SESSION['common_db']) $out .= "\t\t\t<type> common </type>\n";
		else {
			$table=$GLOBALS['table_prefix'].'accounting_dbs';
			$query="SELECT * FROM `$table`";
			$res_accounting_dbs = mysql_db_query ($_SESSION['common_db'],$query,$this->link);
			if($errno=mysql_errno()) {
				$msg="Error in ".__FUNCTION__." - ";
				$msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
				$msg.='query: '.$query."\n";
				echo nl2br($msg)."\n";
				error_msg(__FILE__,__LINE__,$msg);
				return 0;
			}
			while($arr_accounting_dbs=mysql_fetch_array($res_accounting_dbs)) {
				if($db==$arr_accounting_dbs['db']) $out .= "\t\t\t<type> accounting </type>\n";
				break;
			}
		}
	//	$out .= "\t\t\t<name> $db </name>\n";
		
		while($arr_tables=mysql_fetch_array($res_tables)) {
			$table=$arr_tables[0];
			if(!$this -> is_mhr_table($table)) continue;
			
			$out .= "\t\t\t<table name=\"$table\">\n";
			$out .= "\t\t\t\t<name> $table </name>\n";
			
			$query="SHOW CREATE TABLE `".$table."`";
			$res_tbl_create=mysql_db_query($db,$query,$this->link);
			if($errno=mysql_errno()) {
				$msg="Error in ".__FUNCTION__." - ";
				$msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
				$msg.='query: '.$query."\n";
				echo nl2br($msg)."\n";
				error_msg(__FILE__,__LINE__,$msg);
				return 0;
			}
			$arr_tbl_create=mysql_fetch_array($res_tbl_create);
			$out .= "\t\t\t\t<string> $arr_tbl_create[1] </string>\n";
			
			$query="SHOW COLUMNS FROM `".$table."`";
			$res_fields=mysql_db_query($db,$query,$this->link);
			if($errno=mysql_errno()) {
				$msg="Error in ".__FUNCTION__." - ";
				$msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
				$msg.='query: '.$query."\n";
				echo nl2br($msg)."\n";
				error_msg(__FILE__,__LINE__,$msg);
				return 0;
			}
			
			while($arr_fields=mysql_fetch_array($res_fields)) {
				$out .= "\t\t\t\t<field name=\"".$arr_fields['Field']."\">\n";
				$out .= "\t\t\t\t\t<name> ".$arr_fields['Field']." </name>\n";
				$out .= "\t\t\t\t\t<type> ".$arr_fields['Type']." </type>\n";
				$out .= "\t\t\t\t\t<null> ".$arr_fields['Null']." </null>\n";
				$out .= "\t\t\t\t\t<key> ".$arr_fields['Key']." </key>\n";
				$out .= "\t\t\t\t\t<default> ".$arr_fields['Default']." </default>\n";
				$out .= "\t\t\t\t\t<extra> ".$arr_fields['Extra']." </extra>\n";
				$out .= "\t\t\t\t</field>\n";
			}
			
			
			$out .= "\t\t\t</table>\n";
		}
		$out .= "\t\t</database>\n";
		$out.=
'	</data>
</MyHandyRestaurant>';
		
		return $out;
	}

	// $tables_struct_only should be subarray of $tables_show
	function db_to_sql($db,$drop_tables=0,$mysqldump_format=false,$tables_show='',$lang_export='',$tables_struct_only='') {
		$this->database=$db;
		
		if(is_array($tables_show)) {
			for (reset ($tables_show); list ($key, $value) = each ($tables_show); ) {
				$tables_show[$key]=$GLOBALS['table_prefix'].$tables_show[$key];
				
				// if the $lang_export var is set, only exports the *language* version of the selected tables
				if(!empty($lang_export)) $tables_show[$key]=$tables_show[$key].'_'.$lang_export;
			}
		}
		
		if(is_array($tables_struct_only)) {
			for (reset ($tables_struct_only); list ($key, $value) = each ($tables_struct_only); ) {
				$tables_struct_only[$key]=$GLOBALS['table_prefix'].$tables_struct_only[$key];
				
				// if the $lang_export var is set, only exports the *language* version of the selected tables
				if(!empty($lang_export)) $tables_struct_only[$key]=$tables_struct_only[$key].'_'.$lang_export;
			}
		}
		
		$query="SHOW TABLES FROM `".$this->database."`";
		$res_tables=mysql_db_query($this->database,$query,$this->link);
		if($errno=mysql_errno()) {
			$msg="Error in ".__FUNCTION__." - ";
			$msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
			$msg.='query: '.$query."\n";
			echo nl2br($msg)."\n";
			error_msg(__FILE__,__LINE__,$msg);
			return 0;
		}
		
		$numtables = mysql_num_rows ($res_tables);
		if($numtables==0) return 0;
		
		$out = "#\n";
		$out .="# My Handy Restaurant database dump\n";
		$out .= "#\n";
		$out .= "\n";
		$out .= "#\n";
		$out .="# Generating time: ".date("j F Y",time())." at ".date("H:i",time())."\n";
		$out .= "#\n";
		$out .= "# Database name: $db\n";
		if($db==$_SESSION['common_db']) $out .= "# Database type: main\n";
		else {
			$table=$GLOBALS['table_prefix'].'accounting_dbs';
			$query="SELECT * FROM `$table`";
			$res_accounting_dbs = mysql_db_query ($_SESSION['common_db'],$query,$this->link);
			if($errno=mysql_errno()) {
				$msg="Error in ".__FUNCTION__." - ";
				$msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
				$msg.='query: '.$query."\n";
				echo nl2br($msg)."\n";
				error_msg(__FILE__,__LINE__,$msg);
				return 0;
			}
			while($arr_accounting_dbs=mysql_fetch_array($res_accounting_dbs)) {
				if($db==$arr_accounting_dbs['db']) $out .= "# Database type: accounting\n";
				break;
			}
		}
		
		$out .= "#\n";
		while($arr_tables=mysql_fetch_array($res_tables)) {
			$table=$arr_tables[0];
			$subtable= substr ($table, 0, strlen($table)-3);
			
			if(is_array($tables_show) && (in_array($table,$tables_show) || in_array($subtable,$tables_show))) {
				if(is_array($tables_struct_only) && (in_array($table,$tables_struct_only) || in_array($subtable,$tables_struct_only))) {
					$tmp = $this -> table_structure($table,0);
				} else {
					$tmp = $this -> table_structure($table,$drop_tables);
				}
				if($tmp!=0) return 0;
				$out .= $tmp;
				
				// echo '<br>'.$table.': a'.is_array($tables_struct_only).'b'.in_array($table,$tables_struct_only).'c'.in_array($subtable,$tables_struct_only)."<br>";
				if(!is_array($tables_struct_only) || (!in_array($table,$tables_struct_only) && !in_array($subtable,$tables_struct_only))) {
					$tmp = $this -> table_dump($table,$mysqldump_format);
					if($tmp!=0) return 0;
					$out .= $tmp;
				}
				
			} elseif(!is_array($tables_show)) {
				$tmp = $this -> table_structure($table,$drop_tables);
				if($tmp!=0) return 0;
				$out .= $tmp;
				
				$tmp = $this -> table_dump($table,$mysqldump_format);
				if($tmp!=0) return 0;
				$out .= $tmp;
			}

		}

		$out .= "\n# ------------------------------------------------------\n";
		$out .= "\n";
		$out .= "#\n";
		$out .= "# End of dump for database $db\n";
		$out .= "#\n";
		
		return $out;
	}
	
	function table_structure ($table,$drop_tables) {
		$out = '';
		if(!$this -> is_mhr_table($table)) return '';
	
		/*
		$out .= "\n# ------------------------------------------------------\n";
		$out .= "\n#\n# Table: $table\n#\n";
		*/
		$out .= "\n";
		$out .= "#\n";
		$out .= "# Structure dump for table $table \n";
		$out .= "#\n";
		$out .= "\n";
		
		$query="SHOW CREATE TABLE `".$table."`";
		$res_tbl_create=mysql_db_query($this->database,$query,$this->link);
		if($errno=mysql_errno()) {
			$msg="Error in ".__FUNCTION__." - ";
			$msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
			$msg.='query: '.$query."\n";
			echo nl2br($msg)."\n";
			error_msg(__FILE__,__LINE__,$msg);
			return 0;
		}
		$arr_tbl_create=mysql_fetch_array($res_tbl_create);
		
		if($drop_tables) {
			$out .="DROP TABLE IF EXISTS `$table`;\n";
		}
		$out .= "$arr_tbl_create[1];\n";
		
		return $out;
	}
	
	function table_dump($table,$mysqldump_format) {
		$out = '';
		if(!$this -> is_mhr_table($table)) return '';
		
		$query="SHOW COLUMNS FROM `".$table."`";
		$res_fields=mysql_db_query($this->database,$query,$this->link);
		if($errno=mysql_errno()) {
			$msg="Error in ".__FUNCTION__." - ";
			$msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
			$msg.='query: '.$query."\n";
			echo nl2br($msg)."\n";
			error_msg(__FILE__,__LINE__,$msg);
			return 0;
		}
		
		while($arr_fields=mysql_fetch_array($res_fields)) {
			$fields[] = $arr_fields['Field'];
		}
		
		$query="SELECT * FROM `".$table."`";
		$res_data=mysql_db_query($this->database,$query,$this->link);
		if($errno=mysql_errno()) {
			$msg="Error in ".__FUNCTION__." - ";
			$msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
			$msg.='query: '.$query."\n";
			echo nl2br($msg)."\n";
			error_msg(__FILE__,__LINE__,$msg);
			return 0;
		}

		if(mysql_num_rows($res_data)) {
			$out .= "\n";
			$out .= "#\n";
			$out .= "# Data dump for table $table \n";
			$out .= "#\n";
			$out .= "\n";
		
			if ($mysqldump_format) {
				$out .= "INSERT INTO `".$table."` (";
				for (reset ($fields); list ($key, $value) = each ($fields); ) {
					$out .= "`".$fields[$key]."`, ";
				}
				// strips the last comma that has been put
				$out = substr ($out, 0, strlen($out)-2);
				$out.=") VALUES ";
			
				while($arr_data=mysql_fetch_row($res_data)) {
					$out .= "(";
					for (reset ($arr_data); list ($key, $value) = each ($arr_data); ) {
						$data_val=$arr_data[$key];
						$data_val = str_replace ("\'", "'", $data_val);
						$data_val = str_replace ("'", "\'", $data_val);
						
						$data_val = str_replace ("\r\n", '\r\n', $data_val);
						$data_val = str_replace ("\n", '\r\n', $data_val);
						$data_val = str_replace ("\r", '\r\n', $data_val);
						
						//$data_val=mysql_real_escape_string($data_val);
						//$data_val=addslashes($data_val);
						$out .= "'".$data_val."', ";
					}
					// strips the last comma that has been put
					$out = substr ($out, 0, strlen($out)-2);
					$out .= "),";
				}
				// strips the last comma that has been put
				$out = substr ($out, 0, strlen($out)-1);
				$out .= ";\n";
			} else {
				while($arr_data=mysql_fetch_row($res_data)) {
					$out .= "INSERT INTO `".$table."` VALUES ";
					$out .= "(";
					for (reset ($arr_data); list ($key, $value) = each ($arr_data); ) {
						$data_val=$arr_data[$key];
						$data_val = str_replace ("\'", "'", $data_val);
						$data_val = str_replace ("'", "\'", $data_val);
						
						$data_val = str_replace ("\r\n", '\r\n', $data_val);
						$data_val = str_replace ("\n", '\r\n', $data_val);
						$data_val = str_replace ("\r", '\r\n', $data_val);
						
						//$data_val=mysql_real_escape_string($data_val);
						//$data_val=addslashes($data_val);
						$out .= "'".$data_val."', ";
					}
					// strips the last comma that has been put
					$out = substr ($out, 0, strlen($out)-2);
					$out .= ");\n";
				}
			}
		} else {
			$out .= "\n";
			$out .= "#\n";
			$out .= "# No Data found to be dumped in table $table (table is empty)\n";
			$out .= "#\n";
		
		}
		unset($fields);
		return $out;
	}
	
	function tree_to_array ($orig) {
		if(!is_array($orig)) return 1;
		
		$work=$orig['MyHandyRestaurant'][0]['data'][0]['database'][0];

		for (reset ($work); list ($key, $value) = each ($work); ) {
			if($key=='name') {
				$out['name']=$work['name'][0]['VALUE'];
			}
			if($key=='type') {
				$out['type']=$work['type'][0]['VALUE'];
			}
			if($key=='table') {
				for (reset ($work['table']); list ($tkey, $tvalue) = each ($work['table']); ) {
					$tmp = $this -> parse_table($work['table'][$tkey]);
					$out['tables']=array_merge($out['tables'],$tmp);
				}
			}
		}
	
		return $out;
	}
	
	function parse_table ($arr) {
		for (reset ($arr); list ($tkey, $tvalue) = each ($arr); ) {
			$name=trim($arr['name'][0]['VALUE']);
			
			$out[$name]['string']=trim($arr['string'][0]['VALUE']);
			
			if($tkey=='field') {
			for (reset ($arr['field']); list ($fkey, $fvalue) = each ($arr['field']); ) {
				$tmp = $this -> parse_field($arr['field'][$fkey]);
				$out[$name]['fields']=array_merge($out[$name]['fields'],$tmp);
			}
			}	
		}
		return $out;
	}
	
	function parse_field ($arr) {
		$name = trim($arr['name'][0]['VALUE']);
		$type = trim($arr['type'][0]['VALUE']);
		$null = trim($arr['null'][0]['VALUE']);
		if($arr['key'][0]['VALUE']) $key = $name;
		else $key='';
		$default = trim($arr['default'][0]['VALUE']);
		$extra = trim($arr['extra'][0]['VALUE']);
	
		$string = '`'.$name.'`';
		$string .= ' '.$type;
		if(!$null) $string .= ' NOT NULL';
		else $string .= ' NULL';
		if($default!="") $string .= ' default \''.$default.'\'';
		if($extra) $string .= ' '.$extra;
		//if($key) $string .= ', PRIMARY KEY (`'.$name.'`)';
	
		$out[$name]['name']=$name;
		$out[$name]['type']=$type;
		$out[$name]['null']=$null;
		$out[$name]['key']=$key;
		$out[$name]['default']=$default;
		$out[$name]['extra']=$extra;
		$out[$name]['string']=$string;
		
		return $out;
	}
	
	function upgrade_form ($vars) {
		$tmp = '';
		$query="SELECT * FROM `#prefix#system` WHERE `name`='version'";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
		
		if(!mysql_errno() && mysql_num_rows($res)) {
			$arr=mysql_fetch_array($res);
			$version_now=$arr['value'];
		} else {
			return ucfirst(phr('UPGRADE_MINIMUM_VERSION'));
		}
		
		$last_ok=$this->upgrade_get_last_ok();
		
		$tmp .= ucphr('VERSION_INSTALLED').': <b>'.$version_now.'</b>'."<br/>\n";
		$tmp .= ucphr('LAST_UPGRADE_DONE').': <b>'.$last_ok.'</b>'."<br/>\n";

		if(!$this->upgrade_available()) {
			$tmp .= '<br/>'.ucphr('NO_UPGRADE_IS_AVAILABLE');
			return $tmp;
		}
		
		$tmp .= '
<form action="'.ROOTDIR.'/admin/upgrade.php" method="POST">
	<input type="hidden" name="command" value="upgrade">
<table>
	<tr>
	<td>'.ucphr('UPGRADE_FILE').':</td>
	<td>
		<select name="file">';
		
		$upgrades=list_upgrades(ROOTDIR.'/upgrade');
		sort($upgrades);
		$num=count($upgrades);
		for (reset ($upgrades); list ($key, $value) = each ($upgrades); ) {
			$i++;
			$filename=$value;
			
			if(!$this->upgrade_upgrade_to_do ($filename)) continue;
			
			$tmp_local = eregi_replace("\.sql$",'',$filename);
			list($tmp_local,$number,$version_from,$version_to) = explode("_", $tmp_local);
			$description=ucfirst(phr('VERSION_FROM'));
			$description.=' '.$version_from;
			$description.=' '.phr('VERSION_TO');
			$description.=' '.$version_to;
			
			if($i==$num) $selected=' selected';
			else $selected='';
			
			$tmp .= '
			<option value="'.$filename.'"'.$selected.'>'.$description.'</option>';
		}
		$tmp .= '
		</select>
	</td>
	</tr>
	<tr>
	<td colspan="2"><input type="checkbox" name="simulate">'.ucphr('SIMULATE_ONLY').'</td>
	</tr>
	<tr>
	<td>'.ucphr('VERBOSITY').':</td>
	<td>
		<select name="verbosity">
			<option value="0">'.ucphr('VERBOSITY_NONE').'</option>
			<option value="1" selected>'.ucphr('VERBOSITY_LOW').'</option>
			<option value="2">'.ucphr('VERBOSITY_HIGH').'</option>
		</select>
	</td>
	</tr>
	<tr>
	<td colspan="2" align="center"><input type="submit"></td>
	</tr>
</table>
</form>';
		
		if($vars['devel']) {
			$tmp .= '
<form action="'.ROOTDIR.'/admin/upgrade.php" method="POST">
	<input type="hidden" name="command" value="upgrade">
<table>
	<tr>
	<td>'.ucphr('UPGRADE_STRING').':</td>
	</tr>
	<tr>
	<td>
		<textarea name="string" rows="15" cols="120"></textarea>
	</td>
	</tr>
	<tr>
	<td>'.ucphr('VERBOSITY').':
		<select name="verbosity">
			<option value="0">'.ucphr('VERBOSITY_NONE').'</option>
			<option value="1" selected>'.ucphr('VERBOSITY_LOW').'</option>
			<option value="2">'.ucphr('VERBOSITY_HIGH').'</option>
		</select>
	</td>
	</tr>
	<tr>
	<td align="center"><input type="submit"></td>
	</tr>
</table>
</form>';
		}
		return $tmp;
	
	} 

	function restore_form ($vars) {
	
	$maxarr[] = ini_get('post_max_size');
	$maxarr[] = ini_get('upload_max_filesize');
	
	foreach ($maxarr as $key => $value) {
		if (eregi("G$", $maxarr[$key])) $maxarr[$key] = (int) $maxarr[$key] * 1024 * 1024 * 1024;
		elseif (eregi("M$", $maxarr[$key])) $maxarr[$key] = (int) $maxarr[$key] * 1024 * 1024;
		elseif (eregi("K$", $maxarr[$key])) $maxarr[$key] = (int) $maxarr[$key] * 1024;
	}
	$max=max($maxarr);
	
	$tmp = '
<form action="'.ROOTDIR.'/admin/restore.php" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="command" value="restore">
<table>
	<tr>
	<td colspan="2">
		<font color="#FF0000">
		'.ucphr('RESTORE_WARNING').'
		</font>
		<br/>
		<br/>
		</td>
	</tr>
	<tr>
	<tr>
	<td>'.ucphr('RESTORE_DB').':</td>
	<td>
			<select name="db_destination">';
			
			$db_list = mysql_list_dbs();

			while ($arr_dbs = mysql_fetch_array($db_list)) {
				if($_SESSION['common_db']==$arr_dbs[0]) $selected=' selected';
				else $selected='';

				$tmp .=  '
				<option value="'.$arr_dbs[0].'"'.$selected.'>'.htmlentities($arr_dbs[0]).'</option>';
			}
			
			$tmp .= '
			</select>
	</td>
	</tr>
	<tr>
	<td>'.ucphr('RESTORE_FILE').':</td>
	<td>
		<input type="hidden" name="MAX_FILE_SIZE" value="'.$max.'">
		<input name="userfile" type="file">
	</td>
	</tr>
	<tr>
	<td colspan="2"><input type="checkbox" name="simulate">'.ucphr('SIMULATE_ONLY').':</td>
	</tr>
	<tr>
	<td>'.ucphr('VERBOSITY').':</td>
	<td>
		<select name="verbosity">
			<option value="0">'.ucphr('VERBOSITY_NONE').'</option>
			<option value="1" selected>'.ucphr('VERBOSITY_LOW').'</option>
			<option value="2">'.ucphr('VERBOSITY_HIGH').'</option>
		</select>
	</td>
	</tr>
	<tr>
	<td colspan="2" align="center"><input type="submit"></td>
	</tr>
</table>
</form>';
		if($vars['devel']) {
			$tmp .= '
<form action="'.ROOTDIR.'/admin/restore.php" method="POST">
	<input type="hidden" name="command" value="restore">
<table>
	<tr>
	<td>'.ucphr('UPGRADE_STRING').':</td>
	</tr>
	<tr>
	<td>
		<textarea name="string" rows="15" cols="120"></textarea>
	</td>
	</tr>
	<tr>
	<td>'.ucphr('VERBOSITY').':
		<select name="verbosity">
			<option value="0">'.ucphr('VERBOSITY_NONE').'</option>
			<option value="1" selected>'.ucphr('VERBOSITY_LOW').'</option>
			<option value="2">'.ucphr('VERBOSITY_HIGH').'</option>
		</select>
	</td>
	</tr>
	<tr>
	<td align="center"><input type="submit"></td>
	</tr>
</table>
</form>';
		}
	return $tmp;
	} 

}

?>