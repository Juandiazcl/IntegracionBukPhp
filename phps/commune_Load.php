<?php
//header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();

if($_POST['type']=='all'){
	$array = executeSelect("SELECT * FROM CIUDAD ORDER BY ciu_des");
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}
}elseif($_POST['type']=='one'){
	$array = executeSelect("SELECT * FROM CIUDAD WHERE cod_ciu=".$_POST['cod_ciu']);
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}
}

?>