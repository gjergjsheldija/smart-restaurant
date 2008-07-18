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
class Dish extends Controller {
	
	function __construct() {
		parent::Controller();
		$this->load->helper('html');
		$this->load->helper('MY_url_helper');
		$this->load->helper('language');
		//$this->output->enable_profiler(TRUE);		
	}	
	
	function index() {
		if (!$this->site_sentry->is_logged_in())
			redirect('login');			
		$this->load->model('dish/dish_model');
		if(isset($_REQUEST['cat_id']))  
			$dishes['query'] = $this->dish_model->dish_list_by_category($_REQUEST['cat_id']);
		else 
			$dishes['query'] = $this->dish_model->dish_list();
		$this->load->model('category/category_model');
		$dishes['category'] = $this->category_model->category_dropdown();				
		$dishes['body'] = $this->load->view('dish/dish_list', $dishes, TRUE);
		$this->load->view('main', $dishes);
	}

	function edit($dish) {
		if($dish)
			$dish_id = $dish;
		else 
			$dish_id = $this->uri->segment(3);
			
		$this->load->model('dish/dish_model');
		$dishes['edit'] = $this->dish_model->list_one($dish_id);
		$dishes['query'] = $this->dish_model->dish_list();
		$this->load->model('category/category_model');
		$dishes['category'] = $this->category_model->category_dropdown();
		$this->load->model('ingredient/ingredient_model');
		$dishes['ingredients'] = $this->ingredient_model->ingredient_dropdown($dishes['edit'][0]);	
		$dishes['ingredient_quantity'] = $this->ingredient_model->ingredient_list_by_dish($dish_id);	
		$this->load->model('printer/printer_model');
		$dishes['printer'] = $this->printer_model->printer_dropdown();		
		$dishes['body'] = $this->load->view('dish/dish_list', $dishes, TRUE);
		$this->load->view('main', $dishes);		
	}

	function newDish() {
		$this->load->model('dish/dish_model');
		$dishes['newdish'] = 'newdish';		
		$dishes['query'] = $this->dish_model->dish_list();
		$this->load->model('category/category_model');
		$dishes['category'] = $this->category_model->category_dropdown();		
		$this->load->model('printer/printer_model');
		$dishes['printer'] = $this->printer_model->printer_dropdown();				
		$dishes['body'] = $this->load->view('dish/dish_list', $dishes, TRUE);
		$this->load->view('main', $dishes);		
	}

	function addnew() {
		if($_FILES['image']['tmp_name']) {
			move_uploaded_file($_FILES['image']['tmp_name'],'../images/dishes/' . $_FILES['image']['name']);
			$_POST['image'] = '/images/pjatat/' . $_FILES['image']['name'];
		} 
		$this->db->insert('dishes', $_POST);

		redirect('dish');		
	}	
	
	function save() {
		if($_FILES['image']['tmp_name']) {
			move_uploaded_file($_FILES['image']['tmp_name'],'../images/dishes/' . $_FILES['image']['name']);
			$_POST['image'] = '/images/pjatat/' . $_FILES['image']['name'];
		} 
		$this->db->where('id',$_POST['id']);
		$this->db->update('dishes', $_POST);

		redirect('dish');
	}	
	
	function delete() {
		$query = array('deleted' => 1);
		$id = $this->uri->segment(3);
		$this->db->where('id',$id);
		$this->db->update('dishes', $query);

		redirect('dish');
	}

	function insertIngredient() {
		//save ingredient into stock_ingredient_quantities
		$query  = "INSERT INTO stock_ingredient_quantities( obj_id,dish_id) ";
		$query .= " SELECT stock_objects.id, " . $_POST['dish_id'];
		$query .= " FROM stock_objects ";
		$query .= " WHERE stock_objects.ref_id = " . $_POST['ingredient_id'];
		$insert = $this->db->query($query);
		
		//and update dishes(ingreds)
		$queryUpdate  = "UPDATE dishes SET ingreds = CONCAT( ingreds , '" . $_POST['ingredient_id'] . " ' )";
		$queryUpdate .= " WHERE dishes.id = '" . $_POST['dish_id'] . "'";
		$update = $this->db->query($queryUpdate);
		
		$this->edit($_POST['dish_id']);
	}
	
	function updateIngredientQuantity() {
		$data = array('quantity' => $_POST['value'] );
		$this->db->where('id', $_POST['object_id']);
		$this->db->update('stock_ingredient_quantities',$data);
		print $_POST['value'];
	}
	
	function updateIngredientUnitType() {
		$data = array('unit_type' => $_POST['value'] );
		$this->db->where('id', $_POST['stock_id']);
		$this->db->update('stock_objects',$data);
		print $_POST['value'] == '2' ? 'lt' : 'kg';	
	}
	
	function deleteIngredient($ingredient) {
		$vars = explode("-",$ingredient);
		//delete ingredient from stock_ingredient_quantities
		$this->db->select('dish_id')->from('stock_ingredient_quantities')->where('id', $vars[0]);
		$query = $this->db->get();
		$this->db->delete('stock_ingredient_quantities', array('id' => $vars[0])); 
		$tmp = $query->result();
		$this->edit($tmp[0]->dish_id);
		
		//have to delete it also from the list of the ingredients
		$queryList = $this->db->get_where('dishes',array('id'=>$vars[1]));
		$ingreds = '';
		foreach($queryList->result_array() as $row) $ingreds = $row['ingreds'];
		$ingred = explode(" ", $ingreds);
		$mods = '';
		foreach($ingred as $key=>$val) {
			if($val != $vars[2]) { $mods .= ' ' . $val; };
		}
		$this->db->query("UPDATE dishes SET ingreds = '" . $mods . "' WHERE id = '" .  $vars[1] . "'"); 

	}
}
?>