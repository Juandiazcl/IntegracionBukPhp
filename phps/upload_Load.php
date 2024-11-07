<?php
include("../connection/connection.php");

if($_POST['type']=='verifySettlement'){
	if(isset($_POST['rut'])){
		$rut = explode('-', $_POST['rut']);
		$rut = str_replace('.', '', $rut[0]);
		$sql = "SELECT p.*,
				fpx.ID AS ID_FINIQUITO
				FROM (PERSONAL p 
				LEFT JOIN FINIQUITO_PERSONAL fpx ON fpx.rut=p.rut_per)
				WHERE p.rut_per=$rut AND fpx.pago_estado='PENDIENTE'";

		$arrayReview = executeSelect($sql);
		if(count($arrayReview)==0){
			$sql = "SELECT p.*,
				IIF(ISNULL(
				(SELECT TOP 1 fp.ID FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per AND pago_estado='PENDIENTE')),0,
				(SELECT TOP 1 fp.ID FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per AND pago_estado='PENDIENTE')) AS ID_FINIQUITO
				FROM (PERSONAL p 
				LEFT JOIN FINIQUITO_PERSONAL fpx ON fpx.rut=p.rut_per)
				WHERE p.rut_per=$rut AND fpx.pago_estado='PENDIENTE'";
		}

	}else{

		$id = $_POST['id'];
		$sql = "SELECT p.*,
				IIF(ISNULL(
				(SELECT TOP 1 fp.ID FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per AND pago_estado='PENDIENTE' AND fp.ID=$id)),0,
				(SELECT TOP 1 fp.ID FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per AND pago_estado='PENDIENTE' AND fp.ID=$id)) AS ID_FINIQUITO
				FROM (PERSONAL p 
				LEFT JOIN FINIQUITO_PERSONAL fpx ON fpx.rut=p.rut_per)
				WHERE fpx.ID=$id";
	}
	//echo $sql;
	$array = executeSelect($sql);

	
	if(count($array)>0){
		for($i=0;$i<count($array);$i++){
			if($array[$i]["ID_FINIQUITO"]!=0){
				$result = glob('../documents/uploads/'.$array[$i]["ID_FINIQUITO"].'.*');

				if($result){
					$array[$i]['fileState'] = 'EXIST';
				}else{
					$resultB = glob('../documents/uploads/_'.$array[$i]["ID_FINIQUITO"].'.*');
					if($resultB){
						$array[$i]['fileState'] = 'PENDING';
					}else{
						$array[$i]['fileState'] = 'NOT EXIST';
					}
				}
			}
		}

		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}

}

?>