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

function account_movement_from_manage($mgmt_id){
	require(ROOTDIR."/manage/mgmt_start.php");

	$editing=0;
	$table='#prefix#account_account_log';
	$query="SELECT * FROM $table WHERE `mgmt_id`='$mgmt_id'";
	$res = mysql_db_query ($_SESSION['common_db'],$query);

	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 1;

	if(mysql_num_rows($res)){
		$arr=mysql_fetch_array($res);
		$movement_id=$arr['id'];
		$editing=1;
	}

	$table='#prefix#account_mgmt_main';
	$query="SELECT * FROM $table WHERE `id`='$mgmt_id'";

	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	$arr=mysql_fetch_array($res);
	//mizuko : accounting date fixed
	$data['date']['year']=substr($arr['date'],0,4);
	$data['date']['month']=substr($arr['date'],4,2);
	$data['date']['day']=substr($arr['date'],6,2);
	$hour=substr($arr['date'],8,2);
	$minute=substr($arr['date'],10,2);
	$second=substr($arr['date'],12,2);
/*	$date['year']=substr($arr['date'],0,4);
	$date['month']=substr($arr['date'],5,2);
	$date['day']=substr($arr['date'],8,2);
	$date['hour']=substr($arr['date'],11,2);
	$date['minute']=substr($arr['date'],14,2);
	$date['second']=substr($arr['date'],17,2);*/
	$data['account_id']=$arr['account_id'];
	$data['type']=$arr['type'];
	$data['amount']=$arr['bank_amount'];
	//$data['timestamp']=$arr['date'];
	$data['description']=$arr['description'];
	$data['mgmt_id']=$mgmt_id;
	//end : mizuko
	if($editing)
	$movement_id=account_movement_update($movement_id,$data);
	else
	$movement_id=account_movement_insert($data);

	if(!$movement_id){
		$msg="Error inserting/modifying movement from mgmt data feed";
		$msg.=' - mysql: '.mysql_errno().' '.mysql_error();
		echo $msg,"<br>\n";
		error_msg(__FILE__,__LINE__,$msg);
		return 0;
	}

	return $movement_id;

}

function account_select_plugin($id=0){
	require(ROOTDIR."/manage/mgmt_start.php");
	$table='#prefix#account_accounts';
	$query="SELECT * FROM $table ORDER BY `bank`";
	$res=common_query($query,__FILE__,__LINE__);

	?>
<select name="data[account_id]">

<?php
while($arr=mysql_fetch_array($res)){
	?>
	<option value="<?php echo $arr['id']; ?>"
	<?php if($id==$arr['id']) echo "selected"; ?>><?php echo $arr['bank']; ?>/<?php echo $arr['number']; ?>
	<?php echo $arr['name']; ?></option>
	<?php
}
?>
</select>
<?php
}

function account_movement_list($account_id=0,$orderby){
	require(ROOTDIR."/manage/mgmt_start.php");

	$table=$GLOBALS['table_prefix'].'account_account_log';
	if($account_id)
	$query="SELECT * FROM $table WHERE `account_id`='$account_id'";
	else
	$query="SELECT * FROM $table";

	$query.=" AND `timestamp` >= ".$_SESSION['timestamp']['start']." AND `timestamp` <= ".$_SESSION['timestamp']['end'];
	$query.= " ORDER BY `$orderby`";

	//echo $query;
	$res = mysql_db_query ($_SESSION['common_db'],$query);
	if($errno=mysql_errno()) {
		$msg="Error in accounts list - ";
		$msg.='mysql: '.mysql_errno().' '.mysql_error();
		echo $msg,"<br>\n";
		error_msg(__FILE__,__LINE__,$msg);
		return 1;
	}

	if(!mysql_num_rows($res)) return 2;

	$account_number=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'account_accounts','number',$account_id);
	$account_name=ucfirst(get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'account_accounts','name',$account_id));
	$account_bank=ucfirst(get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'account_accounts','bank',$account_id));
	$title=phr('ACCOUNT_TITLE')." $account_bank/$account_number ($account_name)";
	$title.=" ".phr('FROM')." ".$_SESSION['date']['start']." ".phr('TO')." ".$_SESSION['date']['end'];
	?>
<div align="center">
<h3><?php echo $title; ?></h3>
<table class="mgmt_color_tablebg">
	<thead>
		<th><a href="account.php?command=movement_list&orderby=timestamp"><?php echo phr('ACCOUNT_LABEL_DATE'); ?></a></th>
		<th><a href="account.php?command=movement_list&orderby=type"><?php echo phr('ACCOUNT_LABEL_CAUSAL'); ?></a></th>
		<th><a href="account.php?command=movement_list&orderby=description"><?php echo phr('ACCOUNT_LABEL_DESCRIPTION'); ?></a></th>
		<th><a href="account.php?command=movement_list&orderby=amount"><?php echo phr('ACCOUNT_LABEL_IN'); ?></a></th>
		<th><a href="account.php?command=movement_list&orderby=amount"><?php echo phr('ACCOUNT_LABEL_OUT'); ?></a></th>
	</thead>
	<tbody>
	<?php
	$i=0;
	while($arr=mysql_fetch_array($res)){
		$year=substr($arr['timestamp'],0,4);
		$month=substr($arr['timestamp'],5,2);
		$day=substr($arr['timestamp'],8,2);

		$edit_link="account.php?command=movement_edit&id=".$arr['id'];
		if($arr['mgmt_id']){
			$table='#prefix#account_mgmt_main';
			$query="SELECT * FROM $table WHERE `id`='".$arr['mgmt_id']."'";
			$res=common_query($query,__FILE__,__LINE__);
			if(mysql_num_rows($res_local))
			$edit_link="db.php?command=show&id=".$arr['mgmt_id'];
		}

		$mgmt_type = new mgmt_type($arr['type']);
		$type_name=$mgmt_type -> name($_SESSION['language']);
		unset($mgmt_type);

		?>
		<tr class="<?php echo color_css($i); ?>">
			<td><?php echo $day; ?>/<?php echo $month; ?>/<?php echo $year; ?></td>
			<td><?php echo $type_name; ?></td>
			<td><a href="<?php echo $edit_link; ?>"><?php echo $arr['description']; ?></a></td>
			<?php
			if($arr['amount']>=0){
				?>
			<td><?php echo abs($arr['amount']); ?></td>
			<td>&nbsp;</td>
			<?php
} else {
	?>
			<td>&nbsp;</td>
			<td><?php echo abs($arr['amount']); ?></td>
			<?php
}
?>
			<td><a href="<?php echo $edit_link; ?>"><?php echo ucfirst(phr('EDIT')); ?></a></td>
		</tr>
		<?php
}
?>
	</tbody>
</table>
</div>
<?php





}


function account_movement_check_values($input_data){

	$input_data['amount']=str_replace (",", ".", $input_data['amount']);
	$input_data['amount']=round ($input_data['amount'],2);

	if($err=check_date($input_data)) {
		switch($err){
			case 1: $msg = ucfirst(phr('CHECK_DAY')); break;
			case 2: $msg = ucfirst(phr('CHECK_MONTH')); break;
			case 3: $msg = ucfirst(phr('CHECK_YEAR')); break;
			case 4: $msg = ucfirst(phr('CHECK_DATE')); break;
		}
		//echo("<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; URL=javascript:window.close();\">");
		echo "<script language=\"javascript\">
			window.alert(\"".$msg."\");
			history.go(-1);
		</script>\n";
		return 2;
	}
	$input_data=format_date($input_data);
	$input_data['timestamp']=$input_data['date'];
	unset($input_data['date']);
	return $input_data;
}

function account_main_header() {
	?>
<table border="0">
	<tbody>
		<tr>
			<td>
			<FIELDSET><LEGEND><?php echo phr('ACCOUNT_MAIN_LEGEND'); ?></LEGEND>

			<form action="account.php" method="GET" name="account_main_form">
			<table border="0">
				<tbody>
					<tr>
						<td><input type="radio"
							onClick="document.account_main_form.submit()" name="command"
							value="list" checked> <a href="#"
							onclick="JavaScript:type_insert_check('account_main_form','command',0);document.account_main_form.submit();return(false);"><?php echo phr('ACCOUNT_LIST'); ?></a><br>
						</td>
					</tr>
					<tr>
						<td><input type="radio"
							onClick="document.account_main_form.submit()" name="command"
							value="new"> <a href="#"
							onclick="JavaScript:type_insert_check('account_main_form','command',1);document.account_main_form.submit();return(false);"><?php echo phr('ACCOUNT_INSERT'); ?></a><br>
						</td>
					</tr>
					<tr>
						<td><input type="radio"
							onClick="document.account_main_form.submit()" name="command"
							value="movement_new"> <a href="#"
							onclick="JavaScript:type_insert_check('account_main_form','command',2);document.account_main_form.submit();return(false);"><?php echo phr('ACCOUNT_MOVEMENT_INSERT'); ?></a><br>
						</td>
					</tr>
				</tbody>
			</table>
			</form>

			</FIELDSET>
			</td>
		</tr>
	</tbody>
</table>

	<?php
}

function account_movement_form($id=0){
	require(ROOTDIR."/manage/mgmt_start.php");
	if($id) {
		$editing=1;
		$table='#prefix#account_account_log';
		$query="SELECT * FROM $table WHERE `id`='$id'";
		$res=common_query($query,__FILE__,__LINE__);
		$arr=mysql_fetch_array($res);

		/*		$year=substr($arr['timestamp'],0,4);
		 $month=substr($arr['timestamp'],4,2);
		 $day=substr($arr['timestamp'],6,2);
		 $hour=substr($arr['timestamp'],8,2);
		 $minute=substr($arr['timestamp'],10,2);
		 $second=substr($arr['timestamp'],12,2);*/
		$date['year']=substr($row['date'],0,4);
		$date['month']=substr($row['date'],5,2);
		$date['day']=substr($row['date'],8,2);
		$date['hour']=substr($row['date'],11,2);
		$date['minute']=substr($row['date'],14,2);
		$date['second']=substr($row['date'],17,2);
	} else {
		$day=date("j",time());
		$month=date("n",time());
		$year=date("Y",time());
		$hour=date("H",time());
		$minute=date("i",time());
		$second=date("s",time());

		$editing=0;
	}
	?>
<div align="center">
<table>
	<tr>
		<td>
		<fieldset><legend><?php echo phr('ACCOUNT_MOVEMENT_LEGEND'); ?></legend>

		<form action="account.php" name="account_form" method="get"><?php
		if($editing){
			?> <input type="hidden" name="command" value="movement_update"> <input
			type="hidden" name="id" value="<?php echo $id; ?>"> <?php
} else {
	?> <input type="hidden" name="command" value="movement_insert"> <?php
}
?>
		<table>
			<tr>
				<td><?php echo phr('ACCOUNT_LABEL_DATE'); ?>:</td>
				<td><input type="text" size="2" maxlength="2" name="data[date][day]"
					value="<?php echo $day; ?>">/ <input type="text" size="2"
					maxlength="2" name="data[date][month]"
					value="<?php echo $month; ?>">/ <input type="text" size="4"
					maxlength="4" name="data[date][year]" value="<?php echo $year; ?>">
				</td>
			</tr>
			<tr>
				<td><?php echo phr('ACCOUNT_LABEL_BANK_ACCOUNT'); ?>:</td>
				<td><select name="data[account_id]">
				<?php
				$table='#prefix#account_accounts';
				$query="SELECT * FROM $table";
				$res_type=common_query($query,__FILE__,__LINE__);
				while($arr_type=mysql_fetch_array($res_type)){
					?>
					<option value="<?php echo $arr_type['id']?>"
					<?php if($arr_type['id']==$arr['account_id']) echo "selected"; ?>><?php echo $arr_type['bank']; ?>/<?php echo $arr_type['number']; ?>
					- <?php echo $arr_type['name']; ?></option>
					<?php
}
?>
				</select></td>
			</tr>
			<tr>
				<td><?php echo phr('ACCOUNT_LABEL_CAUSAL'); ?>:</td>
				<td><select name="data[type]">
				<?php
				$table='#prefix#mgmt_types';
				$query="SELECT * FROM $table ORDER BY `name`";
				$res_type=common_query($query,__FILE__,__LINE__);
				while($arr_type=mysql_fetch_array($res_type)){
					$mgmt_type = new mgmt_type($arr_type['id']);
					$type_name=$mgmt_type->name($_SESSION['language']);
					unset($mgmt_type);

					?>
					<option value="<?php echo $arr_type['id']; ?>"
					<?php if($arr_type['id']==$arr['type']) echo "selected"; ?>><?php echo $type_name; ?></option>
					<?php
}
?>
				</select></td>
			</tr>
			<tr>
				<td><?php echo phr('ACCOUNT_LABEL_DESCRIPTION'); ?>:</td>
				<td><input type="text" name="data[description]"
					value="<?php echo $arr['description']; ?>"></td>
			</tr>
			<tr>
				<td><?php echo phr('ACCOUNT_LABEL_AMOUNT'); ?> (- <?php echo phr('ACCOUNT_LABEL_IF_NEGATIVE'); ?>):
				</td>
				<td><input type="text" name="data[amount]"
					value="<?php echo $arr['amount']; ?>"></td>
			</tr>
			<tr>
				<td colspan=2 align="center">
				<table>
					<tr>
						<td><?php
						if(!$editing){
							?> <input type="submit"
							value="<?php echo phr('ACCOUNT_MOVEMENT_INSERT'); ?>">
						</form>
						</td>
						<?php
} else {
	?>
						<td><input type="submit"
							value="<?php echo phr('ACCOUNT_MOVEMENT_EDIT'); ?>">
						</form>
						</td>
						<td>
						<form action="account.php" name="account_form" method="get"><input
							type="hidden" name="command" value="movement_delete"> <input
							type="hidden" name="delete[<?php echo $id; ?>]" value="1"> <input
							type="submit"
							value="<?php echo phr('ACCOUNT_MOVEMENT_DELETE'); ?>"></form>
						</td>
						<?php
}
?>
					</tr>
				</table>
				</td>
			</tr>
		</table>
		
		</fieldset>
		</td>
	</tr>
</table>
</div>
<?php

}

function account_movement_delete($input_id) {
	require("./mgmt_start.php");

	$table=$GLOBALS['table_prefix'].'account_account_log';
	$query="SELECT * FROM $table WHERE `id`='$input_id'";
	$res=mysql_db_query($_SESSION['common_db'],$query);
	$arr=mysql_fetch_array($res);
	$old_movement_amount=$arr['amount'];
	$description=$arr['description'];

	$account_id=$arr['account_id'];

	$table=$GLOBALS['table_prefix'].'account_accounts';
	$query="SELECT * FROM $table WHERE `id`='$account_id'";
	$res=mysql_db_query($_SESSION['common_db'],$query);
	$arr=mysql_fetch_array($res);
	$old_amount=$arr['amount'];

	// Now we'll build the correct INSERT query, based on the fields provided
	$table=$GLOBALS['table_prefix'].'account_account_log';
	$query="DELETE FROM $table ";
	$query.=" WHERE `id`='$input_id'";
	$res = mysql_db_query ($_SESSION['common_db'],$query);
	$num_affected=mysql_affected_rows();

	if ($num_affected==1) {

		/*
		 <META HTTP-EQUIV="Refresh" CONTENT="<?php echo get_conf(__FILE__,__LINE__,"refresh_time_management"); ?>; URL=account.php?command=movement_list&id=<?php echo $account_id; ?>">
		 */
		?>
		<?php echo phr('ACCOUNT_MOVEMENT'); ?>
<b><?php echo $description; ?></b>
		<?php echo phr('ACCOUNT_MOVEMENT_DELETE_SUCCESS'); ?>
.
<br>
		<?php

		$reset_amount=$old_amount-$old_movement_amount;

		$table=$GLOBALS['table_prefix'].'account_accounts';
		$query="UPDATE $table SET `amount`='$reset_amount' WHERE `id`='$account_id'";
		$res = mysql_db_query ($_SESSION['common_db'],$query);
		$num_affected=mysql_affected_rows();

		return $input_id;
} elseif($errno=mysql_errno()) {
	$msg="Error in account_account_log delete - ";
	$msg.='mysql: '.mysql_errno().' '.mysql_error();
	echo $msg,"<br>\n";
	error_msg(__FILE__,__LINE__,$msg);
	return 0;
} else {

	/*
	 <META HTTP-EQUIV="Refresh" CONTENT="<?php echo get_conf(__FILE__,__LINE__,"refresh_time_management"); ?>; URL=account.php?command=movement_list&id=<?php echo $account_id; ?>">
	 */
	?>
	<?php echo phr('ACCOUNT_MOVEMENT_NOTHING_DONE'); ?>
.
<br>
	<?php
	return $input_id;
}
}

function account_movement_update($input_id,$input_data) {
	require("./mgmt_start.php");

	if(!$input_data['account_id']) return -2;

	$table=$GLOBALS['table_prefix'].'account_account_log';
	$query="SELECT * FROM $table WHERE `id`='$input_id'";
	$res=mysql_db_query($_SESSION['common_db'],$query);
	$arr=mysql_fetch_array($res);
	$old_movement_amount=$arr['amount'];
	$old_account_id=$arr['account_id'];

	$table=$GLOBALS['table_prefix'].'account_accounts';
	$query_local="SELECT * FROM $table WHERE `id`='$old_account_id'";
	$res_local=mysql_db_query($_SESSION['common_db'],$query_local);
	$arr_local=mysql_fetch_array($res_local);
	$old_amount=$arr_local['amount'];


	$input_data=account_movement_check_values($input_data);
	if($input_data<0) return $input_data;

	// Now we'll build the correct INSERT query, based on the fields provided
	$table=$GLOBALS['table_prefix'].'account_account_log';
	$query="UPDATE $table SET ";
	for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
		$query.="`".$key."`='".$value."',";
	}
	// strips the last comma that has been put
	$query = substr ($query, 0, strlen($query)-1);
	$query.=" WHERE `id`='$input_id'";

	$res = mysql_db_query ($_SESSION['common_db'],$query);
	$num_affected=mysql_affected_rows();

	if ($num_affected==1) {

		/*
		 <META HTTP-EQUIV="Refresh" CONTENT="<?php echo get_conf(__FILE__,__LINE__,"refresh_time_management");?>; URL=account.php?command=movement_list&id=<?php echo $input_data['account_id']; ?>">
		 */
		?>
		<?php echo phr('ACCOUNT_MOVEMENT'); ?>
<b><?php echo $input_data['name']; ?></b>
		<?php echo phr('ACCOUNT_MOVEMENT_UPDATE_SUCCESS'); ?>
.
<br>
		<?php

		$reset_amount=$old_amount-$old_movement_amount;

		$table=$GLOBALS['table_prefix'].'account_accounts';
		$query="UPDATE $table SET `amount`='$reset_amount' WHERE `id`='$old_account_id]'";
		$res = mysql_db_query ($_SESSION['common_db'],$query);
		$num_affected=mysql_affected_rows();

		$table=$GLOBALS['table_prefix'].'account_accounts';
		$query="SELECT * FROM $table WHERE `id`='".$input_data['account_id']."'";
		$res=mysql_db_query($_SESSION['common_db'],$query);
		$arr=mysql_fetch_array($res);
		$new_amount=$arr['amount'];
		$new_amount=$new_amount+$input_data['amount'];

		$table=$GLOBALS['table_prefix'].'account_accounts';
		$query="UPDATE $table SET `amount`='$new_amount' WHERE `id`='".$input_data['account_id']."'";
		$res = mysql_db_query ($_SESSION['common_db'],$query);
		$num_affected=mysql_affected_rows();

		return $input_id;
} elseif($errno=mysql_errno()) {
	$msg="Error in account_account_log update - ";
	$msg.='mysql: '.mysql_errno().' '.mysql_error();
	echo $msg,"<br>\n";
	error_msg(__FILE__,__LINE__,$msg);
	return 0;
} else {

	/*
	 <META HTTP-EQUIV="Refresh" CONTENT="<?php echo get_conf(__FILE__,__LINE__,"refresh_time_management"); ?>; URL=account.php?command=movement_list&id=<?php echo $input_data['account_id']; ?>">
	 */
	?>
	<?php echo phr('ACCOUNT_MOVEMENT_NOTHING_DONE'); ?>
.
<br>
	<?php
	return $input_id;
}
}

function account_movement_insert($input_data) {
	require("./mgmt_start.php");

	if(!$input_data['account_id']) return -2;

	$table=$GLOBALS['table_prefix'].'account_accounts';
	$query="SELECT * FROM $table WHERE `id`='".$input_data['account_id']."'";
	$res=mysql_db_query($_SESSION['common_db'],$query);
	$arr=mysql_fetch_array($res);

	$old_amount=$arr['amount'];

	$input_data=account_movement_check_values($input_data);
	if($input_data<0) return $input_data;

	// Now we'll build the correct INSERT query, based on the fields provided
	$table=$GLOBALS['table_prefix'].'account_account_log';
	$query="INSERT INTO $table (";
	for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
		$query.="`".$key."`,";
	}
	// strips the last comma that has been put
	$query = substr ($query, 0, strlen($query)-1);
	$query.=") VALUES (";
	for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
		$query.="'".$value."',";
	}
	// strips the last comma that has been put
	$query = substr ($query, 0, strlen($query)-1);	$query.=")";

	$res = mysql_db_query ($_SESSION['common_db'],$query);
	$num_affected=mysql_affected_rows();
	$inserted_id = mysql_insert_id();

	if ($num_affected==1) {
		/*
		 <META HTTP-EQUIV="Refresh" CONTENT="<?php echo get_conf(__FILE__,__LINE__,"refresh_time_management"); ?>; URL=account.php?command=movement_list&id=<?php echo $input_data['account_id']; ?>">
		 */
		?>
		<?php echo phr('ACCOUNT_MOVEMENT'); ?>
<b><?php echo $input_data['name']; ?></b>
		<?php echo phr('ACCOUNT_MOVEMENT_ADD_SUCCESS'); ?>
.
<br>
		<?php
		$new_amount=$old_amount+$input_data['amount'];

		$table=$GLOBALS['table_prefix'].'account_accounts';
		$query="UPDATE $table SET `amount`='$new_amount' WHERE `id`='".$input_data['account_id']."'";
		$res = mysql_db_query ($_SESSION['common_db'],$query);
		$num_affected=mysql_affected_rows();

		return $inserted_id;
} elseif($errno=mysql_errno()) {
	$msg="Error in account_account_log insert - ";
	$msg.='mysql: '.mysql_errno().' '.mysql_error();
	echo $msg,"<br>\n";
	error_msg(__FILE__,__LINE__,$msg);
	return 0;
} else {
	/*
	 <META HTTP-EQUIV="Refresh" CONTENT="<?php echo get_conf(__FILE__,__LINE__,"refresh_time_management"); ?>; URL=account.php?command=movement_list&id=<?php echo $input_data['account_id']; ?>">
	 */
	?>
	<?php echo phr('ACCOUNT_MOVEMENT_NOTHING_DONE'); ?>
.
<br>
	<?php
	return 0;
}
}







function account_check_values($input_data){

	if(!$input_data['number']) return -1;
	$input_data['number']=sprintf("%012d",$input_data['number']);

	$input_data['abi']=sprintf("%05d",$input_data['abi']);
	$input_data['cab']=sprintf("%05d",$input_data['cab']);
	if(strlen($input_data['cin'])!=1) $input_data['cin']="";
	if(strlen($input_data['bic1'])!=8 || strlen($input_data['bic1'])!=11) $input_data['bic1']="";
	if(strlen($input_data['bic2'])!=8 || strlen($input_data['bic2'])!=11) $input_data['bic2']="";
	if(strlen($input_data['iban'])!=12) $input_data['iban']="";

	if(isset($input_data['amount'])){
		$input_data['amount']=str_replace (",", ".", $input_data['amount']);
		$input_data['amount']=round ($input_data['amount'],2);
	}
	if(isset($input_data['currencies'])){
		strtoupper($input_data['currencies']);
	}
	if(isset($input_data['iban'])){
		strtoupper($input_data['iban']);
	}
	if(isset($input_data['bic1'])){
		strtoupper($input_data['bic1']);
	}
	if(isset($input_data['bic2'])){
		strtoupper($input_data['bic2']);
	}
	if(isset($input_data['cin'])){
		strtoupper($input_data['cin']);
	}
	return $input_data;
}

function account_update($input_id,$input_data) {
	require("./mgmt_start.php");

	$input_data=account_check_values($input_data);
	if($input_data<0) return $input_data;

	// Now we'll build the correct INSERT query, based on the fields provided
	$table=$GLOBALS['table_prefix'].'account_accounts';
	$query="UPDATE $table SET ";
	for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
		$query.="`".$key."`='".$value."',";
	}
	// strips the last comma that has been put
	$query = substr ($query, 0, strlen($query)-1);
	$query.=" WHERE `id`='$input_id'";

	$res = mysql_db_query ($_SESSION['common_db'],$query);
	$num_affected=mysql_affected_rows();
	$inserted_id = mysql_insert_id();

	if ($num_affected==1) {

		/*
		 <META HTTP-EQUIV="Refresh" CONTENT="<?php echo get_conf(__FILE__,__LINE__,"refresh_time_management"); ?>; URL=account.php?command=list">
		 */
		?>
		<?php echo phr('ACCOUNT_THE'); ?>
<b><?php echo $input_data['name']; ?></b>
		<?php echo phr('ACCOUNT_MOVEMENT_UPDATE_SUCCESS'); ?>
.
<br>
		<?php
} elseif($errno=mysql_errno()) {
	$msg="Error in accounts update - ";
	$msg.='mysql: '.mysql_errno().' '.mysql_error();
	echo $msg,"<br>\n";
	error_msg(__FILE__,__LINE__,$msg);
	return 1;
} else {

	/*
	 <META HTTP-EQUIV="Refresh" CONTENT="<?php echo get_conf(__FILE__,__LINE__,"refresh_time_management"); ?>; URL=account.php?command=list">
	 */
	?>
	<?php echo phr('ACCOUNT_MOVEMENT_NOTHING_DONE'); ?>
.
<br>
	<?php
}
}

function account_insert($input_data) {
	require("./mgmt_start.php");

	$input_data=account_check_values($input_data);
	if($input_data<0) return $input_data;

	// Now we'll build the correct INSERT query, based on the fields provided
	$table=$GLOBALS['table_prefix'].'account_accounts';
	$query="INSERT INTO $table (";
	for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
		$query.="`".$key."`,";
	}
	// strips the last comma that has been put
	$query = substr ($query, 0, strlen($query)-1);
	$query.=") VALUES (";
	for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
		$query.="'".$value."',";
	}
	// strips the last comma that has been put
	$query = substr ($query, 0, strlen($query)-1);	$query.=")";

	$res = mysql_db_query ($_SESSION['common_db'],$query);
	$num_affected=mysql_affected_rows();
	$inserted_id = mysql_insert_id();

	if ($num_affected==1) {
		/*
		 <META HTTP-EQUIV="Refresh" CONTENT="<?php echo get_conf(__FILE__,__LINE__,"refresh_time_management"); ?>; URL=account.php?command=list">
		 */
		?>
		<?php echo phr('ACCOUNT_THE'); ?>
<b><?php echo $input_data['name']; ?></b>
		<?php echo phr('ACCOUNT_MOVEMENT_ADD_SUCCESS'); ?>
.
<br>
		<?php
} elseif($errno=mysql_errno()) {
	$msg="Error in accounts insert - ";
	$msg.='mysql: '.mysql_errno().' '.mysql_error();
	echo $msg,"<br>\n";
	error_msg(__FILE__,__LINE__,$msg);
	return 1;
} else {

	/*
	 <META HTTP-EQUIV="Refresh" CONTENT="<?php echo get_conf(__FILE__,__LINE__,"refresh_time_management"); ?>; URL=account.php?command=list">
	 */
	?>
	<?php echo phr('ACCOUNT_MOVEMENT_NOTHING_DONE'); ?>
.
<br>
	<?php
}
}

function account_form($id=0){
	require("./mgmt_start.php");
	if($id) {
		$editing=1;
		$table=$GLOBALS['table_prefix'].'account_accounts';
		$query="SELECT * FROM $table WHERE `id`='$id'";
		$res=mysql_db_query($_SESSION['common_db'],$query);
		$arr=mysql_fetch_array($res);
	} else {
		$editing=0;
	}
	?>
<div align="center">
<table>
	<tr>
		<td>
		<fieldset><legend><?php echo phr('ACCOUNT_LEGEND'); ?></legend>

		<form action="account.php" name="account_form" method="get"><?php
		if($editing){
			?> <input type="hidden" name="command" value="update"> <input
			type="hidden" name="id" value="<?php echo $id; ?>"> <?php
} else {
	?> <input type="hidden" name="command" value="insert"> <?php
}
?>
		<table>
			<tr>
				<td><?php echo phr('ACCOUNT_BANK'); ?>:</td>
				<td><select name="data[bank]">
				<?php
				$table='#prefix#account_mgmt_addressbook';
				$query="SELECT * FROM $table WHERE `type`='1'";
				$res_bank=common_query($query,__FILE__,__LINE__);
				while($arr_bank=mysql_fetch_array($res_bank)){
					?>
					<option
					<?php if ($arr_bank['name']==$arr['bank']) echo ' selected="true"';?>><?php echo $arr_bank['name']; ?></option>
					<?php
}
?>
				</select></td>
			</tr>
			<tr>
				<td><?php echo phr('ACCOUNT_NAME'); ?>:</td>
				<td><input type="text" name="data[name]"
					value="<?php echo $arr['name']; ?>"></td>
			</tr>
			<tr>
				<td><?php echo phr('ACCOUNT_NUMBER'); ?>:</td>
				<td><input type="text" name="data[number]" maxlength="12"
					value="<?php echo $arr['number']; ?>"></td>
			</tr>
			<tr>
				<td><?php echo phr('ACCOUNT_ABI'); ?>:</td>
				<td><input type="text" name="data[abi]" maxlength="5"
					value="<?php echo $arr['abi']; ?>"></td>
			</tr>
			<tr>
				<td><?php echo phr('ACCOUNT_CAB'); ?>:</td>
				<td><input type="text" name="data[cab]" maxlength="5"
					value="<?php echo $arr['cab']; ?>"></td>
			</tr>
			<tr>
				<td><?php echo phr('ACCOUNT_CIN'); ?>:</td>
				<td><input type="text" name="data[cin]" maxlength="1"
					value="<?php echo $arr['cin']; ?>"></td>
			</tr>
			<tr>
				<td><?php echo phr('ACCOUNT_SWIFT'); ?>:</td>
				<td><input type="text" name="data[bic1]" maxlength="11"
					value="<?php echo $arr['bic1']; ?>"></td>
			</tr>
			<tr>
				<td><?php echo phr('ACCOUNT_BIC'); ?>:</td>
				<td><input type="text" name="data[bic2]" maxlength="11"
					value="<?php echo $arr['bic2']; ?>"></td>
			</tr>
			<tr>
				<td><?php echo phr('ACCOUNT_IBAN'); ?>:</td>
				<td><input type="text" name="data[iban]" maxlength="12"
					value="<?php echo $arr['iban']; ?>"></td>
			</tr>
			<tr>
				<td><?php echo phr('ACCOUNT_CURRENCY'); ?>:</td>
				<td><input type="text" name="data[currencies]" maxlength="3"
					value="<?php if($editing) echo $arr['currencies']; else echo country_conf_currencies(); ?>">
				</td>
			</tr>
			<?php
			if(!$editing){
				?>
			<tr>
				<td><?php echo phr('ACCOUNT_INITIAL_AMOUNT'); ?>:</td>
				<td><input type="text" name="data[amount]" maxlength="12"
					value="0.00"></td>
			</tr>
			<?php
}
?>
			<tr>
				<td colspan=2 align="center"><?php
				if(!$editing){
					?> <input type="submit"
					value="<?php echo phr('ACCOUNT_INSERT'); ?>"> <?php
} else {
	?> <input type="submit" value="<?php echo phr('ACCOUNT_EDIT'); ?>"> <?php
}
?></td>
			</tr>
		</table>
		</form>

		</fieldset>
		</td>
	</tr>
</table>
</div>
<?php

}

function account_list($orderby="name"){
	require("./mgmt_start.php");

	$table=$GLOBALS['table_prefix'].'account_accounts';
	$query="SELECT * FROM $table ORDER BY `$orderby`";
	$res = mysql_db_query ($_SESSION['common_db'],$query);
	if($errno=mysql_errno()) {
		$msg="Error in accounts list - ";
		$msg.='mysql: '.mysql_errno().' '.mysql_error();
		echo $msg,"<br>\n";
		error_msg(__FILE__,__LINE__,$msg);
		return 1;
	}

	if(!mysql_num_rows($res)) return 2;

	$title=phr('ACCOUNT_TABLE_TITLE');
	?>
<div align="center">
<h3><?php echo $title; ?></h3>
<table class="mgmt_color_tablebg">
	<thead>
		<th><a href="account.php?command=list&orderby=bank"><?php echo phr('ACCOUNT_TABLE_BANK'); ?></a></th>
		<th><a href="account.php?command=list&orderby=name"><?php echo phr('ACCOUNT_TABLE_NAME'); ?></a></th>
		<th><a href="account.php?command=list&orderby=number"><?php echo phr('ACCOUNT_TABLE_NUMBER'); ?></a></th>
		<th><a href="account.php?command=list&orderby=abi"><?php echo phr('ACCOUNT_TABLE_ABI'); ?></a></th>
		<th><a href="account.php?command=list&orderby=cab"><?php echo phr('ACCOUNT_TABLE_CAB'); ?></a></th>
		<th><a href="account.php?command=list&orderby=cin"><?php echo phr('ACCOUNT_TABLE_CIN'); ?></a></th>
		<th><a href="account.php?command=list&orderby=bic1"><?php echo phr('ACCOUNT_TABLE_SWIFT'); ?></a></th>
		<th><a href="account.php?command=list&orderby=bic2"><?php echo phr('ACCOUNT_TABLE_BIC'); ?></a></th>
		<th><a href="account.php?command=list&orderby=iban"><?php echo phr('ACCOUNT_TABLE_IBAN'); ?></a></th>
		<th><a href="account.php?command=list&orderby=currencies"><?php echo phr('ACCOUNT_TABLE_CURRENCY'); ?></a></th>
		<th><a href="account.php?command=list&orderby=amount"><?php echo phr('ACCOUNT_TABLE_AMOUNT'); ?></a></th>
	</thead>
	<tbody>
	<?php
	$i=0;
	while($arr=mysql_fetch_array($res)){
		?>
		<tr class="<?php echo color_css($i); ?>">
			<td><?php
			$table='#prefix#account_mgmt_addressbook';
			$query="SELECT id FROM $table WHERE `type`='1' AND `name`='".$arr['bank']."'";
			$res_bank=common_query($query,__FILE__,__LINE__);
			if($arr_bank=mysql_fetch_array($res_bank)){
				?> <a
				href="supply.php?command=showknownsupplier&id=<?php echo $arr_bank['id']; ?>">
				<?php echo ucfirst($arr['bank']); ?> </a> <?php
} else {
	?> <?php echo ucfirst($arr['bank']); ?> <?php
}
?></td>
			<td><a
				href="account.php?command=movement_list&id=<?php echo $arr['id']; ?>"><?php echo ucfirst($arr['name']); ?></a></td>
			<td><a
				href="account.php?command=movement_list&id=<?php echo $arr['id']; ?>"><?php echo $arr['number']; ?></a></td>
			<td><?php echo $arr['abi']; ?></td>
			<td><?php echo $arr['cab']; ?></td>
			<td><?php echo $arr['cin']; ?></td>
			<td><?php echo $arr['bic1']; ?></td>
			<td><?php echo $arr['bic2']; ?></td>
			<td><a
				href="account.php?command=movement_list&id=<?php echo $arr['id']; ?>"><?php echo $arr['iban']; ?></a></td>
			<td><?php echo $arr['currencies']; ?></td>
			<?php
if($arr['amount']>0){
?>
			<td><?php echo $arr['amount']; ?></td>
			<?php
} else {
?>
			<td><font color="#F10404"><?php echo $arr['amount']; ?></font></td>
			<?php
}
?>
			<td><a href="account.php?command=edit&id=<?php echo $arr['id']; ?>"><?php echo ucfirst(phr('EDIT')); ?></a></td>
		</tr>
		<?php
	}
?>
	</tbody>
</table>
</div>
<?php

}

?>
