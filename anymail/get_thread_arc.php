<?php

include("globals.php");

$arc = new thread_arc($_REQUEST["id"]);
$map = $arc->get_image_map();

$output = $map.'
	<img src="dynamic_image.php?mid='.$_REQUEST["id"].'" style="border: 0;" usemap="#arc_map" />';

echo $output;

?>