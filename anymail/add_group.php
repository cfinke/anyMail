<?php

include("globals.php");

foreach($_REQUEST as $key => $val) $_REQUEST[$key] = stripslashes($val);

$query = "INSERT INTO `anymail_contact_groups` 
	(`group_name`,
	 `user_id`,
	 `contact_ids`)
	 VALUES
	 ('".db_escape($_REQUEST["name"])."',
	  '".intval($_SESSION["anymail"]["user"]["user_id"])."',
	  '".db_escape(serialize(explode(",",$_REQUEST["contact_ids"])))."')";
$result = db_query($query);

?>