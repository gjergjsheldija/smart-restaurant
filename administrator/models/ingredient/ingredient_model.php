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
		$this->db->select('ingreds.id, ingreds.name, ingreds.price, ingreds.sell_price, ingreds.category as catname')
						->from('ingreds')
						->where('ingreds.deleted' , '0')
						->order_by('ingreds.category', 'asc')
						->order_by('ingreds.name', 'asc');
		$query = $this->db->get();						
		return $query;
	}
	
	/**
	 * creates a dropdown of the ingredients 
	 *
	 * @return array with ingredients list
	 */
	function ingredient_dropdown($catid) {
		$this->db->select('ingreds.id, ingreds.name')
				->from('ingreds')
				->where('ingreds.deleted' , '0')
				->where('ingreds.category',$catid->catid )
				->or_where('ingreds.category', '0')
				->order_by('ingreds.name', 'asc');
		$query = $this->db->get();
		$ingreds = array();

		foreach ($query->result_array() as $row) {
		   $ingreds[$row['id']] = $row['name'];
		}

		return $ingreds;
	}	
	/**
	 * creates a dropdown of the ingredients 
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
	 * ingredients list by dish id
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
	 * by the selected ingredient id
	 *
	 * @param int $id
	 * @return object
	 */
	function list_one($id) {
		$this->db->select('ingreds.id, ingreds.name, ingreds.price, ingreds.sell_price, ingreds.category as catid, stock_objects.stock_is_on, stock_objects.quantity, stock_objects.unit_type')
						->from('ingreds')
						->join('stock_objects','stock_objects.ref_id = ingreds.id','inner')
						->where('ingreds.deleted' , '0')
						->where('ingreds.id', $id)
						->order_by('ingreds.name', 'asc');
						
		$query = $this->db->get();			
		return $query->result();
	}
	
	/**
	 * list of dishes which contain the selected 
	 * the selected ingredient id
	 *
	 * @param int $id
	 * @return object
	 */	
	function list_dishes_of_ingredient($ingred_id) {
		$this->db->select('dishes.name, dishes.id, stock_objects.unit_type, stock_ingredient_quantities.quantity')
				->from('stock_objects')
				->join('stock_ingredient_quantities','stock_objects.id = stock_ingredient_quantities.obj_id','inner')
				->join('ingreds','stock_objects.ref_id = ingreds.id')
				->join('dishes','dishes.id = stock_ingredient_quantities.dish_id')
				->where('ingreds.deleted','0')
				->where('ingreds.id',$ingred_id)
				->order_by('ingreds.name', 'asc');
				
		$query = $this->db->get();						
		return $query;
	}
}
?>