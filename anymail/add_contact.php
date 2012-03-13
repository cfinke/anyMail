<?php

include("globals.php");

foreach($_REQUEST as $key => $val) $_REQUEST[$key] = stripslashes($val);

$query = "INSERT INTO 
	`anymail_contacts` (`contact_name`,`contact_email`,`user_id`) 
		VALUES 
	('".db_escape($_REQUEST["name"])."','".db_escape($_REQUEST["address"])."','".intval($_SESSION["anymail"]["user"]["user_id"])."')";
$result = db_query($query);

?>