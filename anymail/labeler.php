<?php

include("globals.php");

foreach($_REQUEST as $key => $val) $_REQUEST[$key] = stripslashes($val);

switch($_REQUEST["action"]){
	case 'new':
		$query = "SELECT * FROM `anymail_labels` WHERE `label_name` LIKE '".mysql_escape_string($_REQUEST["label"])."' AND `user_id`='".$_SESSION["anymail"]["user"]["user_id"]."'";
		$result = run_query($query);
		
		if (mysql_num_rows($result) == 0){
			$query = "INSERT INTO `anymail_labels` (`label_name`,`user_id`) VALUES ('".mysql_escape_string($_REQUEST["label"])."','".$_SESSION["anymail"]["user"]["user_id"]."')";
			$result = run_query($query);
			
			echo mysql_insert_id();
		}
		else{
			echo 0;
		}
		
		break;
	case 'label':
		// Commented code labels entire conversation
		/*
		$thread = new email_thread($_REQUEST["mid"]);
		
		if (is_array($thread->flat_thread_mids)){
			$query = "SELECT `labels`,`message_id` FROM `anymail_messages` WHERE `message_id` IN (".implode($thread->flat_thread_mids,",").")";
			$result = run_query($query);
			
			while ($row = mysql_fetch_assoc($result)){
				$labels = unserialize($row["labels"]);
				
				if (in_array($_REQUEST["lid"], $labels)) {
					$key = array_search($_REQUEST["lid"], $labels);
					unset($labels[$key]);
				}
				else{
					$labels[] = $_REQUEST["lid"];
				}
				
				$query = "UPDATE `anymail_messages` SET `labels` = '".mysql_escape_string(serialize($labels))."' WHERE `message_id`='".$row["message_id"]."'";
				run_query($query);
			}
		}
		*/
		
		$query = "SELECT `labels`,`message_id` FROM `anymail_messages` WHERE `message_id` = '".$_REQUEST["mid"]."'";
		$result = run_query($query);
		$row = mysql_fetch_assoc($result);
		
		$labels = unserialize($row["labels"]);
		
		if (in_array($_REQUEST["lid"], $labels)) {
			$key = array_search($_REQUEST["lid"], $labels);
			
			if ($key !== false){
				unset($labels[$key]);
			}
		}
		else{
			$labels[] = $_REQUEST["lid"];
		}
		
		$query = "UPDATE `anymail_messages` SET `labels` = '".mysql_escape_string(serialize($labels))."' WHERE `message_id`='".$row["message_id"]."'";
		run_query($query);
		
		break;
}

?>