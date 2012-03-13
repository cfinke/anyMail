<?php

include("globals.php");

$output = '';

$query = "SELECT * FROM `anymail_labels` WHERE `user_id`='".intval($_SESSION["anymail"]["user"]["user_id"])."' ORDER BY `label_name` ASC";
$result = db_query($query);

$output .= '<div style="height: 80px; overflow: auto;">
		<table cellspacing="0" cellpadding="0">
			<tr id="label_row_x" class="';
		if ('x' == $_SESSION["anymail"]["lid"]) $output .= 'label_row_selected';
		else $output .= 'label_row_unselected';
		$output .= '">';
		$output .= '<td><a href="javascript:void(0);" onclick="select_label(\'x\');filter_listing(\'x\');">[none]</a></td>';
		$output .= '</tr>';

if (db_num_rows($result) > 0){
	while ($row = db_fetch_assoc($result)){
		$output .= '<tr id="label_row_'.$row["label_id"].'" class="';
		if ($row["label_id"] == $_SESSION["anymail"]["lid"]) $output .= 'label_row_selected';
		else $output .= 'label_row_unselected';
		$output .= '">';
		$output .= '<td><a href="javascript:void(0);" onclick="select_label('.$row["label_id"].');filter_listing('.$row["label_id"].');">'.$row["label_name"].'</a></td>';
		$output .= '</tr>';
	}
}

$output .= '</table></div><table cellspacing="0" cellpadding="0">
		<tr><td><hr /></td></tr>
		<tr id="label_row_inbox" class="';

if ("inbox" == $_SESSION["anymail"]["flid"]) $output .= 'label_row_selected';
else $output .= 'label_row_unselected';
$output .= '">
			<td><a href="javascript:void(0);" onclick="select_label(\'inbox\');filter_listing(\'inbox\');">Received</a></td>
		</tr>
		<tr id="label_row_sent" class="';

if ("sent" == $_SESSION["anymail"]["flid"]) $output .= 'label_row_selected';
else $output .= 'label_row_unselected';
$output .= '">
			<td><a href="javascript:void(0);" onclick="select_label(\'sent\');filter_listing(\'sent\');">Sent</a></td>
		</tr>
		<tr id="label_row_trash" class="';

if ("trash" == $_SESSION["anymail"]["flid"]) $output .= 'label_row_selected';
else $output .= 'label_row_unselected';
$output .= '">
			<td><a href="javascript:void(0);" onclick="select_label(\'trash\');filter_listing(\'trash\');">Trash</a></td>
		</tr>
		<tr><td><hr /></td></tr>
		<tr id="label_row_unread" class="';if($_SESSION["anymail"]["alid"] == "unread") $output .= 'label_row_selected';else $output .= 'label_row_unselected'; $output .= '">
			<td><a href="javascript:void(0);" onclick="select_auto_label(\'unread\');filter_auto_listing(\'unread\');">Unread Messages</a></td>
		</tr>
		<tr id="label_row_last7" class="';if($_SESSION["anymail"]["alid"] == "last7") $output .= 'label_row_selected';else $output .= 'label_row_unselected'; $output .= '">
			<td><a href="javascript:void(0);" onclick="select_auto_label(\'last7\');filter_auto_listing(\'last7\');">Last 7 Days</a></td>
		</tr>
		<tr id="label_row_last7-unreplied" class="';if($_SESSION["anymail"]["alid"] == "last7-unreplied") $output .= 'label_row_selected';else $output .= 'label_row_unselected'; $output .= '">
			<td><a href="javascript:void(0);" onclick="select_auto_label(\'last7-unreplied\');filter_auto_listing(\'last7-unreplied\');">Last 7 Days Unreplied</a></td>
		</tr>
		<tr id="label_row_contacts" class="';if($_SESSION["anymail"]["alid"] == "contacts") $output .= 'label_row_selected';else $output .= 'label_row_unselected'; $output .= '">
			<td><a href="javascript:void(0);" onclick="select_auto_label(\'contacts\');filter_auto_listing(\'contacts\');">From Address Book Contacts</a></td>
		</tr>
		<tr id="label_row_prev-contacts" class="';if($_SESSION["anymail"]["alid"] == "prev-contacts") $output .= 'label_row_selected';else $output .= 'label_row_unselected'; $output .= '">
			<td><a href="javascript:void(0);" onclick="select_auto_label(\'prev-contacts\');filter_auto_listing(\'prev-contacts\');">From Previous Contacts</a></td>
		</tr>
		<tr id="label_row_first-time" class="';if($_SESSION["anymail"]["alid"] == "first-time") $output .= 'label_row_selected';else $output .= 'label_row_unselected'; $output .= '">
			<td><a href="javascript:void(0);" onclick="select_auto_label(\'first-time\');filter_auto_listing(\'first-time\');">From First-time Contacts</a></td>
		</tr>
	</table>';

echo $output;

?>