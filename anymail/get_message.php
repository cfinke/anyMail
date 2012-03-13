<?php

include("globals.php");

$query = "SELECT * FROM `anymail_messages` WHERE `message_id`='".intval($_REQUEST["id"])."'";
$result = db_query($query);
$row = db_fetch_assoc($result);

if ($row["html_part"] != ''){
	$output = $row["html_part"];
	
	if (stristr($output, "</body>") !== false){
		$output = preg_replace("/(<body.*>)/Uis","<body>",$output);
		$html = explodei("<body>",$output, 2);
		$html = explodei("</body>",$output[1], 2);
		$html = $output[0];
	}
	
	// Fix line breaks inside HTML tags where the line break is where a space should be
	$output = preg_replace("/<(.*)(\w+)([\r\n]+)(\w+)(.*)>/Ui","<\\1\\2 \\4\\5>",$output);
	
	//Fix line breaks inside HTML tags where the line break is on a word boundary
	$output = preg_replace("/<(.*)([\r\n]+)(.*)>/Ui","<\\1\\3>",$output);
	
	// Fix unquoted attributes longer than one character
	$output = preg_replace("/<(.*)=([^\"\'])(\S*)([^\"\'])([\s>])/Uis","<\\1=\"\\2\\3\\4\"\\5",$output);
	
	// Fix unquoted one-character attributes
	$output = preg_replace("/<(.*)=(\w)([\s>])/Uis","<\\1=\"\\2\"\\3",$output);
	
	$output = preg_replace("/<link.*>/Uis","",$output);
}
else{
	$output = nl2br(htmlentities($row["text_part"]));
}

$thread = new email_thread($_REQUEST["id"]);
$output .= $thread->thread_nav;

echo '<span id="message_top"></span>'.$output;

$query = "UPDATE `anymail_messages` SET `seen`=1 WHERE `message_id`='".intval($_REQUEST["id"])."'";
$result = db_query($query);

?>