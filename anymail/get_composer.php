<?php

include("globals.php");

$output = '';

$output .= '
	<table cellspacing="0" cellpadding="0">
		'.get_header_row().'
		<tr>
			<td style="background: #ffffff; width: 100%; height: 500px;">
				<iframe name="composer_iframe" id="composer_iframe" src="composer_page.php?a=b'.(isset($_REQUEST["jto"]) ? '&jto='.$_REQUEST["jto"] : '').(isset($_REQUEST["jcc"]) ? '&jcc='.$_REQUEST["jcc"] : '').(isset($_REQUEST["jbcc"]) ? '&jbcc='.$_REQUEST["jbcc"] : '').'&request_var='.urlencode(serialize($_REQUEST)).'" style="width: 100%; height: 500px; border: 0;"></iframe>
			</td>
		</tr>
		'.get_footer_row().'
	</table>';

echo $output;

?>