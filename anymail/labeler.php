<?php

include("globals.php");

foreach($_REQUEST as $key => $val) $_REQUEST[$key] = stripslashes($val);

switch($_REQUEST["action"]){
	case 'new':
		$query = "SELECT * FROM `anymail_labels` WHERE `label_name` LIKE '".db_escape($_REQUEST["label"])."' AND `user_id`='".intval($_SESSION["anymail"]["user"]["user_id"])."'";
		$result = db_query($query);
		
		if (db_num_rows($result) == 0){
			$query = "INSERT INTO `anymail_labels` (`label_name`,`user_id`) VALUES ('".db_escape($_REQUEST["label"])."','".intval($_SESSION["anymail"]["user"]["user_id"])."')";
			$result = db_query($query);
			
			echo db_insert_id();
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
			$result = db_query($query);
			
			while ($row = db_fetch_assoc($result)){
				$labels = unserialize($row["labels"]);
				
				if (in_array($_REQUEST["lid"], $labels)) {
					$key = array_search($_REQUEST["lid"], $labels);
					unset($labels[$key]);
				}
				else{
					$labels[] = $_REQUEST["lid"];
				}
				
				$query = "UPDATE `anymail_messages` SET `labels` = '".db_escape(serialize($labels))."' WHERE `message_id`='".db_escape($row["message_id"])."'";
				db_query($query);
			}
		}
		*/
		
		$query = "SELECT `labels`,`message_id` FROM `anymail_messages` WHERE `message_id` = '".intval($_REQUEST["mid"])."'";
		$result = db_query($query);
		$row = db_fetch_assoc($result);
		
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
		
		$query = "UPDATE `anymail_messages` SET `labels` = '".db_escape(serialize($labels))."' WHERE `message_id`='".db_escape($row["message_id"])."'";
		db_query($query);
		
		break;
}

?>