<?php

$GLOBALS["DATABASE_SLAVES"] = array(
	"127.0.0.1"
);

define('DB_SLAVE_NAME', '');
define('DB_SLAVE_USER', '');
define('DB_SLAVE_PASSWORD', '');

$GLOBALS["DATABASE_MASTERS"] = array(
	"127.0.0.1"
);

define('DB_MASTER_NAME', '');
define('DB_MASTER_USER', '');
define('DB_MASTER_PASSWORD', '');

include "db.php";

?>