<?php
	require_once('connection.php');
	$pokemonName = $_POST['pokemonName'];
	$sql = sprintf("CALL sp_selectSpawnedPokemonInfoByName('%s')", $pokemonName);
	$result = $con->query($sql);
	$data = array();
	while($row = $result->fetch_assoc()) {
	  $data[] = $row;
	}
	$info = json_encode($data);
	echo $info;
?>