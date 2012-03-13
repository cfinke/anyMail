<?php

session_start();
error_reporting(E_ALL);

if (!isset($_SESSION["anymail"]["user"]["user_id"])){
	header("Location: logout.php");
	exit;
}

if (!isset($_SESSION["anymail"]["lid"])){
	$_SESSION["anymail"]["lid"] = '';
}

if (!isset($_SESSION["anymail"]["cid"])){
	$_SESSION["anymail"]["cid"] = 0;
}

if (!isset($_SESSION["anymail"]["flid"])){
	$_SESSION["anymail"]["flid"] = "inbox";
}

if (!isset($_SESSION["anymail"]["alid"])){
	$_SESSION["anymail"]["alid"] = "";
}

if (!isset($_SESSION["anymail"]["sortby"])){
	$_SESSION["anymail"]["sortby"] = "nice_date";
}

if (!isset($_SESSION["anymail"]["sortdir"])){
	$_SESSION["anymail"]["sortdir"] = "DESC";
}

include("config.php");
include("message_parser.class.php");
include("thread_arc.class.php");
include("thread.class.php");
include("functions.php");

?>