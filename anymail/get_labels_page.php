<?php

include("globals.php");

$output = '';

$output .= '
	<table cellspacing="0" cellpadding="0">
		'.get_header_row().'
		<tr>
			<td style="background: #ffffff; width: 100%; vertical-align: top;">
				<div id="labelManager" style="height: 500px; overflow: auto;">
					<table cellspacing="0" cellpadding="2">
						<tr class="label_row_header">
							<td style="width: 10ex;">Actions</td>
							<td>Label <a href="javascript:void(0);" onclick="new_label();">(New Label)</a></td>
							<td># Messages</td>
						</tr>';

$query = "SELECT * FROM `anymail_labels` WHERE `user_id`='".$_SESSION["anymail"]["user"]["user_id"]."' ORDER BY `label_name` ASC";
$result = run_query($query);

$i = 0;

while ($row = mysql_fetch_array($result)){
	$output .= '
		<tr class="label_row_'.(($i++ % 2) + 1).'">
			<td>
				<a href="javascript:void(0);" onclick="var newLabelName = prompt(\'Edit '.$row["label_name"].' label:\', \''.$row["label_name"].'\'); if (newLabelName) edit_label(\''.$row["label_name"].'\', newLabelName);"><img src="images/edit.gif" title="Edit" /></a>
				<a href="javascript:void(0);" onclick="delete_label(\''.str_replace("'","\'",$row["label_name"]).'\');"><img src="images/x.gif" title="Delete" /></a>
			</td>
			<td>'.$row["label_name"].'</td>
			<td>';
	
	$new_query = "SELECT COUNT(*) AS `num_messages` FROM `anymail_messages` WHERE `user_id`='".$_SESSION["anymail"]["user"]["user_id"]."' AND `labels` LIKE '%\"".$row["label_id"]."\"%'";
	$new_result = run_query($new_query);
	$output .= mysql_result($new_result, 0, 'num_messages').'</td></tr>';
}

$output .= '
					</table>
				</div>
			</td>
		</tr>
		'.get_footer_row().'
	</table>';

echo $output;

?>