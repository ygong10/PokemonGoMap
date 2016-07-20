<?php
	require 'connection.php';
	$pokemonName = $_POST['pokemonName'];
	$latitude = $_POST['lat'];
	$longitude = $_POST['long'];
	$hour = $_POST['hour'];
	$minute = $_POST['minute'];

	if (!($stmt = $con->prepare("CALL sp_insertIntoPokemonInfo (?, ?, ?, ?, ?)"))) {
	     echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}

	/* Prepared statement, stage 2: bind and execute */
	if (!$stmt->bind_param("sddii", $pokemonName, $latitude, $longitude, $hour, $minute)) {
	    echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	}

	if (!$stmt->execute()) {
	    echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	}

	/* explicit close recommended */
	$stmt->close();

	$con->close();
?>