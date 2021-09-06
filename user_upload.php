<?php

$dbServer = 'db';
$dbUser = 'root';
$dbPassword = 'dbpassword';
$dbName = 'test';

$db = new mysqli($dbServer, $dbUser, $dbPassword, $dbName);

$usersTable = "CREATE TABLE Users (
	id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(30) NOT NULL,
	surname VARCHAR(30) NOT NULL,
	email VARCHAR(50) NOT NULL UNIQUE
)";

if (mysqli_query($db, $usersTable)) {
	echo "Created table";
} else {
	echo "Did not create table " . mysqli_error($db);
}

if ($db->connect_error) {
	die("Unable to connect to database: {$db->connect_error}");
}

