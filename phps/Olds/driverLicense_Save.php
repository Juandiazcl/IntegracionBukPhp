<?php
header('Content-Type: text/html; charset=UTF-8'); 

include("../connection/connection.php");

$type=$_POST['type'];
if($type!='delete'){
	$id=$_POST['id'];
	$name=$_POST['name'];
}

if($type=='save'){
	$count = executeSelect("SELECT COUNT(*) AS count FROM driver_license WHERE name='$name'");
	if($count[0]["count"]==0){
		executeSql("INSERT INTO driver_license(name) VALUES('$name')");
		echo 'OK';
	}else{
		echo 'ERROR';
	}
}elseif($type=='update'){
	$id=$_POST['id'];
	$count = executeSelect("SELECT COUNT(*) AS count FROM driver_license WHERE name='$name' AND NOT id=$id");
	if($count[0]["count"]==0){
		executeSql("UPDATE driver_license SET name='$name' WHERE id=$id");
		echo 'OK';
	}else{
		echo 'ERROR';
	}
}elseif($type=='delete'){
	$id=$_POST['id'];
	$count = executeSelect("SELECT COUNT(*) AS count FROM personal WHERE driver_license_id=$id");
	if($count[0]["count"]==0){
		executeSql("DELETE FROM driver_license WHERE id=$id");
		echo 'OK';
	}else{
		echo 'ERROR';
	}
}
//echo json_encode($array);

?>