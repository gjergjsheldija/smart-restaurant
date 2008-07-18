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
class Category extends Controller {

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
		$this->load->model('category/category_model');
		$categories['query'] = $this->category_model->category_list();
		$categories['body'] = $this->load->view('category/category_list', $categories, TRUE);
		$this->load->view('main', $categories);
	}

	function edit() {
		$this->load->model('category/category_model');
		$categories['edit'] = $this->category_model->list_one($this->uri->segment(3));
		$categories['query'] = $this->category_model->category_list();
		$categories['body'] = $this->load->view('category/category_list', $categories, TRUE);
		$this->load->view('main', $categories);		
	}
	
	function newCat() {
		$this->load->model('category/category_model');
		$categories['newcat'] = 'newcategory';		
		$categories['query'] = $this->category_model->category_list();
		$categories['body'] = $this->load->view('category/category_list', $categories, TRUE);
		$this->load->view('main', $categories);		
	}

	function save() {
		if($_FILES['image']['tmp_name']) {
			move_uploaded_file($_FILES['image']['tmp_name'],'../images/categories/' . $_FILES['image']['name']);
			$_POST['image'] = '/images/kategorite/' . $_FILES['image']['name'];
		} 
		$this->db->where('id',$_POST['id']);
		$this->db->update('categories', $_POST);

		redirect('category');
	}
	
	function addnew() {
		if($_FILES['image']['tmp_name']) {
			move_uploaded_file($_FILES['image']['tmp_name'],'../images/categories/' . $_FILES['image']['name']);
			$_POST['image'] = '/images/kategorite/' . $_FILES['image']['name'];
		} 
		$this->db->insert('categories', $_POST);

		redirect('category');		
	}
	
	function delete() {
		$query = array('deleted' => 1);
		$id = $this->uri->segment(3);
		$this->db->where('id',$id);
		$this->db->update('categories', $query);

		redirect('category');
	}	
}

?>