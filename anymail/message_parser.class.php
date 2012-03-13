<?php

$mime_types = array("text","multipart","message","application","audio","image","video","other");
$encodings = array("7bit","8bit","binary","base64","quoted-printable","other");

class message_parser {
	var $mbox;
	var $mid;
	
	var $headers;
	var $parsed_headers;
	
	var $structure;
	
	var $type;
	var $subtype;
	
	var $parts = array();
	
	function message_parser($mbox = null, $id = null){
		if (($mbox != null) && ($id != null)){
			$this->mbox = $mbox;
			$this->mid = $id;
			
			// Get the full headers of the message
			$this->headers = imap_fetchheader($this->mbox, $this->mid);
			
			// Parse the headers into an array.
			$this->parsed_headers = $this->parse_headers($this->headers);
			
			// Get the structure of the message.
			$this->structure = imap_fetchstructure($this->mbox, $this->mid);
			
			// Get the main type of the message
			$this->type = $this->structure->type;
			
			// Get any subtypes of the message.
			if ($this->structure->ifsubtype){
				$this->subtype = $this->structure->subtype;
			}
			
			// Get any information about the parts of a multipart message.
			$this->get_part_info($this->structure);
		}
	}
	
	function get_part_info(&$obj){
		global $mime_types;
		global $encodings;
		
		// Create an array to store the part numbers.
		$part_numbers = array();
		
		// Check if the message is multipart.
		if ($obj->type == 1){
			// It is; get the information about each part.
			$this->get_part_info_multipart($obj->parts, "");
		}
		else{
			// It isn't; get the information about the only part.
			
			// Check if the whole message is a file.
			if ($obj->ifdparameters){
				foreach($obj->dparameters as $param){
					if ($param->attribute == "FILENAME"){
						$filename = $param->value;
						break;
					}
				}
			}
			
			if (!isset($filename) && ($obj->ifparameters)){
				foreach($obj->parameters as $param){
					if ($param->attribute == "NAME"){
						$filename = $param->value;
						break;
					}
				}
			}
			
			// Fill in the parts array.
			$this->parts[] = array("part_number" => 1,
								   "type" => $mime_types[$obj->type],
							 	   "subtype" => strtolower($obj->subtype),
							 	   "encoding" => $encodings[$obj->encoding],
								   "filename" => (isset($filename) ? $filename : ''),
								   "data" => imap_fetchbody($this->mbox, $this->mid, 1));
		}
	}
	
	function get_part_info_multipart(&$parts, $part_number){
		global $mime_types;
		global $encodings;
		
		$n = 1;
		
		// Check each sub-part of the part passed in.
		foreach ($parts as $obj) {
			if ($obj->type == 1){
				// Check if this part is multipart itself.
				$this->get_part_info_multipart($obj->parts, "$part_number$n.");
			}
			else{
				// Check if this part is an attachment.
				if ($obj->ifdparameters){
					foreach($obj->dparameters as $param){
						if ($param->attribute == "FILENAME"){
							$filename = $param->value;
							break;
						}
					}
				}
				
				if (!isset($filename) && ($obj->ifparameters)){
					foreach($obj->parameters as $param){
						if ($param->attribute == "NAME"){
							$filename = $param->value;
							break;
						}
					}
				}
				
				$this->parts[] = array("part_number" => "$part_number$n",
									   "type" => $mime_types[$obj->type],
									   "subtype" => strtolower($obj->subtype),
								 	   "encoding" => $encodings[$obj->encoding],
									   "filename" => (isset($filename) ? $filename : ''),
									   "data" => imap_fetchbody($this->mbox, $this->mid, "$part_number$n"));
			}
			
			$n++;
		}
	}
	
	function parse_headers($headers){
		// This function parses headers into an array keyed by the header name.
		
		// Make sure that the following headers are in the correct case.
		$patterns = array("Return-Path:","From:","Reply-To:","To:","Subject:","Cc:","Message-ID:","In-Reply-To:","Date:");
		
		foreach ($patterns as $x){
			$headers = preg_replace("/".$x."/i",$x, $headers);
		}
		
		// Divide the headers up by line.
		$header_array = explode("\n",$headers);
		
		foreach($header_array as $line){
			// Get the header type and value.
			$headerstuff = explode(":", $line, 2);
			$newheadertype = trim($headerstuff[0]);
			$newheadervalue = (count($headerstuff) > 1) ? trim($headerstuff[1]) : '';
			
			// If the header type hasn't been found yet, add the value to the array.
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
					if (isset($headertype)){
						$parsed_headers[$headertype] .= ' ' . trim($line);
					}
				}
			}
		}
		
		// Set default values for these headers.
		if (!isset($parsed_headers["Return-Path"])) $parsed_headers["Return-Path"] = '';
		if (!isset($parsed_headers["From"])) $parsed_headers["From"] = '';
		if (!isset($parsed_headers["Reply-To"])) $parsed_headers["Reply-To"] = '';
		if (!isset($parsed_headers["To"])) $parsed_headers["To"] = '';
		if (!isset($parsed_headers["Subject"])) $parsed_headers["Subject"] = '';
		if (!isset($parsed_headers["Cc"])) $parsed_headers["Cc"] = '';
		if (!isset($parsed_headers["Message-ID"])) $parsed_headers["Message-ID"] = '';
		if (!isset($parsed_headers["In-Reply-To"])) $parsed_headers["In-Reply-To"] = '';
		if (!isset($parsed_headers["Date"])) $parsed_headers["Date"] = '';
		
		return $parsed_headers;
	}
}

?>