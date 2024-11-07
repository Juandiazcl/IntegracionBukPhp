<?php
//header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();

if($_POST['type']=='all'){
	$sql = "SELECT * FROM T0010 ORDER BY PlNombre";
	if($_SESSION['profile']!='ADM'){
		/*$sql = "SELECT t.* 
				FROM (T0010 t
				LEFT JOIN CCOS3 c ON VAL(c.cc1)=t.Pl_codigo)
				WHERE c.usr1='".$_SESSION['userId']."'
				OR c.usr2='".$_SESSION['userId']."'
				OR c.usr3='".$_SESSION['userId']."'
				ORDER BY PlNombre";*/
		$sql = "SELECT t.* 
				FROM (T0010 t
				LEFT JOIN T0002_CAMPOS c ON c.Pl_codigo=t.Pl_codigo)
				WHERE c.Usr_codigo='".$_SESSION['userId']."'
				ORDER BY PlNombre";
	}
	$array = executeSelect($sql);
	if(count($array)>0){
		/*for($i=0;$i<count($array);$i++){
			$array[$i]["editar"]='<button class="btn btn-warning" onclick="editRow(\''.$array[$i]['id'].'\')"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>';
			$array[$i]["eliminar"]='<button class="btn btn-danger" onclick="deleteRow(\''.$array[$i]['id'].'\')"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';
		}*/
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}

}else if($_POST['type']=='allb'){
	$sql = "SELECT * FROM T0010b";
	if($_SESSION['profile']!='ADM'){
		/*$sql = "SELECT t.* 
				FROM (T0010 t
				LEFT JOIN CCOS3 c ON VAL(c.cc1)=t.Pl_codigo)
				WHERE c.usr1='".$_SESSION['userId']."'
				OR c.usr2='".$_SESSION['userId']."'
				OR c.usr3='".$_SESSION['userId']."'
				ORDER BY PlNombre";*/
		$sql = "SELECT t.* 
				FROM (T0010b t
				LEFT JOIN T0002_CAMPOS c ON c.Pl_codigo=t.Pl_codigo)
				WHERE c.Usr_codigo='".$_SESSION['userId']."'
				ORDER BY PlNombre";
	}
	$array = executeSelect($sql);
	if(count($array)>0){
		/*for($i=0;$i<count($array);$i++){
			$array[$i]["editar"]='<button class="btn btn-warning" onclick="editRow(\''.$array[$i]['id'].'\')"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>';
			$array[$i]["eliminar"]='<button class="btn btn-danger" onclick="deleteRow(\''.$array[$i]['id'].'\')"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';
		}*/
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}

}elseif($_POST['type']=='one'){
	$array = executeSelect("SELECT * FROM T0010 WHERE Pl_codigo=".$_POST['Pl_codigo']);
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}

}elseif($_POST['type']=='allUser'){
	$sql = "SELECT * FROM T0010 WHERE NOT Pl_codigo IN (0,98) ORDER BY PlNombre";
	$array = executeSelect($sql);
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}
}

?>