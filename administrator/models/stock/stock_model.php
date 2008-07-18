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

class Stock_Model extends Model {
	
	function stock_model () {
		parent::Model();
	}
	
	function stock_actual() {
		$query = $this->db->get_where('stock_objects',array('deleted'=>'0',));
	
		return $query;
	}
	
	function stock_movement($from, $to) {
		$this->db->select('users.name,stock_objects.name as article ,stock_movements.quantity, stock_movements.value, stock_movements.timestamp')
				 ->from('users')
				 ->join('stock_movements','users.id = stock_movements.user')
				 ->join('stock_objects','stock_movements.obj_id = stock_objects.id')
				 ->where('stock_movements.timestamp >=',$from)
				 ->where('stock_movements.timestamp <=',$to)
				 ->order_by('article');

		$query = $this->db->get();	
		return $query;				 
	}	
}
?>