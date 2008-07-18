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

class Printer_Model extends Model {

	function Printer_Model () {
		parent::Model();
	}

	function printer_list() {
		$query = $this->db->get_where('dests', array('deleted' => '0'));
		return $query->result();
	}	
	
	function list_one($id) {
		$query = $this->db->get_where('dests', array('id' => $id ));
		return $query->result();
	}	
	
	function printer_dropdown() {
		$this->db->select('dests.id, dests.name')
				->from('dests')
				->where('dests.deleted' , '0')
				->order_by('dests.name', 'asc');
		$query = $this->db->get();
		$dests = array();

		foreach ($query->result_array() as $row) {
		   $dests[$row['id']] = $row['name'];
		}

		return $dests;
	}	
}
?>