<?php

include("globals.php");

$output = '';

$output .= '
	<table cellspacing="0" cellpadding="0">
		'.get_header_row().'
		<tr>
			<td style="background: #ffffff; width: 100%; vertical-align: top;">
				<div id="contactManager" style="height: 500px; overflow: auto;">
					<table cellspacing="0" cellpadding="2">
						<tr class="label_row_header">
							<td style="width: 10ex;">&nbsp;</td>
							<td>Group Name <a href="javascript:void(0);" onclick="new_contact_group();">(New Group From Checked)</a></td>
							<td>Contacts</td>
						</tr>';

$query = "SELECT * FROM `anymail_contact_groups` WHERE `user_id`='".intval($_SESSION["anymail"]["user"]["user_id"])."' ORDER BY `group_name` ASC";
$result = db_query($query);

$i = 0;

if (db_num_rows($result) > 0){
	while ($row = db_fetch_assoc($result)){
		$this_contacts = array();
		
		$output .= '
			<tr class="contact_row_'.(($i++ % 2) + 1).'">
				<td style="vertical-align: top; text-align: center;">
					<!-- <a href="javascript:void(0);" onclick="edit_contact_group(\''.str_replace("'","\'",$row["group_name"]).'\', '.$row["group_id"].');"><img src="images/edit.gif" title="Edit Group" /></a> -->
					<a href="javascript:void(0);" onclick="delete_contact_group(\''.str_replace("'","\'",$row["group_name"]).'\', '.$row["group_id"].');"><img src="images/x.gif" title="Delete" /></a>
				</td>
				<td style="vertical-align: top;">'.$row["group_name"].'</td>';
		
		$contact_ids = unserialize($row["contact_ids"]);
		
		foreach($contact_ids as $cid){
			$newquery = "SELECT * FROM `anymail_contacts` WHERE `contact_id`='".intval($cid)."'";
			$newresult = db_query($newquery);
			$newrow = db_fetch_assoc($newresult);
			
			$this_contacts[] = $newrow["contact_name"] . ' &lt;'.$newrow["contact_email"].'&gt;';
		}
		
		$output .= '<td>'.implode("<br />",$this_contacts).'</td>
			</tr>';
	}
}
else{
	$output .= '<tr class="contact_row_1"><td colspan="3" style="text-align: center;">There are no contact groups to display.</td></tr>';
}

$output .= '
						<tr class="label_row_header">
							<td style="width: 10ex;">&nbsp;</td>
							<td>Contact Name <a href="javascript:void(0);" onclick="new_contact();">(New Contact)</a></td>
							<td>E-mail Address</td>
						</tr>';

//var newLabelName = prompt(\'Edit '.$row["label_name"].' label:\', \''.$row["label_name"].'\'); if (newLabelName) edit_label(\''.$row["label_name"].'\', newLabelName);
$query = "SELECT * FROM `anymail_contacts` WHERE `user_id`='".intval($_SESSION["anymail"]["user"]["user_id"])."' ORDER BY `contact_name` ASC";
$result = db_query($query);

$i = 0;

if (db_num_rows($result) > 0){
	while ($row = db_fetch_assoc($result)){
		$output .= '
			<tr class="contact_row_'.(($i++ % 2) + 1).'">
				<td>
					<a href="javascript:void(0);" onclick="edit_contact(\''.str_replace("'","\'",$row["contact_name"]).'\', \''.$row["contact_email"].'\', '.$row["contact_id"].');"><img src="images/edit.gif" title="Edit" /></a>
					<a href="javascript:void(0);" onclick="delete_contact(\''.str_replace("'","\'",$row["contact_name"]).'\', '.$row["contact_id"].');"><img src="images/x.gif" title="Delete" /></a>
					<input type="checkbox" name="addToGroup" value="'.$row["contact_id"].'" />
				</td>
				<td>'.$row["contact_name"].'</td>
				<td>'.$row["contact_email"].'</td>';
	}
}
else{
	$output .= '<tr class="contact_row_1"><td colspan="3" style="text-align: center;">There are no contacts to display.</td></tr>';
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