<?php

include("globals.php");

if (!isset($_REQUEST["mids"])) exit;

$mids = explode(",",$_REQUEST["mids"]);

if (count($mids) == 0) exit;

create_zip($mids);

function create_zip($mids){
	$i = 0;
	do {
		$dir = "anyMail.".date("Y.m.d").".".++$i . "/";
	} while (is_dir($dir));
	
	mkdir($dir, 0777);
	
	write_email_files($mids, $dir);
	
	do {
		$filename = rand();
	} while (is_file($filename));
	
	system("tar -cf ".$filename. " " .$dir);
	
	$handle = fopen($filename, "r");
	
	if ($handle){
		$file = fread($handle, filesize($filename));
		fclose($handle);
		
		// Send the content type
		header("Content-type: application/zip;");
		
		// Get a filename for it too
		header("Content-Disposition: attachment; filename=anyMail.".date("Y.m.d").".tar;");
		
		// Send the attachment
		echo $file;
		
		system("rm -r ".$filename);
		system("rm -r ".$dir);
	}
	
	return;
}

function write_email_files($mids, $dir){
	foreach($mids as $mid){
		$file_array[] = write_email_to_file($mid, $dir);
	}
	
	return;
}

function write_email_to_file($id, $dir){
	$query = "SELECT `Subject`,UNIX_TIMESTAMP(`nice_date`) AS `unix_date` FROM `anymail_messages` WHERE `message_id`=".$id;
	$result = run_query($query);
	$row = mysql_fetch_assoc($result);
	
	$date = date("Y-m-d",$row["unix_date"]);
	
	$text = get_message_source($id);
	
	$filename = str_replace("?","",
				str_replace(":","-",
				str_replace("/","-",
				str_replace("\\","-",
				str_replace("|","-",
				str_replace('"',"'",
				str_replace("*","",
				str_replace("<","",
				str_replace(">","",substr($row["Subject"],0,56))))))))));
	
	if (trim($filename) == ""){
		$filename = '[No Subject]';
	}
	
	$filename = $date .' - '. $filename;
	
	$temp_filename = $filename;
	$i = 1;
	
	while (is_file($dir . $filename . '.eml')){
		$filename = $temp_filename . ' (' . $i++ . ')';
	}
	
	$filename .= '.eml';
	
	$handle = fopen($dir . $filename, "w");
	fwrite($handle, $text);
	fclose($handle);
	
	return $filename;
}

?>