<?php
$servername = "localhost";
$username = "u461214172_fr";
$password = "FRESH_jj990";
$dbname = "u461214172_fr";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);

	// Check connection
	if ($conn->connect_error) 
	{
	    die("Connection failed: " . $conn->connect_error);
	}