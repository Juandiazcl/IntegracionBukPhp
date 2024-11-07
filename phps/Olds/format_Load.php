<?php

header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();

if($_POST['type']=='all'){
	$array = executeSelect("SELECT f.*, t.name AS type_name, 
							(SELECT e.name FROM enterprise e WHERE e.id=f.enterprise1) AS enterprise_name1,
							(SELECT e.name FROM enterprise e WHERE e.id=f.enterprise2) AS enterprise_name2
							FROM format f 
							LEFT JOIN format_type t ON t.id=f.type
							ORDER BY f.id");
	if(count($array)>0){
		for($i=0;$i<count($array);$i++){
			$array[$i]["editar"]='<button class="btn btn-warning" onclick="editRow('.$array[$i]['id'].')" '.$_SESSION["display"]["format"]["update"].'><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>';
			$array[$i]["copiar"]='<button class="btn btn-primary" onclick="copyRowMsg('.$array[$i]['id'].')" '.$_SESSION["display"]["format"]["insert"].'><span class="glyphicon glyphicon-paste" aria-hidden="true"></span></button>';
			$array[$i]["eliminar"]='<button id="edit" class="btn btn-danger" onclick="deleteRow('.$array[$i]['id'].')" '.$_SESSION["display"]["format"]["delete"].'><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';
		}
		echo json_encode($array);
	}else{
		echo 0;
	}	
}elseif($_POST['type']=='one'){
	$array = executeSelect("SELECT * FROM format WHERE id=".$_POST['id']);
	if(count($array)>0){
		echo json_encode($array);
	}else{
		echo 0;
	}

}elseif($_POST['type']=='rows'){
	$array = executeSelect("SELECT * FROM format_row WHERE format_id=".$_POST['id']);
	if(count($array)>0){
		echo json_encode($array);
	}else{
		echo 0;
	}
}elseif($_POST['type']=='allType'){
	$array = executeSelect("SELECT * FROM format WHERE type=".$_POST['typeid']);
	if(count($array)>0){
		echo json_encode($array);
	}else{
		echo 0;
	}
}


?>