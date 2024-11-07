<?php
header('Content-Type: text/html; charset=UTF-8'); 

include("../connection/connection.php");

$type=$_POST['type'];

if($type=='state'){
	$id=$_POST['id'];
	$state=$_POST['state'];
	$personal_id=$_POST['personal_id'];
	
	executeSql("UPDATE contract_process SET state='$state' WHERE id=$id");
	if($state=='FINIQUITADO'){
		$state='DISPONIBLE';
	}
	executeSql("UPDATE personal SET state='$state' WHERE id=$personal_id");
	echo 'OK';

}if($type=='termination'){

	$id=$_POST['id']; //ContractProcess
	$enterprise1=$_POST['enterprise1'];
	$enterprise2=$_POST['enterprise2'];
	$format=$_POST['format'];
	$format_advice=$_POST['format_advice'];
	$date=$_POST['date'];
	$dateStart=$_POST['dateStart'];
	$dateEnd=$_POST['dateEnd'];
	$end_type=$_POST['end_type'];
	
	$lastId=executeSql("INSERT INTO termination(enterprise1_id, enterprise2_id, date, date_start, date_end, end_type, format_id, format_advice_id) VALUES($enterprise1, $enterprise2, '$date', '$dateStart', '$dateEnd', '$end_type', $format, $format_advice)");

	$contract_process = executeSelect("SELECT *, (SELECT personal_id FROM contract_personal cp WHERE cp.id=cpr.contract_personal_id) AS personal_id FROM contract_process cpr WHERE id=$id");
	//executeSql("DELETE FROM termination_personal WHERE termination_id=$lastId AND personal_id=".$contract_process[0]['personal_id']);
	$termination_personal_id = executeSql("INSERT INTO termination_personal(termination_id, personal_id) VALUES($lastId, ".$contract_process[0]['personal_id'].")");

	executeSql("UPDATE contract_process SET termination_personal_id=".$termination_personal_id.", date_end='$dateEnd', state='PREFINIQUITADO' WHERE id=$id");
	executeSql("UPDATE personal SET state='PREFINIQUITADO' WHERE id=".$contract_process[0]['personal_id']);

	$array = executeSelect("SELECT
							p.rut AS rut,
							CONCAT(p.name,' ',p.lastname1,' ',p.lastname2) AS nombre,
							EXTRACT(DAY FROM cpr.date_start) AS dd,
							EXTRACT(MONTH FROM cpr.date_start) AS mm,
							EXTRACT(YEAR FROM cpr.date_start) AS aaaa,
							p.civil_status AS estado_civil,
							CONCAT(p.address,' ',p.address_number) AS domicilio,
							cm.name AS comuna,
							EXTRACT(DAY FROM p.birthdate) AS nac_dd,
							EXTRACT(MONTH FROM p.birthdate) AS nac_mm,
							EXTRACT(YEAR FROM p.birthdate) AS nac_aaaa,
							a.name AS afp,
							c.format_id AS format,
							c.format_advice_id AS format_advice_id,
							cpr.id AS terminationProcessID

							FROM personal p
							LEFT JOIN termination_personal cp ON cp.personal_id=p.id
							LEFT JOIN contract_process cpr ON cpr.termination_personal_id=cp.id
							LEFT JOIN termination c ON c.id=cp.termination_id
							LEFT JOIN commune cm ON cm.id=p.commune_id
							LEFT JOIN afp a ON a.id=p.afp_id
							WHERE cpr.id=$id ORDER BY p.id DESC");


	//Carga y edición de documento
	for($j=0;$j<count($array);$j++){
		
		//Temporal, se eliminan todo el historial de contrato al realmacenar los datos
		executeSql("DELETE FROM termination_doc WHERE termination_process_id=".$id);

		$arrayDoc = executeSelect("SELECT fr.*, f.title, f.footer1, f.footer2, f.type
								FROM format_row fr
								LEFT JOIN format f ON f.id=fr.format_id
								WHERE f.id=".$array[$j]['format']);
		
		$lastDocId=executeSql("INSERT INTO termination_doc(termination_personal_id, termination_process_id,format_type_id,title,footer1,footer2) VALUES($termination_personal_id,".$id.",".$arrayDoc[0]['type'].",'".$arrayDoc[0]['title']."','".$arrayDoc[0]['footer1']."','".$arrayDoc[0]['footer2']."')");

		for($i=0;$i<count($arrayDoc);$i++){
			$arrayDoc[$i]['text'] = str_replace("[rut]", $array[$j]['rut'], $arrayDoc[$i]['text']);
			$arrayDoc[$i]['text'] = str_replace("[nombre]", $array[$j]['nombre'], $arrayDoc[$i]['text']);
			$arrayDoc[$i]['text'] = str_replace("[dd]", $array[$j]['dd'], $arrayDoc[$i]['text']);
			$arrayDoc[$i]['text'] = str_replace("[mm]", $array[$j]['mm'], $arrayDoc[$i]['text']);
			$arrayDoc[$i]['text'] = str_replace("[aaaa]", $array[$j]['aaaa'], $arrayDoc[$i]['text']);
			$arrayDoc[$i]['text'] = str_replace("[estado_civil]", $array[$j]['estado_civil'], $arrayDoc[$i]['text']);
			$arrayDoc[$i]['text'] = str_replace("[domicilio]", $array[$j]['domicilio'], $arrayDoc[$i]['text']);
			$arrayDoc[$i]['text'] = str_replace("[comuna]", $array[$j]['comuna'], $arrayDoc[$i]['text']);
			$arrayDoc[$i]['text'] = str_replace("[nac_dd]", $array[$j]['nac_dd'], $arrayDoc[$i]['text']);
			$arrayDoc[$i]['text'] = str_replace("[nac_mm]", $array[$j]['nac_mm'], $arrayDoc[$i]['text']);
			$arrayDoc[$i]['text'] = str_replace("[nac_aaaa]", $array[$j]['nac_aaaa'], $arrayDoc[$i]['text']);
			$arrayDoc[$i]['text'] = str_replace("[afp]", $array[$j]['afp'], $arrayDoc[$i]['text']);
			$arrayDoc[$i]['rut'] = $array[$j]['rut'];

			executeSql("INSERT INTO termination_doc_row(number,text,termination_doc_id) VALUES(".$arrayDoc[$i]['number'].",'".$arrayDoc[$i]['text']."',".$lastDocId.")");

		}

		//Temporal, se eliminan todo el historial de contrato al realmacenar los datos
		executeSql("DELETE FROM advice_doc WHERE termination_process_id=".$id);

		$arrayDocAdvice = executeSelect("SELECT fr.*, f.title, f.footer1, f.footer2, f.type
								FROM format_row fr
								LEFT JOIN format f ON f.id=fr.format_id
								WHERE f.id=".$array[$j]['format_advice_id']);
		
		$lastDocAdviceId=executeSql("INSERT INTO advice_doc(termination_personal_id,termination_process_id,format_type_id,title,footer1,footer2) VALUES($termination_personal_id,".$id.",".$arrayDocAdvice[0]['type'].",'".$arrayDocAdvice[0]['title']."','".$arrayDocAdvice[0]['footer1']."','".$arrayDocAdvice[0]['footer2']."')");

		for($i=0;$i<count($arrayDocAdvice);$i++){
			$arrayDocAdvice[$i]['text'] = str_replace("[rut]", $array[$j]['rut'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[nombre]", $array[$j]['nombre'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[dd]", $array[$j]['dd'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[mm]", $array[$j]['mm'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[aaaa]", $array[$j]['aaaa'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[estado_civil]", $array[$j]['estado_civil'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[domicilio]", $array[$j]['domicilio'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[comuna]", $array[$j]['comuna'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[nac_dd]", $array[$j]['nac_dd'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[nac_mm]", $array[$j]['nac_mm'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[nac_aaaa]", $array[$j]['nac_aaaa'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[afp]", $array[$j]['afp'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['rut'] = $array[$j]['rut'];

			executeSql("INSERT INTO advice_doc_row(number,text,advice_doc_id) VALUES(".$arrayDocAdvice[$i]['number'].",'".$arrayDocAdvice[$i]['text']."',".$lastDocAdviceId.")");

		}

		//$array[$j]["document"] = $arrayDoc;
	}

echo 'OK';


}if($type=='cancel_termination'){

	$id=$_POST['id'];
	$state=$_POST['state'];
	$personal_id=$_POST['personal_id'];
	
	$contract_process = executeSelect("SELECT * FROM contract_process cpr WHERE id=$id");

	$termination_personal = executeSelect("SELECT * FROM termination_personal WHERE id=".$contract_process[0]['termination_personal_id']);

	$termination_doc = executeSelect("SELECT * FROM termination_doc WHERE termination_process_id=$id");
	$advice_doc = executeSelect("SELECT * FROM advice_doc WHERE termination_process_id=$id");

	executeSql("DELETE FROM termination_doc_row WHERE termination_doc_id=".$termination_doc[0]['id']);
	executeSql("DELETE FROM termination_doc WHERE id=".$termination_doc[0]['id']);
	executeSql("DELETE FROM termination_personal WHERE id=".$termination_personal[0]['id']);

	executeSql("DELETE FROM advice_doc_row WHERE advice_doc_id=".$advice_doc[0]['id']);
	executeSql("DELETE FROM advice_doc WHERE id=".$advice_doc[0]['id']);

	executeSql("UPDATE contract_process SET state='$state' WHERE id=$id");
	executeSql("UPDATE personal SET state='$state' WHERE id=$personal_id");

	echo 'OK';


}if($type=='attachment'){

	$id=$_POST['id']; //ContractProcess
	$enterprise1=$_POST['enterprise1'];
	$enterprise2=$_POST['enterprise2'];
	$format=$_POST['format'];
	$date=$_POST['date'];
	$attachmentType=$_POST['attachmentType'];

	$verifyOk = true;
	if($attachmentType=='EXTENSION'){
		$extensions = executeSelect("SELECT COUNT(*) AS COUNT
									FROM attachment_doc ad
									LEFT JOIN contract_process cpr ON cpr.id=ad.contract_process_id
									WHERE cpr.id=$id AND ad.attachment_type='EXTENSION'");
		if($extensions[0]['COUNT']==2){
			$verifyOk = false;
		}
		if($verifyOk==true){
			$dateEnd=$_POST['dateEnd'];
			executeSql("UPDATE contract_process SET date_end='$dateEnd' WHERE id=$id");
		}
	}elseif($attachmentType=='SUELDO'){
		$basePayment=$_POST['basePayment'];
		executeSql("UPDATE contract_process SET base_payment=$basePayment WHERE id=$id");
	}
	
	if($verifyOk==true){

		$array = executeSelect("SELECT
								p.rut AS rut,
								CONCAT(p.name,' ',p.lastname1,' ',p.lastname2) AS nombre,
								EXTRACT(DAY FROM cpr.date_start) AS dd,
								EXTRACT(MONTH FROM cpr.date_start) AS mm,
								EXTRACT(YEAR FROM cpr.date_start) AS aaaa,
								p.civil_status AS estado_civil,
								CONCAT(p.address,' ',p.address_number) AS domicilio,
								cm.name AS comuna,
								EXTRACT(DAY FROM p.birthdate) AS nac_dd,
								EXTRACT(MONTH FROM p.birthdate) AS nac_mm,
								EXTRACT(YEAR FROM p.birthdate) AS nac_aaaa,
								a.name AS afp,
								c.format_id AS format,
								cpr.id AS contractProcessID

								FROM personal p
								LEFT JOIN contract_personal cp ON cp.personal_id=p.id
								LEFT JOIN contract_process cpr ON cpr.contract_personal_id=cp.id
								LEFT JOIN contract c ON c.id=cp.contract_id
								LEFT JOIN commune cm ON cm.id=p.commune_id
								LEFT JOIN afp a ON a.id=p.afp_id
								WHERE cpr.id=$id ORDER BY p.id DESC");


		//Carga y edición de documento
		for($j=0;$j<count($array);$j++){
			
			$arrayDoc = executeSelect("SELECT fr.*, f.title, f.footer1, f.footer2, f.type
									FROM format_row fr
									LEFT JOIN format f ON f.id=fr.format_id
									WHERE f.id=".$format);
			
			$lastDocId=executeSql("INSERT INTO attachment_doc(contract_process_id,format_type_id,title,footer1,footer2,format_id,attachment_type) VALUES(".$id.",".$arrayDoc[0]['type'].",'".$arrayDoc[0]['title']."','".$arrayDoc[0]['footer1']."','".$arrayDoc[0]['footer2']."',$format,'$attachmentType')");

			for($i=0;$i<count($arrayDoc);$i++){
				$arrayDoc[$i]['text'] = str_replace("[rut]", $array[$j]['rut'], $arrayDoc[$i]['text']);
				$arrayDoc[$i]['text'] = str_replace("[nombre]", $array[$j]['nombre'], $arrayDoc[$i]['text']);
				$arrayDoc[$i]['text'] = str_replace("[dd]", $array[$j]['dd'], $arrayDoc[$i]['text']);
				$arrayDoc[$i]['text'] = str_replace("[mm]", $array[$j]['mm'], $arrayDoc[$i]['text']);
				$arrayDoc[$i]['text'] = str_replace("[aaaa]", $array[$j]['aaaa'], $arrayDoc[$i]['text']);
				$arrayDoc[$i]['text'] = str_replace("[estado_civil]", $array[$j]['estado_civil'], $arrayDoc[$i]['text']);
				$arrayDoc[$i]['text'] = str_replace("[domicilio]", $array[$j]['domicilio'], $arrayDoc[$i]['text']);
				$arrayDoc[$i]['text'] = str_replace("[comuna]", $array[$j]['comuna'], $arrayDoc[$i]['text']);
				$arrayDoc[$i]['text'] = str_replace("[nac_dd]", $array[$j]['nac_dd'], $arrayDoc[$i]['text']);
				$arrayDoc[$i]['text'] = str_replace("[nac_mm]", $array[$j]['nac_mm'], $arrayDoc[$i]['text']);
				$arrayDoc[$i]['text'] = str_replace("[nac_aaaa]", $array[$j]['nac_aaaa'], $arrayDoc[$i]['text']);
				$arrayDoc[$i]['text'] = str_replace("[afp]", $array[$j]['afp'], $arrayDoc[$i]['text']);
				$arrayDoc[$i]['rut'] = $array[$j]['rut'];

				executeSql("INSERT INTO attachment_doc_row(number,text,attachment_doc_id) VALUES(".$arrayDoc[$i]['number'].",'".$arrayDoc[$i]['text']."',".$lastDocId.")");

			}

		}

		echo 'OK';
	
	}else{
		echo 'EXISTE';
	}

}


?>