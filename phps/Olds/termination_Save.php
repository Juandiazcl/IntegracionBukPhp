<?php
header('Content-Type: text/html; charset=UTF-8'); 

include("../connection/connection.php");

$type=$_POST['type'];
/*if($type!='delete'){
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
}*/


$id=$_POST['id']; //ContractProcess
$enterprise1=$_POST['enterprise1'];
$enterprise2=$_POST['enterprise2'];
$format=$_POST['format'];
$date=$_POST['date'];
$dateStart=$_POST['dateStart'];
$dateEnd=$_POST['dateEnd'];
$end_type=$_POST['end_type'];

if(!isset($_POST['format_advice'])){
    $format_advice = 0;
}else{
    $format_advice=$_POST['format_advice'];    
}

if($type=='save'){
	$lastId=executeSql("INSERT INTO termination(enterprise1_id, enterprise2_id, date, date_start, date_end, end_type, format_id, format_advice_id) VALUES($enterprise1, $enterprise2, '$date', '$dateStart', '$dateEnd', '$end_type', $format, $format_advice)");

}elseif($type=='update'){
	$lastId=$_POST['termination_id'];
    if($lastId==0){
        $lastId=executeSql("INSERT INTO termination(enterprise1_id, enterprise2_id, date, date_start, date_end, end_type, format_id, format_advice_id) VALUES($enterprise1, $enterprise2, '$date', '$dateStart', '$dateEnd', '$end_type', $format, $format_advice)");    
    }else{
        executeSql("UPDATE termination SET enterprise1_id=$enterprise1, enterprise2_id=$enterprise2, date='$date', date_start='$dateStart', date_end='$dateEnd', end_type='$end_type', format_id=$format, format_advice_id=$format_advice WHERE id=$lastId");    

    }
	
}

/*$contract_process = executeSelect("SELECT cpr.*, cp.personal_id
									FROM contract_process cpr
									LEFT JOIN contract_personal cp ON cp.id=cpr.contract_personal_id
									WHERE cp.contract_id=$id");*/


$itemsData=$_POST['personalData'];

//echo $itemsData;
//$itemsArray = explode("&&&&&&",$itemsData);
$itemsArray = explode("&&&&",$itemsData);
$where = '';


for($i=0;$i<count($itemsArray)-1;$i++){//Último arreglo vacío
	$itemArray = explode("&&",$itemsArray[$i]);
	
	if($where!=''){
		$where .= ',';
	}
	$where .= $itemArray[0];

	//executeSql("UPDATE contract_personal SET termination_number=".$itemArray[6]." , termination_text='".$itemArray[7]."' WHERE id=".$itemArray[0]);

	if(!is_numeric($itemArray[7])){
		$itemArray[7]=0;
	}
	if(!is_numeric($itemArray[9])){
		$itemArray[9]=0;
	}
	if(!is_numeric($itemArray[10])){
		$itemArray[10]=0;
	}
	if(!is_numeric($itemArray[11])){
		$itemArray[11]=0;
	}
	if(!is_numeric($itemArray[12])){
		$itemArray[12]=0;
	}
	if(!is_numeric($itemArray[13])){
		$itemArray[13]=0;
	}
	if(!is_numeric($itemArray[14])){
		$itemArray[14]=0;
	}
	executeSql("UPDATE contract_personal SET 
				termination_number=".$itemArray[7].", 
				termination_text='".$itemArray[8]."',
				termination1_number=".$itemArray[9].",
				termination2_number=".$itemArray[10].",
				termination3_number=".$itemArray[11].",
				termination4_number=".$itemArray[12].",
				termination5_number=".$itemArray[13].",
				termination6_number=".$itemArray[14].", 
				termination7_number=".$itemArray[15]." 
				WHERE id=".$itemArray[0]);

	$date_end_Array = explode('/', $itemArray[5]);
	$date_end = $date_end_Array[2].'-'.$date_end_Array[1].'-'.$date_end_Array[0];

	executeSql("UPDATE contract_process SET date_end='".$date_end."' WHERE contract_personal_id=".$itemArray[0]);

		/*executeSql("UPDATE contract_personal SET 
				termination_number=".$itemArray[6].", 
				termination_text='".$itemArray[7]."',
				termination1_number=".$itemArray[8].", 
				termination1_text='".$itemArray[9]."',
				termination2_number=".$itemArray[10].", 
				termination2_text='".$itemArray[11]."',
				termination3_number=".$itemArray[12].", 
				termination3_text='".$itemArray[13]."',
				termination4_number=".$itemArray[14].", 
				termination4_text='".$itemArray[15]."',
				termination5_number=".$itemArray[16].", 
				termination5_text='".$itemArray[17]."',
				termination6_number=".$itemArray[18].", 
				termination6_text='".$itemArray[19]."' 
				WHERE id=".$itemArray[0]);*/
}

$contract_process = executeSelect("SELECT cpr.*, cp.personal_id
									FROM contract_process cpr
									LEFT JOIN contract_personal cp ON cp.id=cpr.contract_personal_id
									WHERE cp.id IN ($where)");
									
//executeSql("DELETE FROM termination_personal WHERE termination_id=$lastId AND personal_id=".$contract_process[$k]['personal_id']);
executeSql("UPDATE contract SET termination_id=".$lastId." WHERE id=".$id);	

//executeSql("DELETE FROM termination_personal WHERE termination_id=$lastId");
for($k=0;$k<count($contract_process);$k++){

	executeSql("DELETE FROM termination_personal WHERE termination_id=$lastId AND personal_id=".$contract_process[$k]['personal_id']);
	$termination_personal_id = executeSql("INSERT INTO termination_personal(termination_id, personal_id) VALUES($lastId, ".$contract_process[$k]['personal_id'].")");

	//executeSql("UPDATE contract_process SET termination_personal_id=".$termination_personal_id.", date_end='$dateEnd', state='PREFINIQUITADO' WHERE id=".$contract_process[$k]['id']);
	executeSql("UPDATE contract_process SET termination_personal_id=".$termination_personal_id.", state='PREFINIQUITADO' WHERE id=".$contract_process[$k]['id']);
	
	executeSql("UPDATE personal SET state='PREFINIQUITADO' WHERE id=".$contract_process[$k]['personal_id']);

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

							t.format_id AS format,
							t.format_advice_id AS format_advice_id,
							cpr.id AS terminationProcessID,

							CONCAT(EXTRACT(DAY FROM cpr.date_start),' de ',
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
							END) END) END) END) END) END) END) END) END) END) END),' de ',
							EXTRACT(YEAR FROM cpr.date_start)) AS inicio_faena,

							CONCAT(EXTRACT(DAY FROM cpr.date_end),' de ',
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
							END) END) END) END) END) END) END) END) END) END) END),' de ',
							EXTRACT(YEAR FROM cpr.date_end)) AS fin_faena,

							termination_number,
							termination_text,
							termination1_number,
							termination1_text,
							termination2_number,
							termination2_text,
							termination3_number,
							termination3_text,
							termination4_number,
							termination4_text,
							termination5_number,
							termination5_text,
							termination6_number,
							termination6_text,
							termination7_number,
							termination7_text

							FROM personal p
							LEFT JOIN contract_personal cp ON cp.personal_id=p.id
							LEFT JOIN contract_process cpr ON cpr.contract_personal_id=cp.id
							LEFT JOIN contract c ON c.id=cp.contract_id
							LEFT JOIN commune cm ON cm.id=p.commune_id
							LEFT JOIN afp a ON a.id=p.afp_id
							LEFT JOIN health_system h ON h.id=p.health_system_id
							LEFT JOIN enterprise e1 ON e1.id=c.enterprise1_id
							LEFT JOIN enterprise e2 ON e2.id=c.enterprise2_id

							LEFT JOIN termination_personal tp ON tp.personal_id=p.id
							LEFT JOIN termination t ON t.id=tp.termination_id

							WHERE cpr.id=".$contract_process[$k]['id']." ORDER BY p.id DESC");

	//Carga y edición de documento
	for($j=0;$j<count($array);$j++){
		
		//Temporal, se eliminan todo el historial de contrato al realmacenar los datos
		executeSql("DELETE FROM termination_doc WHERE termination_process_id=".$array[$j]['terminationProcessID']);

		$arrayDoc = executeSelect("SELECT fr.*, f.title, f.footer1, f.footer2, f.type, f.firm_ok, f.firm_url,f.font_size
								FROM format_row fr
								LEFT JOIN format f ON f.id=fr.format_id
								WHERE f.id=".$array[$j]['format']);


		$footer1 = str_replace("[C.I. TRABAJADOR]",$array[$j]['rut'],$arrayDoc[0]['footer1']);
		$footer2 = str_replace("[RAZÓN SOCIAL EMPLEADOR]",$array[$j]['nombreEmpleador'],$arrayDoc[0]['footer2']);
		$footer2 = str_replace("[RUT EMPLEADOR]",$array[$j]['rutEmpleador'],$footer2);
		$footer2 = str_replace("[NOMBRE REPRESENTANTE LEGAL]",$array[$j]['nombreRepresentante'],$footer2);
		$footer2 = str_replace("[RUT REPRESENTANTE LEGAL]",$array[$j]['rutRepresentante'],$footer2);
		
		$lastDocId=executeSql("INSERT INTO termination_doc(termination_personal_id,format_type_id,title,footer1,footer2, termination_process_id,firm_ok,firm_url,font_size) VALUES(".$termination_personal_id.",".$arrayDoc[0]['type'].",'".$arrayDoc[0]['title']."','".$footer1."','".$footer2."',".$array[$j]['terminationProcessID'].",".$arrayDoc[0]['firm_ok'].",'".$arrayDoc[0]['firm_url']."',".$arrayDoc[0]['font_size'].")");

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
					
					$arrayDoc[$i]['text'] = str_replace("[Finiquito]", $array[$j]['termination_number'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[Finiquito Letras]", $array[$j]['termination_text'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[Feriado Proporcional]", $array[$j]['termination1_number'], $arrayDoc[$i]['text']);
					//$arrayDoc[$i]['text'] = str_replace("[Feriado Proporcional Letras]", $array[$j]['termination1_text'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[Feriado Anual]", $array[$j]['termination2_number'], $arrayDoc[$i]['text']);
					//$arrayDoc[$i]['text'] = str_replace("[Feriado Anual Letras]", $array[$j]['termination2_text'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[Indemnización Años Servicio]", $array[$j]['termination3_number'], $arrayDoc[$i]['text']);
					//$arrayDoc[$i]['text'] = str_replace("[Indemnización Años Servicio Letras]", $array[$j]['termination3_text'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[Indemnización Voluntaria]", $array[$j]['termination4_number'], $arrayDoc[$i]['text']);
					//$arrayDoc[$i]['text'] = str_replace("[Indemnización Voluntaria Letras]", $array[$j]['termination4_text'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[Seguro Cesantía]", $array[$j]['termination5_number'], $arrayDoc[$i]['text']);
					//$arrayDoc[$i]['text'] = str_replace("[Seguro Cesantía]", $array[$j]['termination5_text'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[Descuentos]", $array[$j]['termination6_number'], $arrayDoc[$i]['text']);
					//$arrayDoc[$i]['text'] = str_replace("[Descuentos Letras]", $array[$j]['termination6_text'], $arrayDoc[$i]['text']);
					$arrayDoc[$i]['text'] = str_replace("[Bono]", $array[$j]['termination7_number'], $arrayDoc[$i]['text']);
					//$arrayDoc[$i]['text'] = str_replace("[Bono Letras]", $array[$j]['termination7_text'], $arrayDoc[$i]['text']);
			$arrayDoc[$i]['rut'] = $array[$j]['rut'];

			executeSql("INSERT INTO termination_doc_row(number,text,termination_doc_id) VALUES(".$arrayDoc[$i]['number'].",'".$arrayDoc[$i]['text']."',".$lastDocId.")");

		}
		

		//Temporal, se eliminan todo el historial de contrato al realmacenar los datos
		executeSql("DELETE FROM advice_doc WHERE termination_personal_id=".$termination_personal_id);

		$arrayDocAdvice = executeSelect("SELECT fr.*, f.title, f.footer1, f.footer2, f.type, f.firm_ok, f.firm_url, 							f.font_size
								FROM format_row fr
								LEFT JOIN format f ON f.id=fr.format_id
								WHERE f.id=".$array[$j]['format_advice_id']);

		$footer1 = str_replace("[C.I. TRABAJADOR]",$array[$j]['rut'],$arrayDocAdvice[0]['footer1']);
		$footer2 = str_replace("[RAZÓN SOCIAL EMPLEADOR]",$array[$j]['nombreEmpleador'],$arrayDocAdvice[0]['footer2']);
		$footer2 = str_replace("[RUT EMPLEADOR]",$array[$j]['rutEmpleador'],$footer2);
		$footer2 = str_replace("[NOMBRE REPRESENTANTE LEGAL]",$array[$j]['nombreRepresentante'],$footer2);
		$footer2 = str_replace("[RUT REPRESENTANTE LEGAL]",$array[$j]['rutRepresentante'],$footer2);
		
		$lastDocAdviceId=executeSql("INSERT INTO advice_doc(termination_personal_id,format_type_id,title,footer1,footer2,firm_ok,firm_url,font_size) VALUES(".$termination_personal_id.",".$arrayDocAdvice[0]['type'].",'".$arrayDocAdvice[0]['title']."','".$footer1."','".$footer2."',".$arrayDocAdvice[0]['firm_ok'].",'".$arrayDocAdvice[0]['firm_url']."',".$arrayDocAdvice[0]['font_size'].")");

		for($i=0;$i<count($arrayDocAdvice);$i++){


			$arrayDocAdvice[$i]['text'] = str_replace("[Comuna]", $array[$j]['Comuna'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[Ciudad]", $array[$j]['Comuna'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[día]", $array[$j]['dd'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[mes]", $array[$j]['mm'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[año]", $array[$j]['aaaa'], $arrayDocAdvice[$i]['text']);

			$arrayDocAdvice[$i]['text'] = str_replace("[fin_día]", $array[$j]['end_dd'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[fin_mes]", $array[$j]['end_mm'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[fin_año]", $array[$j]['end_aaaa'], $arrayDocAdvice[$i]['text']);

			$arrayDocAdvice[$i]['text'] = str_replace("[Nombre Trabajador]", $array[$j]['nombreCompleto'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[C.I. Trabajador]", $array[$j]['rut'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[Estado Civil Trabajador]", $array[$j]['estado_civil'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[Domicilio del Trabajador]", $array[$j]['domicilio'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[Nacimiento día]", $array[$j]['nac_dd'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[Nacimiento mes]", $array[$j]['nac_mm'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[Nacimiento año]", $array[$j]['nac_aaaa'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[AFP]", $array[$j]['afp'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[Sistema Salud]", $array[$j]['health_system'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[Sueldo Base]", $array[$j]['salary_number'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[Sueldo Base en palabras]", $array[$j]['salary_text'], $arrayDocAdvice[$i]['text']);

			$arrayDocAdvice[$i]['text'] = str_replace("[Nombre Cliente]", $array[$j]['nombreCliente'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[Dirección Cliente]", $array[$j]['direccionCliente'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[Ciudad Cliente]", $array[$j]['ciudadCliente'], $arrayDocAdvice[$i]['text']);


			$arrayDocAdvice[$i]['text'] = str_replace("[Razón Social Empleador]", $array[$j]['nombreEmpleador'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[RUT Empleador]", $array[$j]['rutEmpleador'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[Domicilio Empleador]", $array[$j]['direccionEmpleador'], $arrayDocAdvice[$i]['text']);
			
			$arrayDocAdvice[$i]['text'] = str_replace("[Nombre Representante Legal]", $array[$j]['nombreRepresentante'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[RUT Representante Legal]", $array[$j]['rutRepresentante'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[Domicilio Representante Legal]", $array[$j]['direccionRepresentante'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[Comuna Representante Legal]", $array[$j]['comunaRepresentante'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[Ciudad Representante Legal]", $array[$j]['ciudadRepresentante'], $arrayDocAdvice[$i]['text']);

			$arrayDocAdvice[$i]['text'] = str_replace("[Inicio Faena]", $array[$j]['inicio_faena'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[Fin Faena]", $array[$j]['fin_faena'], $arrayDocAdvice[$i]['text']);

			$arrayDocAdvice[$i]['text'] = str_replace("[Finiquito]", $array[$j]['termination_number'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[Finiquito Letras]", $array[$j]['termination_text'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[Feriado Proporcional]", $array[$j]['termination1_number'], $arrayDocAdvice[$i]['text']);
			//$arrayDocAdvice[$i]['text'] = str_replace("[Feriado Proporcional Letras]", $array[$j]['termination1_text'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[Feriado Anual]", $array[$j]['termination2_number'], $arrayDocAdvice[$i]['text']);
			//$arrayDocAdvice[$i]['text'] = str_replace("[Feriado Anual Letras]", $array[$j]['termination2_text'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[Indemnización Años Servicio]", $array[$j]['termination3_number'], $arrayDocAdvice[$i]['text']);
			//$arrayDocAdvice[$i]['text'] = str_replace("[Indemnización Años Servicio Letras]", $array[$j]['termination3_text'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[Indemnización Voluntaria]", $array[$j]['termination4_number'], $arrayDocAdvice[$i]['text']);
			//$arrayDocAdvice[$i]['text'] = str_replace("[Indemnización Voluntaria Letras]", $array[$j]['termination4_text'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[Seguro Cesantía]", $array[$j]['termination5_number'], $arrayDocAdvice[$i]['text']);
			//$arrayDocAdvice[$i]['text'] = str_replace("[Seguro Cesantía]", $array[$j]['termination5_text'], $arrayDocAdvice[$i]['text']);
			$arrayDocAdvice[$i]['text'] = str_replace("[Descuentos]", $array[$j]['termination6_number'], $arrayDocAdvice[$i]['text']);
			//$arrayDocAdvice[$i]['text'] = str_replace("[Descuentos Letras]", $array[$j]['termination6_text'], $arrayDocAdvice[$i]['text']);

			$arrayDocAdvice[$i]['rut'] = $array[$j]['rut'];

			executeSql("INSERT INTO advice_doc_row(number,text,advice_doc_id) VALUES(".$arrayDocAdvice[$i]['number'].",'".$arrayDocAdvice[$i]['text']."',".$lastDocAdviceId.")");

		}
	}
	//$array[$j]["document"] = $arrayDoc;
}
echo $lastId;



?>