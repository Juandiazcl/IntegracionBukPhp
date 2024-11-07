<?php
//header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();

if($_POST['type']=='all'){
	$array = executeSelect("SELECT * FROM AFP ORDER BY des_afp");
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}
}elseif($_POST['type']=='one'){
	$array = executeSelect("SELECT * FROM AFP WHERE cod_afp='".$_POST['cod_afp']."'");
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}
}

?>