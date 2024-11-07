<?php
header('Content-Type: text/html; charset=UTF-8'); 

include("../connection/connection.php");

$type=$_POST['type'];
if($type!='delete'){
	$id=$_POST['id'];
	$rut=$_POST['rut'];
	$name=$_POST['name'];
	$enterprise_type=$_POST['enterprise_type'];
	$address=$_POST['address'];
	$commune_id=$_POST['commune_id'];
	$phone1=$_POST['phone1'];
	$phone2=$_POST['phone2'];
	$city=$_POST['city'];
	$legal_represent_rut=$_POST['legal_represent_rut'];
	$legal_represent_name=$_POST['legal_represent_name'];

	if($enterprise_type==1){
		$legal_represent_commune_id=$_POST['legal_represent_commune_id'];
		$legal_represent_address=$_POST['legal_represent_address'];
		$legal_represent_city=$_POST['legal_represent_city'];
	}
}

if($type=='save'){
	$filter=$_POST['filter'];

	$count = executeSelect("SELECT COUNT(*) AS count FROM enterprise WHERE rut='$rut' AND type=$filter");
	if($count[0]["count"]==0){

	if($enterprise_type==2){
		executeSql("INSERT INTO enterprise(rut,name,type,address,commune_id,phone1,phone2,city,legal_represent_rut,legal_represent_name) VALUES('$rut','$name',$enterprise_type,'$address',$commune_id,'$phone1','$phone2','$city','$legal_represent_rut','$legal_represent_name')");
	}else{
		executeSql("INSERT INTO enterprise(rut,name,type,address,commune_id,phone1,phone2,city,legal_represent_rut,legal_represent_name,legal_represent_commune_id,legal_represent_address,legal_represent_city) VALUES('$rut','$name',$enterprise_type,'$address',$commune_id,'$phone1','$phone2','$city','$legal_represent_rut','$legal_represent_name',$legal_represent_commune_id,'$legal_represent_address','$legal_represent_city')");
	}

		echo 'OK';
	}else{
		echo 'ERROR';
	}
}elseif($type=='update'){
	$id=$_POST['id'];
	$filter=$_POST['filter'];
	
	$count = executeSelect("SELECT COUNT(*) AS count FROM enterprise WHERE rut='$rut' AND NOT id=$id AND type=$filter");
	if($count[0]["count"]==0){
		if($enterprise_type==2){
			executeSql("UPDATE enterprise SET rut='$rut', 
						name='$name', 
						type=$enterprise_type, 
						address='$address', 
						commune_id=$commune_id, 
						phone1='$phone1', 
						phone2='$phone2', 
						city='$city', 
						legal_represent_rut='$legal_represent_rut', 
						legal_represent_name='$legal_represent_name'
						WHERE id=$id");
		}else{
			executeSql("UPDATE enterprise SET rut='$rut', 
						name='$name', 
						type=$enterprise_type, 
						address='$address', 
						commune_id=$commune_id, 
						phone1='$phone1', 
						phone2='$phone2', 
						city='$city', 
						legal_represent_rut='$legal_represent_rut', 
						legal_represent_name='$legal_represent_name',
						legal_represent_commune_id=$legal_represent_commune_id,
						legal_represent_address='$legal_represent_address' ,
						legal_represent_city='$legal_represent_city' 
						WHERE id=$id");
		}
		echo 'OK';
	}else{
		echo 'ERROR';
	}
}elseif($type=='delete'){
	$id=$_POST['id'];
	$count = executeSelect("SELECT COUNT(*) AS count FROM contract WHERE enterprise1_id=$id OR enterprise2_id=$id");
	$count2 = executeSelect("SELECT COUNT(*) AS count FROM format WHERE enterprise1=$id OR enterprise2=$id");
	if($count[0]["count"]==0 && $count2[0]["count"]==0){
		executeSql("DELETE FROM enterprise WHERE id=$id");
		echo 'OK';
	}else{
		echo 'ERROR';
	}
}
//echo json_encode($array);

?>