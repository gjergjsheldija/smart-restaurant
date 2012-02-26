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

class Ingredient extends Controller {
	
	function __construct() {
		parent::Controller();
		$this->load->helper(array('html','MY_url_helper','language'));		
		
		$language = $this->session->userdata('language');
		if($language == '' ) $language = 'english';
		$this->lang->load('smartrestaurant', $language);		
		
		if($this->config->item('enable_app_debug'))
			$this->output->enable_profiler(TRUE);		
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
		$ingredients['category'] = $this->category_model->category_array();
		$ingredients['category_names'] = $this->category_model->category_array();			
		$ingredients['body'] = $this->load->view('ingredient/ingredient_list', $ingredients, TRUE);
		$this->load->view('main', $ingredients);
	}

	function edit() {
		$this->load->model('ingredient/ingredient_model');
		$ingredients['edit'] = $this->ingredient_model->list_one($this->uri->segment(3));
		$ingredients['query'] = $this->ingredient_model->ingredient_list();
		$ingredients['dish_list'] = $this->ingredient_model->list_dishes_of_ingredient($this->uri->segment(3));
		
		$this->load->model('category/category_model');
		$ingredients['category'] = $this->category_model->category_array();	
		$ingredients['category_names'] = $this->category_model->category_array();		
		$ingredients['stockison'] = array( 0 => lang('yes'), 1 => lang('no') );
		$ingredients['body'] = $this->load->view('ingredient/ingredient_list', $ingredients, TRUE);
		
		$this->load->view('main', $ingredients);		
	}

	function newIngredient() {
		$this->load->model('ingredient/ingredient_model');
		$ingredients['newingredient'] = 'newingredient';		
		$ingredients['query'] = $this->ingredient_model->ingredient_list();
		$this->load->model('category/category_model');
		$ingredients['category'] = $this->category_model->category_array();		
		$ingredients['category_names'] = $this->category_model->category_array();	
		$ingredients['stockison'] = array( 0 => lang('yes'), 1 => lang('no') );		
		$ingredients['body'] = $this->load->view('ingredient/ingredient_list', $ingredients, TRUE);
		$this->load->view('main', $ingredients);		
	}
	
	/**
	 * creating a new ingredient
	 * - new entry in the ingreds table
	 * - new entry in the stock_objects
	 *
	 */
	function addnew() {
		
		$stock_is_on = $_POST['stock_is_on'];
		$_POST['visible'] = '1';
		$_POST['override_autocalc'] = '1';
		unset($_POST['stock_is_on']);
		$this->db->insert('ingreds', $_POST);

		unset($_POST['category']);
		unset($_POST['price']);
		unset($_POST['sell_price']);
		unset($_POST['visible']);
		unset($_POST['override_autocalc']);
		$_POST['ref_id'] = $this->db->insert_id();		
		$_POST['ref_type'] = '2';
		$_POST['stock_is_on'] = $stock_is_on;
		$this->db->insert('stock_objects', $_POST);
		redirect('ingredient');		
	}	
	
	function save() {
		
		$stock_is_on = array ('stock_is_on' => $_POST['stock_is_on']);
		unset($_POST['stock_is_on']);
		
		$this->db->where('id',$_POST['id']);
		$this->db->update('ingreds', $_POST);
	
		$this->db->where('ref_id',$_POST['id']);
		$this->db->update('stock_objects', $stock_is_on);
		
		redirect('ingredient');
	}	
	
	function delete() {
		$query = array('deleted' => 1);
		$id = $this->uri->segment(3);
		
		//delete it from the ingreds table
		$this->db->where('id',$id);
		$this->db->update('ingreds', $query);
		
		//and from the stock objects table
		$this->db->where('ref_id',$id);
		$this->db->update('stock_objects',$query);

		redirect('ingredient');
	}	
}
?>