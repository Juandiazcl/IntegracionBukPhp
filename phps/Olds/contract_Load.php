<?php
header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();

if($_POST['type']=='all'){
	$array = executeSelect("SELECT c.*,
							(CASE WHEN end_type='FAENA' THEN CONCAT(work1,' - ',work2) ELSE
							(CASE WHEN end_type='FIJO' THEN date_end
							ELSE 'INDEFINIDO' END) END) AS end_date,
							(SELECT name FROM enterprise e WHERE e.id=c.enterprise1_id) AS RazonSocial,
							(SELECT name FROM enterprise e WHERE e.id=c.enterprise2_id) AS Cliente
							FROM contract c
							ORDER BY c.id");
	if(count($array)>0){
		for($i=0;$i<count($array);$i++){
			$array[$i]["editar"]='<button class="btn btn-warning" onclick="editRow('.$array[$i]['id'].')" '.$_SESSION["display"]["contract"]["update"].'><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>';
			$array[$i]["eliminar"]='<button id="edit" class="btn btn-danger" onclick="deleteRow('.$array[$i]['id'].')" '.$_SESSION["display"]["contract"]["delete"].'><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';
		}
		echo json_encode($array);
	}else{
		echo 0;
	}	

}elseif($_POST['type']=='one'){
	$array = executeSelect("SELECT * FROM contract WHERE id=".$_POST['id']);
	$arraySchedule = executeSelect("SELECT * FROM contract_schedule WHERE contract_id=".$_POST['id']);
	$schedule="";
	for($i=0;$i<count($arraySchedule);$i++){
		$schedule .= $arraySchedule[$i]["text"]."&&";
	}
	$array[0]["scheduleData"] = $schedule;
	
	if(count($array)>0){
		echo json_encode($array);
	}else{
		echo 0;
	}

}elseif($_POST['type']=='contract_pdf'){
	$array = executeSelect("SELECT cpr.*
							FROM contract_process cpr
							LEFT JOIN contract_personal cp ON cp.id=cpr.contract_personal_id
							WHERE cp.contract_id=".$_POST['id']);

	if(count($array)>0){
		echo json_encode($array);
	}else{
		echo 0;
	}

}elseif($_POST['type']=='attachment_pdf'){
	$array = executeSelect("SELECT cpr.id
						FROM personal p
						LEFT JOIN contract_personal cp ON cp.personal_id=p.id
						LEFT JOIN contract_process cpr ON cpr.contract_personal_id=cp.id
						LEFT JOIN contract c ON c.id=cp.contract_id
						LEFT JOIN commune cm ON cm.id=p.commune_id
						LEFT JOIN afp a ON a.id=p.afp_id
						LEFT JOIN attachment_doc ad ON ad.contract_process_id=cpr.id
						WHERE c.id=".$_POST['id']." AND ad.attachment_id=".$_POST['idAttachment']." ORDER BY p.id DESC");

	if(count($array)>0){
		echo json_encode($array);
	}else{
		echo 0;
	}

}elseif($_POST['type']=='termination_pdf'){

	$array = executeSelect("SELECT cpr.id
							FROM personal p
							LEFT JOIN termination_personal cp ON cp.personal_id=p.id
							LEFT JOIN contract_process cpr ON cpr.termination_personal_id=cp.id
							LEFT JOIN termination c ON c.id=cp.termination_id
							LEFT JOIN commune cm ON cm.id=p.commune_id
							LEFT JOIN afp a ON a.id=p.afp_id
							WHERE c.id=".$_POST['id']." ORDER BY p.id DESC");

	if(count($array)>0){
		echo json_encode($array);
	}else{
		echo 0;
	}
}
?>