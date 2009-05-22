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

class Printer extends Controller {

	function __construct() {
		parent::Controller();
		$this->load->helper('html');
		$this->load->helper('directory');	
		$this->load->helper('MY_url_helper');	
		$this->load->helper('language');
		if($this->config->item('enable_app_debug'))			
			$this->output->enable_profiler(TRUE);
	}

	function index() {
		if (!$this->site_sentry->is_logged_in())
			redirect('login');		
		$this->load->model('printer/printer_model');
		$printer['query'] = $this->printer_model->printer_list();
		$printer['body'] = $this->load->view('printer/printer_list', $printer, TRUE);
		$this->load->view('main', $printer);
	}

	function edit() {
		$this->load->model('printer/printer_model');
		$printer['edit'] = $this->printer_model->list_one($this->uri->segment(3));
		$printer['query'] = $this->printer_model->printer_list();

		foreach(directory_map('../drivers/') as $tmp) 
			$drivers[substr($tmp, 0, -4)] = substr($tmp, 0, -4);

		$printer['driver'] = $drivers;
		
		foreach(directory_map('../templates/', TRUE) as $tmp) 
			$templates[$tmp]= $tmp;

		$printer['template'] = $templates;

		$printer['body'] = $this->load->view('printer/printer_list', $printer, TRUE);
		$this->load->view('main', $printer);		
	}
	
	function newPrinter() {
		$this->load->model('printer/printer_model');
		$printer['newprinter'] = 'newprinter';		
		$printer['query'] = $this->printer_model->printer_list();	
		
		foreach(directory_map('../drivers/') as $tmp) 
			$drivers[substr($tmp, 0, -4)] = substr($tmp, 0, -4);

		$printer['driver'] = $drivers;
		
		foreach(directory_map('../templates/', TRUE) as $tmp) 
			$templates[$tmp]= $tmp;

		$printer['template'] = $templates;		
		
		$printer['body'] = $this->load->view('printer/printer_list', $printer, TRUE);
		$this->load->view('main', $printer);		
	}

	function save() {				
		$this->db->where('id',$_POST['id']);
		$this->db->update('dests', $_POST);

		redirect('printer');
	}
	
	function addnew() {
		$this->db->insert('dests', $_POST);
		
		redirect('printer');		
	}
	
	function delete() {
		$query = array('deleted' => 1);
		$id = $this->uri->segment(3);
		$this->db->where('id',$id);
		$this->db->update('dests', $query);

		redirect('printer');
	}	
}

?>