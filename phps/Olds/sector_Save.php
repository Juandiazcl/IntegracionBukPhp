<?php
header('Content-Type: text/html; charset=UTF-8'); 

include("../connection/connection.php");

$type=$_POST['type'];
if($type!='delete'){
	$id=$_POST['id'];
	$name=$_POST['name'];
	$commune_id=$_POST['commune'];
}

if($type=='save'){
	$count = executeSelect("SELECT COUNT(*) AS count FROM sector WHERE name='$name' AND commune_id=$commune_id");
	if($count[0]["count"]==0){
		executeSql("INSERT INTO sector(name, commune_id) VALUES('$name', $commune_id)");
		echo 'OK';
	}else{
		echo 'ERROR';
	}
}elseif($type=='update'){
	$id=$_POST['id'];
	$count = executeSelect("SELECT COUNT(*) AS count FROM sector WHERE name='$name' AND commune_id=$commune_id AND NOT id=$id");
	if($count[0]["count"]==0){
		executeSql("UPDATE sector SET name='$name', commune_id=$commune_id WHERE id=$id");
		echo 'OK';
	}else{
		echo 'ERROR';
	}
}elseif($type=='delete'){
	$id=$_POST['id'];
	$count = executeSelect("SELECT COUNT(*) AS count FROM personal WHERE sector_id=$id");
	if($count[0]["count"]==0){
		executeSql("DELETE FROM sector WHERE id=$id");
		echo 'OK';
	}else{
		echo 'ERROR';
	}
}
//echo json_encode($array);

?>