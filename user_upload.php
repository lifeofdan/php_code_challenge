<?php
/*
	Arguments to take [
		'--create_table' This will create the users table ,
		'--dry_run', This will show the results of importing the file but doesn't write to the db
		'--file', specify the file ot use
		'-u' specify the user for the db
		'-p', specify the password to the db
		'-h', specify the db host
		'-n', specify the db name //Not in the specs but usefull all the same.
		'--help', give help for the cli app with the options and a brief description
	];
*/

// Params
$shortParams = "u:p:h:n:";
$longParams = array(
	"create_table::",
	"dry_run::",
	"file:",
	"help::"
);
$helpMsg =
	"command [Options]" . PHP_EOL . PHP_EOL .
	"Options:" . PHP_EOL .
	"-h [hostname]		Specify database host name." . PHP_EOL .
	"-u [username]		Specify database user name." . PHP_EOL .
	"-p [password]		Specify database password." . PHP_EOL .
	"-n [databasename]	Specify database name." . PHP_EOL .
	"--create_table 		Create the Users table. This only needs to be run this once." . PHP_EOL .
	"--dry_run 		Used with the --file option to run the command without a database insert." . PHP_EOL .
	"--file [filename]	Specify the file you want to import." . PHP_EOL .
	"--help 			Displays help instructions." . PHP_EOL;

// Database Settings & Defaults
$dbServer = 'db';
$dbUser = 'root';
$dbPassword = 'dbpassword';
$dbName = 'test';

$createTable = false;
$help = false;
$dryRun = false;
$file = '';

$params = getopt($shortParams, $longParams, $rest_index);

foreach($params as $param => $value) {
	if ($param === 'help') {
		$help = true;
	}

	if ($param === 'create_table') {
		$createTable = true;
	}

	if ($param === 'h') {
		$dbServer = $value;
	}

	if ($param === 'u') {
		$dbUser = $value;
	}

	if ($param === 'p') {
		$dbPassword = $value;
	}

	if ($param === 'n') {
		$dbName = $value;
	}

	if ($param === 'dry_run') {
		$dryRun = true;
	}

	if ($param === 'file') {
		$file = $value;
	}
}

$db = new mysqli($dbServer, $dbUser, $dbPassword, $dbName);

switch ($help) {
	case true:
		echo $helpMsg;
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
				echo "Cannot validate email {$email}. This row will not add to table." . PHP_EOL;
			}
		}
		$skipRow1++;
	}
}

function formatName($name)
{
	$name = $name;
	/*
		SANITIZE NAMES

		Name fields are difficult to check because of the need for special characters in different countries.
		This preg_replace only replaces a few very obvious characters and numbers.
		This does not include unicode.
	*/
	$name = preg_replace("/[0-9\~!@#\$%\^&\*\(\)=\+\|\[\]\{\};\\:\",\.\<\>\?\/]+/", "", $name);

	$name = strtolower($name);
	$name = ucwords($name, " \t\r\n\f\v'");

	return $name;
}

function formatEmail($email)
{
	$email = $email;
	$email = trim(strtolower($email));
	$email = str_replace('', '', $email);

	return $email;
}
