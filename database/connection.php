<?php
	require 'credentials.php';
	// Create connection
	$con = new mysqli($servername, $username, $password, $dbname);
	
	// Check connection
	if ($con->connect_errno) {
    	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	}
?>