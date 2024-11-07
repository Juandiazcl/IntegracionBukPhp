<?php

header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

if($_POST['type']=='process'){
	$array = executeSelect("SELECT * FROM attachment_doc WHERE contract_process_id=".$_POST['id']);
	if(count($array)>0){
		echo json_encode($array);
	}else{
		echo 0;
	}

}elseif($_POST['type']=='contract'){
	$array = executeSelect("SELECT f.*, a.id AS attachment_id
							FROM format f
							LEFT JOIN attachment a ON a.format_id=f.id 
							WHERE a.contract_id=".$_POST['id']);
	if(count($array)>0){
		echo json_encode($array);
	}else{
		echo 0;
	}

}elseif($_POST['type']=='view'){
	$array = executeSelect("SELECT * FROM attachment_doc WHERE attachment_id=".$_POST['id']);
	if(count($array)>0){
		echo json_encode($array);
	}else{
		echo 0;
	}

}elseif($_POST['type']=='view_extension'){
	$array = executeSelect("SELECT cpr.id AS id,
							(SELECT COUNT(a.id)
							FROM attachment_doc a
							WHERE a.contract_process_id=cpr.id AND attachment_type='EXTENSION') AS Contador
							FROM contract_process cpr
							LEFT JOIN contract_personal cp ON cp.id=cpr.contract_personal_id
							WHERE cp.contract_id=".$_POST['id']);
	if(count($array)>0){
		echo json_encode($array);
	}else{
		echo 0;
	}

}


?>