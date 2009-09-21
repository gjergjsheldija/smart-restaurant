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
class Account extends Controller {

	function __construct() {
		parent::Controller();
		$this->load->helper(array('html','date','MY_url_helper','language')); 
		
		$language = $this->session->userdata('language');
		if($language == '' ) $language = 'english';
		$this->lang->load('smartrestaurant', $language);
				
		if($this->config->item('enable_app_debug'))
			$this->output->enable_profiler(TRUE);
	}

	function index() {
		if (!$this->site_sentry->is_logged_in())
			redirect('login');		
		//ap = mp
		//ar = ma
		$id = $this->uri->segment(3);
		$account['acctype'] = $id;
		$this->load->model('account/account_model');
		$account['query'] = $this->account_model->account_list($id);
		$account['body'] = $this->load->view('account/account_list', $account, TRUE);
		$this->load->view('main', $account);
	}
	
	function newAccount($type) {
		$id = $this->uri->segment(3);
		$account['acctype'] = $id;
		$this->load->model('account/account_model');
		$account['newaccount'] = 'newaccount';		
		$account['query'] = $this->account_model->account_list($id);
		$account['person'] = $this->account_model->account_who();	
		$account['payment_type'] = $this->account_model->payment_type();	
		$account['bank_account'] = $this->account_model->bank_account();
		$account['body'] = $this->load->view('account/account_list', $account, TRUE);
		$this->load->view('main', $account);		
	}

	function addnew() {
		$id = $this->uri->segment(3);

		$this->load->model('account/account_model');

		$insertArray = array (
			'number' => $_POST['number'],
			'date' => $_POST['date'],
			'who' => $this->account_model->getUserName($_POST['who']),
			'description' => $_POST['description'],
			'type' => $_POST['payment_type']
		);
		
		//AP or AR
		if($id == 'ap')
			$amount = -$_POST['amount'];
		elseif($id == 'ar')
			$amount = $_POST['amount'];
		
		//payed or not
		if(isset($_POST['paid'])) { 
			$insertArray['paid'] =  '1';
			$insertArray['debit'] = '0';
		} elseif(!isset($_POST['paid'])) {
			$insertArray['paid'] =  '0';
			$insertArray['debit'] = '1';
		}
		
		//bank payment ?
		if(isset($_POST['bank_account']) && $_POST['payment_type'] != 3) {
			$insertArray['bank_amount'] = $amount;
			$insertArray['account_id'] =  $_POST['bank_account'];
		}elseif(isset($_POST['payment_type']) && $_POST['payment_type'] == 3) {
			$insertArray['cash_amount'] = $amount;
		}
		
		$this->db->insert('account_mgmt_main', $insertArray); 
		redirect('account/index/' . $id);
	}

	function report_actual() {
		$date_from = $this->input->post('date_from');
		$date_to = $this->input->post('date_to');
		
		if(!isset($from) || $from == '')
			$from = date('Y-m-d');
		if(!isset($to) || $to == '')
			$to = date('Y-m-d');
					
		$this->load->model('account/account_model');
		$account['ap'] = $this->account_model->total_ap($date_from, $date_to);
		$account['ar'] = $this->account_model->total_ar($date_from, $date_to);
		$account['dt_from'] = $date_from;
		$account['dt_to'] = $date_to;
		$account['body'] = $this->load->view('account/account_actual',$account, TRUE);
		$this->load->view('main', $account);
	}
	
	function report_movement() {
		$date_from = $this->input->post('date_from');
		$date_to = $this->input->post('date_to');
		
		if(!isset($date_from) || $date_from == '')
			$date_from = date('Y-m-d');
		if(!isset($date_to) || $date_to == '')
			$date_to = date('Y-m-d');

		$this->load->model('account/account_model');
		$account['account_movements'] = $this->account_model->account_movement($date_from, $date_to);
		$account['dt_from'] = $date_from;
		$account['dt_to'] = $date_to;
		$account['body'] = $this->load->view('account/account_movement',$account, TRUE);
		$this->load->view('main', $account);		
	}
	
	function report_movement_pdf() {
		$this->load->plugin('to_pdf');
		$this->load->helper('file'); 
				
		list($date_from, $date_to) = explode("_", $this->uri->segment(3));
		
		if(!isset($date_from) || $date_from == '')
			$date_from = date('Y-m-d');
		if(!isset($date_to) || $date_to == '')
			$date_to = date('Y-m-d');

		$this->load->model('account/account_model');
		$account['account_movements'] = $this->account_model->account_movement($date_from, $date_to);
		$account['dt_from'] = $date_from;
		$account['dt_to'] = $date_to;

		$html  = $this->load->view('account/report_movement', $account, TRUE);
		pdf_create($html, "account_movement", TRUE);
	}
	
	function report_waiter() {
		$date_from = $this->input->post('date_from');
		$date_to = $this->input->post('date_to');
		
		if(!isset($date_from) || $date_from == '')
			$date_from = date('Y-m-d');
		if(!isset($date_to) || $date_to == '')
			$date_to = date('Y-m-d');

		$this->load->model('account/account_model');
		$account['account_movements'] = $this->account_model->waiter_movement($date_from, $date_to);
		$account['dt_from'] = $date_from;
		$account['dt_to'] = $date_to;
		$account['body'] = $this->load->view('account/account_waiter',$account, TRUE);
		$this->load->view('main', $account);				
	}
	
	function report_waiter_pdf() {
		$this->load->plugin('to_pdf');
		$this->load->helper('file'); 
				
		list($date_from, $date_to) = explode("_", $this->uri->segment(3));
		
		if(!isset($date_from) || $date_from == '')
			$date_from = date('Y-m-d');
		if(!isset($date_to) || $date_to == '')
			$date_to = date('Y-m-d');

		$this->load->model('account/account_model');
		$account['account_movements'] = $this->account_model->waiter_movement($date_from, $date_to);
		$account['dt_from'] = $date_from;
		$account['dt_to'] = $date_to;
		$html = $this->load->view('account/report_waiter',$account, TRUE);
	
		pdf_create($html, "account_waiter", TRUE);					
	}
	
	function report_sector() {
		$date_from = $this->input->post('date_from');
		$date_to = $this->input->post('date_to');
		
		if(!isset($date_from) || $date_from == '')
			$date_from = date('Y-m-d');
		if(!isset($date_to) || $date_to == '')
			$date_to = date('Y-m-d');
			
		$this->load->model('account/account_model');
		$account['sector_movements'] = $this->account_model->sector_movement($date_from, $date_to);
		$account['sector_numdish'] = $this->account_model->sector_numdish($date_from, $date_to);
		$account['dt_from'] = $date_from;
		$account['dt_to'] = $date_to;
		$account['body'] = $this->load->view('account/account_sector',$account, TRUE);
		$this->load->view('main', $account);		
	}

	function report_sector_pdf() {
		$this->load->plugin('to_pdf');
		$this->load->helper('file'); 
				
		list($date_from, $date_to) = explode("_", $this->uri->segment(3));
		
		if(!isset($date_from) || $date_from == '')
			$date_from = date('Y-m-d');
		if(!isset($date_to) || $date_to == '')
			$date_to = date('Y-m-d');
			
		$this->load->model('account/account_model');
		$account['sector_movements'] = $this->account_model->sector_movement($date_from, $date_to);
		$account['sector_numdish'] = $this->account_model->sector_numdish($date_from, $date_to);
		$account['dt_from'] = $date_from;
		$account['dt_to'] = $date_to;
		$html = $this->load->view('account/report_sector',$account, TRUE);	
	
		pdf_create($html, "account_waiter", TRUE);	
	}
}
?>