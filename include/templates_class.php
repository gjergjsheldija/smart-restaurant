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

class template {

	var $filename;
	var $string;
	var $vars;
	var $output;

	function template() {
		$this->vars=array();
	}
	
	function set_print_template_file ($destination,$type) {
		$query="SELECT * FROM `#prefix#dests` WHERE `id`='$destination'";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
		
		if(!$arr=mysql_fetch_array($res)) {
			$error_msg='Printer not found id: '.$destination;
			error_msg(__FILE__,__LINE__,$error_msg);
			echo $error_msg;
			return ERR_PRINTER_NOT_FOUND;
		}
	
		$this->filename=ROOTDIR.'/templates/'.$arr['template'].'/prints/'.$type.'.tpl';
		
		return 0;
	}
	
	function set_waiter_template_file ($type) {
		$template='default';
		if(isset($_SESSION['userid'])) {
			$user = new user($_SESSION['userid']);
			if(!empty($user->data['template'])) $template=$user->data['template'];
		}
	
		$this->filename=ROOTDIR.'/templates/'.$template.'/'.$type.'.tpl';
		
		return 0;
	}
	
	function set_admin_template_file ($type) {
		$template='default';
		if(isset($_SESSION['userid'])) {
			$user = new user($_SESSION['userid']);
			if(!empty($user->data['template'])) $template=$user->data['template'];
		}

		$this->filename=ROOTDIR.'/templates/'.$template.'/admin/'.$type.'.tpl';
		
		return 0;
	}
	
	function assign($var,$value) {
		$value=str_replace('{','[[[curlyl]]]',$value);
		$value=str_replace('}','[[[curlyr]]]',$value);
		$this->vars[$var]=$value;
		return 0;
	}

	function append($var,$value) {
		$value=str_replace('{','[[[curlyl]]]',$value);
		$value=str_replace('}','[[[curlyr]]]',$value);
		
		// declares the var if not declared
		if(!isset($this->vars[$var])) $this->vars[$var]='';
		
		$this->vars[$var].=$value;
		return 0;
	}

	function get($var) {
		return $this->vars[$var];
	}
	
	function getOutput () {
		return $this->output;
	}
	
	function getSize () {
		return strlen($this -> output);
	}
	
	function print_size () {
		$size = $this -> getSize ();
		$size=$size/1024;
		$size=round($size,2);
		$size .= ' kb';
		return $size;
	}
	
	function reset_vars() {
		$this->output='';
		$this->vars=array();
		$this->filename='';
		$this->string='';
		return 0;
	}
	
	function parse_file($filename) {
		if(empty($filename)) {
			error_msg(__FILE__,__LINE__,'template file variable is empty');
			return ERR_NO_TEMPLATE_SET;
		}
		if(!is_array($this->vars)) $this->vars=array();
		
		if(!$fd = fopen($filename, "r")) {
			return ERR_CANNOT_OPEN_TEMPLATE_FILE;
		}
		$template = fread ($fd, filesize ($filename));
		fclose ($fd);
		unset ($fd);
		$template = stripslashes($template);
		for (reset ($this->vars); list ($key, $value) = each ($this->vars); ) {
			//$pattern="{[^}]*".$key."[^}]*}";
			//$template = eregi_replace("$pattern", "$value", $template);
			
			$template=preg_replace("/\{.*?".$key.".*?\}/",$value,$template);
		}
		
		$this->output=$template;
		return 0;
	}
	
	function parse_string($string) {
		if(empty($string)) {
			error_msg(__FILE__,__LINE__,'template string variable is empty');
			return ERR_NO_TEMPLATE_SET;
		}
		if(!is_array($this->vars)) $this->vars=array();
		
		$template = stripslashes($string);
		for (reset ($this->vars); list ($key, $value) = each ($this->vars); ) {
			//$pattern="{[^}]*".$key."[^}]*}";
			//$template = eregi_replace("$pattern", "$value", $template);
			
			$template=preg_replace("/\{.*?".$key.".*?\}/",$value,$template);
		}

		$this->output=$template;
		return 0;
	}
	
	function parse() {
		if(empty($this->filename) && empty($this->string)) {
			error_msg(__FILE__,__LINE__,'template file and string variables are empty');
			return ERR_NO_TEMPLATE_SET;
		}
		
		if(!empty($this->filename)) $err=$this->parse_file($this->filename);
		elseif(!empty($this->string)) $err=$this->parse_string($this->string);
		
		return $err;
	}
	
	function clean() {
		if (CONF_DEBUG_PRINT_MARKUP) {
			$this->output=str_replace('[[[curlyl]]]','{',$this->output);
			$this->output=str_replace('[[[curlyr]]]','}',$this->output);
			return 0;
		}
		
		$pattern="{[^}]*}";
		$this->output = eregi_replace($pattern, "", $this->output);
		
		$this->output=str_replace('[[[curlyl]]]','{',$this->output);
		$this->output=str_replace('[[[curlyr]]]','}',$this->output);
		return 0;
	}
	
	function restore_curly () {
		$this->output=str_replace('[[[curlyl]]]','{',$this->output);
		$this->output=str_replace('[[[curlyr]]]','}',$this->output);
		return 0;
	}
	
	function list_vars () {
		$output .= '<hr/>'."\n";
		$output .= '<b>Template active vars</b> <br/>'."\n";
		foreach ($this -> vars as $var => $value) 
			$output .= '<b>{'.$var.'}</b> -> '.nl2br(htmlentities($value)).'<br/>'."\n";
		$output .= '<hr/>'."\n";
		echo $output;
	}
}

?>