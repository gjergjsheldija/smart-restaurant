	<?php $ci_uri = trim($this->uri->uri_string(), '/'); $att = ' id="active"';?>
    <ul id="navlist">
		<li<?php echo  $ci_uri == ''? $att: ''?>><?php echo anchor('', 'home')?></li>
		<li<?php echo  substr($ci_uri, 0, 7) == 'example'? $att: ''?>><?php echo anchor('example', 'examples')?></li>
		<li<?php echo  $ci_uri == $this->config->item('FAL_login_uri')? $att: ''?>><?php echo anchor($this->config->item('FAL_login_uri'), 'login')?></li>
		<li<?php echo  $ci_uri == $this->config->item('FAL_register_uri')? $att: ''?>><?php echo anchor($this->config->item('FAL_register_uri'), 'register')?></li>
		<li<?php echo  $ci_uri == $this->config->item('FAL_forgottenPassword_uri')? $att: ''?>><?php echo anchor($this->config->item('FAL_forgottenPassword_uri'), 'forgotten password')?></li>
		<li<?php echo  $ci_uri == $this->config->item('FAL_changePassword_uri')? $att: ''?>><?php echo anchor($this->config->item('FAL_changePassword_uri'), 'change password')?></li>
		<li<?php echo  substr($ci_uri, 0, 5) == 'admin'? $att: ''?>><?php echo anchor('admin', 'admin')?></li>
	</ul>

