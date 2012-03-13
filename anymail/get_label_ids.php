<?php

include("globals.php");

$query = "SELECT `label_id` FROM `anymail_labels` WHERE `user_id`='".$_SESSION["anymail"]["user"]["user_id"]."' ORDER BY `label_name` ASC";
$result = run_query($query);

while ($row = mysql_fetch_assoc($result)){
	$labels[] = $row["label_id"];
}

echo implode($labels, '|');

?>