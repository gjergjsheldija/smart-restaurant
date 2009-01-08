<?php
/**
 * Smart Restaurant
 *
 * An open source application to manage restaurants
 *
 * @package		SmartRestaurant
 * @author		Gjergj Sheldija
 * @copyright	Copyright (c) 2008, Gjergj Sheldija
 * @license		http://www.gnu.org/licenses/gpl.txt
 * @since		Version 1.0
 * @filesource
 * 
 *  Smart Restaurant is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, version 3 of the License.
 *
 *	Smart Restaurant is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.

 *	You should have received a copy of the GNU General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 */

function check_db_status () {
	$query="USE `".$_SESSION['common_db'].'`';
	$res = common_query($query,__FILE__,__LINE__,true);
	if(!$res) {
		$url=ROOTDIR.'/install.php';
		header('Location: '.$url);
		$error_msg = common_header ('Database not found');
		$error_msg .= redirectJS ($url);
		$error_msg .= 'DB not found. Going to installation page.';
		$error_msg .= common_bottom ();
		echo $error_msg;
		die();
	}
	$query="SHOW TABLES";
	$tableslist = common_query($query,__FILE__,__LINE__,true);
	// this is unlikely to happen
	if(!$tableslist) {
		$url=ROOTDIR.'/install.php?command=fresh_install';
		header('Location: '.$url);
		$error_msg = common_header ('Database tables not found');
		$error_msg .= redirectJS ($url);
		$error_msg .= 'No table found on the database. Going to installation page.';
		$error_msg .= common_bottom ();
		echo $error_msg;
		die();
	}
	$numtables = mysql_num_rows ($tableslist);
	if(!$numtables) {
		$url=ROOTDIR.'/install.php?command=fresh_install';
		header('Location: '.$url);
		$error_msg = common_header ('Database tables not found');
		$error_msg .= redirectJS ($url);			/*RTG: I prefer to get a exception in somewhere in my code I use a not existing lang
			//(by the way, mysql_list_tables is deprecated)  // K: substituted with show tables query
			K: I don't agree. we simply call this functions for a lang, then it autonomously decides which table to use,
			This makes the function call easier.
			*/
		$error_msg .= 'No table found on the database. Going to installation page.';
		$error_msg .= common_bottom ();
		echo $error_msg;
		die();
	}
}

function timestamp_to_unix ($str) {
	$year=substr($str,0,4);
	$month=substr($str,4,2);
	$day=substr($str,6,2);
	$hour=substr($str,8,2);
	$minute=substr($str,10,2);
	$second=substr($str,12,2);

	$new_timestamp = $year.'/'.$month.'/'.$day.' '.$hour.':'.$minute.':'.$second;

	if (($unix = strtotime($new_timestamp)) === -1) return 0;
	return $unix;
}

function show_logo () {
	$ret = '<img src="'.ROOTDIR.'/images/logo2.jpg" alt="Smart Restaurant Logo">'."\n";
	return $ret;
}

function eq_to_number ($eq) {
	if(CONF_DEBUG_DISABLE_FUNCTION_INSERT) return $eq;
	
	$eq=trim($eq);
	
	if ($eq==='') return 0;
	
	$eq=ereg_replace(',','.',$eq);
	$eq=ereg_replace('[^-|(|)|0-9|+|*|/|.]','',$eq);
	
	while(ereg('^[^0-9|(|.|-]',$eq)) $eq = substr($eq,1);
	while(ereg('[^0-9|)]$',$eq)) $eq = substr($eq,0,-1);
	
	$eq='$num = '.$eq.';';
	
	@eval($eq);
	
	if(!is_numeric($num)) return 0;
	
	return $num;
}

function get_unit_type ($unit) {
	global $unit_types_volume;
	global $unit_types_mass;
	
	$unit = strtolower($unit);
	
	if(in_array($unit,$unit_types_mass)) return UNIT_TYPE_MASS;
	if(in_array($unit,$unit_types_volume)) return UNIT_TYPE_VOLUME;
	return UNIT_TYPE_NONE;
}

function get_unit_from_eq ($eq) {
	list($eq,$unit,$garbage) = explode(' ',$eq,3);
	$unit = strtolower($unit);
	$type = get_unit_type ($unit);
	return $type;
}

function get_default_unit ($type) {
	if ($type == UNIT_TYPE_MASS) return 'kg';
	if ($type == UNIT_TYPE_VOLUME) return 'l';
	return '';
}

function get_user_unit ($type) {
	if ($type == UNIT_TYPE_MASS) return CONF_UNIT_MASS;
	if ($type == UNIT_TYPE_VOLUME) return CONF_UNIT_VOLUME;
	return '';
}
/**
 *@author:mizuko
 * converted quantity
 */
function convert_units ($eq, $dest_unit='') {
	if(CONF_DEBUG_DISABLE_FUNCTION_INSERT) return $eq;
	
	global $convertion_constants;
	
	$eq=trim($eq);
	
	if ($eq==='') return 0;
	
	list($eq,$unit,$garbage) = explode(' ',$eq,3);
	$num = eq_to_number($eq);
	$unit = strtolower($unit);
	$dest_unit = strtolower($dest_unit);
	$type = get_unit_type ($unit);
	
	// defaults
	if ($type == UNIT_TYPE_MASS) {
		if($dest_unit==='') $dest_unit = 'kg';
		$intermediate_unit = 'kg';
	} elseif ($type == UNIT_TYPE_VOLUME) {
		if($dest_unit==='') $dest_unit = 'l';
		$intermediate_unit = 'l';
	}

	if(isset($unit)) {
		$conv = $unit.'-'.$dest_unit;
		$convinv = $dest_unit.'-'.$unit;
		$convtrans0 = $unit.'-'.$intermediate_unit;
		$convtrans1 = $dest_unit.'-'.$intermediate_unit;
		if (array_key_exists($conv,$convertion_constants)) {
			$num = $num * $convertion_constants[$conv];
		} elseif (array_key_exists($convinv,$convertion_constants)) {
			$num = $num / $convertion_constants[$convinv];
		} elseif (array_key_exists($convtrans0,$convertion_constants) && array_key_exists($convtrans1,$convertion_constants)) {
			$num = $num * $convertion_constants[$convtrans0];
			$num = $num / $convertion_constants[$convtrans1];
		}
	}
	return $num;
}

function common_set_error_reporting () {
	if (CONF_DEBUG_REPORT_NOTICES) error_reporting (E_ALL);
	else error_reporting (E_ALL ^ E_NOTICE);
	
	return 0;
}

function common_get_language () {
	if(isset($_SESSION['language'])) return $_SESSION['language'];
	
	$value=get_conf(__FILE__,__LINE__,'default_language');
	$value=trim($value);
	if ($value == null || strlen($value) == 0) return "en";

	return $value;
}

function common_find_col_lenght ($arr) {
	$trim=4;

	foreach ($arr as $value) {
		$value = str_replace('&nbsp;',' ',$value);
		$tmp=strlen($value);
		if ($tmp > ($col_len+$trim)) $col_len=$tmp-$trim;
	}
	return $col_len;
}

function database_query_translator ($query) {
	$prefix=$GLOBALS['table_prefix'];

	$query = str_replace ("#prefix#", $prefix, $query);
	if(isset($_SESSION['language'])) $query = str_replace ("#lang#", '_'.$_SESSION['language'], $query);
	else $query = str_replace ("#lang#", '', $query);
	
	return $query;
}

function database_query($query,$file,$line,$db,$silent=false) {
	if(!isset($GLOBALS['mysql_timer'])) $GLOBALS['mysql_timer']=0;
	$query = database_query_translator ($query);

	$start = microtime ();
	mysql_select_db($db);
	$res=@mysql_query($query);
	$end = microtime ();
	
	$start = explode (" ", $start);
	$start = (float)$start[0] + (float)$start[1];
	$end = explode (" ", $end);
	$end = (float)$end[0] + (float)$end[1];

	$elapsed = $end - $start;
	
	if(!isset($GLOBALS['mysql_queries'])) $GLOBALS['mysql_queries']=array();
	if(!isset($GLOBALS['mysql_queries_doubles'])) $GLOBALS['mysql_queries_doubles']=array();
	
	if(in_array($query,$GLOBALS['mysql_queries'])) $GLOBALS['mysql_queries_doubles'][]=$query;
	
	$GLOBALS['mysql_queries'][] = $query;
	
	if(CONF_DEBUG_DISPLAY_MYSQL_QUERIES) {
		$GLOBALS['mysql_queries_list'][] = $query.' - '.$file.' - '.$line;
	}
	$GLOBALS['mysql_timer'] = $GLOBALS['mysql_timer'] + $elapsed;
	
	if($errno=mysql_errno()) {
		$msg="Error in ".__FUNCTION__." - ";
		$msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
		$msg.='query: '.$query."\n";
		$msg_out = "\n".$msg;
		$msg_out .= 'file: '.$file."\n".'line: '.$line."\n";
		if (CONF_DISPLAY_MYSQL_ERRORS && !$silent) echo nl2br($msg_out)."\n";
		error_msg($file,$line,$msg);
		return false;
	}
	return $res;
}

function common_query($query,$file,$line,$silent=false) {
	$db=$_SESSION['common_db'];
	$res = database_query($query,$file,$line,$db,$silent);
	return $res;
}

function accounting_query($query,$file,$line,$silent=false) {
	$db=$_SESSION['common_db'];
	$res = database_query($query,$file,$line,$db,$silent);
	return $res;
}

function display_todo($servrd){

	clearstatcache(void);
	$lastupdate=date("j F Y G:i:s",filemtime("./todo.php"));

	echo "<hr><h5>\n";
	echo "CLIENT DATA: $servrd\n";
	echo "<br>Todo file last update:";
	echo "<b>$lastupdate</b></h5>";
	echo "<a href=\"todo.php\">Todo and Roadmap</a>";
}

function lang_to_file($string,$filename) {
	if (!is_writable($filename)) {
		print "Cannot write to file ($filename)";
		return 2;	
	}
	
	// Opening $filename in append mode.
	// The file pointer is at the bottom of the file.
	if (!$handle = fopen($filename, 'w')) {
		print "Cannot open file ($filename)";
		return 1;
	}
	
	// Write message to opened file.
	if (!fwrite($handle, $string)) {
		print "Cannot write to file ($filename)";
		return 3;
	}
	
	fclose($handle);
	return 0;
}

function lang_db_to_string($lang) {
	$output=
'<?xml version="1.0"?>
<!DOCTYPE SmartRestaurant [
	<!ELEMENT SmartRestaurant (type,data+)>
	<!ELEMENT type (#PCDATA)>
	<!ELEMENT language (#PCDATA)>
	<!ELEMENT data (item*)>
	<!ELEMENT item (#PCDATA)>
	<!ATTLIST item
		name (CDATA) #REQUIRED
	>
]>
<SmartRestaurant>
	<type> translation </type>
	<language>'.$lang.'</language>
	<data>
';
	$table="#prefix#lang";
	$lang_table="#prefix#lang_".$lang;

	$query="SELECT * FROM `".$table."` ORDER BY `name`";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	while($arr = mysql_fetch_array ($res)) {
		$id = $arr['id'];
		$name=$arr['name'];
	
		$query="SELECT `table_name` FROM `".$lang_table."` WHERE `table_id`='".$id."'";
		$reslocal=common_query($query,__FILE__,__LINE__);
		if(!$reslocal) return ERR_MYSQL;
	
		$arrlocal = mysql_fetch_array ($reslocal);
		$value=htmlentities($arrlocal['table_name']);

		$output.="\t\t".'<item name="'.$name.'">'.$value.'</item>'."\n" ;
	}
	$output.=
'	</data>
</SmartRestaurant>';

	return $output;
}

function start_language () {
	lang_read_all();
	
	if(get_conf(__FILE__,__LINE__,"default_language")=="") $conf_language="en";
	else $conf_language=get_conf(__FILE__,__LINE__,"default_language");
	
	if(!isset($_SESSION['language'])) $_SESSION['language']=$conf_language;
	
	if(isset($_SESSION['userid'])) {
		$user = new user($_SESSION['userid']);
		if(!empty($user -> data['language'])) $_SESSION['language'] = $user -> data['language'];
	}
	
	
	return 0;
}

function lang_read_all() {
	if(!CONF_XML_TRANSLATIONS) return 0;

	$dir=ROOTDIR.'/lang';
	clearstatcache();
	if ($handle = opendir($dir)) {
		while (false !== ($file = readdir($handle))) {
			if (is_file($dir.'/'.$file) && is_readable($dir.'/'.$file) && strtolower(substr($file,-4))=='.xml') {
				if($err=lang_file_reader ($dir.'/'.$file)) return $err;
			}
		}
		closedir($handle);
	}

	return 0;
}

function lang_file_reader($filename) {
	debug_msg(__FILE__,__LINE__,'reading lang faile: '.$filename);

	$xml_parser = xml_parser_create();
	
	$simple = '';
	
	if (!($fp = fopen($filename, "r"))) {
		return 1;
	}
	
	while ($data = fread($fp, 4096)) {
		$simple.=$data;
	}
	
	xml_parse_into_struct($xml_parser,$simple,$vals);
	
	xml_parser_free($xml_parser);
	
	for (reset ($vals); list ($key, $value) = each ($vals); ) {
		if($vals[$key]['tag']=='ITEM') {
			$name=$vals[$key]['attributes']['NAME'];
			$cont=$vals[$key]['value'];
			$local[$name]=$cont;
		}
		if($vals[$key]['tag']=='LANGUAGE') {
			$xml_lang=trim($vals[$key]['value']);
		}
		if($vals[$key]['tag']=='TYPE') {
			$xml_type=trim($vals[$key]['value']);
		}
		
	}
	
	if(strtolower($xml_type) != 'translation') {
		debug_msg(__FILE__,__LINE__,'unknown xml file type: '.$xml_type);
		return 2;
	}
	if(empty($xml_lang)) return 3;
	
	$GLOBALS['lang'][$xml_lang]=$local;

	unset($local);
	
	return 0;
}

function status_report ($name,$err) {
	global $tpl;
	if(!$err) $tmp = '<font color="green">'.ucphr($name).' ok'.'</font>'."<br/>\n";
	else $tmp = '<font color="red">'.ucphr($name).' '.ucphr('FAILED').'</font>'."<br/>\n";
	$tpl -> append ('messages',$tmp);
	
	if($err) error_display($err,true);
	return 0;
}

function error_display($number,$silent=false,$level=ERROR_LEVEL_USER) {
	global $tpl;
	$tmp='';
	if(!$silent) $tmp .= ucfirst(phr('ERROR')).': ';
	$tmp .= ucfirst(error_get($number))."<br/>\n";
	$tmp='<font color="Red">'.$tmp.'</font>';
	$tpl -> append ('messages',$tmp);

	if($level==ERROR_LEVEL_USER) return 0;
	elseif($level==ERROR_LEVEL_DEBUG) debug_msg(__FILE__,__LINE__,$msg);
	elseif($level==ERROR_LEVEL_ERROR) error_msg(__FILE__,__LINE__,$msg);
	
	return 0;
}

function error_get($number,$lang='') {
	$name = 'ERR_'.$number;
	if(empty($lang) && isset($_SESSION['language'])) $lang=$_SESSION['language'];
	else $lang = 'en';

	if(CONF_DEBUG_LANG_DISABLED) return $name;

	$charset = lang_get($lang,'CHARSET');
	
	if(CONF_XML_TRANSLATIONS) $ret = lang_get_xml($lang,$name,$charset);
	else $ret = lang_get_db($lang,$name,$charset);
	
	if($ret==$name) {
		if(CONF_XML_TRANSLATIONS) $ret = lang_get_xml($lang,'ERR_UNKNOWN',$charset);
		else $ret = lang_get_db($lang,'ERR_UNKNOWN',$charset);

		$ret .= ' ('.$number.')';
	}
	
	return $ret;
}

function phr($name) {
	if(isset($_SESSION['language'])) $lang = $_SESSION['language'];
	else $lang= get_conf(__FILE__,__LINE__,"default_language");
	
	$ret = lang_get($lang,$name);
	
	return $ret;
}

function ucphr($name) {
	if(isset($_SESSION['language'])) $lang = $_SESSION['language'];
	else $lang= get_conf(__FILE__,__LINE__,"default_language");
	
	$ret = lang_get($lang,$name);
	// optimisation
	//if(ord($ret)>97 && ord($ret)<122)
	$ret = ucfirst ($ret);
	
	return $ret;
}

function common_list_tables () {
	if(!isset($GLOBALS['cache_var'])) $GLOBALS['cache_var']=new cache();
	if($cache_out=$GLOBALS['cache_var'] -> gen_get ('mysql_list_tables')) return $cache_out;
	
	$query="SHOW TABLES";
	$res = common_query($query,__FILE__,__LINE__);
	if(!$res) return '';
	
	while ($arr = mysql_fetch_array($res)) {
		$tables[]=$arr[0];
	}
	
	$GLOBALS['cache_var'] -> gen_set ('mysql_list_tables',$tables);
	return $tables;
}

function lang_get($lang,$name) {
	if(CONF_DEBUG_LANG_DISABLED) return $name;

	if(empty($lang)) $lang="en";
	
	if(!isset($GLOBALS['cache_var'])) $GLOBALS['cache_var']=new cache();
	
	if($cache_out=$GLOBALS['cache_var']->lang_get ($lang,$name)) return $cache_out;

	if($name!='CHARSET') {
		if($cache_out=$GLOBALS['cache_var']->lang_get ($lang,'CHARSET')) $charset=$cache_out;
		else {
			if(CONF_XML_TRANSLATIONS) $charset = lang_get_xml($lang,'CHARSET','');
			else $charset = lang_get_db($lang,'CHARSET','');
			
			$GLOBALS['cache_var'] -> lang_set ($lang,'CHARSET',$charset);
		}
	}
	else $charset='';
	
	if(CONF_XML_TRANSLATIONS) $ret = lang_get_xml($lang,$name,$charset);
	else $ret = lang_get_db($lang,$name,$charset);
	
	$GLOBALS['cache_var'] -> lang_set ($lang,$name,$ret);
	
	return $ret;
}

function lang_get_xml($language,$name,$charset='iso-8859-1') {

	if(empty($language))
		$language="en";
	
	$value=$GLOBALS['lang'][$language][$name];
	if(!empty($value)) {
		$value=stripslashes($value);
		if($charset=='CHARSET' || empty($charset)) $charset='iso-8859-1';
		$value = html_entity_decode ($value,ENT_QUOTES,$charset);
		return $value;
	}
	
	debug_msg(__FILE__,__LINE__,"$name value missing in $language language file.");

	if(get_conf(__FILE__,__LINE__,"default_language")=="") $conf_language="en";
	else $conf_language=get_conf(__FILE__,__LINE__,"default_language");

	$value=$GLOBALS['lang'][$conf_language][$name];
	if(!empty($value)) {
		$value=stripslashes($value);
		if($charset=='CHARSET' || empty($charset)) $charset='iso-8859-1';
		$value = html_entity_decode ($value,ENT_QUOTES,$charset);
		return $value;
	}
	debug_msg(__FILE__,__LINE__,"$name value missing in $conf_language language file.");
	
	if(is_array($GLOBALS['lang'])) {
	for (reset ($GLOBALS['lang']); list ($key, $value) = each ($GLOBALS['lang']); ) {
		$value=$GLOBALS['lang'][$key][$name];
		
		if(empty($value)) continue;
		
		$value=stripslashes($value);
		if($charset=='CHARSET' || empty($charset)) $charset='iso-8859-1';
		$value = html_entity_decode ($value,ENT_QUOTES,$charset);
		
		return $value;
	}
	}
	
	return $name;
}

function lang_get_db($lang,$name,$charset='iso-8859-1'){

	if(empty($lang)) $lang="en";

	$table="#prefix#lang";
	$lang_table="#prefix#lang_".$lang;


	$query="SELECT `table_name` FROM `".$table."` WHERE `name`='".$name."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	$arr = mysql_fetch_array ($res);
	

	$ret=$arr['table_name'];
	$ret=stripslashes($ret);
	
	if($charset=='CHARSET' || empty($charset)) $charset='iso-8859-1';
	$ret = html_entity_decode ($ret,ENT_QUOTES,$charset);
	
	return $ret;
}

function var_dump_table($var){
$tmp = '';
$tmp .= '
<table bgcolor="'.color(-1).'">';

if(is_array($var)){
	foreach ($var as $key=>$value) {
	$tmp .= '
	<tr bgcolor="'.color($i).'">
		<td>['.$key.']</td>
		<td>';
		if(is_array($value)) {
			$tmp .= var_dump_table($var[$key]);
		} else $tmp .= $value;
		$tmp .= '
		</td>
	</tr>';
	$i++;
	}
} else {
	$tmp .= '
	<tr bgcolor="'.color($i).'">
		<td>&nbsp;</td>
		<td>'.$var.'
		</td>
	</tr>';
}
$tmp .= '
</table>';

return $tmp;
}

function var_dump_string($var){
	if(is_array($var)){
		for (reset ($var); list ($key, $value) = each ($var); ) {
			$out .= '['.$key.'] -> ';
			if(is_array($value)) var_dump_string($var[$key]);
			else $out .= $value.', ';
		}
	} else {
		$out .= $var;
	}
	return $out;
}

function list_upgrades($dir) {
clearstatcache();
if ($handle = opendir($dir)) {
	while (false !== ($file = readdir($handle))) {
		if (is_file($dir.'/'.$file) && is_readable($dir.'/'.$file) && $file != "." && $file != ".." && eregi("^mhr_",$file) && eregi("\.sql$",$file)) {
			$output[]=$file;
		}
	}
	closedir($handle);
}

return $output;
}

function list_drivers($dir) {
clearstatcache ();
	if ($handle = opendir ( $dir )) {
		while ( false !== ($file = readdir ( $handle )) ) {
			if (is_file ( $dir . '/' . $file ) && is_readable ( $dir . '/' . $file ) && $file != "." && $file != "..") {
				$file = str_replace ( '.php', "", $file );
				$drivers [] = $file;
			}
		}
		closedir ( $handle );
	}
	return $drivers;
}

function list_templates($dir) {
clearstatcache ();
	$templates = array ();
	if ($handle = opendir ( $dir )) {
		while ( false !== ($file = readdir ( $handle )) ) {
			if (is_dir ( $dir . '/' . $file ) && is_readable ( $dir . '/' . $file ) && $file != "." && $file != ".." && is_dir ( $dir . '/' . $file . '/prints' )) {
				
				if (! in_array ( $file, $templates ))
					$templates [] = $file;
			}
		}
		closedir ( $handle );
	}
	return $templates;
}

function list_languages($dir) {
clearstatcache();
$langs=array();
$points=array();

if ($handle = opendir($dir)) {
	while (false !== ($file = readdir($handle))) {
		if (is_file($dir.'/'.$file) && is_readable($dir.'/'.$file) && $file != "." && $file != "..") {
			$code = strtolower ($file);
			
			$code = str_replace ('lang_',"",$code);
			$code = str_replace ('.php',"",$code);
			$code = str_replace ('_waiter',"",$code);
			
			if(!isset($points[$code])) $points[$code]=0;
			
			if (strlen($file)==11) {				// lang_xx.php file
				$points[$code]++;
			} elseif (strlen($file)==18) {			// // lang_xx_waiter.php file
				$points[$code]++;
			}
			
			// we use >= instead of == to allow having useless lang files (caused by old versions)
			if(!in_array($code,$langs) && $points[$code]>=LANG_FILES_NUMBER) {	// all the lang files have been found
				$langs[]=$code;
			}
		}
	}
	closedir($handle);
}
return $langs;
}

function list_db_languages() {
	$points = array ();
	
	$res_lang = mysql_list_tables ( $_SESSION ['common_db'] );
	while ( $arr_lang = mysql_fetch_array ( $res_lang ) ) {
		if (eregi ( $GLOBALS ['table_prefix'] . '[^_]*_*.*[^_]*_.?.?$', $arr_lang [0] )) {
			$lang_now = eregi_replace ( $GLOBALS ['table_prefix'] . "[^_]*_*.*[^_]*_(.?.?)$", "\\1", $arr_lang [0] );
			$lang_now = substr ( $lang_now, - 2 ); // this is useless if eregi works correctly, but a check it's better, isn't it?
			

			if (! isset ( $points [$lang_now] ))
				$points [$lang_now] = 0;
			
			$points [$lang_now] ++;
			
			if ($points [$lang_now] == LANG_TABLES_NUMBER) // all the lang tables have been found
				$lang_array [] = $lang_now;
		}
	}
	$langs = $lang_array;
	return $langs;
}

function css_line_admin($i){
	if($i==-2){
		return 'admin_table';
	}elseif($i==-1){
		return 'admin_table';
	} elseif(($i%2)){
		return 'admin_tr_0';
	} else {
		return 'admin_tr_1';
	}
}

function color($i){
	if($i==-2){
		return MGMT_COLOR_BACKGROUND;
	}elseif($i==-1){
		return MGMT_COLOR_TABLEBG;
	} elseif(($i%2)){
		return MGMT_COLOR_CELLBG1;
	} else {
		return MGMT_COLOR_CELLBG0;
	}
}

function check_output_files() {
	clearstatcache();

	$name = ERROR_FILE;
	if(!file_exists($name)) {
		$dirname=dirname($name);
		if(!is_writeable($dirname))
			return 2;
	} elseif (!is_writeable($name))
		return 1;

	if(CONF_DEBUG) {
		$name = DEBUG_FILE;
		if(!file_exists($name)) {
			$dirname=dirname($name);
			if(!is_writeable($dirname))
				return 4;
		} elseif (!is_writeable($name))
			return 3;
	}

	return 0;
}

function check_conf_file($name) {
	clearstatcache();

	if(!file_exists($name)) {
		$dirname=dirname($name);
		if(!is_writeable($dirname))
			return 2;
	} elseif (!is_writeable($name))
		return 1;

	return 0;
}

function get_db_data($file,$line,$db,$table,$field,$id){
	$cache = new cache ();
	if($cache_out=$cache -> get ($table,$id,$field)) return $cache_out;
	
	$table='#prefix#'.$table;
	$query="SELECT * FROM `$table` WHERE id='$id'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;
	$row = mysql_fetch_array ($res);
	$cache -> set ($table,$id,$field,$row["$field"]);
	return $row["$field"];
}

function get_conf($file,$line,$name){
	$cache = new cache ();
	$cache_out=$cache -> get ('#prefix#conf',$name,'value');
	if($cache_out!='') return $cache_out;

	$query="SELECT * FROM `#prefix#conf` WHERE `name`='$name'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;
	
	$arr = mysql_fetch_array ($res);
	$ret = $arr['value'];
	
	$cache -> set ('#prefix#conf',$name,'value',$ret);
	
	return $ret;
}

function common_find_first_db($db_wanted='') {
	
	$arr['db'] = $GLOBALS['db_common'];
	return $arr['db'];
}

function stri_replace ($find,$replace,$string) {

	if(!is_array($find))
		$find = array($find);
	if(!is_array($replace))
	{
		if(!is_array($find)) $replace = array($replace);
		else
		{
			// this will duplicate the string into an array the size of $find
			$c = count($find);
			$rString = $replace;
			unset($replace);
			for ($i = 0; $i < $c; $i++)
			{
				$replace[$i] = $rString;
			}
		}
	}
	foreach($find as $fKey => $fItem)
	{
		$between = explode(strtolower($fItem),strtolower($string));
		$pos = 0;
		foreach($between as $bKey => $bItem)
		{
			$between[$bKey] = substr($string,$pos,strlen($bItem));
			$pos += strlen($bItem) + strlen($fItem);
		}
		$string = implode($replace[$fKey],$between);
	}
	return($string);
}

function common_header($title,$mgmt='') {	
	if(IN_CONFIG) {
		$charset='iso-8859-1';
		$javascript = CONF_JS_URL_CONFIG;
		$css = CONF_CSS_URL_CONFIG;
	} else {
		$charset = phr('CHARSET');
		$javascript = CONF_JS_URL;
		$css = CONF_CSS_URL;
	}

	$msg='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
    <html>
	<head>
	<meta http-equiv="content-type" content="text/html; charset='.$charset.'">
	<title>Smart Restaurant - '.$title.'</title>
	<script type="text/javascript" language="JavaScript" src="'.$javascript.'"></script>
	<link rel="stylesheet" href="'.$css.'" type="text/css">
	<meta http-equiv="Cache-Control" content="no-cache">
	<meta http-equiv="Expires" content="0">
	</head>
	<body'.$mgmt.'>
	<center>
';
	return $msg;
}

function strip_newlines($msg) {
	$msg=str_replace("\r\n",'',$msg);
	$msg=str_replace("\r",'',$msg);
	$msg=str_replace("\n",'',$msg);
	return $msg;
}

function help_sticky ($msg_code) {
	
	$help=strip_newlines(ucphr($msg_code.'_HELP'));
	$title=strip_newlines(ucphr($msg_code));
	$help=str_replace("'","\'",$help);
	$title=str_replace("'","\'",$title);
	
	$overlib_code="return overlib('".$help."', AUTOSTATUS, CAPICON,'".ROOTDIR."/images/docs_small.png', CAPTION,'".$title."',FGCLASS, 'help_fg',BGCLASS, 'help_bg',CLOSECLICK, TEXTFONTCLASS, 'help_text',CAPTIONFONTCLASS,'help_caption', CLOSEFONTCLASS, 'help_close', WIDTH, 400, DELAY, 300);";
	
	$tmp .= '<img src="'.ROOTDIR.'/images/help_small.png" border="0" alt="'.ucphr('MHR_ONLINE_HELP').'" width="16" height="16" style="vertical-align: text-bottom"';
	$tmp .= ' onmouseover="'.$overlib_code.'" onmouseout="nd();"';
	$tmp .= '>';
	return $tmp;
}

function head_line ($title) {
	global $tpl;
	
	$output='
	<meta http-equiv="content-type" content="text/html; charset='.phr('CHARSET').'">
	<title>Smart Restauant - '.$title.'</title>
	<script type="text/javascript" language="JavaScript" src="'.ROOTDIR.'/overlib/overlib.js"><!-- overLIB (c) Erik Bosrup --></script>
	<script type="text/javascript" language="JavaScript" src="'.CONF_JS_URL.'"></script>
	<link rel="stylesheet" href="'.CONF_CSS_URL.'" type="text/css">
	<meta http-equiv="Cache-Control" content="no-cache">
	<meta http-equiv="Expires" content="0">';
	$tpl -> assign("head", $output);

	$tmp = '<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>';
	$tpl -> append("scripts", $tmp);

	return $output;
}
//mizuko
function head_line_waiter ($title) {
	global $tpl;
	
	$output='
	<meta http-equiv="content-type" content="text/html; charset='.phr('CHARSET').'">
	<META name="HandheldFriendly" content="True">
	<title>Smart Restaurant - '.$title.'</title>
	<script type="text/javascript" language="JavaScript" src="'.CONF_JS_URL_WAITER.'"></script>

	<meta http-equiv="Cache-Control" content="no-cache">
	<meta http-equiv="Expires" content="0">';
	$tpl -> assign("head", $output);

	$tpl -> append("scripts", $tmp);

	return $output;
}

function disconnect_line () {
	if(isset($_SESSION['userid'])) {
		$user = new user($_SESSION['userid']);

		$output = ucfirst(phr('IF_YOU_ARE_NOT_DISCONNECT_0')).' <b>'.$user->data['name'].'</b> '.ucfirst(phr('IF_YOU_ARE_NOT_DISCONNECT_1')).'<br/>'."\n";
	}
	return $output;
}

function disconnect_line_pos () {
	if(isset($_SESSION['userid'])) {
		$user = new user($_SESSION['userid']);

		$output = '<a href="disconnect.php"><img src='.IMAGE_LOGOUT.'></a>';
	}
	return $output;
}

function common_bottom() {

	$msg.='
	</center>
	</body>
</html>';
	return $msg;
}

function next_free_id($db,$table) {
	$query="SELECT `id` FROM `$table` ORDER BY `id` DESC";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	if(!mysql_num_rows($res)){
		return 1;
	}

	$arr=mysql_fetch_array($res);
	return $arr['id']+1;

}

function request($name){

	$iget="";
	$iget=isset($_REQUEST[$name]) && !empty($_REQUEST[$name]) ? $_REQUEST[$name] : '';
	return $iget;
}

function redirectJS($url){
$msg = '
	<script type="text/javascript">
	document.location.href="'.$url.'";
	</script>
';
return $msg;
}

function redirect_waiter($url) {
	$refresh_time=REFRESH_TIME;
	$msg = redirect_timed($url,$refresh_time*1000);
	return $msg;
}

function redirect_manage($url) {
	$refresh_time=get_conf(__FILE__,__LINE__,"refresh_time_management");
	$msg = redirect_timed($url,$refresh_time*1000);
	return $msg;
}

function redirect_timed($url,$millitime) {
	$msg = '
		<script type="text/javascript">
		setTimeout("redir(\''.$url.'\')", '.$millitime.');
		</script>
	';

	return $msg;
}

function unset_session_vars(){
	unset($_SESSION['command']);
	unset($_SESSION['id']);
	unset($_SESSION['orderby']);
	unset($_SESSION['ordersort']);
	unset($_SESSION['data']);
	unset($_SESSION['separated']);
	unset($_SESSION['extra_care']);
	unset($_SESSION['type']);
	unset($_SESSION['account']);
	unset($_SESSION['select_all']);
	return 0;
}

function unset_source_vars(){
	unset($_SESSION['sourceid']);
	unset($_SESSION['separated']);
	unset($_SESSION['extra_care']);
	unset($_SESSION['type']);
	unset($_SESSION['account']);
	unset($_SESSION['select_all']);
	return 0;
}

function debug_msg($file,$line,$msg){

	if(!CONF_DEBUG) return 0;
	
	$filename = DEBUG_FILE;
	
	$tmp=date("j/n/Y G:i:s",time());
	
	$tmp.=" Table: ".$_SESSION['tablenum'];
	$tmp .= " User: ".$_SESSION['userid'];
	$tmp .= " - $file line: $line - ";
	
	$tmp.=$msg;

	$msg=$tmp."\n";

	if (!is_writable($filename)) {
		print "Cannot write to file ($filename)";
		return 2;	
	}
	
	// Opening $filename in append mode.
	// The file pointer is at the bottom of the file.
	if (!$handle = fopen($filename, 'a')) {
		print "Cannot open file ($filename)";
		return 1;
	}
	
	// Write message to opened file.
	if (!fwrite($handle, $msg)) {
		print "Cannot write to file ($filename)";
		return 3;
	}
	
	fclose($handle);
	return 0;
}

function error_msg($file,$line,$msg){
	debug_msg($file,$line,'ERROR - '.$msg);
	
	$filename = ERROR_FILE;
	
	$tmp=date("j/n/Y G:i:s",time());
	
	$tmp.=" Table: ".$_SESSION['tablenum'];
	$tmp .= " User: ".$_SESSION['userid'];
	$tmp .= " - $file line: $line - ";
	
	$tmp.=$msg."\n";
	
	$msg=$tmp;
	
	if (!is_writable($filename)) {
		echo "Cannot write to file ($filename).<br/>";
		echo "Set the file permission to rw-rw-rw- (666) to allow the webserver to write on it.";
		return 2;
	}
	
	// Opening $filename in append mode.
	// The file pointer is at the bottom of the file.
	if (!$handle = fopen($filename, 'a')) {
		print "Cannot open file ($filename)";
		return 1;
	}
	
	// Write message to opened file.
	if (!fwrite($handle, $msg)) {
		print "Cannot write to file ($filename)";
		return 3;
	}
	
	fclose($handle);
	return 0;
}

/* Used to display detailed information about an array */
function printa($obj,$sql_commented=0) {
  global $__level_deep;
   
  if (!isset($__level_deep)) $__level_deep = array();

  if (is_object($obj))
    print '[obj]';
  elseif (is_array($obj)) {
    foreach(array_keys($obj) as $keys) {
      array_push($__level_deep, "[".$keys."]");
      $printa_output .= printa_child($obj[$keys],$sql_commented);
      array_pop($__level_deep);
    }
  } else {
  	if($sql_commented) $printa_output .= "# ";
  	$printa_output .= implode(" ",$__level_deep)." = $obj\n";
  	if(!$sql_commented) $printa_output .= "<br />";
}
	return $printa_output;

}

function printa_child ($obj,$sql_commented=0) {
  global $__level_deep;
  
  if (!isset($__level_deep)) $__level_deep = array();

  if (is_object($obj))
    print '[obj]';
  elseif (is_array($obj)) {
    foreach(array_keys($obj) as $keys) {
      array_push($__level_deep, "[".$keys."]");
      $local .= printa_child($obj[$keys],$sql_commented);
      array_pop($__level_deep);
    }
  } else {
  	if($sql_commented) $local .= "# ";
  	$local .= implode(" ",$__level_deep)." = $obj\n";
  	if(!$sql_commented) $local .= "<br />";
}
	return $local;

}

function generating_time($inizio){
	$output='';
	if(!CONF_DEBUG_PRINT_GENERATING_TIME && !CONF_DEBUG_DISPLAY_MYSQL_QUERIES) return $output;
	
	//Timer to tell how much time was needed to generate the page.
	$inizio = explode (" ", $inizio);
	$inizio = (float)$inizio[0] + (float)$inizio[1];

	$fine=microtime();
	$fine = explode (" ", $fine);
	$fine = (float)$fine[0] + (float)$fine[1];

	$intervallo=$fine-$inizio;
	$intervallo=round($intervallo,5);

	$output = '</center><div align="left">';
	
	if(CONF_DEBUG_PRINT_GENERATING_TIME &&
	CONF_DEBUG_PRINT_GENERATING_TIME_ONLY_IF_HIGH &&
	$intervallo>=CONF_DEBUG_PRINT_GENERATING_TIME_TRESHOLD){
		$output .= '<br><font color="#F10404"><b>'.$intervallo.'</b>
			'.ucfirst(phr('SECONDS_TO_GENERATE_THE_PAGE')).'.</font><br>';
		$msg='Generating time over max ('.CONF_DEBUG_PRINT_GENERATING_TIME_TRESHOLD.'): '.$intervallo.' secs reached';
		error_msg(__FILE__,__LINE__,$msg);
	}
	elseif(CONF_DEBUG_PRINT_GENERATING_TIME &&
	CONF_DEBUG_PRINT_GENERATING_TIME_ONLY_IF_HIGH &&
	$intervallo<CONF_DEBUG_PRINT_GENERATING_TIME_TRESHOLD){
		// do nothing because we're under treshold and only want to see if we're over it
	} elseif(CONF_DEBUG_PRINT_GENERATING_TIME && !CONF_DEBUG_PRINT_GENERATING_TIME_ONLY_IF_HIGH){
		$output .= '<br><b>'.$intervallo.'</b>
			'.ucfirst(phr('SECONDS_TO_GENERATE_THE_PAGE')).'.<br>';
		$sql_time=round($GLOBALS['mysql_timer'],5);
		$sql_percent=$sql_time/$intervallo*100;
		$sql_percent=round($sql_percent,1);
		$all_queries=count($GLOBALS['mysql_queries']);
		$dbl_queries=count($GLOBALS['mysql_queries_doubles']);
		$dbl_percent=$dbl_queries/$all_queries*100;
		$dbl_percent=round($dbl_percent,1);
		$output .= '<b>'.$sql_time.'</b> ('.$sql_percent.'% of time) spent for <b>'.$all_queries.'</b> Mysql queries (<b>'.$dbl_queries.'</b> doubles - '.$dbl_percent.'% of queries).<br/>';
		
		
		if(isset($GLOBALS['end_require_time'])) {
			$require_time=$GLOBALS['end_require_time'];
			
			$require_time = explode (" ", $require_time);
			$require_time = (float)$require_time[0] + (float)$require_time[1];
			$require_time=$require_time-$inizio;
			$require_time=round($require_time,5);
			$require_percent=$require_time/$intervallo*100;
			$require_percent=round($require_percent,1);
			
			$output .= '<b>'.$require_time.'</b> ('.$require_percent.'% of time) spent for initial requires.<br/>';
		}
		
	}
	
	
	if(CONF_DEBUG_DISPLAY_MYSQL_QUERIES) {
		$i=0;
		foreach (array_unique($GLOBALS['mysql_queries_doubles']) as $query) {
			$GLOBALS['mysql_queries_doubles_ord']['query'][$i] = $query;
			$GLOBALS['mysql_queries_doubles_ord']['times'][$i] = array_count_occurr($GLOBALS['mysql_queries_doubles'],$query);
			$i++;
		}
		arsort($GLOBALS['mysql_queries_doubles_ord']['times']);
		
		$output .= '<hr><b>'.count($GLOBALS['mysql_queries_doubles']).' double queries</b> (most requested first)<br>'."\n";
		foreach ($GLOBALS['mysql_queries_doubles_ord']['times'] as $index => $times) {
			$query = $GLOBALS['mysql_queries_doubles_ord']['query'][$index];
			$output .= $times.'x - '.$query.'<br>'."\n";
		}
		$output .= '<hr><b>All '.count($GLOBALS['mysql_queries']).' queries</b> (chronological order)<br>'."\n";
		foreach ($GLOBALS['mysql_queries_list'] as $query) {
			$output .= $query.'<br>'."\n";
		}
	}

	return $output;
}

function array_count_occurr ($arr,$target) {
	$i=0;
	foreach ($arr as $val)
		if($val==$target) $i++;
	return $i;
}

function elapsed_time($inizio,$fine){
	//tells how much time was elapsed between two given microtimes.
	$inizio = explode (" ", $inizio);
	$inizio = $inizio[0] + $inizio[1];

	$fine = explode (" ", $fine);
	$fine = $fine[0] + $fine[1];

	$intervallo=$fine-$inizio;
	return $intervallo;
}

?>
