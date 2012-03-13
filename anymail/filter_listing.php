<?php

include("globals.php");

$output = '';
$current_date = '';

if (isset($_REQUEST["sortby"])){
	if ($_REQUEST["sortby"] == $_SESSION["anymail"]["sortby"]){
		$_SESSION["anymail"]["sortdir"] = ($_SESSION["anymail"]["sortdir"] == "ASC") ? "DESC" : "ASC";
	}
	else{
		$_SESSION["anymail"]["sortby"] = $_REQUEST["sortby"];
	}
}

if (isset($_REQUEST["alid"])){
	if ($_REQUEST["alid"] == $_SESSION["anymail"]["alid"]){
		$_SESSION["anymail"]["alid"] = '';
	}
	else{
		$_SESSION["anymail"]["alid"] = $_REQUEST["alid"];
	}
}

if (isset($_REQUEST["lid"])){
	if ($_SESSION["anymail"]["lid"] == $_REQUEST["lid"]){
		$_SESSION["anymail"]["lid"] = '';
	}
	else{
		$_SESSION["anymail"]["lid"] = $_REQUEST["lid"];
	}
}
else{
	download_messages();
}

if (isset($_REQUEST["flid"])){
	$_SESSION["anymail"]["flid"] = $_REQUEST["flid"];
}

switch ($_SESSION["anymail"]["alid"]){
	case 'unread':
		$where_clause = " AND `seen` = 0 ";
		break;
	case 'last7':
		$where_clause = " AND UNIX_TIMESTAMP(`nice_date`) > ".(time() - (7 * 24 * 60 * 60));
		break;
	case 'last7-unreplied':
		$query = "SELECT `message_id`,`Message-ID` FROM `anymail_messages` WHERE UNIX_TIMESTAMP(`nice_date`) > ".(time() - (7 * 24 * 60 * 60));
		$result = db_query($query);
		
		$mids = array();
		
		while ($row = db_fetch_assoc($result)){
			$query = "SELECT `message_id` FROM `anymail_messages` WHERE `In-Reply-To`='".db_escape($row["Message-ID"])."' AND `deleted` = 0 AND `user_id`='".intval($_SESSION["anymail"]["user"]["user_id"])."' AND `sent` = 1";
			$new_result = db_query($query);
			
			if (db_num_rows($new_result) == 0){
				$mids[] = $row["message_id"];
			}
		}
		
		if (count($mids) > 0){
			$where_clause = " AND `message_id` IN (".implode($mids, ',').") ";
		}
		else{
			$where_clause = "AND 3 = 2 ";
		}
		
		break;
	case 'contacts':
		$contacts = array();
		
		$query = "SELECT `contact_email` FROM `anymail_contacts` WHERE `user_id`='".intval($_SESSION["anymail"]["user"]["user_id"])."' GROUP BY `contact_email`";
		$result = db_query($query);
		
		if (db_num_rows($result) > 0){
			$where_clause = " AND (";
			
			while ($row = db_fetch_assoc($result)){
				$where_clause .= " `From` LIKE '%".db_escape($row["contact_email"])."%' OR ";
			}
			
			$where_clause = substr($where_clause, 0, strlen($where_clause) - 3) . ")";
		}
		else{
			$where_clause = " AND 8 = 2";
		}
		
		break;
	case 'prev-contacts':
		$query = "SELECT `From`, COUNT(`From`) AS `num_contacts` FROM `anymail_messages` WHERE `user_id`='".intval($_SESSION["anymail"]["user"]["user_id"])."' GROUP BY `From`";
		$result = db_query($query);
		
		$froms = array();
		
		while($row = db_fetch_assoc($result)){
			if ($row["num_contacts"] > 1){
				$froms[] = db_escape($row["From"]);
			}
		}
		
		if (count($froms) > 0){
			$where_clause = " AND `From` IN ('".implode($froms, '\',\'')."') ";
		}
		else{
			$where_clause = " AND 6 = 2 ";
		}
		
		break;
	case 'first-time':
		$query = "SELECT `message_id`, COUNT(`From`) AS `num_contacts` FROM `anymail_messages` WHERE `user_id`='".intval($_SESSION["anymail"]["user"]["user_id"])."' GROUP BY `From`";
		$result = db_query($query);
		
		$mids = array();
		
		while($row = db_fetch_assoc($result)){
			if ($row["num_contacts"] == 1){
				$mids[] = $row["message_id"];
			}
		}
		
		if (count($mids) > 0){
			$where_clause = " AND `message_id` IN (".implode($mids, ',').") ";
		}
		else{
			$where_clause = " AND 5 = 2 ";
		}
		
		break;
	default:
		$where_clause = " AND 1 ";
		break;
}

if ($_SESSION["anymail"]["lid"] !== ''){
	if ($_SESSION["anymail"]["lid"] == 'x'){
		$where_clause .= " AND `labels` LIKE '".db_escape(serialize(array()))."' ";
	}
	else{
		$where_clause .= " AND `labels` LIKE '%\"".$_SESSION["anymail"]["lid"]."\"%' ";
	}
}

switch ($_SESSION["anymail"]["flid"]){
	case 'inbox':
		$where_clause .= " AND `deleted`=0 AND `sent`=0 ";
		break;
	case 'sent':
		$where_clause .= " AND `deleted`=0 AND `sent`=1 ";
		break;
	case 'trash':
		$where_clause .= " AND `deleted`=1 ";
		break;
}

if ($_SESSION["anymail"]["lid"] == ''){
	$where_clause .= " AND `archived`=0 ";
}

$query = "SELECT *, UNIX_TIMESTAMP(`nice_date`) AS `unix_time` FROM `anymail_messages` WHERE 1 ".$where_clause." AND `user_id`='".intval($_SESSION["anymail"]["user"]["user_id"])."' ORDER BY `".$_SESSION["anymail"]["sortby"]."` ".$_SESSION["anymail"]["sortdir"]." LIMIT 100";
$result = db_query($query);

$old_rows = '';

$output = '<table cellspacing="0" cellpadding="2">';

if (db_num_rows($result) > 0){
	$all_labels = array();
	
	$labelquery = "SELECT * FROM `anymail_labels` WHERE `user_id`='".intval($_SESSION["anymail"]["user"]["user_id"])."' ORDER BY `label_name`";
	$labelresult = db_query($labelquery);
	
	while ($labelrow = db_fetch_assoc($labelresult)) $all_labels[$labelrow["label_id"]] = $labelrow["label_name"];
	
	$output .= '
			<tr class="listing_header">
				<td class="checkbox"><input type="checkbox" name="checkall" id="checkall" onclick="check_boxes();" style="width: 10px; height: 10px;" /></td>
				<td class="date_col"><a href="javascript:void(0);" onclick="change_sortby(\'nice_date\');">Date</a></td>
				<td class="from_col"><a href="javascript:void(0);" onclick="change_sortby(\'From\');">From</a></td>
				<td class="attach_col"><img src="images/paperclip.gif" /></td>
				<td class="subj_col"><a href="javascript:void(0);" onclick="change_sortby(\'Subject\');">Subject</a></td>
				<td class="label_col">Labels</td>
			</tr>';
	
	if ($_SESSION["anymail"]["sortby"] == "nice_date"){
		while($row = db_fetch_assoc($result)){
			$labels = array();
			
			$row["attachments"] = unserialize($row["attachments"]);
			$row["labels"] = unserialize($row["labels"]);
			
			$date = substr($row["nice_date"],0,8);
			
			if ($current_date != $date){
				if ($old_rows != ''){
					$output .= '
						<tr class="date_header" style="display: auto;">
							<td colspan="6">'.date("l  F j  Y", $row["unix_time"]).'</td>
						</tr>';
					
					$output .= $old_rows;
					$old_rows = '';
				}
				
				$unix_time = $row["unix_time"];
			}
			
			$old_rows .= '
				<tr id="message_row_'.$row["message_id"].'" class="row_unselected" style="display: auto;">
					<td>
						<input type="checkbox" id="input_row['.$row["message_id"].']" name="input_row" value="'.htmlspecialchars($row["message_id"]).'" style="width: 10px; height: 10px;" />
					</td>
					<td>
						<abbr title="'.date("l F j, Y g:i a",$row["unix_time"]).'">'.date("g:i a",$row["unix_time"]).'</abbr>
					</td>
					<td>'.htmlentities($row["From"]).'</td>
					<td>'.((count($row["attachments"]) > 0) ? count($row["attachments"]) : '&nbsp;').'</td>
					<td style="text-decoration: none;';
			
			if (!$row["seen"]) $old_rows .= 'font-weight: bold;';
			
			$old_rows .= '"><a href="javascript:void(0);" onclick="show_message('.$row["message_id"].');">'.((trim($row["Subject"]) != '') ? $row["Subject"] : '[No Subject]').'</a></td>
					<td id="label_cell_'.$row["message_id"].'">';
			
			if ($row["sent"]) $labels[] = "Sent";
			if ($row["deleted"]) $labels[] = "Trash";
			
			foreach($row["labels"] as $lid){
				$labels[] = $all_labels[$lid];
			}
			
			$labels = array_unique($labels);
			
			sort($labels);
			
			$label_string = implode($labels, ", ");
			
			if (count($labels) > 0) $label_string .= ", ";
			
			$old_rows .= $label_string;
			
			$old_rows .= '</td>
				</tr>';
			
			$current_date = $date;
			$current_unix_time = $row["unix_time"];
		}
		
		$output .= '
			<tr class="date_header" style="display: auto;">
				<td colspan="6">'.date("l   F j  Y", $current_unix_time).'</td>
			</tr>' . $old_rows;
	}
	else{
		$current_letter = '';
		
		while($row = db_fetch_assoc($result)){
			$labels = array();
			
			$row["attachments"] = unserialize($row["attachments"]);
			$row["labels"] = unserialize($row["labels"]);
			
			$letter = strtoupper(substr($row[$_SESSION["anymail"]["sortby"]],0,1));
			
			if ($current_letter != $letter){
				if ($old_rows != ''){
					$output .= '
						<tr class="date_header" style="display: auto;">
							<td colspan="6">'.strtoupper(htmlentities($current_letter)).'</td>
						</tr>';
					
					$output .= $old_rows;
					$old_rows = '';
				}
			}
			
			$old_rows .= '
				<tr id="message_row_'.$row["message_id"].'" class="row_unselected" style="display: auto;">
					<td>
						<input type="checkbox" id="input_row['.$row["message_id"].']" name="input_row" value="'.htmlspecialchars($row["message_id"]).'" style="width: 10px; height: 10px;" />
					</td>
					<td>
						<abbr title="'.date("l F j, Y g:i a",$row["unix_time"]).'">'.date("g:i a",$row["unix_time"]).'</abbr>
					</td>
					<td>'.htmlentities($row["From"]).'</td>
					<td>'.((count($row["attachments"]) > 0) ? count($row["attachments"]) : '&nbsp;').'</td>
					<td style="text-decoration: none;';
			
			if (!$row["seen"]) $old_rows .= 'font-weight: bold;';
			
			$old_rows .= '"><a href="javascript:void(0);" onclick="show_message('.$row["message_id"].');">'.((trim($row["Subject"]) != '') ? $row["Subject"] : '[No Subject]').'</a></td>
					<td id="label_cell_'.$row["message_id"].'">';
			
			if ($row["sent"]) $labels[] = "Sent";
			if ($row["deleted"]) $labels[] = "Trash";
			
			foreach($row["labels"] as $lid){
				$labels[] = $all_labels[$lid];
			}
			
			$labels = array_unique($labels);
			
			sort($labels);
			
			$label_string = implode($labels, ", ");
			
			if (count($labels) > 0) $label_string .= ", ";
			
			$old_rows .= $label_string;
			
			$old_rows .= '</td>
				</tr>';
			
			$current_letter = strtoupper($letter);
		}
	}
	
	
	if ($_SESSION["anymail"]["sortby"] != "nice_date"){
		$output .= '
			<tr class="date_header" style="display: auto;">
				<td colspan="6">'.$current_letter.'</td>
			</tr>' . $old_rows;
	}
	
	
}
else{
	$output .= '<tr class="no_messages"><td colspan="6">There are no messages to display.</td></tr>';
}

$output .= '</table>';

echo $output;

?>