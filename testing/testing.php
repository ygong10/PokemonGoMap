<?php
	require('testing_database.php');
	/* Prepared statement, stage 1: prepare */
	if (!($stmt = $con->prepare("CALL sp_insertIntoPokemonInfo (?, ?, ?, ?, ?)"))) {
	     echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}


	$pokemonName = "farfetch'd";
	$latitude = 1.1;
	$longitude = 1.1;
	$hour = 1;
	$minute = 1;

	/* Prepared statement, stage 2: bind and execute */
	if (!$stmt->bind_param("sddii", $pokemonName, $latitude, $longitude, $hour, $minute)) {
	    echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	}

	if (!$stmt->execute()) {
	    echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	}

	/* explicit close recommended */
	$stmt->close();

?>