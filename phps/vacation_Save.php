<?php
header('Content-Type: text/html; charset=UTF-8'); 

include("../connection/connection.php");

$type=$_POST['type'];


if($type=='save'){
	$Fecha_Inicio=$_POST['Fecha_Inicio'];
	$Fecha_Fin=$_POST['Fecha_Fin'];
	$Fecha_Reintegracion=$_POST['Fecha_Reintegracion'];
	$Dias_Habiles=$_POST['Dias_Habiles'];
	$Dias_Inhabiles=$_POST['Dias_Inhabiles'];
	$Periodo_Inicio=$_POST['Periodo_Inicio'];
	$Periodo_Fin=$_POST['Periodo_Fin'];
	$Dias_Progresivos=$_POST['Dias_Progresivos'];
	if($Dias_Progresivos==''){
		$Dias_Progresivos = 0;
	}
	$Rut=$_POST['Rut'];
	executeSql("INSERT INTO FERIADO_PROPORCIONAL(Fecha_Inicio,Fecha_Fin,Fecha_Reintegracion,Dias_Habiles,Dias_Inhabiles,Periodo_Inicio,Periodo_Fin,Rut,Dias_Progresivos) VALUES('$Fecha_Inicio','$Fecha_Fin','$Fecha_Reintegracion',$Dias_Habiles,$Dias_Inhabiles,$Periodo_Inicio,$Periodo_Fin,$Rut,$Dias_Progresivos)");

}elseif($type=='update'){
	$id=$_POST['id'];
	$Fecha_Inicio=$_POST['Fecha_Inicio'];
	$Fecha_Fin=$_POST['Fecha_Fin'];
	$Fecha_Reintegracion=$_POST['Fecha_Reintegracion'];
	$Dias_Habiles=$_POST['Dias_Habiles'];
	$Dias_Inhabiles=$_POST['Dias_Inhabiles'];
	$Periodo_Inicio=$_POST['Periodo_Inicio'];
	$Periodo_Fin=$_POST['Periodo_Fin'];
	$Dias_Progresivos=$_POST['Dias_Progresivos'];
	if($Dias_Progresivos==''){
		$Dias_Progresivos = 0;
	}
	$Rut=$_POST['Rut'];


echo "UPDATE FERIADO_PROPORCIONAL
				SET Fecha_Inicio='$Fecha_Inicio',
				Fecha_Fin='$Fecha_Fin',
				Fecha_Reintegracion='$Fecha_Reintegracion',
				Dias_Habiles=$Dias_Habiles,
				Dias_Inhabiles=$Dias_Inhabiles,
				Periodo_Inicio=$Periodo_Inicio,
				Periodo_Fin=$Periodo_Fin,
				Dias_Progresivos=$Dias_Progresivos
				WHERE ID=$id";

	executeSql("UPDATE FERIADO_PROPORCIONAL
				SET Fecha_Inicio='$Fecha_Inicio',
				Fecha_Fin='$Fecha_Fin',
				Fecha_Reintegracion='$Fecha_Reintegracion',
				Dias_Habiles=$Dias_Habiles,
				Dias_Inhabiles=$Dias_Inhabiles,
				Periodo_Inicio=$Periodo_Inicio,
				Periodo_Fin=$Periodo_Fin,
				Dias_Progresivos=$Dias_Progresivos
				WHERE ID=$id");

}elseif($type=='delete'){
	$id=$_POST['id'];
	executeSql("DELETE FROM FERIADO_PROPORCIONAL WHERE ID=$id");
	echo 'OK';
}

?>