<?php

echo form_open('translator', '', $hidden );

?>

<table>

<?php

echo '<tr>';
echo '<td class="translator_table_header">' . 'Key' . '</td>';
echo '<td class="translator_table_header"><b>' . ucwords( $masterLang ) . '</td>';
echo '<td class="translator_table_header">' . ucwords( $slaveLang ) . '</td>';
echo '</tr>';

foreach ( $moduleData as $key => $line ) {
	echo '<tr>';
	echo '<td>' . $key . '</td>';
	echo '<td>' . htmlspecialchars( $line['master'] ) . '</td>';
	echo '<td>' . htmlspecialchars( $line['slave'] ) . '</td>';
	echo '</tr>';
}

?>

</table>

<?php

echo form_submit('ConfirmSaveLang', 'Confirm' );

echo form_close();
	
?>