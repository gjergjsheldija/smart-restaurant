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

header("Content-type: text/css;");
?>

<meta http-equiv="content-type" content="text/css">

/*-------------------------------------------------
PAGE BODY
-------------------------------------------------*/
body {
	font-family:"Lucida Grande", verdana, arial, helvetica, sans-serif;
	color:#333;
	background-color:#e5e5e5;
	text-align:center;
	margin:0 auto;
	padding:0; 
}

/* Make sure the table cells show the right font */
td {
	font-family:"Lucida Grande", verdana, arial, helvetica, sans-serif; 
}

.login div.Container {
	width:500px;
	min-width:inherit;
	margin:60px auto 20px; 
}
	
.login a {
	font-size:16px;
	line-height:24px;
	color:#666;
}	

.login p {
	font-size:16px;
	line-height:24px;
	color:#666;
}	

dd {
	font-size:11px;
	line-height:24px;
	color:#666;
	margin:0 0 5px 80px; 
}

.aligncenter {
    align: center
}

.modified {
    font-size: smaller
}

.color_table_cell {
    width: 15px;
    height:15px;
}
.mgmt_main_table {
    border: 0px;
    background: #FFCA68
}
.mgmt_main_table tbody {
    overflow: auto;
    height: 350px
}
.mgmt_main_table tbody tr {
    height: 1em
} /* work around IE bug */

.mgmt_printable_table {
    font-size: 10 pt;
    border: 0px #000 solid background: #eee;
    background: #AAAAAA
}
.break {
    page-break-before:always;
}

.mgmt_printable_tablebg {
    border: 0px;
}
.mgmt_printable_cellbg0 {
    border: 0px #000 solid;
    height: 1em;
    background: #CCCCCC
}
.mgmt_printable_cellbg1 {
    border: 0px #000 solid;
    height: 1em;
    background: #FFFFFF
}

.admin_table {
    border-width: 0px;
    background-color: #FFFFFF;
}
.admin_th {
    text-align: left;
    border-width: 0px;
}
.admin_tr_0 {
    border-width: 0px;
    background-color: #EDF1ED;
}
.admin_tr_1 {
    border-width: 0px;
    background-color: #FDFFD5;
}
.admin_tr_highlight {
    background-color: #DDDDDD
}
.admin_td_0 {
    vertical-align: text-bottom;
    border-width: 0px;
}
.admin_td_1 {
    vertical-align: text-bottom;
    border-width: 0px;
}

.admin_ingreds_list {
    font-size: 85%;
}

* {
	font-family: sans-serif, arial, verdana;
	font-size: 12.5px;
}


a:link {
    color: #0E17BF;
    text-decoration: none
}

a:visited {
    color: #0E17BF;
    text-decoration: none
}

a:hover {
    color: #CC0000;
    text-decoration: none
}

.invisible {
    font-size: 0pt;
    color: #FFFFFF;
    text-decoration: none;
}

.preferred_answer {
    font-size: 150%;
}

.error_msg {
    color: red;
    align: center
}
.page_title {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 150%;
    font-weight: bold
}

.form {
    background-color: #FFCC99;
    color: #333333;
    border: 1px #CC0000 solid;
    font-family: "Lucida Console", "Courier New", "Courier", "mono"
}

.mgmt_body_index {
    background: #FEEFAC
}
.mgmt_color_tablebg {
    border: 0px;
    background: #FFCA68
}
.mgmt_color_cellbg0 {
    height: 1em;
    background: #FFE9B7
}
.mgmt_color_cellbg1 {
    height: 1em;
    background: #FAFF97
}

.help_text {
    font-size: 100%;
}
.help_text * {
    font-size: 100%;
}
.help_bg {
    background-color: #333399
}
.help_fg {
    background-color: #FFFFCC
}
.help_caption {
    vertical-align: middle;
    color: #FFFFFF;
    font-size: 100%;
}
.help_close A {
    color: #FFFFFF;
    font-size: 100%;
}

/* POS */

.pos {
    font-size : 30px;
    height : 40px; 
    width : 40px;       
    font-weight: bold;  

}

.poscheck {
    font-size : 30px;
    height : 30px; 
    width : 30px;       
    font-weight: bold;  

}

input, textarea, select, radio{
	font:1.5em Arial,Helvetica,FreeSans,sans-serif;
	padding:.2em
}

fieldset{
	padding:.8em 0 .8em .6em;
	border-top:2px solid #621D36
}

legend{
	font-size:1.5em;
	line-height:1.5em;
	padding-left:.2em;
	padding-right:.2em
}

.informational.message{
background:#d4e8ff url(images/information.png) no-repeat top left;
border:solid #666;border-width:1px 2px 2px 1px;color:black;margin:0 auto 10px auto;padding:0 10px 10px 20px;width:30em}  {

/* END POS */