<?php
header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();

$id = $_POST['id'];
$type = $_POST['type'];

if($type=='list'){
	$array = executeSelect("SELECT p.rut, p.name, p.lastname1, p.lastname2, CONCAT(p.name+' '+p.lastname1+' '+p.lastname2), cpr.date_start, cpr.date_end, cpr.id, cpr.state, c.end_type, c.work1, c.work2
							FROM personal p
							LEFT JOIN contract_personal cp ON cp.personal_id=p.id
							LEFT JOIN contract_process cpr ON cpr.contract_personal_id=cp.id
							LEFT JOIN contract c ON c.id=cp.contract_id
							WHERE p.id=$id ORDER BY cpr.id DESC");
	if(count($array)>0){
		for($i=0;$i<count($array);$i++){
			$array[$i]["editar"]='<button class="btn btn-warning" onclick="editRow('.$array[$i]['id'].')" '.$_SESSION["display"]["contractDetail"]["update"].'><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>';
			$array[$i]["eliminar"]='<button id="edit" class="btn btn-danger" onclick="deleteRow('.$array[$i]['id'].')" '.$_SESSION["display"]["contractDetail"]["delete"].'><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';
		}
		echo json_encode($array);
	}else{
		echo 0;
	}	

}elseif($type=='process'){
	$array = executeSelect("SELECT p.rut, p.name, p.lastname1, p.lastname2, cpr.date_start, cpr.date_end, cpr.id, cpr.state
							FROM personal p
							LEFT JOIN contract_personal cp ON cp.personal_id=p.id
							LEFT JOIN contract_process cpr ON cpr.contract_personal_id=cp.id
							WHERE cpr.id=$id");
	if(count($array)>0){
		for($i=0;$i<count($array);$i++){
			$array[$i]["editar"]='<button class="btn btn-warning" onclick="editRow('.$array[$i]['id'].')"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>';
			$array[$i]["eliminar"]='<button id="edit" class="btn btn-danger" onclick="deleteRow('.$array[$i]['id'].')"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';
		}
		echo json_encode($array);
	}else{
		echo 0;
	}	

}elseif($type=='document'){
	$array = executeSelect("SELECT p.rut, p.name, p.lastname1, p.lastname2, cpr.date_start, cpr.date_end, cpr.id, cpr.state,
							c.enterprise1_id, c.enterprise2_id
							FROM personal p
							LEFT JOIN contract_personal cp ON cp.personal_id=p.id
							LEFT JOIN contract_process cpr ON cpr.contract_personal_id=cp.id
							LEFT JOIN contract c ON c.id=cp.contract_id
							WHERE cpr.id=$id");

	if(count($array)>0){
		echo json_encode($array);
	}else{
		echo 0;
	}	
$array[$i]["eliminar"]='<button id="edit" class="btn btn-danger" onclick="deleteRow('.$array[$i]['id'].')" '.$_SESSION["display"]["contractDetail"]["delete"].'><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';
}elseif($type=='document_list'){
	$array = executeSelect("SELECT id, title, 'contract' AS tipo, 'Contrato' AS tipoNombre FROM contract_doc WHERE contract_process_id=$id
							UNION
							SELECT id, title, 'attachment' AS tipo, 'Anexo' AS tipoNombre FROM attachment_doc WHERE contract_process_id=$id
							UNION
							SELECT a.id, a.title, 'advice' AS tipo, 'Aviso' AS tipoNombre
							FROM advice_doc a
							LEFT JOIN termination_doc t ON t.termination_personal_id=a.termination_personal_id
							WHERE t.termination_process_id=$id
							UNION
							SELECT id, title, 'termination' AS tipo, 'Finiquito' AS tipoNombre FROM termination_doc WHERE termination_process_id=$id");

	if(count($array)>0){
		for($i=0;$i<count($array);$i++){
			if($array[$i]["tipo"]=='contract'){
				$color = 'primary';
				$link = "window.open('format_pdf.php?id=".$id."&type=process&paper=";
		
			}elseif($array[$i]["tipo"]=='attachment'){
				$color = 'success';
				$link = "window.open('format_pdf.php?id=".$id."&type=attachment&by=process&idAttachment=".$array[$i]['id']."&paper=";
		
			}elseif($array[$i]["tipo"]=='advice'){
				$color = 'warning';
				$link = "window.open('format_pdf.php?id=".$id."&type=advice&by=process&paper=";
		
			}elseif($array[$i]["tipo"]=='termination'){
				$color = 'danger';
				$link = "window.open('format_pdf.php?id=".$id."&type=termination&by=process&paper=";
			}

			$array[$i]["folio"]='<button class="btn btn-'.$color.'" onclick="'.$link.'folio\');"><i class="fa fa-file-text-o fa-fw"></i></span></button>';
			$array[$i]["letter"]='<button class="btn btn-'.$color.'" onclick="'.$link.'letter\');"><i class="fa fa-file-text-o fa-fw"></i></span></button>';
			$array[$i]["a4"]='<button class="btn btn-'.$color.'" onclick="'.$link.'a4\');"><i class="fa fa-file-text-o fa-fw"></i></span></button>';
		}
		echo json_encode($array);
	}else{
		echo 0;
	}	
}

/*if(count($array)>0){
	echo json_encode($array);
}else{
	echo 0;
}*/

?>