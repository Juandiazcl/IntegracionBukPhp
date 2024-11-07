<?php
header('Content-Type: text/html; charset=UTF-8'); 

include("../connection/connection.php");

$type=$_POST['type'];
if($type!='delete'){
	$id=$_POST['id'];
	$value=$_POST['value'];
	$format_type_id=$_POST['format_type_id'];
}

if($type=='save'){
	$count = executeSelect("SELECT COUNT(*) AS count FROM format_value WHERE value='$value' AND format_type_id=$format_type_id");
	if($count[0]["count"]==0){
		executeSql("INSERT INTO format_value(value, format_type_id) VALUES('$value', $format_type_id)");
		echo 'OK';
	}else{
		echo 'ERROR';
	}
}elseif($type=='update'){
	$id=$_POST['id'];
	$count = executeSelect("SELECT COUNT(*) AS count FROM format_value WHERE value='$value' AND format_type_id=$format_type_id AND NOT id=$id");
	if($count[0]["count"]==0){
		executeSql("UPDATE format_value SET value='$value', format_type_id=$format_type_id WHERE id=$id");
		echo 'OK';
	}else{
		echo 'ERROR';
	}
}elseif($type=='delete'){
	$id=$_POST['id'];
	executeSql("DELETE FROM format_value WHERE id=$id");
}
//echo json_encode($array);

?>