<?php

include("../connection/connection.php");
set_time_limit(40000);

if($_POST['type']=='one'){
	$campo = $_POST['plant'];
	$campoBuk = $_POST['plantNvo'];
	$trabajador = $_POST['job'];
	$periodo = $_POST['year'];

	$fechaFor=strtotime($periodo);
	$mes=date("n",$fechaFor);
	$year=date("Y",$fechaFor);
	$filter='';	
	//  echo "Parametros: ";
	//  echo $campoBuk;
	// echo "   ";
	// echo $year;
	// echo "   ";
	// echo $mes;
	// echo "   ";

if ($trabajador!=0){
	$filter="and t.rut_per=".$trabajador."";
}
//extracionAusencias(2022, $mes, $trabajador);

// Consulta base carga 1.0
// $sql="SELECT pe.cc1_per, pe.ficha_per+'-'+pe.dv_per, FORMAT(T.fechatj,'yyyy-mm-dd') AS fechatjf, T.det_trato, 100, t.codigo_labor_buk, T.LugarBuk, t.UnidadBuk, 1, T.rendtj, 0, 0, tr.unidadtrt, tr.desctrt, lab.labor2, t.cc1trt, T.val1trt, t.cattrt, t.codtrt, t.cc2, t.cc3 AS cc3, t.cc4 AS cc4, PE.id_buk, PE.sbase_per, trim(val(t.cc2))+t.cc3+t.cc4, trim(val(t.cc1trt))+trim(t.cattrt)+trim(t.codtrt)
// FROM ((TARJAS1 AS T LEFT JOIN PERSONAL AS PE ON PE.rut_per=T.rut_per) 
// LEFT JOIN cc22 AS lab ON (lab.cc4=t.cc4) AND (lab.cc3=t.cc3) AND (lab.cc2=t.cc2)) 
// LEFT JOIN tratos11 AS tr ON (tr.codtrt=t.codtrt) AND (tr.cattrt=t.cattrt) AND (tr.cc1trt=t.cc1trt)
// WHERE YEAR(T.fechatj)=2022 AND MONTH(T.fechatj)=".$mes." and t.cc1trt='".trim($campo)."' ".$filter."
// ORDER BY t.rut_per, T.fechatj";

//getIdEmpleadoAct($campoBuk);


$sql="SELECT FORMAT(T.fechatj,'yyyy-mm-dd') AS fechatjf, t.rendtj, PE.id_buk2, t.idTar, t.valtj, t.det_trato, t.jornadatj, t.hhtj, t.idLug
FROM (TARJASBUK2 AS t 
LEFT JOIN PERSONAL3 AS PE ON PE.rut_per=t.rut_per) 
WHERE YEAR(T.fechatj)=".$year." AND MONTH(T.fechatj)=".$mes." and t.cc1trt='".trim($campoBuk)."' and t.idTar>2 ".$filter."
ORDER BY t.rut_per, T.fechatj";

//echo $sql;
$CarCampo=trim($campoBuk);
$arrayCen = executeSelect($sql);

// echo " < Total registros tratos filtrados : >";
// echo count($arrayCen);

if (count($arrayCen)==0){
	echo "No hay registro por mostrar";
	exit();
} else{
	
	$resLab=0;

	// Envio de tarjas del Periodo activo a Buk

	for($i=0;$i<count($arrayCen);$i++){
	if ($arrayCen[$i]['jornadatj']==1){   
		$porcentajeWork=100;
	} else if($arrayCen[$i]['jornadatj']==0.5){
		$porcentajeWork=50;
	}
	
	// echo "Prueba Id Labor";
	// echo $idLab;
	$json = array(
		'daily_base_floor' => true,  //Piso base Diario
		'discount_day_amount' => false,
	 'overwrite_existing' => true, //Que sobreescriba registros existentes
		'calculate_total_pay' => true,  // Calcular el pago total
		'sync_attendance' => 0,
		'day' => $arrayCen[$i]['fechatjf'],
		'employee_id' => $arrayCen[$i]['id_buk2'],
		'worklogs' => array(
			array(
				'piecework_execution_id' => $arrayCen[$i]['idTar'],
				'production' => round($arrayCen[$i]['rendtj'],1),
				'rate_value' => round($arrayCen[$i]['valtj'],1),
				'saved_in' => "porcentaje",
				'total_pay' => round(0,1),
				'work_type' =>$arrayCen[$i]['det_trato'],
				'worked_units' => $porcentajeWork
			)
		)
	);

	$data =  json_encode($json);
	$jsonGuideRaw = bsalePOST('https://remuneracionagricola.buk.cl/api/v1/chile/piecework/worklogs', 'H1z6Gc8abmzd217CmKzxbzTj', $data);	
	//echo $data;


	if ($arrayCen[$i]['hhtj']>0){
     $idLug=$arrayCen[$i]['idLug'];
	 //echo "Id Lugar para HHExt ";
	//  echo $idLug;
		   
		$sql4 = "SELECT cc.codigo FROM (CCBuk cc
		left join LugaresBuk as Lg on Lg.id_centro_costo=cc.id)
		WHERE Lg.id_lugar=$idLug";

		//echo $sql;		
		$arrayIdCC = executeSelect($sql4);

		$idCC=$arrayIdCC[0]['codigo'];
		// echo " Codigo CC  ";
		// echo $idCC;

		$idBuk2=$arrayCen[$i]['id_buk2'];
		$sql5 = "SELECT * FROM HHextras
		WHERE idTrabajador=$idBuk2 and aa=$year and mes=$mes";
		$arrayIdHH = executeSelect($sql5);


		// echo "Reg. HH Extras   ";
		// echo count($arrayIdHH);
		if (count($arrayIdHH)==0){

		$linkHH='https://remuneracionagricola.buk.cl/api/v1/chile/attendances/overtime';
					//$jsonGuideRawDel = bsaleDELETE($linkBorrado, 'H1z6Gc8abmzd217CmKzxbzTj'); 
					 $jsonHH = array(
					 'month' => $mes,
					 'year' => $year,
					 'hours' => $arrayCen[$i]['hhtj'],
					 'employee_id' => $arrayCen[$i]['id_buk2'],
					 'type_id' => 1,
					 'centro_costo' => $idCC,
					 );
					 $dataHH = json_encode($jsonHH);
					  $jsonGuideRaw = bsalePOST($linkHH, 'H1z6Gc8abmzd217CmKzxbzTj', $dataHH);
					 //echo $dataHH;
					
					$sql6 = "INSERT INTO HHextras(aa, mes, idtrabajador,
					ultfechaGuardado)
					VALUES(".$year.",
							".$mes.",
							".$idBuk2.",
							".$arrayCen[$i]['hhtj'].",
							'".$arrayCen[$i]['fechatjf']."')";
					executeSql($sql6);

		} else {
			$cantHoras=0.0;
			$cantHoras=$arrayIdHH[0]['cantidad']+ $arrayCen[$i]['hhtj'];
			$linkHH='https://remuneracionagricola.buk.cl/api/v1/chile/attendances/overtime';
			 $jsonHH = array(
			 'month' => $mes,
			 'year' => $year,
			 'hours' =>  $cantHoras,
			 'employee_id' => $arrayCen[$i]['id_buk2'],
			 'type_id' => 1,
			 'centro_costo' => $idCC,
			 );
			 $dataHH = json_encode($jsonHH);
			 $jsonGuideRaw = bsalePUT($linkHH, 'H1z6Gc8abmzd217CmKzxbzTj', $dataHH);
			 //echo $dataHH;

			 $sql6 = "UPDATE HHextras SET
			 aa=".$year.",
			 mes=".$mes.",
			 idTrabajador=".$idBuk2.",
			 cantidad=".$cantHoras.",
			   ultfechaGuardado='".$arrayCen[$i]['fechatjf']."' 
		   WHERE idTrabajador=$idBuk2. and aa=$year and mes=$mes";
	   //echo $sql6;		
	   executeSql($sql6);
		}
	}

	}

	//Envio de Inasistencias

	$sqlAus="SELECT FORMAT(T.fechatj,'yyyy-mm-dd') AS fechatjf, t.rendtj, PE.id_buk2, t.idTar, t.valtj, t.det_trato
	FROM (TARJASBUK2 AS t 
	LEFT JOIN PERSONAL3 AS PE ON PE.rut_per=t.rut_per) 
	WHERE YEAR(T.fechatj)=".$year." AND MONTH(T.fechatj)=".$mes." and t.cc1trt='".trim($campoBuk)."' and t.idTar=0 ".$filter."
	ORDER BY t.rut_per, T.fechatj";

	//echo $sqlAus;
	$arrayAus = executeSelect($sqlAus);

	if (count($arrayAus)>0){
		echo "  Tiene inasistencias.";
		for($i=0;$i<count($arrayAus);$i++){
		$linkBorrado='https://remuneracionagricola.buk.cl/api/v1/chile/absences/absence?employee_ids='.$arrayAus[$i]['id_buk2'].'&start_date='.$arrayAus[$i]['fechatjf'];
					//$jsonGuideRawDel = bsaleDELETE($linkBorrado, 'H1z6Gc8abmzd217CmKzxbzTj'); 
					 $jsonIna = array(
					 'start_date' => $arrayAus[$i]['fechatjf'],
					 'days_count' => 1,
					 'justification' => "injustificada",
					 'employee_id' => $arrayAus[$i]['id_buk2'],
					 'absence_type_id' => 8,
					 );
					 $dataIna = json_encode($jsonIna);
					 $jsonGuideRaw = bsalePOST('https://remuneracionagricola.buk.cl/api/v1/chile/absences/absence', 'H1z6Gc8abmzd217CmKzxbzTj', $dataIna);
					 //echo $dataIna;
		}
		//extraerVacaciones(2022, $mes, $trabajador);
		//envioAusencias($year, $mes, $trabajador);	
		} else {
		echo "  No Tiene inasistencia  ";
	}

	/// Envio de Licencias a Buk

	$sqlLic="SELECT FORMAT(T.fechatj,'yyyy-mm-dd') AS fechatjf, t.rendtj, PE.id_buk2, t.idTar, t.valtj, t.det_trato
	FROM (TARJASBUK2 AS t 
	LEFT JOIN PERSONAL3 AS PE ON PE.rut_per=t.rut_per) 
	WHERE YEAR(T.fechatj)=".$year." AND MONTH(T.fechatj)=".$mes." and t.cc1trt='".trim($campoBuk)."' and t.idTar=1 ".$filter."
	ORDER BY t.rut_per, T.fechatj";

	echo $sqlLic;
	$arrayLic = executeSelect($sqlLic);

	if (count($arrayLic)>0){
		echo "  Tiene licencias  ";

		for($i=0;$i<count($arrayLic);$i++){
		$linkBorrado='https://remuneracionagricola.buk.cl/api/v1/chile/absences/licence?employee_ids='.$arrayAus[$i]['id_buk2'].'&start_date='.$arrayAus[$i]['fechatjf'];
		// echo "Link Borrado:   ";
		// echo $linkBorrado;
		//$jsonGuideRawDel = bsaleDELETE($linkBorrado, 'H1z6Gc8abmzd217CmKzxbzTj');
		$json2L = array(
		'licence_type_id' => 5,
		'contribution_days' => 0,
		 'format' => "electronica",
		 'type' => "accidente_comun",
		 'start_date' =>$arrayAus[$i]['fechatjf'],
		 'days_count' =>1,
		 'day_percent' => "1",
		 'employee_id' => $arrayAus[$i]['id_buk2'],
		 );
		 $data2L =  json_encode($json2L);
		 $jsonGuideRaw = bsalePOST('https://remuneracionagricola.buk.cl/api/v1/chile/absences/licence', 'H1z6Gc8abmzd217CmKzxbzTj', $data2L);
		 //echo $data2L;
		}

		//extraerLicencias(2022, $mes, $trabajador);
		//envioLicencia($An, $trabajador);
	} else {
		//echo "  NO Tiene licencias  ";
	}
	
		/// Envio de Vacaciones a Buk

		// $sqlVac="SELECT FORMAT(T.fechatj,'yyyy-mm-dd') AS fechatjf, t.rendtj, PE.id_buk2, t.idTar, t.valtj, t.det_trato
		// FROM (TARJASBUK2 AS t 
		// LEFT JOIN PERSONAL3 AS PE ON PE.rut_per=t.rut_per) 
		// WHERE YEAR(T.fechatj)=".$year." AND MONTH(T.fechatj)=".$mes." and t.cc1trt='".trim($campoBuk)."' and t.idTar=2 ".$filter."
		// ORDER BY t.rut_per, T.fechatj";
	
		// echo $sqlVac;
		// //$arrayVac = executeSelect($sqlVac);
	
		// if (count($arrayVac)>0){
		// 	echo "  Tiene vacaciones registradas  ";
	
		// 	for($i=0;$i<count($arrayVac);$i++){
		// 		$linkBorrado='https://remuneracionagricola.buk.cl/api/v1/chile/vacations?employee_id='.$arrayVac[$i]['id_buk2'].'&start_date='.$arrayAus[$i]['fechatjf'].'&end_date='.$arrayAus[$i]['fechatjf'];
		// 		//$jsonGuideRawDel = bsaleDELETE($linkBorrado, 'H1z6Gc8abmzd217CmKzxbzTj'); 
		// 		$json2L = array(
		// 		'type' => "legales",
		// 		'start_date' => $arrayAus[$i]['fechatjf'],
		// 		'end_date' => $arrayAus[$i]['fechatjf'],
		// 		'percent_day' => 1,
		// 		'employee_id' => $arrayVac[$i]['id_buk2'],
		// 	   );
		// 		$data2L =  json_encode($json2L);												
		// 		$jsonGuideRaw = bsalePOST('https://remuneracionagricola.buk.cl/api/v1/chile/vacations', 'H1z6Gc8abmzd217CmKzxbzTj', $data2L);
		// 		//echo $data2L;
		// 	}
	
		// 	//extraerLicencias(2022, $mes, $trabajador);
		// 	//envioLicencia($An, $trabajador);
		// } else {
		// 	echo "  NO Tiene vacaciones  ";
		// }
	
	//envioTarjas($An, $mes, $campo, $trabajador);
}
} elseif ($_POST['type']=='periodo'){
	// Extrae de Buk los periodos registrados y los carga en la bd
	getPeriodos();
	$array = executeSelect("SELECT * FROM PeriodosBuk");
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}
}elseif ($_POST['type']=='worked'){

	$campoBuk = $_POST['plantNvo'];
	$campoBuk2 = $_POST['plantNvo2'];
	$idArea = $_POST['idArea'];
	$idRole = $_POST['idRole'];
	$idLeader = $_POST['idLeader'];
	$campo = $_POST['plant'];
	$trabajador = $_POST['job'];
	$periodo = $_POST['year'];
	$fechaFor=strtotime($periodo);
	$mes=date("n",$fechaFor);
	$year=date("Y",$fechaFor);
	$filter='';	
	//  echo "Parametros: ";
	//  echo $campoBuk;
	// echo "   ";
	// echo $year;
	// echo "   ";
	// echo $mes;
	// echo "   ";

	if ($trabajador!=0){
		$filter="and rut_per=".$trabajador."";
	}
echo $campo;
//getIdEmpleadoAct($campoBuk);	
$sqlTra="SELECT distinct t.rut_per, pe.*
FROM (TARJAS1 AS t 
LEFT JOIN PERSONAL AS PE ON PE.rut_per=t.rut_per) 
WHERE YEAR(T.fechatj)=".$year." AND MONTH(T.fechatj)=".$mes." and t.cc1trt='".trim($campo)."' ".$filter."
";
$sqlTra2="SELECT *
FROM PERSONAL  
WHERE cc1_per='".trim($campo)."' ".$filter."
";
//echo $sqlTra2;
$arrayTra = executeSelect($sqlTra2);



// envio del trabajador
	if (count($arrayTra)>0){
	echo "  Hay empleados para enviar.  ";
		for($i=0;$i<count($arrayTra);$i++){

			$rutComp=strval(round($arrayTra[$i]['rut_per']))."-".$arrayTra[$i]['dv_per'];
			$rutEmpresa=$arrayTra[$i]['emp_per'];
			// echo "    ";
			// echo $rutComp;
			// echo "    ";
			// echo $array3[$i]['Nom_per'];
			// echo "    ";
	   
	   
			  $json3 = array(
				  'first_name' => $arrayTra[$i]['Nom_per'],
				  'surname' => $arrayTra[$i]['Apepat_per'],
				  'second_surname' => $arrayTra[$i]['Apemat_per'],
				  'rut' => $rutComp,
				  'code_sheet' => '-',
				  'nationality' =>$arrayTra[$i]['cod_nac'],
				  'gender' => $arrayTra[$i]['sexo_per'],
				  'civil_status' => 'Soltero',
				  'birthday' => $arrayTra[$i]['fecnac_per'],
				  'email' => $arrayTra[$i]['email'],
				  'personal_email' => $arrayTra[$i]['email'],
				  'location_id' => 143,
				  'address' => $arrayTra[$i]['Direc_per'],
				  'city' => $arrayTra[$i]['comuna_per'],
				  'payment_method' => 'Transferencia Bancaria',
				  'bank' => $arrayTra[$i]['cta_banco_buk'],
				  'account_type' => $arrayTra[$i]['cta_tipo_buk'],
				  'account_number' => $arrayTra[$i]['cta_numero_buk'],
				  'start_date' => $arrayTra[$i]['fecing_per'],
				  'private_role' => false,
				  'active' => true
				  );
		   
			 $data3 = json_encode(utf8ize($json3)); 
			 $jsonGuideRaw = bsalePOST('https://remuneracionagricola.buk.cl/api/v1/chile/employees', 'H1z6Gc8abmzd217CmKzxbzTj', $data3);
		     //echo $data3;
			

		   $jsonGuide = json_decode(bsaleGET('https://remuneracionagricola.buk.cl/api/v1/chile/employees?rut='.$rutComp, 'H1z6Gc8abmzd217CmKzxbzTj'), true);

			//echo "  ";

			//var_dump($jsonGuide);
			//echo "Total registros: ";
			//echo count($jsonGuide['data']);
			$idBukTrabajador=0;
			  //echo " ID: ";
			  //echo $jsonGuide['data'][0]['id'];
			$rutEmpleado=$arrayTra[$i]['rut_per'];  
			$idBukTrabajador=$jsonGuide['data'][0]['id'];
			//Docs Empleados
			    envioEmpleadoE2($idBukTrabajador, $rutEmpleado);
			 // Jobs Empleados
				envioEmpleadoE1($idBukTrabajador, $rutEmpresa, $rutEmpleado, $idArea, $idRole, $idLeader, $campoBuk2);
			if ($idBukTrabajador!=null){
				 
				 //envioEmpleadoE2($idBukTrabajador);
				 
				} else {
				// /getIdEmpleadoAct($campoBuk); 
				// $sqlExiste="select rut_per from personal where rut_per=".$trabajador;
				// $arrayExiste = executeSelect($sqlExiste);
				//echo "< ID no existe, consultar con Administrador >:";
				//echo $arrayExiste[0]['id_buk2'];
				// envioEmpleadoE2($arrayExiste[0]['id_buk2']);
				// envioEmpleadoE1($arrayExiste[0]['id_buk2']);
			}
	

		}
	} else {
		echo "  NO hay empleados para enviar. ";
	}
} elseif ($_POST['type']=='oldTally'){
	$mes=$_POST['mes'];
	$an=$_POST['an'];
	$campoBuk = $_POST['plantNvo4'];
	$campo = $_POST['plantB'];
	$trabajador = $_POST['job'];
	 //echo "Antiguo  ";
	// echo $mes;
	// echo $an;
	//  echo $campo;
	// echo "Nuevo  ";
	//  echo $campoBuk;
	$filter='';

	if ($trabajador!=0){
		$filter="and t.rut_per=".$trabajador."";
	}

	$sqlUpdate="UPDATE tarjas1 SET det_trato = 'dia'
	 WHERE cattrt=0 and cc1trt='".trim($campo)."'";
	executeSql($sqlUpdate);
	//echo $sqlUpdate;
	$sqlUpdate="UPDATE tarjas1 SET det_trato = 'trato'
	 WHERE cattrt<>0 and cc1trt='".trim($campo)."'";
	 executeSql("$sqlUpdate");
	 //echo $sqlUpdate;

	$borrarRegistros="DELETE FROM TARJASBUK2 WHERE YEAR(T.fechatj)=".$an." AND MONTH(T.fechatj)=".$mes." and t.cc1trt='".trim($campoBuk)."' ".$filter;
	//executeSql($borrarRegistros);

	$sql="SELECT FORMAT(T.fechatj,'yyyy-mm-dd') AS fechatjf, t.rendtj, PE.id_buk2, t.valtj, t.det_trato, t.jornadatj, t.hhtj, t.cc3, t.cc4, t.codtj, t.rut_per, t.fichatj, t.nomtrabtj, t.cc1trt, t.cc1tj
	FROM (TARJAS1 AS t 
	LEFT JOIN PERSONAL AS PE ON PE.rut_per=t.rut_per) 
	WHERE YEAR(T.fechatj)=".$an." AND MONTH(T.fechatj)=".$mes." and t.cc1trt='".trim($campo)."' ".$filter."
	ORDER BY t.rut_per, T.fechatj";

	//echo $sql;
	$CarCampo=trim($campoBuk);
	$arrayOld = executeSelect($sql);

	if(count($arrayOld)==0){
	echo 0;	
	exit();
	}else{
	//count($arrayOld)
	
	for($i=0;$i<count($arrayOld);$i++){
		if ($arrayOld[$i]['cc3']=='12' && $arrayOld[$i]['cc4']=='01'){
             $idTarOld=2;
			 $detalleTrato='ausencia';
		} elseif($arrayOld[$i]['cc3']=='12' && $arrayOld[$i]['cc4']=='02'){
			$idTarOld=1;
			$detalleTrato='ausencia';
		} elseif($arrayOld[$i]['cc3']=='12' && $arrayOld[$i]['cc4']=='03'){
			$idTarOld=0;
			$detalleTrato='ausencia';
		} else {
			$idTarOld=0;
			$detalleTrato=$arrayOld[$i]['det_trato'];
		}
		//echo $detalleTrato;
		$sql2="insert into TARJASBUK2 (cc1tj, cc1trt, fechatj, rendtj, idTar, valtj, det_trato, jornadatj, hhtj, codtj, rut_per, fichatj, nomtrabtj) values ('".$campoBuk."', '".$campoBuk."', '".$arrayOld[$i]['fechatjf']."', ".$arrayOld[$i]['rendtj'].", ".$idTarOld.", ".$arrayOld[$i]['valtj']
		.", '".$detalleTrato."', ".$arrayOld[$i]['jornadatj'].", ".$arrayOld[$i]['hhtj'].", ".$arrayOld[$i]['codtj'].", ".$arrayOld[$i]['rut_per'].", '".$arrayOld[$i]['fichatj']."', '".$arrayOld[$i]['nomtrabtj']."')";
		//echo $sql2;
		executeSql($sql2);
	}	
	
	echo 1;	
	}	
} elseif ($_POST['type']=='admTarifas'){
	$campoBuk = $_POST['plantNvo'];
	//echo "antes de tarifa";
	   getIdUnidades();
	   getIdProductos();
	   getIdLugares();
	   getIdLabores();
	   getIdtarifas();
	getIdtarifasNew($campoBuk);
	 echo 1;

}elseif ($_POST['type']=='envioCC'){
	$mes=$_POST['mes'];
	$an=$_POST['an'];
	$campoBuk = $_POST['plantNvo4'];
	//$campo = $_POST['plantB'];
	$trabajador = $_POST['job'];
	 //echo "Antiguo  ";
	// echo $mes;
	// echo $an;
	//  echo $campo;
	// echo "Nuevo  ";
	//  echo $campoBuk;
	$filter='';

	if ($trabajador!=0){
		$filter="and t.rut_per=".$trabajador."";
	}

//WHERE YEAR(fechatj)=2023 AND MONTH(fechatj)=5 and cc1trt='01' and idTar>2 ";
//WHERE YEAR(T.fechatj)=2023 AND MONTH(T.fechatj)=5 and t.cc1trt='01' and idTar>2 and t.rut_per=$arrayOld[$i]['cc3']8257475"

$sql="SELECT distinct rut_per
FROM TARJASBUK2 
WHERE YEAR(fechatj)=".$an." AND MONTH(fechatj)=".$mes." and idTar>2 and cc1trt='".trim($campoBuk)."' ".$filter."";

// echo $sql;
$arrayCC = executeSelect($sql);

for($i=0;$i<count($arrayCC);$i++){


$sql2="SELECT t.fechatj, t.idLug, cc.codigo as cod
FROM (((TARJASBUK2 AS t 
LEFT JOIN PERSONAL3 AS PE ON PE.rut_per=t.rut_per) 
LEFT JOIN LugaresBuk as Lg on lg.id_lugar=t.idLug)
LEFT JOIN CCBuk as cc on cc.id=Lg.id_centro_costo)
WHERE YEAR(T.fechatj)=".$an." AND MONTH(T.fechatj)=".$mes." and idTar>2 and t.cc1trt='".trim($campoBuk)."' and t.rut_per=".$arrayCC[$i]['rut_per']."";

$arrayTjCC = executeSelect($sql2);
// echo "BD 2";
// echo $sql2;

$TdiasTrabajados=count($arrayTjCC);

$sql3="SELECT distinct cc.codigo
FROM (((TARJASBUK2 AS t 
LEFT JOIN PERSONAL3 AS PE ON PE.rut_per=t.rut_per) 
LEFT JOIN LugaresBuk as Lg on lg.id_lugar=t.idLug)
LEFT JOIN CCBuk as cc on cc.id=Lg.id_centro_costo)
WHERE YEAR(T.fechatj)=2023 AND MONTH(T.fechatj)=5 and t.cc1trt='01' and idTar>2 and t.rut_per=".$arrayCC[$i]['rut_per']."";

$arrayListCC = executeSelect($sql3);
// echo "BD 3";
// echo $sql3;


$ListaPorcCC=array();
$ListaDiasCC=array();
for($j=0;$j<count($arrayListCC);$j++){
	$cantDiasCC=0;
for($k=0;$k<count($arrayTjCC);$k++){
	
 if($arrayTjCC[$k]['cod']==$arrayListCC[$j]['codigo']){
	$cantDiasCC=$cantDiasCC+1;
 }
 
}
$ListaDiasCC[$j]=$cantDiasCC;
 echo " Trabajador: ";
 echo $arrayCC[$i]['rut_per'];
 echo " CC ";
 echo $arrayListCC[$j]['codigo'];
 echo " cantidad de dias ";
 echo $ListaDiasCC[$j];
 $ListaPorcCC[$j]=($cantDiasCC*100)/$TdiasTrabajados;
 echo " Porcentaje CC ";
 echo round($ListaPorcCC[$j],2);
 echo " Total dias sin libres ";
 echo $TdiasTrabajados;
 echo "      ";

 $sql4="INSERT INTO CCXEmpleado (rutEmpleado, cc, cantDias, porcPeso) VALUES (".$arrayCC[$i]['rut_per'].", '".$arrayListCC[$j]['codigo']."', $ListaDiasCC[$j], round($ListaPorcCC[$j],2))";
echo $sql4;
}


}
}
 
	
exit();
 
// *** Extrae labores nuevas segun filtro pantalla ****	
function ExtraerLaboresNvas($An, $mes, $campo){
	$lab=0;
	$sqlNLab ="select distinct codigo_labor_buk as cLb from tarjas1 as Tb
	left join LaboresBuk as Lb on lb.code=Tb.codigo_labor_buk
	where YEAR(fechatj)=$An AND MONTH(fechatj)=$mes and cc1trt='".trim($campo)."' and lb.code is null";
	$arrayNl = executeSelect($sqlNLab);
	if (count($arrayNl)==0){
		echo " < No hay nuevas Labores > ";
		return $lab;
	}

  for($i=0;$i<count($arrayNl);$i++){
	executeSql("insert into LaboresBuk (code, id) values (".$arrayNl[$i]['cLb'].", 0)");
  }
  $lab=1;
  return $lab;

} 

// Recibo de Json x Get
function bsaleGET($url, $access_token){

    // Inicia cURL
    $session = curl_init($url);


    // Indica a cURL que retorne data
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false );
    // Configura cabeceras
    $headers = array(
        'auth_token: '.$access_token,
        'Accept: application/json',
        'Content-Type: application/json'
    );
    curl_setopt($session, CURLOPT_HTTPHEADER, $headers);

    // Ejecuta cURL
    $response = curl_exec($session);
    //$code = curl_getinfo($session, CURLINFO_HTTP_CODE);
 	if ($response === false) $response = curl_error($session);
 	
    // Cierra la sesión cURL
    curl_close($session);
	
    //Esto es sólo para poder visualizar lo que se está retornando
    return $response;
}
// Envio de Json x Post
function bsalePOST($url, $access_token, $data){

    // Inicia cURL
    $session = curl_init($url);


    // Indica a cURL que retorne data
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false );
    // Configura cabeceras
    $headers = array(
        'auth_token: '.$access_token,
        'Accept: application/json',
        'Content-Type: application/json'
    );
    curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
 	// Indica que se va ser una petición POST
    curl_setopt($session, CURLOPT_POST, true);

    // Agrega parámetros
    curl_setopt($session, CURLOPT_POSTFIELDS, $data);
    
    // Ejecuta cURL
    $response = curl_exec($session);

 	if ($response === false) $response = curl_error($session);
	
    // Cierra la sesión cURL
    curl_close($session);
	
    //Esto es sólo para poder visualizar lo que se está retornando
	echo $response;
    return $response;
}

// ************* For para sacar los valores de los tratos
// function obtenerTratos(){

// $sqlVal ="SELECT T.jornadatj, FORMAT(T.fechatj,'yyyy/mm/dd') as fechatjf , round(T.rut_per,0) as rut_perf, T.rendtj, round(tt.val1trt,0) as val1trtf, t.cc1trt, t.codtrt, t.cc3 as cc3, t.cc4 as cc4
// FROM TARJASBUK2 AS T
// LEFT JOIN TRATOS11 AS TT
// ON  TT.cc1trt=T.cc1trt and tt.codtrt=t.codtrt
// WHERE YEAR(T.fechatj)=2022 AND MONTH(T.fechatj)=07 ORDER BY T.fechatj";
//  $arrayV = executeSelect($sqlVal);
//  for($i=0;$i<count($arrayV);$i++){
// 	 	$varRut=$arrayV[$i]['rut_perf'];
// 	 	$varFec=$arrayV[$i]['fechatjf'];
// 	 	$varVal=$arrayV[$i]['val1trtf'];
// 	 	echo " --- Rut - ";
// 	 	echo $varRut;
// 	 	echo " ----- fecha - ";
// 	 	echo $varFec;
// 	 	echo " ----- CAT - ";
// 	 	echo $varVal;
// 	 	echo " ----- CONSULTA ---";
// 	 	echo "UPDATE TARJASBUK2 SET val1trt=$varVal WHERE rut_per=$varRut and fechatj=#".$varFec."#";
// 	 	echo " ----- ";
// 	 	executeSql("UPDATE TARJASBUK2 SET val1trt=$varVal WHERE rut_per=$varRut and fechatj=#".$varFec."#");	
//  }
//}


// ************* For para sacar licencias  count($arrayL) FORMAT(fechatj,'dd/MM/yyyy') as fechatjf
function extraerLicencias($An, $mes, $trabaja){  
	if ($trabaja!=0) {
		$filtroTrab=" and t.rut_per=$trabaja";
	   } else {
		$filtroTrab=" ";
	   }
   $sqlLic="select cc3, cc4, round(rut_per,0) as rut_perf, fechatj as fechatjf,  MONTH(FORMAT(fechatj,'dd/MM/yyyy')) as mes from tarjas1 where YEAR(fechatj)=$An and MONTH(fechatj)=$mes".$filtroTrab." and cc3='12' and cc4='02'order by rut_per, fechatj";
   $arrayL=executeSelect($sqlLic);
   $priFec=$arrayL[0]['fechatjf'];
   $rutTemp=$arrayL[0]['rut_perf'];
   $mesP=$arrayL[0]['mes'];
   echo $mesP;
   echo "-----------";
   $conDias=0;
   for($i=0;$i<count($arrayL);$i++){
         $conDias=$conDias+1;
  	 	if ($arrayL[$i]['rut_perf']!=$rutTemp){
 			$conDias=$conDias-1;
 	 	 	$varRut=$arrayL[$i-1]['rut_perf'];
 	 	 	$varFecF=$arrayL[$i-1]['fechatjf'];
 			echo " ----- CONSULTA ---";
 	 	    echo "UPDATE TARJAS1 SET fec_fLic=#".$varFecF."#, diaslic=$conDias WHERE rut_per=$varRut and fechatj=#".$priFec."#";
 	 	    echo " ----- ";
 	 	    executeSql("UPDATE TARJAS1 SET fec_fLic=#".$varFecF."#, diaslic=$conDias WHERE rut_per=$varRut and fechatj=#".$priFec."#");
		   		$priFec=$arrayL[$i]['fechatjf'];
 		$rutTemp=$arrayL[$i]['rut_perf'];
 		$conDias=0;
		//	 	echo " --- Rut - ";
 	 	// echo $varRut;
 	 	// echo " ----- fecha - ";
 	 	// echo $varFecF;
 	 	echo " ----- CAT - ";
// 	 	echo $varVal;
	 		
     }
  }
}


//*** Ingreso de API Licencia  ok
function envioLicencia($An){
// {
// 	"licence_type_id": 0,
// 	"contribution_days": 0,
// 	"format": "electronica",
// 	"type": "accidente_comun",
// 	"start_date": "2022-06-12",
// 	"days_count": 0,
// 	"day_percent": "1",
// 	"application_date": "2022-06-12",
// 	"justification": "string",
// 	"employee_id": 0     count($arrayL)
//   }

  $sqlLicJs="select t.cc3, t.cc4, round(t.rut_per,0) as rut_perf, t.fechatj as fechatjf, t.diasLic, t.fec_fLic, tt.id_buk from tarjas	1 as t
  LEFT JOIN PERSONAL AS TT
  ON  TT.rut_per=T.rut_per   
  where YEAR(t.fechatj)=$An and t.cc3='12' and t.cc4='02' and t.diasLic>0 order by t.rut_per, t.fechatj";
  $arrayLj=executeSelect($sqlLicJs);
     for($i=0;$i<count($arrayLj);$i++){
   	echo " ----- Rut";
 	echo $arrayLj[$i]['rut_perf'];    
   	$json2L = array(
  	'licence_type_id' => 1,
  	'contribution_days' => 0,
   	'format' => "electronica",
   	'type' => "accidente_comun",
   	'start_date' => $arrayLj[$i]['fechatjf'],
   	'days_count' => $arrayLj[$i]['diasLic'],
   	'day_percent' => "1",
 	'application_date' => $arrayLj[$i]['fechatjf'],
 	'justification' => "licencia valida",
   	'employee_id' => $arrayLj[$i]['id_buk2'],
   	);
   	$data2L =  json_encode($json2L);
   	$jsonGuideRaw = bsalePOST('https://remuneracionagricola.buk.cl/api/v1/chile/absences/licence', 'H1z6Gc8abmzd217CmKzxbzTj', $data2L);
   	echo $data2L;
   }
}


// ************* For para sacar vacaciones todo 2022 count($arrayL) FORMAT(fechatj,'dd/MM/yyyy') as fechatjf
 function extraerVacaciones($An, $mes, $trabaja){ 
	if ($trabaja!=0) {
		$filtroTrab=" and t.rut_per=$trabaja";
	   } else {
		$filtroTrab=" ";
	   }
    $sqlLic="select cc3, cc4, round(rut_per,0) as rut_perf, fechatj as fechatjf, diasLic, fec_fLic from tarjas1 where YEAR(fechatj)=$An and MONTH(fechatj)=$mes".$filtroTrab." and cc3='12' and cc4='01' order by rut_per, fechatj";
    $arrayL=executeSelect($sqlLic);
    $priFec=$arrayL[0]['fechatjf'];
    $rutTemp=$arrayL[0]['rut_perf'];
//    //$mesP=$arrayL[0]['mes'];
//    //echo $mesP;
//     echo "-----------";
     $conDias=0;
    for($i=0;$i<count($arrayL);$i++){
          $conDias=$conDias+1;
   	 	if ($arrayL[$i]['rut_perf']!=$rutTemp){
  			$conDias=$conDias-1;
  	 	 	$varRut=$arrayL[$i-1]['rut_perf'];
  	 	 	$varFecF=$arrayL[$i-1]['fechatjf'];
  			// echo " ----- CONSULTA ---";
  	 	    // echo "UPDATE TARJASBUK2 SET fec_fVac=#".$varFecF."#, diasVac=$conDias WHERE rut_per=$varRut and fechatj=#".$priFec."#";
  	 	    // echo " ----- ";
  	 	   executeSql("UPDATE TARJAS1 SET fec_fVac=#".$varFecF."#, diasVac=$conDias WHERE rut_per=$varRut and fechatj=#".$priFec."#");
  		$priFec=$arrayL[$i]['fechatjf'];
  		$rutTemp=$arrayL[$i]['rut_perf'];
  		$conDias=0;
		// echo " --- Rut - ";
 	 	// echo $varRut;
 	 	// echo " ----- fecha - ";
 	 	// echo $varFecF;
 	 	echo " ----- CAT - ";
        // echo $varVal;		
      }
   }
}


//*** Ingreso de API vacaciones Se cae, por parametrizacion y meses no cerrados
function envioVacaciones($An, $mes){
    // {
 	// "employee_id": 0,
 	// "type": "legales",
 	// "start_date": "2022-07-18",
 	// "end_date": "2022-07-18",
 	// "percent_day": 0
//   }
   $sqlVacJs="select t.cc3, t.cc4, t.rut_per, t.fechatj as fechatjf, t.fec_fVac, tt.id_buk2 from tarjas1 as t
   LEFT JOIN PERSONAL AS TT
   ON  TT.rut_per=T.rut_per  
   where YEAR(t.fechatj)=$An and MONTH(t.fechatj)=$mes and t.cc3='12' and t.cc4='01' and t.diasVac>0 order by t.rut_per, t.fechatj";
     $arrayLj=executeSelect($sqlVacJs);
       for($i=0;$i<count($arrayLj);$i++){
     	echo " ----- Rut";
 	  	echo $arrayLj[$i]['rut_per'];    
    	$json2L = array(
     	'type' => "legales",
     	'start_date' => $arrayLj[$i]['fechatjf'],
     	'end_date' => $arrayLj[$i]['fec_fVac'],
     	'day_percent' => "1",
     	'employee_id' => $arrayLj[$i]['id_buk2'],
    	);
     	$data2L =  json_encode($json2L);												
 	    $jsonGuideRaw = bsalePOST('https://remuneracionagricola.buk.cl/api/v1/chile/vacations', 'H1z6Gc8abmzd217CmKzxbzTj', $data2L);
     	echo $data2L;
     }
  }

function marcarEmpleadosNvos($an,$mes,$campo){
//*****  Marcar los empleados por ingresar a Buk x mes count($arrayLic)
$Lab=0;
  $sqlPerUsados="SELECT distinct rut_per
  FROM TARJAS1
  WHERE YEAR(fechatj)=$an and MONTH(fechatj)=$mes and cc1tj='".trim($campo)."'";

 	  //echo $sqlPerUsados;   
 $arrayLic = executeSelect($sqlPerUsados);
 if (count($arrayNl)==0){
	echo " < No hay nuevos Empleados > ";
	return $lab;
}
      for($i=0;$i<count($arrayLic);$i++){
     	$varRut=$arrayLic[$i]['rut_per'];
    	 echo " --- RUT - ";
    	 echo $varRut;
     	 echo " ------ ";
     	 echo " ----- CONSULTA ---";
     	 echo "UPDATE PERSONAL SET id_buk=1 WHERE rut_per=$varRut and (id_buk2 is null or id_buk2<1)";
       echo " ----- ";
   	executeSql("UPDATE PERSONAL SET id_buk=1 WHERE rut_per=$varRut and (id_buk2 is null or id_buk2<1)");	
 	}    
	$Lab=1;
	return $lab;
}



//***************** */ Envio por Post de Labores x Api a Labores de Buk   funcionando, todos enviados  count($array4)
 function envioLabores(){

// {
// 	"task": { 
// 	  "code": 0,
// 	  "description": "string",
// 	  "seventh_workday": true
// 	}
//   
// $sql4="SELECT * FROM cc2";
// $sql4="SELECT c1.CODBUK as codBuk, c1.labor2 as desLab
// FROM (cc22 AS c1 LEFT JOIN cc21 AS c2 ON (c2.cc3=c1.cc3) AND (c2.cc2=c1.cc2)) LEFT JOIN cc2 AS c3 ON c3.cc2=c1.cc2";
$sql4="SELECT c1.CODBUK as codBuk, c1.labor2 as desLab
FROM cc22 AS c1 
left join LaboresBuk as Lb on Lb.code=c1.codBuk
where lb.id=0";
 $array4 = executeSelect($sql4);
    for($i=0;$i<2;$i++){
   	$json4 = array(
   		'code' => $array4[$i]['codBuk'],
   		'description' => $array4[$i]['desLab'],
   		'seventh_workday' => true,
   	);
  	 $data4 =  json_encode($json4);
  	 $jsonGuideRaw = bsalePOST('https://remuneracionagricola.buk.cl/api/v1/chile/piecework/tasks', 'H1z6Gc8abmzd217CmKzxbzTj', $data4);
 	 echo $data4;
   }

   
}

//***************** */ Envio por Post de Tratos a Api a Labores de Buk   funcionando, todos enviados  count($array4)
 function envioTratos(){

// {
// 	"task": { 
// 	  "code": 0,
// 	  "description": "string",
// 	  "seventh_workday": true
// 	}
//   
// $sql4="SELECT * FROM cc2";
// $sql4="SELECT c1.CODBUK as codBuk, c1.desctrt as desTrt
// FROM (tratos11 AS c1 LEFT JOIN tratos1 AS c2 ON (c2.cattrt=c1.cattrt) AND (c2.cc1trt=c1.cc1trt)) LEFT JOIN t0010 AS c3 ON c3.pl_codigo=val(c1.cc1trt)";
$sql4="SELECT c1.CODBUK as codBuk, c1.desctrt as desTrt
FROM tratos11 AS c1 LEFT JOIN LaboresBuk as Lb on Lb.code=c1.codBuk
where lb.id=0";
 $array4 = executeSelect($sql4);
    for($i=0;$i<count($array4);$i++){
   	$json4 = array(
   		'code' => $array4[$i]['codBuk'],
   		'description' => $array4[$i]['desTrt'],
   		'seventh_workday' => true,
   	);
  	 $data4 =  json_encode($json4);
  	 $jsonGuideRaw = bsalePOST('https://remuneracionagricola.buk.cl/api/v1/chile/piecework/tasks', 'H1z6Gc8abmzd217CmKzxbzTj', $data4);
 	 echo $data4;
   }
}

// function licenciasEmpleados(){
//  *****  Marcar los empleados con licencias en 2022 buk=1 
//   $sqlPerUsadosLic="SELECT distinct rut_per
// 	  FROM TARJASBUK2
// 	  WHERE YEAR(fechatj)=2022 and cc3='12' and cc4='02'";
// 	  echo $sqlPerUsadosLic;
//   $arrayLic = executeSelect($sqlPerUsadosLic);

//    for($i=0;$i<count($arrayLic);$i++){
//    	$varRut=$arrayLic[$i]['rut_per'];
//   	 echo " --- RUT - ";
//   	 echo $varRut;
//    	 echo " ------ ";
//    	 echo " ----- CONSULTA ---";
//    	 echo "UPDATE PERSONAL SET bukLic=2 WHERE rut_per=$varRut";
//      echo " ----- ";
//  	executeSql("UPDATE PERSONAL SET bukLic=2 WHERE rut_per=$varRut");	
//     }
// }

//function envioAtributosEmpleados(){
//***************** */ Envio por Post de Atributos a BD Personal
// $sql3="SELECT *, FORMAT(fecnac_per,'yyyy-mm-dd') as fecnac_perf, FORMAT(fecing_per,'yyyy-mm-dd') as fecing_perf FROM PERSONAL where id_buk=1 order by rut_per";
// $array3 = executeSelect($sql3);
//  $tipCta='';
//   $tipBco='';
//   $ctaNum='';
//   $codNac='';
//    for($i=0;$i<count($array3);$i++){
//  	$varRut=round($array3[$i]['rut_per']);

//   	 if ($array3[$i]['cta_tipo']=='corriente'){
//   			 $tipCta='Corriente';
//   	 }else{
//   		 $tipCta='Vista';
//   	 }
//   	 if ($array3[$i]['cta_banco']=='BCI'){
//   		 $tipBco='BCI';
//   	 }elseif ($array3[$i]['cta_banco']=='CREDICHILE'){
//   				$tipBco='CrediChile';
//   	 }elseif ($array3[$i]['cta_banco']=='FALABELLA'){
//   		 	   $tipBco='Falabella';
//   	 }elseif ($array3[$i]['cta_banco']=='SANTANDER'){
//   				$tipBco='Santander';
//   	 }elseif ($array3[$i]['cta_banco']=='SCOTIABANK'){
//   				$tipBco='ScotiaBank';
//  	 }elseif ($array3[$i]['cta_banco']=='SECURITY'){
//   				$tipBco='Security';
//   	 }else {
//   				$tipBco='Banco Estado';
//   	 }
	
//   	echo "nacionalidad";
//   	echo $array3[$i]['nac_per'];
//   	$codNac=$array3[$i]['nac_per'];
//   	if($codNac=='boliviana' or $codNac=='boliviano'){
//   			$codNac='BO';
//   		}elseif( $codNac=='colombiana' or $codNac=='colombiano'){
//   			   $codNac='CO';
//   		}elseif($codNac=='haitiano' or $codNac=='haitiana' or $codNac=='haitian' or $codNac=='haiti'){
//   					$codNac='HT';
//   		}elseif($codNac=='PERUANA' or $codNac=='peruano'){
//   					   $codNac='PE';
//   		}elseif($codNac=='VENEZOLANA' or $codNac=='venezolano'){
//   						   $codNac='VE';
//  		}else{
//   		$codNac='CL';
//   	}
//   	echo $codNac;
//   	if($array3[$i]['cta_numero']==''){
//   		$ctaNum='000';
//   	} else {
//   		$ctaNum=$array3[$i]['cta_numero'];
//   	}
//   $varEmail="sinmail".strval($i+2450)."@gmail.com";
//   	echo $varEmail;
//  echo "UPDATE PERSONAL SET cta_tipo_buk='".$tipCta."', cta_banco_buk='".$tipBco."', cta_numero='".$ctaNum."', email_per='".$varEmail."' WHERE rut_per=$varRut"; 
//  //echo "UPDATE PERSONAL SET cta_tipo_buk='".$tipCta."', cta_banco_buk='".$tipBco."', cta_numero='".$ctaNum."', cod_nac='".$codNac."', email_per='".$varEmail."' WHERE rut_per=$varRut";
//   executeSql("UPDATE PERSONAL SET cta_tipo_buk='".$tipCta."', cta_banco_buk='".$tipBco."', cta_numero_buk='".$ctaNum."', email='".$varEmail."' WHERE rut_per=$varRut");
//  } 
// }

function envioEmpleados(){
//***** Envio Api EMpleados *****
 	// $data3a =  json_encode($jsona);
 	// echo $data3a;
$sql3="SELECT *, FORMAT(fecnac_per,'yyyy-mm-dd') as fecnac_perf, FORMAT(fecing_per,'yyyy-mm-dd') as fecing_perf FROM PERSONAL 
where id_buk=1 order by rut_per";
$array3 = executeSelect($sql3);


 	// echo "Numero de registros";
    //  echo count($array3);
  	for($i=0;$i<count($array3);$i++){
   	$rutComp=strval(round($array3[$i]['rut_per']))."-".$array3[$i]['dv_per'];
 	// echo "    ";
 	// echo $rutComp;
 	// echo "    ";
 	// echo $array3[$i]['Nom_per'];
 	// echo "    ";


   	$json3 = array(
   		'first_name' => $array3[$i]['Nom_per'],
   		'surname' => $array3[$i]['Apepat_per'],
   		'second_surname' => $array3[$i]['Apemat_per'],
   		'rut' => $rutComp,
 		'code_sheet' => '-',
     	'nationality' =>$array3[$i]['cod_nac'],
   		'gender' => $array3[$i]['sexo_per'],
   		'civil_status' => 'Soltero',
   		'birthday' => $array3[$i]['fecnac_perf'],
   		'email' => $array3[$i]['email'],
   		'personal_email' => $array3[$i]['email'],
     	 'location_id' => 143,
   		'address' => $array3[$i]['Direc_per'],
   		'city' => $array3[$i]['comuna_per'],
   		'payment_method' => 'Transferencia Bancaria',
   		'bank' => $array3[$i]['cta_banco_buk'],
   		'account_type' => $array3[$i]['cta_tipo_buk'],
   		'account_number' => $array3[$i]['cta_numero_buk'],
   		'start_date' => $array3[$i]['fecing_perf'],
   		'private_role' => false,
   		'active' => true
   		);
	
  	$data3 = json_encode(utf8ize($json3)); 
 	$jsonGuideRaw = bsalePOST('https://remuneracionagricola.buk.cl/api/v1/chile/employees', 'H1z6Gc8abmzd217CmKzxbzTj', $data3);
    //echo $data3;
 	}
	 executeSql("UPDATE PERSONAL SET id_buk=0");
}


//***** Envio Api EMpleados endpoint Jobs Necesita Id Buk *****
function envioEmpleadoE1($idBuk2, $rutEmpresa, $rutEmpleado, $idArea, $idRole, $idLeader, $campoBuk2){
// Atributo sacado 'end_of_contract_2' => $array3b[$i]['fec_fin_per'],
// Atributoo sacado select , FORMAT(fecing_per,'yyyy-mm-dd') as fechaing
// Atributo sacado api Buk 'without_wage' => false,
// Atributo sacado api Buk 'periodicity' => 'diaria',
// Atributo sacado api Buk 'location_id '=> 143,   //Region del maule por omision
// Atributo sacado api Buk a pedido de Daniel 'other_type_of_working_day' =>'extraordinaria_art_30',
// Atributo sacado Buk 'end_of_contract' => $array3b[$i]['fecter_per'],
// {
// 	"company_id": 0,
// 	"start_date": "2022-10-28",
// 	"type_of_contract": "Renovación Automática",
// 	"end_of_contract": "2022-10-28",
// 	"end_of_contract_2": "2022-10-28",
// 	"periodicity": "mensual",
// 	"regular_hours": 45,
// 	"type_of_working_day": "ordinaria_art_22",
// 	"other_type_of_working_day": "extraordinaria_art_30",
// 	"location_id": 0,
// 	"area_id": 0,
// 	"role_id": 0,
// 	"leader_id": 0,
// 	"wage": 0,
// 	"currency": "peso",
// 	"without_wage": false,
// 	"reward_concept": "articulo_47",
// 	"reward_payment_period": "gratificacion_mensual"
//   }
 	// $data3a =  json_encode($jsona);
 	// echo $data3a;
	$sqlCampoBuk="SELECT PlNombre FROM T0010b where pl_codigo=$campoBuk2";
	 //echo $sql3b;
	$arrayCpo = executeSelect($sqlCampoBuk);
	$campName=$arrayCpo[0]['PlNombre'];

	$idBu=$idBuk2;
	$sql3b="SELECT * FROM PERSONAL where rut_per=$rutEmpleado order by rut_per";
	//echo $sql3b;
 	$array3b = executeSelect($sql3b);


 	//  echo "Numero de registros";
    //  echo count($array3b);
	// echo "< Link para Post >";
	 $linkBu="https://remuneracionagricola.buk.cl/api/v1/chile/employees/".$idBu."/jobs";
	//echo $linkBu;
  	for($i=0;$i<count($array3b);$i++){
   		// $rutComp=strval(round($array3b[$i]['rut_per']))."-".$array3b[$i]['dv_per'];
 		// echo "    ";
 		// echo $rutComp;
 		// echo "    ";
 		// echo $array3b[$i]['Nom_per'];
 		// echo "    ";
		if ($rutEmpresa==76125892){
			$company=2;
		} else {
			$company=1;
		}

		if($array3b[$i]['indef']==0){
			$tipoContrato='Obra';
			
		} elseif ($array3b[$i]['indef']==1){
			$tipoContrato='Indefinido';
		} 
		//echo $array3b[$i]['sbase_per'];
		$sueldoBase=$array3b[$i]['sbase_per'];
		echo " Sueldo Base: ";
		echo $sueldoBase;
		echo "              ";
   	$json3b = array(
   		'company_id' => $company,
		'start_date' => $array3b[$i]['fecing_per'],
   		'type_of_contract' => $tipoContrato,
     	'regular_hours' => 45,
   		'type_of_working_day' =>'ordinaria_art_22',
   		'area_id' => $idArea,  //temporero BUSCAR CAMPO, determinar manual
   		'role_id' => $idRole,  //Por omision trabajador agricola.
   		'leader_id' => $idLeader,  // Determinar Manual
		'days'=> [
			'l',
			'm',
			'w',
			'j',
			'v',
			's'
		  ],
   		'wage' => $sueldoBase,
     	'currency' => 'peso',
   		'reward_concept' => 'articulo_47',
   		'reward_payment_period' => 'gratificacion_mensual',
		'custom_attributes' => 
			array(
				'Campo' => $campName
			)
			
   	);
// AREAS 59 cauquenes estrella/ 54 CAUQUENES PULMODON / 60 PAUQERAY ESTRELLA
// 57 MAIPU 56 SANTA ROSA  55 TALCA / 53 CURICO (PULMODON)
		
  	$data3 = json_encode(utf8ize($json3b)); 
 	//json_encode($json3b);
	$jsonGuideRaw = bsalePOST($linkBu, 'H1z6Gc8abmzd217CmKzxbzTj', $data3);
	echo $data3;
 	}
}

//***** Envio Api Empleados endpoint Plan Necesita Id Buk *****
function envioEmpleadoE2($idBuk2, $rutEmpleado){
	// {
	// 	"data": {
	// 	  "start_date": "2022-12-01",
	// 	  "pension_scheme": "string",
	// 	  "fund_quote": "string",
	// 	  "health_company": "string",
	// 	  "health_company_plan": 0,
	// 	  "health_company_plan_currency": 0,
	// 	  "health_company_plan_percentage": 0,
	// 	  "disability": true,
	// 	  "invalidity": "string",
	// 	  "afc": "string"
	// 	}
	//   }
	$idBu=$idBuk2;
	$sql3b="SELECT IIF(p.afp_per = '000' , 'no_cotiza', 'afp') AS TipoPrev, FORMAT(p.fecing_per,'yyyy-mm-dd') as fechaing,  IIF(p.afp_per<> '000', a.des_afpBuk,' ') as NomAfp, i.nom_isaBuk as Isapre, p.UF_isa_per As IsaUF, p.peso_isa_per as IsaClp, p.porc_isa_per as IsaPor, p.afp_per from ((personal as p
	left join afp as a on a.cod_afp=p.afp_per)
	left join isapres as i on   i.cod_isa=p.isa_per)
	where rut_per=$rutEmpleado order by rut_per";
	$array3b = executeSelect($sql3b);


	echo "Numero de registros";
	echo count($array3b);
 	//echo "< Link para Post >";
 	//https://remuneracionagricola.buk.cl/api/v1/chile/employees/1/plans
	$linkBu="https://remuneracionagricola.buk.cl/api/v1/chile/employees/".$idBu."/plans";
	//echo $linkBu;
  	for($i=0;$i<count($array3b);$i++){
		if($array3b[$i]['afp_per']=='000'){
          $estadoAfc= 'no_cotiza';
		} else {
			$estadoAfc= 'normal';
		}
   		$json3b = array(
	  		'pension_scheme' => $array3b[$i]['TipoPrev'],
	 		'fund_quote' => strtolower($array3b[$i]['NomAfp']),
	  		'health_company' => strtolower($array3b[$i]['Isapre']),
			'health_company_plan' => $array3b[$i]['IsaUF'],
	 		'health_company_plan_currency' => $array3b[$i]['IsaClp'],
	 		'health_company_plan_percentage' => $array3b[$i]['IsaPor'],
	   		'afc' =>$estadoAfc,
			'disability' => false,
			'invalidity '=> 'no',
	  	);

	
 	 $data3 = json_encode(utf8ize($json3b)); 
 	//json_encode($json3b);
  	
	$jsonGuideRaw = bsalePOST($linkBu, 'H1z6Gc8abmzd217CmKzxbzTj', $data3);
	//echo $data3;
 	}
}

// *************** Envio de Tarifas a Api Buk
function envioTarifas(){

// {
// 	"execution": {
// 	  "piecework_place_id": 0,
// 	  "piecework_task_id": 0,
// 	  "piecework_unit_id": 0,
// 	  "piecework_product_id": 0,
// 	  "formula": 0,
// 	  "start_date": "2022-05-26",
// 	  "type_rate": 0,
// 	  "monetary_floor": 0
// 	}
// }
// Antigua consulta
//  $sql2="SELECT TR.cc1trt, TR.cattrt,TR.unidadtrt, TR.codtrt, val(TR.val1trt) as val1trtf, TT.buk as bukT FROM TRATOS11 as TR
//  LEFT JOIN T0010 AS TT
//  ON  TT.Pl_codigo=val(TR.cc1trt) 
//   where TR.buk=1 order by TR.cc1trt, TR.cattrt, TR.codtrt";
//  $array2 = executeSelect($sql2);
// Nueva consulta
  $sql2="SELECT lb.id as idLab, Lu.id_lugar as idLug,1, 1, '01-07-2022', tt.val1trt
 FROM ((tratos11 as tt 
 left join LugaresBuk as Lu on Lu.code=tt.codLugar)
 left join LaboresBuk as Lb on Lb.code=tt.codBuk) 
 where Lu.id_lugar>0";
  $array2 = executeSelect($sql2);
//   $i=0;
//   $prodId=0;
// //  //count($array2) $array[$i]['rut_per']
   for($i=0;$i<count($array2);$i++){
//   	if($array2[$i]['unidadtrt']=='ALAMBRE'){
//   			$prodId=9;
//  	}elseif($array2[$i]['unidadtrt']=='BINS' or $array2[$i]['unidadtrt']=='B'){
//   			$prodId=10;
//  	}elseif($array2[$i]['unidadtrt']=='CAJA'){
//   			$prodId=11;
//  	}elseif($array2[$i]['unidadtrt']=='dia' or $array2[$i]['unidadtrt']=='día'){
//   			$prodId=12;
//  	}elseif($array2[$i]['unidadtrt']=='GAMELA'){
//   			$prodId=13;
//  	}elseif($array2[$i]['unidadtrt']=='HAS'){
//   			$prodId=14;
//  	}elseif($array2[$i]['unidadtrt']=='mata'){
//   			$prodId=15;
//  	}elseif($array2[$i]['unidadtrt']=='MT'){
//   			$prodId=16;
// 	}elseif($array2[$i]['unidadtrt']=='NOGALES'){
//   			$prodId=17;
//  	}elseif($array2[$i]['unidadtrt']=='PALO' or $array2[$i]['unidadtrt']=='PALOS'){
//   			$prodId=20;
//  	}elseif($array2[$i]['unidadtrt']=='PARRON'){
//   			$prodId=18;
//  	}elseif($array2[$i]['unidadtrt']=='RIEGO'){
//   			$prodId=19;
//  	}elseif($array2[$i]['unidadtrt']=='TINTORERO'){
//   			$prodId=21;
//  	}elseif($array2[$i]['unidadtrt']=='UNIDAD'){
//   			$prodId=22;
//  	}else{
//   			$prodId=8;
//   	}
  	$json2 = array(
  	'piecework_place_id' => $array2[$i]['idLab'],
  	'piecework_task_id' => $array2[$i]['idLug'],
  	'piecework_unit_id' => 1,
  	'piecework_product_id' => 1,
  	'formula' => $array2[$i]['val1trtf'],
  	'start_date' => "2022-05-01",
  	'type_rate' => 0,
  	'monetary_floor' => 0,
  	);
  	$data2 =  json_encode($json2);
 	$jsonGuideRaw = bsalePOST('https://remuneracionagricola.buk.cl/api/v1/chile/piecework/executions', 'H1z6Gc8abmzd217CmKzxbzTj', $data2);
	echo $data2;
  }
}

// *************** Envio de tarjas x dia y x Trabajador a Api Buk, me dice que day debe ser del mes actual revisar
function envioTarjas($An, $mes, $campo){
//   {
// 	"daily_base_floor": false,
// 	"discount_day_amount": false,
// 	"calculate_total_pay": false,
// 	"sync_attendance": 0,
// 	"day": "2022-01-01",
// 	"employee_id": 1,
// 	"worklogs": [
// 	  {
// 		"piecework_execution_id": 1,
// 		"production": 0,
// 		"rate_value": 3,
// 		"saved_in": "horas",
// 		"total_pay": 100,
// 		"work_type": "trato",
// 		"worked_units": 8
// 	  }
// 	]
//   }
//Antigua consulta
// $sql ="SELECT T.jornadatj, FORMAT(T.fechatj,'yyyy-mm-dd') as fechatjf, T.det_trato, T.rut_per, T.rendtj, tt.val1trt as val1trtf, t.cc1trt, t.codtrt, t.cc3 as cc3, t.cc4 as cc4
// FROM TARJASBUK2 AS T
// LEFT JOIN TRATOS11 AS TT
// ON  TT.cc1trt=T.cc1trt and tt.codtrt=t.codtrt
// WHERE YEAR(T.fechatj)=2022 AND MONTH(T.fechatj)=07 ORDER BY T.fechatj";

//Nueva consulta
//  $sql ="SELECT T.jornadatj, FORMAT(T.fechatj,'yyyy-mm-dd') as fechatjf , T.det_trato, T.rut_per, T.rendtj, t.cc1trt, T.val1trt, t.cattrt, t.codtrt, t.cc3 as cc3, t.cc4 as cc4, PE.id_buk, PE.sbase_per
//  FROM TARJASBUK2 AS T
//  LEFT JOIN PERSONAL AS PE
//  ON PE.rut_per=T.rut_per
//  WHERE YEAR(T.fechatj)=2022 AND MONTH(T.fechatj)=07  and cc1tj='06' ORDER BY t.rut_per, T.fechatj";

//***Nueva consulta 2.0
$sql ="SELECT FORMAT(T.fechatj,'yyyy-mm-dd') as fechatjf, PE.id_buk2 as idBuk, T.rendtj, Tb.id_tarifa_buk as IdTar, t.det_trato as detTrat
FROM (((TARJAS1 AS T LEFT JOIN PERSONAL AS PE ON PE.rut_per=T.rut_per) LEFT JOIN LaboresBuk AS Lb ON Lb.code=T.codigo_labor_buk) LEFT JOIN TarifasBuk AS Tb ON Tb.codigo_labor=Lb.id)
WHERE YEAR(T.fechatj)=$An AND MONTH(T.fechatj)=$mes and t.cc1trt='".trim($campo)."'
ORDER BY t.rut_per, T.fechatj";


  $array = executeSelect($sql);
//  $i=0;
// // //count($array) $array[$i]['rut_per']  and T.rut_per=8142348

//  echo count($array);
  for($i=0;$i<count($array);$i++){
// 	 if($array[$i]['cc3']=='12' and $array[$i]['cc4']=='03' ){
// 	 	$varInasistencia='ausencia';
// 	 } else {
// 	 	$varInasistencia='trato';
// 	 }
// 	 	$valorJornada=0;
// 	 	$cantHoras=0;
// 	 	if ($array[$i]['jornadatj']==1){
// 	 		$cantidadhoras=8;
// 	 	}
// 	 	else if ($array[$i]['jornadatj']==0.5){
// 	 		$cantidadhoras=4;
// 	 	}	$array[$i]['fechatjf']	
//  if ($array[$i]['cattrt']==0 and $array[$i]['codtrt']==0){
//  	$valorApagar=0;
//    }else{
//       $valorDiario=round($array[$i]['sbase_per']/31,0); 
//  	//  echo "Valor Diario ";
//  	//  echo $valorDiario;
//  	//  echo "cc3 ";
//  	//  echo $array[$i]['cc3'];
//  	//  echo "cc4 ";
//  	//  echo $array[$i]['cc4'];
//      if ($array[$i]['cc3']=='02' && $array[$i]['cc4']=='02'){
//  		$valorApagar=15500;
//  	   //$valorApagar=$array[$i]['val1trt'];
//  	   //echo "Entro donde no debia ";
//      }else {
//         $valorJornada=$array[$i]['rendtj'] * $array[$i]['val1trt']; 
// 	 	   //echo "Valor jornada: ";
//        //echo $valorJornada;
//         if($valorDiario>$valorJornada){
//             $valorApagar=0;
//         }
//         else{
//  	   $valorApagar=$valorJornada-$valorDiario;	
//         }
//      }
//  }

//  echo " ";
//  echo $i;
//  // echo "Valor a pagar: ";
//  // echo $valorApagar;
//  //echo $valorJornada;
 		$json = array(
 		'daily_base_floor' => true,  //Piso base Diario
 		'discount_day_amount' => false, 
 		'calculate_total_pay' => true,  // Calcular el pago total
 		'sync_attendance' => 0,
 		'day' => $array[$i]['fechatjf'],
 		'employee_id' => $array[$i]['idBuk'],
 		'worklogs' => array(
 			array(
 				'piecework_execution_id' => $array[$i]['IdTar'],
 				'production' => round($array[$i]['rendtj'],1),
 				'rate_value' => round(0,1),
 				'saved_in' => "porcentaje",
 				'total_pay' => round(0,1),
 				'work_type' => $array[$i]['detTrat'],
 				'worked_units' => 100
 			)
 		)
 	);
// 	//echo $json;
 	$data =  json_encode($json);
	$jsonGuideRaw = bsalePOST('https://remuneracionagricola.buk.cl/api/v1/chile/piecework/worklogs', 'H1z6Gc8abmzd217CmKzxbzTj', $data);	
 	echo $data;
 }
}

//function marcarEmpleadosAusencia(){
//*****  Marcar los empleados con inasistencias en julio 2022 bukIna=3 count($arrayLic)
//   $sqlPerUsadosIna="SELECT distinct rut_per
//  	  FROM TARJASBUK2
//  	  WHERE YEAR(fechatj)=2022 and MONTH(fechatj)=07 and cc3='12' and cc4='03'";

//  	  echo $sqlPerUsadosIna;   
//  $arrayLic = executeSelect($sqlPerUsadosIna);
//       for($i=0;$i<count($arrayLic);$i++){
//      	$varRut=$arrayLic[$i]['rut_per'];
//     	 echo " --- RUT - ";
//     	 echo $varRut;
//      	 echo " ------ ";
//      	 echo " ----- CONSULTA ---";
//      	 echo "UPDATE PERSONAL SET bukVac=3 WHERE rut_per=$varRut";
//        echo " ----- ";
// executeSql("UPDATE PERSONAL SET bukVac=3 WHERE rut_per=$varRut");	
// 	}  
//}   

// ************ Extraccion de inasistencias OK  ********* 
 function envioAusencias($An, $mes, $trabaja){ 
//{
// 	"start_date": "2022-06-28",
// 	"days_count": 0,
// 	"day_percent": "1",
// 	"application_date": "2022-06-28",
// 	"justification": "string",
// 	"employee_id": 0,
// 	"absence_type_id": 0  ".$filtroTrab." count($arrayIna)
//   }
if ($trabaja!=0) {
 $filtroTrab=" and t.rut_per=$trabaja";
} else {
 $filtroTrab=" ";
}
echo $filtroTrab;
//  $sqlInasistencia ="SELECT jornadatj, FORMAT(fechatj,'yyyy-mm-dd') as fechatjf , det_trato, rut_per, cc3, cc4 FROM TARJASBUK2 
//  WHERE YEAR(fechatj)=2022 AND MONTH(fechatj)=03 and cc3='12' and cc4='03' ORDER BY fechatj";
//  echo count($sqlInasistencia);
 $sqlInasistencia="SELECT t.jornadatj, FORMAT(t.fechatj,'yyyy-mm-dd') as fechatjf , tt.id_buk2 as idbuk, t.rut_per, t.cc3, t.cc4 FROM TARJAS1 as t 
 LEFT JOIN PERSONAL AS TT
 ON  TT.rut_per=T.rut_per 
 WHERE YEAR(t.fechatj)=$An AND MONTH(t.fechatj)=$mes".$filtroTrab." and t.cc3='12' and t.cc4='03' ORDER BY t.rut_per,t.fechatj";

   $arrayIna=executeSelect($sqlInasistencia);
   echo $sqlInasistencia;
       for($i=0;$i<1;$i++){
     	echo " ----- ";
   	   //echo $arrayLj[$i]['rut_perf'];    
     	$jsonIna = array(
    	'start_date' => $arrayIna[$i]['fechatjf'],
   	 	'days_count' => 1,
     	'day_percent' => "1",
     	'application_date' => $arrayIna[$i]['fechatjf'],
     	'justification' => "injustificada",
     	'employee_id' => $arrayIna[$i]['idbuk'],
     	'absence_type_id' => 19,
     	);
     	$dataIna = json_encode($jsonIna);
     	$jsonGuideRaw = bsalePOST('https://remuneracionagricola.buk.cl/api/v1/chile/absences/absence', 'H1z6Gc8abmzd217CmKzxbzTj', $dataIna);
     	echo $dataIna;
     } 
}

//***************** */ Envio por Post de Lugares a Api Buk funcionando Ok
//function envioLugares(){
// {
// 	"place": { t00010
// 	  "code": "string",
// 	  "name": "string",
// 	  "empresa_id": 0,
// 	  "centro_costo_definition_id": 0
// 	}
//   }
//      $bl=' ';
//     for($i=3;$i<4;$i++){
//  	   if ($array5[$i]['PlNombre']=='') {
//  		   $bl='blanco';
//  					   }else{
//  						 $bl=$array5[$i]['PlNombre'];
//  					   }

//   	$json5 = array(
//   		'code' => $array5[$i]['Pl_codigo'],
//    		'name' => $bl,
//    		'empresa_id' => 1,
//   		'centro_costo_definition_id' => 1,
//    	);
//  	 $data5 =  json_encode($json5);
//   	 $jsonGuideRaw = bsalePOST('https://remuneracionagricola.buk.cl/api/v1/chile/piecework/places', 'tj5UTcsKTnP63CWbTJFP2bgS', $data5);
//   	 echo $data5;
//    }
// }

//Se realiza un Get para extraer el ID del empleado
function getIdEmpleado(){
	$jsonGuide = json_decode(bsaleGET('https://remuneracionagricola.buk.cl/api/v1/chile/employees?page_size=10000', 'H1z6Gc8abmzd217CmKzxbzTj'), true);

	echo " Ahora es JsonGuide:   ";

	//var_dump($jsonGuide);
	echo "Total registros: ";
	echo count($jsonGuide['data']);
	for($i=0; $i<count($jsonGuide['data']); $i++){
		//  echo " ID: ";
		//  echo $jsonGuide['data'][$i]['id'];
		//  echo "   RUT:";
		//  echo $jsonGuide['data'][$i]['rut'];
	

	// echo $jsonGuide['data'][0]['rut'];
	 echo "    ";
	// echo $jsonGuide['data'][0]['id'];
	

	$TamRut=strlen($jsonGuide['data'][$i]['rut']);
	echo $TamRut;
	echo "    ";
	//echo intval($jsonGuide['data'][0]['rut']);
	if ($TamRut==11){
		$rut1=substr($jsonGuide['data'][$i]['rut'], 0, 1);
		$rut2=substr($jsonGuide['data'][$i]['rut'], 2, 3);
		$rut3=substr($jsonGuide['data'][$i]['rut'], 6, 3);
		} else
		{
		$rut1=substr($jsonGuide['data'][$i]['rut'], 0, 2);
		$rut2=substr($jsonGuide['data'][$i]['rut'], 3, 3);
		$rut3=substr($jsonGuide['data'][$i]['rut'], 7, 3);	
	}
	echo $rut1;
	echo "  ";
	echo $rut2;
	echo "  ";
	echo $rut3;
	$srut=intval($rut1.$rut2.$rut3);
	echo "  ";
	echo $srut;
	      executeSql("UPDATE personal SET 
	      			id_buk2=".$jsonGuide['data'][$i]['id']."
	      			WHERE rut_per=$srut");
		}
	}

	//Se realiza un Get para extraer los empleados del campo elegido con Atributo personalizado Campo

	

//***************************************************************** */
	//Se realiza un Get para extraer los ID de las Tarifas
	function getPeriodos(){
		$jsonGuideP = json_decode(bsaleGET('https://remuneracionagricola.buk.cl/api/v1/chile/process_periods', 'H1z6Gc8abmzd217CmKzxbzTj'), true);
	
		//echo "     Ahora es JsonGuide Periodos):   ";
	
		//var_dump($jsonGuideT);
		 //echo count($jsonGuideT['data']);
		executeSql("delete from PeriodosBuk"); 
		for($i=0; $i<count($jsonGuideP['data']); $i++){
		
		//   echo "INSERT INTO TarifasBuk (id_tarifa_buk, codigo_labor) values (" 
		//   .($jsonGuideT['data'][$i]['id']).", ".($jsonGuideT['data'][$i]['piecework_task_id']).")";
		  executeSql("INSERT INTO PeriodosBuk (id, mes, statusF) values (" 
		  .($jsonGuideP['data'][$i]['id']).", '".($jsonGuideP['data'][$i]['month'])."', '".($jsonGuideP['data'][$i]['status'])."')");
		}	
	}

//*********************** */

	function getFullDate($date){
		$todayDay = ""; 
		$todayMonth = "";
		if(date('w', strtotime($date))==1) $todayDay="Lunes";
		if(date('w', strtotime($date))==2) $todayDay="Martes";
		if(date('w', strtotime($date))==3) $todayDay="Mi&eacute;rcoles";
		if(date('w', strtotime($date))==4) $todayDay="Jueves";
		if(date('w', strtotime($date))==5) $todayDay="Viernes";
		if(date('w', strtotime($date))==6) $todayDay="S&aacute;bado";
		if(date('w', strtotime($date))==0) $todayDay="Domingo";
	
		if(date('m', strtotime($date))==1) $todayMonth="ENERO";
		if(date('m', strtotime($date))==2) $todayMonth="FEBRERO";
		if(date('m', strtotime($date))==3) $todayMonth="MARZO";
		if(date('m', strtotime($date))==4) $todayMonth="ABRIL";
		if(date('m', strtotime($date))==5) $todayMonth="MAYO";
		if(date('m', strtotime($date))==6) $todayMonth="JUNIO";
		if(date('m', strtotime($date))==7) $todayMonth="JULIO";
		if(date('m', strtotime($date))==8) $todayMonth="AGOSTO";
		if(date('m', strtotime($date))==9) $todayMonth="SEPTIEMBRE";
		if(date('m', strtotime($date))==10) $todayMonth="OCTUBRE";
		if(date('m', strtotime($date))==11) $todayMonth="NOVIEMBRE";
		if(date('m', strtotime($date))==12) $todayMonth="DICIEMBRE";
	
		return date('d',strtotime($date))." DE ".$todayMonth." DE ".date('Y',strtotime($date));
	
	}

	function getIdEmpleadoAct($idCampo){
		//echo 'test GetIdEmpleadoAct';
		executeSql("DELETE from PERSONAL3");
		 $campNum=intval($idCampo);
		//  echo ' Campo         ';
		//  echo $campNum;
		$ConsX="SELECT PLNOMBRE as CampoTr FROM T0010b where pl_codigo=$campNum";
		// echo '          ';
		// echo $ConsX;
		$CampoTra=executeSelect("SELECT PLNOMBRE as CampoTr FROM T0010b where pl_codigo=$campNum");
		//echo 'paso select  ';
		$camFiltrado = str_replace(" ","+",$CampoTra[0]['CampoTr']);
		// echo $camFiltrado;
		// echo '          ';
		$LinkEmp='https://remuneracionagricola.buk.cl/api/v1/chile/employees?custom_attr_job_name=Campo&custom_attr_job_value='.$camFiltrado;
		//echo $LinkEmp;
		$jsonGuide = json_decode(bsaleGET($LinkEmp, 'H1z6Gc8abmzd217CmKzxbzTj'), true);
	
		//echo "Detalle JsonGuide:   ";
	
		//echo var_dump($jsonGuide);
		//echo "Total registros: ";
		//echo count($jsonGuide['data']);
		if(count($jsonGuide['data'])>0){
		for($i=0; $i<count($jsonGuide['data']); $i++){
			//  echo " ID: ";
			//  echo $jsonGuide['data'][$i]['id'];
			//  echo "   RUT:";
			//  echo $jsonGuide['data'][$i]['rut'];
		
	
		// echo $jsonGuide['data'][0]['rut'];
		//  echo "    ";
		// echo $jsonGuide['data'][0]['id'];
		
	
		$TamRut=strlen($jsonGuide['data'][$i]['rut']);
		// echo $TamRut;
		// echo "    ";
		//echo intval($jsonGuide['data'][0]['rut']);
		if ($TamRut==11){
			$rut1=substr($jsonGuide['data'][$i]['rut'], 0, 1);
			$rut2=substr($jsonGuide['data'][$i]['rut'], 2, 3);
			$rut3=substr($jsonGuide['data'][$i]['rut'], 6, 3);
			$dva=substr($jsonGuide['data'][$i]['rut'], 10, 1);
			} else
			{
			$rut1=substr($jsonGuide['data'][$i]['rut'], 0, 2);
			$rut2=substr($jsonGuide['data'][$i]['rut'], 3, 3);
			$rut3=substr($jsonGuide['data'][$i]['rut'], 7, 3);	
			$dva=substr($jsonGuide['data'][$i]['rut'], 11, 1);
		}
		// echo $rut1;
		// echo "  ";
		// echo $rut2;
		// echo "  ";
		// echo $rut3;
		$srut=intval($rut1.$rut2.$rut3);
		// echo "  ";
		// echo $srut;
		// echo " ";
		$fname=$jsonGuide['data'][$i]['first_name'];
		$apPat=$jsonGuide['data'][$i]['surname'];
		$apMat=$jsonGuide['data'][$i]['second_surname'];
		$idTra=$jsonGuide['data'][$i]['id'];
	
		// echo $jsonGuide['data'][$i]['first_name'];
		// echo $jsonGuide['data'][$i]['surname'];
		// echo $jsonGuide['data'][$i]['second_surname'];
		// echo $jsonGuide['data'][$i]['id'];
		//$linkId='https://remuneracionagricola.buk.cl/api/v1/chile/employees/'+$idTra+'/jobs';
		//$linkId='https://remuneracionagricola.buk.cl/api/v1/chile/employees?custom_attr_job_name=Campo?custom_attr_job_value='+$CampoTra;
		//$idCamp=json_decode(bsaleGET($linkId, 'tj5UTcsKTnP63CWbTJFP2bgS'), true);
	
		// que dato necesito rut-id-nombre-campo-dias trabajados
			//   executeSql("UPDATE personal SET 
			//   			id_buk2=".$jsonGuide['data'][$i]['id']."
			//   			WHERE rut_per=$srut");
			//$consultaTxt="INSERT INTO personal3 (Id_buk2, rut_per, dv_per, Nom_per, Apepat_per, Apemat_per, estado_per, planta_per) VALUES ($idTra, $srut, '$dva', '$fname', '$apPat', '$apMat', 'V', $campNum)";
			//echo $consultaTxt;
			executeSql("INSERT INTO personal3 (Id_buk2, rut_per, dv_per, Nom_per, Apepat_per, Apemat_per, estado_per, planta_per) VALUES ($idTra, $srut, '$dva', '$fname', '$apPat', '$apMat', 'V', $campNum)");
			}
			return 1;
		} else {
			return 0;
		}
		
	}

	//Se realiza un Get para extraer los ID de los lugares
function getIdLugares(){

	$jsonGuideLu = json_decode(bsaleGET('https://remuneracionagricola.buk.cl/api/v1/chile/piecework/places?&page_size=100', 'H1z6Gc8abmzd217CmKzxbzTj'), true);

	//echo "     Ahora es JsonGuide (Lugares):   ";

	executeSql("delete from LugaresBuk"); 
	//var_dump($jsonGuideT);  count($jsonGuideLu['data']
	 //echo $jsonGuideLu['data'][0]['id'];
	for($i=0; $i<count($jsonGuideLu['data']); $i++){
	// echo $jsonGuideLu['data'][$i]['id'];
	// echo "      ";
	// echo $jsonGuideLu['data'][$i]['code'];
	// echo "    ";
	// echo $jsonGuideLu['data'][$i]['name'];
	// echo "    ";
	//echo "INSERT INTO LugaresBuk (id_lugar, code, nombre, id_empresa, id_centro_costo) values (".($jsonGuideLu['data'][$i]['id']).", '".($jsonGuideLu['data'][$i]['code'])."', '".($jsonGuideLu['data'][$i]['name'])."', ".($jsonGuideLu['data'][$i]['empresa_id']).", ".($jsonGuideLu['data'][$i]['centro_costo_definition_id']).")";
	executeSql("INSERT INTO LugaresBuk (id_lugar, code, nombre, id_empresa, id_centro_costo) values (".($jsonGuideLu['data'][$i]['id']).", '".($jsonGuideLu['data'][$i]['code'])."', '".($jsonGuideLu['data'][$i]['name'])."', ".($jsonGuideLu['data'][$i]['empresa_id']).", ".($jsonGuideLu['data'][$i]['centro_costo_definition_id']).")");
	}

	// echo $jsonGuideL['data'][0]['id'];
	//  echo "      ";
	// echo $jsonGuideLu['data'][0]['code'];
	//  echo "    ";
	//  echo $jsonGuideLu['data'][0]['name'];
}
//Se realiza un Get para extraer los ID de las Unidades
function getIdUnidades(){

	$jsonGuideUn = json_decode(bsaleGET('https://remuneracionagricola.buk.cl/api/v1/chile/piecework/units?&page_size=100', 'H1z6Gc8abmzd217CmKzxbzTj'), true);

	//echo "     Ahora es JsonGuide (unidades):   ";

	executeSql("delete from UnidadesBuk"); 
	 //echo $jsonGuideLu['data'][0]['id'];
	for($i=0; $i<count($jsonGuideUn['data']); $i++){
	// echo $jsonGuideLu['data'][$i]['id'];
	// echo "      ";
	// echo $jsonGuideLu['data'][$i]['code'];
	// echo "    ";
	// echo $jsonGuideLu['data'][$i]['name'];
	// echo "    ";
	//echo "INSERT INTO LugaresBuk (id_lugar, code, nombre, id_empresa, id_centro_costo) values (".($jsonGuideLu['data'][$i]['id']).", '".($jsonGuideLu['data'][$i]['code'])."', '".($jsonGuideLu['data'][$i]['name'])."', ".($jsonGuideLu['data'][$i]['empresa_id']).", ".($jsonGuideLu['data'][$i]['centro_costo_definition_id']).")";
	executeSql("INSERT INTO UnidadesBuk (id_unidad, codigo, nombre) values (".($jsonGuideLu['data'][$i]['id']).", '".($jsonGuideLu['data'][$i]['code'])."', '".($jsonGuideLu['data'][$i]['name']).")");
	}

	// echo $jsonGuideL['data'][0]['id'];
	//  echo "      ";
	// echo $jsonGuideLu['data'][0]['code'];
	//  echo "    ";
	//  echo $jsonGuideLu['data'][0]['name'];
}

//Se realiza un Get para extraer los ID de las Unidades
function getIdProductos(){

	$jsonGuidePr = json_decode(bsaleGET('https://remuneracionagricola.buk.cl/api/v1/chile/piecework/products?&page_size=100', 'H1z6Gc8abmzd217CmKzxbzTj'), true);

	//echo "     Ahora es JsonGuide (unidades):   ";

	executeSql("delete from ProductosBuk"); 
	 //echo $jsonGuideLu['data'][0]['id'];
	for($i=0; $i<count($jsonGuidePr['data']); $i++){
	// echo $jsonGuideLu['data'][$i]['id'];
	// echo "      ";
	// echo $jsonGuideLu['data'][$i]['code'];
	// echo "    ";
	// echo $jsonGuideLu['data'][$i]['name'];
	// echo "    ";
	//echo "INSERT INTO LugaresBuk (id_lugar, code, nombre, id_empresa, id_centro_costo) values (".($jsonGuideLu['data'][$i]['id']).", '".($jsonGuideLu['data'][$i]['code'])."', '".($jsonGuideLu['data'][$i]['name'])."', ".($jsonGuideLu['data'][$i]['empresa_id']).", ".($jsonGuideLu['data'][$i]['centro_costo_definition_id']).")";
	executeSql("INSERT INTO ProductosBuk (id_product, codigo, nombre) values (".($jsonGuideLu['data'][$i]['id']).", '".($jsonGuideLu['data'][$i]['code'])."', '".($jsonGuideLu['data'][$i]['name']).")");
	}

	// echo $jsonGuideL['data'][0]['id'];
	//  echo "      ";
	// echo $jsonGuideLu['data'][0]['code'];
	//  echo "    ";
	//  echo $jsonGuideLu['data'][0]['name'];
}

//Se realiza un Get para extraer los ID de las Labores/Tratos
function getIdLabores(){
     $j=1;
	 $pag='';
	 $cont=0;
	 //echo "Prueba   ";
	 executeSql("delete from LaboresBuk");
	 //echo $cont;
	while($cont==0){
	// echo "  Entre   ";
     $pag=intval($j);
	// echo $pag;
	$jsonGuideL = json_decode(bsaleGET('https://remuneracionagricola.buk.cl/api/v1/chile/piecework/tasks?page_size=100&page='.$pag, 'H1z6Gc8abmzd217CmKzxbzTj'), true);

	//echo "     Ahora es JsonGuide (Labores/Tratos) Totales:   ";

	//var_dump($jsonGuideT);
	//echo count($jsonGuideL['data']);
	for($i=0; $i<count($jsonGuideL['data']); $i++){
	//  echo $jsonGuideL['data'][$i]['id'];
	//  echo "      ";
	//  echo $jsonGuideL['data'][$i]['code'];
	//  echo "    ";
	//  echo $jsonGuideL['data'][$i]['description'];
	//  echo "    ";
	  //echo "INSERT INTO LaboresBuk (id, code, description) values (" 
	  //.($jsonGuideL['data'][$i]['id']).", '".($jsonGuideL['data'][$i]['code'])."', '".($jsonGuideL['data'][$i]['description'])."')";
	 // echo "INSERT INTO LaboresBuk (id, code, descriptionL) values (" 
	  //.($jsonGuideL['data'][$i]['id']).", '".($jsonGuideL['data'][$i]['code'])."', '".($jsonGuideL['data'][$i]['description'])."')";
	   executeSql("INSERT INTO LaboresBuk (id, code, descriptionL) values (" 
	   .($jsonGuideL['data'][$i]['id']).", '".($jsonGuideL['data'][$i]['code'])."', '".($jsonGuideL['data'][$i]['description'])."')");
	
	}
	if (count($jsonGuideL['data'])<100){
		$cont=1;
		$treg=100+count($jsonGuideL['data']);
		//echo $treg;
	   }
		else{
		$j=$j+1;	
		}
	
	}
	// echo $jsonGuideL['data'][0]['id'];
	//  echo "      ";
	// echo $jsonGuideL['data'][0]['code'];
	//  echo "    ";
	//  echo $jsonGuideL['data'][0]['description'];
	//echo "Todavia bien";
}

//***************************************************************** */
	//Se realiza un Get para extraer los ID de las Tarifas
	function getIdtarifasNew($campo){
	 $treg=0;
	 $j=1;
	 $pag='';
	 $cont=0;
	 //echo "Prueba   ";
	 
	 $sql = "SELECT top 1 id_tarifa_buk FROM TarifasBuk
	 order by id_tarifa_buk DESC";
	 $arrayIdTar = executeSelect($sql);
	 $ultimoId=$arrayIdTar[0]['id_tarifa_buk'];

	//echo $ultimoId;

	
	
	// while($cont==0){
	// 	$pag=intval($j);
	// 	$jsonGuideT = json_decode(bsaleGET('https://remuneracionagricola.buk.cl/api/v1/chile/piecework/executions?page_size=100&page='.$pag, 'H1z6Gc8abmzd217CmKzxbzTj'), true);
	// 	//echo "     Ahora es JsonGuide (Tarifas):   ";
		//var_dump($jsonGuideT);
		// echo $jsonGuideT['data'];
		// echo $pag;

	 $sql2 = "SELECT tr.*, tr2.idCampo from (TarifasBuk2 tr
	 left join TarifasBuk as Tr2 on Tr2.id_tarifa_buk=tr.id_tarifa_buk)";
	 $arrayTarif = executeSelect($sql2);

	 executeSql("delete from TarifasBuk"); 
		
		for($i=0; $i<count($arrayTarif); $i++){
		//  echo $jsonGuideT['data'][$i]['id'];
		//  echo "      ";
		// echo $jsonGuideT['data'][$i]['piecework_task_id'];
		// echo "    ";
		// 
		     if ($arrayTarif[$i]['id_tarifa_buk']>$ultimoId){

				// echo "INSERT INTO TarifasBuk (id_tarifa_buk, codigo_labor, codigo_unidad, codigo_lugar, codigo_producto, valor_tarifa, fecha_inicio, tipo_tarifa, idCampo) values (" 
				// .($arrayTarif[$i]['id_tarifa_buk']).", ".($arrayTarif[$i]['codigo_labor']).", ".($arrayTarif[$i]['codigo_unidad']).", ".($arrayTarif[$i]['codigo_lugar']).", ".($arrayTarif[$i]['codigo_producto']).", ".($arrayTarif[$i]['valor_tarifa']).", '".($arrayTarif[$i]['fecha_inicio'])."', '".($arrayTarif[$i]['tipo_tarifa'])."', ".$campo.")";		


		    executeSql("INSERT INTO TarifasBuk (id_tarifa_buk, codigo_labor, codigo_unidad, codigo_lugar, codigo_producto, valor_tarifa, fecha_inicio, tipo_tarifa, idCampo) values (" 
	       .($arrayTarif[$i]['id_tarifa_buk']).", ".($arrayTarif[$i]['codigo_labor']).", ".($arrayTarif[$i]['codigo_unidad']).", ".($arrayTarif[$i]['codigo_lugar']).", ".($arrayTarif[$i]['codigo_producto']).", ".($arrayTarif[$i]['valor_tarifa']).", '".($arrayTarif[$i]['fecha_inicio'])."', '".($arrayTarif[$i]['tipo_tarifa'])."', ".$campo.")");
			} else {
				// echo "INSERT INTO TarifasBuk (id_tarifa_buk, codigo_labor, codigo_unidad, codigo_lugar, codigo_producto, valor_tarifa, fecha_inicio, tipo_tarifa, idCampo) values (" 
				// .($arrayTarif[$i]['id_tarifa_buk']).", ".($arrayTarif[$i]['codigo_labor']).", ".($arrayTarif[$i]['codigo_unidad']).", ".($arrayTarif[$i]['codigo_lugar']).", ".($arrayTarif[$i]['codigo_producto']).", ".($arrayTarif[$i]['valor_tarifa']).", '".($arrayTarif[$i]['fecha_inicio'])."', '".($arrayTarif[$i]['tipo_tarifa'])."', ".($arrayTarif[$i]['idCampo']).")";
				executeSql("INSERT INTO TarifasBuk (id_tarifa_buk, codigo_labor, codigo_unidad, codigo_lugar, codigo_producto, valor_tarifa, fecha_inicio, tipo_tarifa, idCampo) values (" 
				.($arrayTarif[$i]['id_tarifa_buk']).", ".($arrayTarif[$i]['codigo_labor']).", ".($arrayTarif[$i]['codigo_unidad']).", ".($arrayTarif[$i]['codigo_lugar']).", ".($arrayTarif[$i]['codigo_producto']).", ".($arrayTarif[$i]['valor_tarifa']).", '".($arrayTarif[$i]['fecha_inicio'])."', '".($arrayTarif[$i]['tipo_tarifa'])."', ".($arrayTarif[$i]['idCampo']).")");
			}
		}
	

}

//***************************************************************** */
	//Se realiza un Get para extraer los ID de las Tarifas
	function getIdtarifas(){
		$treg=0;
		$j=1;
		$pag='';
		$cont=0;
		//echo "Prueba   ";
		executeSql("delete from TarifasBuk2"); 
		//echo $cont;
	   while($cont==0){
		   $pag=intval($j);
		   $jsonGuideT = json_decode(bsaleGET('https://remuneracionagricola.buk.cl/api/v1/chile/piecework/executions?page_size=100&page='.$pag, 'H1z6Gc8abmzd217CmKzxbzTj'), true);
		   //echo "     Ahora es JsonGuide (Tarifas):   ";
		   //var_dump($jsonGuideT);
		   // echo count($jsonGuideT['data']);
		   // echo "     ";
		   
		   for($i=0; $i<count($jsonGuideT['data']); $i++){
		   // echo $jsonGuideT['data'][$i]['id'];
		   // echo "      ";
		   // echo $jsonGuideT['data'][$i]['piecework_task_id'];
		   // echo "    ";
		   // echo "INSERT INTO TarifasBuk (id_tarifa_buk, codigo_labor, codigo_unidad, codigo_lugar, codigo_producto, valor_tarifa, fecha_inicio, tipo_tarifa) values (" 
		   //    .($jsonGuideT['data'][$i]['id']).", ".($jsonGuideT['data'][$i]['piecework_task_id']).", ".($jsonGuideT['data'][$i]['piecework_unit_id']).", ".($jsonGuideT['data'][$i]['piecework_place_id']).", ".($jsonGuideT['data'][$i]['piecework_product_id']).", ".($jsonGuideT['data'][$i]['formula']).", '".($jsonGuideT['data'][$i]['start_date'])."', '".($jsonGuideT['data'][$i]['type_rate'])."')";
			  executeSql("INSERT INTO TarifasBuk2 (id_tarifa_buk, codigo_labor, codigo_unidad, codigo_lugar, codigo_producto, valor_tarifa, fecha_inicio, tipo_tarifa) values (" 
				 .($jsonGuideT['data'][$i]['id']).", ".($jsonGuideT['data'][$i]['piecework_task_id']).", ".($jsonGuideT['data'][$i]['piecework_unit_id']).", ".($jsonGuideT['data'][$i]['piecework_place_id']).", ".($jsonGuideT['data'][$i]['piecework_product_id']).", ".($jsonGuideT['data'][$i]['formula']).", '".($jsonGuideT['data'][$i]['start_date'])."', '".($jsonGuideT['data'][$i]['type_rate'])."')");
		   }
		   if (count($jsonGuideT['data'])<100){
			   $cont=1;
			   $treg=$treg+count($jsonGuideT['data']);
			   //echo " Total de registros Api: ";
			   //echo $treg;
			  }
			   else{
			   $treg=$treg+100;
			   $j=$j+1;	
			   }
		   // echo $jsonGuideT['data'][0]['id'];
		   // echo "      ";
		   // echo $jsonGuideT['data'][0]['formula'];
		   // echo "    ";
	   }
   }
?>