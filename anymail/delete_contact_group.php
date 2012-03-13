<?php

include("globals.php");

$query = "DELETE FROM `anymail_contact_groups` WHERE `group_id`='".intval($_REQUEST["id"])."' AND `user_id`='".intval($_SESSION["anymail"]["user"]["user_id"])."'";
$result = db_query($query);

?>