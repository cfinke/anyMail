<?php

include("globals.php");

$_REQUEST["labelName"] = stripslashes($_REQUEST["labelName"]);

$query = "SELECT `label_id` FROM `anymail_labels` WHERE `label_name`='".db_escape($_REQUEST["labelName"])."' AND `user_id`='".intval($_SESSION["anymail"]["user"]["user_id"])."'";
$result = db_query($query);
$label_id = mysql_result($result, 0, 'label_id');

$query = "SELECT `message_id`,`labels` FROM `anymail_messages` WHERE `labels` LIKE '%\"".$label_id."\"%'";
$result = db_query($query);

while ($row = db_fetch_assoc($result)){
	$row["labels"] = unserialize($row["labels"]);
	
	$key = array_search($label_id, $row["labels"]);
	unset($row["labels"][$key]);
	
	$new_query = "UPDATE `anymail_messages` SET `labels`='".db_escape(serialize($row["labels"]))."' WHERE `message_id`='".intval($row["message_id"])."'";
	$new_result = db_query($new_query);
}

$query = "DELETE FROM `anymail_labels` WHERE `label_id`='".intval($label_id)."'";
$result = db_query($query);

?>