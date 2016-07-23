<?php 
$lat = $_POST['lat'];
$long = $_POST['long'];
$location = '"'.$lat.','.$long.'"';
$command = 'venv\Scripts\python test.py -l '.$location ;
$result = shell_exec($command);
if ($result){
	echo "done";
}else{
	echo "not done";
}
?>