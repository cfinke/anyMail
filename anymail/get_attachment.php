<?php

include("globals.php");

$query = "SELECT * FROM `anymail_attachments` WHERE `attachment_id`='".intval($_REQUEST["aid"])."' AND `user_id`='".intval($_SESSION["anymail"]["user"]["user_id"])."'";
$result = db_query($query);
$info_row = db_fetch_assoc($result);

$query = "SELECT * FROM `anymail_attachment_data` WHERE `data_id` = '".intval($info_row["data_id"])."' ORDER BY `part_id` ASC";
$result = db_query($query);

$file_contents = '';

while ($row = db_fetch_assoc($result)){
	$file_contents .= $row["data"];
}

if ($info_row["encoding"] == "base64"){
	$file_contents = base64_decode($file_contents);
}

header("Content-Type: ".$info_row["mime_type"]);
header("Content-Disposition: attachment; filename=".$info_row["filename"]);

echo $file_contents;

?>