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

class Stock_Model extends Model {
	
	function stock_model () {
		parent::Model();
	}
	
	function stock_actual() {
		
		$sql  = "SELECT stock_objects.name AS 'name', stock_objects.unit_type  AS 'uom', ";
		$sql .= "ROUND(stock_objects.quantity, 2) AS 'quantity', ";
		$sql .= "IF(ingreds.sell_price = 0,ROUND(SUM(dishes.price / stock_ingredient_quantities.quantity),2),ingreds.sell_price) AS 'sell_price', ";
		$sql .= "ROUND(SUM(stock_objects.value), 2) AS 'buy_price', ";
		$sql .= "IF(ingreds.sell_price = 0,ROUND(SUM(dishes.price / stock_ingredient_quantities.quantity)*SUM(stock_objects.quantity),2),ROUND(ingreds.sell_price*stock_objects.quantity,2)) AS 'value_price_sell', ";
		$sql .= "ROUND((SUM(stock_objects.quantity) * stock_objects.value),2) AS 'value_price_buy', ";
		$sql .= "COUNT(dishes.id) AS 'total', ";
		$sql .= "dishes.destid ";
		$sql .= "FROM stock_objects ";
		$sql .= "INNER JOIN stock_ingredient_quantities ";
		$sql .= "ON stock_objects.id = stock_ingredient_quantities.obj_id ";
		$sql .= "INNER JOIN dishes ON stock_ingredient_quantities.dish_id = dishes.id ";
		$sql .= "INNER JOIN ingreds ON ingreds.id = stock_objects.ref_id " ;
		$sql .= "WHERE stock_objects.deleted = '0' ";
		$sql .= "AND dishes.deleted = '0' ";
		$sql .= "AND stock_objects.stock_is_on = '1' ";
		$sql .= "GROUP BY stock_objects.name, stock_objects.unit_type ";
		$sql .= "ORDER BY dishes.category, dishes.name ";
		
		
		$query = $this->db->query($sql);
	
		return $query;
	}
	
	function stock_movement($from, $to) {
		$this->db->distinct();
		$this->db->select('stock_objects.name as article, users.name ,stock_movements.quantity, stock_movements.value, stock_movements.timestamp')
				 ->from('users')
				 ->join('stock_movements','users.id = stock_movements.user')
				 ->join('stock_objects','stock_movements.obj_id = stock_objects.id')
				 ->where('stock_movements.timestamp >=',$from)
				 ->where('stock_movements.timestamp <=',$to)
				 ->where('stock_objects.stock_is_on =','1')
				 ->order_by('article');

		$query = $this->db->get();	
		return $query;				 
	}	
}
?>