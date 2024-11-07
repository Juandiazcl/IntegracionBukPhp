<?php
header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

$id = $_POST['id'];

$array = executeSelect("SELECT cp.*, p.id AS personalId, p.rut, p.name, p.lastname1, p.lastname2, cpr.state, cpr.date_end
						FROM contract_personal cp
						LEFT JOIN contract_process cpr ON cpr.contract_personal_id=cp.id
						LEFT JOIN personal p ON p.id=cp.personal_id
						WHERE contract_id=$id ORDER BY id");


if(count($array)>0){
	echo json_encode($array);
}else{
	echo 0;
}

?>