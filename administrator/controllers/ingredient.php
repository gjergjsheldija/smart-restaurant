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

class Ingredient extends Controller {
	
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
		$this->load->model('ingredient/ingredient_model');
		if(isset($_REQUEST['cat_id']))  
			$ingredients['query'] = $this->ingredient_model->ingredient_list_by_category($_REQUEST['cat_id']);
		else 
			$ingredients['query'] = $this->ingredient_model->ingredient_list();
		$this->load->model('category/category_model');
		$ingredients['category'] = $this->category_model->category_dropdown();				
		$ingredients['body'] = $this->load->view('ingredient/ingredient_list', $ingredients, TRUE);
		$this->load->view('main', $ingredients);
	}

	function edit() {
		$this->load->model('ingredient/ingredient_model');
		$ingredients['edit'] = $this->ingredient_model->list_one($this->uri->segment(3));
		$ingredients['query'] = $this->ingredient_model->ingredient_list();
		$this->load->model('category/category_model');
		$ingredients['category'] = $this->category_model->category_dropdown();			
		$ingredients['body'] = $this->load->view('ingredient/ingredient_list', $ingredients, TRUE);
		$this->load->view('main', $ingredients);		
	}

	function newIngredient() {
		$this->load->model('ingredient/ingredient_model');
		$ingredients['newingredient'] = 'newingredient';		
		$ingredients['query'] = $this->ingredient_model->ingredient_list();
		$this->load->model('category/category_model');
		$ingredients['category'] = $this->category_model->category_dropdown();					
		$ingredients['body'] = $this->load->view('ingredient/ingredient_list', $ingredients, TRUE);
		$this->load->view('main', $ingredients);		
	}
	
	/**
	 * krijon nje perberes te ri ty ba 2 veprime
	 * - shton entry ne tabelen ingreds
	 * - krijon elementin ne tabelen stock_objects
	 *
	 */
	function addnew() {
		$_POST['visible'] = '1';
		$_POST['override_autocalc'] = '1';
		$this->db->insert('ingreds', $_POST);

		unset($_POST['category']);
		unset($_POST['price']);
		unset($_POST['sell_price']);
		unset($_POST['visible']);
		unset($_POST['override_autocalc']);
		$_POST['ref_id'] = $this->db->insert_id();		
		$_POST['ref_type'] = '2';
		$_POST['stock_is_on'] = '1';
		$this->db->insert('stock_objects', $_POST);
		redirect('ingredient');		
	}	
	
	function save() {
		$this->db->where('id',$_POST['id']);
		$this->db->update('ingreds', $_POST);

		redirect('ingredient');
	}	
	
	function delete() {
		$query = array('deleted' => 1);
		$id = $this->uri->segment(3);
		$this->db->where('id',$id);
		$this->db->update('ingreds', $query);

		redirect('ingredient');
	}	
}
?>