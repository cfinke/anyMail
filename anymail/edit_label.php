<?php

include("globals.php");

foreach ($_REQUEST as $key => $val) $_REQUEST[$key] = stripslashes($val);

$query = "UPDATE `anymail_labels` SET `label_name`='".mysql_escape_string($_REQUEST["new_name"])."' WHERE `label_name`='".mysql_escape_string($_REQUEST["old_name"])."' AND `user_id`='".$_SESSION["anymail"]["user"]["user_id"]."'";
$result = run_query($query);

echo $query;

?>