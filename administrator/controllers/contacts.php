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
class Contacts extends Controller {

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
		$this->load->model('contacts/contacts_model');
		$contacts['query'] = $this->contacts_model->contacts_list();
		$contacts['body'] = $this->load->view('contacts/contacts_list', $contacts, TRUE);
		$this->load->view('main', $contacts);
	}
	
	function edit() {
		$this->load->model('contacts/contacts_model');
		$contacts['edit'] = $this->contacts_model->list_one($this->uri->segment(3));
		$contacts['query'] = $this->contacts_model->contacts_list();
		$contacts['conttype'] = $this->contacts_model->contacts_dropdown();		
		$contacts['body'] = $this->load->view('contacts/contacts_list', $contacts, TRUE);
		$this->load->view('main', $contacts);		
	}

	function newContact() {
		$this->load->model('contacts/contacts_model');
		$contacts['newcontact'] = 'newcontact';		
		$contacts['query'] = $this->contacts_model->contacts_list();
		$contacts['conttype'] = $this->contacts_model->contacts_dropdown();			
		$contacts['body'] = $this->load->view('contacts/contacts_list', $contacts, TRUE);
		$this->load->view('main', $contacts);		
	}

	function save() {
		$this->db->where('id',$_POST['id']);
		$this->db->update('account_mgmt_addressbook', $_POST);

		redirect('contacts');
	}
	
	function addnew() {
		$this->db->insert('account_mgmt_addressbook', $_POST);

		redirect('contacts');		
	}
	
	function delete() {
		$id = $this->uri->segment(3);
		$this->db->where('id', $id);
		$this->db->delete('account_mgmt_addressbook'); 
		redirect('contacts');
	}		
}
?>