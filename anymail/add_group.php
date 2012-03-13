<?php

include("globals.php");

foreach($_REQUEST as $key => $val) $_REQUEST[$key] = stripslashes($val);

$query = "INSERT INTO `anymail_contact_groups` 
	(`group_name`,
	 `user_id`,
	 `contact_ids`)
	 VALUES
	 ('".mysql_escape_string($_REQUEST["name"])."',
	  '".$_SESSION["anymail"]["user"]["user_id"]."',
	  '".mysql_escape_string(serialize(explode(",",$_REQUEST["contact_ids"])))."')";
$result = run_query($query);

?>