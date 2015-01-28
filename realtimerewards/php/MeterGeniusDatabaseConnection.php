<?php

#returns the database sql connection
function getDatabaseConnection(){

	$servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "Meter_Genius_Data";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
	}

	return $conn;

}


function closeDatabaseConnection($conn){

	$conn->close;

}


function getMySqlPDO(){

	# Database credentials
	$mysql_host = "localhost";
	$mysql_database = "Meter_Genius_Data";
	$mysql_user = "root";
	$mysql_password = "";
	# MySQL with PDO_MYSQL  
	$pdo = new PDO("mysql:host=$mysql_host", $mysql_user, $mysql_password);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	return $pdo;




}


?>