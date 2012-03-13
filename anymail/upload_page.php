<?php

include("globals.php");

if (isset($_REQUEST["is_upload"])){
	if (!is_uploaded_file($_FILES["mupload"]["tmp_name"])){
		exit;
	}
	
	//include("upload.functions.php");
	//include("upload.config.php");
	include("upload.message_class.php");
	include("upload.attachment.class.php");
	
	$mdata = file_get_contents($_FILES["mupload"]["tmp_name"]);
	
	$headers = explode("\r\n\r\n",$mdata, 2);
	$body = $headers[1];
	$headers = $headers[0];
	
	$message = new email_message($headers, $body);
	$attachments = array();
	
	// Get each attachment and write it to the database.
	for ($i = 1; $i <= $message->num_attachments; $i++){
		// Get the data out of the file.
		$file = new attachment($i, $message);
		
		$dupe_query = "SELECT * FROM `anymail_attachments` WHERE `hash`='".$file->hash."'";
		$dupe_result = mysql_query($dupe_query) or die(mysql_error() . '<br /><br />' . $dupe_query);
		
		if (mysql_num_rows($dupe_result) > 0){
			$mysql_data_id = mysql_result($dupe_result, 0, 'data_id');
			$insert_query = "INSERT INTO `anymail_attachments` 
						(`user_id`,`filename`,`mime_type`,`encoding`,`hash`,`data_id`) 
						VALUES
						('".$_SESSION["anymail"]["user"]["user_id"]."',
						 '".mysql_escape_string($file->filename)."',
						 '".mysql_escape_string($file->mime_type)."',
						 '".mysql_escape_string($file->encoding)."',
						 '".mysql_escape_string($file->hash)."',
						 '".$mysql_data_id."')";
			$insert_result = mysql_query($insert_query) or die(mysql_error() . '<br /><br />' . $insert_query);
			
			$attachments[] = mysql_insert_id();
		}
		else{
			$id_query = "SELECT MAX(`data_id`) as `new_id` FROM `anymail_attachment_data`";
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
					$data_insert = "INSERT INTO `anymail_attachment_data`
								(`data_id`,`part_id`,`data`)
								VALUES 
								('".$mysql_data_id."',
								 ".$part_id++.",
								 '".mysql_escape_string($bodypart)."')";
					$insert_result = mysql_query($data_insert) or die(mysql_error() . '<br /><br />' . $data_insert);
				}
				
				$bookmark += 100000;
			}
			
			$insert_query = "INSERT INTO `anymail_attachments` 
						(`user_id`,`filename`,`mime_type`,`encoding`,`hash`,`data_id`) 
						VALUES
						('".$_SESSION["anymail"]["user"]["user_id"]."',
						 '".mysql_escape_string($file->filename)."',
						 '".mysql_escape_string($file->mime_type)."',
						 '".mysql_escape_string($file->encoding)."',
						 '".mysql_escape_string($file->hash)."',
						 '".$mysql_data_id."')";
			$insert_result = mysql_query($insert_query) or die(mysql_error() . '<br /><br />' . $insert_query);
			
			$attachments[] = mysql_insert_id();
		}
	}
	
	$message_query = "INSERT INTO `anymail_messages` 
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
		`seen`,
		`text_part`,
		`html_part`,
		`sent`)
		VALUES (
		'".$_SESSION["anymail"]["user"]["user_id"]."',
		'".mysql_escape_string($message->export_headers())."', 
		'".mysql_escape_string(serialize($attachments))."', 
		'".mysql_escape_string($message->parsed_headers["Return-Path"])."', 
		'".mysql_escape_string($message->parsed_headers["From"])."', 
		'".mysql_escape_string($message->parsed_headers["Reply-To"])."', 
		'".mysql_escape_string($message->parsed_headers["To"])."', 
		'".mysql_escape_string($message->parsed_headers["Subject"])."', 
		'".mysql_escape_string($message->parsed_headers["Cc"])."', 
		'".mysql_escape_string($message->parsed_headers["Message-ID"])."', 
		'".mysql_escape_string($message->parsed_headers["In-Reply-To"])."', 
		'".mysql_escape_string($message->parsed_headers["Date"])."', 
		'".make_timestamp_from_date($message->parsed_headers["Date"])."', 
		'".mysql_escape_string(serialize(array()))."', 
		'0',
		'".substr(mysql_escape_string($message->export_text_body()),0,40000)."',
		'".substr(mysql_escape_string($message->export_html_body()),0,40000)."',
		'".intval(isset($_REQUEST["sent"]))."')";
	$message_result = mysql_query($message_query) or die(mysql_error() . '<br /><br />' . $message_query);
	
	$status_message = '<p>Your e-mail message was uploaded successfully.</p>';
}
else{
	$status_message = '';
}

$output = '
	<html>
		<head>
			<script src="anymail.js" type="text/javascript"></script>
			<link rel="stylesheet" type="text/css" href="style.css" />
			<style type="text/css">
				body, td {
					background-color: #ffffff;
				}
				
				body {
					padding: 10px;
				}
			</style>
		</head>
		<body>
		'.$status_message.'
	<form action="'.$_SERVER["PHP_SELF"].'" method="post" enctype="multipart/form-data">
		<input type="hidden" name="is_upload" value="1" />
		<p>Using the form below, you can upload an e-mail message into your account.</p>
		<input type="file" name="mupload" /> <input type="checkbox" name="sent" value="1" /> I sent this message.<br />
		<input type="submit" value="Upload" />
	</form>
	</body>
	</html>';

echo $output;

?>