<?php
header('Content-Type: text/html; charset=UTF-8'); 

include("../connection/connection.php");

$id=$_POST['id'];
$password=$_POST['password'];
//$password= md5($password);
$newPassword=$_POST['newPassword'];
//$newPassword= md5($newPassword);


$array = executeSelect("SELECT * FROM T0002 WHERE Usr_Codigo='$id' AND UsrPassw='$password'");

if(isset($array)){
	executeSql("UPDATE T0002 SET UsrPassw='$newPassword' WHERE Usr_Codigo='$id'");
	echo 'OK';
}else{
	echo 'ERROR';
}

?>