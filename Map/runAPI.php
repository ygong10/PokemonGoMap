<?php 
$lat = $_POST['lat'];
$long = $_POST['long'];
$location = '"'.$lat.','.$long.'"';
$id = 0;
$radius = 3;
$cooldown = 10;
$command = 'python3 "scanner/main.py" -id '.$id.' -c '.$location.' -r '.$radius.' -t '.$cooldown;

$result = shell_exec($command);
if ($result){
	echo "not done";
}else{
	echo "done";
}
?>
