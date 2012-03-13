<?php

session_start();

include("config.php");
include("functions.php");

if (isset($_REQUEST["manual_registration"])){
	if ($_REQUEST["protocol"] == "/pop3") $port = 110;
	else $port = 143;
	
	$mailbox = @imap_open('{'.gethostbyname($_REQUEST["host_name"]).':'.$port.$_REQUEST["protocol"].'}INBOX', $_REQUEST["username"], $_REQUEST["password"]);
	
	imap_errors();
	
	if (!$mailbox){
		header("Location: ".$_SERVER["PHP_SELF"]."?bad_host=1");
		exit;
	}
	
	$add_host_query = "INSERT INTO `anymail_hosts`
		(`domain`,
		 `protocol`,
		 `port`)
		VALUES
		('".mysql_escape_string($_REQUEST["host_name"])."',
		 '".$_REQUEST["protocol"]."',
		 '".$port."')";
	$add_host_result = run_query($add_host_query);
	
	$host_id = mysql_insert_id();
	
	// Add the user to the users table.
	$add_user_query = "INSERT INTO `anymail_users`
		(`host_id`,`email_address`,`username`)
		VALUES
		('".$host_id."','".mysql_escape_string($_REQUEST["email_address"])."','".mysql_escape_string($_REQUEST["username"])."')";
	$add_user_result = run_query($add_user_query);
	
	// Set the username and password session variables.
	$_SESSION["anymail"]["user"]["user_id"] = mysql_insert_id();
	$_SESSION["anymail"]["user"]["username"] = $_REQUEST["username"];
	$_SESSION["anymail"]["user"]["password"] = $_REQUEST["password"];
	
	// Set the host information.
	$_SESSION["anymail"]["host"]["domain"] = $_REQUEST["host_name"];
	$_SESSION["anymail"]["host"]["ip"] = gethostbyname($_SESSION["anymail"]["host"]["domain"]);
	$_SESSION["anymail"]["host"]["port"] = $port;
	$_SESSION["anymail"]["host"]["protocol"] = $_REQUEST["protocol"];
	
	// Send the user to their settings.
	header("Location: anymail_main.php");
	exit;
}
else{
	if (!isset($_REQUEST["bad_host"])){
		// Check if the user logging in is already listed in the database.
		$query = "SELECT * FROM `anymail_users` WHERE `email_address`='".$_REQUEST["email_address"]."'";
		$result = run_query($query);
		
		if (mysql_num_rows($result) > 0){
			// If so, collect the data from the database and send the user on his way.
			$row = mysql_fetch_array($result);
			
			// Set the username and password session variables.
			$_SESSION["anymail"]["user"]["user_id"] = $row["user_id"];
			$_SESSION["anymail"]["user"]["username"] = $row["username"];
			$_SESSION["anymail"]["user"]["password"] = $_REQUEST["password"];
			$_SESSION["anymail"]["user"]["email_address"] = $row["email_address"];
			
			// Get the host information for this user.
			$query = "SELECT * FROM `anymail_hosts` WHERE `host_id` = ".$row["host_id"];
			$result = run_query($query);
			$newrow = mysql_fetch_array($result);
			
			// Set the host information.
			$_SESSION["anymail"]["host"]["domain"] = $newrow["domain"];
			$_SESSION["anymail"]["host"]["ip"] = gethostbyname($_SESSION["anymail"]["host"]["domain"]);
			$_SESSION["anymail"]["host"]["port"] = $newrow["port"];
			$_SESSION["anymail"]["host"]["protocol"] = $newrow["protocol"];
			
			// Send the user to the main page.
			header("Location: anymail_main.php");
			exit;
		}
		
		// This is the user's first time logging in with this system.
		
		$host_information = find_host($_REQUEST["email_address"],$_REQUEST["password"]);
		
		if ($host_information){
			// The host was found.
			// Add the host to the hosts table.
			$add_host_query = "INSERT INTO `anymail_hosts`
				(`domain`,
				 `protocol`,
				 `port`)
				VALUES
				('".mysql_escape_string($host_information["host"])."',
				 '".mysql_escape_string($host_information["protocol"])."',
				 '".mysql_escape_string($host_information["port"])."')";
			$add_host_result = run_query($add_host_query);
			
			$host_id = mysql_insert_id();
			
			// Add the user to the users table.
			$add_user_query = "INSERT INTO `anymail_users`
				(`host_id`,`email_address`,`username`)
				VALUES
				('".$host_id."','".mysql_escape_string($_REQUEST["email_address"])."','".mysql_escape_string($host_information["username"])."')";
			$add_user_result = run_query($add_user_query);
			
			// Set the username and password session variables.
			$_SESSION["anymail"]["user"]["user_id"] = mysql_insert_id();
			$_SESSION["anymail"]["user"]["username"] = $host_information["username"];
			$_SESSION["anymail"]["user"]["password"] = $_REQUEST["password"];
			
			// Set the host information.
			$_SESSION["anymail"]["host"]["domain"] = $host_information["domain"];
			$_SESSION["anymail"]["host"]["ip"] = gethostbyname($_SESSION["anymail"]["host"]["domain"]);
			$_SESSION["anymail"]["host"]["port"] = $host_information["port"];
			$_SESSION["anymail"]["host"]["protocol"] = $host_information["protocol"];
			
			// Send the user to their settings.
			header("Location: anymail_main.php");
			exit;
		}
	}
	
	// The system was unable to determine the host.
	// Ask the user for the information.
	
	$output = '
		<html>
			<head>
				<title>anyMail Login</title>
				<link rel="stylesheet" type="text/css" href="style.css" />
			</head>
			<body>
				<center>
					<div id="login" style="border: thin black solid; background-color: #ffffff; width: 40%; margin-left: auto; margin-right: auto; margin-top: 50px; padding: 10px; text-align: left;">
						<form action="'.$_SERVER["PHP_SELF"].'" method="post">';

	if (isset($_REQUEST["bad_host"])){
		$output .= '<p>anyMail could not connect to the host with the information you provided. Please fill in the form below to continue.</p>';
	}
	else{
		$output .= '<p>anyMail could not determine your mail host or username.  Please fill in the form below to continue.</p>
			<input type="hidden" name="email_address" value="'.$_REQUEST["email_address"].'" />
			<input type="hidden" name="password" value="'.$_REQUEST["password"].'" />';
	}
	
	$output .= '
					<input type="hidden" name="manual_registration" value="1" />
						<table>
						<tr>
							<td>Mail host:</td>
							<td><input type="text" name="host_name" /></td>
						</tr>
						<tr>
							<td>Mail protocol:</td>
							<td>
								<select name="protocol">
									<option value="/pop3">POP3</option>
									<option value="">IMAP</option>
								</select>
							</td>
						</tr>';
	
	if (isset($_REQUEST["bad_host"])){
		$output .= '
			<tr>
				<td>E-mail address:</td>
				<td><input type="text" name="email_address" />
			</tr>';
	}
	
	$output .= '
		<tr>
			<td>Username:</td>
			<td><input type="text" name="username" />
		</tr>';
		
	if (isset($_REQUEST["bad_host"])){
		$output .= '
			<tr>
				<td>Password:</td>
				<td><input type="password" name="password" />
			</tr>';
	}
	
	$output .= '
							<tr>
								<td colspan="2" style="text-align: center;"><input type="submit" value="Submit" />
							</tr>
						</table>
					</form>
				</div>
			</body>
		</html>';
	
	echo $output;
	exit;
}

?>