<?php

include("globals.php");

$query = "SELECT `label_id` FROM `anymail_labels` WHERE `user_id`='".intval($_SESSION["anymail"]["user"]["user_id"])."' ORDER BY `label_name` ASC";
$result = db_query($query);

while ($row = db_fetch_assoc($result)){
	$labels[] = $row["label_id"];
}

echo implode($labels, '|');

?>