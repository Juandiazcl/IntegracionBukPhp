<?php
//header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();

$year = $_POST['year'];

if($_POST['type']=='all'){
	$array = executeSelect("SELECT * FROM DIAS_FESTIVOS WHERE YEAR(Fecha)=$year");
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}
}

?>