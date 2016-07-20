<?php
	require_once 'connection.php';
	$sql = "CALL sp_selectAllMinute";
	$result = $con->query($sql);
?>