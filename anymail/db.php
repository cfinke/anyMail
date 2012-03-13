<?php

/**
 * This file contains wrapper functions for PHP's MySQL interface.
 * Connections are automatically made to the proper database based on whether
 * the query is reading or writing.
 *
 * @author finke
 */

/**
 * Queries the database.
 *
 * Sends the query to the master for writes or the slave for reads.
 * 
 * @author finke
 * @param string $q The SQL query.
 * @return mixed False on failure, resource identifier on success.
 */
 
function db_query($q) {
	$q = trim($q);
	
	if (stripos(strtoupper($q), "SELECT") === 0) {
		$dbh = db_connect("read");
	}
	else {
		$dbh = db_connect("write");
	}
	
	$result = mysql_query($q, $dbh);

	if (!$result) {
		db_query_error($q, mysql_error($dbh));
	
		return false;
	}
	else {
		return $result;
	}
}

/**
 * Handles errors that arise during a database query.
 *
 * @author finke
 * @param string $query The failed query.
 * @param string $error The error message.
 */

function db_query_error($query, $error) {
	throw new Exception($error . " " . $query);
}

/**
 * Escapes data for inclusion in a SQL query.
 *
 * @author finke
 * @param string $string The data to be escaped.
 * @return string The escaped string.
 */

function db_escape($string) {
	return mysql_escape_string($string);
}

/**
 * Connects to a given database.
 *
 * @author finke
 * @param string $mode The type of connection necessary ("read" by default)
 * @return MySQL link identifier 
 */

function db_connect($mode = "read") {
	if (count($GLOBALS["DATABASE_SLAVES"]) == 0) {
		$mode = "write";
	}
	
	if ($mode === "read") {
		if (!isset($GLOBALS["dbr"])) {
			$mysql_slave = $GLOBALS["DATABASE_SLAVES"][array_rand($GLOBALS["DATABASE_SLAVES"])];
			
			$GLOBALS["dbr"] = mysql_connect($mysql_slave, DB_SLAVE_USER, DB_SLAVE_PASSWORD, true);
			mysql_select_db(DB_SLAVE_NAME, $GLOBALS["dbr"]);
			
			mysql_query("SET NAMES utf8", $GLOBALS["dbr"]);
			mysql_query('SET CHARACTER SET utf8', $GLOBALS["dbr"]);
		}
		
		if (!$GLOBALS["dbr"]) {
			die("We are experiencing some technical difficulties.");
		}
		
		return $GLOBALS["dbr"];
	}
	
	if ($mode === "write") {
		if (!isset($GLOBALS["dbw"])) {
			$mysql_master = $GLOBALS["DATABASE_MASTERS"][array_rand($GLOBALS["DATABASE_MASTERS"])];
			
			$GLOBALS["dbw"] = mysql_connect($mysql_master, DB_MASTER_USER, DB_MASTER_PASSWORD, true);
			mysql_select_db(DB_MASTER_NAME, $GLOBALS["dbw"]);
			
			mysql_query("SET NAMES utf8", $GLOBALS["dbw"]);
		}
		
		if (!$GLOBALS["dbw"]) {
			// @todo Localize.
			die("Sorry, we are experiencing some technical difficulties.");
		}
		
		return $GLOBALS["dbw"];
	}
}

/**
 * Returns the last autoincrement ID created on the write connection.
 *
 * @author finke
 * @return int
 */

function db_insert_id($resource = null) {
	return mysql_insert_id($GLOBALS["dbw"]);
}

function db_fetch_assoc($resource) {
	return mysql_fetch_assoc($resource);
}

function db_num_rows($resource) {
	return mysql_num_rows($resource);
}

function db_found_rows($resource) {
	$count_query = "SELECT FOUND_ROWS()";
	$count_result = db_query($count_query);
	return mysql_result($count_result, 0);
}

/**
 * Closes the connection to a database.
 *
 * @author finke
 * @param string $mode Which connection to close (null by default, indicating all connections)
 */

function db_close($mode = null) {
	if ($mode === "read" || $mode === null) {
		if (isset($GLOBALS["dbr"])) {
			mysql_close($GLOBALS["dbr"]);
			unset($GLOBALS["dbr"]);
		}
	}
	else if ($mode === "write" || $mode === null) {
		if (isset($GLOBALS["dbw"])) {
			mysql_close($GLOBALS["dbw"]);
			unset($GLOBALS["dbw"]);
		}
	}
}

?>