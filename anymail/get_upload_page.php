<?php

include("globals.php");

$output = '';

$output .= '
	<table cellspacing="0" cellpadding="0">
		'.get_header_row().'
		<tr>
			<td style="background: #ffffff; width: 100%; height: 500px;">
				<iframe name="upload_iframe" id="upload_iframe" src="upload_page.php" style="width: 100%; height: 500px; border: 0;"></iframe>
			</td>
		</tr>
		'.get_footer_row().'
	</table>';

echo $output;

?>