	<?php $ci_uri = trim($this->uri->uri_string(), '/'); $att = ' id="active"';?>
    <ul id="navlist">
		<li<?= $ci_uri == ''? $att: ''?>><?=anchor('', 'home')?></li>
		<li<?= substr($ci_uri, 0, 7) == 'example'? $att: ''?>><?=anchor('example', 'examples')?></li>
		<li<?= $ci_uri == $this->config->item('FAL_login_uri')? $att: ''?>><?=anchor($this->config->item('FAL_login_uri'), 'login')?></li>
		<li<?= $ci_uri == $this->config->item('FAL_register_uri')? $att: ''?>><?=anchor($this->config->item('FAL_register_uri'), 'register')?></li>
		<li<?= $ci_uri == $this->config->item('FAL_forgottenPassword_uri')? $att: ''?>><?=anchor($this->config->item('FAL_forgottenPassword_uri'), 'forgotten password')?></li>
		<li<?= $ci_uri == $this->config->item('FAL_changePassword_uri')? $att: ''?>><?=anchor($this->config->item('FAL_changePassword_uri'), 'change password')?></li>
		<li<?= substr($ci_uri, 0, 5) == 'admin'? $att: ''?>><?=anchor('admin', 'admin')?></li>
	</ul>

