<?php

include("globals.php");

$query = "DELETE FROM `anymail_contact_groups` WHERE `group_id`='".$_REQUEST["id"]."' AND `user_id`='".$_SESSION["anymail"]["user"]["user_id"]."'";
$result = run_query($query);

?>