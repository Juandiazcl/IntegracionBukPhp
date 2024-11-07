<?php
header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();

if($_POST['type']=='all'){
	$array = executeSelect("SELECT c.id, c.name, r.name AS region_name, r.number
							FROM commune c 
							LEFT JOIN region r ON r.id=c.region_id
							ORDER BY c.id");
	if(count($array)>0){
		for($i=0;$i<count($array);$i++){
			$array[$i]["editar"]='<button class="btn btn-warning" onclick="editRow('.$array[$i]['id'].')" '.$_SESSION["display"]["commune"]["update"].'><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>';
			$array[$i]["eliminar"]='<button id="edit" class="btn btn-danger" onclick="deleteRow('.$array[$i]['id'].')" '.$_SESSION["display"]["commune"]["delete"].'><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';
		}
		echo json_encode($array);
	}else{
		echo 0;
	}	

}elseif($_POST['type']=='one'){
	$array = executeSelect("SELECT * FROM commune WHERE id=".$_POST['id']);
	if(count($array)>0){
		echo json_encode($array);
	}else{
		echo 0;
	}	
}elseif($_POST['type']=='list'){
	$array = executeSelect("SELECT * FROM commune WHERE region_id=".$_POST['region_id'].' ORDER BY name');
	if(count($array)>0){
		echo json_encode($array);
	}else{
		echo 0;
	}		
}


?>