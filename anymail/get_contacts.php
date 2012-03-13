<?php

require_once("globals.php");

$output = '';

$output .= '<div style="height: 150px; overflow: auto;">
		<table cellspacing="0" cellpadding="0" >';

$query = "SELECT * FROM `anymail_contact_groups` WHERE `user_id`='".intval($_SESSION["anymail"]["user"]["user_id"])."'";
$result = db_query($query);

if (db_num_rows($result) > 0){
	while ($row = db_fetch_assoc($result)){
		$this_contacts = array();
		
		$contact_ids = unserialize($row["contact_ids"]);
		
		foreach($contact_ids as $cid){
			$newquery = "SELECT * FROM `anymail_contacts` WHERE `contact_id`='".intval($cid)."'";
			$newresult = db_query($newquery);
			$newrow = db_fetch_assoc($newresult);
			
			$this_contacts[] = $newrow["contact_name"] . ' <'.$newrow["contact_email"].'>';
		}
		
		$contact_string = str_replace("'","\'",htmlspecialchars(implode(", ",$this_contacts)));
		
		$output .= '<tr id="group_row_'.$row["group_id"].'" class="contact_row_unselected">
				<td><a href="javascript:void(0);" onclick="to(\''.$contact_string.'\');">'.$row["group_name"].'</a></td>
				<td><a href="javascript:void(0);" onclick="to(\''.$contact_string.'\');">To</a></td>
				<td><a href="javascript:void(0);" onclick="cc(\''.$contact_string.'\');">Cc</a></td>
				<td><a href="javascript:void(0);" onclick="bcc(\''.$contact_string.'\');">Bcc</a></td>';
			$output .= '</tr>';
	}
	
	$output .= '<tr><td colspan="4"><hr /></td></tr>';
}

$query = "SELECT * FROM `anymail_contacts` WHERE `user_id`='".intval($_SESSION["anymail"]["user"]["user_id"])."' ORDER BY `contact_name` ASC";
$result = db_query($query);

if (db_num_rows($result) > 0){
	while ($row = db_fetch_assoc($result)){
		$output .= '<tr id="contact_row_'.$row["contact_id"].'" class="';
		if ($row["contact_id"] == $_SESSION["anymail"]["cid"]) $output .= 'contact_row_selected';
		else $output .= 'contact_row_unselected';
		$output .= '">';
		$output .= '
			<td><a href="javascript:void(0);" onclick="to(\''.str_replace("'","\'",htmlspecialchars($row["contact_name"].' <'.$row["contact_email"].'>')).'\');">'.$row["contact_name"].'</a></td>
			<td><a href="javascript:void(0);" onclick="to(\''.str_replace("'","\'",htmlspecialchars($row["contact_name"].' <'.$row["contact_email"].'>')).'\');">To</a></td>
			<td><a href="javascript:void(0);" onclick="cc(\''.str_replace("'","\'",htmlspecialchars($row["contact_name"].' <'.$row["contact_email"].'>')).'\');">Cc</a></td>
			<td><a href="javascript:void(0);" onclick="bcc(\''.str_replace("'","\'",htmlspecialchars($row["contact_name"].' <'.$row["contact_email"].'>')).'\');">Bcc</a></td>';
		$output .= '</tr>';
	}
}

$output .= '</table></div>';

echo $output;

?>