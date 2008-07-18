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


class Site_sentry {

	function Site_sentry() {
		$this->obj =& get_instance();
	}

	function is_logged_in() {
		if ($this->obj->session) {
			if ($this->obj->session->userdata('logged_in')) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}

	function login_routine() {

		$password = $this->obj->input->post('password');
		$username = $this->obj->input->post('username');

		$query = $this->obj->db->get_where('users',array('name'=>$username,'password'=>md5($password)));

		if($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				$id = $row->id;
				$credentials = array('user_id' => $id, 'logged_in' => '1');
				$this->obj->session->set_userdata($credentials);
				return TRUE;			
			}
		} else {
			return FALSE;
		}
	}
}
?>