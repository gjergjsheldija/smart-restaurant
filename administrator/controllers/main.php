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

class Main extends Controller {

	function __construct() {
		parent::Controller();
		$this->load->helper('html');
		$this->load->helper('MY_url_helper');
		$this->load->helper('language');	
	}

	function index() {
		if (!$this->site_sentry->is_logged_in())
			redirect('login');		
		$data['page_title'] = "Smart Res";
		$this->load->view('main', $data);

	}

	function userinfo($userid) {
		$this->load->helper( 'gravatar' ); 
		$data['left_menu'] = $this->load->view('menu', '', TRUE);
		$this->load->view('main', $data);
	}
	
}
?>