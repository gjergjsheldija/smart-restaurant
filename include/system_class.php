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

class system {
	function reset_all_menu_data () {
		$query = "TRUNCATE TABLE `#prefix#categories`";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
		
		if($err=$this->reset_associated_langs ('categories')) return $err;
		
		$query = "TRUNCATE TABLE `#prefix#dishes`";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
		
		if($err=$this->reset_associated_langs ('dishes')) return $err;
		
		$query = "TRUNCATE TABLE `#prefix#ingreds`";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
		
		if($err=$this->reset_associated_langs ('ingreds')) return $err;
		
		$query = "TRUNCATE TABLE `#prefix#orders`";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
		
		$query = "TRUNCATE TABLE `#prefix#sources`";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
		
		return 0;
	}
	
	function reset_all_menu_form () {
		
	}
	
	function reset_associated_langs ($table) {
		$langs=list_db_languages();
		foreach ($langs as $key => $value) {
			$query = "TRUNCATE TABLE `#prefix#".$table."_".$value."`";
			$res=common_query($query,__FILE__,__LINE__);
			if(!$res) return ERR_MYSQL;
		}
		return 0;
	}

	function info () {
		$output = '<body onLoad="javascript:type_text();">';
	
		$output .= $this->print_version();
		$output .= "<hr>\n";
		$output .= $this->thanks ();
		$output .= $this->short_license();
		$output .= "<hr>\n";
		$output .= $this->license(true);
		
		return $output;
	}
	
	function thanks () {
		$output = <<<EOT
	<form> <textarea rows=20 cols=100></textarea></form>
EOT;
	return $output;
	}

	function getVersion () {
		$query="SELECT * FROM `#prefix#system` WHERE `name`='version'";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return 0;
		if(mysql_num_rows($res)) {
			$arr=mysql_fetch_array($res);
			return $arr['value'];
		}
		return 0;
	}
	
	function upgradeCompleted ($updateNumber) {
		$last_ok = db_manager::upgrade_get_last_ok();
		if($last_ok >= $updateNumber) return true;
		return false;
	}
	
	function print_version () {
		$output = '</center>';
		
		if($version_now = $this -> getVersion ()) {
			$output .= ucphr('VERSION_INSTALLED').': <b>'.$version_now.'</b>'."<br/>\n";
		} else return '';
		
		$output .= ucphr('PHP_VERSION').': <b>'.phpversion().'</b>'."<br/>\n";
		$output .= ucphr('WEBSERVER_VERSION').': <b>'.$_SERVER['SERVER_SOFTWARE'].'</b>'."<br/>\n";
		$output .= ucphr('MYSQL_VERSION').': <b>'.mysql_get_server_info().'</b>'."<br/>\n";
		$output .= '<center>';
		return $output;
	}
	
	function print_last_upgrade () {
		$output = '';
		$last_ok=db_manager::upgrade_get_last_ok();
		if($last_ok) $output .= ucphr('LAST_UPGRADE_DONE').': <b>'.$last_ok.'</b>'."<br/>\n";
		return $output;
	}
	
	function short_license () {
		$output = '<dd>Powered by <a href="http://smartres.sourceforge.net/">Smart Restaurant</a></dd>';
		$output =nl2br($output);
		return $output;
	}
	
	function license () {
		$output = '';
		$license = '';
		$file=LICENSE_FILE;
		
		$fp = fopen ($file, 'r');
		while (! feof ($fp)){									// While the file lasts,
			$license .= fgets ($fp, 1024*1024);					// read it line by line.
		}
		fclose($fp);
		
		$output .= '
		<form>
		<textarea cols="80" rows="20">
		'.$license.'
		</textarea>
		</form>';
		
		return $output; 
	}
	
	function changelog($file,$textarea=false) {
		$output = '';
		
		$fp = fopen ($file, 'r');								// Open the dump file.
		if($textarea) $output .= '<a href="?">Preview</a><br/><br/>'."\n";
		if($textarea) {
			$output .= '
			<form>
			<textarea cols="120" rows="20">
	';
		}
		$output .= '<b>'."\n";
		$open=false;
		$li=false;
		//$output.='<ul>';
		while (! feof ($fp)){									// While the file lasts,
			$line = fgets ($fp, 1024*1024);					// read it line by line.
			$line = trim($line);
			if($line!='' && $open && !ereg("^-(.*)", $line)) {
				$output.='</ul>'."\n".'<b>'."\n";
				$open=false;
				$li=false;
			}
	
			if(ereg("^-(.*)", $line)) {
				if(!$li && !$open) {
					$output.='</b>'."\n".'<ul>'."\n";
					$open=true;
				}
				$li=true;
				$tmp = ereg_replace ("^-(.*)", "\\1", $line);
				$tmp = htmlentities($tmp);
				$tmp = '<li>'.$tmp.'</li>';
				$output .= $tmp."\n";
			} else {
				$output .= $line.'<br/>'."\n";
			}
		}
		fclose($fp);
		if($open) {
			$output.='</ul>'."\n";
			$open=false;
		}
		if($textarea)
			$output .= '</textarea>
	</form>'."\n";
		return $output;
	}
}
?>