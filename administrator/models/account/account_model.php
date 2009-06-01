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

class Account_Model extends Model {
	
	function account_Model () {
		parent::Model();
	}
	
	function account_list($type) {
		if($type == 'ap') {
			$this->db->select('*')
					->from('account_mgmt_main')
					->join('mgmt_types','mgmt_types.id = account_mgmt_main.type')
					->where('account_mgmt_main.waiter_income','0')
					->where('account_mgmt_main.cash_amount <=','0') // < 0
					->where('account_mgmt_main.bank_amount <=','0') // < 0
					->order_by('account_mgmt_main.who','ASC');		
		}elseif($type == 'ar'){
			$this->db->select('*')
					->from('account_mgmt_main')
					->join('mgmt_types','mgmt_types.id = account_mgmt_main.type')
					->where('account_mgmt_main.waiter_income','0')
					->where('account_mgmt_main.cash_amount >=','0') // > 0
					->where('account_mgmt_main.bank_amount >=','0') // > 0
					->order_by('account_mgmt_main.who','ASC');			
		}
		$query = $this->db->get();	
		return $query;
	}
	
	function account_who() {
		$query = $this->db->get('account_mgmt_addressbook');
		$account_who = array();

		foreach ($query->result_array() as $row) {
		   $account_who[$row['id']] = $row['name'];
		}

		return $account_who;		
	}

	function account_supplier() {
		$query = $this->db->get_where('account_mgmt_addressbook',array('type' => '2'));
		$account_who = array();

		foreach ($query->result_array() as $row) {
		   $account_who[$row['id']] = $row['name'];
		}

		return $account_who;		
	}	
	
	function supplier_invoice() {
		$query = $this->db->get_where('account_mgmt_addressbook',array('type' => '2'));
		$account_who = array();

		foreach ($query->result_array() as $row) {
		   $account_who[$row['name']] = $row['name'];
		}

		return $account_who;		
	}	
	
	function getUserName($userid) {
		$query = $this->db->get_where('account_mgmt_addressbook',array('id' => $userid));
		$tmp = $query->result();
		return $tmp[0]->name;
	}
	
	function payment_type($type = '') {
		if($type == '')
			$this->db->select('*')->from('mgmt_types')->where('is_invoice_payment','1')->or_where('is_invoice','1');
		else
			$this->db->select('*')->from('mgmt_types');
		$query = $this->db->get();
		$payment_type = array();
		
		foreach ($query->result_array() as $row) {
		   $payment_type[$row['id']] = lang($row['name']);
		}

		return $payment_type;		
	}	
	
	function bank_account() {
		$query = $this->db->get('account_accounts');
		$bank_account = array();

		//add's an empty element
		foreach ($query->result_array() as $row) {
		   $bank_account[$row['id']] = $row['name'];
		}

		return $bank_account;		
	}

	function total_ap($from, $to) {
		$query = $this->db->select('sum(cash_amount) as total_ap')
						  ->from('account_mgmt_main')
						  ->where('cash_amount <','0');
		
		$query = $this->db->get();	
		return $query->result_array();
		
	}
	
	function total_ar($from, $to) {
		$query = $this->db->select('sum(cash_amount) as total_ar')
						  ->from('account_mgmt_main')
						  ->where('cash_amount >','0');
		
		$query = $this->db->get();	
		return $query->result_array();
		
	}
	
	function account_movement($from, $to) {
		$query = $this->db->select('*')
						  ->from('account_mgmt_main')
						  ->join('mgmt_types','mgmt_types.id = account_mgmt_main.type')
						  ->where('account_mgmt_main.date >=', $from)
						  ->where('account_mgmt_main.date <=', $to)
						  ->where('account_mgmt_main.bank_amount', '0')
						  ->order_by('account_mgmt_main.who','asc')
						  ->order_by('account_mgmt_main.date','asc');
		
		$query = $this->db->get();	
		return $query;		
	}
	
	function waiter_movement($from, $to) {
		$query = $this->db->select('*, account_mgmt_main.id as accid')
						  ->from('account_mgmt_main')
						  ->join('mgmt_types','mgmt_types.id = account_mgmt_main.type')
						  ->where('account_mgmt_main.date >=', $from)
						  ->where('account_mgmt_main.date <=', $to)
						  ->where('account_mgmt_main.waiter_income', '1')						  
						  ->order_by('account_mgmt_main.who','asc')
						  ->order_by('account_mgmt_main.date','desc');
		
		$query = $this->db->get();	
		$account_movements = $query->result_array();

		$tmp = array();
		$result = array();
		foreach($account_movements as $account_movement) {
			$querymovement = $this->db->select('*, dishes.name as dishname, account_log.price as dishprice, account_log.quantity as dishqty')
									   ->from('account_log')
									   ->join('dishes','dishes.id = account_log.dish','inner')
									   ->where('payment =',$account_movement['accid']);
			$querymovement = $this->db->get();
			$tmp = $account_movement;
			foreach($querymovement->result_array() as $row ) {
				$tmp['dish'][] = $row;
			}
			$result[] = $tmp;
		}
		
		return $result;		
	}
	
	function sector_movement($from, $to) {
		$query = $this->db->select('users.name AS waiter, dests.name, sum(price) AS shuma')
						  ->from('users')
						  ->join('account_log','users.id = account_log.waiter')
						  ->join('dests','account_log.destination = dests.id')
						  ->where('account_log.datetime >=', $from)
						  ->where('account_log.datetime <=', $to)
						  ->group_by(array('users.name', 'dests.name')) 					  
						  ->order_by('account_log.waiter','asc');
		
		$query = $this->db->get();	
		return $query;			
	}
	
	function sector_numdish($from, $to) {
		$query = $this->db->select('dishes.name, count( dishes.name ) AS numdish')
						  ->from('dishes')
						  ->join('account_log','dishes.id = account_log.dish')
						  ->where('account_log.datetime >=', $from)
						  ->where('account_log.datetime <=', $to)
						  ->group_by('account_log.dish') 					  
						  ->order_by('numdish','asc');
		
		$query = $this->db->get();	
		return $query->result_array();			
	}	
}
?>