<?php

session_start();

session_unset($_SESSION["anymail"]);
session_destroy();

header("Location: index.php");
exit;

?>