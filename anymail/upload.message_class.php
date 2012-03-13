<?php

class email_message {
	var $parsed_headers = array();
	var $is_multipart = false;
	var $boundary;
	var $parts = array();
	var $has_html = false;
	var $num_attachments = 0;
	
	function email_message($headers, $body){
		$this->boundary = get_boundary($headers);
		
		$this->parsed_headers = parse_headers($headers);
		
		if ($this->boundary != ''){
			$this->is_multipart = true;
			$this->parts = $this->create_parts($body);
			
			$i = count($this->parts) - 1;
			
			// Check for multipart parts
			while ($i >= 0){
				if ($this->parts[$i]->is_multipart){
					// Find out how many subparts the part has.
					$num_parts = count($this->parts[$i]->email_message->parts);
					
					// Add any attachments found in this part to the main part.
					$this->num_attachments += $this->parts[$i]->email_message->num_attachments;
					
					// If this part has html, then the whole message does.
					$this->has_html = ($this->parts[$i]->email_message->has_html) ? true : $this->has_html;
					
					// Initialize the new_parts.  This is important.
					$new_parts = array();
					
					// Copy over the first parts up until the current part.
					for($x = 0; $x < $i; $x++){
						$new_parts[] = $this->parts[$x];
					}
					
					// Copy the subparts as new main parts.
					for ($x = 0; $x < $num_parts; $x++){
						$new_parts[] = $this->parts[$i]->email_message->parts[$x];
					}
					
					// Copy over the remaining parts.
					for ($x = $i + 1; $x < count($this->parts); $x++){
						$new_parts[] = $this->parts[$x];
					}
					
					// Replace the old parts with the new parts.
					$this->parts = $new_parts;
					
					// Increase $i to the last subpart of this part.
					// This allows for recursively checking for subparts.
					$i += $num_parts;
				}
				
				$i--;
			}
		}
		elseif(stristr($this->parsed_headers["Content-Type"], "text/html") !== false){
			$this->boundary = md5(uniqid(time()));
			
			$this->is_multipart = true;
			$this->has_html = true;
			
			$new_html_part = "Content-Type: text/html; charset=\"US-ASCII\"\n";
			$new_html_part .= "\r\n\r\n";
			$new_html_part .= $body;
			
			$new_text_part = "Content-Type: text/plain; charset=\"US-ASCII\"\n";
			$new_text_part .= "\r\n\r\n";
			$new_text_part .= strip_tags($body);
			
			$this->parts[] = new message_part("If you are reading this, then your e-mail client does not support MIME.");
			$this->parts[] = new message_part($new_text_part);
			$this->parts[] = new message_part($new_html_part);
			
			$this->parsed_headers["Content-Type"] = "multipart/alternative; boundary=\"".$this->boundary."\"";
			$this->parsed_headers["MIME-Version"] = "1.0";
		}
		else{
			$this->parts[0] = new message_part("This line is used for internal data and should never be seen.\r\n\r\n".trim($body));
			$this->parts[0]->content_type = "text";
			$this->parts[0]->content_subtype = "plain";
			$this->parts[0]->content_disposition = "inline";
			$this->parts[0]->data = $this->parts[0]->clean_text($this->parts[0]->data);
		}
	}
	
	function create_parts($body){
		$bodyparts = explode("--".$this->boundary,$body);
		
		for($i = 0; $i < count($bodyparts) - 1; $i++){
			$parts[] = new message_part(trim($bodyparts[$i]));
			
			if ($parts[count($parts) - 1]->isfile){
				$this->num_attachments++;
			}
			elseif(strtolower($parts[count($parts) - 1]->content_subtype) == "html"){
				$this->has_html = true;
			}
		}
		
		return $parts;
	}
	
	function export_headers(){
		$output = '';
		
		foreach($this->parsed_headers as $header_name => $header_value){
			$output .= $header_name . ": " . $header_value . "\n";
		}
		
		return $output;
	}
	
	function export_body(){
		$output = '';
		if ($this->is_multipart){
			foreach($this->parts as $part){
				$output .= "\n";
				
				if ($part->content_type != ''){
					$output .= 'Content-Type: '.strtolower($part->content_type) . '/' . strtolower($part->content_subtype) . ';';
				}
				
				if ($part->isfile){
					if ($part->filename != ''){
						$output .= ' name="'.htmlspecialchars($part->filename).'"'."\n";
					}
				}
				else{
					if ($part->charset != ''){
						$output .= ' charset="'.htmlspecialchars($part->charset).'"'."\n";
					}
					else{
						$output .= "\n";
					}
				}
				
				if ($part->content_transfer_encoding != ''){
					$output .= 'Content-Transfer-Encoding: '.$part->content_transfer_encoding."\n";
				}
				
				if ($part->content_disposition != ''){
					$output .= 'Content-Disposition: '.$part->content_disposition.";";
					
					if ($part->isfile){
						if ($part->filename != ''){
							$output .= ' filename="'.htmlspecialchars($part->filename).'"'."\n";
						}
					}
					else{
						$output .= "\n";
					}
				}
				else{
					if ($part->isfile){
						if ($part->filename != ''){
							$output .= 'Content-Disposition: attachment; filename="'.htmlspecialchars($part->filename).'"'."\n";
						}
					}
				}
				
				$output .= "\r\n\r\n";
				$output .= trim($part->data);
				$output .= "\r\n\r\n";
				$output .= '--'.$this->boundary;
			}
			
			$output .= '--';
		}
		else{
			$output = trim($this->parts[0]->data);
		}
		
		return $output;
	}
	
	function export_text_body(){
		if ($this->is_multipart){
			foreach($this->parts as $part){
				if (($part->content_type == "text") && ($part->content_subtype == "plain")){
					$body = $part->data;
					break;
				}
			}
		}
		else{
			$body = $this->parts[0]->data;
		}
		
		return remove_extra_newlines(trim($body));
	}
	
	function export_html_body(){
		if (!$this->is_multipart){
			$body = '';
		}
		else{
			$body = $this->parts[0]->data;
			
			foreach($this->parts as $part){
				if (($part->content_type == "text") && ($part->content_subtype == "html")){
					$body = $part->data;
					return $body;
				}
			}
			
			$body = '';
		}
		
		return $body;
	}
}

class message_part {
	var $content_type;
	var $content_subtype;
	var $content_disposition;
	var $isfile = false;
	var $filename;
	var $content_transfer_encoding;
	var $charset;
	var $data;
	var $parsed_headers = array();
	var $boundary;
	var $is_multipart = false;
	var $email_message;
	
	function message_part($part_data){
		$part_parts = explode("\r\n\r\n",$part_data,2);
		$headers = $part_parts[0];
		
		$this->parsed_headers = parse_headers($headers);
		
		if (isset($this->parsed_headers["Content-Type"])){
			$this->boundary = get_boundary($this->parsed_headers["Content-Type"]);
		}
		else{
			$this->boundary = '';
		}
		
		if ($this->boundary != ''){
			$this->is_multipart = true;
			$this->email_message = new email_message($part_parts[0],$part_parts[1]);
		}
		else{
			if (stristr($part_data,"Content-Type") !== false){
				$this->content_type = $this->parsed_headers["Content-Type"];
				
				if (strstr($this->content_type, ";") !== false){
					$this->content_type = explode(";",$this->content_type, 2);
					$this->content_type = trim(str_replace('"',"",$this->content_type[0]));
				}
				else{
					$this->content_type = explode("\n",$this->content_type, 2);
					$this->content_type = trim(str_replace('"',"",$this->content_type[0]));
				}
				
				$this->content_type = explode("/",$this->content_type, 2);
				$this->content_subtype = strtolower($this->content_type[1]);
				$this->content_type = strtolower($this->content_type[0]);
			}
			
			if (stristr($part_data,"Content-Disposition") !== false){
				$this->content_disposition = $this->parsed_headers["Content-Disposition"];
				
				if (strstr($this->content_disposition,";") !== false){
					$this->content_disposition = explode(";",$this->content_disposition, 2);
					$this->content_disposition = strtolower(trim(str_replace('"',"",$this->content_disposition[0])));
				}
			}
			
			if (stristr($part_data,"Content-Transfer-Encoding") !== false){
				$this->content_transfer_encoding = explodei("Content-Transfer-Encoding:",$part_data,2);
				$this->content_transfer_encoding = $this->content_transfer_encoding[1];
				$this->content_transfer_encoding = explode("\n",$this->content_transfer_encoding, 2);
				$this->content_transfer_encoding = strtolower(trim(str_replace('"',"",$this->content_transfer_encoding[0])));
				
				if ((strtolower($this->content_type) != "text") || ($this->content_disposition != '')){
					$this->isfile = true;
					$this->filename = explode("name=",$part_data,2);
					$this->filename = $this->filename[1];
					$this->filename = explode("\n",$this->filename, 2);
					$this->filename = trim(str_replace('"',"",$this->filename[0]));
				}
			}
			
			if (stristr($part_data,"charset=") !== false){
				$this->charset = explodei("charset=",$part_data,2);
				$this->charset = $this->charset[1];
				$this->charset = explode("\n",$this->charset, 2);
				$this->charset = trim(str_replace('"',"",$this->charset[0]));
			}
			
			$part_data_parts = explode("\r\n\r\n", $part_data, 2);
			
			if (count($part_data_parts) == 2){
				$this->data = trim($part_data_parts[1]);
			}
			else{
				$this->data = $part_data;
			}
			
			if (!$this->isfile){
				if ($this->content_type == "text"){
					$this->data = $this->clean_text($this->data);
				}
				
				if ($this->content_subtype == "html"){
					$this->data = $this->clean_html($this->data);
				}
			}
		}
	}
	
	function clean_text($text){
		$text = str_replace("=\n\r","=\n",$text);
		$text = str_replace("=\r\n","=\r",$text);
		$text = str_replace("=\n","",$text);
		$text = str_replace("=\r","",$text);
		
		$temp_text = preg_replace("/=([0-8a-fA-F])([0-8a-fA-F])/Ui","%\\1\\2",$text);
		
		if ($temp_text != $text){
			$text = urldecode($temp_text);
		}
		
		return $text;
	}
	
	function clean_html($html){
		if (stristr($this->data, "</body>") !== false){
			$html = preg_replace("/(<body.*>)/Uis","<body>",$html);
			$html = explodei("<body>",$html, 2);
			$html = explodei("</body>",$html[1], 2);
			$html = $html[0];
		}
		
		// Fix line breaks inside HTML tags where the line break is where a space should be
		$html = preg_replace("/<(.*)(\w+)([\r\n]+)(\w+)(.*)>/Ui","<\\1\\2 \\4\\5>",$html);
		
		//Fix line breaks inside HTML tags where the line break is on a word boundary
		$html = preg_replace("/<(.*)([\r\n]+)(.*)>/Ui","<\\1\\3>",$html);
		
		// Fix unquoted attributes longer than one character
		$html = preg_replace("/<(.*)=([^\"\'])(\S*)([^\"\'])([\s>])/Uis","<\\1=\"\\2\\3\\4\"\\5",$html);
		
		// Fix unquoted one-character attributes
		$html = preg_replace("/<(.*)=(\w)([\s>])/Uis","<\\1=\"\\2\"\\3",$html);
		
		return $html;
	}
}

function parse_headers($headers){
	$parsed_headers = array();
	$headers = preg_replace("/Message-ID/i","Message-ID", $headers);
	$header_array = explode("\n",$headers);
	
	$headertype = 'DEFAULT';
	$headervalue = '';
	
	foreach($header_array as $line){
		$headerstuff = explode(":", $line, 2);
		$newheadertype = trim($headerstuff[0]);
		$newheadervalue = (isset($headerstuff[1])) ? trim($headerstuff[1]) : '';
		
		if (!isset($parsed_headers[$newheadertype])){
			if (ctype_upper(substr($newheadertype, 0, 1)) && count($headerstuff) == 2){
				$headertype = $newheadertype;
				$headervalue = $newheadervalue;
				
				if ((strtoupper($headertype) != "SUBJECT") && (strtoupper($headertype) != "CONTENT-TYPE")){
					$headervalue = str_replace("'","",str_replace('"',"",$headervalue));
				}
				
				$parsed_headers[$headertype] = $headervalue;
			}
			else{
				if (!isset($parsed_headers[$headertype])){
					$parsed_headers[$headertype] = ' ' . trim($line);
				}
				else{
					$parsed_headers[$headertype] .= ' ' . trim($line);
				}
			}
		}
	}
	
	if (!isset($parsed_headers["Cc"])) $parsed_headers["Cc"] = '';
	if (!isset($parsed_headers["In-Reply-To"])) $parsed_headers["In-Reply-To"] = '';
	if (!isset($parsed_headers["Return-Path"])) $parsed_headers["Return-Path"] = '';
	if (!isset($parsed_headers["Message-ID"])) $parsed_headers["Message-ID"] = '';
	if (!isset($parsed_headers["Reply-To"])) $parsed_headers["Reply-To"] = '';
	
	return $parsed_headers;
}

?>