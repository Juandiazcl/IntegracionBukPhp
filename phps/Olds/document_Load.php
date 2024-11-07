<?php
header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

$id = $_POST['id'];
$type = $_POST['type'];
$by = $_POST['by'];

if($by=='process'){
	//Datos personales y contratación
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
	if($type=='contract'){
		//Antiguo, tomaba la información del documento según el diseño		
		/*$arrayDoc = executeSelect("SELECT fr.*, f.title, f.footer1, f.footer2
								FROM format_row fr
								LEFT JOIN format f ON f.id=fr.format_id
								WHERE f.id=".$array[0]['format']);*/
		//Tipo contrato=1
		$arrayDoc = executeSelect("SELECT dr.*, d.title, d.footer1, d.footer2
								FROM contract_doc_row dr
								LEFT JOIN contract_doc d ON d.id=dr.contract_doc_id
								WHERE d.contract_process_id=".$array[0]['contractProcessID']." AND d.format_type_id=1");

		for($i=0;$i<count($arrayDoc);$i++){
			$arrayDoc[$i]['text'] = str_replace("[rut]", $array[0]['rut'], $arrayDoc[$i]['text']);
			$arrayDoc[$i]['text'] = str_replace("[nombre]", $array[0]['nombre'], $arrayDoc[$i]['text']);
			$arrayDoc[$i]['text'] = str_replace("[dd]", $array[0]['dd'], $arrayDoc[$i]['text']);
			$arrayDoc[$i]['text'] = str_replace("[mm]", $array[0]['mm'], $arrayDoc[$i]['text']);
			$arrayDoc[$i]['text'] = str_replace("[aaaa]", $array[0]['aaaa'], $arrayDoc[$i]['text']);
			$arrayDoc[$i]['text'] = str_replace("[estado_civil]", $array[0]['estado_civil'], $arrayDoc[$i]['text']);
			$arrayDoc[$i]['text'] = str_replace("[domicilio]", $array[0]['domicilio'], $arrayDoc[$i]['text']);
			$arrayDoc[$i]['text'] = str_replace("[comuna]", $array[0]['comuna'], $arrayDoc[$i]['text']);
			$arrayDoc[$i]['text'] = str_replace("[nac_dd]", $array[0]['nac_dd'], $arrayDoc[$i]['text']);
			$arrayDoc[$i]['text'] = str_replace("[nac_mm]", $array[0]['nac_mm'], $arrayDoc[$i]['text']);
			$arrayDoc[$i]['text'] = str_replace("[nac_aaaa]", $array[0]['nac_aaaa'], $arrayDoc[$i]['text']);
			$arrayDoc[$i]['text'] = str_replace("[afp]", $array[0]['afp'], $arrayDoc[$i]['text']);
			$arrayDoc[$i]['rut'] = $array[0]['rut'];
		}

		echo json_encode($arrayDoc);

	}elseif($type=='process'){
		$array = executeSelect("SELECT p.rut, p.name, p.lastname1, p.lastname2, cpr.date_start, cpr.date_end, cpr.id, cpr.state
								FROM personal p
								LEFT JOIN contract_personal cp ON cp.personal_id=p.id
								LEFT JOIN contract_process cpr ON cpr.contract_personal_id=cp.id
								WHERE cpr.id=$id");
	}

}elseif($by=='contract'){
	//Datos personales y contratación
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

							c.format_id AS format

							FROM personal p
							LEFT JOIN contract_personal cp ON cp.personal_id=p.id
							LEFT JOIN contract_process cpr ON cpr.contract_personal_id=cp.id
							LEFT JOIN contract c ON c.id=cp.contract_id
							LEFT JOIN commune cm ON cm.id=p.commune_id
							LEFT JOIN afp a ON a.id=p.afp_id
							WHERE c.id=$id ORDER BY p.id DESC");


	//Carga y edición de documento
	for($j=0;$j<count($array);$j++){
		$arrayDoc = executeSelect("SELECT fr.*, f.title, f.footer1, f.footer2
								FROM format_row fr
								LEFT JOIN format f ON f.id=fr.format_id
								WHERE f.id=".$array[$j]['format']);

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
		}
		$array[$j]["document"] = $arrayDoc;
	}

	echo json_encode($array);

}

/*if(count($array)>0){
	echo json_encode($array);
}else{
	echo 0;
}*/

?>