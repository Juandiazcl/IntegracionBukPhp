<?php
header('Content-Type: text/html; charset=UTF-8'); 

include("../connection/connection.php");

$type=$_POST['type'];
if($type!='delete'){
	$id=$_POST['id'];
	$name=$_POST['name'];
	$region_id=$_POST['region'];
}

if($type=='save'){
	$count = executeSelect("SELECT COUNT(*) AS count FROM commune WHERE name='$name' AND region_id=$region_id");
	if($count[0]["count"]==0){
		executeSql("INSERT INTO commune(name, region_id) VALUES('$name', $region_id)");
		echo 'OK';
	}else{
		echo 'ERROR';
	}
}elseif($type=='update'){
	$id=$_POST['id'];
	$count = executeSelect("SELECT COUNT(*) AS count FROM commune WHERE name='$name' AND region_id=$region_id AND NOT id=$id");
	if($count[0]["count"]==0){
		executeSql("UPDATE commune SET name='$name', region_id=$region_id WHERE id=$id");
		echo 'OK';
	}else{
		echo 'ERROR';
	}
}elseif($type=='delete'){	
	$id=$_POST['id'];
	$count = executeSelect("SELECT COUNT(*) AS count FROM sector WHERE commune_id=$id");
	if($count[0]["count"]==0){
		executeSql("DELETE FROM commune WHERE id=$id");
		echo 'OK';
	}else{
		echo 'ERROR';
	}
}
//echo json_encode($array);

?>