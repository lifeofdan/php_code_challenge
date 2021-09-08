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
$shortParams = "";
$shortParams .= "u:";
$shortParams .= "p:";
$shortParams .= "h:";
$longParams = array(
	"create_table::",
	"dry_run::",
	"file:",
	"help::"
);

// Database Settings & Defaults
$dbServer = 'db';
$dbUser = 'root';
$dbPassword = 'dbpassword';
$dbName = 'test';

$createTable = false;
$help = false;
$dryRun = false;
$file = '';

$options = getopt($shortParams, $longParams, $rest_index);

foreach($options as $option => $value) {
	if ($option === 'help') {
		$help = true;
	}

	if ($option === 'create_table') {
		$createTable = true;
	}

	if ($option === 'h') {
		$dbServer = $value;
	}
	if ($option === 'u') {
		$dbUser = $value;
	}
	if ($option === 'p') {
		$dbPassword = $value;
	}
	if ($option === 'dry_run') {
		$dryRun = true;
	}
	if ($option === 'file') {
		$file = $value;
	}
}

$db = new mysqli($dbServer, $dbUser, $dbPassword, $dbName);

switch ($help) {
	case true:
		echo "here is some help" . PHP_EOL;
		break;
	case false:
		switch ($createTable) {
			case true:
				echo "Attempting to create users table..." . PHP_EOL;
				createTable();
				break;
			case false:
				if ($dryRun) {
					if ($file) {
						echo "we do a dry run" . PHP_EOL;
					} else {
						echo "You need to specify a file" . PHP_EOL;
					}
				}

				if ($file) {
					insertDataFromCVSFile($file, $dryRun);
				} else {
					echo "You need to specify a file." . PHP_EOL;
				}
				break;
		}
	break;
}

function createTable()
{
	global $db;

	$usersTable = "CREATE TABLE Users (
						name VARCHAR(100) NOT NULL,
						surname VARCHAR(100) NOT NULL,
						email VARCHAR(255) NOT NULL UNIQUE
					)";

	if ($db->connect_error) {
		die("Unable to connect to database: {$db->connect_error}" . PHP_EOL);
	}

	if (mysqli_query($db, $usersTable)) {
		echo "Created table" . PHP_EOL;
	} else {
		echo "Did not create table" . mysqli_error($db) . PHP_EOL;
	}
}

function insertDataFromCVSFile(string $file, bool $dryRun)
{
	global $db;

	$csv = fopen($file, 'r');
	$skipRow1 = 0;

	while (($col = fgetcsv($csv, 0, ", ")) !== FALSE) {
		if ($skipRow1 > 0) {
			$email = formatEmail($col[2]);
			$name = formatName($col[0]);
			$lastname = formatName($col[1]);

			if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$query = "INSERT INTO Users VALUES (?, ?, ?)";
				$db->begin_transaction();
				$rowToInsert = $db->prepare($query);
				$rowToInsert->bind_param('sss', $name, $lastname, $email);
				$rowToInsert->execute();
				if(!$dryRun) {
					$db->commit();
				}
				$db->rollback();
			} else {
				echo "Cannot validate email {$email}. Will not add to table." . PHP_EOL;
			}
		}
		$skipRow1++;
	}
}

function formatName($name)
{
	$name = (string) $name;
	$name = strtolower($name);
	$name = ucwords($name, " \t\r\n\f\v'");

	return $name;
}

function formatEmail($email)
{
	$email = (string) $email;
	$email = trim(strtolower($email));
	$email = str_replace('', '', $email);

	return $email;
}
