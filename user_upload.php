<?php
/* @todo
	Arguments to take [
		'user_upload.php' (this one will not be used but argv will list it as an arg),
	'--create_table' This will create the users table ,
	'--dry_run', This will show the results of importing the file but doesn't write to the db
	'--file', specify the file ot use
	'-u' specify the user for the db
	'-p', specify the password to the db
	'-h', specify the db host
	'--help', give help for the cli app with the options and a brief description
	];
*/

$scriptName = $argv[0]; // Not sure yet if I will utilize this.

// Params
$shortopts = "";
$shortopts .= "u:";
$shortopts .= "p:";
$shortopts .= "h:";
$longopts = array(
	"create_table::",
	"dry_run::",
	"file:",
	"help::"
);

// Database Settings & Defaults
$dbServer = '';
$dbUser = '';
$dbPassword = '';
$dbName = 'test';

$createTable = false;
$help = false;
$dryRun = false;
$file = '';

$options = getopt($shortopts, $longopts, $rest_index);
