<?php

include("globals.php");

if (isset($_REQUEST["id"])){
	$query = "SELECT `Subject`,`From`,`To`,`attachments` FROM `anymail_messages` WHERE `message_id`='".intval($_REQUEST["id"])."'";
	$result = db_query($query);
	$row = db_fetch_assoc($result);
	$row["attachments"] = unserialize($row["attachments"]);
	
	$output = '<table cellpadding="0" cellspacing="0"><tr>';
	
	$output .= '<td><h1>'.$row["Subject"].'</h1>';
	$line2 = 'From: '.$row["From"].', To: '.$row["To"];
	if (strlen($line2) > 100) $line2 = substr($line2,0,100) . '...';
	$output .= '<h2>'.htmlentities($line2).'</h2></td>';
	
	if (count($row["attachments"]) > 0){
		$attachment_form = '<td style="text-align: right; padding-right: 10px;">
				<select name="attachments" id="attachments" onchange="get_attachment();">
					<option value="0">Download attachments</option>';
		
		foreach ($row["attachments"] as $aid){
			$query = "SELECT * FROM `anymail_attachments` WHERE `attachment_id`='".intval($aid)."'";
			$result = db_query($query);
			$newrow = db_fetch_assoc($result);
			
			$attachment_form .= '<option value="'.htmlspecialchars($aid).'">'.$newrow["filename"].'</option>';
		}
		
		$attachment_form .= '</select></td>';
		
		$output .= $attachment_form;
	}
	
	$output .= '</tr></table>';
	
	echo $output;
}

?>