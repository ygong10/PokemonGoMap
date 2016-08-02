<?php
	require_once('connection.php');
	#$sql = "CALL sp_selectAllPokemonInfo";
	$sql = "SELECT * FROM pokemonInfo LIMIT 0, 10000";
	$result = $con->query($sql);

	$data = array();
	while($row = $result->fetch_assoc()) {
	  $data[] = $row;
	}
	echo json_encode($data);
?>
