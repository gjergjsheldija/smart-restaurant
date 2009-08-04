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
		return false;
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
				$db_type=trim($line);
				$db_type=strtolower($db_type);
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
					$res_local = mysql_db_query($_SESSION['common_db'],$query_local);
					while($arr_db = mysql_fetch_array($res_local)) {
						
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
	
	function tree_to_array ($orig) {
		if(!is_array($orig)) return 1;
		
		$work=$orig['SmartRestaurant'][0]['data'][0]['database'][0];

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
	
		$out[$name]['name']=$name;
		$out[$name]['type']=$type;
		$out[$name]['null']=$null;
		$out[$name]['key']=$key;
		$out[$name]['default']=$default;
		$out[$name]['extra']=$extra;
		$out[$name]['string']=$string;
		
		return $out;
	}
}

?>