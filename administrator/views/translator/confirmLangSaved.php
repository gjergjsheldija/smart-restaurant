<table>

<?php

echo '<tr>';
echo '<td class="translator_table_header">' . ucwords( $slaveLang ) . '</td>';
echo '<td class="translator_table_header">' . ucwords( $langModule ) . '</td>';
echo '</tr>';


?>

</table>

<p><?php echo $this->data['saved_data']; ?></p>