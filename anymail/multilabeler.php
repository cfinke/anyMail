<?php

include("globals.php");

foreach($_REQUEST as $key => $val) $_REQUEST[$key] = stripslashes($val);

$mids = explode(",", $_REQUEST["mids"]);
print_r($mids);
foreach ($mids as $mid){
	$query = "SELECT `labels`,`message_id` FROM `anymail_messages` WHERE `message_id` = '".intval($mid)."'";
	$result = db_query($query);
	$row = db_fetch_assoc($result);
	
	$labels = unserialize($row["labels"]);
	
	if (in_array($_REQUEST["lid"], $labels)) {
		$key = array_search($_REQUEST["lid"], $labels);
		
		if ($key !== false){
			unset($labels[$key]);
		}
	}
	else{
		$labels[] = $_REQUEST["lid"];
	}
	
	$query = "UPDATE `anymail_messages` SET `labels` = '".db_escape(serialize($labels))."' WHERE `message_id`='".db_escape($row["message_id"])."'";
	db_query($query);
}

?>