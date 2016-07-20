<?php
	//require_once 'connection.php';
	$sqlNames = "CALL sp_selectAllPokemonName";
	
	$names = $con->query($sqlNames);
		ChromePhp::log($result);
	
?>