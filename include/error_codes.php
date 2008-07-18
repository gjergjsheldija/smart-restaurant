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
Error codes:
*/
// tables
define('ERR_TABLE_NOT_FOUND',100);
define('ERR_TABLE_ALREADY_ASSOCIATED',101);
define('ERR_NOT_ALLOWED_TO_DISSOCIATE',102);
define('ERR_TABLE_IS_LOCKED',103);
define('ERR_TAKEAWAY_SURNAME_NOT_SET',104);
define('ERR_TAKEAWAY_CHECK_SURNAME',105);
define('ERR_CUSTOMER_NOT_SPECIFIED',106);

// orders
define('ERR_ORDER_NOT_FOUND',200);
define('ERR_GENERIC_ORDER_NOT_PRICED_FOUND',201);
define('ERR_SYNCING_SUBORDERS',202);
define('ERR_COULD_NOT_CREATE_ORDER_OBJECT',203);
define('ERR_NO_ORDER_FOUND',204);
define('ERR_NO_ORDER_CHOSEN',205);
define('ERR_NOT_ALLOWED_TO_CHANGE_FIELD',206);

// templates
define('ERR_PARSING_TEMPLATE',300);
define('ERR_NO_TEMPLATE_SET',302);
define('ERR_CANNOT_OPEN_TEMPLATE_FILE',303);
define('ERR_NO_TEMPLATE_VAR_SET',303);

// printing
define('ERR_PRINTING_ERROR',400);
define('ERR_ORDER_NOT_SET_AS_PRINTED',401);
define('ERR_NO_ORDERS_PRINTED_CATEGORY',402);
define('ERR_PRINTER_NOT_FOUND',403);
define('ERR_PRINTER_NOT_FOUND_FOR_SELECTED_TYPE',404);
define('ERR_NO_ORDER_SELECTED',405);
define('ERR_NO_PRINT_DESTINATION_FOUND',406);
define('ERR_COULD_NOT_OPEN_PRINTER',407);

// mods
define('ERR_MOD_NOT_CREATED',500);

// users
define('ERR_USER_NOT_FOUND',600);
define('ERR_WRONG_PASSWORD',601);
define('ERR_NO_USER_PROVIDED',602);
define('ERR_NO_PASSWORD',603);

// stock
define('ERR_NAME_IS_BLANK',700);
define('ERR_NO_STOCK_OBJECT_CHOSEN',701);
define('ERR_NO_TYPE_SPECIFIED',702);
define('ERR_OBJECT_ALREADY_EXISTS',703);

// upgrades
define('ERR_SQL_UPGRADE_VARIABLE_NOT_AVAILABLE',800);
define('ERR_SQL_CONTINUING',801);

// other
define('ERR_MYSQL',1000);
define('ERR_ACCESS_DENIED',1001);
define('ERR_UNKNOWN',1002);
define('ERR_UNEXPECTED_VERSION_NUMBER',1003);

?>