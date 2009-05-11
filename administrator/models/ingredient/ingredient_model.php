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
class Ingredient_Model extends Model {

	/**
	 * constructor per Ingredient_Model
	 *
	 * @return Ingredient_Model
	 */
	function Ingredient_Model () {
		parent::Model();
	}
	/**
	 * ingredient list
	 *
	 * @return array with ingredients list
	 */
	function ingredient_list() {
		$this->db->select('ingreds.id, ingreds.name, ingreds.price, ingreds.sell_price, categories.name as catname')
						->from('ingreds')
						->join('categories','categories.id=ingreds.category','inner')
						->where('ingreds.deleted' , '0')
						->where('categories.deleted' , '0')
						->order_by('categories.name', 'asc')
						->order_by('ingreds.name', 'asc');
		$query = $this->db->get();						
		return $query;
	}
	
	/**
	 * creates a dropdonw of the ingredients 
	 *
	 * @return array with ingredients list
	 */
	function ingredient_dropdown($catid) {
		$this->db->select('ingreds.id, ingreds.name')
				->from('ingreds')
				->where('ingreds.deleted' , '0')
				->where('ingreds.category',$catid->catid )
				->order_by('ingreds.name', 'asc');
		$query = $this->db->get();
		$ingreds = array();

		foreach ($query->result_array() as $row) {
		   $ingreds[$row['id']] = $row['name'];
		}

		return $ingreds;
	}	
	/**
	 * creates a dropdonw of the ingredients 
	 * used in the supply screen
	 *
	 * @return array with ingredients list
	 */
	function ingredient_dropdown_stock() {
		$this->db->select('ingreds.id, ingreds.name')
				->from('ingreds')
				->where('ingreds.deleted' , '0')
				->order_by('ingreds.name', 'asc');
		$query = $this->db->get();
		$ingreds = array();

		foreach ($query->result_array() as $row) {
		   $ingreds[$row['id']] = $row['name'];
		}

		return $ingreds;
	}	
	
	/**
	 * ingredients by category
	 *
	 * @param int $catid
	 * @return array with ingredients list
	 */
	function ingredient_list_by_category($catid) {
		$query = $this->db->get_where('ingreds', array('id' => $catid ));
		return $query->result();	
	}
	
	/**
	 * ingredients list by dish
	 *
	 * @param int $dishid
	 * @return array
	 */
	function ingredient_list_by_dish($dishid) {
		$this->db->select('ingreds.id, ingreds.name, stock_objects.unit_type, 
							stock_ingredient_quantities.quantity, stock_ingredient_quantities.id as object_id, 
							stock_objects.id as stock_id')
						->from('stock_objects')
						->join('stock_ingredient_quantities','stock_objects.id = stock_ingredient_quantities.obj_id')
						->join('ingreds','stock_objects.ref_id = ingreds.id')
						->where('ingreds.deleted' , '0')
						->where('stock_ingredient_quantities.dish_id' , $dishid)
						->order_by('ingreds.name', 'asc');		
		$query = $this->db->get();
		return $query->result();	
	}
	
	/**
	 * ingredient details
	 * by the selected id
	 *
	 * @param int $id
	 * @return object
	 */
	function list_one($id) {
		$this->db->select('ingreds.id, ingreds.name, ingreds.price, ingreds.sell_price, ingreds.category as catid')
						->from('ingreds')
						->join('categories','categories.id=ingreds.category','inner')
						->where('ingreds.deleted' , '0')
						->where('categories.deleted' , '0')						
						->where('ingreds.id', $id)
						->order_by('categories.name', 'asc')
						->order_by('ingreds.name', 'asc');
		$query = $this->db->get();			
		return $query->result();
	}
}
?>