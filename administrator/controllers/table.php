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
class Table extends Controller {

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
		$this->load->model('table/table_model');
		$table['query'] = $this->table_model->table_list();
		$table['body'] = $this->load->view('table/table_list', $table, TRUE);
		$this->load->view('main', $table);
	}

	function edit() {
		$this->load->model('table/table_model');
		$table['edit'] = $this->table_model->list_one($this->uri->segment(3));
		$table['query'] = $this->table_model->table_list();
		$this->load->model('user/user_model');
		$table['users'] = $this->user_model->user_list();
		$table['body'] = $this->load->view('table/table_list', $table, TRUE);
		$this->load->view('main', $table);		
	}
	
	function newTable() {
		$this->load->model('table/table_model');
		$table['newtable'] = 'newtable';		
		$table['query'] = $this->table_model->table_list();
		$this->load->model('user/user_model');
		$table['users'] = $this->user_model->user_list();		
		$table['body'] = $this->load->view('table/table_list', $table, TRUE);
		$this->load->view('main', $table);		
	}

	function save() {
		$str = "";
		foreach($_POST['locktouser'] as $lockto )
			$str .= $lockto . "," ;
		
		$_POST['locktouser'] =  substr($str,0,-1);
				
		$this->db->where('id',$_POST['id']);
		$this->db->update('sources', $_POST);

		redirect('table');
	}
	
	function addnew() {
		$str = "";
		foreach($_POST['locktouser'] as $lockto )
			$str .= $lockto . "," ;
		
		$_POST['locktouser'] =  substr($str,0,-1);
		$this->db->insert('sources', $_POST);
		redirect('table');		
	}
	
	function delete() {
		$id = $this->uri->segment(3);
		$this->db->where('id',$id);
		$this->db->delete('sources');

		redirect('table');
	}	
}

?>