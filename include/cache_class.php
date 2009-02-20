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
	 
/*
The caching system:

everything is in one single var, called cache, saved on user session.
When a new cache object is inserted it should be classified as follows:
1. class name or 'lang'
2. 

*/
class cache {
	function cache() {
		if(!CONF_CACHE_TYPE) return 0;
		if(CONF_CACHE_TYPE==1 && !isset($GLOBALS['cachedata_glob'])) {
			$GLOBALS['cachedata_glob']=array();
		} elseif(CONF_CACHE_TYPE==2 && !isset($_SESSION['cachedata'])) {
			$_SESSION['cachedata']=array();
		} else {
			if(!isset($GLOBALS['cachedata_glob'])) {
				$GLOBALS['cachedata_glob']=array();
			}
			if(!isset($_SESSION['cachedata'])) {
				$_SESSION['cachedata']=array();
			}
		}
		
	}
	
	function get ($table,$id,$field) {
		if(!CONF_CACHE_TYPE) return '';
		if(CONF_CACHE_TYPE==1 && isset($GLOBALS['cachedata_glob'][$table][$id][$field])) return $GLOBALS['cachedata_glob'][$table][$id][$field];
		elseif(CONF_CACHE_TYPE==2 && isset($_SESSION['cachedata'][$table][$id][$field])) return $_SESSION['cachedata'][$table][$id][$field];
		elseif(isset($GLOBALS['cachedata_glob'][$table][$id][$field])) return $GLOBALS['cachedata_glob'][$table][$id][$field];
		return '';
	}

	function set ($table,$id,$field,$value) {
		if(!CONF_CACHE_TYPE) return 0;
		if(CONF_CACHE_TYPE==1) $GLOBALS['cachedata_glob'][$table][$id][$field]=$value;
		elseif(CONF_CACHE_TYPE==2) $_SESSION['cachedata'][$table][$id][$field]=$value;
		else $GLOBALS['cachedata_glob'][$table][$id][$field]=$value; 
		return 0;
	}

	function lang_get ($lang,$name) {
		if(!CONF_CACHE_TYPE) return '';
		if(CONF_CACHE_TYPE==1 && isset($GLOBALS['cachedata_glob']['language_data'][$lang][$name])) return $GLOBALS['cachedata_glob']['language_data'][$lang][$name];
		elseif(isset($_SESSION['cachedata']['language_data'][$lang][$name])) return $_SESSION['cachedata']['language_data'][$lang][$name];
		return '';
	}

	function lang_set ($lang,$name,$value) {
		if(!CONF_CACHE_TYPE) return 0;
		if(CONF_CACHE_TYPE==1) $GLOBALS['cachedata_glob']['language_data'][$lang][$name]=$value;
		else $_SESSION['cachedata']['language_data'][$lang][$name]=$value;
		return 0;
	}

	function gen_get ($name) {
		if(!CONF_CACHE_TYPE) return '';
		if(CONF_CACHE_TYPE==1 && isset($GLOBALS['cachedata_glob']['generic_data'][$name])) return $GLOBALS['cachedata_glob']['generic_data'][$name];
		elseif(isset($_SESSION['cachedata']['generic_data'][$name])) return $_SESSION['cachedata']['generic_data'][$name];
		return '';
	}

	function gen_set ($name,$value) {
		if(!CONF_CACHE_TYPE) return 0;
		if(CONF_CACHE_TYPE==1) $GLOBALS['cachedata_glob']['generic_data'][$name]=$value;
		else $_SESSION['cachedata']['generic_data'][$name]=$value;
		return 0;
	}

	function flush ($table,$id) {
		if(CONF_CACHE_TYPE==1) unset($GLOBALS['cachedata_glob'][$table][$id]);
		elseif(CONF_CACHE_TYPE==2) unset($_SESSION['cachedata'][$table][$id]);
		else unset($GLOBALS['cachedata_glob'][$table][$id]);
		return 0;
	}

	function flush_all () {
		if(CONF_CACHE_TYPE==1) unset($GLOBALS['cachedata_glob']);
		elseif(CONF_CACHE_TYPE==2) unset($_SESSION['cachedata']);
		else {
			unset($GLOBALS['cachedata_glob']);
			unset($_SESSION['cachedata']);
		}
		return 0;
	}
	
	function show () {
		if(CONF_CACHE_TYPE==1) return var_dump_table($GLOBALS['cachedata_glob']);
		elseif(CONF_CACHE_TYPE==2) return var_dump_table($_SESSION['cachedata']);
		else return var_dump_table($GLOBALS['cachedata_glob']).'<hr>'.var_dump_table($_SESSION['cachedata']);
	}
}
?>