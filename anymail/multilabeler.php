<?php

include("globals.php");

foreach($_REQUEST as $key => $val) $_REQUEST[$key] = stripslashes($val);

$mids = explode(",", $_REQUEST["mids"]);
print_r($mids);
foreach ($mids as $mid){
	$query = "SELECT `labels`,`message_id` FROM `anymail_messages` WHERE `message_id` = '".$mid."'";
	$result = run_query($query);
	$row = mysql_fetch_assoc($result);
	
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
	
	$query = "UPDATE `anymail_messages` SET `labels` = '".mysql_escape_string(serialize($labels))."' WHERE `message_id`='".$row["message_id"]."'";
	run_query($query);
}

?>