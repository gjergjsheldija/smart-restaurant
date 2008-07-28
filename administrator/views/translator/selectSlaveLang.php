<?php

/* Forms */

foreach ( $languages as $language ) {
	
	echo form_open('translator', '', $hidden );
	
	echo form_submit('slaveLang', $language);
	
	echo form_close();
	
}

?>
