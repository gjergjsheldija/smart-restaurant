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
class Stock extends Controller {

	function __construct() {
		parent::Controller();
		$this->load->helper(array('html','url','MY_url_helper','language'));	
		
		$language = $this->session->userdata('language');
		if($language == '' ) $language = 'english';
		$this->lang->load('smartrestaurant', $language);		
		
		if($this->config->item('enable_app_debug'))
			$this->output->enable_profiler(TRUE);
	}

	function index() {
		if (!$this->site_sentry->is_logged_in())
			redirect('login');			
		$this->load->model('account/account_model');
		$stock['supplier'] = $this->account_model->supplier_invoice();	
		$stock['payment_type'] = $this->account_model->payment_type();	
		$stock['bank_account'] = $this->account_model->bank_account();		
		$this->load->model('ingredient/ingredient_model');
		$stock['ingredients'] = $this->ingredient_model->ingredient_dropdown_stock();			
		$stock['body'] = $this->load->view('stock/stock_add', $stock, TRUE);
		$this->load->view('main', $stock);
	}
	
	function ingredientList() {
		$this->load->model('ingredient/ingredient_model');
		$ingredients = $this->ingredient_model->ingredient_dropdown_stock();	
		$ingredientList = '';
		foreach($ingredients as $key => $value) {
			$ingredientList .= "<option value='" . $key . "'>" . $value . "</option>";
		}
		echo $ingredientList;
	}
	
	function addnew() {
		//account vars
		$timestamp = $this->input->post('timestamp', TRUE);
		$invoice_id = $this->input->post('invoice_id', TRUE);
		$paid = $this->input->post('paid', TRUE);
		$type = $this->input->post('type', TRUE);
		$account_id = $this->input->post('account_id', TRUE);
		$supplier =  $this->input->post('supplier', TRUE);
		//stock vars
		$ingredients = $this->input->post('ingredient', TRUE);
		$quantity = $this->input->post('quantity', TRUE);
		$price = $this->input->post('price', TRUE);
		//session vars
		$session_data =  $this->session->userdata;
		$user_id = $session_data['user_id'];

		//stock supposed FIFO
		$this->db->trans_start();
		//stock movements
		//step 1 : obj_id of the dish
		//step 2 : update quantity in the stock_movements table
		foreach($ingredients as $key => $value) {
			$sqlInsert = "INSERT INTO stock_movements ( obj_id, quantity, value, timestamp, user )"
				 . " SELECT id, '" .$quantity[$key] . "', '".$price[$key]."','" . $timestamp . "', '" . $user_id . "' "
				 . " FROM stock_objects WHERE ref_id =  ".$value ;
			
			$queryInsert = $this->db->query($sqlInsert);
		}
		

		//stock state
		foreach($ingredients as $key => $value) {
			//step 3 : value of unit
			$sqlValue = "SELECT ROUND(SUM(value) / SUM(quantity), 2) AS value "
					  . "FROM stock_movements "
					  . "WHERE obj_id = (SELECT id FROM stock_objects WHERE ref_id = '".$value ."' and deleted = '0') ";
			$queryValue = $this->db->query($sqlValue);
			$tmp = $queryValue->result_array();
			
			if(isset($tmp[0]['value']) && $tmp[0]['value'] != '0' )
				$ingredientValue = $tmp[0]['value'];
			else
				$ingredientValue = $price[$key];
				
			//step 4 : update stock_objects
			$sqlUpdate = "UPDATE stock_objects SET quantity = quantity + " . $quantity[$key] . ", " 
				 	   . " value =  '" . $ingredientValue . "'"
				 	   . " WHERE ref_id = '".$value ."'";
			
			
			$queryUpdate = $this->db->query($sqlUpdate);
		}
		
		//the money...
		
		//payed or not
		$debit = FALSE;
		if(isset($paid) && $paid == 'paid') {
			$sqlPaid = "  '1' ,  '0' ,  '0' ";
			$debit = FALSE;
		} else {
			$sqlPaid = " '0' , '1' ,  '" . array_sum($price) . "' ";
			$debit = TRUE;
		}
		
		//payment type
		$bank = FALSE;
		if( isset($type) && $type == '3') {
			$sqlPayment = ", '". $type ."' , '0' ";
			$sqlPayment .= ", '-" . array_sum($price) . "' ,  '0' ";
		} else {
			$sqlPayment = ", '". $type ."' , '" . $account_id . "' ";
			$sqlPayment .= ", '0' ,  '-" . array_sum($price) . "' ";
			$bank = TRUE;
		}

		$sqlInsertPayment = "INSERT INTO account_mgmt_main ( date, paid, debit, debit_amount, "
						  . " type, account_id, cash_amount, bank_amount, description, number, who ) "
						  . " VALUES ( "
						  . "'" . $timestamp . "' , " . $sqlPaid . "  " .  $sqlPayment . ", "
						  . "'Fatura Nr. : " .$invoice_id.  "' , '" . $invoice_id . "', '" . $supplier . "' )";

		
		$queryInsertPayment = $this->db->query($sqlInsertPayment);
		
		//if bank payment...
		if( isset($bank) && $bank == TRUE) {
			$bankMovement = array('account_id' => $account_id,
								  'type' => $type,
								  'amount' => -array_sum($price),
								  'description' => 'Invoice Nr. : ' .$invoice_id);
			$this->db->insert('account_account_log', $bankMovement);
		}
		$this->db->trans_complete();
		redirect('stock/index/add');
	}
	
	function report_actual() {
		$this->load->model('stock/stock_model');
		$stock['stock_actual'] = $this->stock_model->stock_actual();
		$this->load->model('printer/printer_model');
		$stock['warehouse'] = $this->printer_model->printer_dropdown();
		$stock['uom'] = array('0' => lang('pieces'),'1' => 'kg', '2' => 'lt');				
		$stock['body'] = $this->load->view('stock/stock_actual', $stock, TRUE);
		$this->load->view('main', $stock);		
	}

	function report_movement() {
		date_default_timezone_set($this->config->item('default_timezone'));
		
		$date_from = $this->input->post('date_from');
		$date_to = $this->input->post('date_to');
		
		if(!isset($date_from) || $date_from == '')
			$date_from = date('Y-m-d');
		if(!isset($date_to) || $date_to == '')
			$date_to = date('Y-m-d');
						
		$this->load->model('stock/stock_model');
		$stock['stock_actual'] = $this->stock_model->stock_movement($date_from,$date_to);
		$stock['dt_from'] = $date_from;
		$stock['dt_to'] = $date_to;
		$stock['body'] = $this->load->view('stock/stock_movement', $stock, TRUE);
		$this->load->view('main', $stock);		
	}	
	
	function report_movement_pdf() {
		date_default_timezone_set($this->config->item('default_timezone'));
		
		$this->load->plugin('to_pdf');
		$this->load->helper('file'); 

		list($date_from, $date_to) = explode("_", $this->uri->segment(3));
		
		if(!isset($date_from) || $date_from == '')
			$date_from = date('Y-m-d');
		if(!isset($date_to) || $date_to == '')
			$date_to = date('Y-m-d');
						
		$this->load->model('stock/stock_model');
		$stock['stock_actual'] = $this->stock_model->stock_movement($date_from,$date_to);
		$stock['dt_from'] = $date_from;
		$stock['dt_to'] = $date_to;

		$html  = $this->load->view('stock/report_movement', $stock, TRUE);
		pdf_create($html, "stock_movement", TRUE);
	}
	
	function report_actual_pdf() {
		$this->load->plugin('to_pdf');
		$this->load->helper('file'); 
				
		$this->load->model('stock/stock_model');
		$stock['stock_actual'] = $this->stock_model->stock_actual();

		$html  = $this->load->view('stock/report_actual', $stock, TRUE);
		pdf_create($html, "stock_actual", TRUE);		
	}
	
}
?>