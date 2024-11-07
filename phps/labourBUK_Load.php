<?php
//header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();

if($_POST['type']=='labourBUK'){
    $plant=$_POST['plant'];
	//  echo "campo: ";
	//  echo $plant;
	$filter='';
	if ($plant=='02' || $plant=='06'|| $plant=='07'){
		$filter="OR tr.idCampo=55";
	}

	$sql="SELECT Lb.id, Lb.descriptionL, Lg.nombre as nomLug, tr.id_tarifa_buk, Pr.nombre as nomPro, Un.nombre as nomUni, tr.valor_tarifa as tarif  FROM ((((LaboresBuk Lb
	left join tarifasBuk as tr on tr.codigo_labor=Lb.id)
    left join LugaresBuk as Lg on Lg.id_lugar=tr.codigo_lugar)
	left join ProductosBuk as Pr on Pr.id_product=tr.codigo_producto)
	left join UnidadesBuk as Un on Un.id_unidad=tr.codigo_unidad)
	WHERE tr.idCampo=val($plant) or tr.idCampo=99 ".$filter."
	ORDER BY Lb.descriptionL, Lg.nombre";
	//echo $sql;
	//$array = executeSelect("SELECT id, descriptionL FROM LaboresBuk ORDER BY descriptionL");
	$array = executeSelect($sql);

	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}

}
?>