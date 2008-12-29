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

class user extends object {
	var $level;

	function user($id=0) {
		$this -> db = 'common';
		$this->table=$GLOBALS['table_prefix'].'users';
		$this->id=$id;
		$this -> title = ucphr('USERS');
		$this->file=ROOTDIR.'/admin/admin.php';

		$this->flag_delete = true;
		//$this->fields_boolean=array('disabled');
		$this->fields_show=array('id','name','template','password','disabled','level','dest_type');
		$this->fields_names=array(	'id'=>ucphr('ID'),
								'name'=>ucphr('NAME'),
								'template'=>ucphr('TEMPLATE'),
								'password'=>ucphr('PASSWORD'),
								'disabled'=>ucphr('DISABLED'),
								'dest_type'=>'Lloji');
		$this->fields_width=array(	'name'=>'100%');
		$this->allow_single_update = array ('level','disabled');
		$this -> fetch_data();
		$this -> get_level ();
	}

	function update_field ($field) {
		if(!$this->id) return ERR_NO_ORDER_CHOSEN;

		if(!isset($this->allow_single_update)) return ERR_NOT_ALLOWED_TO_CHANGE_FIELD;
		if(!in_array($field,$this->allow_single_update)) return ERR_NOT_ALLOWED_TO_CHANGE_FIELD;

		$this->fetch_data();
		$input_data=$this->data;
		$new_value = $this->data[$field] ? 0 : 1;

		if($field=='level') {
			$this -> get_level ();
			$lev = $this -> level;
			$idx=$_REQUEST['data']['subfield'];
			$lev[$idx] = $lev[$idx] ? 0 : 1;

			krsort($lev);
			foreach($lev as $val) $levinv .= $val;

			$levinv = bindec($levinv);
			$new_value = $levinv;
		}

		if($err = $this->set ($field,$new_value)) return $err;
		return 0;
	}

	function list_search ($search) {
		$query = '';

		$table = $this->table;
		$lang_table = $table."_".$_SESSION['language'];

		$query="SELECT
		$table.`id`,
		$table.`name`,
		RPAD('".ucphr('USERS')."',30,' ') as `table`,
				".TABLE_USERS." as `table_id`
		FROM `$table`
		WHERE $table.`deleted`='0'
		AND $table.`name` LIKE '%$search%' ";

		return $query;
	}

	function list_query_all () {
		$table = $this->table;

		$query="SELECT $table.`id`,
		$table.`name`,
		$table.`language`,
		$table.`template`,
		IF($table.`password`<>'','".ucphr('YES')."','".ucphr('NO')."') as `password`,
		IF($table.`disabled`<>0,'".ucphr('YES')."','".ucphr('NO')."') as `disabled`,
		$table.`level`,
		$table.`dest_type`
		FROM `$table`
		WHERE $table.`deleted`='0' ";

		return $query;
	}

	function list_rows ($arr,$row) {
		global $tpl;
		global $display;

		$col=0;
		if(!$this->disable_mass_delete) {
			$display->rows[$row][$col]='<input type="checkbox" name="delete[]" value="'.$arr['id'].'">';
			$display->width[$row][$col]='1%';
			$col++;
		}
		foreach ($arr as $field => $value) {
			if(isset($this->fields_boolean) && in_array($field,$this->fields_boolean)) {
				if($value) $value=ucfirst(phr('YES'));
				else $value=ucfirst(phr('NO'));
			}

			if (isset($this->allow_single_update) && in_array($field,$this->allow_single_update)) {
				$link_update_field = $this->link_base.'&amp;command=update_field&amp;data[id]='.$arr['id'].'&amp;data[field]='.$field;
				if($this->limit_start) $link_update_field .= '&amp;data[limit_start]='.$this->limit_start;
				if($this->orderby) $link_update_field.='&amp;data[orderby]='.$this->orderby;
				if($this->sort) $link_update_field.='&amp;data[sort]='.$this->sort;

				$display->links[$row][$col]=$link_update_field;
			} elseif (method_exists($this,'form')) $link = $this->file.'?class='.get_class($this).'&amp;command=edit&amp;data[id]='.$arr['id'];
			else $link='';

			$display->rows[$row][$col]=$value;
			if($link && $field=='name') $display->links[$row][$col]=$link;
			if($link) $display->clicks[$row][$col]='redir(\''.$link.'\');';

			if($field=='level') {
				$user = new user($arr['id']);
				$user -> get_level ();

				if($user->level[USER_BIT_WAITER]) $value = ucphr('YES');
				else $value = ucphr('NO');
				$display->rows[$row][$col] = $value;
				$link = $link_update_field.'&amp;data[subfield]='.USER_BIT_WAITER;
				if($link) $display->links[$row][$col] = $link;
				if($link) $display->clicks[$row][$col]='redir(\''.$link.'\');';
				$col++;

				if($user->level[USER_BIT_CASHIER]) $value = ucphr('YES');
				else $value = ucphr('NO');
				$display->rows[$row][$col] = $value;
				$link = $link_update_field.'&amp;data[subfield]='.USER_BIT_CASHIER;
				if($link) $display->links[$row][$col] = $link;
				if($link) $display->clicks[$row][$col]='redir(\''.$link.'\');';
				$col++;

				if($user->level[USER_BIT_STOCK]) $value = ucphr('YES');
				else $value = ucphr('NO');
				$display->rows[$row][$col] = $value;
				$link = $link_update_field.'&amp;data[subfield]='.USER_BIT_STOCK;
				if($link) $display->links[$row][$col] = $link;
				if($link) $display->clicks[$row][$col]='redir(\''.$link.'\');';
				$col++;

				if($user->level[USER_BIT_CONTACTS]) $value = ucphr('YES');
				else $value = ucphr('NO');
				$display->rows[$row][$col] = $value;
				$link = $link_update_field.'&amp;data[subfield]='.USER_BIT_CONTACTS;
				if($link) $display->links[$row][$col] = $link;
				if($link) $display->clicks[$row][$col]='redir(\''.$link.'\');';
				$col++;

				if($user->level[USER_BIT_MENU]) $value = ucphr('YES');
				else $value = ucphr('NO');
				$display->rows[$row][$col] = $value;
				$link = $link_update_field.'&amp;data[subfield]='.USER_BIT_MENU;
				if($link) $display->links[$row][$col] = $link;
				if($link) $display->clicks[$row][$col]='redir(\''.$link.'\');';
				$col++;

				if($user->level[USER_BIT_USERS]) $value = ucphr('YES');
				else $value = ucphr('NO');
				$display->rows[$row][$col] = $value;
				$link = $link_update_field.'&amp;data[subfield]='.USER_BIT_USERS;
				if($link) $display->links[$row][$col] = $link;
				if($link) $display->clicks[$row][$col]='redir(\''.$link.'\');';
				$col++;

				if($user->level[USER_BIT_ACCOUNTING]) $value = ucphr('YES');
				else $value = ucphr('NO');
				$display->rows[$row][$col] = $value;
				$link = $link_update_field.'&amp;data[subfield]='.USER_BIT_ACCOUNTING;
				if($link) $display->links[$row][$col] = $link;
				if($link) $display->clicks[$row][$col]='redir(\''.$link.'\');';
				$col++;

				if($user->level[USER_BIT_TRANSLATION]) $value = ucphr('YES');
				else $value = ucphr('NO');
				$display->rows[$row][$col] = $value;
				$link = $link_update_field.'&amp;data[subfield]='.USER_BIT_TRANSLATION;
				if($link) $display->links[$row][$col] = $link;
				if($link) $display->clicks[$row][$col]='redir(\''.$link.'\');';
				$col++;

				if($user->level[USER_BIT_CONFIG]) $value = ucphr('YES');
				else $value = ucphr('NO');
				$display->rows[$row][$col] = $value;
				$link = $link_update_field.'&amp;data[subfield]='.USER_BIT_CONFIG;
				if($link) $display->links[$row][$col] = $link;
				if($link) $display->clicks[$row][$col]='redir(\''.$link.'\');';
				$col++;

				if($user->level[USER_BIT_MONEY]) $value = ucphr('YES');
				else $value = ucphr('NO');
				$display->rows[$row][$col] = $value;
				$link = $link_update_field.'&amp;data[subfield]='.USER_BIT_MONEY;
				if($link) $display->links[$row][$col] = $link;
				if($link) $display->clicks[$row][$col]='redir(\''.$link.'\');';
			}

			$col++;
		}
	}

	function list_head ($arr) {
		global $tpl;
		global $display;

		$col=0;
		if(!$this->disable_mass_delete) {
			$display->rows[0][$col]='<input type="checkbox" name="all_checker" onclick="check_all(\''.$this->form_name.'\',\'delete[]\')">';
			$display->width[0][$col]='1%';
			$col++;
		}
		foreach ($arr as $field => $val) {
			if(isset($this->fields_names[$field])) $display->rows[0][$col]=$this->fields_names[$field];
			else $display->rows[0][$col]=$field;

			if($field==$this->orderby && strtolower($this->sort)=='asc') {
				$next_sort='desc';
				$display->rows[0][$col].= ' (+)';
			} else {
				$next_sort='asc';
				if($field==$this->orderby) $display->rows[0][$col].= ' (-)';
			}

			$link = $this->link_base.'&amp;data[orderby]='.$field.'&amp;data[sort]='.$next_sort;
			if($this->category) $link.='&amp;data[category]='.$this->category;
			if($this->search) $link.='&amp;data[search]='.$this->search;

			$display->links[0][$col]=$link;
			$display->clicks[0][$col]='redir(\''.$link.'\');';
			if(isset($this->fields_width[$field])) $display->widths[0][$col]=$this->fields_width[$field];

			if($field=='level') {
				$letters_per_label = 6;

				$display->rows[0][$col] = substr(ucphr('WAITER'),0,$letters_per_label);
				$display->links[0][$col] = '';
				$display->clicks[0][$col] = '';
				$col++;
				$display->rows[0][$col] = substr(ucphr('CASHIER'),0,$letters_per_label);
				$display->links[0][$col] = '';
				$display->clicks[0][$col] = '';
				$col++;
				$display->rows[0][$col] = substr(ucphr('STOCK'),0,$letters_per_label);
				$display->links[0][$col] = '';
				$display->clicks[0][$col] = '';
				$col++;
				$display->rows[0][$col] = substr(ucphr('CONTACTS'),0,$letters_per_label);
				$display->links[0][$col] = '';
				$display->clicks[0][$col] = '';
				$col++;
				$display->rows[0][$col] = substr(ucphr('MENU'),0,$letters_per_label);
				$display->links[0][$col] = '';
				$display->clicks[0][$col] = '';
				$col++;
				$display->rows[0][$col] = substr(ucphr('USERS'),0,$letters_per_label);
				$display->links[0][$col] = '';
				$display->clicks[0][$col] = '';
				$col++;
				$display->rows[0][$col] = substr(ucphr('ACCOUNTING'),0,$letters_per_label);
				$display->links[0][$col] = '';
				$display->clicks[0][$col] = '';
				$col++;
				$display->rows[0][$col] = substr(ucphr('TRANSLATION'),0,$letters_per_label);
				$display->links[0][$col] = '';
				$display->clicks[0][$col] = '';
				$col++;
				$display->rows[0][$col] = substr(ucphr('CONFIG'),0,$letters_per_label);
				$display->links[0][$col] = '';
				$display->clicks[0][$col] = '';
				$col++;
				$display->rows[0][$col] = substr(ucphr('MONEY'),0,$letters_per_label);
				$display->links[0][$col] = '';
				$display->clicks[0][$col] = '';
			}

			$col++;
		}
	}

	function password_cover ($password) {
		if(CONF_ENCRYPT_PASSWORD) return $this -> password_encrypt ($password);
		return $this -> password_md5 ($password);
	}

	function password_encrypt ($password) {
		$jumble = md5(microtime() . microtime() . getmypid());
		$salt = substr($jumble,0,CRYPT_SALT_LENGTH);

		/*
		 the best available system is automatically chosen,
		 but this could cause the password to be non exportable to other systems

		 This, for example, causes the default admin password to be non readable on some systems,
		 because another crypt system is used.
		 */
		if(CRYPT_BLOWFISH) $salt = '$2$'.substr($salt,0,(CRYPT_SALT_LENGTH-3));
		elseif(CRYPT_MD5) $salt = '$1$'.substr($salt,0,(CRYPT_SALT_LENGTH-3));
		elseif(CRYPT_EXT_DES) $salt =substr($salt,0,9);
		elseif(CRYPT_STD_DES) $salt =substr($salt,0,2);

		/*
		 echo 'CRYPT_BLOWFISH: '.CRYPT_BLOWFISH.'<br>';
		 echo 'CRYPT_MD5: '.CRYPT_MD5.'<br>';
		 echo 'CRYPT_EXT_DES: '.CRYPT_EXT_DES.'<br>';
		 echo 'CRYPT_STD_DES: '.CRYPT_STD_DES.'<br>';
		 */

		$crypted= crypt($password, $salt);

		// $datalen=strlen ($crypted)-strlen ($salt);
		// echo $datalen.' - '.$salt.' '.$crypted.'<br>';
		return $crypted;
	}

	function password_md5 ($password) {
		$output = md5($password);
		return $output;
	}

	function password_check ($test) {
		if(CONF_ENCRYPT_PASSWORD) return $this -> password_check_crypt ($test);
		return $this -> password_check_md5 ($test);
	}

	function password_check_md5 ($test) {
		$target = $this->data['password'];
		$crypted = md5($test);

		if ($crypted==$target) return true;
		return false;
	}

	function password_check_crypt ($test) {
		$target = $this->data['password'];
		$crypted = crypt($test, $target);

		if ($crypted==$target) return true;
		return false;
	}

	function is_waiter() {
		if($this->level[USER_BIT_WAITER]) return true;
		if($this->level[USER_BIT_CASHIER]) return true;
		return false;
	}

	function is_admin() {
		for($i=2;$i<100;$i++)
		if($this->level[$i] && $i!=USER_BIT_MONEY) return true;

		return false;
	}

	function html_select($who=SHOW_ALL_USERS) {
		$output = '';

		switch($who) {
			case SHOW_ALL_USERS:
				$waiter_only=false;
				$admin_only=false;
				break;
			case SHOW_WAITER_ONLY:
				$waiter_only=true;
				$admin_only=false;
				break;
			case SHOW_ADMIN_ONLY:
				$waiter_only=false;
				$admin_only=true;
				break;
			default:
				$waiter_only=false;
				$admin_only=false;
				break;
		}

		/* begin : mizuko selects users for POS and HandHeld */

		$query="SELECT `id`,`name` FROM `#prefix#users` WHERE `disabled`='0' ";
		if( !$admin_only ) $query.=" AND dest_type = 'palm' ";

		if (system::upgradeCompleted(6)) $query .= " AND `deleted`='0'";
		$query.=" ORDER BY name ASC";

		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return $output;

		$output .= '			<select name="userid" size="8">'."\n";
		$i=0;
		while ($arr=mysql_fetch_array ($res)) {
			$user=new user($arr['id']);

			$write_line=false;
			if($waiter_only && $user->is_waiter()) $write_line=true;
			elseif ($admin_only && $user->is_admin()) $write_line=true;
			elseif (!$admin_only && !$waiter_only) $write_line=true;

			if($i==0 && $write_line) {
				$selected=' selected';
				$i++;
			} else $selected='';


			if($write_line) $output .= '				<option value="'.$arr['id'].'"'.$selected.'>'.$arr['name'].'</option>'."\n";
		}
		$output .= '			</select>'."\n";

		return $output;
	}

	function html_select_tables( $kamerieri, $who=SHOW_ALL_USERS ) {
		$output = '';

		switch($who) {
			case SHOW_ALL_USERS:
				$waiter_only=false;
				$admin_only=false;
				break;
			case SHOW_WAITER_ONLY:
				$waiter_only=true;
				$admin_only=false;
				break;
			case SHOW_ADMIN_ONLY:
				$waiter_only=false;
				$admin_only=true;
				break;
			default:
				$waiter_only=false;
				$admin_only=false;
				break;
		}


		$query="SELECT `id`,`name` FROM `#prefix#users` WHERE `disabled`='0'";
		if (system::upgradeCompleted(6)) $query .= " AND `deleted`='0'";
		$query.=" ORDER BY name ASC";

		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return $output;

		$output .= '			<select name="data[locktouser]" size="1">'."\n";
		$i=0;
		while ($arr=mysql_fetch_array ($res)) {
			$user=new user($arr['id']);

			$write_line=false;
			if($waiter_only && $user->is_waiter()) $write_line=true;
			elseif ($admin_only && $user->is_admin()) $write_line=true;
			elseif (!$admin_only && !$waiter_only) $write_line=true;

			if($i==0 && $write_line && ($kamerieri == $arr['id']) ) {
				$selected=' selected';
				$i++;
			} else $selected='';

			if($write_line) $output .= '				<option value="'.$arr['id'].'">'.$arr['name'].'</option>'."\n";
		}
		$output .= '			</select>'."\n";

		return $output;
	}

	function html_button_login_pos($who=SHOW_ALL_USERS) {
		$output = '';

		switch($who) {
			case SHOW_ALL_USERS:
				$waiter_only=false;
				$admin_only=false;
				break;
			case SHOW_WAITER_ONLY:
				$waiter_only=true;
				$admin_only=false;
				break;
			case SHOW_ADMIN_ONLY:
				$waiter_only=false;
				$admin_only=true;
				break;
			default:
				$waiter_only=false;
				$admin_only=false;
				break;
		}

		/* begin : mizuko modifikim ci me dale vec perdoruesi i caktuem ne pda ose ne pc */

		$query="SELECT `id`,`name` FROM `#prefix#users` WHERE `disabled`='0'
				AND `deleted`='0' AND dest_type = 'pos' AND level!= '1022' ORDER BY name ASC";

		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return $output;

		$output .= '<table cellspacing="10" width="100%"><tr>';
		$i=0;
		while ($arr=mysql_fetch_array ($res)) {
			$user=new user($arr['id']);

			$write_line=false;
			if($waiter_only && $user->is_waiter()) $write_line=true;
			elseif ($admin_only && $user->is_admin()) $write_line=true;
			elseif (!$admin_only && !$waiter_only) $write_line=true;

			if($write_line) $output .='
			<td bgcolor="'.COLOR_TABLE_GENERAL.'">
					<center>
					<input type="image" name="loginimage" value="'.$arr['id'].'" src="'.IMAGE_LOGIN.'" width=64 height=64 hspace="20" vspace="20" onClick="(document.getElementById(\'userid\')).value = this.value;" />
					<br>
				<strong>'.strtoupper($arr['name']).'</strong></center>
			</td>';
		}
		$output .= '
		</tr></table>';
		return $output;
	}

	function count_users () {
		$query="SELECT * FROM `#prefix#users`";
		if (system::upgradeCompleted(6)) $query .= " WHERE `deleted`='0'";

		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return 0;

		return mysql_num_rows($res);
	}

	function disconnect_waiter () {
		global $tpl;

		$tpl -> set_waiter_template_file ('disconnect');

		// has to be before session_unset, because of common_db session var need
		$redirect = redirect_waiter('index.php');

		$err=$this->disconnect ();
		status_report ('DISCONNECTION',$err);

		if ($err) return $err;

		$tpl -> append ('scripts',$redirect);

		$tmp = '<a href="index.php">'.ucfirst(phr('CONNECT')).'</a>';
		$tpl -> assign ('logout',$tmp);

		return 0;
	}

	function connect() {
		if (!isset($_REQUEST['userid'])) return ERR_NO_USER_PROVIDED;

		$query="SELECT * FROM `#prefix#users` WHERE `id`='".$_REQUEST['userid']."'";
		if (system::upgradeCompleted(6)) $query .= " AND `deleted`='0'";

		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;

		$arr = mysql_fetch_array ($res);

		if(!$arr) return ERR_USER_NOT_FOUND;

		$user = new user ($arr['id']);

		if(isset($_REQUEST['password']) && !empty($_REQUEST['password'])) {
			if(!$user->password_check($_REQUEST['password'])) return ERR_WRONG_PASSWORD;
			else $_SESSION['passworded']=true;
		}

		if(isset($_REQUEST['password']) && empty($_REQUEST['password']) && (!$user->level[USER_BIT_WAITER] && !$user->level[USER_BIT_CASHIER])) return ERR_NO_PASSWORD;
		elseif(!isset($_REQUEST['password']) && (!$user->level[USER_BIT_WAITER] && !$user->level[USER_BIT_CASHIER])) return ERR_NO_PASSWORD;

		$_SESSION['userid']=$_REQUEST['userid'];
		$_SESSION['language']=$arr['language'];

		$lang_file=ROOTDIR."/lang/lang_".$_SESSION['language'].".php";
		if(is_readable($lang_file)) include($lang_file);
		else error_msg(__FILE__,__LINE__,'file '.$lang_file.' is not readable');

		return 0;
	}

	//04.07.2007
	//mizuko user can log with password only password...barcode scanner / mag reader
	function connect_pos() {
		if (!isset($_REQUEST['password']) || empty($_REQUEST['password']) ) return ERR_NO_PASSWORD;

		$query="SELECT * FROM `#prefix#users` WHERE dest_type = 'pos' AND `password` = '".md5($_REQUEST['password'])."'";
		if (system::upgradeCompleted(6)) $query .= " AND `deleted`='0'";

		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;

		$arr = mysql_fetch_array ($res);

		if(!$arr) return ERR_USER_NOT_FOUND;

		//all the work logic...
//		if(isset($_REQUEST['userid']) && !empty($_REQUEST['userid']) && $_REQUEST['userid'] == $arr['id'])
			$user = new user ($arr['id']);
//		else
//			return ERR_WRONG_PASSWORD;

		if(isset($_REQUEST['password']) && !empty($_REQUEST['password'])) {
			if(!$user->password_check($_REQUEST['password'])) return ERR_WRONG_PASSWORD;
			else $_SESSION['passworded']=true;
		}

		if(isset($_REQUEST['password']) && empty($_REQUEST['password']) ) return ERR_NO_PASSWORD;
		elseif(!isset($_REQUEST['password']) ) return ERR_NO_PASSWORD;

		$_SESSION['userid']=$arr['id'];
		$_SESSION['language']=$arr['language'];

		$lang_file=ROOTDIR."/lang/lang_".$_SESSION['language'].".php";
		if(is_readable($lang_file)) include($lang_file);
		else error_msg(__FILE__,__LINE__,'file '.$lang_file.' is not readable');

		return 0;
	}

	function disconnect () {
		$msg='INFO destroy.php - '.$this->data['name'].' ('.$this->id.') disconnected';
		debug_msg(__FILE__,__LINE__,$msg);

		// has to be before session_unset, because of common_db session var need
		//$redirect = redirect_waiter('index.php');

		$_SESSION=array();

		//setcookie( session_name() ,"",0,"/");
		unset($_COOKIE[session_name()]);

		session_unset();
		session_destroy();

		if (isset($_SESSION['userid'])) return true;

		return 0;
	}

	function disconnect_confirm () {
		global $tpl;

		$tpl -> set_waiter_template_file ('disconnect');

		$tmp = ucfirst(phr('CONNECTED_AS')).": <b>".$this->data['name']."</b><br>\n";
		$tmp .= ucfirst(phr('DISCONNECT_ASK'))."<br>\n";
		$tmp .= '<a href="?command=destroy&rndm='.rand(0,100000).'"><h4><div class="preferred_answer">'.ucfirst(phr('YES')).'</div></h4></a>';

		$tmp .= '<a href="#" onclick="javascript:history.go(-1); return false;"><h2>'.ucfirst(phr('NO')).'</h2></a><br>'."\n";
		$tpl -> assign ('logout',$tmp);
		return 0;
	}

	function printDailyIncome($user) {
		
		$fillimi = date('Y-m-d') . " 00:00:01";
		$fundi = date('Y-m-d') . " 23:59:00";
		
		$userName = $user->data['name'];
		$table='#prefix#account_mgmt_main';
		$queryMoney = "SELECT date, who, description, cash_amount ";
		$queryMoney .= "FROM `#prefix#account_mgmt_main`";
		$queryMoney .= "WHERE who = '" . $userName . "' AND ";
		$queryMoney .= "date > '" . $fillimi . "' AND date < '" . $fundi . "'";

		$resMoney=common_query($queryMoney,__FILE__,__LINE__);
		if(!$resMoney) return '';

		$therearerecordsMoney=mysql_num_rows ($resMoney); 
		if($therearerecordsMoney) {		
		$output = '
			<span class="style1">Xhiroja Ditore e '. $userName .'  nga '. $fillimi .'  ne '.  $fundi .'</span><br><br><br>
			<table>
			<tr>
				<td height="31" width="100"><strong>Data - Ora</strong></td>
				<td width="145"><div align="right"><strong>Nr i fatures</strong></div></td>
				<td width="80"><div align="right"><strong>Vlera lek</strong></div></td>
			</tr>';
			while ($arr = mysql_fetch_array ($resMoney)) { 
				$output .= '	
			 <tr>
			  	<td width="200"><div align="left">'.$arr['date'] .'</div></td>
			    <td width="145"><div align="right">'. $arr['description'].'</div></td>
			    <td width="80"><div align="right">'. $arr['cash_amount'] .'</div></td>
			  </tr>';
				$totali+=$arr['cash_amount'];
			} 
			
			$output .= '</table>
			<br><br>
			Totali i lekve per '. $userName .' eshte <b>'. $totali.'  LEK</b>';
		}
		return $output;
	}
	
	function disconnect_confirm_pos () {
		global $tpl;

		$tpl -> set_waiter_template_file ('disconnect');

		$tmp = ucfirst(phr('CONNECTED_AS')).": <b>".$this->data['name'];
		$tmp .= ucfirst(phr('DISCONNECT_ASK'));
		//$tmp .= '<a href="disconnect.php"><img src='.IMAGE_LOGOUT.'></a>';
		$tmp .= '<a href="?command=destroy&rndm='.rand(0,100000).'"><h4><div class="preferred_answer"><img src='.IMAGE_YES.' height=64 width=64></div></h4></a>';

		// don't know if this works correctly with any browser.
		// we should try but go(-1) and back() ways and report success or failures.
		// please report results here.
		$tmp .= '<a href="#" onclick="javascript:history.go(-1); return false;"><h2><img src='.IMAGE_NO.' height=64 width=64></h2></a><br>'."\n";
		$tpl -> assign ('logout',$tmp);
		return 0;
	}

	function post_update($input_data) {
		global $tpl;
		$this -> get_level ();

		$_SESSION['language']=$this->get('language');

		$lang_file=ROOTDIR."/lang/lang_".$_SESSION['language'].".php";
		if(is_readable($lang_file)) include($lang_file);
		else error_msg(__FILE__,__LINE__,'file '.$lang_file.' is not readable');

		$menu = new menu();
		$tmp = $menu -> main ();
		$tpl -> assign("menu", $tmp);

		return $input_data;
	}

	function check_values($input_data){
		$msg="";
		$input_data['name']=trim($input_data['name']);

		if($input_data['name']=="") {
			$msg=ucfirst(phr('CHECK_NAME'));
		}

		$query="SELECT * FROM `#prefix#users` WHERE `name`='".$input_data['name']."' AND `deleted`='0'";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return 0;
		while($arr=mysql_fetch_array($res)) {
			if($arr['id']!=$input_data['id']) $msg=ucfirst(phr('CHECK_NAME_ALREADY_TAKEN'));
		}

		if($input_data['password1'] != $input_data['password2'])
		$msg=ucfirst(phr('CHECK_PASSWORD_COMPARE'));

		if(!empty($input_data['password1']) && strlen($input_data['password1'])<6)
		$msg=ucfirst(phr('CHECK_PASSWORD_LENGHT'));

		if($msg){
			echo "<script language=\"javascript\">
				window.alert(\"".$msg."\");
				history.go(-1);
			</script>\n";
			return -2;
		}

		$lev=array();
		for($i=0;$i<=USER_BIT_LAST;$i++)
		if($input_data['level'][$i]) $lev[$i]='1'; else $lev[$i]='0';

		krsort($lev);
		foreach($lev as $val) $levinv.=$val;

		$levinv=bindec($levinv);
		$input_data['level']=$levinv;

		if($input_data['disabled']) $input_data['disabled']=1; else $input_data['disabled']=0;

		if($input_data['language']=="") $input_data['language']=$_SESSION['language'];

		if(!empty($input_data['password1']) && $input_data['password1'] == $input_data['password2']) {
			$input_data['password'] = trim($input_data['password1']);
			$input_data['password'] = $this -> password_cover ($input_data['password']);
		}
		unset($input_data['password1']);
		unset($input_data['password2']);

		if($input_data['password_remove']) $input_data['password']='';
		unset($input_data['password_remove']);

		if(strlen($input_data['language'])!=2) $input_data['language']=$_SESSION['language'];
		$input_data['language']=strtolower($input_data['language']);

		return $input_data;
	}

	function get_level () {
		$this -> fetch_data();
		$level = $this -> data['level'];
		$level = decbin($level);
		$level = sprintf("%010d", $level);

		$res=array();
		for($i=0;$i<=USER_BIT_LAST;$i++)
		$res[$i]=$level{USER_BIT_LAST-$i};

		$this -> level =$res;
		return 0;
	}

	function is_waiter_level($level) {
		if($level[USER_BIT_WAITER]) return true;
		if($level[USER_BIT_CASHIER]) return true;
		return false;
	}

	function is_admin_level($level) {
		for($i=2;$i<100;$i++)
		if($level[$i] && $i!=USER_BIT_MONEY) return true;

		return false;
	}

	function form() {
		if($this->id) {
			$editing=1;
			$query="SELECT * FROM `".$this->table."` WHERE `id`='".$this->id."'";
			$res=common_query($query,__FILE__,__LINE__);
			if(!$res) return mysql_errno();

			$arr=mysql_fetch_array($res);
			$this -> get_level ();
			$waiterlevel=$this->level;
		} else {
			$editing=0;
			$arr['id']=next_free_id($_SESSION['common_db'],$this->table);
		}
		$output .= '
	<div align="center">
	<a href="?class='.get_class($this).'">'.ucphr('BACK_TO_LIST').'.</a>
	<table>
	<tr>
	<td>
	<fieldset>
	<legend>'.ucphr('USER').'</legend>

	<form action="?" name="edit_form_'.get_class($this).'" method="post">
	<input type="hidden" name="class" value="'.get_class($this).'">
	<input type="hidden" name="data[id]" value="'.$arr['id'].'">';
		if($editing){
			$output .= '
	<input type="hidden" name="command" value="update">';
		} else {
			$output .= '
	<input type="hidden" name="command" value="insert">';
		}
		$output .= '
	<table>
		<tr>
			<td>
			'.ucphr('ID').':
			</td>
			<td>
			'.$arr['id'].'
			</td>
	</tr>
	<tr>
		<td>
		'.ucphr('NAME').':
		</td>
		<td>
		<input type="text" name="data[name]" value="'.$arr['name'].'">
		</td>
	</tr>
	<tr>
		<td colspan="2">';
		if (!empty($arr['password'])) {
			$output .= ucphr('PASSWORD_SET').'<br/>';
			$output .= '<input type="checkbox" name="data[password_remove]" value="1">'.ucphr('PASSWORD_REMOVE');
		} else {
			$output .= ucphr('PASSWORD_NOT_SET');
		}
		$output .= '
		</td>
		</td>
	</tr>
	<tr>
		<td>
		'.ucphr('PASSWORD').':
		</td>
		<td>
		<input type="password" name="data[password1]" value="">
		</td>
	</tr>
	<tr>
		<td>
		'.ucphr('PASSWORD_CONFIRM').':
		</td>
		<td>
		<input type="password" name="data[password2]" value="">
		</td>
	</tr>
	<tr>
		<td> Type </td>
		<td> 
			<select name="data[dest_type]">';
		if($arr['dest_type'] == 'pos')  {
			$selectedpos=' selected';
		} elseif ($arr['dest_type'] == 'palm') {
			$selectedpalm=' selected';
		}
		$output .= '
				<option value="pos" '.$selectedpos.'>POS  </option>			
				<option value="palm" '.$selectedpalm.'>Palm  </option>
			</select>
		</td>
	</tr>
	<tr>
		<td>
		'.ucphr('TEMPLATE').':
		</td>
		<td>
		<select name="data[template]">';

		$templates=list_templates(ROOTDIR.'/templates');
		for (reset ($templates); list ($key, $value) = each ($templates); ) {
			if($arr['template']==$value) $selected=' selected';
			else $selected='';

			$output .= '<option value="'.$value.'"'.$selected.'>'.$value.'</option>';
		}

		$output .= '
		</select>
		</td>
	</tr>
	<tr>
		<td colspan="2">
		<input type="checkbox" name="data[disabled]" value="1"';
		if($this->data['disabled']) $output .= ' checked';
		$output .= '>'.ucphr('DISABLED').'
		</td>
	</tr>
	<tr>
		<td colspan=2 align="center">
		<fieldset>
		<legend>'.ucphr('PERMISSIONS').'</legend>
		<table>
		<tr>
		<td>
		<input type="checkbox" name="data[level][0]" value="1"';
		if($this->level[USER_BIT_WAITER]) $output .= ' checked';
		$output .= '>'.ucphr('WAITER').'
		</td>
		<td>
		<input type="checkbox" name="data[level][1]" value="1"';
		if($this->level[USER_BIT_CASHIER]) $output .= ' checked';
		$output .= '>'.ucphr('CASHIER').'
		</td>
		</tr>
		<tr>
		<td>
		<input type="checkbox" name="data[level][2]" value="1"';
		if($this->level[USER_BIT_STOCK]) $output .= ' checked';
		$output .= '>'.ucphr('STOCK').'
		</td>
		<td>
		<input type="checkbox" name="data[level][3]" value="1"';
		if($this->level[USER_BIT_CONTACTS]) $output .= ' checked';
		$output .= '>'.ucphr('CONTACTS').'
		</td>
		</tr>
		<tr>
		<td>
		<input type="checkbox" name="data[level][4]" value="1"';
		if($this->level[USER_BIT_MENU]) $output .= ' checked';
		$output .= '>'.ucphr('MENU').'
		</td>
		<td>
		<input type="checkbox" name="data[level][5]" value="1"';
		if($this->level[USER_BIT_USERS]) $output .= ' checked';
		$output .= '>'.ucphr('USERS').'
		</td>
		</tr>
		<tr>
		<td>
		<input type="checkbox" name="data[level][6]" value="1"';
		if($this->level[USER_BIT_ACCOUNTING]) $output .= ' checked';
		$output .= '>'.ucphr('ACCOUNTING').'
		</td>
		<td>
		<input type="checkbox" name="data[level][7]" value="1"';
		if($this->level[USER_BIT_TRANSLATION]) $output .= ' checked';
		$output .= '>'.ucphr('TRANSLATION').'
		</td>
		</tr>
		<tr>
		<td>
		<input type="checkbox" name="data[level][8]" value="1"';
		if($this->level[USER_BIT_CONFIG]) $output .= ' checked';
		$output .= '>'.ucphr('CONFIG').'
		</td>
		<td>
		<input type="checkbox" name="data[level][9]" value="1"';
		if($this->level[USER_BIT_MONEY]) $output .= ' checked';
		$output .= '>'.ucphr('MONEY').'
		</td>

		</tr>
		</table>
		</fieldset>
		</td>
		</tr>
		<tr>
		<td colspan=2 align="center">
		<table>
		<tr>
			<td>';
		if(!$editing){
			$output .= '
				<input type="submit" value="'.ucphr('INSERT').'">
	</form>
			</td>';
		} else {
			$output .= '
			<td>
				<input type="submit" value="'.ucphr('UPDATE').'">
	</form>
			</td>
			<td>
				<form action="?" name="delete_form_'.get_class($this).'" method="post">
				<input type="hidden" name="class" value="'.get_class($this).'">
				<input type="hidden" name="command" value="delete">
				<input type="hidden" name="delete[]" value="'.$this->id.'">
				<input type="submit" value="'.ucphr('DELETE').'">
				</form>
			</td>';
		}
		$output .= '
		</tr>
		</table>
		</td>
	</tr>
</table>


</fieldset>
</td>
</tr>
</table>
</div>';

		return $output;
	}
}

?>