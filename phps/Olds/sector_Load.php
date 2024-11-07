<?php
header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();

if($_POST['type']=='all'){
	$array = executeSelect("SELECT s.id, s.name, r.name AS region_name, r.number, c.name AS commune_name	
							FROM sector s 
							LEFT JOIN commune c ON c.id=s.commune_id
							LEFT JOIN region r ON r.id=c.region_id
							ORDER BY s.id");
	if(count($array)>0){
		for($i=0;$i<count($array);$i++){
			$array[$i]["editar"]='<button class="btn btn-warning" onclick="editRow('.$array[$i]['id'].')" '.$_SESSION["display"]["sector"]["update"].'><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>';
			$array[$i]["eliminar"]='<button id="edit" class="btn btn-danger" onclick="deleteRow('.$array[$i]['id'].')" '.$_SESSION["display"]["sector"]["delete"].'><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';
		}
		echo json_encode($array);
	}else{
		echo 0;
	}

}elseif($_POST['type']=='one'){
	$array = executeSelect("SELECT s.*, r.id AS region_id
							FROM sector s
							LEFT JOIN commune c ON c.id=s.commune_id
							LEFT JOIN region r ON r.id=c.region_id
							WHERE s.id=".$_POST['id']);
	if(count($array)>0){
		echo json_encode($array);
	}else{
		echo 0;
	}
}elseif($_POST['type']=='list'){
	$array = executeSelect("SELECT * FROM sector WHERE commune_id=".$_POST['commune_id']);
	if(count($array)>0){
		echo json_encode($array);
	}else{
		echo 0;
	}
}


?>