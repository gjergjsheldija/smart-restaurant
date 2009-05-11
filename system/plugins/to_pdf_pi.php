<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Smart Restaurant
 *
 * An open source application to manage restaurants
 *
 * @package		SmartRestaurant
 * @author		Gjergj Sheldija
 * @copyright	Copyright (c) 2008-2009, Gjergj Sheldija
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

ini_set("memory_limit","32M");

 function pdf_create($html, $filename, $stream=TRUE, $papersize = 'a4', $orientation = 'portrait') {
        
 		require_once("dompdf/dompdf_config.inc.php");

        $dompdf = new DOMPDF();
        $dompdf->load_html($html);
        $dompdf->set_paper($papersize, $orientation);
        $dompdf->render();

        if ($stream) {
            $options['Attachment'] = 1;
            $options['Accept-Ranges'] = 0;
            $options['compress'] = 1;
            $dompdf->stream($filename.".pdf", $options);
        } else {
            write_file("$filename.pdf", $dompdf->output());
        }
    }
?>