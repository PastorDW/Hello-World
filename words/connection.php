<?php
// Your host, 99% of the time it's localhost.
$db_host = 'localhost';
// Your username for MySQL.
$db_user = 'practie3_dwhit';
// Your password for MySQL.
$db_pass = 'prodigy';
// And your given name for the database.
$db_name = 'practie3_WORDS';

// The database connection.
$con = mysql_connect($db_host, $db_user, $db_pass);
if(!$con) { 
	die("Cannot connect. " . mysql_error());
}
// The database name selection.
$dbselect = mysql_select_db($db_name);
if(!$dbselect) { 
	die("Cannot select database " . mysql_error());
}
?>