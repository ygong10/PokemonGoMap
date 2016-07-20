<?php
	require_once('connection.php');
	$sql = "CALL sp_selectAllPokemonInfo";
	$result = $con->query($sql);

	$data = array();
	while($row = $result->fetch_assoc()) {
	  $data[] = $row;
	}
?>