<?php

include("globals.php");

$output = '
	<table cellspacing="0" cellpadding="0">
		'.get_header_row().'
		<tr>
			<td>
				<div id="listing_frame"></div>
			</td>
		</tr>
		</form>
		<tr>
			<td>
				<div id="header_header"></div>
			</td>
		</tr>
		<tr>
			<td>
				<div id="message_header"></div>
			</td>
		</tr>
		<tr>
			<td>
				<div id="message_body"></div>
			</td>
		</tr>
		'.get_footer_row().'
	</table>';

echo $output;

?>