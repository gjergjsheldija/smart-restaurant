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
class Contacts_Model extends Model {
	
	function contacts_Model () {
		parent::Model();
	}
	
	function contacts_list() {
		$this->db->select('*, mgmt_people_types.name as contacttype')
				->from('account_mgmt_addressbook')
				->join('mgmt_people_types','account_mgmt_addressbook.type = mgmt_people_types.id')
				->order_by('account_mgmt_addressbook.type');
		$query = $this->db->get();
		return $query;
	}
	
	function list_one($id) {
		$query = $this->db->get_where('account_mgmt_addressbook', array('id' => $id ));
		return $query->result();
	}
	
	function contacts_dropdown() {
		$this->db->select('mgmt_people_types.id, mgmt_people_types.name')
				->from('mgmt_people_types')
				->order_by('mgmt_people_types.name', 'asc');
		$query = $this->db->get();
		$contacts = array();

		foreach ($query->result_array() as $row) {
		   $contacts[$row['id']] = lang($row['name']);
		}

		return $contacts;
	}
}
?>