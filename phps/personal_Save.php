<?php
header('Content-Type: text/html; charset=UTF-8'); 

include("../connection/connection.php");





if($type=='account'){
$type=$_POST['type'];
$rut=$_POST['rut'];
$accountType=$_POST['accountType'];
$accountNumber=$_POST['accountNumber'];
$accountBank=$_POST['accountBank'];
	executeSql("UPDATE PERSONAL SET 
				cta_tipo='$accountType',
				cta_numero='$accountNumber',
				cta_banco='$accountBank'
				WHERE rut_per=$rut");
	echo 'OK';
}
if($type=='save2'){
	$txtRut=$_POST['txtRut'];
	echo $txtRut;
	// $listPerEnter=$_POST['listPerEnter'];
	// $RutEnter=0;
	// if ($listPerEnter=0){
	// 	$RutEnter=$listPerEnter;
	// 	else

	// }
	$listPerEnter=$_POST['listPerEnter'];
	switch ($listPerEnter) {
		case 0:
			$RutEnter=3045710;
			break;
		case 1:
			$RutEnter=76427839;
			break;
		case 2:
			$RutEnter=76125892;
			break;
		case 3:
			$RutEnter=59097850;
				break;
	}
	$listPerPlant=$_POST['listPerPlant'];
	
	switch ($listPerPlant) {
		case 0:
			$CodCampo="09";
			break;
		case 1:
			$CodCampo="14";
			break;
		case 2:
			$CodCampo="10";
			break;
		case 3:
			$CodCampo="02";
			break;
		case 4:
			$CodCampo="01";
			break;
		case 5:
			$CodCampo="12";
			break;
		case 6:
			$CodCampo="04";
			break;
		case 7:
			$CodCampo="08";
			break;
		case 8:
			$CodCampo="07";
			break;
		case 9:
			$CodCampo="11";
			break;
		case 10:
			$CodCampo="05";
			break;
		case 11:
			$CodCampo="06";
			break;
		case 12:
			$CodCampo="13";
			break;
		case 13:
			$CodCampo="03";
			break;
	}
	echo $listPerPlant;
	$listPerState=$_POST['listPerState'];
	$txtLastname1=$_POST['txtLastname1'];
	$txtLastname2=$_POST['txtLastname2'];	
	$txtName=$_POST['txtName'];
	$txtAddress=$_POST['txtAddress'];
	$listCommune=$_POST['listCommune'];
	$txtPhone=$_POST['txtPhone'];
	$txtCellphone=$_POST['txtCellphone'];
	$txtMail=$_POST['txtMail'];
	$listJob=$_POST['listJob'];
	$listEducation=$_POST['listEducation'];
	$txtCountry=$_POST['txtCountry'];
	$txtBirthDate=$_POST['txtBirthDate'];
	$listCivilState=$_POST['listCivilState'];
	$listGender=$_POST['listGender'];
	$listMilitary=$_POST['listMilitary'];
	$txtFamilyLoadSection=$_POST['txtFamilyLoadSection'];
	$txtFamilyLoadQuantity=$_POST['txtFamilyLoadQuantity'];
	$txtCCSoftland=$_POST['txtCCSoftland'];
	$txtContratStartDate=$_POST['txtFamilyLoadQuantity'];
	$txtContratEndDate=$_POST['txtContratEndDate'];
	$listAFP=$_POST['listAFP'];
	$listAFPVoluntary=$_POST['listAFPVoluntary'];
	$txtAFPVoluntaryAmount=$_POST['txtAFPVoluntaryAmount'];
	$listHealthSystem=$_POST['listHealthSystem'];
	$txtAFPVoluntaryAmount=$_POST['txtAFPVoluntaryAmount'];

	$SoloRut=substr($txtRut,0,8);
	$SoloDv=substr($txtRut,9,1);
	$count = executeSelect("SELECT COUNT(*) AS count FROM T0002 WHERE Usr_Codigo='$username'");
	if($count[0]["count"]==0){
		//executeSql("INSERT INTO Personal(rut_per, dv_per, emp_per, cc1_per, estado_per, apepat_per, apemat_per, nom_per, direc_per) VALUES($SoloRut, '$SoloDv', $RutEnter, '$CodCampo','$listPerState','$txtLastname1','$txtLastname2','$txtName'");
		//saveModules($username);
		//savePlants($username,$listPlant);
		echo 'OK';
	}else{
		echo 'ERROR';
	}
}

?>