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

function get_text_start_date() {
	// explode data string from DD/MM/YYYY to array
	list($date[2],$date[1],$date[0])=explode("/",$_SESSION['date']['start']);
	ksort($date);

	$conf_day_end=get_conf(__FILE__,__LINE__,"day_end");
	$year=$date[0];
	$month=$date[1];
	$day=$date[2];
	$hour=substr($conf_day_end,0,2);
	$minute=substr($conf_day_end,2,2);
	$second=substr($conf_day_end,4,2);

	$time_start=mktime($hour,$minute,$second,$month,$day,$year);

	$time_start_arr[2]=date("j",$time_start);
	$time_start_arr[1]=date("n",$time_start);
	$time_start_arr[0]=date("Y",$time_start);
	$time_start_arr[3]=date("H",$time_start);
	$time_start_arr[4]=date("i",$time_start);
	$time_start_arr[5]=date("s",$time_start);

	// begins writing of the timestamp string
	$timestamp_start="";
	for ($i=0;$i<6;$i++) {
		if($i=="0"){
			$timestamp_start.=sprintf("%04d",$time_start_arr[$i]);
		} else {
			$timestamp_start.=sprintf("%02d",$time_start_arr[$i]);
		}
	}

	$start_date=substr($timestamp_start,6,2)."/";
	$start_date.=substr($timestamp_start,4,2)."/";
	$start_date.=substr($timestamp_start,0,4);
	return $start_date;
}

function get_text_start_hour () {
	$hour=substr($_SESSION['timestamp']['start'],8,2);
	return $hour;
}

function get_text_start_minute () {
	$minute=substr($_SESSION['timestamp']['start'],10,2);
	return $minute;
}

function get_text_end_hour () {
	$hour=substr($_SESSION['timestamp']['end'],8,2);
	return $hour;
}

function get_text_end_minute () {
	$minute=substr($_SESSION['timestamp']['end'],10,2);
	return $minute;
}

function get_text_end_date() {
	list($date[2],$date[1],$date[0])=explode("/",$_SESSION['date']['end']);
	ksort($date);

	$conf_day_end=get_conf(__FILE__,__LINE__,"day_end");
	$year=$date[0];
	$month=$date[1];
	$day=$date[2];
	$hour=substr($conf_day_end,0,2);
	$minute=substr($conf_day_end,2,2);
	$second=substr($conf_day_end,4,2);

	$time_end=mktime($hour,$minute,$second,$month,$day,$year);

	$time_end_arr[2]=date("j",$time_end);
	$time_end_arr[1]=date("n",$time_end);
	$time_end_arr[0]=date("Y",$time_end);
	$time_end_arr[3]=date("H",$time_end);
	$time_end_arr[4]=date("i",$time_end);
	$time_end_arr[5]=date("s",$time_end);

	$timestamp_end="";
	for ($i=0;$i<6;$i++) {
		if($i=="0"){
			$timestamp_end.=sprintf("%04d",$time_end_arr[$i]);
		} else {
			$timestamp_end.=sprintf("%02d",$time_end_arr[$i]);
		}
	}

	$end_date=substr($timestamp_end,6,2)."/";
	$end_date.=substr($timestamp_end,4,2)."/";
	$end_date.=substr($timestamp_end,0,4);

	return $end_date;
}

function main_header($to_page="index.php"){
	require("./mgmt_start.php");

	$start_date_local=get_text_start_date();
	$end_date_local=get_text_end_date();
	$start_hour_local=get_text_start_hour();
	$start_minute_local=get_text_start_minute();
	$end_hour_local=get_text_end_hour();
	$end_minute_local=get_text_end_minute();
?>
<center><form action="<?php echo $to_page; ?>" method="GET" name="time_range">
<table>
<tr valign="middle">
<td align="right"><?php echo ucfirst(GLOBALMSG_REPORT_PERIOD); ?></td>
	<td align="left">
		<input type="hidden" name="formdata" value="true">
		<input type="text" id="f_date_a" name="date_start" value="<?php echo $start_date_local; ?>" size="14" maxlength="10">
		<input type="text" name="hour_start" value="<?php echo $start_hour_local; ?>" size="2" maxlength="2">:<input type="text" name="minute_start" value="<?php echo $start_minute_local; ?>" size="2" maxlength="2">
		<input type="text" id="f_date_b" name="date_end" value="<?php echo $end_date_local; ?>" size="14" maxlength="10">
		<input type="text" name="hour_end" value="<?php echo $end_hour_local; ?>" size="2" maxlength="2">:<input type="text" name="minute_end" value="<?php echo $end_minute_local; ?>" size="2" maxlength="2">
	</td>
	
<script type="text/javascript">
    function catcalc(cal) {
        var date = cal.date;
        var time = date.getTime()
        // use the _other_ field
        var field = document.getElementById("f_date_b");
        if (field == cal.params.inputField) {
            field = document.getElementById("f_date_a");
            time -= Date.DAY; // substract one week
        } else {
            time += Date.DAY; // add one week
        }
        var date2 = new Date(time);
        field.value = date2.print("%d/%m/%Y");
    }
    Calendar.setup({
        inputField     :    "f_date_a",   // id of the input field
        ifFormat       :    "%d/%m/%Y",       // format of the input field
       // showsTime      :    true,
        timeFormat     :    "24",
	weekNumbers : false,
	step:1,
	firstDay : 1
      //  onUpdate       :    catcalc
    });
    Calendar.setup({
        inputField     :    "f_date_b",
        ifFormat       :    "%d/%m/%Y",       // format of the input field
//        ifFormat       :    "%Y-%m-%d %H:%M",
        //showsTime      :    true,
        timeFormat     :    "24",
	weekNumbers : false,
	step:1,
	firstDay : 1
     //   onUpdate       :    catcalc
    });
</script>

	
	<td><?php echo GLOBALMSG_REPORT_ACCOUNT; ?></td>
	<td align="left">
<?php
		$checked="";
		if(mysql_list_tables($arr['db']) && $_SESSION['common_db']==$arr['db']) {
			$checked=" checked";
		}
		if(mysql_list_tables($arr['db'])) {
			if($account==$arr['db'])
				$checked=" checked";
			echo '<input type="radio" onClick="JavaScript:document.time_range.submit();" name="mgmt_db_number" value="'.$arr['db'].'"'.$checked.'>'.$arr['name'].' '."\n";
		}
?>
	</td>
<td ><input type="submit" value="<?php echo ucfirst(phr('REPORT_GENERATE')); ?>"></td>
</tr>
</table></form></center>

<?php
}

function check_date($data){
	if($data["date"]["day"]=="") return 1;
	if($data["date"]["month"]=="") return 2;
	if($data["date"]["year"]=="") return 3;

	$day=(int) $data["date"]["day"];
	$month=(int) $data["date"]["month"];
	$year=(int) $data["date"]["year"];

	if(!checkdate($month,$day,$year)) return 4;

	return 0;

}

function check_compulsory_fields($data){
	$table='mgmt_types';
	$res=mysql_db_query($_SESSION['common_db'],"SELECT * FROM $table WHERE `id`='".$data['type']."'");
	$row=mysql_fetch_array($res);
	$type=strtolower($row['name']);
	if(strtolower($type)=="fattura")
		$invoice=1;
	else
		$invoice=0;

	if(!isset($data["date"]) || !is_array($data["date"])){
		return 1;
	} elseif(!isset($data["description"])){
		return 3;
	} elseif(isset($data["description"]) && $data["description"]==""){
		return 3;
	} elseif(!isset($data["type"])) {
		return 4;
	} elseif(isset($data["type"]) && $data["type"]=="") {
		return 4;
	}
	return 0;

}

function format_date($data) {
	require(ROOTDIR."/conf/config.constants.inc.php");

	//$conf_day_end=get_conf(__FILE__,__LINE__,"day_end");
	$conf_day_end='000000';
	if(!isset($data['date']['hour']))
		$data['date']['hour']=substr($conf_day_end,0,2);//date("H");
	if(!isset($data['date']['minute']))
		$data['date']['minute']=substr($conf_day_end,2,2);//date("i");
	if(!isset($data['date']['second']))
		$data['date']['second']=substr($conf_day_end,4,2)+1;//date("s");

	for (reset ($data); list ($key, $value) = each ($data); ) {
		if($key=="date") {
			$date_array=$value;
			for (reset ($date_array); list ($key2, $value2) = each ($date_array); ) {
				switch($key2){
					case "year": $tmpdate[0]=sprintf("%04d",$value2); break;
					case "month": $tmpdate[1]=sprintf("%02d",$value2); break;
					case "day": $tmpdate[2]=sprintf("%02d",$value2); break;
					case "hour": $tmpdate[3]=sprintf("%02d",$value2); break;
					case "minute": $tmpdate[4]=sprintf("%02d",$value2); break;
					case "second": $tmpdate[5]=sprintf("%02d",$value2); break;
				}
			}
			ksort($tmpdate);

			$newdate = implode ("", $tmpdate);
			for (reset ($data); list ($key2, $value2) = each ($data); ) {
				if($key2=="date") {
					$data[$key2]=$newdate;
				}
			}
			return $data;
		}
	}
	return 1;
}

function format_currency($data) {
	$table='account_mgmt_main';
	$res = mysql_db_query ($_SESSION['common_db'],"SELECT * FROM $table");

	$fieldnum=mysql_num_fields($res);
	for($i=0;$i<$fieldnum;$i++){
		$fieldname=mysql_field_name($res, $i);
		$fieldtype[$fieldname]=mysql_field_type($res, $i);
	}

	for (reset ($data); list ($key, $value) = each ($data); ) {
		if($fieldtype[$key]=="real") {
			$data[$key]= str_replace (",", ".", $data[$key]);
		}
	}
	return $data;
}

function format_checkbox($data) {
	$table='account_mgmt_main';
	$res = mysql_db_query ($_SESSION['common_db'],"SELECT * FROM $table");

	for (reset ($data); list ($key, $value) = each ($data); ) {
		if($key=="paid" && $value==1) {
			$data["paid"]= 1;
		} elseif ($data["paid"]!=1) {
			$data["paid"]= 0;
		}
	}
	return $data;
}

function calculate_amount($data){
	$data["cash_amount"]=$data["cash_taxable_amount"]+$data["cash_vat_amount"];
	$data["bank_amount"]=$data["bank_taxable_amount"]+$data["bank_vat_amount"];
	$data["debit_amount"]=$data["debit_taxable_amount"]+$data["debit_vat_amount"];
	return $data;
}

function timestamp_is_between($date_read,$date_start,$date_end){
	// this function return 1 if the supplied $date_read is between
	// or the same day (depends on date_is_before and date_is_later cond�figuration)
	// the $date_start and the $date_end dates.
	// timestamp format is: YYYYMMDDhhmmss
	if (timestamp_is_before($date_read,$date_end) && timestamp_is_later($date_read,$date_start))
		return 1;
	else {
		return 0;
	}
}

function timestamp_is_later($date_read,$date_refer){
	// this function return 1 if the supplied $date_read is later or the same day of
	// the $date_refer date.
	// timestamp format is: YYYYMMDDhhmmss

	if($date_read<$date_refer){
		return 0;
	} elseif ($date_read>$date_refer){
		return 1;
	} elseif (substr($date_read,8)==substr($date_refer,8)){
		// put return 0 here if you want to be same-day-excluded
		return 1;
	}
	return 0;
}

function timestamp_is_before($date_read,$date_refer){
	// this function return 1 if the supplied $date_read is before or the same day of
	// the $date_refer date.
	// timestamp format is: YYYYMMDDhhmmss

	if($date_read<$date_refer){
		return 1;
	} elseif ($date_read>$date_refer){
		return 0;
	} elseif (substr($date_read,8)==substr($date_refer,8)){
		// put return 0 here if you want to be same-day-excluded
		return 1;
	}
	return 0;
}

function date_is_between($date_read,$date_start,$date_end){
	// this function return 1 if the supplied $date_read is between
	// or the same day (depends on date_is_before and date_is_later cond�figuration)
	// the $date_start and the $date_end dates.
	// date format is: DD/MM/YYYY
	if (date_is_before($date_read,$date_end) && date_is_later($date_read,$date_start))
		return 1;
	else {
		return 0;
	}
}

function date_is_later($date_read,$date_refer){
	// this function return 1 if the supplied $date_read is later or the same day of
	// the $date_refer date.
	// date format is: DD/MM/YYYY

	list($date['read']['day'],$date['read']['month'],$date['read']['year'])=explode("/",$date_read);
	list($date['refer']['day'],$date['refer']['month'],$date['refer']['year'])=explode("/",$date_refer);

	if($date['read']['year']>$date['refer']['year']){
		return 1;
	} elseif($date['read']['year']==$date['refer']['year']){
		if($date['read']['month']>$date['refer']['month']){
			return 1;
		} elseif($date['read']['month']==$date['refer']['month']){
			// change next row to have same day included or not
			if($date['read']['day']>=$date['refer']['day']){
				return 1;
			}
		}
	}
	return 0;
}

function date_is_before($date_read,$date_refer){
	// this function return 1 if the supplied $date_read is before or the same day of
	// the $date_refer date.
	// date format is: DD/MM/YYYY

	list($date['read']['day'],$date['read']['month'],$date['read']['year'])=explode("/",$date_read);
	list($date['refer']['day'],$date['refer']['month'],$date['refer']['year'])=explode("/",$date_refer);

	if($date['read']['year']<$date['refer']['year']){
		return 1;
	} elseif($date['read']['year']==$date['refer']['year']){
		if($date['read']['month']<$date['refer']['month']){
			return 1;
		} elseif($date['read']['month']==$date['refer']['month']){
			// change next row to have same day included or not
			// <= same day included - < same day not included
			if($date['read']['day']<=$date['refer']['day']){
				return 1;
			}
		}
	}
	return 0;
}

?>
