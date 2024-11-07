<?php
header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();

if($_POST['type']=='all'){
	$array = executeSelect("SELECT Perfil AS id, PerfNom AS name FROM T0097 ORDER BY Perfil");
	if(count($array)>0){
		for($i=0;$i<count($array);$i++){
			$array[$i]["editar"]='<button class="btn btn-warning" onclick="editRow(\''.$array[$i]['id'].'\')"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>';
			$array[$i]["eliminar"]='<button id="edit" class="btn btn-danger" onclick="deleteRow(\''.$array[$i]['id'].'\')"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';
		}
		//echo json_encode($array);
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}
}elseif($_POST['type']=='one'){
	$array = executeSelect("SELECT Perfil AS id, PerfNom AS name FROM T0097 WHERE Perfil='".$_POST['id']."'");
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}
}

?>