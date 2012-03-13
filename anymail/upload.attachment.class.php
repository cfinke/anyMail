<?php

require_once("upload.message_class.php");

class attachment {
	var $hash;
	var $filename;
	var $mime_type;
	var $encoding;
	var $file_data;
	
	function attachment($i, $message){
		$body = $message->export_body();
		
		$parts = explode('filename="',$body);
		$part = $parts[$i];
		
		$type = strrev($parts[$i - 1]);
		$type = explode(strrev("Content-Type: "), $type, 2);
		$type = explode(";",strrev($type[0]),2);
		$this->mime_type = $type[0];
		
		$encoding = explode("Encoding:",$parts[$i-1]);
		$encoding = explode("\n",$encoding[count($encoding)-1]);
		$this->encoding = trim($encoding[0]);
		
		$file = explode("\r\n\r\n",$part, 2);
		$file = explode("\n--", $file[1]);
		
		$this->file_data = trim($file[0]);
		
		$this->hash = md5($this->file_data);
		
		$filename = explode('"',$part,2);
		$this->filename = str_replace(" ","_",$filename[0]);
	}
}