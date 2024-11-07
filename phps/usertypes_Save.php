<?php
header('Content-Type: text/html; charset=UTF-8'); 

include("../connection/connection.php");

$type=$_POST['type'];
$Perfil=$_POST['id'];
$PerfNom=$_POST['name'];

if($type=='save'){
	$count = executeSelect("SELECT COUNT(*) AS count FROM T0097 WHERE Perfil='$Perfil'");
	if($count[0]["count"]==0){
		executeSql("INSERT INTO T0097(Perfil, PerfNom) VALUES('$PerfNom')");
		echo 'OK';
	}else{
		echo 'ERROR';
	}
}elseif($type=='update'){
	$Perfil=$_POST['Perfil'];
	$count = executeSelect("SELECT COUNT(*) AS count FROM T0097 WHERE PerfNom='$PerfNom'");
	if($count[0]["count"]==0){
		executeSql("UPDATE T0097 SET PerfNom='$PerfNom' WHERE Perfil='$Perfil'");
		echo 'OK';
	}else{
		echo 'ERROR';
	}

}elseif($type=='delete'){
	$Perfil=$_POST['Perfil'];
	executeSql("DELETE FROM T0097 WHERE Perfil='$Perfil'");
}


?>