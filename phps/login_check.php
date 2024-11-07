<?php
session_start();
$user=$_POST['user'];
$password=$_POST['password'];
//$password=md5($password);

include("../connection/connection.php");


$array = executeSelect("SELECT * FROM T0002 WHERE Usr_Codigo='$user' AND UsrPassw='$password'");

if(isset($array)){
	$_SESSION["userId"] = $array[0]['Usr_Codigo'];
	$_SESSION["userName"] = $_POST['user'];
	$_SESSION["profile"] = $array[0]['Perfil'];

	$arrayModulesOriginal = executeSelect("SELECT * FROM T0002_MODULOS_ORIGINAL");
	for($i=0;$i<count($arrayModulesOriginal);$i++){
		$_SESSION['display'][$arrayModulesOriginal[$i]['Modulo']]['insert'] = 'style="display: none;"';
		$_SESSION['display'][$arrayModulesOriginal[$i]['Modulo']]['update'] = 'style="display: none;"';
		$_SESSION['display'][$arrayModulesOriginal[$i]['Modulo']]['delete'] = 'style="display: none;"';
		$_SESSION['display'][$arrayModulesOriginal[$i]['Modulo']]['view'] = 'style="display: none;"';
	}


	$arrayModules = executeSelect("SELECT * FROM T0002_MODULOS WHERE Usr_Codigo='".$_SESSION['userId']."'");

	
	for($i=0;$i<count($arrayModules);$i++){
		if($arrayModules[$i]['Insertar']==0){
			$_SESSION['display'][$arrayModules[$i]['Modulo']]['insert'] = 'style="display: none;"';
		}else{
			$_SESSION['display'][$arrayModules[$i]['Modulo']]['insert'] = '';
		}

		if($arrayModules[$i]['Modificar']==0){
			$_SESSION['display'][$arrayModules[$i]['Modulo']]['update'] = 'style="display: none;"';
		}else{
			$_SESSION['display'][$arrayModules[$i]['Modulo']]['update'] = '';
		}

		if($arrayModules[$i]['Eliminar']==0){
			$_SESSION['display'][$arrayModules[$i]['Modulo']]['delete'] = 'style="display: none;"';
		}else{
			$_SESSION['display'][$arrayModules[$i]['Modulo']]['delete'] = '';
		}

		if($arrayModules[$i]['Ver']==0){
			$_SESSION['display'][$arrayModules[$i]['Modulo']]['view'] = 'style="display: none;"';
		}else{
			$_SESSION['display'][$arrayModules[$i]['Modulo']]['view'] = '';
		}
	}

	echo 1;
}else{
	echo 0;
}



?>