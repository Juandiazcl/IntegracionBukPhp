<?php
//header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();

if($_POST['type']=='all'){
	$array = executeSelect("SELECT u.UsrNombre AS name, u.Usr_Codigo AS id, ut.PerfNom AS usertype_Name
							FROM T0002 u 
							LEFT JOIN T0097 ut ON ut.Perfil=u.Perfil
							ORDER BY u.Usr_Codigo");
	if(count($array)>0){
		for($i=0;$i<count($array);$i++){
			$array[$i]["editar"]='<button class="btn btn-warning btn-sm" onclick="editRow(\''.$array[$i]['id'].'\')"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>';
			$array[$i]["eliminar"]='<button class="btn btn-danger btn-sm" onclick="deleteRow(\''.$array[$i]['id'].'\')"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';
		}
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}
}elseif($_POST['type']=='one'){
	$array = executeSelect("SELECT UsrNombre AS name, Usr_Codigo AS id, Perfil AS usertype 
							FROM T0002 WHERE Usr_Codigo='".$_POST['id']."'");
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}

}elseif($_POST['type']=='modules'){
	$id = $_POST['id'];

	$array = executeSelect("SELECT * FROM T0002_MODULOS WHERE Usr_Codigo='$id' ORDER BY ID");

	if(count($array)>0){
		echo json_encode($array);
	}else{
		echo 0;
	}
}elseif($_POST['type']=='plants'){
	$sql = "SELECT * FROM T0002_CAMPOS WHERE Usr_codigo='".$_POST['Usr_codigo']."'";
	$array = executeSelect($sql);
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}
}

?>