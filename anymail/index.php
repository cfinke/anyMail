<?php

// Login page

session_start();
error_reporting(E_ALL);

include("config.php");

$output = '
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
	<html>
		<head>
			<title>anyMail Login</title>
			<link rel="stylesheet" type="text/css" href="style.css" />
		</head>
		<body>
			<form action="login.php" method="post" name="loginform" id="loginform">
				<div id="login">
					<center>
						<table style="border: thin black solid; background-color: #ffffff; width: 40%; margin-left: auto; margin-right: auto; margin-top: 50px; padding:10px;">
							<tr>
								<td colspan="2">
									<h1 style="text-align: center; margin-bottom: 50px;">anyMail</h1>
								</td>
							</tr>
							<tr>
								<td class="formlabel">
									<label for="email_address">E-mail Address:</label>
								</td>
								<td class="forminput">
									<input type="text" name="email_address" />
								</td>
							</tr>
							<tr>
								<td class="formlabel">
									<label for="password">Password:</label>
								</td>
								<td class="forminput">
									<input type="password" name="password" />
								</td>
							</tr>
							<tr>
								<td colspan="2" style="text-align: center;">
									<input type="submit" name="action" value="Log in" />
								</td>
							</tr>
						</table>
					</center>
				</div>
			</form>
		</body>
	</html>';

echo $output;

?>