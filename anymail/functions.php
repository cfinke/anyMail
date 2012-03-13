<?php

function connect_to_mailbox(){
	// This function establishes a connection to the user's mailbox and
	// returns the resulting resource handler.
	
	$mailbox = imap_open('{'.$_SESSION["anymail"]["host"]["domain"].':'.$_SESSION["anymail"]["host"]["port"].$_SESSION["anymail"]["host"]["protocol"].'}INBOX', $_SESSION["anymail"]["user"]["username"], $_SESSION["anymail"]["user"]["password"]) or die('Error connecting to mail host.');
	
	return $mailbox;
}

function download_messages(){
	// This function downloads all new messages from the server, writes them
	// to the database and returns the number of new messages it downloaded.
	
	// Establish a connection.
	$mailbox = connect_to_mailbox();
	
	// Get the total number of messages.
	$total = imap_num_msg($mailbox);
	
	// Write each message to the database.
	for($msg = $total; $msg > 0; $msg--){
		$message = new message_parser($mailbox, $msg);
		
		write_message_to_database($message);
		
		// Delete the object to save memory.
		unset($message);
		
		// Mark the message for deletion from the server.
		imap_delete($mailbox, $msg);
	}
	
	// Delete the messages marked for deletion.
	imap_expunge($mailbox);
	
	// Call the errors function to avoid having errors sent directly to the browser.
	imap_errors();
	
	// Close the mailbox connection.
	imap_close($mailbox);
	
	// Return the number of new messages.
	return $total;
}

function write_message_to_database($mobj, $sent = false){
	// This function writes a message stored as a message_parser object to the 
	// database.
	
	if (is_array($mobj->parts)){
		$attachments = array();
		
		$html_part = '';
		$text_part = '';
		
		// Find the text part.
		foreach ($mobj->parts as $key => $part){
			if (($part["type"] == 'text') && ($part["subtype"] == 'plain') && ($part["encoding"] != "base64")){
				$text_part = $part["data"];
				unset($mobj->parts[$key]);
				break;
			}
		}
		
		// Find the HTML part.
		foreach ($mobj->parts as $key => $part){
			if (($part["type"] == 'text') && ($part["subtype"] == 'html') && ($part["encoding"] != "base64")){
				$html_part = $part["data"];
				unset($mobj->parts[$key]);
				break;
			}
		}
		
		// The rest of the parts are attachments.
		// Get each attachment and write it to the database.
		foreach ($mobj->parts as $part){
			// Create a hash of the file to check for duplicates.
			$hash = md5($part["data"]);
			
			// Check if the data for this attachment is already stored in the database.
			$dupe_query = "SELECT * FROM `anymail_attachments` WHERE `hash`='".db_escape($hash)."' AND `mime_type`='".db_escape($part["type"]."/".$part["subtype"])."'";
			$dupe_result = db_query($dupe_query);
			
			// If it is, only create a reference to the data.
			if (db_num_rows($dupe_result) > 0){
				$mysql_data_id = mysql_result($dupe_result, 0, 'data_id');
				$insert_query = "INSERT INTO `anymail_attachments` 
							(`user_id`,`filename`,`mime_type`,`encoding`,`hash`,`data_id`) 
							VALUES
							('".intval($_SESSION["anymail"]["user"]["user_id"])."',
							 '".db_escape($part["filename"])."',
							 '".db_escape($part["type"] .'/' . $part["subtype"])."',
							 '".db_escape($part["encoding"])."',
							 '".db_escape($hash)."',
							 '".intval($mysql_data_id)."')";
				$insert_result = db_query($insert_query);
				
				// Add this attachment to the attachments for the message.
				$attachments[] = db_insert_id();
			}
			else{
				// If the data is not in the database, add it, and then add a reference to it.
				
				// Find the biggest data_id.
				$id_query = "SELECT MAX(`data_id`) as `new_id` FROM `anymail_attachments`";
				$id_result = db_query($id_query);
				
				if (db_num_rows($id_result) == 0){
					$mysql_data_id = 1;
				}
				else{
					$mysql_data_id = mysql_result($id_result, 0, 'new_id') + 1;
				}
				
				$bookmark = 0;
				$part_id = 0;
				
				$length = strlen($part["data"]);
				
				// Write the data in chunks of 100000 to the database to avoid
				// the max_packet_size error.
				
				while ($bookmark < $length){
					$bodypart = substr($part["data"],$bookmark,100000);
					
					if (strlen($bodypart) > 0){
						$data_insert = "INSERT INTO `anymail_attachment_data`
									(`data_id`,`part_id`,`data`)
									VALUES 
									('".intval($mysql_data_id)."',
									 ".$part_id++.",
									 '".db_escape($bodypart)."')";
						$insert_result = db_query($data_insert);
					}
					
					$bookmark += 100000;
				}
				
				// Add the reference to the data.
				$insert_query = "INSERT INTO `anymail_attachments` 
							(`user_id`,`filename`,`mime_type`,`encoding`,`hash`,`data_id`) 
							VALUES
							('".intval($_SESSION["anymail"]["user"]["user_id"])."',
							 '".db_escape($part["filename"])."',
							 '".db_escape($part["type"] .'/'.$part["subtype"])."',
							 '".db_escape($part["encoding"])."',
							 '".db_escape($hash)."',
							 '".intval($mysql_data_id)."')";
				$insert_result = db_query($insert_query);
				
				// Add the attachment to the attachments for this message.
				$attachments[] = db_insert_id();
			}
		}
		
		// Write the message info to the database.
		
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
			`text_part`,
			`html_part`,
			`sent`)
			VALUES (
			'".intval($_SESSION["anymail"]["user"]["user_id"])."',
			'".db_escape($mobj->headers)."', 
			'".db_escape(serialize($attachments))."', 
			'".db_escape($mobj->parsed_headers["Return-Path"])."', 
			'".db_escape($mobj->parsed_headers["From"])."', 
			'".db_escape($mobj->parsed_headers["Reply-To"])."', 
			'".db_escape($mobj->parsed_headers["To"])."', 
			'".db_escape($mobj->parsed_headers["Subject"])."', 
			'".db_escape($mobj->parsed_headers["Cc"])."', 
			'".db_escape($mobj->parsed_headers["Message-ID"])."', 
			'".db_escape($mobj->parsed_headers["In-Reply-To"])."', 
			'".db_escape($mobj->parsed_headers["Date"])."', 
			'".make_timestamp_from_date($mobj->parsed_headers["Date"])."', 
			'".db_escape(serialize(array()))."', 
			'".db_escape(substr($text_part,0,40000))."',
			'".db_escape(substr($html_part,0,40000))."',
			'".intval($sent)."')";
		$message_result = db_query($message_query);
	}
	
	return;
}

function display($output){
	// This function displays the page layout.
	
	global $inHead;
	global $inBodyTag;
	global $inBody;
	
	echo '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html>
			<head>
				<title>anyMail</title>
				<link rel="stylesheet" type="text/css" href="style.css" />
				'.$inHead.'
				<script src="anymail.js" type="text/javascript"></script>
			</head>
			<body'.$inBodyTag.'>
				'.$inBody.'
				'.$output.'
			</body>
		</html>';
	exit;
}

function find_host($email, $password){
	// This function attempts to determine the mail host from an e-mail address and
	// a password and returns either an array of host information on success or 
	// false on failure.
	
	// First, get the domain of this host.
	$domain = explode("@", $email);
	$domain = $domain[1];
	
	// Make an array of possible hostnames, in some order of likelihood.
	$subdomains = array("","pop.","mail.","imap.","www.");
	
	// Make an array of possible usernames
	$usernames = array(str_replace(array($domain,"@"),"",$email), $email);
	
	// Make an array of possible port/protocols
	// The first entry is for imap, which uses port 143, and doesn't need to be 
	// specified in the PHP connection string.
	$protocols = array(array("110","/pop3"),array("143",""));
	
	// Now check each combination, in the most probable order.
	
	foreach ($subdomains as $subdomain){
		// Create a hostname by appending each subdomain to the domain.
		$host = $subdomain . $domain;
		$host_ip = gethostbyname($host);
		
		if ($host != $host_ip){
			// Check each username
			foreach ($usernames as $username){
				// Check each protocol
				foreach ($protocols as $protocol){
					// Create the connection string.
					$connection_string = "{".$host_ip.":".$protocol[0].$protocol[1]."}INBOX";
					
					// Check if this combination makes a valid connection.
					// Suppress the output of imap_open() because we will obviously encounter
					// some errors.
					if (@imap_open($connection_string, $username, $password)){
						// We have found the necessary information.
						return array("host"=>$host,"protocol"=>$protocol[1],"port"=>$protocol[0],"username"=>$username);
						exit;
					}
				}
			}
		}
	}
	
	// At this point, none of the combinations worked, most likely because the mail
	// server is not run off of the same domain as the e-mail address indicates.
	
	// Attempt to determine the mail server by running a tracert on the domain.
	// This should be user as a last resort because it is sloooooooooow.
	
	// Get the data output by a tracert command.
	$tracert = exec("traceroute ".$domain);
	$tracert = str_replace("  "," ",$tracert);
	$tracert = explode(" ", $tracert);
	
	// Pull the host name from the tracert.
	$possible_host = trim($tracert[1]);
	
	foreach ($subdomains as $subdomain){
		// Check each subdomain.
		$newhost = $subdomain . $possible_host;
		
		// Check each username.
		foreach ($usernames as $username){
			// Check each protocol/port pair.
			foreach ($protocols as $protocol){
				// Check if this combination makes a valid connection.
				$connection_string = "{".$newhost.":".$protocol[0].$protocol[1]."}INBOX";
				
				// Suppress the output of imap_open() because we will obviously encounter
				// some errors.
				if (@imap_open($connection_string, $username, $password)){
					return array("host"=>$newhost,"protocol"=>$protocol[1],"port"=>$protocol[1],"username"=>$username);
					exit;
				}
			}
		}
	}
	
	// At this point, the host could not be determined.
	return false;
}

function make_timestamp_from_date($date){
	// This function makes a MySQL-style timestamp (YYYYMMDDHHSSMM) from an e-mail timestamp
	$month_key = Array("jan"=>'01',"feb"=>'02',"mar"=>'03',"apr"=>'04',"may"=>'05',"jun"=>'06',"jul"=>'07',"aug"=>'08',"sep"=>'09',"oct"=>'10',"nov"=>'11',"dec"=>'12');
	
	// For now, the timezone will be assumed to be CST.
	$user_timezone = "-0500";
	
	// The day of the week is optional. Check for it here, and remove it.
	if (strstr($date, ",")){
		$date = explode(",",$date);
		$date = trim($date[1]);
	}
	
	// Remove double spaces.
	do {
		$tempdate = str_replace("  "," ",trim($date));
	} while (($tempdate != $date) && ($date = $tempdate));
	
	$dateparts = explode(" ",$date);
	$year = (isset($dateparts[2])) ? $dateparts[2] : date("Y");
	$month = (isset($dateparts[1])) ? $month_key[strtolower($dateparts[1])] : $month_key[strtolower(date("M"))];
	$day = $dateparts[0];
	
	$timeparts = (count($dateparts) > 3) ? explode(":",$dateparts[3]) : array(date("H"),date("i"),date("S"));
	$hour = $timeparts[0];
	$minute = $timeparts[1];
	$second = $timeparts[2];
	
	$unix_time = mktime($hour, $minute, $second, $month, $day, $year);
	
	$timezone = (isset($dateparts[4])) ? substr($dateparts[4],1,4) : "0500";
	$operator = (isset($dateparts[4])) ? ((substr($dateparts[4],0,1) == "-") ? "+" : "-") : "-";	
	$adjustment = ($timezone / 100) * 3600;	
	$xxx = eval("\$unix_time $operator= \$adjustment;");
	
	$user_operator = substr($user_timezone,0,1);
	$user_timezone = substr($user_timezone,1,4);	
	$user_adjustment = ($user_timezone / 100) * 3600;
	$xxx = eval("\$unix_time $user_operator= \$user_adjustment;");
	
	$timestamp = date("YmdHis",$unix_time);
	
	return $timestamp;
}

function explodei($separator, $string, $limit = false ){
	// This function is a case-insensitive version of explode and was found on the
	// php.net site.
	
	$len = strlen($separator);
	
	for ($i = 0; ; $i++ ){
		if (($pos = stripos($string, $separator)) === false || ($limit !== false && $i > $limit - 2 )){
			$result[$i] = $string;
			break;
		}
		
		$result[$i] = substr($string, 0, $pos);
		$string = substr($string, $pos + $len);
	}
	
	return $result;
}

function count_r($arg){
	// This function recursively counts the items in an array.
	
	if ($arg){
		if(!is_array($arg)){
			return 1;
		}
		foreach($arg as $key => $val){
			$count += count_r($val);
		}
		
		return $count;
	}
}

function remove_extra_newlines($text){
	// This function removes any sets of more than two newlines.
	
	do {
		$temptext = str_replace("\n\n\n", "\n\n",$text);
		$temptext = str_replace("\r", "",$temptext);
	} while (($temptext != $text) && ($text = $temptext));
	
	return $temptext;
}

if (!function_exists('stripos')){
	function stripos( $haystack, $needle, $start = 0){
		$haystack = strtolower ( substr($haystack, $start) );
		$needle = strtolower ( $needle );
		return strpos( $haystack, $needle);
	}
}

function delete_message($mid){
	// This function deletes a message and its attachments from the database.
	
	// Check if the message had any attachments.
	$query = "SELECT `attachments` FROM `anymail_messages` WHERE `message_id`='".intval($mid)."'";
	$result = db_query($query);
	$attachments = unserialize(mysql_result($result, 0, "attachments"));
	
	if (count($attachments) > 0){
		// Delete each attachment reference, and the data for each attachment,
		// if it was the only reference to that data.
		
		foreach ($attachments as $aid){
			// Find out if more than one message references this attachment.
			$query = "SELECT `data_id` FROM `anymail_attachments` WHERE `attachment_id` = '".intval($aid)."'";
			$result = db_query($query);
			$data_id = mysql_result($result, 0, 'data_id');
			
			// Delete the reference to the data.
			$query = "DELETE FROM `anymail_attachments` WHERE `attachment_id` = '".intval($aid)."'";
			$result = db_query($query);
			
			$query = "SELECT * FROM `anymail_attachments` WHERE `data_id` = '".intval($data_id)."' LIMIT 1";
			$result = db_query($query);
			
			if (db_num_rows($result) == 0){
				// Delete the data if it was only referenced from one place.
				$query = "DELETE FROM `anymail_attachment_data` WHERE `data_id`='".intval($data_id)."'";
				$result = db_query($query);
			}
		}
	}
	
	// Delete the main message record.
	$query = "DELETE FROM `anymail_messages` WHERE `message_id`='".intval($mid)."'";
	$result = db_query($query);
	
	return;
}

function trash_message($mid){
	// This function moves a message to the "trash."
	$query = "UPDATE `anymail_messages` SET `deleted`='1' WHERE `message_id`='".intval($mid)."'";
	$result = db_query($query);
	
	return;
}

function empty_trash(){
	// This function permanently deletes all messages in the "trash."
	$query = "SELECT `message_id` FROM `anymail_messages` WHERE `deleted`=1 AND `user_id` = '".intval($_SESSION["anymail"]["user"]["user_id"])."'";
	$result = db_query($query);
	
	while ($row = db_fetch_assoc($result)){
		delete_message($row["message_id"]);
	}
	
	return;
}

function label_message($mid, $lid){
	// This function labels a message id'd by $mid with a label id'd by $lid.
	return;
}

function get_footer_row(){
	$output = '
		<tr>
			<td>
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td style="width: 20px; height: 20px;"><img src="images/bottom-left-round.gif" /></td>
						<td style="background-color: #ffffff;">&nbsp;</td>
						<td style="width: 20px; height: 20px;"><img src="images/bottom-right-round.gif" /></td>
					</tr>
				</table>
			</td>
		</tr>';
	
	return $output;
}

function get_header_row(){
	$output = '
		<tr>
			<td>
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td style="width: 20px; vertical-align: bottom;"><img src="images/top-left-round.gif" /></td>
						<td id="mail_tab" class="tab_bg">
							<a onclick="show_main_page();filter_listing();show_labels();" href="javascript:void(0);">
								Mail
							</a>
						</td>
						<td id="compose_tab" class="tab_bg">
							<a onclick="compose(0);" href="javascript:void(0);">
								Compose
							</a>
						</td>
						<td id="labels_tab" class="tab_bg">
							<a onclick="show_labels_page();" href="javascript:void(0);">
								Labels
							</a>
						</td>
						<td id="contacts_tab" class="tab_bg">
							<a onclick="show_contacts_page();" href="javascript:void(0);">
								Contacts
							</a>
						</td>
						<td id="upload_tab" class="tab_bg">
							<a onclick="show_upload_page();" href="javascript:void(0);">
								Upload
							</a>
						</td>
						<td id="logout_tab" class="tab_bg">
							<a href="logout.php">
								Log Out
							</a>
						</td>
						<td style="background-color: #B1B1B1; text-align: right;">
							<select name="global_actions" id="global_actions" onchange="this.selectedIndex = 0;">
								<option value="">Perform Action</option>
								<option value="arcchecked" onclick="archive_messages(getSelectedCheckboxValue(document.forms[0].input_row));">Archive Checked Messages</option>
								<option value="delchecked" onclick="delete_messages(getSelectedCheckboxValue(document.forms[0].input_row));filter_listing();">Delete Checked Messages</option>
								<option value="downchecked" onclick="download_messages(getSelectedCheckboxValue(document.forms[0].input_row));">Download Checked Messages</option>
								<option value="emptytrash" onclick="empty_trash();">Empty Trash</option>
								<optgroup label="Label Checked Messages:">';

	$query = "SELECT * FROM `anymail_labels` WHERE `user_id` = '".intval($_SESSION["anymail"]["user"]["user_id"])."' ORDER BY `label_name`";
	$result = db_query($query);
	
	while ($label_row = db_fetch_assoc($result)){
		$output .= '<option value="'.$label_row["label_id"].'" onclick="label_messages(getSelectedCheckboxValue(document.forms[0].input_row),'.$label_row["label_id"].', \''.str_replace("'","\'",$label_row["label_name"]).'\');">'.$label_row["label_name"].'</option>';
	}

$output .= '
								</optgroup>
							</select>
						</td>
						<td style="width: 20px; vertical-align: bottom;"><img src="images/top-right-round.gif" /></td>
					</tr>
				</table>
			</td>
		</tr>';
	
	return $output;
}

function get_nice_sender($from){
	## This function returns a "nice" sender name, given the value of
	## a header "From" field.  It checks to see if there is a name
	## associated with the address and return that.
	
	$sender = explode('@',$from);
	if (count($sender) > 1){
		$parts = explode(" ",$sender[0]);
		if(count($parts) > 1){
			$from = '';
			for ($i = 0; $i < count($parts) - 1; $i++){
				$from .= ' '.$parts[$i];
			}
		}
		else{
			$misc = explode(" ", $sender[1]);
			$from = $sender[0] . '@' . $misc[0];
		}
	}
	
	$from = trim(str_replace("'","",str_replace("<","",str_replace(">","",$from))));
	
	return trim($from);
}

function get_message_source($id){
	$query = "SELECT * FROM `anymail_messages` WHERE `message_id`='".db_escape($id)."'";
	$result = db_query($query);
	$row = db_fetch_assoc($result);
	$row["attachments"] = unserialize($row["attachments"]);
	
	$body = '';
	$body .= $row["headers"]."\r\n\r\n";
	
	$boundary = get_boundary($row["headers"]);
	
	if ($boundary != ''){
		$body .= "If you are reading this, your e-mail client does not support MIME.\r\n\r\n";
		
		$body .= "--".$boundary."\n";
		$body .= "Content-Type: text/plain; charset=\"iso-8859-1\"\n";
		$body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
		$body .= $row["text_part"]."\n\n";
		
		if (trim($row["html_part"]) != ''){
			$body .= "--".$boundary."\n";
			$body .= "Content-Type: text/html; charset=\"iso-8859-1\"\n";
			$body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
			$body .= $row["html_part"];
		}
		
		foreach($row["attachments"] as $aid){
			$aquery = "SELECT * FROM `anymail_attachments` WHERE `attachment_id`='".intval($aid)."'";
			$aresult = db_query($aquery);
			$arow = db_fetch_assoc($aresult);
			
			$body .= "\r\n\r\n--".$boundary."\n";
			$body .= "Content-Type: ".$arow["mime_type"]."; name=\"".$arow["filename"]."\"\n";
			$body .= "Content-Transfer-Encoding: ".$arow["encoding"]."\n";
			$body .= "Content-Disposition: attachment; filename=\"".$arow["filename"]."\"\r\n\r\n";
			
			$filequery = "SELECT * FROM `anymail_attachment_data` WHERE `data_id` = '".intval($arow["data_id"])."' ORDER BY `part_id` ASC";
			$fileresult = db_query($filequery);
			
			while ($filerow = db_fetch_assoc($fileresult)){
				$body .= $filerow["data"];
			}
		}
		
		$body .=  "\r\n\r\n--".$boundary."--\n";
	}
	else{
		$body .= $row["text_part"];
	}
	
	return $body;
}

function get_boundary($headers){
	$boundary = '';
	
	if (stristr($headers, "boundary=") !== false){
		$boundary = explodei("boundary=",$headers,2);
		$boundary = $boundary[1];
		$boundary = explode("\n",$boundary,2);
		$boundary = $boundary[0];
		$boundary = str_replace('"',"",$boundary);
		$boundary = str_replace("'","",$boundary);
	}
	
	return trim($boundary);
}

?>