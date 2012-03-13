<?php

include("globals.php");

$query = "SELECT * FROM `anymail_attachments` WHERE `attachment_id`='".$_REQUEST["aid"]."' AND `user_id`='".$_SESSION["anymail"]["user"]["user_id"]."'";
$result = run_query($query);
$info_row = mysql_fetch_assoc($result);

$query = "SELECT * FROM `anymail_attachment_data` WHERE `data_id` = '".$info_row["data_id"]."' ORDER BY `part_id` ASC";
$result = run_query($query);

$file_contents = '';

while ($row = mysql_fetch_assoc($result)){
	$file_contents .= $row["data"];
}

if ($info_row["encoding"] == "base64"){
	$file_contents = base64_decode($file_contents);
}

header("Content-Type: ".$info_row["mime_type"]);
header("Content-Disposition: attachment; filename=".$info_row["filename"]);

echo $file_contents;

?>