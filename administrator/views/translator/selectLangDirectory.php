<?php

/* Forms */

foreach ( $langdirs as $langdir ) {
	
	echo form_open('translator');
	
	echo form_submit('langDir', $langdir);
	
	echo form_close();
	
}

?>
