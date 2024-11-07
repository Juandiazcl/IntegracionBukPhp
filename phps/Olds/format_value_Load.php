<?php
header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();

if($_POST['type']=='all'){
	$array = executeSelect("SELECT v.*, f.name
							FROM format_value v 
							LEFT JOIN format_type f ON f.id=v.format_type_id
							ORDER BY v.id");
	if(count($array)>0){
		for($i=0;$i<count($array);$i++){
			$array[$i]["editar"]='<button class="btn btn-warning" onclick="editRow('.$array[$i]['id'].')" '.$_SESSION["display"]["format_values"]["update"].'><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>';
			$array[$i]["eliminar"]='<button id="edit" class="btn btn-danger" onclick="deleteRow('.$array[$i]['id'].')" '.$_SESSION["display"]["format_values"]["delete"].'><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';
		}
		echo json_encode($array);
	}else{
		echo 0;
	}	
}elseif($_POST['type']=='one'){
	$array = executeSelect("SELECT * FROM format_value WHERE id=".$_POST['id']);
	if(count($array)>0){
		echo json_encode($array);
	}else{
		echo 0;
	}		
}
?>