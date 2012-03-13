<?php

include("globals.php");

foreach($_REQUEST as $key => $val) $_REQUEST[$key] = stripslashes($val);

$query = "UPDATE `anymail_contacts` SET `contact_name`='".mysql_escape_string($_REQUEST["name"])."', `contact_email`='".mysql_escape_string($_REQUEST["address"])."' WHERE `user_id`='".$_SESSION["anymail"]["user"]["user_id"]."' AND `contact_id`='".$_REQUEST["contact_id"]."'";
$result = run_query($query);

?>