<?php
	require 'credentials.php';

	$dbname = 'pokemongo';
	// Create connection
	$con = new mysqli($servername, $username, $password, $dbname);
	if ($con->connect_errno) {
    	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	}
?>