<?php
header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

$id = $_POST['id'];

$array = executeSelect("SELECT * FROM usertype_modules WHERE usertypeId=$id ORDER BY id");

if(count($array)>0){
	echo json_encode($array);
}else{
	echo 0;
}
?>