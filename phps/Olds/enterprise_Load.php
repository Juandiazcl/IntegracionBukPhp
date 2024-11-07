<?php
header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();

if($_POST['type']=='all'){
	$filter = $_POST['filter'];

	$filterString = " WHERE e.type=$filter";

	$array = executeSelect("SELECT e.*, t.name AS enterprise_type
							FROM enterprise e
							LEFT JOIN enterprise_type t ON t.id=e.type
							$filterString
							ORDER BY e.id");
	if(count($array)>0){
		for($i=0;$i<count($array);$i++){
			$array[$i]["editar"]='<button class="btn btn-warning" onclick="editRow('.$array[$i]['id'].')" '.$_SESSION["display"]["enterprise"]["update"].'><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>';
			$array[$i]["eliminar"]='<button id="edit" class="btn btn-danger" onclick="deleteRow('.$array[$i]['id'].')" '.$_SESSION["display"]["enterprise"]["delete"].'><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';
		}
		echo json_encode($array);
	}else{
		echo 0;
	}	
}elseif($_POST['type']=='one'){
	$array = executeSelect("SELECT e.*, c.region_id, cr.region_id AS represent_region_id
							FROM enterprise e 
							LEFT JOIN commune c ON c.id=e.commune_id
							LEFT JOIN commune cr ON cr.id=e.legal_represent_commune_id
							WHERE e.id=".$_POST['id']);
	if(count($array)>0){
		echo json_encode($array);
	}else{
		echo 0;
	}	
}elseif($_POST['type']=='verify'){
	$filter = $_POST['filter'];

	$filterString = " AND type=$filter";

	$array = executeSelect("SELECT * FROM enterprise WHERE rut='".$_POST['rut']."' AND NOT id=".$_POST['id'].$filterString);
	if(count($array)>0){
		echo json_encode($array);
	}else{
		echo 0;
	}	
}elseif($_POST['type']=='filter'){
	$enterprise_type=1;
	if($_POST['enterprise_type']=="CLIENTE"){
		$enterprise_type=2;
	}
	$array = executeSelect("SELECT * FROM enterprise WHERE type=$enterprise_type");
	if(count($array)>0){
		echo json_encode($array);
	}else{
		echo 0;
	}		
}

?>