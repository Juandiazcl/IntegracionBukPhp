<?php
//header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();

if($_POST['type']=='all'){
	$array = executeSelect("SELECT * FROM T0058");
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}

} else if ($_POST['type']=='periodo') {
	$array = executeSelect("SELECT * FROM PeriodosBuk WHERE status='abierto'");
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}
}

?>