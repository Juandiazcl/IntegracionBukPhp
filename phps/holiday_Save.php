<?php
header('Content-Type: text/html; charset=UTF-8'); 

include("../connection/connection.php");

$type=$_POST['type'];
$date=$_POST['date'];
$cellID=$_POST['cellID'];

if($type=='save'){
	$dateArray = explode("/", $date);
	$retardDate = $dateArray[1]."/".$dateArray[0]."/".$dateArray[2];

	$holiday = executeSelect("SELECT ID FROM DIAS_FESTIVOS WHERE Fecha=#$retardDate#");

	if(count($holiday)==0){
		executeSql("INSERT INTO DIAS_FESTIVOS(Fecha,CeldaID) VALUES('$date','$cellID')");
		echo 'save';
	}else{
		executeSql("DELETE FROM DIAS_FESTIVOS WHERE Fecha=#$retardDate#");
		echo 'delete';
	}
}

?>