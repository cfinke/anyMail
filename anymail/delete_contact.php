<?php

include("globals.php");

$query = "DELETE FROM `anymail_contacts` WHERE `contact_id`='".intval($_REQUEST["id"])."' AND `user_id`='".intval($_SESSION["anymail"]["user"]["user_id"])."'";
$result = db_query($query);

?>