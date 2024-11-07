<?php
header('Content-Type: text/html; charset=UTF-8'); 

include("../connection/connection.php");

$type=$_POST['type'];
if($type!='delete'){
	$id=$_POST['id'];
	$name=$_POST['name'];
	$number=$_POST['number'];
}

if($type=='save'){
	$count = executeSelect("SELECT COUNT(*) AS count FROM region WHERE name='$name' AND number='$number'");
	if($count[0]["count"]==0){
		executeSql("INSERT INTO region(name, number) VALUES('$name', '$number')");
		echo 'OK';
	}else{
		echo 'ERROR';
	}
}elseif($type=='update'){
	$id=$_POST['id'];
	$count = executeSelect("SELECT COUNT(*) AS count FROM region WHERE name='$name' AND number='$number' AND NOT id=$id");
	if($count[0]["count"]==0){
		executeSql("UPDATE region SET name='$name', number='$number' WHERE id=$id");
		echo 'OK';
	}else{
		echo 'ERROR';
	}
}elseif($type=='delete'){
	$id=$_POST['id'];

	$count = executeSelect("SELECT COUNT(*) AS count FROM commune WHERE  region_id=$id");
	if($count[0]["count"]==0){
		executeSql("DELETE FROM region WHERE id=$id");
		echo 'OK';
	}else{
		echo 'ERROR';
	}
}
//echo json_encode($array);

?>