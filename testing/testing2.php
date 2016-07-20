<?php
	require 'testing_database.php';
	$sql = "CALL sp_selectAllPokemonName";
	
	//$result = mysqli_query($con, $sql); // Procedural
	if ($result = $con->query($sql)){
		$result;
            while ($row = $result->fetch_assoc()){
		      echo $row['pokemonName'];
		  }  	
	}
?>