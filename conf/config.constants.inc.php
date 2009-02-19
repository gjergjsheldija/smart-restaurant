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
define('CONF_DEBUG',						1);	// prints all debug info in the relative file and some debug data on screen
define('CONF_DEBUG_PRINT_GENERATING_TIME',			0);	// allows printing of the generating time
define('CONF_DEBUG_REPORT_NOTICES',				0);	// uses E_ALL php error reporting
define('CONF_DEBUG_LANG_DISABLED',				0);	// disables the language functions (both db and xml)
define('CONF_DEBUG_PRINT_GENERATING_TIME_ONLY_IF_HIGH',		0);	//prints the generating time only if it is higher than the below indicated level
define('CONF_DEBUG_PRINT_GENERATING_TIME_TRESHOLD',		0);
define('CONF_DEBUG_DONT_DELETE',				0);	// if active the order will never be deleted
define('CONF_DEBUG_DONT_SET_PRINTED',				0);	// if the flag is active, the flag printed in orders table won't be set
define('CONF_DEBUG_DONT_PRINT',					1);	// if active, printing won't work
define('CONF_DEBUG_PRINT_MARKUP',				0);	// if active all the unused markup will not be deleted before printing
define('CONF_DEBUG_PRINT_TICKET_DEST',				0);	// if active the print destionation will be printed
define('CONF_DEBUG_PRINT_DISPLAY_MSG',				0);
define('CONF_DEBUG_DISPLAY_MYSQL_QUERIES',			0);	// displays the number of common_query() calls
define('CONF_DEBUG_DISABLE_FUNCTION_INSERT',			0);	// enables the use of functions besides of numbers (1+1 instead of 2)
define('CONF_DEBUG_PRINT_PAGE_SIZE',				0);  // if on prints the size of the generated page (images excluded)


define('CONF_SHOW_DEFAULT_ON_MISSING',				1);	// if a lang value in the db is empty, writes the corresponding value in the default language instead of the lang code
define('CONF_TOPLIST_HIDE_PRIORITY',				0);	// sets if priority button should be displayed in the toplist box
define('CONF_TOPLIST_HIDE_QUANTITY',				0);	// sets if quantity button should be displayed in the toplist box
define('CONF_TOPLIST_SAVED_NUMBER',				1000);	// quantity of orders to be saved for toplist statistics
define('CONF_ALLOW_EASY_DELETE',				true);	// if true shows the little trash icon when an order has quantity 1
define('CONF_XML_TRANSLATIONS',					true);	// if true uses the xml language files instead of the database. It is recomended to leave this funciton off unless you know what you are doing
define('CONF_PRINT_BARCODES',					false);	// if true prints the barcode with the order ID for each order
														// it requires a barcode-ready printer to work
														// and is as of today a useless feature
define('CONF_COLOUR_PRINTED',					1);	// if on the user can see the elapsed time from the printing of the order ticket as a linear color
define('CONF_COLOUR_PRINTED_COLOUR',				'yellow');	// possible values: red, green, blue, magenta, yellow, cyan, grey. default: yellow
define('CONF_COLOUR_PRINTED_MAX_TIME',				20);	// after how much time in mins should the max colour be reached
define('CONF_TIME_SINCE_PRINTED',				1);	// if on the elapsed time since printing will be written aside the dish name in the orders list
define('CONF_ENCRYPT_PASSWORD',					false);	// if true the passwords will be encrypted with the best available method, otherwise a MD5 checksum will be prepared
														// the checksum is a bit less secure, but ensures that the password will be the same on every machine,
														// otherwise changing the OS or upgrading it could cause all the passwords to be unusable (recreate the users is the only solution)
define('CONF_DISPLAY_MYSQL_ERRORS',				false);  // if on the mysql errors will be displayed to the users and logged to file, otherwise they will be only logged to errors file
define('CONF_SQL_RESUME_ENABLED',				false);	// if on the sql upgrades and restores will be stopped and resumed to allow progress display (HIGHLY EXPERIMENTAL!!!)
define('CONF_SHOW_SUMMARY_ON_LIST',				false);	// if on a summary of the data about the ingredients/dishes will be displayed in the tables in admin section (slows the page generation by a factor of about 4)
define('CONF_SHOW_PERCENT_INSERTED_ON_LIST',			false);	// if on the percent of inserted ingredient quantities will be displayed in the table in admin section (slows the page generation by a factor of about 4)
define('CONF_UNIT_MASS',					'g');	// measure unit for weigths
define('CONF_UNIT_VOLUME',					'l');	// measure unit for volumes
define('CONF_FORCE_UPGRADE',					false);	// if true forces upgrading, otherwise only displays suggestion with ink in messages
define('CONF_STOCK_QUANTITY_ALARM',				10);	// treshold for low quantity in stock messages
define('CONF_FAST_ORDER',					true);	// enables the fast order form in the orders page (also disables the keyboad shurtcuts on the orders form)
define('ADMINISTRATOR', 						1);
define('IMAGE_UPLOAD', ROOTDIR.'/images/categories/');
define('SHOW_CHANGE',							1); //shows or hides the change
/*
Cache system for db queries
0: disable the db query caching system (low performance)
1: cache on page (cache is reset on page reload)
2: cache on session (cache is reset on user connection) (high performance, but updates from other users cannot be seen until disconnection!)
3: cache data on page and lang on session (suggested)
*/
define('CONF_CACHE_TYPE',3);

/************************************************************************************
* YOU SHOULDN'T MODIFY ANYTHING BELOW THIS LINE!
* (unless you really know what you're doing)
*************************************************************************************/
define('ERROR_FILE',ROOTDIR.'/error.log');
define('DEBUG_FILE',ROOTDIR.'/debug.log');

define('MIN_SEARCH_LENGTH',0);

define('SERVICE_ID',-1);
define('MOD_ID',-2);
define('DISCOUNT_ID',-3);

define('LANG_TABLES_NUMBER',7);			// The number of tables added per language to the db
define('LANG_FILES_NUMBER',1);			// The number of files added per language to the lang dir

define('AUTOSELECT_FIRST',0);			// if 1: selects the first item in mods' quantity to be modified
						// else selects the last item in mods' quantity to be modified


// if yes checks for translation problems in the tables every time the translators page is loaded (heavy CPU load).
// otherwise prints a message inviting them to do that
define('CONF_TRANSLATE_ALWAYS_CHECK_TABLES',0);


define('REFRESH_TIME',0.2);

// max displayed quanitty in quantity <select > boxes.
// This is NOT the maximux allowed quantity, so don't use this for security matters.
define('MAX_QUANTITY',50);

define('USER_BIT_WAITER',0);
define('USER_BIT_CASHIER',1);
define('USER_BIT_STOCK',2);
define('USER_BIT_CONTACTS',3);
define('USER_BIT_MENU',4);
define('USER_BIT_USERS',5);
define('USER_BIT_ACCOUNTING',6);
define('USER_BIT_TRANSLATION',7);
define('USER_BIT_CONFIG',8);
define('USER_BIT_MONEY',9);

define('USER_BIT_LAST',9);

define('SHOW_ALL_USERS',0);
define('SHOW_WAITER_ONLY',1);
define('SHOW_ADMIN_ONLY',2);

define('ERROR_LEVEL_USER',0);
define('ERROR_LEVEL_DEBUG',1);
define('ERROR_LEVEL_ERROR',2);

define('TABLE_INGREDIENTS',1);
define('TABLE_DISHES',2);
define('TABLE_CATEGORIES',3);
define('TABLE_TABLES',4);
define('TABLE_USERS',5);
define('TABLE_AUTOCALC',6);
define('TABLE_VAT_RATES',7);
define('TABLE_PRINTERS',8);
define('TABLE_STOCK_OBJECTS',9);
define('TABLE_STOCK_DISHES',10);

$halttime=2;

define('TYPE_NONE',0);
define('TYPE_DISH',1);
define('TYPE_INGREDIENT',2);

define('INGRED_TYPE_INCLUDED',1);
define('INGRED_TYPE_AVAILABLE',2);


define('UNIT_TYPE_NONE',0);
define('UNIT_TYPE_MASS',1);
define('UNIT_TYPE_VOLUME',2);
define('UNIT_TYPE_MONEY',3);

$allowed_not_upgraded  = array('upgrade.php','connect.php','export_db.php');

global $convertion_constants;
$convertion_constants = array (
	// weight US
	'oz-kg'=>0.02834952313,
	'lb-kg'=>0.45359237,
	// weight IS
	'mg-kg'=>0.000001,
	'cg-kg'=>0.00001,
	'dg-kg'=>0.0001,
	'g-kg'=>0.001,
	'dag-kg'=>0.001,
	'hg-kg'=>0.1,
	// volume US
	'gal-l'=>3.785411784,
	'floz-l'=>0.02957352956,
	// volume IS
	'ml-l'=>0.001,
	'cl-l'=>0.01,
	'dl-l'=>0.1,
	'hl-l'=>100.0,
);

global $unit_types_volume;
$unit_types_volume = array ('gal','floz','ml','cl','dl','l','hl');
global $unit_types_mass;
$unit_types_mass = array ('oz','lb','mg','cg','dg','g','dag','hg','kg');

define('CONF_HTTP_ROOT_DIR',ROOTDIR.'/');

define('CONF_JS_URL',CONF_HTTP_ROOT_DIR."generic.js");
define('CONF_JS_URL_WAITER',CONF_HTTP_ROOT_DIR."waiter.js");
define('CONF_CSS_URL',CONF_HTTP_ROOT_DIR."styles.css");

define('CONF_JS_URL_CONFIG',"./generic.js");

// images used

define('IMAGE_CUSTOMER_KNOWN',CONF_HTTP_ROOT_DIR."images/personal.png");
define('IMAGE_MENU',CONF_HTTP_ROOT_DIR."images/gohome.png");
define('IMAGE_NO',CONF_HTTP_ROOT_DIR."images/agt_action_fail.png");
define('IMAGE_OK',CONF_HTTP_ROOT_DIR."images/agt_action_success.png");
define('IMAGE_PRINT',CONF_HTTP_ROOT_DIR."images/print.png");
define('IMAGE_SOURCE',CONF_HTTP_ROOT_DIR."images/source.png");
define('IMAGE_TRASH',CONF_HTTP_ROOT_DIR."images/trash.png");
define('IMAGE_LITTLE_TRASH',CONF_HTTP_ROOT_DIR."images/little_trash.png");
define('IMAGE_YES',CONF_HTTP_ROOT_DIR."images/agt_action_success.png");
define('IMAGE_BACK',CONF_HTTP_ROOT_DIR."./images/back.jpg");
define('IMAGE_CLOSE',CONF_HTTP_ROOT_DIR."images/newclose.png");
define('IMAGE_MINUS',CONF_HTTP_ROOT_DIR."images/down.png");
define('IMAGE_PLUS',CONF_HTTP_ROOT_DIR."images/up.png");
define('IMAGE_FIND',CONF_HTTP_ROOT_DIR."images/find.png");
define('IMAGE_NEW',CONF_HTTP_ROOT_DIR."images/new.png");
define('IMAGE_LOGOUT',CONF_HTTP_ROOT_DIR."images/logout.png");
define('IMAGE_SHOW_ORDERS',CONF_HTTP_ROOT_DIR."images/show.png");
define('IMAGE_HIDE_ORDERS',CONF_HTTP_ROOT_DIR."images/hide.png");
define('IMAGE_TABLE',CONF_HTTP_ROOT_DIR."images/tavolina.png");
define('IMAGE_LOGIN',CONF_HTTP_ROOT_DIR."images/personal.png");


// all the colors used in background and tables

define('COLOR_TABLE_GENERAL','#FFCC99');

define('COLOR_TABLE_TOTAL','#FFEEBB');
define('COLOR_HIGHLIGHT','#DDDDDD');
define('COLOR_BACK_OK','#6FFA7D');
define('COLOR_BACK_ERROR','#FF0D11');
define('COLOR_ORDER_PRINTED','#FFFFFF');
define('COLOR_ORDER_TO_PRINT','#6FFA7D');
define('COLOR_ORDER_SUSPENDED','#FF9966');
define('COLOR_ORDER_EXTRACARE','#2206DB');
define('COLOR_ERROR','#FF9966');
define('COLOR_OK','#6FFA7D');
define('COLOR_TABLE_FREE','#FFFFFF');
define('COLOR_TABLE_MINE','#6FFA7D');
define('COLOR_TABLE_OTHER','#FF9966');
define('COLOR_TABLE_CLOSED_OPENABLE','#FFFF0C');
define('COLOR_TABLE_NOT_OPENABLE','#FF9966');
define('COLOR_TABLE_GENERIC_NOT_PRICED','#8890FF');

define('COLOR_ORDER_PRIORITY_PRINTED','#FF9966');
define('COLOR_ORDER_PRIORITY_1','#FFFFFF');
define('COLOR_ORDER_PRIORITY_2','#00FFFF');
define('COLOR_ORDER_PRIORITY_3','#FF0000');

define('MGMT_COLOR_BACKGROUND','#FEEFAC');
$mgmt_color_background="#FEEFAC";
define('MGMT_COLOR_TABLEBG','#FFCA68');
$mgmt_color_tablebg="#FFCA68";
define('MGMT_COLOR_CELLBG0','#FFE9B7');
$mgmt_color_cellbg0="#FFE9B7";
define('MGMT_COLOR_CELLBG1','#FAFF97');
$mgmt_color_cellbg1="#FAFF97";

?>
