<?php

include("globals.php");

if (!isset($_REQUEST["iteration"])){
	$iteration = 0;
}
else{
	$iteration = $_REQUEST["iteration"];
}
mysql_select_db("adwoioxb_efinke");
$query = "SELECT * FROM `adwoioxb_efinke`.`email` WHERE `user`=1 ORDER BY `id` ASC LIMIT ".($iteration * 100).",100";
$result = mysql_query($query);

$x = 0;

if (mysql_num_rows($result) > 0){
while ($row = mysql_fetch_array($result)){
	$message = create_message_object($row["id"]);
	
	$attachments = array();
	
	// Get each attachment and write it to the database.
	for ($i = 1; $i <= $message->num_attachments; $i++){
		// Get the data out of the file.
		$file = new attachment($i, $row["id"]);
		
		$dupe_query = "SELECT * FROM `adwoioxb_anymail`.`anymail_attachments` WHERE `hash`='".$file->hash."'";
		$dupe_result = mysql_query($dupe_query) or die(mysql_error() . '<br /><br />' . $dupe_query);
		
		if (mysql_num_rows($dupe_result) > 0){
			$mysql_data_id = mysql_result($dupe_result, 0, 'data_id');
			$insert_query = "INSERT INTO `adwoioxb_anymail`.`anymail_attachments` 
						(`user_id`,`filename`,`mime_type`,`encoding`,`hash`,`data_id`) 
						VALUES
						('".$row["user"]."',
						 '".mysql_escape_string($file->filename)."',
						 '".mysql_escape_string($file->mime_type)."',
						 '".mysql_escape_string($file->encoding)."',
						 '".mysql_escape_string($file->hash)."',
						 '".$mysql_data_id."')";
			$insert_result = mysql_query($insert_query) or die(mysql_error() . '<br /><br />' . $insert_query);
			
			$attachments[] = $mysql_data_id;
		}
		else{
			$id_query = "SELECT MAX(`data_id`) as `new_id` FROM `adwoioxb_anymail`.`anymail_attachment_data`";
			$id_result = mysql_query($id_query) or die(mysql_error() . '<br /><br />' . $id_query);
			
			if (mysql_num_rows($id_result) == 0){
				$mysql_data_id = 1;
			}
			else{
				$mysql_data_id = mysql_result($id_result, 0, 'new_id') + 1;
			}
			
			$bookmark = 0;
			$part_id = 0;
			
			$length = strlen($file->file_data);
			
			while ($bookmark < $length){
				$bodypart = substr($file->file_data,$bookmark,100000);
				
				if (strlen($bodypart) > 0){
					$data_insert = "INSERT INTO `adwoioxb_anymail`.`anymail_attachment_data`
								(`data_id`,`part_id`,`data`)
								VALUES 
								('".$mysql_data_id."',
								 ".$part_id++.",
								 '".mysql_escape_string($bodypart)."')";
					$insert_result = mysql_query($data_insert) or die(mysql_error() . '<br /><br />' . $data_insert);
				}
				
				$bookmark += 100000;
			}
			
			$insert_query = "INSERT INTO `adwoioxb_anymail`.`anymail_attachments` 
						(`user_id`,`filename`,`mime_type`,`encoding`,`hash`,`data_id`) 
						VALUES
						('".$row["user"]."',
						 '".mysql_escape_string($file->filename)."',
						 '".mysql_escape_string($file->mime_type)."',
						 '".mysql_escape_string($file->encoding)."',
						 '".mysql_escape_string($file->hash)."',
						 '".$mysql_data_id."')";
			$insert_result = mysql_query($insert_query) or die(mysql_error() . '<br /><br />' . $insert_query);
			
			$attachments[] = $mysql_data_id;
		}
	}
	
	$message_query = "INSERT INTO `adwoioxb_anymail`.`anymail_messages` 
		(
		`user_id` , 
		`headers` , 
		`attachments` , 
		`Return-Path` , 
		`From` , 
		`Reply-To` , 
		`To` , 
		`Subject` , 
		`Cc` , 
		`Message-ID` , 
		`In-Reply-To` , 
		`Date` , 
		`nice_date` , 
		`labels` , 
		`read`,
		`text_part`,
		`html_part`)
		VALUES (
		'".$row["user"]."',
		'".mysql_escape_string($message->export_headers())."', 
		'".mysql_escape_string(serialize($attachments))."', 
		'".mysql_escape_string($row["Return-Path"])."', 
		'".mysql_escape_string($row["From"])."', 
		'".mysql_escape_string($row["Reply-To"])."', 
		'".mysql_escape_string($row["To"])."', 
		'".mysql_escape_string($row["Subject"])."', 
		'".mysql_escape_string($row["Cc"])."', 
		'".mysql_escape_string($row["Message-ID"])."', 
		'".mysql_escape_string($row["In-Reply-To"])."', 
		'".mysql_escape_string($row["Date"])."', 
		'".make_timestamp_from_date($row["Date"])."', 
		'".mysql_escape_string(serialize(array()))."', 
		'0',
		'".substr(mysql_escape_string($message->export_text_body()),0,40000)."',
		'".substr(mysql_escape_string($message->export_html_body()),0,40000)."')";
	$message_result = mysql_query($message_query) or die(mysql_error() . '<br /><br />' . $message_query);
	
	echo $x++.'<br />';
}

echo '<a href="email_transfer.php?iteration='.($iteration + 1).'">Next</a>';

}
else{
	echo 'Done.';
}

?>