<?php

include("globals.php");

if (isset($_REQUEST["id"])){
	$query = "SELECT `labels`, UNIX_TIMESTAMP(`nice_date`) AS `unix_date` FROM `anymail_messages` WHERE `message_id`='".$_REQUEST["id"]."'";
	$result = run_query($query);
	$row = mysql_fetch_assoc($result);
	
	$labels = unserialize($row["labels"]);
	
	$query = "SELECT * FROM `anymail_labels` WHERE `user_id` = '".$_SESSION["anymail"]["user"]["user_id"]."' ORDER BY `label_name`";
	$result = run_query($query);
	
	$label_options = '<option>Label this message</option>';
	
	while ($label_row = mysql_fetch_assoc($result)){
		$label_options .= '<option value="'.$label_row["label_id"].'" onclick="label_message('.$_REQUEST["id"].','.$label_row["label_id"].');show_header_header('.$_REQUEST["id"].');"';
		if (in_array($label_row["label_id"],$labels)) $label_options .= ' style="background: rgb(61,128,223); color: #ffffff;"';
		$label_options .= '>'.$label_row["label_name"].'</option>';
	}
	
	$output = '
		<div style="float: right; padding: 0px 5px 0px 5px;">
			<a href="javascript:void(0);" onclick="compose('.$_REQUEST["id"].',\'reply\');" title="Reply"><img src="images/reply.gif" alt="Reply" class="action_button" /></a>
			<a href="javascript:void(0);" onclick="compose('.$_REQUEST["id"].',\'replyall\');" title="Reply to All"><img src="images/replytoall.gif" alt="Reply to All" class="action_button" /></a>
			<a href="javascript:void(0);" onclick="compose('.$_REQUEST["id"].',\'forward\');" title="Forward"><img src="images/forward.gif" alt="Forward" class="action_button" /></a>
			<a href="javascript:void(0);" onclick="delete_message('.$_REQUEST["id"].');" title="Delete"><img src="images/delete.gif" alt="Delete" class="action_button" /></a>
			<select name="label_list" id="label_list">
				'.$label_options.'
				<option onclick="new_label('.$_REQUEST["id"].');">New Label</option>
			</select>
		</div>';
	$output .= '<h2>Written '.date("l, F j, Y",$row["unix_date"]).'</h2>';
	echo $output;
}

?>