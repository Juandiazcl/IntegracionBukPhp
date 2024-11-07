<?php
header('Content-Type: text/html; charset=UTF-8'); 

include("../connection/connection.php");

$type=$_POST['type'];
if($type!='delete' && $type!='contact'){
	$id=$_POST['id'];
	$rut=$_POST['rut'];
	$name=$_POST['name'];
	$lastname1=$_POST['lastname1'];
	$lastname2=$_POST['lastname2'];
	$birthdate=$_POST['birthdate'];
	$gender=$_POST['gender'];
	$civil_status=$_POST['civil_status'];
	$commune_id=$_POST['commune_id'];
	$sector_id=$_POST['sector_id'];
	$address=$_POST['address'];
	$address_number=$_POST['address_number'];
	$charge_id=$_POST['charge_id'];
	$health_system_id=$_POST['health_system_id'];
	$afp_id=$_POST['afp_id'];
	$driver_license_id=$_POST['driver_license_id'];
	$driver_license_date=$_POST['driver_license_date'];
	$turn=$_POST['turn'];
	$cellphone=$_POST['cellphone'];
	$phone=$_POST['phone'];
	$mail=$_POST['mail'];
	$clothing_size=$_POST['clothing_size'];
	$shoe_size=$_POST['shoe_size'];
	$payment_mode=$_POST['payment_mode'];
	$bank=$_POST['bank'];
	$bank_account=$_POST['bank_account'];
	$rut_date=$_POST['rut_date'];
}

if($type=='save'){
	$count = executeSelect("SELECT COUNT(*) AS count FROM personal WHERE rut='$rut'");
	if($count[0]["count"]==0){
		$lastId = executeSql("INSERT INTO personal(rut, name, lastname1, lastname2, birthdate, gender, civil_status, commune_id, sector_id, address, address_number, charge_id, turn, cellphone, phone, mail, state, health_system_id, afp_id,  driver_license_date, clothing_size, shoe_size, payment_type, payment_bank, payment_account, rut_date) VALUES('$rut', '$name', '$lastname1', '$lastname2', '$birthdate', '$gender', '$civil_status', $commune_id, $sector_id, '$address', '$address_number', $charge_id, '$turn', '$cellphone', '$phone', '$mail', 'DISPONIBLE', $health_system_id, $afp_id, '$driver_license_date', '$clothing_size', '$shoe_size', '$payment_mode', '$bank', '$bank_account', '$rut_date')");

		$listLicense = explode("-",$driver_license_id);
		for($d=0;$d<count($listLicense);$d++){
			executeSql("INSERT INTO driver_license_personal(personal_id,driver_license_id) VALUES(".$lastId.",".$listLicense[$d].")");
		}

		echo 'OK';
	}else{
		echo 'ERROR';
	}
}elseif($type=='update'){
	$id=$_POST['id'];
	$count = executeSelect("SELECT COUNT(*) AS count FROM personal WHERE rut='$rut' AND NOT id=$id");
	if($count[0]["count"]==0){
		executeSql("UPDATE personal SET rut='$rut', name='$name', lastname1='$lastname1', lastname2='$lastname2', birthdate='$birthdate', gender='$gender', civil_status='$civil_status', commune_id=$commune_id, sector_id=$sector_id, address='$address', address_number='$address_number', charge_id=$charge_id, turn='$turn', cellphone='$cellphone', phone='$phone', mail='$mail', health_system_id='$health_system_id', afp_id='$afp_id', driver_license_date='$driver_license_date', clothing_size='$clothing_size', shoe_size='$shoe_size', payment_type='$payment_mode', payment_bank='$bank', payment_account='$bank_account', rut_date='$rut_date' WHERE id=$id");

		executeSql("DELETE FROM driver_license_personal WHERE personal_id=$id");
		$listLicense = explode("-",$driver_license_id);
		for($d=0;$d<count($listLicense);$d++){
			executeSql("INSERT INTO driver_license_personal(personal_id,driver_license_id) VALUES(".$id.",".$listLicense[$d].")");
		}
		echo 'OK';
	}else{
		echo 'ERROR';
	}
}elseif($type=='delete'){
	$id=$_POST['id'];
	$count = executeSelect("SELECT COUNT(*) AS count FROM contract_personal WHERE personal_id=$id");
	if($count[0]["count"]==0){
		executeSql("DELETE FROM personal WHERE id=$id");
		executeSql("DELETE FROM driver_license_personal WHERE personal_id=$id");
		echo 'OK';
	}else{
		echo 'ERROR';
	}

}elseif($type=='contact'){
	$id=$_POST['id'];
	$state=$_POST['state'];
	$observation=$_POST['observation'];
	executeSql("UPDATE personal SET contacted_state='$state', contacted_observation='$observation' WHERE id=$id");
}
//echo json_encode($array);

?>