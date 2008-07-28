<?php

echo form_open('translator', '', $hidden );

?>

<table>

<?php

echo '<tr>';
echo '<td class="translator_table_header">' . 'Key' . '</td>';
echo '<td class="translator_table_header">' . ucwords( $masterLang ) . '</td>';
echo '<td class="translator_table_header">' . ucwords( $slaveLang ) . '</td>';
echo '</tr>';

foreach ( $moduleData as $key => $line ) {
	echo '<tr valign="top" align="left">';
	echo '<td>' . $key . '</td>';
	echo '<td>' . htmlspecialchars( $line[ 'master' ] ) . '</td>';
	
	if ( mb_strlen( $line[ 'slave' ] ) > $textarea_line_break ) {
		echo '<td>' . form_textarea( array( 'name' => $postUniquifier . $key,
											'value' => $line[ 'slave' ],
											'rows' => $textarea_rows
											)
									);
	} else {
		echo '<td>' . form_input( $postUniquifier . $key, $line[ 'slave' ] );
	}

	if ( strlen( $line[ 'error' ] ) > 0 ) {
		echo '<br /><span class="translator_error">' . $line[ 'error' ] . '</span>';
	}

	if ( strlen( $line[ 'note' ] ) > 0 ) {
		echo '<br /><span class="translator_note">' . $line[ 'note' ] . '</span>';
	}

	echo '</td>';
	echo '</tr>';
}

?>

</table>

<?php

echo form_submit('SaveLang', 'Save' );

echo form_close();
	
?>