<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Smart Restaurant
 *
 * An open source application to manage restaurants
 *
 * @package		SmartRestaurant
 * @author		Gjergj Sheldija
 * @copyright	Copyright (c) 2008-2012, Gjergj Sheldija
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
class Dish_Model extends Model {

	function Dish_Model () {
		parent::Model();
	}
	
	function dish_list() {
		$this->db->select('dishes.id, dishes.name, dests.name as destname, dishes.price, dishes.image, categories.name as catname')
						->from('dishes')
						->join('categories','categories.id=dishes.category','inner')
						->join('dests','dests.id=dishes.destid','inner')
						->where('dishes.deleted' , '0')
						->where('categories.deleted' , '0')
						->order_by('categories.name', 'asc')
						->order_by('dishes.name', 'asc');
		$query = $this->db->get();						
		return $query;
	}
	
	function dish_list_by_category($catid) {
		$query = $this->db->get_where('dishes', array('id' => $catid ));
		return $query->result();	
	}
	
	
	function list_one($id) {
		$this->db->select('dishes.id, dishes.name, dishes.destid, dishes.price, dishes.image, dishes.category as catid')
						->from('dishes')
						->join('categories','categories.id=dishes.category','inner')
						->join('dests','dests.id=dishes.destid','inner')
						->where('dishes.deleted' , '0')
						->where('categories.deleted' , '0')						
						->where('dishes.id', $id)
						->order_by('categories.name', 'asc')
						->order_by('dishes.name', 'asc');
		$query = $this->db->get();			
		return $query->result();
	}
}
?>