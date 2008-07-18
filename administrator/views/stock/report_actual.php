<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<style type="text/css">
body {
    margin: 0.5in;
}
h1, h2, h3, h4, h5, h6, li, blockquote, p, th, td {
    font-family: Helvetica, Arial, Verdana, sans-serif; /*Trebuchet MS,*/
}
h1, h2, h3, h4 {
    color: #5E88B6;
    font-weight: normal;
}
h4, h5, h6 {
    color: #5E88B6;
}
h2 {
    margin: 0 auto auto auto;
    font-size: x-large;
}
li, blockquote, p, th, td {
    font-size: 80%;
}

table {
    width:95%;
    border-top:1px solid #e5eff8;
    border-right:1px solid #e5eff8;
    margin: 0 auto;
    border-collapse:collapse;
    font-size: 14px;
    }
tr.odd td    {
    background:#f7fbff
    }
tr.odd .column1    {
    background:#f4f9fe;
    }    
.column1    {
    background:#D8DFEA;
    }
td {
    color:#444;
    border-bottom:1px solid #e5eff8;
    border-left:1px solid #e5eff8;
    padding: 2px;
    text-align:left;
    }                
th {
    font-weight:normal;
    color: #3B5998;
    text-align:left;
    border-bottom: 1px solid #e5eff8;
    border-left:1px solid #e5eff8;
    padding: 2px 2px 2px 5px;
    }                            
thead th {
    background:#D8DFEA;
    text-align:left;
    font:bold 11px "Century Gothic","Trebuchet MS",Arial,Helvetica,sans-serif;
    color:#3B5998;
    }    
tfoot th {
    text-align:center;
    background:#f4f9fe;
    }    
tfoot th strong {
    font:bold 01px "Century Gothic","Trebuchet MS",Arial,Helvetica,sans-serif;
    margin: 0;
    color:#66a3d3;
        }        
tfoot th em {
    color:#f03b58;
    font-weight: bold;
    font-size: 10px;
    font-style: normal;
    }    
}
</style>
</head>
<body>
<h2><?=lang('actual_state'); ?></h2>
<?=form_open('stock/report_actual');?>
<br /><br />
<table>
	<colgroup>
		<col style='width:15%;' />
		<col style='width:20%;' />
		<col style='width:10%;' />
		<col style='width:10%;' />
	</colgroup>				
	<thead>
		<tr>
			<th><?=lang('name'); ?></th>
			<th align="right"><?=lang('quantity'); ?></th>
			<th align="right"><?=lang('uom'); ?></th>
			<th align="right"><?=lang('amount'); ?></th>
		</tr>
	</thead>
<?php 
	$total = 0; 
	foreach($stock_actual->result() as $row) { 
	$total += $row->value;
?>
	<tr>
		<td><?=$row->name;?></td>
		<td align="right"><?=$row->quantity;?></td>
		<td align="right"><?php 
		switch( $row->unit_type ) {
			case 0;
				echo 'cp';
				break;
			case 1;
				echo 'kg';
				break;
			case 2;
				echo 'lt';
				break;
		}
		?></td>
		<td align="right"><?=$row->value;?></td>
	</tr>
<?php } ?>	
<tr><td colspan="4"></td></tr>
<tr><td colspan="4" align="right"><strong><?=lang('total'); ?> : <?=$total; ?></strong></td></tr>
</table>
<script type="text/php">
  if ( isset($pdf) ) {

  	// Open the object: all drawing commands will
  	// go to the object instead of the current page
  	$footer = $pdf->open_object();

    $font = Font_Metrics::get_font("verdana");;
  	$size = 6;
  	$color = array(0,0,0);
  	$text_height = Font_Metrics::get_font_height($font, $size);

  	$w = $pdf->get_width();
  	$h = $pdf->get_height();
  	$y = $h - 2 * $text_height - 24;
  	$pdf->line(16, $y, $w - 16, $y, $color, 1);

  	$width = Font_Metrics::get_text_width("Page 1 of 2", $font, $size);
	$width2= Font_Metrics::get_text_width("Smart Bar (c) 2006/2008 Gjergj Sheldija                                ", $font, $size);
  	$pdf->page_text($w / 2 - $width / 2, $y, "Page {PAGE_NUM} of {PAGE_COUNT}", $font, $size, $color);
  	$pdf->page_text($w - $width2, $y, "Smart Bar (c) 2006/2008 Gjergj Sheldija", $font, $size, $color);

}
</script>
</body>
</html>