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

function checker_check_only ($devel=false,$langs=array()) {
	$to_check_user=array(
	'ingreds',
	'dishes',
	'categories'
	);
	
	$to_check_admin=array(
	'conf',
	'ingreds',
	'dishes',
	'categories',
	'lang',
	'mgmt_people_types',
	'mgmt_types'
	);
	
	if($devel) $to_check=$to_check_admin;
	else $to_check=$to_check_user;
	
	for (reset ($to_check); list ($key, $value) = each ($to_check); ) {
		$table=$value;
	
		$res_lang=mysql_list_tables($_SESSION['common_db']);
		while($arr_lang=mysql_fetch_array($res_lang)) {
			if($lang_now=stristr($arr_lang[0],$GLOBALS['table_prefix'].$table.'_')) {
				$lang_now= substr($lang_now,-2);
			if(isset($langs) && !empty($langs) && in_array($lang_now,$langs)) $lang_array[]=$arr_lang[0];
			elseif(!isset($langs) || empty($langs)) $lang_array[]=$arr_lang[0];
			}
		}
	}
	
	// checks the data and tell us if some error has been found
	$found_error=false;
	for (reset ($lang_array); list ($key, $value) = each ($lang_array); ) {
		$lang_table=$value;
		$table=substr($lang_table,0,-3);
	
		$corrections[$lang_table]=checker_table($lang_table);
	
		if(is_array($corrections[$lang_table])) {
			return true;
		}
	}

	return 0;
}

function checker_check_and_correct ($table,$silent=false,$langs=array()) {
	$res_lang=mysql_list_tables($_SESSION['common_db']);
	while($arr_lang=mysql_fetch_array($res_lang)) {
		if($lang_now=stristr($arr_lang[0],$GLOBALS['table_prefix'].$table.'_')) {
			$lang_now= substr($lang_now,-2);
			if(isset($langs) && !empty($langs) && in_array($lang_now,$langs)) $lang_array[]=$arr_lang[0];
			elseif(!isset($langs) || empty($langs)) $lang_array[]=$arr_lang[0];
		}
	}

	foreach ($lang_array as $key => $value) {
		$lang_table=$value;
		$table=substr($lang_table,0,-3);
	
		$corrections[$lang_table]=checker_table($lang_table);
	}

	foreach ($lang_array as $key => $value) {
		$lang_table=$value;
		$table=substr($lang_table,0,-3);

		debug_msg(__FILE__,__LINE__,'corrector called for table '.$lang_table);

		if(is_array($corrections[$lang_table])) {
			checker_corrector($lang_table,$corrections[$lang_table],$silent);
		}
	}
}

function checker_corrector($lang_table,$corrections,$silent=false) {
	global $tpl;
	$table=substr($lang_table,0,-3);

	if(!is_array($corrections)) {
		$tmp = "Error in ".__FILE__." :: ".__FUNCTION__." :: ".__LINE__."<br />\n";
		$tpl -> append('messages',$tmp);
		return 1;
	}
	if(count($corrections['delete']['id'])) {
		foreach ($corrections['delete']['id'] as $id) {
			$err=checker_delete($lang_table,$id);
			
			$descr=$corrections['delete']['name'][$id];
			if(strlen($descr)>100) $descr=substr($descr,0,100).'...';
			if(!$err && !$silent) $output .= '<br/>'.$lang_table.' -> id: '.$id.' ('.$descr.') deleted'."\n";
			elseif ($err) return $output;
		}
	}
	if(count($corrections['create']['id'])) {
		foreach ($corrections['create']['id'] as $id) {
			$err=checker_insert($lang_table,$id);
			
			$descr=$corrections['create']['name'][$id];
			if(strlen($descr)>100) $descr=substr($descr,0,100).'...';
			if(!$err && !$silent) $output .= '<br/>'.$lang_table.' -> table_id: '.$id.' ('.$descr.') inserted'."\n";
			elseif ($err) return $output;
		}
	}
	return $output;
}

function checker_insert($lang_table,$input_id) {
	$table=substr($lang_table,0,-3);

	$tmp_table= stri_replace ($GLOBALS['table_prefix'],'',$table);
	$name=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],$tmp_table,'name',$input_id);

	$name=str_replace("'","\'",$name);
	$input_id=str_replace("'","\'",$input_id);
	$name=str_replace('"','\"',$name);
	$input_id=str_replace('"','\"',$input_id);

/*	$query="INSERT INTO `$lang_table` ( `table_id` , `table_name` ) VALUES ( '$input_id' , '' )";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return mysql_errno();*/
	
	return 0;
}


function checker_delete($lang_table,$input_id) {
	$query="DELETE FROM $lang_table WHERE `id`='$input_id'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return mysql_errno();

	return 0;
}

function checker_report($lang_table,$corrections) {
	global $tpl;
	$table=substr($lang_table,0,-3);
	
	$output = '';

	if(!is_array($corrections)) {
		$tmp = "Error in ".__FILE__." :: ".__FUNCTION__." :: ".__LINE__."<br />\n";
		$tpl -> append("messages", $tmp);
		return 1;
	}

	if(count($corrections['delete']['id'])) {
		$output .= "<br />\n";
		$output .= '<font color="#FF0000">'."\n";
		$output .= "Records to be deleted from table $lang_table:<br />\n";
		$output .= "</font>\n";
		$output .= "(Records present in $lang_table but not in $table)<br />\n";
		foreach ($corrections['delete']['id'] as $id) {
			$descr=$corrections['delete']['name'][$id];
			if(strlen($descr)>100) $descr=substr($descr,0,100).'...';
			
			$output .= 'id: '.$id." (".$descr.")<br />\n";
		}
	}

	if(count($corrections['create']['id'])) {
		$output .= "<br />\n";
		$output .= '<font color="#FF0000">'."\n";
		$output .= "To be created in table $lang_table:<br />";
		$output .= "</font>\n";
		$output .= "(Records present in $table but not in $lang_table)<br />";
		foreach ($corrections['create']['id'] as $id) {
			$descr=$corrections['create']['name'][$id];
			if(strlen($descr)>100) $descr=substr($descr,0,100).'...';
			
			$output .= 'table_id: '.$id." (".$descr.")<br />\n";
		}
	}

	return $output;
}

function checker_table($lang_table) {
	$table=substr($lang_table,0,-3);

	$corrections['create']=array();
	$corrections['delete']=array();

	$query="SELECT * FROM `".$table."`";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return mysql_errno();
	
/*	while($arr=mysql_fetch_array($res)) {
		$id=$arr['id'];
		$query="SELECT `name` FROM `".$lang_table."` WHERE `table_id`='".$id."'";
		$res2=common_query($query,__FILE__,__LINE__);
		if(!$res2) return mysql_errno();
		
		if(!mysql_num_rows($res2)) {
			// echo "Expected table_id ".$id." not found in table ".$lang_table." - This record is present in $table but not in $lang_table<br />";
			$corrections['create']['id'][]=$id;
			$corrections['create']['name'][$id]=$arr['name'];
		}
	}*/

	$query="SELECT * FROM `".$lang_table."`";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return mysql_errno();
	
	while($arr=mysql_fetch_array($res)) {
		$query="SELECT `id` FROM `".$table."` WHERE `id`='".$arr['table_id']."'";
		$res2=common_query($query,__FILE__,__LINE__);
		if(!$res2) return mysql_errno();
		
		if(!mysql_num_rows($res2)) {
			//echo "Expected id ".$id." not found in table ".$table." - This record is present in $lang_table but not in $table<br />";
			$corrections['delete']['id'][]=$arr['id'];
			$corrections['delete']['name'][$arr['id']]=$arr['table_name'];
		}
	}


	if(!count($corrections['create']) && !count($corrections['delete'])) {
		$corrections=0;
	}

	return $corrections;
}

?>