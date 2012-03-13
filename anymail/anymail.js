var httpRequest;
var requestURL = "get_label_ids.php";
		
if (window.XMLHttpRequest) httpRequest = new XMLHttpRequest();
else if (window.ActiveXObject) httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
	
httpRequest.open("GET", requestURL, false);
httpRequest.send(null);
	
var label_ids = httpRequest.responseText.split('|');
label_ids.push('x');

function change_sortby(sortby){
	document.getElementById("listing_frame").innerHTML = 'Loading messages...';
	document.getElementById("header_header").innerHTML = '';
	document.getElementById("message_header").innerHTML = '';
	document.getElementById("message_body").innerHTML = '';
	
	var requestURL = "filter_listing.php?sortby=" + sortby;
	var httpRequest;
		
	if (window.XMLHttpRequest) {
		httpRequest = new XMLHttpRequest();
	}
	else if (window.ActiveXObject) {
		httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
	}
	
	httpRequest.open("GET", requestURL, false);
	
	httpRequest.send(null);
	
	document.getElementById("listing_frame").innerHTML = httpRequest.responseText;
}

function check_boxes() {
	var state = document.forms[0].checkall.checked;
	
	for (i = 0; i < document.forms[0].elements.length; i++){
		if (document.forms[0].elements[i].type == 'checkbox'){
			document.forms[0].elements[i].checked = state;
		}
	}
}

function color_tab(tab_name){
	var tabs = Array();
	tabs.push("mail_tab");
	tabs.push("compose_tab");
	tabs.push("labels_tab");
	tabs.push("contacts_tab");
	
	for (var i = 0; i < tabs.length; i++){
		document.getElementById(tabs[i]).className = "tab_bg";
	}
	
	document.getElementById(tab_name + "_tab").className = "tab_fg";
}

function compose(id, type, to, cc, bcc){
	if (type == null) type = 'compose';
	
	var httpRequest;
	
	if (window.XMLHttpRequest) {
		httpRequest = new XMLHttpRequest();
	}
	else if (window.ActiveXObject) {
		httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
	}
	
	var requestUrl = "get_composer.php?mid=" + id + "&type=" + type;
	
	if (to != null) requestUrl += "&jto=" + escape(to);
	if (cc != null) requestUrl += "&jcc=" + escape(cc);
	if (bcc != null) requestUrl += "&jbcc=" + escape(bcc);
	
	httpRequest.open("GET", requestUrl, false);
		
	httpRequest.send(null);
	
	document.getElementById("inner_frame").innerHTML = httpRequest.responseText;
	
	document.getElementById("global_actions").style.visibility = "hidden";
	color_tab("compose");
	hide_labels();
	hide_thread_arc();
}

function delete_contact(name, id){
	if (confirm("Are you sure you want to delete the contact \"" + name + "\"?")){
		var httpRequest;
	
		if (window.XMLHttpRequest) {
			httpRequest = new XMLHttpRequest();
		}
		else if (window.ActiveXObject) {
			httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
		}
		
		httpRequest.open("GET", "delete_contact.php?id=" + id, false);
			
		httpRequest.send(null);
		
		show_contacts_page();
		show_contacts();
	}
}

function delete_contact_group(name, id){
	if (confirm("Are you sure you want to delete the contact group \"" + name + "\"?")){
		var httpRequest;
	
		if (window.XMLHttpRequest) {
			httpRequest = new XMLHttpRequest();
		}
		else if (window.ActiveXObject) {
			httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
		}
		
		httpRequest.open("GET", "delete_contact_group.php?id=" + id, false);
			
		httpRequest.send(null);
		
		show_contacts_page();
		show_contacts();
	}
}

function delete_label(labelName){
	if (confirm("Are you sure you want to delete the " + labelName + " label?")){
		var httpRequest;
	
		if (window.XMLHttpRequest) {
			httpRequest = new XMLHttpRequest();
		}
		else if (window.ActiveXObject) {
			httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
		}
		
		httpRequest.open("GET", "delete_label.php?labelName=" + escape(labelName), false);
			
		httpRequest.send(null);
		
		// show_labels();
		show_labels_page();
	}
	else{
		return false;
	}
}

function delete_message(id){
	document.getElementById("thread_arc").innerHTML = "";
	
	delete_message_(id);

	filter_listing();
}

function delete_message_(id){
	var httpRequest;
	
	if (window.XMLHttpRequest) {
		httpRequest = new XMLHttpRequest();
	}
	else if (window.ActiveXObject) {
		httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
	}
	
	httpRequest.open("GET", "delete_message.php?mid=" + id, false);
	
	httpRequest.send(null);
}

function delete_messages(_array){
	for (var i = 0; i < _array.length; i++){
		delete_message_(_array[i]);
	}
}

function deselect_all_auto_labels(){
	var labels = Array();
	labels[0] = "unread";
	labels[1] = "last7";
	labels[2] = "last7-unreplied";
	labels[3] = "contacts";
	labels[4] = "prev-contacts";
	labels[5] = "first-time";
	
	for (var i = 0; i < labels.length; i++){
		document.getElementById("label_row_" + labels[i]).className = "label_row_unselected";
	}
}

function deselect_all_labels(){
	for (var i = 0; i < label_ids.length; i++){
		try {
			document.getElementById('label_row_' + label_ids[i]).className = "label_row_unselected";
		} catch (e) { }
	}
}

function edit_label(oldName, newName){
	var requestURL;
	var httpRequest;
		
	requestURL = "edit_label.php?old_name=" + escape(oldName) + "&new_name=" + escape(newName);
		
	if (window.XMLHttpRequest) {
		httpRequest = new XMLHttpRequest();
	}
	else if (window.ActiveXObject) {
		httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
	}
	
	httpRequest.open("GET", requestURL, false);
	
	httpRequest.send(null);
	
	// show_labels();
	show_labels_page();
}

function edit_contact(name, address, id){
	var newName;
	var newAddress;
	
	if (newName = prompt("Please enter the new contact name:",name)){
		if (newAddress = prompt("Please enter the new address for " + newName + ":",address)){
			var requestURL;
			var httpRequest;
				
			requestURL = "edit_contact.php?name=" + escape(newName) + "&address=" + escape(newAddress) + "&contact_id=" + id;
				
			if (window.XMLHttpRequest) {
				httpRequest = new XMLHttpRequest();
			}
			else if (window.ActiveXObject) {
				httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
			}
			
			httpRequest.open("GET", requestURL, false);
			
			httpRequest.send(null);
			
			show_contacts_page();
			show_contacts();
		}
		else{
			return false;
		}
	}
	else{
		return false;
	}
}

function empty_trash(){
	var requestURL;
	var httpRequest;
		
	requestURL = "empty_trash.php";
		
	if (window.XMLHttpRequest) {
		httpRequest = new XMLHttpRequest();
	}
	else if (window.ActiveXObject) {
		httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
	}
	
	httpRequest.open("GET", requestURL, false);
	
	httpRequest.send(null);
	
	filter_listing();
}

function filter_auto_listing(labelName){
	document.getElementById("listing_frame").innerHTML = 'Loading messages...';
	
	var requestURL;
	var httpRequest;
		
	requestURL = "filter_listing.php?alid=" + escape(labelName);
		
	if (window.XMLHttpRequest) {
		httpRequest = new XMLHttpRequest();
	}
	else if (window.ActiveXObject) {
		httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
	}
	
	httpRequest.open("GET", requestURL, false);
	
	httpRequest.send(null);
	
	document.getElementById("listing_frame").innerHTML = httpRequest.responseText;
}

function filter_listing(lid){
	if (!document.getElementById("listing_frame")){
		show_main_page();
	}

	document.getElementById("listing_frame").innerHTML = 'Loading messages...';
	document.getElementById("header_header").innerHTML = '';
	document.getElementById("message_header").innerHTML = '';
	document.getElementById("message_body").innerHTML = '';
	
	var requestURL;
	var httpRequest;
		
	if (lid == null){
		requestURL = "filter_listing.php";
	}
	else if (lid == "inbox"){
		requestURL = "filter_listing.php?flid=inbox";
	}
	else if (lid == "sent"){
		requestURL = "filter_listing.php?flid=sent";
	}
	else if (lid == "trash"){
		requestURL = "filter_listing.php?flid=trash";
	}
	else{
		requestURL = "filter_listing.php?lid=" + lid;
	}
		
	if (window.XMLHttpRequest) {
		httpRequest = new XMLHttpRequest();
	}
	else if (window.ActiveXObject) {
		httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
	}
	
	httpRequest.open("GET", requestURL, false);
	
	httpRequest.send(null);
	
	document.getElementById("listing_frame").innerHTML = httpRequest.responseText;
}

function get_attachment(){
	var asb = document.getElementById('attachments');
	var aid = asb.options[asb.selectedIndex].value;
	
	if (aid != 0){
		document.location.href = 'get_attachment.php?aid=' + aid;
	}
}

function getSelectedCheckbox(buttonGroup) {
   // Go through all the check boxes. return an array of all the ones
   // that are selected (their position numbers). if no boxes were checked,
   // returned array will be empty (length will be zero)
   var retArr = new Array();
   var lastElement = 0;
   if (buttonGroup[0]) { // if the button group is an array (one check box is not an array)
      for (var i=0; i<buttonGroup.length; i++) {
         if (buttonGroup[i].checked) {
            retArr.length = lastElement;
            retArr[lastElement] = i;
            lastElement++;
         }
      }
   } else { // There is only one check box (it's not an array)
      if (buttonGroup.checked) { // if the one check box is checked
         retArr.length = lastElement;
         retArr[lastElement] = 0; // return zero as the only array value
      }
   }
   return retArr;
} // Ends the "getSelectedCheckbox" function

function getSelectedCheckboxValue(buttonGroup) {
   // return an array of values selected in the check box group. if no boxes
   // were checked, returned array will be empty (length will be zero)
   var retArr = new Array(); // set up empty array for the return values
   var selectedItems = getSelectedCheckbox(buttonGroup);
   if (selectedItems.length != 0) { // if there was something selected
      retArr.length = selectedItems.length;
      for (var i=0; i<selectedItems.length; i++) {
         if (buttonGroup[selectedItems[i]]) { // Make sure it's an array
            retArr[i] = buttonGroup[selectedItems[i]].value;
         } else { // It's not an array (there's just one check box and it's selected)
            retArr[i] = buttonGroup.value;// return that value
         }
      }
   }
   return retArr;
} // Ends the "getSelectedCheckBoxValue" function

function hide_labels(){
	document.getElementById("label_frame").innerHTML = '';
}

function hide_thread_arc(){
	document.getElementById("thread_arc").innerHTML = "";
}

function label_message(mid, lid){
	var httpRequest;
	
	if (window.XMLHttpRequest) httpRequest = new XMLHttpRequest();
	else if (window.ActiveXObject) httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
	
	httpRequest.open("GET", "labeler.php?action=label&mid=" + mid + "&lid=" + lid, false);
	
	httpRequest.send(null);
	
	return true;
}

function archive_messages(_array){
	for (var i = 0; i < _array.length; i++){
		document.getElementById("message_row_" + _array[i]).style.display = 'none';
		document.getElementById("input_row["+_array[i]+"]").checked = false;
	}
	
	var httpRequest;
	
	if (window.XMLHttpRequest) httpRequest = new XMLHttpRequest();
	else if (window.ActiveXObject) httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
	
	httpRequest.open("GET", "archiver.php?mids=" + _array, true);
	
	httpRequest.send(null);
}

function label_messages(_array, lid, lname){
	for (var i = 0; i < _array.length; i++){
		document.getElementById("label_cell_" + _array[i]).innerHTML += lname + ", ";
	}

	var httpRequest;

	if (window.XMLHttpRequest) httpRequest = new XMLHttpRequest();
	else if (window.ActiveXObject) httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
	
	httpRequest.open("GET", "multilabeler.php?mids=" + _array + "&lid=" + lid, true);
	
	httpRequest.send(null);	
}

function new_contact(){
	var contact_name;
	var contact_email;
	
	if (contact_name = prompt("Please enter the name of the contact:")){
		if (contact_email = prompt("Please enter the e-mail address of " + contact_name + ":")){
			add_contact(contact_name, contact_email);
		}
		else{
			return false;
		}
	}
	else{
		return false;
	}
}

function new_contact_group(){
	var contacts = getSelectedCheckboxValue(document.forms[0].addToGroup);
	
	if (contacts.length > 0){
		
		var groupName;
		
		if (groupName = prompt("Please enter the new group name:")){
			var httpRequest;
			
			if (window.XMLHttpRequest) httpRequest = new XMLHttpRequest();
			else if (window.ActiveXObject) httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
	
			httpRequest.open("GET", "add_group.php?name=" + escape(groupName) + "&contact_ids=" + escape(contacts), false);
				
			httpRequest.send(null);
			
			show_contacts_page();
			show_contacts();
		}
		else{
			return false;
		}
	}
	else{
		return false;
	}
}

function new_label(mid){
	var httpRequest;
	var lid;
	var label_name = prompt("Please enter the new label name.");
	
	if (label_name){
		if (window.XMLHttpRequest) httpRequest = new XMLHttpRequest();
		else if (window.ActiveXObject) httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
		
		if (mid != null){
			httpRequest.open("GET", "labeler.php?action=new&mid=" + mid + "&label=" + escape(label_name), false);
			
			httpRequest.send(null);
			
			lid = httpRequest.responseText;
			
			if (lid != 0){
				label_ids[label_ids.lenth] = lid;
				
				if (label_message(mid, lid)){
					show_labels();
					show_header_header(mid);
				}
			}
			else{
				return false;
			}
		}
		else{
			httpRequest.open("GET", "labeler.php?action=new&label=" + escape(label_name), false);
			
			httpRequest.send(null);
			
			lid = httpRequest.responseText;
			
			if (lid != 0){
				label_ids[label_ids.lenth] = lid;
				// show_labels();
				show_labels_page();
			}
			else{
				return false;
			}
		}
	}
	else{
		return false;
	}
}

function add_contact(name, address){
	var httpRequest;

	if (window.XMLHttpRequest) httpRequest = new XMLHttpRequest();
	else if (window.ActiveXObject) httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
		
	httpRequest.open("GET", "add_contact.php?name=" + escape(name) + "&address=" + escape(address), false);
			
	httpRequest.send(null);

	show_contacts_page();
	show_contacts();
}

function select_auto_label(labelName){
	var className = document.getElementById("label_row_" + labelName).className;
	
	deselect_all_auto_labels();
	
	if (className == 'label_row_unselected'){
		document.getElementById("label_row_" + labelName).className = "label_row_selected";
	}
}

function select_label(lid){
	var className = document.getElementById("label_row_" + lid).className;
	
	if ((lid == 'inbox') || (lid == 'sent') || (lid == 'trash')){
		document.getElementById("label_row_inbox").className = "label_row_unselected";
		document.getElementById("label_row_sent").className = "label_row_unselected";
		document.getElementById("label_row_trash").className = "label_row_unselected";
		
		document.getElementById("label_row_" + lid).className = "label_row_selected";
	}
	else{
		deselect_all_labels();
		
		if (className == "label_row_unselected"){
			document.getElementById("label_row_" + lid).className = "label_row_selected";
		}
	}
}

function select_message(id){
	var row_input = document.getElementById("input_row[" + id + "]");
	var row = document.getElementById("message_row_" + id);
	
	if (row.className == "row_selected"){
	   row_input.checked = false;
	   row.className = "row_unselected";
	}
	else if (row.className == "row_unselected"){
	   row_input.checked = true;
	   row.className = "row_selected";
	}
	else{
		 alert(row.className);
	}
}

function show_body(id){
	document.getElementById("message_body").innerHTML = "One moment...";
	
	var httpRequest;
	
	if (window.XMLHttpRequest) httpRequest = new XMLHttpRequest();
	else if (window.ActiveXObject) httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
	
	httpRequest.open("GET", "get_message.php?id=" + id, true);
	
	httpRequest.onreadystatechange = function (evt) {
		if (httpRequest.readyState == 4) {
			document.getElementById("message_body").innerHTML = httpRequest.responseText;
		}
	};
	
	httpRequest.send(null);	
}

function show_contacts(){
	var httpRequest;
	
	if (window.XMLHttpRequest) {
		httpRequest = new XMLHttpRequest();
	}
	else if (window.ActiveXObject) {
		httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
	}
	
	httpRequest.open("GET", "get_contacts.php", true);
	
	httpRequest.onreadystatechange = function (evt) {
		if (httpRequest.readyState == 4) {
			document.getElementById("contact_frame").innerHTML = httpRequest.responseText;
		}
	};
	
	httpRequest.send(null);
}

function show_contacts_page(){
	var httpRequest;
	
	if (window.XMLHttpRequest) {
		httpRequest = new XMLHttpRequest();
	}
	else if (window.ActiveXObject) {
		httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
	}
	
	httpRequest.open("GET", "get_contact_page.php", false);
		
	httpRequest.send(null);
	
	document.getElementById("inner_frame").innerHTML = httpRequest.responseText;
	document.getElementById("global_actions").style.visibility = "hidden";
	document.getElementById("global_actions").style.visibility = "hidden";
	color_tab("contacts");
}

function show_header(id){
	document.getElementById("message_header").innerHTML = "One moment...";
	
	var httpRequest;
	
	if (window.XMLHttpRequest) httpRequest = new XMLHttpRequest();
	else if (window.ActiveXObject) httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
	
	httpRequest.open("GET", "get_header.php?id=" + id, true);
	
	httpRequest.onreadystatechange = function (evt) {
		if (httpRequest.readyState == 4) {
			document.getElementById("message_header").innerHTML = httpRequest.responseText;
		}
	};
	
	httpRequest.send(null);	
}

function show_header_header(id){
	document.getElementById("header_header").innerHTML = "One moment...";
	var httpRequest;
	
	if (window.XMLHttpRequest) httpRequest = new XMLHttpRequest();
	else if (window.ActiveXObject) httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
	
	httpRequest.open("GET", "get_header_header.php?id=" + id, true);
	
	httpRequest.onreadystatechange = function (evt) {
		if (httpRequest.readyState == 4) {
			document.getElementById("header_header").innerHTML = httpRequest.responseText;
		}
	};
	
	httpRequest.send(null);
}

function show_labels(){
	var httpRequest;
	
	if (window.XMLHttpRequest) {
		httpRequest = new XMLHttpRequest();
	}
	else if (window.ActiveXObject) {
		httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
	}
	
	httpRequest.open("GET", "get_labels.php", false);

	httpRequest.send(null);
	
	document.getElementById("label_frame").innerHTML = httpRequest.responseText;
}

function show_labels_page(){
	var httpRequest;
	
	if (window.XMLHttpRequest) {
		httpRequest = new XMLHttpRequest();
	}
	else if (window.ActiveXObject) {
		httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
	}
	
	httpRequest.open("GET", "get_labels_page.php", false);
		
	httpRequest.send(null);
	
	document.getElementById("inner_frame").innerHTML = httpRequest.responseText;
	document.getElementById("global_actions").style.visibility = "hidden";
	color_tab("labels");
	hide_labels();
	// show_labels();
}

function show_upload_page(){
	var httpRequest;
	
	if (window.XMLHttpRequest) {
		httpRequest = new XMLHttpRequest();
	}
	else if (window.ActiveXObject) {
		httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
	}
	
	var requestUrl = "get_upload_page.php";
	
	httpRequest.open("GET", requestUrl, false);
		
	httpRequest.send(null);
	
	document.getElementById("inner_frame").innerHTML = httpRequest.responseText;
	
	document.getElementById("global_actions").style.visibility = "hidden";
	color_tab("upload");
	hide_labels();
	hide_thread_arc();
}

function show_main_page(){
	var httpRequest;
	
	if (window.XMLHttpRequest) {
		httpRequest = new XMLHttpRequest();
	}
	else if (window.ActiveXObject) {
		httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
	}
	
	httpRequest.open("GET", "get_main_page.php", false);

	httpRequest.send(null);
	
	document.getElementById("inner_frame").innerHTML = httpRequest.responseText;
	
	color_tab("mail");
	show_contacts();
}

function show_message(id){
	document.getElementById("message_body").scrollTop = 0;
	
	show_header(id);
	show_body(id);
	show_header_header(id);
	show_thread_arc(id);

	document.getElementById("message_row_" + id).style.fontWeight = "normal";
}

function show_thread_arc(id){
	document.getElementById("thread_arc").innerHTML = "Loading thread arc...";
	var httpRequest;
	
	if (window.XMLHttpRequest) httpRequest = new XMLHttpRequest();
	else if (window.ActiveXObject) httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
	
	httpRequest.open("GET", "get_thread_arc.php?id=" + id, true);
	
	httpRequest.onreadystatechange = function (evt) {
		if (httpRequest.readyState == 4) {
			document.getElementById("thread_arc").innerHTML = httpRequest.responseText;
		}
	};
	
	httpRequest.send(null);
}

function to(email){
	if (!document.getElementById("composer_iframe")){
		compose(0, 'compose', email + ', ');
	}
	else{
		document.getElementById("composer_iframe").contentDocument.getElementById("to").value += email + ', ';
	}
}

function cc(email){
	if (!document.getElementById("composer_iframe")){
		compose(0, 'compose', null, email + ', ');
	}
	else{
		document.getElementById("composer_iframe").contentDocument.getElementById("cc").value += email + ', ';
	}
}

function bcc(email){
	if (!document.getElementById("composer_iframe")){
		compose(0, 'compose', null, null, email + ', ');
	}
	else{
		document.getElementById("composer_iframe").contentDocument.getElementById("bcc").value += email + ', ';
	}
}

function download_messages(mids){
	if (mids.length > 0){
		document.location.href = 'download_messages.php?mids=' + mids;	
		return true;
	}
	else{
		return false;
	}
}