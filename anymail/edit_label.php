<?php

include("globals.php");

foreach ($_REQUEST as $key => $val) $_REQUEST[$key] = stripslashes($val);

$query = "UPDATE `anymail_labels` SET `label_name`='".db_escape($_REQUEST["new_name"])."' WHERE `label_name`='".db_escape($_REQUEST["old_name"])."' AND `user_id`='".intval($_SESSION["anymail"]["user"]["user_id"])."'";
$result = db_query($query);

echo $query;

?>