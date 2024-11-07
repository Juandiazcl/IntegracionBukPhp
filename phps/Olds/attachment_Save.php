<?php
header('Content-Type: text/html; charset=UTF-8'); 

include("../connection/connection.php");

$type=$_POST['type'];

if($type=='save'){

	/*$id=$_POST['id']; //ContractProcess
	$enterprise1=$_POST['enterprise1'];
	$enterprise2=$_POST['enterprise2'];
	$format=$_POST['format'];
	$format_advice=$_POST['format_advice'];
	$date=$_POST['date'];
	$dateStart=$_POST['dateStart'];
	$dateEnd=$_POST['dateEnd'];
	$end_type=$_POST['end_type'];
	
	$lastId=executeSql("INSERT INTO termination(enterprise1_id, enterprise2_id, date, date_start, date_end, end_type, format_id, format_advice_id) VALUES($enterprise1, $enterprise2, '$date', '$dateStart', '$dateEnd', '$end_type', $format, $format_advice)");

	savePersonal($lastId,$dateStart,$dateEnd,$end_type,$work1,$work2);
	saveContracts($lastId);
	echo 'OK';*/

}elseif($type=='update'){

	$id=$_POST['id']; //ContractProcess
	$enterprise1=$_POST['enterprise1'];
	$enterprise2=$_POST['enterprise2'];
	$format=$_POST['format'];
	$date=$_POST['date'];
	$attachmentType=$_POST['attachmentType'];
	/*$dateStart=$_POST['dateStart'];
	$dateEnd=$_POST['dateEnd'];
	$end_type=$_POST['end_type'];*/


	$itemsData=$_POST['personalData'];
	$itemsArray = explode("&&&&&&",$itemsData);
	$where = '';
	
	for($i=0;$i<count($itemsArray)-1;$i++){//Último arreglo vacío
		$itemArray = explode("&&",$itemsArray[$i]);
		
		if($where!=''){
			$where .= ',';
		}
		$where .= $itemArray[0];
	
		if($attachmentType=='SUELDO'){	
			executeSql("UPDATE contract_process SET base_payment=".$itemArray[5]." WHERE contract_personal_id=".$itemArray[0]);
			executeSql("UPDATE contract_personal SET salary_number=".$itemArray[5].", salary_text='".$itemArray[6]."' WHERE id=".$itemArray[0]);
		}

	}

	$verifyOk = true;
	if($attachmentType=='EXTENSION'){
		
		$extensions = executeSelect("SELECT contract_process_id,
									SUM(CASE WHEN ad.attachment_type='EXTENSION' THEN 1	ELSE 0 END) AS extensiones
									FROM attachment_doc ad
									LEFT JOIN contract_process cpr ON cpr.id=ad.contract_process_id
									LEFT JOIN contract_personal cp ON cp.id=cpr.contract_personal_id
									WHERE cp.id IN ($where)
									GROUP BY contract_process_id");
		for($e=0;$e<count($extensions);$e++){
			if($extensions[$e]['extensiones']==2){
				$verifyOk = false;
			}
		}
	}
	


	if($verifyOk==true){
		$lastId=executeSql("INSERT INTO attachment(contract_id, format_id) VALUES($id, $format)");

		/*$contract_process = executeSelect("SELECT cpr.*, cp.personal_id
											FROM contract_process cpr
											LEFT JOIN contract_personal cp ON cp.id=cpr.contract_personal_id
											WHERE cp.contract_id=$id");*/


		$contract_process = executeSelect("SELECT cpr.*, cp.personal_id
											FROM contract_process cpr
											LEFT JOIN contract_personal cp ON cp.id=cpr.contract_personal_id
											WHERE cp.id IN ($where)");
		
		
		for($k=0;$k<count($contract_process);$k++){
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

									EXTRACT(DAY FROM cpr.date_end) AS end_dd,
									(CASE WHEN EXTRACT(MONTH FROM cpr.date_end)=1 THEN 'ENERO' ELSE
									(CASE WHEN EXTRACT(MONTH FROM cpr.date_end)=2 THEN 'FEBRERO' ELSE
									(CASE WHEN EXTRACT(MONTH FROM cpr.date_end)=3 THEN 'MARZO' ELSE
									(CASE WHEN EXTRACT(MONTH FROM cpr.date_end)=4 THEN 'ABRIL' ELSE
									(CASE WHEN EXTRACT(MONTH FROM cpr.date_end)=5 THEN 'MAYO' ELSE
									(CASE WHEN EXTRACT(MONTH FROM cpr.date_end)=6 THEN 'JUNIO' ELSE
									(CASE WHEN EXTRACT(MONTH FROM cpr.date_end)=7 THEN 'JULIO' ELSE
									(CASE WHEN EXTRACT(MONTH FROM cpr.date_end)=8 THEN 'AGOSTO' ELSE
									(CASE WHEN EXTRACT(MONTH FROM cpr.date_end)=9 THEN 'SEPTIEMBRE' ELSE
									(CASE WHEN EXTRACT(MONTH FROM cpr.date_end)=10 THEN 'OCTUBRE' ELSE
									(CASE WHEN EXTRACT(MONTH FROM cpr.date_end)=11 THEN 'NOVIEMBRE' ELSE
									(CASE WHEN EXTRACT(MONTH FROM cpr.date_end)=12 THEN 'DICIEMBRE' END)
									END) END) END) END) END) END) END) END) END) END) END) AS end_mm,
									EXTRACT(YEAR FROM cpr.date_end) AS end_aaaa,

									p.civil_status AS estado_civil,
									CONCAT(p.address,' ',p.address_number) AS domicilio,
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
									WHERE cpr.id=".$contract_process[$k]['id']." ORDER BY p.id DESC");
			

			if($attachmentType=='EXTENSION'){
				$dateEnd=$_POST['dateEnd'];
				executeSql("UPDATE contract_process SET date_end='$dateEnd' WHERE id=".$contract_process[$k]['id']);
			
			}/*elseif($attachmentType=='SUELDO'){
				executeSql($paymentSql[$k]);
			}*/

			//Carga y edición de documento
			for($j=0;$j<count($array);$j++){
				
				$arrayDoc = executeSelect("SELECT fr.*, f.title, f.footer1, f.footer2, f.type, f.firm_ok, f.firm_url, 							f.font_size
										FROM format_row fr
										LEFT JOIN format f ON f.id=fr.format_id
										WHERE f.id=".$format);
				
				$footer1 = str_replace("[C.I. TRABAJADOR]",$array[$j]['rut'],$arrayDoc[0]['footer1']);
				$footer2 = str_replace("[RAZÓN SOCIAL EMPLEADOR]",$array[$j]['nombreEmpleador'],$arrayDoc[0]['footer2']);
				$footer2 = str_replace("[RUT EMPLEADOR]",$array[$j]['rutEmpleador'],$footer2);
				$footer2 = str_replace("[NOMBRE REPRESENTANTE LEGAL]",$array[$j]['nombreRepresentante'],$footer2);
				$footer2 = str_replace("[RUT REPRESENTANTE LEGAL]",$array[$j]['rutRepresentante'],$footer2);

				$lastDocId=executeSql("INSERT INTO attachment_doc(contract_process_id,format_type_id,title,footer1,footer2,format_id,attachment_id,attachment_type,firm_ok,firm_url,font_size) VALUES(".$contract_process[$k]['id'].",".$arrayDoc[0]['type'].",'".$arrayDoc[0]['title']."','".$footer1."','".$footer2."',$format,$lastId,'$attachmentType',".$arrayDoc[0]['firm_ok'].",'".$arrayDoc[0]['firm_url']."',".$arrayDoc[0]['font_size'].")");

				
				for($i=0;$i<count($arrayDoc);$i++){
					$arrayDoc[$i]['text'] = str_replace("[Comuna]", $array[$j]['Comuna'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[Ciudad]", $array[$j]['Comuna'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[día]", $array[$j]['dd'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[mes]", $array[$j]['mm'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[año]", $array[$j]['aaaa'], $arrayDoc[$i]['text']);

					$arrayDoc[$i]['text'] = str_replace("[fin_día]", $array[$j]['end_dd'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[fin_mes]", $array[$j]['end_mm'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[fin_año]", $array[$j]['end_aaaa'], $arrayDoc[$i]['text']);

					$arrayDoc[$i]['text'] = str_replace("[Nombre Trabajador]", $array[$j]['nombreCompleto'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[C.I. Trabajador]", $array[$j]['rut'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[Estado Civil Trabajador]", $array[$j]['estado_civil'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[Domicilio del Trabajador]", $array[$j]['domicilio'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[Nacimiento día]", $array[$j]['nac_dd'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[Nacimiento mes]", $array[$j]['nac_mm'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[Nacimiento año]", $array[$j]['nac_aaaa'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[AFP]", $array[$j]['afp'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[Sistema Salud]", $array[$j]['health_system'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[Sueldo Base]", $array[$j]['salary_number'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[Sueldo Base en palabras]", $array[$j]['salary_text'], $arrayDoc[$i]['text']);

					$arrayDoc[$i]['text'] = str_replace("[Nombre Cliente]", $array[$j]['nombreCliente'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[Dirección Cliente]", $array[$j]['direccionCliente'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[Ciudad Cliente]", $array[$j]['ciudadCliente'], $arrayDoc[$i]['text']);


					$arrayDoc[$i]['text'] = str_replace("[Razón Social Empleador]", $array[$j]['nombreEmpleador'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[RUT Empleador]", $array[$j]['rutEmpleador'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[Domicilio Empleador]", $array[$j]['direccionEmpleador'], $arrayDoc[$i]['text']);
					
					$arrayDoc[$i]['text'] = str_replace("[Nombre Representante Legal]", $array[$j]['nombreRepresentante'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[RUT Representante Legal]", $array[$j]['rutRepresentante'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[Domicilio Representante Legal]", $array[$j]['direccionRepresentante'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[Comuna Representante Legal]", $array[$j]['comunaRepresentante'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[Ciudad Representante Legal]", $array[$j]['ciudadRepresentante'], $arrayDoc[$i]['text']);

					$arrayDoc[$i]['text'] = str_replace("[Inicio Faena]", $array[$j]['inicio_faena'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[Fin Faena]", $array[$j]['fin_faena'], $arrayDoc[$i]['text']);

					$arrayDoc[$i]['rut'] = $array[$j]['rut'];

					executeSql("INSERT INTO attachment_doc_row(number,text,attachment_doc_id) VALUES(".$arrayDoc[$i]['number'].",'".$arrayDoc[$i]['text']."',".$lastDocId.")");

				}
				
			}
		}
		echo 'OK';
	}else{
		echo 'EXISTE';
	}

}



?>