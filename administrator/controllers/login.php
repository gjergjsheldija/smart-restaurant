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
class Login extends Controller {

	function __construct() {
		parent::Controller();
		$this->load->helper('html'); 
		$this->load->helper('MY_url_helper');
		$this->load->helper('directory');
		$this->load->helper('language');
		
		$language = $this->session->userdata('language');
		if($language == '' ) $language = 'english';
		$this->lang->load('smartrestaurant', $language);
		
		if($this->config->item('enable_app_debug'))
			$this->output->enable_profiler(TRUE);
	}

	function index() {
		$data['langDropDown'] = $this->doLanguageDropDown();
		
		if ($this->site_sentry->is_logged_in()) {
			$this->load->view('main');
		} else {
			$this->load->view('login/login',$data);
		}
	}
	
	function doLogin() {
		if(isset($_POST['language']))
			$this->session->set_userdata('language' , $_POST['language']);

		if($this->site_sentry->login_routine()) {
			redirect('main');
		} else {
			$login['message'] = "Login Failed";
			$this->load->view('login/login', $login);
		}
	}
	
	function doLogout() {
		if(isset($_POST['language']))
			$this->session->set_userdata('language' , $_POST['language']);
		
		$data['langDropDown'] = $this->doLanguageDropDown();
		
		$this->session->sess_destroy();
		$this->load->view('login/login',$data);
	}
	
	function doLanguageDropDown() {
		$map = directory_map(APPPATH .'language', TRUE);

		$dropDown = ''; 
		foreach($map as $id => $langName) {
			if($langName != 'index.html')
				$dropDown .= '<option value="' . $langName . '">' . $langName . '</option>';
		}
		
		return $dropDown;
	}
}
?>