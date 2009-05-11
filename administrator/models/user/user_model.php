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
class User_Model extends Model {

	function User_Model () {
		parent::Model();
	}

	function user_list() {
		$this->db->select('users.id, users.name, users.level')
				->from('users')
				->where('users.deleted' , '0')
				->where('users.disabled' , '0')
				->where('users.level' , '515')
				->order_by('users.name', 'asc');
		$query = $this->db->get();
		
		return $query->result_array();
	}

	function list_users() {
		$query = $this->db->get_where('users', array('deleted' => '0'));
		return $query->result();		
	}
	
	function list_one($id) {
		$query = $this->db->get_where('users', array('id' => $id ));
		return $query->result();
	}		
}
?>