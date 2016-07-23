<?php
	require_once('connection.php');
	$sql = "CALL sp_selectAllSpawnedPokemon";
	$result = $con->query($sql);

	$data = array();
	while($row = $result->fetch_assoc()) {
	  $data[] = $row;
	}

	$info = json_encode($data);
	echo $info;

?>