<?php
//header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();

if($_POST['type']=='all'){
	$array = executeSelect("SELECT * FROM ESTCIV ORDER BY desciv");
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}
}elseif($_POST['type']=='one'){
	$array = executeSelect("SELECT * FROM ESTCIV WHERE Estciv=".$_POST['Estciv']);
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}
}

?>