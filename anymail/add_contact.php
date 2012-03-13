<?php

include("globals.php");

foreach($_REQUEST as $key => $val) $_REQUEST[$key] = stripslashes($val);

$query = "INSERT INTO `anymail_contacts` (`contact_name`,`contact_email`,`user_id`) 
	VALUES ('".mysql_escape_string($_REQUEST["name"])."','".mysql_escape_string($_REQUEST["address"])."','".$_SESSION["anymail"]["user"]["user_id"]."')";
$result = run_query($query);

?>