<?php

include("globals.php");

foreach($_REQUEST as $key => $val) $_REQUEST[$key] = stripslashes($val);

$query = "UPDATE `anymail_contacts` SET `contact_name`='".db_escape($_REQUEST["name"])."', `contact_email`='".db_escape($_REQUEST["address"])."' WHERE `user_id`='".intval($_SESSION["anymail"]["user"]["user_id"])."' AND `contact_id`='".intval($_REQUEST["contact_id"])."'";
$result = db_query($query);

?>