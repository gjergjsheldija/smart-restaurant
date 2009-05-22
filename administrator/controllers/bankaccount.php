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
class BankAccount extends Controller {

	function __construct() {
		parent::Controller();
		$this->load->helper('html');
		$this->load->helper('MY_url_helper');
		$this->load->helper('language');
		if($this->config->item('enable_app_debug'))	
			$this->output->enable_profiler(TRUE);
	}

	function index($type  = 'account') {
		if (!$this->site_sentry->is_logged_in())
			redirect('login');
		if($type == 'account') {
			$this->load->model('bankaccount/bankaccount_model');
			$bankaccount['query'] = $this->bankaccount_model->bankaccount_list();
			$bankaccount['body'] = $this->load->view('bankaccount/bankaccount_list', $bankaccount, TRUE);
			$this->load->view('main', $bankaccount);
		}elseif($type == 'movement') {
			$this->load->model('bankaccount/bankaccount_model');
			$bankmovement['query'] = $this->bankaccount_model->bankaccount_movementlist();
			$bankmovement['body'] = $this->load->view('bankaccount/bankmovement_list', $bankmovement, TRUE);
			$this->load->view('main', $bankmovement);
		}
	}
	
	function edit() {
		$this->load->model('bankaccount/bankaccount_model');
		$bankaccount['edit'] = $this->bankaccount_model->list_one($this->uri->segment(3));
		$bankaccount['query'] = $this->bankaccount_model->bankaccount_list();
		$bankaccount['bankname'] = $this->bankaccount_model->bank_dropdown();		
		$bankaccount['body'] = $this->load->view('bankaccount/bankaccount_list', $bankaccount, TRUE);
		$this->load->view('main', $bankaccount);		
	}

	function newBankAccount() {
		$this->load->model('bankaccount/bankaccount_model');
		$bankaccount['newbankaccount'] = 'newbankaccount';		
		$bankaccount['query'] = $this->bankaccount_model->bankaccount_list();
		$bankaccount['bankname'] = $this->bankaccount_model->bank_dropdown();			
		$bankaccount['body'] = $this->load->view('bankaccount/bankaccount_list', $bankaccount, TRUE);
		$this->load->view('main', $bankaccount);		
	}
	
	function newBankMovement() {
		$this->load->model('account/account_model');
		$this->load->model('bankaccount/bankaccount_model');
		$bankmovement['newbankmovement'] = 'newbankmovement';		
		$bankmovement['query'] = $this->bankaccount_model->bankaccount_movementlist();
		$bankmovement['bankname'] = $this->bankaccount_model->bank_dropdown();	
		$bankmovement['payment_type'] = $this->account_model->payment_type('bank');	
		$bankmovement['bank_account'] = $this->account_model->bank_account();	
		$bankmovement['body'] = $this->load->view('bankaccount/bankmovement_list', $bankmovement, TRUE);
		$this->load->view('main', $bankmovement);
	}
	
	function save() {
		$this->db->where('id',$_POST['id']);
		$this->db->update('account_accounts', $_POST);

		redirect('bankaccount');
	}
	
	function addnew() {
		$this->db->insert('account_accounts', $_POST);

		redirect('bankaccount');		
	}

	function addnewMovement() {
		//add a new bank movement
		$this->db->insert('account_account_log', $_POST);	

		redirect('bankaccount/index/movement');
	}
	
	function delete() {
		$id = $this->uri->segment(3);
		$this->db->where('id', $id);
		$this->db->delete('account_accounts'); 
		redirect('bankaccount');
	}

	function report_actual() {
		$this->load->model('bankaccount/bankaccount_model');
		$bankaccount['bankaccount_actual'] = $this->bankaccount_model->bankaccount_actual();
		$bankaccount['body'] = $this->load->view('bankaccount/bankaccount_actual',$bankaccount, TRUE);
		$this->load->view('main', $bankaccount);
	}

	function report_movement() {
		$date_from = $this->input->post('date_from');
		$date_to = $this->input->post('date_to');
		
		if(!isset($date_from) || $date_from == '')
			$date_from = date('Y-m-d');
		if(!isset($date_to) || $date_to == '')
			$date_to = date('Y-m-d');
					
		$this->load->model('bankaccount/bankaccount_model');
		$bankaccount['bankaccount_actual'] = $this->bankaccount_model->bankaccount_movement($date_from,$date_to);
		$bankaccount['dt_from'] = $date_from;
		$bankaccount['dt_to'] = $date_to;		
		$bankaccount['body'] = $this->load->view('bankaccount/bankaccount_movement',$bankaccount, TRUE);
		$this->load->view('main', $bankaccount);
	}

	function report_actual_pdf() {
		$this->load->plugin('to_pdf');
		$this->load->helper('file'); 
				
		$this->load->model('bankaccount/bankaccount_model');
		$bankaccount['bankaccount_actual'] = $this->bankaccount_model->bankaccount_actual();
		$html = $this->load->view('bankaccount/report_actual',$bankaccount, TRUE);
		pdf_create($html, "bank_actual", TRUE);				
	}

	function report_movement_pdf() {
		$this->load->plugin('to_pdf');
		$this->load->helper('file'); 

		list($date_from, $date_to) = explode("_", $this->uri->segment(3));
		
		if(!isset($date_from) || $date_from == '')
			$date_from = date('Y-m-d');
		if(!isset($date_to) || $date_to == '')
			$date_to = date('Y-m-d');
					
		$this->load->model('bankaccount/bankaccount_model');
		$bankaccount['bankaccount_actual'] = $this->bankaccount_model->bankaccount_movement($date_from,$date_to);
		$bankaccount['dt_from'] = $date_from;
		$bankaccount['dt_to'] = $date_to;	
			
		$html = $this->load->view('bankaccount/report_movement',$bankaccount, TRUE);
		pdf_create($html, "bank_movement", TRUE);		
	}	
	
}
?>