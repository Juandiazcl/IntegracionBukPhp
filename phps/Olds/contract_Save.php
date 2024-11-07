<?php
header('Content-Type: text/html; charset=UTF-8'); 

include("../connection/connection.php");

$type=$_POST['type'];
if($type!='delete' && $type!='state'){
	$id=$_POST['id'];
	$enterprise1=$_POST['enterprise1'];
	$enterprise2=$_POST['enterprise2'];
	$format=$_POST['format'];
	$date=$_POST['date'];
	$dateStart=$_POST['dateStart'];
	$dateEnd=$_POST['dateEnd'];
	$end_type=$_POST['end_type'];
	$work1=$_POST['work1'];
	$work2=$_POST['work2'];
	$season=$_POST['season'];
}

if($type=='save'){
	//$count = executeSelect("SELECT COUNT(*) AS count FROM personal WHERE rut='$rut'");
	//if($count[0]["count"]==0){
	
	$lastId=executeSql("INSERT INTO contract(enterprise1_id, enterprise2_id, date, date_start, date_end, end_type, work1, work2, format_id, season) VALUES($enterprise1, $enterprise2, '$date', '$dateStart', '$dateEnd', '$end_type', '$work1', '$work2', $format, '$season')");


	//$data = executeSelect("SELECT LAST_INSERT_ID();");
	//savePersonal($data[0]["LAST_INSERT_ID"]);
	//saveSchedule($lastId);
	savePersonal($lastId,$dateStart,$dateEnd,$end_type,$work1,$work2);
	//saveContracts($lastId);
	echo $lastId;
	/*}else{
		echo 'ERROR';
	}*/
}elseif($type=='update'){
	$id=$_POST['id'];
	//$count = executeSelect("SELECT COUNT(*) AS count FROM personal WHERE rut='$rut' AND NOT id=$id");
	//if($count[0]["count"]==0){
	executeSql("UPDATE contract SET enterprise1_id=$enterprise1, enterprise2_id=$enterprise2, date='$date', date_start='$dateStart', date_end='$dateEnd', end_type='$end_type', work1='$work1', work2='$work2', format_id=$format, season='$season' WHERE id=$id");
	
	savePersonal($id,$dateStart,$dateEnd,$end_type,$work1,$work2);
	//saveContracts($id);
	echo $id;
	/*}else{
		echo 'ERROR';
	}*/
}elseif($type=='delete'){
	$id=$_POST['id'];
	executeSql("DELETE FROM contract WHERE id=$id");

}elseif($type=='saveContracts'){
	$id=$_POST['id'];
	saveContracts($id,$format);
	echo $id;

}elseif($type=='state'){
	$state=$_POST['state'];
	$itemsData=$_POST['personalData'];
	$itemsArray = explode("&&",$itemsData);
	$state_personal=$_POST['state'];
	if($state=='FINIQUITADO'){
		$state_personal="DISPONIBLE";
	}

	for($i=0;$i<count($itemsArray)-1;$i++){
		executeSql("UPDATE contract_process SET state='$state' WHERE contract_personal_id=".$itemsArray[$i]);
		$arrayPersonal = executeSelect("SELECT * FROM contract_personal WHERE id=".$itemsArray[$i]);
		executeSql("UPDATE personal SET state='$state_personal' WHERE id=".$arrayPersonal[0]['personal_id']);
	}
	echo 'OK';


}
//echo json_encode($array);

function saveSchedule($id){
	$itemsData=$_POST['scheduleData'];
	$itemsArray = explode("&&",$itemsData);
	executeSql("DELETE FROM contract_schedule WHERE contract_id=$id");

	for($i=0;$i<count($itemsArray)-1;$i++){
		executeSql("INSERT INTO contract_schedule(contract_id, text) VALUES($id, '".$itemsArray[$i]."')");
	}
}

function savePersonal($id,$date1,$date2,$end_type,$work1,$work2){
	$itemsData=$_POST['personalData'];
	$itemsArray = explode("&&&&",$itemsData);
	//echo ' - 1 '.date('h:i:s');


	$all_querys = '';
	for($i=1;$i<count($itemsArray)-1;$i++){//1er y último arreglo son vacíos
		$itemArray = explode("&&",$itemsArray[$i]);
		//echo ' - 1V '.date('h:i:s');
		
		$count = executeSelect("SELECT COUNT(*) AS count FROM contract_personal WHERE id='".$itemArray[0]."'");
		if($count[0]["count"]==0){
			$lastId = executeSql("INSERT INTO contract_personal(contract_id, personal_id, workplace, season, charge, salary_number, salary_text, collation_time) VALUES($id, ".$itemArray[1].", '-', '-', '-', '".$itemArray[5]."', '".$itemArray[6]."', '-')");

			//$lastId = executeSql("INSERT INTO contract_personal(contract_id, personal_id, workplace, season, charge, salary_number, salary_text, collation_time) VALUES($id, ".$itemArray[1].", '-', '".$itemArray[5]."', '".$itemArray[6]."', '".$itemArray[7]."', '".$itemArray[8]."', '".$itemArray[9]."')");
			
			if($itemArray[5]=='-'){
				$itemArray[5]=0;
			}
			executeSql("INSERT INTO contract_process(contract_personal_id, date_start, date_end, state, end_type, work1, work2, base_payment) VALUES($lastId, '".$date1."', '".$date2."','POR CONTRATAR', '$end_type', '$work1', '$work2',".$itemArray[5].")");

			executeSql("UPDATE personal SET state='POR CONTRATAR' WHERE id=".$itemArray[1]);
		}else{
			executeSql("UPDATE contract_personal SET contract_id=$id, 
				personal_id=".$itemArray[1].", 
				workplace='-', 
				season='-',
				charge='-', 
				salary_number='".$itemArray[5]."', 
				salary_text='".$itemArray[6]."', 
				collation_time='-' 
				WHERE id=".$itemArray[0]);


			executeSql("UPDATE contract_process
						SET date_start='".$date1."', 
							date_end='".$date2."', 
							state='POR CONTRATAR',
							end_type='$end_type',
							work1='$work1', 
							work2='$work2',
							base_payment=".$itemArray[5]."
						WHERE contract_personal_id=".$itemArray[0]);
			

			/*executeSql("UPDATE contract_personal SET contract_id=$id, 
				personal_id=".$itemArray[1].", 
				workplace='-', 
				season='".$itemArray[5]."',
				charge='".$itemArray[6]."', 
				salary_number='".$itemArray[7]."', 
				salary_text='".$itemArray[8]."', 
				collation_time='".$itemArray[9]."' 
				WHERE id=".$itemArray[0]);*/

		}
	}

}


function saveContracts($idContract,$format){
	$array = executeSelect("SELECT
							p.rut AS rut,
							CONCAT(p.name,' ',p.lastname1,' ',p.lastname2) AS nombreCompleto,
							EXTRACT(DAY FROM cpr.date_start) AS dd,
							(CASE WHEN EXTRACT(MONTH FROM cpr.date_start)=1 THEN 'ENERO' ELSE
							(CASE WHEN EXTRACT(MONTH FROM cpr.date_start)=2 THEN 'FEBRERO' ELSE
							(CASE WHEN EXTRACT(MONTH FROM cpr.date_start)=3 THEN 'MARZO' ELSE
							(CASE WHEN EXTRACT(MONTH FROM cpr.date_start)=4 THEN 'ABRIL' ELSE
							(CASE WHEN EXTRACT(MONTH FROM cpr.date_start)=5 THEN 'MAYO' ELSE
							(CASE WHEN EXTRACT(MONTH FROM cpr.date_start)=6 THEN 'JUNIO' ELSE
							(CASE WHEN EXTRACT(MONTH FROM cpr.date_start)=7 THEN 'JULIO' ELSE
							(CASE WHEN EXTRACT(MONTH FROM cpr.date_start)=8 THEN 'AGOSTO' ELSE
							(CASE WHEN EXTRACT(MONTH FROM cpr.date_start)=9 THEN 'SEPTIEMBRE' ELSE
							(CASE WHEN EXTRACT(MONTH FROM cpr.date_start)=10 THEN 'OCTUBRE' ELSE
							(CASE WHEN EXTRACT(MONTH FROM cpr.date_start)=11 THEN 'NOVIEMBRE' ELSE
							(CASE WHEN EXTRACT(MONTH FROM cpr.date_start)=12 THEN 'DICIEMBRE' END)
							END) END) END) END) END) END) END) END) END) END) END) AS mm,
							EXTRACT(YEAR FROM cpr.date_start) AS aaaa,
							p.civil_status AS estado_civil,
							CONCAT(p.address) AS domicilio,
							cm.name AS Comuna,
							EXTRACT(DAY FROM p.birthdate) AS nac_dd,
							(CASE WHEN EXTRACT(MONTH FROM p.birthdate)=1 THEN 'ENERO' ELSE
							(CASE WHEN EXTRACT(MONTH FROM p.birthdate)=2 THEN 'FEBRERO' ELSE
							(CASE WHEN EXTRACT(MONTH FROM p.birthdate)=3 THEN 'MARZO' ELSE
							(CASE WHEN EXTRACT(MONTH FROM p.birthdate)=4 THEN 'ABRIL' ELSE
							(CASE WHEN EXTRACT(MONTH FROM p.birthdate)=5 THEN 'MAYO' ELSE
							(CASE WHEN EXTRACT(MONTH FROM p.birthdate)=6 THEN 'JUNIO' ELSE
							(CASE WHEN EXTRACT(MONTH FROM p.birthdate)=7 THEN 'JULIO' ELSE
							(CASE WHEN EXTRACT(MONTH FROM p.birthdate)=8 THEN 'AGOSTO' ELSE
							(CASE WHEN EXTRACT(MONTH FROM p.birthdate)=9 THEN 'SEPTIEMBRE' ELSE
							(CASE WHEN EXTRACT(MONTH FROM p.birthdate)=10 THEN 'OCTUBRE' ELSE
							(CASE WHEN EXTRACT(MONTH FROM p.birthdate)=11 THEN 'NOVIEMBRE' ELSE
							(CASE WHEN EXTRACT(MONTH FROM p.birthdate)=12 THEN 'DICIEMBRE' END)
							END) END) END) END) END) END) END) END) END) END) END) AS nac_mm,
							EXTRACT(YEAR FROM p.birthdate) AS nac_aaaa,
							cp.salary_number,
							cp.salary_text,
							a.name AS afp,
							h.name AS health_system,
							p.gender,

							c.format_id AS format,
							cpr.id AS contractProcessID,

							e1.rut AS rutEmpleador,
							e1.name AS nombreEmpleador,
							e1.address AS direccionEmpleador,
							e1.city AS ciudadEmpleador,

							e1.legal_represent_rut AS rutRepresentante,
							e1.legal_represent_name AS nombreRepresentante,
							e1.legal_represent_address AS direccionRepresentante,
							(SELECT clr.name FROM commune clr WHERE clr.id=e1.legal_represent_commune_id) AS comunaRepresentante,
							e1.legal_represent_city AS ciudadRepresentante,

							e1.rut AS rutCliente,
							e1.name AS nombreCliente,
							e1.address AS direccionCliente,
							e1.city AS ciudadCliente,

							cpr.work1 AS inicio_faena,
							cpr.work2 AS fin_faena

							FROM personal p
							LEFT JOIN contract_personal cp ON cp.personal_id=p.id
							LEFT JOIN contract_process cpr ON cpr.contract_personal_id=cp.id
							LEFT JOIN contract c ON c.id=cp.contract_id
							LEFT JOIN commune cm ON cm.id=p.commune_id
							LEFT JOIN afp a ON a.id=p.afp_id
							LEFT JOIN health_system h ON h.id=p.health_system_id
							LEFT JOIN enterprise e1 ON e1.id=c.enterprise1_id
							LEFT JOIN enterprise e2 ON e2.id=c.enterprise2_id
							WHERE c.id=$idContract ORDER BY p.id DESC");

	$arrayDoc = executeSelect("SELECT fr.*, f.title, f.footer1, f.footer2, f.type, f.firm_ok, f.firm_url, f.font_size
								FROM format_row fr
								LEFT JOIN format f ON f.id=fr.format_id
								WHERE f.id=".$format);
	//Carga y edición de documento
	for($j=0;$j<count($array);$j++){
		if($array[$j]['gender']=='FEMENINO'){
			if($array[$j]['estado_civil']=='SOLTERO'){
				$array[$j]['estado_civil']='SOLTERA';
			}
			if($array[$j]['estado_civil']=='CASADO'){
				$array[$j]['estado_civil']='CASADA';
			}
			if($array[$j]['estado_civil']=='DIVORCIADO'){
				$array[$j]['estado_civil']='DIVORCIADA';
			}
			if($array[$j]['estado_civil']=='SEPARADO'){
				$array[$j]['estado_civil']='SEPARADA';
			}
			if($array[$j]['estado_civil']=='VIUDO'){
				$array[$j]['estado_civil']='VIUDA';
			}
		}
		//Temporal, se eliminan todo el historial de contrato al realmacenar los datos
		executeSql("DELETE FROM contract_doc WHERE contract_process_id=".$array[$j]['contractProcessID']);

		
		$footer1 = str_replace("[C.I. TRABAJADOR]",$array[$j]['rut'],$arrayDoc[0]['footer1']);
		$lastDocId=executeSql("INSERT INTO contract_doc(contract_process_id,format_type_id,title,footer1,footer2,firm_ok,firm_url,font_size) VALUES(".$array[$j]['contractProcessID'].",".$arrayDoc[0]['type'].",'".$arrayDoc[0]['title']."','".$footer1."','".$arrayDoc[0]['footer2']."',".$arrayDoc[0]['firm_ok'].",'".$arrayDoc[0]['firm_url']."',".$arrayDoc[0]['font_size'].")");
		//echo "INSERT INTO contract_doc(contract_process_id,format_type_id,title,footer1,footer2,firm_ok,firm_url) VALUES(".$array[$j]['contractProcessID'].",".$arrayDoc[0]['type'].",'".$arrayDoc[0]['title']."','".$footer1."','".$arrayDoc[0]['footer2']."',".$arrayDoc[0]['firm_ok'].",'".$arrayDoc[0]['firm_url']."')";

		for($i=0;$i<count($arrayDoc);$i++){
			$text_value = $arrayDoc[$i]['text'];
			$text_value = str_replace("[Comuna]", $array[$j]['Comuna'], $text_value);
			$text_value = str_replace("[Ciudad]", $array[$j]['Comuna'], $text_value);
			$text_value = str_replace("[día]", $array[$j]['dd'], $text_value);
			$text_value = str_replace("[mes]", $array[$j]['mm'], $text_value);
			
			$text_value = str_replace("[año]", $array[$j]['aaaa'], $text_value);

			$text_value = str_replace("[Nombre Trabajador]", $array[$j]['nombreCompleto'], $text_value);
			$text_value = str_replace("[C.I. Trabajador]", $array[$j]['rut'], $text_value);
			$text_value = str_replace("[Estado Civil Trabajador]", $array[$j]['estado_civil'], $text_value);
			$text_value = str_replace("[Domicilio del Trabajador]", $array[$j]['domicilio'], $text_value);
			$text_value = str_replace("[Nacimiento día]", $array[$j]['nac_dd'], $text_value);
			$text_value = str_replace("[Nacimiento mes]", $array[$j]['nac_mm'], $text_value);
			$text_value = str_replace("[Nacimiento año]", $array[$j]['nac_aaaa'], $text_value);
			$text_value = str_replace("[AFP]", $array[$j]['afp'], $text_value);
			$text_value = str_replace("[Sistema Salud]", $array[$j]['health_system'], $text_value);
			$text_value = str_replace("[Sueldo Base]", $array[$j]['salary_number'], $text_value);
			$text_value = str_replace("[Sueldo Base en palabras]", $array[$j]['salary_text'], $text_value);

			$text_value = str_replace("[Nombre Cliente]", $array[$j]['nombreCliente'], $text_value);
			$text_value = str_replace("[Dirección Cliente]", $array[$j]['direccionCliente'], $text_value);
			$text_value = str_replace("[Ciudad Cliente]", $array[$j]['ciudadCliente'], $text_value);


			$text_value = str_replace("[Razón Social Empleador]", $array[$j]['nombreEmpleador'], $text_value);
			$text_value = str_replace("[RUT Empleador]", $array[$j]['rutEmpleador'], $text_value);
			$text_value = str_replace("[Domicilio Empleador]", $array[$j]['direccionEmpleador'], $text_value);
			
			$text_value = str_replace("[Nombre Representante Legal]", $array[$j]['nombreRepresentante'], $text_value);
			$text_value = str_replace("[RUT Representante Legal]", $array[$j]['rutRepresentante'], $text_value);
			$text_value = str_replace("[Domicilio Representante Legal]", $array[$j]['direccionRepresentante'], $text_value);
			$text_value = str_replace("[Comuna Representante Legal]", $array[$j]['comunaRepresentante'], $text_value);
			$text_value = str_replace("[Ciudad Representante Legal]", $array[$j]['ciudadRepresentante'], $text_value);

			$text_value = str_replace("[Inicio Faena]", $array[$j]['inicio_faena'], $text_value);
			$text_value = str_replace("[Fin Faena]", $array[$j]['fin_faena'], $text_value);

			$arrayDoc[$i]['rut'] = $array[$j]['rut'];

			executeSql("INSERT INTO contract_doc_row(number,text,contract_doc_id) VALUES(".$arrayDoc[$i]['number'].",'".$text_value."',".$lastDocId.")");
		}
	}

}

?>