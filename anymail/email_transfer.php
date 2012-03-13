<?php

include("globals.php");

if (!isset($_REQUEST["iteration"])){
	$iteration = 0;
}
else{
	$iteration = $_REQUEST["iteration"];
}

$query = "SELECT * FROM `email` WHERE `user`=1 ORDER BY `id` ASC LIMIT ".($iteration * 100).",100";
$result = db_query($query);

$x = 0;

if (db_num_rows($result) > 0){
while ($row = db_fetch_assoc($result)){
	$message = create_message_object($row["id"]);
	
	$attachments = array();
	
	// Get each attachment and write it to the database.
	for ($i = 1; $i <= $message->num_attachments; $i++){
		// Get the data out of the file.
		$file = new attachment($i, $row["id"]);
		
		$dupe_query = "SELECT * FROM `adwoioxb_anymail`.`anymail_attachments` WHERE `hash`='".db_escape($file->hash)."'";
		$dupe_result = db_query($dupe_query);
		
		if (db_num_rows($dupe_result) > 0){
			$mysql_data_id = mysql_result($dupe_result, 0, 'data_id');
			$insert_query = "INSERT INTO `adwoioxb_anymail`.`anymail_attachments` 
						(`user_id`,`filename`,`mime_type`,`encoding`,`hash`,`data_id`) 
						VALUES
						('".intval($row["user"])."',
						 '".db_escape($file->filename)."',
						 '".db_escape($file->mime_type)."',
						 '".db_escape($file->encoding)."',
						 '".db_escape($file->hash)."',
						 '".intval($mysql_data_id)."')";
			$insert_result = db_query($insert_query);
			
			$attachments[] = $mysql_data_id;
		}
		else{
			$id_query = "SELECT MAX(`data_id`) as `new_id` FROM `adwoioxb_anymail`.`anymail_attachment_data`";
			$id_result = db_query($id_query);
			
			if (db_num_rows($id_result) == 0){
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
								('".intval($mysql_data_id)."',
								 ".$part_id++.",
								 '".db_escape($bodypart)."')";
					$insert_result = db_query($data_insert);
				}
				
				$bookmark += 100000;
			}
			
			$insert_query = "INSERT INTO `adwoioxb_anymail`.`anymail_attachments` 
						(`user_id`,`filename`,`mime_type`,`encoding`,`hash`,`data_id`) 
						VALUES
						('".intval($row["user"])."',
						 '".db_escape($file->filename)."',
						 '".db_escape($file->mime_type)."',
						 '".db_escape($file->encoding)."',
						 '".db_escape($file->hash)."',
						 '".intval($mysql_data_id)."')";
			$insert_result = db_query($insert_query);
			
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
		'".intval($row["user"])."',
		'".db_escape($message->export_headers())."', 
		'".db_escape(serialize($attachments))."', 
		'".db_escape($row["Return-Path"])."', 
		'".db_escape($row["From"])."', 
		'".db_escape($row["Reply-To"])."', 
		'".db_escape($row["To"])."', 
		'".db_escape($row["Subject"])."', 
		'".db_escape($row["Cc"])."', 
		'".db_escape($row["Message-ID"])."', 
		'".db_escape($row["In-Reply-To"])."', 
		'".db_escape($row["Date"])."', 
		'".make_timestamp_from_date($row["Date"])."', 
		'".db_escape(serialize(array()))."', 
		'0',
		'".substr(db_escape($message->export_text_body()),0,40000)."',
		'".substr(db_escape($message->export_html_body()),0,40000)."')";
	$message_result = db_query($message_query);
	
	echo $x++.'<br />';
}

echo '<a href="email_transfer.php?iteration='.($iteration + 1).'">Next</a>';

}
else{
	echo 'Done.';
}

?>