<?php
header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

if($_POST['type']=='all'){
	$array = executeSelect("SELECT * FROM format_type ORDER BY id");

}elseif($_POST['type']=='one'){
	$array = executeSelect("SELECT * FROM format_type WHERE id=".$_POST['id']);
}
if(count($array)>0){
	echo json_encode($array);
}else{
	echo 0;
}
?>