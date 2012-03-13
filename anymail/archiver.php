<?php

include("globals.php");

foreach($_REQUEST as $key => $val) $_REQUEST[$key] = stripslashes($val);

$mids = explode(",", $_REQUEST["mids"]);

print_r($mids);

$query = "UPDATE `anymail_messages` SET `archived` = 1 WHERE `message_id` IN ('".implode("','",$mids)."') AND `user_id`='".intval($_SESSION["anymail"]["user"]["user_id"])."'";
echo $query;
db_query($query);

?>