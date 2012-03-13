<?php

include("globals.php");

if (isset($_REQUEST["jto"])) $to = stripslashes($_REQUEST["jto"]);
if (isset($_REQUEST["jcc"])) $cc = stripslashes($_REQUEST["jcc"]);
if (isset($_REQUEST["jbcc"])) $bcc = stripslashes($_REQUEST["jbcc"]);

if (isset($_REQUEST["attach"])){
	attach_file($_FILES["attachment"]);
	
	$to = stripslashes($_REQUEST["to"]);
	$cc = stripslashes($_REQUEST["cc"]);
	$bcc = stripslashes($_REQUEST["bcc"]);
	$subject = stripslashes($_REQUEST["subject"]);
	$message = stripslashes($_REQUEST["message"]);
	
	if (isset($_REQUEST["in_reply_to"])) $inreplyto = $_REQUEST["in_reply_to"];
}
else if (isset($_REQUEST["submit"])){
	foreach($_REQUEST as $key => $value) $_REQUEST[$key] = stripslashes($value);
	
	send_message($_REQUEST);
	
	$output = '
		<html>
			<body onload="parent.location.href=\'anymail_main.php\';">
			</body>
		</html>';
	echo $output;
	
	exit;
}
else if (isset($_REQUEST["cancel"])){
	$output = '
		<html>
			<body onload="parent.location.href=\'anymail_main.php\';">
			</body>
		</html>';
	echo $output;
	
	exit;
}
else{
	$_REQUEST = unserialize(stripslashes($_REQUEST["request_var"]));
	
	if (isset($_REQUEST["type"]) && ($_REQUEST["type"] == "forward")){
		get_forwarded_attachments($_REQUEST["mid"]);
	}
	
	switch ($_REQUEST["type"]){
		case 'replyall':
			$cc =  get_receivers($_REQUEST["mid"]);
			$bcc =  get_ccd($_REQUEST["mid"]);
		case 'reply':
			$to = get_senders($_REQUEST["mid"]);
			
			$query = "SELECT `Message-ID` FROM `anymail_messages` WHERE `message_id`='".$_REQUEST["mid"]."'";
			$result = run_query($query);
			$row = mysql_fetch_Array($result);
			$inreplyto = $row["Message-ID"];
			
		case 'forward':
			$message = get_reply_message($_REQUEST["mid"]);
			$prefix = ($_REQUEST["type"] == "forward") ? "Fwd: " : "Re: ";
			$subject = get_subject($_REQUEST["mid"], $prefix);
			
			break;
		default:
			$_SESSION["anymail"]["compose"]["attached_files"] = array();
			break;
	}
}

$output = '';

$output .= '
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
			<form action="'.$_SERVER["PHP_SELF"].'" method="post" enctype="multipart/form-data">';

if (isset($inreplyto)){
	$output .= '<input type="hidden" name="in_reply_to" value="'.$inreplyto.'"" />';
}

$output .= '
	
	<table cellpadding="0" cellspacing="0" style="background: #ffffff;">
		<tr>
			<td>To:</td>
			<td><input type="text" name="to" id="to" value="'.((isset($to)) ? htmlspecialchars($to) : '').'" style="width: 95%;" /></td>
		</tr>
		<tr>
			<td>Cc:</td>
			<td><input type="text" name="cc" id="cc" value="'.((isset($cc)) ? htmlspecialchars($cc) : '').'" style="width: 95%;" /></td>
		</tr>
		<tr>
			<td>Bcc:</td>
			<td><input type="text" name="bcc" id="bcc" value="'.((isset($bcc)) ? htmlspecialchars($bcc) : '').'" style="width: 95%;" /></td>
		</tr>
		<tr>
			<td>Subject:</td>
			<td><input type="text" name="subject" id="subject" value="'.((isset($subject)) ? htmlspecialchars($subject) : '').'" style="width: 95%;" /></td>
		</tr>
		<tr>
			<td style="vertical-align: top;">Attachments:</td>
			<td>';

$output .= '<input type="file" name="attachment" /> <input type="submit" name="attach" value="Attach File" />';
if (isset($_SESSION["anymail"]["compose"]["attached_files"])){
	if (is_array($_SESSION["anymail"]["compose"]["attached_files"])){
		foreach($_SESSION["anymail"]["compose"]["attached_files"] as $file){
			$output .= '<br />' . $file["name"] ." (" . ($file["size"] / 1000) . " KB)";
		}
	}
}

$output .= '
		</tr>
		<tr>
			<td colspan="2">
				<textarea name="message" id="message" rows="15" cols="40" style="width: 95%;">'.((isset($message)) ? $message : '').'</textarea>
			</td>
		</tr>
		<tr>
			<td colspan="2" style="text-align: center;">
				<input type="submit" name="submit" value="Send Message" />
				<input type="submit" name="cancel" value="Discard" />
			</td>
		</tr>
	</table>
	</form>
</body>
</html>';

echo $output;


function get_senders($mid){
	$query = "SELECT `From` FROM `anymail_messages` WHERE `message_id`='".$mid."'";
	$result = run_query($query);
	$row = mysql_fetch_array($result);
	
	$senders = '';
	
	if ((mysql_num_rows($result) > 0) && ($row["From"] != "")){
		$senders .= $row["From"] . ', ';
	}
	
	if (strlen($senders) == 2) return;
	else return $senders;
}

function get_receivers($mid){
	$query = "SELECT `To` FROM `anymail_messages` WHERE `message_id`='".$mid."'";
	$result = run_query($query);
	$row = mysql_fetch_array($result);
	
	$receivers = '';
	
	if ((mysql_num_rows($result) > 0) && ($row["To"] != "")){
		$receivers .= $row["To"] . ', ';
	}
	
	if (strlen($receivers) == 2) return;
	else return $receivers;
}

function get_ccd($mid){
	$query = "SELECT `Cc` FROM `anymail_messages` WHERE `message_id`='".$mid."'";
	$result = run_query($query);
	$row = mysql_fetch_array($result);
	
	$ccd = '';
	
	if ((mysql_num_rows($result) > 0) && ($row["Cc"] != "")){
		$ccd .= $row["Cc"] . ', ';
	}
	
	if (strlen($ccd) == 2) return;
	else return $ccd;
}

function get_sent_date($mid){
	$query = "SELECT `Date` FROM `anymail_messages` WHERE `message_id`='".$mid."'";
	$result = run_query($query);
	$row = mysql_fetch_array($result);
	return $row["Date"];
}

function get_subject($mid, $prefix = ''){
	$query = "SELECT `Subject` FROM `anymail_messages` WHERE `message_id`='".$mid."'";
	$result = run_query($query);
	$row = mysql_fetch_array($result);
	return $prefix . $row["Subject"];
}

function get_forwarded_attachments($mid){
	$query = "SELECT `attachments` FROM `anymail_messages` WHERE `message_id`='".$mid."'";
	$result = run_query($query);
	$row = mysql_fetch_assoc($result);
	$attachments = unserialize($row["attachments"]);
	
    foreach($attachments as $attachment){
		$tmp_file = make_tmp_file($attachment);
		$_SESSION["anymail"]["compose"]["attached_files"][] = 
			array("name"=>$tmp_file["file_info"]["filename"],
				  "tmp_name"=>$tmp_file["filename"],
				  "mimetype"=>$tmp_file["file_info"]["mime_type"],
				  "size"=>filesize($tmp_file["filename"]));
	}
	
    return;
}

function make_tmp_file($aid){
	$temp_directory = "/tmp/";
	
	do {
		$filename = $temp_directory.rand();
	} while(is_file($filename));
	
	$handle = fopen($filename, "w");
	
	$query = "SELECT * FROM `anymail_attachments` WHERE `attachment_id`='".$aid."'";
	$result = run_query($query);
	$row = mysql_fetch_assoc($result);
	
	$query = "SELECT `data` FROM `anymail_attachment_data` WHERE `data_id`='".$row["data_id"]."' ORDER BY `part_id` ASC";
	$result = run_query($query);
	
	$file_contents = '';
	
	$file_data = array("filename"=>$filename,"file_info"=>$row);
	
	while ($newrow = mysql_fetch_assoc($result)){
		if ($row["encoding"] == "base64"){
			$newrow["data"] = base64_decode($newrow["data"]);
		}
		
		$file_contents .= $newrow["data"];
	}
	
	if ($handle){
		fwrite($handle, $file_contents);
		fclose($handle);
	}
	
	return $file_data;
}

function get_reply_message($mid){
	$query = "SELECT `text_part`,`html_part` FROM `anymail_messages` WHERE `message_id`='".$mid."'";
	$result = run_query($query);
	$row = mysql_fetch_array($result);
	
	if (trim($row["text_part"]) != ''){
		$text = "> " . str_replace("\n", "\n> ", wordwrap(trim($row["text_part"]), 70));
	}
	else{
		$text = "> " . str_replace("\n", "\n> ", wordwrap(trim(strip_tags($row["html_part"])), 70));
	}
	
	$header = "----- Original Message -----\n";
	$header .= "From: ".get_senders($_REQUEST["mid"])."\n";
	$header .= "To: ".get_receivers($_REQUEST["mid"])."\n";
	$header .= "Sent: ".get_sent_date($_REQUEST["mid"])."\n";;
	$header .= "Subject: ".get_subject($_REQUEST["mid"])."\n";
	
	return $header . $text;
}

function send_message($message){
	// This function takes care of sending an e-mail message.
	
	$filenames = array();
	
	
	// If there are files attached, create a filenames[] array to pass to mail_attached
	if (is_array($_SESSION["anymail"]["compose"]["attached_files"])){
		foreach($_SESSION["anymail"]["compose"]["attached_files"] as $file){
			// Only add this file if it has a name.
			if (trim($file["name"] != '')){
				$filenames[] = array("file"=>$file["tmp_name"],
									 "mimetype"=>$file["mimetype"],
									 "filename"=>$file["name"]);
			}
		}
	}
	
	// Mail this as a multi-part message if it has attachments or HTML content.
	$message_object = email($message["to"],
						  $message["subject"],
						  $message["message"],
						  $message["cc"],
						  $message["bcc"],
						  $filenames,
						  (isset($message["in_reply_to"]) ? $message["in_reply_to"] : ''));
	
	write_message_to_database($message_object, true);
	
	// Delete each of the files that were attached.
	// Because these files live in a temporary files directory, they will be deleted eventually,
	// but we delete them here just to be nice.
	if (is_array($filenames)){
		foreach($filenames as $file){
			unlink($file["file"]);
		}
	}
	
	$_SESSION["anymail"]["compose"]["attached_files"] = array();
	
	return;
}

function email($to, $subject, $message, $cc, $bcc, $filenames, $in_reply_to = ''){
	$mobj = new message_parser();
	
	$unique_sep = md5(uniqid(time()));
	
	$headers = "";
	$optional_headers = "";
	$body_headers = "";
	
	$optional_headers .= "To: ". $to."\n";
	
	if (strlen(trim($cc)) > 0) $headers .= "Cc: ".$cc."\n";
	if (strlen(trim($bcc)) > 0)	$headers .= "Bcc: ".$bcc."\n";
	
	$headers .= "Date: ".date("r")."\n";
	$headers .= "From: ".$_SESSION["anymail"]["user"]["email_address"] . "\n";
	$headers .= "Return-Path: ".$_SESSION["anymail"]["user"]["email_address"] . "\n";
	$headers .= "Message-ID: <".md5(uniqid(rand(), true))."@".$_SESSION["anymail"]["host"]["domain"].">\n";
	
	if (strlen(trim($in_reply_to)) > 0)	$headers .= "In-Reply-To: ".$in_reply_to."\n";
	
	$optional_headers .= "Subject: ".$subject."\n";
	
	if (is_array($filenames) && (count($filenames) > 0)){
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-Type: multipart/mixed; boundary=\"$unique_sep\"\n";
		$headers .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
		$body_headers .= "If you are reading this, then your e-mail client does not support MIME.\r\n\r\n";
		$body_headers .= "--$unique_sep\n";
		$body_headers .= "Content-Type: text/plain; charset=\"iso-8859-1\"\n";
		$body_headers .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
		$body_headers .= $message."\r\n\r\n";
		
		foreach($filenames as $val) {
			if(file_exists($val['file'])) {
				$mime_type = explode("/",$val["mimetype"]);
				
				$body_headers .= "--$unique_sep\n";
				$body_headers .= "Content-Type: ".$val["mimetype"]."; name=\"".$val['filename']."\"\n";
				$body_headers .= "Content-Transfer-Encoding: base64\n";
				$body_headers .= "Content-Disposition: attachment; filename=\"".$val['filename']."\"\r\n\r\n";
				$filedata = chunk_split(base64_encode(implode(file($val['file']), '')));
				$body_headers .= $filedata;
				
				$mobj->parts[] = array("type" => $mime_type[0],
									   "subtype" => $mime_type[1],
									   "encoding" => "base64",
									   "filename" => $val["filename"],
									   "data" => $filedata);
			}
		}
		
		$body_headers .= "--$unique_sep--\n";
		
		$mobj->type = 1;
		$mobj->subtype = "mixed";
	}
	else{
		$headers .= "Content-Type: text/plain; charset=\"US-ASCII\"\r\n\r\n";
		$body_headers .= $message;
		
		$mobj->type = 0;
		$mobj->subtype = "plain";
	}
	
	$mobj->parts[] = array("type" => "text",
						   "subtype" => "plain",
						   "encoding" => "quoted-printable",
						   "filename" => "",
						   "data" => $message);
	
	mail($to, $subject, '', $headers.$body_headers);
	
	$mobj->headers = $optional_headers . $headers;
	$mobj->parsed_headers = $mobj->parse_headers($mobj->headers);
	
	return $mobj;
}

function attach_file($file){
	$temp_directory = "/tmp/";
	
	if (is_file($file["tmp_name"])){
		do{
			$filename = $temp_directory.rand();
		} while(is_file($filename));
		
		copy($file["tmp_name"], $filename);
		
		$_SESSION["anymail"]["compose"]["attached_files"][] = array("name"=>$file["name"],"tmp_name"=>$filename,"size"=>$file["size"],"mimetype"=>$file["type"]);
	}
	
	return;
}

function remove_attachment($attachment_key){
	$filename = $_SESSION["anymail"]["compose"]["attached_files"][$attachment_key]["tmp_name"];
	
	if (is_file($filename)){
		@unlink($filename);
	}
	
	unset($_SESSION["anymail"]["compose"]["attached_files"][$attachment_key]);
	
	return;
}

?>