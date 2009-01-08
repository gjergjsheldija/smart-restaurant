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

class menu {
	var $output;
	var $links;
	
	var $letters;
	var $names;
	
	function main () {
		$cache = new cache ();
		if($cache_out=$cache -> lang_get ($_SESSION['language'],'menumenumenu')) return $cache_out;
		
		$this -> links = array();
		
		$this -> output = '
		<script type="text/javascript" language="JavaScript1.2" src="'.ROOTDIR.'/coolmenus4.js">
		</script>
		<script type="text/javascript" language="JavaScript1.2">
			oM=new makeCM("oM"); oM.resizeCheck=1; oM.rows=1;  oM.onlineRoot=""; oM.pxBetween =0; 
			oM.fillImg="'.ROOTDIR.'/images/cm_fill.gif"; oM.fromTop=0; oM.fromLeft=0; oM.wait=600; oM.zIndex=400;
			oM.useBar=1; 
			oM.barWidth="100%"; 
			oM.barHeight="menu"; 
			oM.barX=0;
			oM.barY=0; 
			oM.barClass="clBar";
			oM.barBorderClass="";
			oM.barBorderX=0; 
			oM.barBorderY=0;';
			
		$colwidth = 150;
		$rightdist =50;
			
		$this -> output .= '
			oM.level[0]=new cm_makeLevel('.$colwidth.',18,"clT","clTover",1,1,"clB",0,"bottom",0,0,0,0,0);
			oM.level[1]=new cm_makeLevel('.$colwidth.',18,"clS","clSover",1,1,"clB",0,"right",0,0,"'.ROOTDIR.'/images/menu_arrow.gif",10,10);
			oM.level[2]=new cm_makeLevel('.$colwidth.',18,"clS2","clS2over");
			oM.level[3]=new cm_makeLevel('.($colwidth-10).',17);'."\n\n";
	
		
		$index = 0;
		
		$index = $this -> menu_menu($index+1,'');
		$index = $this -> system($index+1,'');
		$index = $this -> accounting($index+1,'');
		$index = $this -> contacts($index+1,'');
		$index = $this -> reports($index+1,'');
		$index = $this -> stock($index+1,'');
		$index = $this -> logging($index+1,'');
		
		$menu_spacing = 'page';
		switch($menu_spacing) {
			case 'left':
				$coeff=10;
				$absx[0]=0;
				$sum=0;
				$i=1;
				ksort($this->letters);
				foreach($this->letters as $value) {
					$sum=$sum+$value*$coeff+20;
					$absx[$i]=$sum;
					$i++;
				}
				$i=0;
				foreach ($absx as $value) {
					if($i) $arr_str.=',';
					$arr_str.=$value;
					$i++;
				}
				$menu_placement = '
		oM.menuPlacement=new Array('.$arr_str.');';
				break;
			case 'page':
				$menu_placement = '
		var avail="0+((cmpage.x2-'.$rightdist.')/7)";
		oM.menuPlacement=new Array(0,avail,avail+"*2",avail+"*3",avail+"*4",avail+"*5",avail+"*6");';
				break;
			default:
				$menu_placement = '
		oM.menuPlacement=new Array();';
				break;
		}
		
		$this -> output .= $menu_placement;
		$this -> output .= '
		oM.construct();
		</script>';
		
		foreach ($this -> links as $i => $elem) $tmp[$i] = '<a class="invisible" href="'.$elem['link'].'">'.$elem['name'].'</a>';
		$this -> output .= '
		<table>
		<tr><td height="20">&nbsp;</td></tr>
		</table>';
		
		$cache -> lang_set ($_SESSION['language'],'menumenumenu',$this -> output);
		return $this -> output;
	}
	
	function system($start_idx,$parent) {

		$i=$start_idx;
		
		$this->letters[$i]=strlen(ucphr('SYSTEM'));
		
		$this -> output.="\t\toM.makeMenu('m$i','$parent','".ucphr('SYSTEM')."','');\n";
		$i++;
		
		$this -> output.="\t\toM.makeMenu('m".$i."','m".$start_idx."','".ucphr('USERS')."','".ROOTDIR."/admin/admin.php?class=user&command=none');\n";
		$this -> links[$i]['name']=ucphr('SYSTEM').': '.ucphr('USERS');
		$this -> links[$i]['link']=ROOTDIR.'/admin/admin.php?class=user&command=none';
		$i++;
		$this -> output.="\t\toM.makeMenu('m".$i."','m".$start_idx."','".ucphr('CONFIGURATION')."','".ROOTDIR."/conf/index.php');\n";
		$this -> links[$i]['name']=ucphr('SYSTEM').': '.ucphr('CONFIGURATION');
		$this -> links[$i]['link']=ROOTDIR.'/conf/index.php';
		$i++;
		$this -> output.="\t\toM.makeMenu('m".$i."','m".$start_idx."','".ucphr('PRINTERS')."','".ROOTDIR."/admin/admin.php?class=printer&command=none');\n";
		$this -> links[$i]['name']=ucphr('SYSTEM').': '.ucphr('PRINTERS');
		$this -> links[$i]['link']=ROOTDIR.'/admin/admin.php?class=printer&command=none';
		$i++;
		$this -> output.="\t\toM.makeMenu('m".$i."','m".$start_idx."','".ucphr('UPGRADE')."','".ROOTDIR."/admin/upgrade.php?command=none');\n";
		$this -> links[$i]['name']=ucphr('SYSTEM').': '.ucphr('UPGRADE');
		$this -> links[$i]['link']=ROOTDIR.'/admin/upgrade.php?command=none';
		$i++;
		
		return $i;
	}
	
	function logging($start_idx,$parent) {
		$i=$start_idx;
		
		global $connect_page;
		
		if($_SESSION['userid']) {
			$notpwd=false;
			$user = new user ($_SESSION['userid']);
			if(!isset($_SESSION['passworded']) || !$_SESSION['passworded']) $notpwd=true;
			
			if ($notpwd) $label = ucphr('LOGOUT').' ('.$user->data['name'].' *)';
			else $label = ucphr('LOGOUT').' ('.$user->data['name'].')';
			
			$this -> output .= "\t\toM.makeMenu('m$i','$parent','".$label."','".ROOTDIR."/admin/connect.php?command=disconnect');\n";
			$this -> links[$i]['name']=$label;
			$this -> links[$i]['link']=ROOTDIR.'/admin/connect.php?command=disconnect';
		} else {
			if($connect_page && !$_REQUEST['command']=='connect') $link='javascript:document.connect_form.submit()';
			else $link = ROOTDIR.'/admin/connect.php?command=none';
			$this -> output .= "\t\toM.makeMenu('m$i','$parent','".ucphr('LOGIN')."','$link');\n";
			$this -> links[$i]['name']=ucphr('LOGIN');
			$this -> links[$i]['link']=$link;
		}
		$i++;
	
		if($notpwd) {
			$this -> output.="\t\toM.makeMenu('m".$i."','m".$start_idx."','".ucphr('RECONNECT_WITH_PASSWORD')."','".ROOTDIR."/admin/connect.php?command=disconnect');\n";
			$this -> links[$i]['name']=ucphr('RECONNECT_WITH_PASSWORD');
			$this -> links[$i]['link']=ROOTDIR.'/admin/connect.php?command=disconnect';
			$i++;
		}
		
		return $i;
	}
	
	function menu_menu($start_idx,$parent) {
		$i=$start_idx;

		$this->letters[$i]=strlen(ucphr('MENU'));
		
		$this -> output.="\t\toM.makeMenu('m$i','$parent','".ucphr('MENU')."','');\n";
		$i++;
		
		
		$this -> output.="\t\toM.makeMenu('m".$i."','m".$start_idx."','".ucphr('INGREDIENTS')."','".ROOTDIR."/admin/admin.php?class=ingredient&command=none');\n";
		$this -> links[$i]['name']=ucphr('MENU').': '.ucphr('INGREDIENTS');
		$this -> links[$i]['link']=ROOTDIR.'/admin/admin.php?class=ingredient&command=none';
		$i++;
		
		$i = $this -> ingredients_categories($i,'m'.($i-1));
	
		$this -> output.="\t\toM.makeMenu('m".$i."','m".$start_idx."','".ucphr('CATEGORIES')."','".ROOTDIR."/admin/admin.php?class=category&command=none');\n";
		$this -> links[$i]['name']=ucphr('MENU').': '.ucphr('CATEGORIES');
		$this -> links[$i]['link']=ROOTDIR.'/admin/admin.php?class=category&command=none';
		$i++;
		$this -> output.="\t\toM.makeMenu('m".$i."','m".$start_idx."','".ucphr('TABLES')."','".ROOTDIR."/admin/admin.php?class=table&command=none');\n";
		$this -> links[$i]['name']=ucphr('MENU').': '.ucphr('TABLES');
		$this -> links[$i]['link']=ROOTDIR.'/admin/admin.php?class=table&command=none';
		$i++;
		$this -> output.="\t\toM.makeMenu('m".$i."','m".$start_idx."','".ucphr('DISHES')."','".ROOTDIR."/admin/admin.php?class=dish&command=none');\n";
		$this -> links[$i]['name']=ucphr('MENU').': '.ucphr('DISHES');
		$this -> links[$i]['link']=ROOTDIR.'/admin/admin.php?class=dish&command=none';
		$i++;
		
		$i = $this -> dishes_categories($i,'m'.($i-1));
	
		$this -> output.="\t\toM.makeMenu('m".$i."','m".$start_idx."','".ucphr('VAT_RATES')."','".ROOTDIR."/admin/admin.php?class=vat_rate&command=none');\n";
		$this -> links[$i]['name']=ucphr('MENU').': '.ucphr('VAT_RATES');
		$this -> links[$i]['link']=ROOTDIR.'/admin/admin.php?class=vat_rate&command=none';
		$i++;		
		$this -> output.="\t\toM.makeMenu('m".$i."','m".$start_idx."','".ucphr('VALUTA')."','".ROOTDIR."/admin/admin.php?class=currencies&command=none');\n";
		$this -> links[$i]['name']=ucphr('MENU').': '.ucphr('VALUTA');
		$this -> links[$i]['link']=ROOTDIR.'/admin/admin.php?class=currencies&command=none';
		$i++;
		$this -> output.="\t\toM.makeMenu('m".$i."','m".$start_idx."','".ucphr('AUTOCALC')."','".ROOTDIR."/admin/admin.php?class=autocalc&command=none');\n";
		$this -> links[$i]['name']=ucphr('MENU').': '.ucphr('AUTOCALC');
		$this -> links[$i]['link']=ROOTDIR.'/admin/admin.php?class=autocalc&command=none';
		$i++;
		
		return $i;
	}
	
	function dishes_categories($start_idx,$parent) {
		$i=$start_idx;

		$language = common_get_language();
		
		$main_table = '#prefix#categories';
		$lang_table = $main_table.'_'.$language;
		$query = "SELECT $main_table.id FROM `$main_table`";
		$query .= " WHERE `deleted`='0'";
		$query .= " ORDER BY `name` ASC";
		
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return 0;
	
		$cat->id=0;
		$this -> output.="\t\toM.makeMenu('m$i','$parent','".ucphr('ALL')."','".ROOTDIR."/admin/admin.php?class=dish&data[category]=0');\n";
		$i++;
		
		$cat=new category;
		while($arr=mysql_fetch_array($res)){
			$cat->id=$arr['id'];
			$this -> output.="\t\toM.makeMenu('m$i','$parent','".ucfirst($cat->name($language))."','".ROOTDIR."/admin/admin.php?class=dish&data[category]=".$arr['id']."');\n";
			$i++;
		}
	
		return $i;
	}
	
	function ingredients_categories($start_idx,$parent) {
		$i=$start_idx;
		
		$language = common_get_language();
		
		$main_table = '#prefix#categories';

		$query = "SELECT $main_table.id FROM `$main_table`";
		$query .= " WHERE  `deleted`='0'";
		$query .= " ORDER BY `name` ASC";
		
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return 0;
	
		$cat->id=0;
		$this -> output.="\t\toM.makeMenu('m$i','$parent','".ucphr('ALL')."','".ROOTDIR."/admin/admin.php?class=ingredient&data[category]=0');\n";
		$i++;
		
		$cat=new category;
		while($arr=mysql_fetch_array($res)){
			$cat->id=$arr['id'];
			$this -> output.="\t\toM.makeMenu('m$i','$parent','".ucfirst($cat->name($language))."','".ROOTDIR."/admin/admin.php?class=ingredient&data[category]=".$arr['id']."');\n";
			$i++;
		}
	
		return $i;
	}
	
	function accounting($start_idx,$parent) {
		$i=$start_idx;

		$this->letters[$i]=strlen(ucphr('ACCOUNTING'));
		
		$this -> output .= "\t\toM.makeMenu('m".$i."','','".ucphr('ACCOUNTING')."','');\n";
		$i++;
		$this -> output.="\t\toM.makeMenu('m".$i."','m".$start_idx."','".ucphr('INCOME_EXPAND')."','".ROOTDIR."/manage/db.php?command=show_all');\n";
		$this -> links[$i]['name']=ucphr('ACCOUNTING').': '.ucphr('INCOME_EXPAND');
		$this -> links[$i]['link']=ROOTDIR.'/manage/db.php?command=show_all';
		$i++;
		$this -> output.="\t\toM.makeMenu('m".$i."','m".$start_idx."','".ucphr('INCOME_COLLAPSE')."','".ROOTDIR."/manage/db.php?command=income_collapse');\n";
		$this -> links[$i]['name']=ucphr('ACCOUNTING').': '.ucphr('INCOME_COLLAPSE');
		$this -> links[$i]['link']=ROOTDIR.'/manage/db.php?command=income_collapse';
		
		$i = $this -> insert_type_new($i+1,'m'.$start_idx);
		$i++;
		
		$i = $this -> accounts($i,'m'.$start_idx);
	
		return $i;
	
	}
	
	function contacts($start_idx,$parent) {
		$i=$start_idx;
		
		$this->letters[$i]=strlen(ucphr('CONTACTS'));
		
		$this -> output .= "\t\toM.makeMenu('m".$i."','','".ucphr('CONTACTS')."','');\n";
		$i++;
		$this -> output.= "\t\toM.makeMenu('m".$i."','m".($i-1)."','".ucphr('CONTACTS_LIST')."','".ROOTDIR."/manage/supply.php?command=list');\n";
		$this -> links[$i]['name']=ucphr('CONTACTS').': '.ucphr('CONTACTS_LIST');
		$this -> links[$i]['link']=ROOTDIR.'/manage/supply.php?command=list';
		
		$i = $this -> insert_supplier($i+1,'m'.($i-1));
		return $i;
	
	}
	
	function insert_supplier($start_idx,$parent) {

		$language = common_get_language();
		
		$query="SELECT * FROM `#prefix#mgmt_people_types` ORDER BY `name`";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return 0;
		
		$this -> output .= "\t\toM.makeMenu('m$start_idx','$parent','".ucphr('CONTACT_INSERT')."','');\n";
		$i=$start_idx;
	
		while($row=mysql_fetch_array ($res)) {
			$i++;
			$people_type = new mgmt_people_type($row['id']);
			$type_name=$people_type -> name($language);
			unset($people_type);
	
			$this -> output .= "\t\toM.makeMenu('m".$i."','m".$start_idx."','".$type_name."','".ROOTDIR."/manage/supply.php?command=new&insert_type=".$row['id']."');\n";
			$this -> links[$i]['name']=ucphr('CONTACT_INSERT').': '.$type_name;
			$this -> links[$i]['link']=ROOTDIR.'/manage/supply.php?command=new&insert_type="'.$row['id'];
		}
		
		return $i;
	}
	
	function insert_type_new($start_idx,$parent) {

		$language = common_get_language();
		
		$query = "SELECT * FROM `#prefix#mgmt_types` WHERE `account_only`=0 ORDER BY `name`";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return 0;
	
		$this -> output .= "\t\toM.makeMenu('m".$start_idx."','$parent','".ucfirst(GLOBALMSG_RECORD_INSERT)."','');\n";
		$i=$start_idx;
		while($row=mysql_fetch_array ($res)) {
			$i++;
			$mgmt_type = new mgmt_type($row['id']);
			$type_name=$mgmt_type -> name($language);
			unset($mgmt_type);
	
			$this -> output .= "\t\toM.makeMenu('m".$i."','m".$start_idx."','".$type_name."','".ROOTDIR."/manage/db.php?command=new&insert_type=".$row['id']."');\n";
			$this -> links[$i]['name']=ucfirst(GLOBALMSG_RECORD_INSERT).': '.$type_name;
			$this -> links[$i]['link']=ROOTDIR.'/manage/db.php?command=new&insert_type="'.$row['id'];
		}
		
		return $i;
	}
	
	function accounts($start_idx,$parent) {

		$i=$start_idx;
		
		$this -> output .= "\t\toM.makeMenu('m".$i."','$parent','".ucphr('ACCOUNT_MAIN_LEGEND')."','');\n";
		$i++;
		
		
		$this -> output .= "\t\toM.makeMenu('m".$i."','m".$start_idx."','".ucphr('ACCOUNT_INSERT')."','".ROOTDIR."/manage/account.php?command=new');\n";
		$this -> links[$i]['name']=ucphr('ACCOUNT_MAIN_LEGEND').': '.ucphr('ACCOUNT_INSERT');
		$this -> links[$i]['link']=ROOTDIR.'/manage/account.php?command=new';
		$i++;
		$this -> output .= "\t\toM.makeMenu('m".$i."','m".$start_idx."','".ucphr('ACCOUNT_LIST')."','".ROOTDIR."/manage/account.php?command=list');\n";
		$this -> links[$i]['name']=ucphr('ACCOUNT_MAIN_LEGEND').': '.ucphr('ACCOUNT_LIST');
		$this -> links[$i]['link']=ROOTDIR.'/manage/account.php?command=list';
		$i++;
		$this -> output .= "\t\toM.makeMenu('m".$i."','m".$start_idx."','".ucphr('ACCOUNT_MOVEMENT_INSERT')."','".ROOTDIR."/manage/account.php?command=movement_new');\n";
		$this -> links[$i]['name']=ucphr('ACCOUNT_MAIN_LEGEND').': '.ucphr('ACCOUNT_MOVEMENT_INSERT');
		$this -> links[$i]['link']=ROOTDIR.'/manage/account.php?command=movement_new';
		$i++;
		$this -> output .= "\t\toM.makeMenu('m".$i."','m".$start_idx."','".ucphr('ACCOUNT_MOVEMENT_LIST')."','');\n";
		$i++;
		
		$i = $this -> accounts_movements($i,'m'.($i-1));
	
		return $i;
	}
	
	function accounts_movements($start_idx,$parent) {
		
		$i = $start_idx;
		
		$table = '#prefix#account_accounts';
		$query = "SELECT * FROM `$table`";
		$res2 = common_query ( $query, __FILE__, __LINE__ );
		if (! $res2)
			return 0;
		
		while ( $arr = mysql_fetch_array ( $res2 ) ) {
			$this->output .= "\t\toM.makeMenu('m$i','$parent','" . ucfirst ( $arr ['name'] ) . "','" . ROOTDIR . "/manage/account.php?command=movement_list&id=" . $arr ['id'] . "');\n";
			$this->links [$i] ['name'] = ucphr ( 'ACCOUNT_MAIN_LEGEND' ) . ': ' . ucphr ( 'ACCOUNT_MOVEMENT_LIST' ) . ':' . ucfirst ( $arr ['name'] );
			$this->links [$i] ['link'] = ROOTDIR . '/manage/account.php?command=movement_list&id=' . $arr ['id'];
			$i ++;
		}
		
		return $i;
	}
	
	function reports($start_idx,$parent) {

		$i=$start_idx;
		
		$this->letters[$i]=strlen(ucphr('REPORTS'));
		
		$this -> output.="\t\toM.makeMenu('m$start_idx','$parent','".ucphr('REPORTS')."','');\n";
		$i++;
		
		
		$this -> output.="\t\toM.makeMenu('m".$i."','m".$start_idx."','".ucphr('VAT_CALCULATION')."','".ROOTDIR."/manage/vat.php?command=none');\n";
		$this -> links[$i]['name']=ucphr('REPORTS').': '.ucphr('VAT_CALCULATION');
		$this -> links[$i]['link']=ROOTDIR.'/manage/vat.php?command=none';
		$i++;
		$this -> output.="\t\toM.makeMenu('m".$i."','m".$start_idx."','".ucphr('STATISTICS')."','".ROOTDIR."/manage/manage.php?command=none');\n";
		$this -> links[$i]['name']=ucphr('REPORTS').': '.ucphr('STATISTICS');
		$this -> links[$i]['link']=ROOTDIR.'/manage/manage.php?command=none';
		$i++;
		
		return $i;
	}
	
	function stock($start_idx,$parent) {
		
		$i=$start_idx;
		$this->letters[$i]=strlen(ucphr('STOCK'));
		
		$this -> output.="\t\toM.makeMenu('m$start_idx','$parent','".ucphr('STOCK')."','');\n";
		$i++;
		
		
		$this -> output.="\t\toM.makeMenu('m".$i."','m".$start_idx."','".ucphr('STOCK_SITUATION')."','".ROOTDIR."/stock/index.php?class=stock_object');\n";
		$this -> links[$i]['name']=ucphr('STOCK').': '.ucphr('STOCK_SITUATION');
		$this -> links[$i]['link']=ROOTDIR.'/stock/index.php?class=stock_object';
		$i++;
		
		$this -> output.="\t\toM.makeMenu('m".$i."','m".$start_idx."','".ucphr('STOCK_MOVEMENTS')."','".ROOTDIR."/stock/index.php?class=stock_movement');\n";
		$this -> links[$i]['name']=ucphr('STOCK').': '.ucphr('STOCK_MOVEMENTS');
		$this -> links[$i]['link']=ROOTDIR.'/stock/index.php?class=stock_movement';
		$i++;
		
		$this -> output.="\t\toM.makeMenu('m".$i."','m".$start_idx."','".ucphr('STOCK_SYNC_ALL_DISHES')."','".ROOTDIR."/stock/index.php?class=stock_object&command=sync_dishes');\n";
		$this -> links[$i]['name']=ucphr('STOCK').': '.ucphr('STOCK_SYNC_ALL_DISHES');
		$this -> links[$i]['link']=ROOTDIR.'/stock/index.php?class=stock_object&command=sync_dishes';
		$i++;
		
		$this -> output.="\t\toM.makeMenu('m".$i."','m".$start_idx."','".ucphr('STOCK_SYNC_ALL_INGREDS')."','".ROOTDIR."/stock/index.php?class=stock_object&command=sync_ingredients');\n";
		$this -> links[$i]['name']=ucphr('STOCK').': '.ucphr('STOCK_SYNC_ALL_INGREDS');
		$this -> links[$i]['link']=ROOTDIR.'/stock/index.php?class=stock_object&command=sync_ingredients';
		$i++;
		$this -> output.="\t\toM.makeMenu('m".$i."','m".$start_idx."','".ucphr('STOCK_INSERT_INGREDS_QUANTITIES')."','".ROOTDIR."/stock/index.php?class=stock_dish&command=none');\n";
		$this -> links[$i]['name']=ucphr('STOCK').': '.ucphr('STOCK_INSERT_INGREDS_QUANTITIES');
		$this -> links[$i]['link']=ROOTDIR.'/stock/index.php?class=stock_dish&command=none';
		$i++;
		$this -> output.="\t\toM.makeMenu('m".$i."','m".$start_idx."','Inventari Aktual - Bari','".ROOTDIR."/raport/bari.php');\n";
		$this -> links[$i]['name']=ucphr('STOCK').': '."Inventali Aktual - Bari";
		$this -> links[$i]['link']=ROOTDIR.'/raport/bari.php';
		$i++;		
		$this -> output.="\t\toM.makeMenu('m".$i."','m".$start_idx."','Inventari Aktual - Guzhina','".ROOTDIR."/raport/guzhina.php');\n";
		$this -> links[$i]['name']=ucphr('STOCK').': '."Inventali Aktual - Guzhina";
		$this -> links[$i]['link']=ROOTDIR.'/raport/guzhina.php';
		$i++;	
		return $i;
	}
	
}
?>