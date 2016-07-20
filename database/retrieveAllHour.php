<?php
	require_once 'connection.php';
	$sqlHours = "CALL sp_selectAllHour";
	$hours = $con->query($sqlHours);
?>