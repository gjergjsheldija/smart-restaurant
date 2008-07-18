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


class xml_parser {
	function GetChildren($vals, &$i) 
	{ 
	$children = array();     // Contains node data
	
	/* Node has CDATA before it's children */
	if (isset($vals[$i]['value'])) 
	$children['VALUE'] = $vals[$i]['value']; 
	
	/* Loop through children */
	while (++$i < count($vals))
	{ 
	switch ($vals[$i]['type']) 
	{ 
	/* Node has CDATA after one of it's children 
		(Add to cdata found before if this is the case) */
	case 'cdata': 
		if (isset($children['VALUE']))
		$children['VALUE'] .= $vals[$i]['value']; 
		else
		$children['VALUE'] = $vals[$i]['value']; 
		break;
	/* At end of current branch */ 
	case 'complete': 
		if (isset($vals[$i]['attributes'])) {
		$children[$vals[$i]['tag']][]['ATTRIBUTES'] = $vals[$i]['attributes'];
		$index = count($children[$vals[$i]['tag']])-1;
	
		if (isset($vals[$i]['value'])) 
		$children[$vals[$i]['tag']][$index]['VALUE'] = $vals[$i]['value']; 
		else
		$children[$vals[$i]['tag']][$index]['VALUE'] = ''; 
		} else {
		if (isset($vals[$i]['value'])) 
		$children[$vals[$i]['tag']][]['VALUE'] = $vals[$i]['value']; 
		else
		$children[$vals[$i]['tag']][]['VALUE'] = ''; 
			}
		break; 
	/* Node has more children */
	case 'open': 
		if (isset($vals[$i]['attributes'])) {
		$children[$vals[$i]['tag']][]['ATTRIBUTES'] = $vals[$i]['attributes'];
		$index = count($children[$vals[$i]['tag']])-1;
		$children[$vals[$i]['tag']][$index] = array_merge($children[$vals[$i]['tag']][$index],$this -> GetChildren($vals, $i));
		} else {
		$children[$vals[$i]['tag']][] = $this -> GetChildren($vals, $i);
		}
		break; 
	/* End of node, return collected data */
	case 'close': 
		return $children; 
	} 
	} 
	} 
	
	/* Function will attempt to open the xmlloc as a local file, on fail it will attempt to open it as a web link */
	function xml_to_tree($data,$case_folding=0) 
	{
	
	$parser = xml_parser_create('iso-8859-1');

	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, $case_folding); 
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1); 
	xml_parse_into_struct($parser, $data, $vals, $index); 
	xml_parser_free($parser); 
	
	$tree = array(); 
	$i = 0; 
	
	if (isset($vals[$i]['attributes'])) {
		$tree[$vals[$i]['tag']][]['ATTRIBUTES'] = $vals[$i]['attributes']; 
		$index = count($tree[$vals[$i]['tag']])-1;
		$tree[$vals[$i]['tag']][$index] =  array_merge($tree[$vals[$i]['tag']][$index], $this ->GetChildren($vals, $i));
	}
	else
	$tree[$vals[$i]['tag']][] = $this -> GetChildren($vals, $i); 
	
	return $tree; 
	} 
	
	function even_remover ($arr) {
		$out=array();
		
		for (reset ($arr); list ($key, $value) = each ($arr); ) {
			if (is_array($value)) {
				$GLOBALS['depth']++;
				
				$even = $GLOBALS['depth'] % 2;
				if($even)
					$out[$key] = $this -> even_remover ($arr[$key]);
				else
					$out = $this -> even_remover ($arr[$key]);
				
				$GLOBALS['depth']--;
			} else {
				$out = $arr[$key];
			}
		}
		return $out;
	}
}

?>