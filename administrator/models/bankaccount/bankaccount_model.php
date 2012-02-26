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

class BankAccount_Model extends Model {
	
	function bankaccount_Model () {
		parent::Model();
	}
	
	function bankaccount_list() {
		$this->db->select('*, account_accounts.id as bankid,account_accounts.name as accname, account_mgmt_addressbook.name as bankname')
				->from('account_accounts')
				->join('account_mgmt_addressbook','account_accounts.bank = account_mgmt_addressbook.id')
				->order_by('account_accounts.bank');	
		$query = $this->db->get();	
		return $query;
	}
	
	function bankaccount_movementlist() {
		$this->db->select('account_mgmt_addressbook.name as bankname, account_accounts.name, account_account_log.description,  account_account_log.amount')
				->from('account_account_log')
				->join('account_accounts', 'account_account_log.account_id = account_accounts.id')
				->join('account_mgmt_addressbook', 'account_accounts.bank = account_mgmt_addressbook.id')
				->order_by('bankname ASC');
		$query = $this->db->get();
		return $query;
	}
	
	function list_one($id) {
		$query = $this->db->get_where('account_accounts', array('id' => $id ));
		return $query->result();
	}
	
	function bank_dropdown() {
		$this->db->select('account_mgmt_addressbook.id, account_mgmt_addressbook.name')
				->from('account_mgmt_addressbook')
				->where('account_mgmt_addressbook.type' , '1')
				->order_by('account_mgmt_addressbook.name', 'asc');
		$query = $this->db->get();
		$bankname = array();

		foreach ($query->result_array() as $row) {
		   $bankname[$row['id']] = $row['name'];
		}

		return $bankname;
	}
	
	function bankaccount_actual() {
		$this->db->select('account_mgmt_addressbook.name as bank, account_accounts.amount, account_accounts.name, account_accounts.number')
				 ->from('account_mgmt_addressbook')
				 ->join('account_accounts','account_mgmt_addressbook.id = account_accounts.bank')
				 ->order_by('bank');

		$query = $this->db->get();	
		return $query;
	}
	
	function bankaccount_movement($from, $to) {
		$this->db->select('*,account_accounts.name as accountname,mgmt_types.name,account_mgmt_addressbook.name as bankname')
				 ->from('account_account_log')
				 ->join('account_accounts','account_account_log.account_id = account_accounts.id')
				 ->join('mgmt_types','account_account_log.type = mgmt_types.id')
				 ->join('account_mgmt_addressbook','account_accounts.bank = account_mgmt_addressbook.id')
				 ->where('account_account_log.timestamp >=',$from)
				 ->where('account_account_log.timestamp <=',$to)
				 ->order_by('bankname');

		$query = $this->db->get();	
		return $query;				 
	}
}
?>