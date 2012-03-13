<?php

include("globals.php");

$thread_arc = new thread_arc($_REQUEST["mid"]);
$thread_arc->export_image();

exit;

?>