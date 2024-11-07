<?php
header('Content-Type: text/html; charset=UTF-8'); 

include("../connection/connection.php");

$type=$_POST['type'];
if($type!='delete'){
	$id=$_POST['id'];
	$username=$_POST['username'];
	$name=$_POST['name'];
	$password=$_POST['password'];
	$usertype=$_POST['usertype'];
	$listPlant=$_POST['listPlant'];
	//$password= md5($password);
}

if($type=='save'){
	$count = executeSelect("SELECT COUNT(*) AS count FROM T0002 WHERE Usr_Codigo='$username'");
	if($count[0]["count"]==0){
		executeSql("INSERT INTO T0002(Usr_Codigo, UsrNombre, UsrPassw, Perfil) VALUES('$username', '$name', '$password', '$usertype')");
		saveModules($username);
		savePlants($username,$listPlant);
		echo 'OK';
	}else{
		echo 'ERROR';
	}
}elseif($type=='update'){
	$id=$_POST['id'];
	$savePassword = '';
	if($password!=''){
		$savePassword = "UsrPassw='$password',";
	}

	executeSql("UPDATE T0002 SET Usr_Codigo='$username', UsrNombre='$name', $savePassword Perfil='$usertype' WHERE Usr_Codigo='$id'");
	saveModules($username);
	savePlants($username,$listPlant);
	echo 'OK';

}elseif($type=='delete'){
	$id=$_POST['id'];
	executeSql("DELETE FROM T0002 WHERE Usr_Codigo='$id'");
}
//echo json_encode($array);


function saveModules($id){
	$modules=$_POST['modules'];
	$itemsArray = explode("&&&&",$modules);

	$sql = "DELETE FROM T0002_MODULOS WHERE Usr_Codigo='$id'";

	executeSql($sql);

	for($i=0;$i<count($itemsArray)-1;$i++){//1er y último arreglo son vacíos
		$itemArray = explode("&&",$itemsArray[$i]);
		
		$sql = "INSERT INTO T0002_MODULOS(Usr_Codigo, Modulo, Insertar, Modificar, Eliminar, Ver) VALUES('$id', '".$itemArray[0]."', ".$itemArray[1].", ".$itemArray[2].", ".$itemArray[3].", ".$itemArray[4].")";
		executeSql($sql);

	}
}

function savePlants($userId,$listPlant){
	$listPlant = explode("-", $listPlant);
	$sql = "DELETE FROM T0002_CAMPOS WHERE Usr_Codigo='$userId'";
	executeSql($sql);
	
	for($i=0;$i<count($listPlant)-1;$i++){
		$sql = "INSERT INTO T0002_CAMPOS(Usr_Codigo,Pl_codigo) VALUES('".$userId."','".$listPlant[$i]."')";
		executeSql($sql);
	}
}
?>