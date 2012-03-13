<?php

include("globals.php");

$_REQUEST["labelName"] = stripslashes($_REQUEST["labelName"]);

$query = "SELECT `label_id` FROM `anymail_labels` WHERE `label_name`='".mysql_escape_string($_REQUEST["labelName"])."' AND `user_id`='".$_SESSION["anymail"]["user"]["user_id"]."'";
$result = run_query($query);
$label_id = mysql_result($result, 0, 'label_id');

$query = "SELECT `message_id`,`labels` FROM `anymail_messages` WHERE `labels` LIKE '%\"".$label_id."\"%'";
$result = run_query($query);

while ($row = mysql_fetch_assoc($result)){
	$row["labels"] = unserialize($row["labels"]);
	
	$key = array_search($label_id, $row["labels"]);
	unset($row["labels"][$key]);
	
	$new_query = "UPDATE `anymail_messages` SET `labels`='".mysql_escape_string(serialize($row["labels"]))."' WHERE `message_id`='".$row["message_id"]."'";
	$new_result = run_query($new_query);
}

$query = "DELETE FROM `anymail_labels` WHERE `label_id`='".$label_id."'";
$result = run_query($query);

?>