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
				break;
			case false:
				if ($dryRun) {
					if ($file) {
						echo "we do a dry run" . PHP_EOL;
					} else {
						echo "you need to specify a file" . PHP_EOL;
					}
				}

				if ($file) {
					$csv = fopen('users.csv', 'r');
					$skipRow1 = 0;

					while (($col = fgetcsv($csv, 0, ", ")) !== FALSE) {
						if ($skipRow1 > 0) {
							$email = (string) $col[2];
							$emailToLower = strtolower($email);

							if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
								$rowToInsert = "INSERT INTO Users (name, surname, email)
								VALUES ('{$col[0]}', '{$col[1]}', '{$emailToLower}')";

								if ($db->query($rowToInsert) === TRUE) {
									echo "New record created" . PHP_EOL;
								} else {
									echo "Error: {$rowToInsert} <br> {$db->error}." . PHP_EOL;
								}
							} else {
								echo "Cannot validate email. Will not add to table." . PHP_EOL;
							}
						}
						$skipRow1++;
					}
				}
				break;
		}
	break;
}
