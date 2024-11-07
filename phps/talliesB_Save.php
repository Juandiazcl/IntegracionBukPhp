<?php
include("../connection/connection.php");

set_time_limit(500);
session_start();

if($_POST['type']=='save'){
	$plant = $_POST['plant'];
	$month = $_POST['month'];
	$year = $_POST['year'];

	//Server 3400
	//$date = explode("/", $_POST['date']);
	//$retardDate = $date[1]."/".$date[0]."/".$date[2];

	$date= $_POST['date'];
	if($date[2]=='/'){
		$date=str_replace("/","-",$date);
	}
	//local
	//echo $_POST['date'];
	$date = explode("-",$date);
	$retardDate = $date[1]."-".$date[0]."-".$date[2];

	$list = $_POST['list'];

	// executeSql("DELETE FROM TARJAS
	// 			WHERE cc1tj='".$plant."' 
	// 			AND fechatj=#$retardDate#");

	$arrayOriginalTally = executeSelect("SELECT * FROM TARJAS
										WHERE cc1tj='".$plant."' 
										AND fechatj=#$retardDate#");

	//RESPALDO DE TARJAS DE TRABAJADORES
	$sql = "INSERT INTO TARJAS1_HISTORIAL(cc1tj,
							fechatj,
							codtj,
							fichatj,
							nomtrabtj,
							tratotj,
							param1,
							param2,
							rut_per,
							jornadatj,
							hhtj,
							rendtj,
							cc2tj,
							cc3tj,
							cc4tj,
							cc2,
							cc3,
							cc4,
							cc1trt,
							cattrt,
							codtrt,
							valtj,
							anteriortj,
							Obslintj,
							rendt1,
							idLab,
							idLug,
							idUni,
							idPro,
							idTar,
							codigo_labor_buk,
							lugarBuk,
							det_trato,
							codbonodd)
					SELECT cc1tj,
							fechatj,
							codtj,
							fichatj,
							nomtrabtj,
							tratotj,
							param1,
							param2,
							rut_per,
							jornadatj,
							hhtj,
							rendtj,
							cc2tj,
							cc3tj,
							cc4tj,
							cc2,
							cc3,
							cc4,
							cc1trt,
							cattrt,
							codtrt,
							valtj,
							anteriortj,
							Obslintj,
							rendt1,
							idLab,
							idLug,
							idUni,
							idPro,
							idTar,
							codigo_labor_buk,
							lugarBuk,
							det_trato,
							codbonodd
						FROM TARJASBUK2
						WHERE cc1tj='".$plant."' 
						AND fechatj=#$retardDate#";
						//echo $sql;
	//executeSql($sql);


	$arrayList = explode('&&&&', $list);

	//echo $arrayList;
	
	$cc1tj = $plant;
	$fechatj = $_POST['date'];
	$ultlintj = count($arrayList);
	$usremitj = $_SESSION['userId'];
	$fecemitj = date('d/m/Y');
	$timemitj = date('H:i:s');
	$usrvbtj = '';
	$fecvbtj = date('d/m/Y');
	$timvbtj = date('H:i:s');
	$obstj = '';
	$stattj = 'A';

	if(count($arrayOriginalTally)==0){

		executeSql("INSERT INTO TARJAS(cc1tj,
								fechatj,
								ultlintj,
								usremitj,
								fecemitj,
								timemitj,
								usrvbtj,
								fecvbtj,
								timvbtj,
								obstj,
								stattj)

								VALUES('".$cc1tj."',
								'".$fechatj."',
								".$ultlintj.",
								'".$usremitj."',
								'".$fecemitj."',
								'".$timemitj."',
								'".$usrvbtj."',
								'".$fecvbtj."',
								'".$timvbtj."',
								'".$obstj."',
								'".$stattj."')
								");
	}else{
		executeSql("UPDATE TARJAS SET 
						ultlintj=".$ultlintj.",
						usremitj='".$usremitj."',
						fecemitj='".$fecemitj."',
						timemitj='".$timemitj."',
						usrvbtj='".$usrvbtj."',
						fecvbtj='".$fecvbtj."',
						timvbtj='".$timvbtj."',
						obstj='".$obstj."',
						stattj='".$stattj."'
					WHERE cc1tj='".$plant."' 
					AND fechatj=#$retardDate#");
	}
// BORRADO PREVIO DE TARJAS
	//Se eliminan los registros para reemplazarlos con los nuevos
	    executeSql("DELETE FROM TARJASBUK2
 	     			WHERE cc1tj='".$plant."' 
 	    			AND fechatj=#$retardDate#");

// echo "cantidad de registros arrayList: ";
// echo count($arrayList);

	for($i=0;$i<count($arrayList);$i++){
		$arrayListDetail = explode('&&', $arrayList[$i]);
		//echo $arrayList[$i];
		$cc1tj = $plant;
		$fechatj = $_POST['date'];
		$codtj = $arrayListDetail[0];
		$fichatj = $arrayListDetail[1];
		if(strlen($fichatj)==7) $fichatj="   ".$fichatj;
		if(strlen($fichatj)==8) $fichatj="  ".$fichatj;
		if(strlen($fichatj)==9) $fichatj=" ".$fichatj;
		$nomtrabtj = $arrayListDetail[2];
		$tratotj = 0;
		$param1 = 0;
		$param2 = 0;
		$rut_per = $arrayListDetail[1];
		
		
		//$cc2tj = $arrayListDetail[3];
		// $cc3tj = $arrayListDetail[4];
		// $cc4tj = $arrayListDetail[5];
		// $cc2 = $arrayListDetail[3];
		// $cc3 = $arrayListDetail[4];
		// $cc4 = $arrayListDetail[5];

		$cc1trt = $plant;
		// $cattrt = $arrayListDetail[6];
		// $codtrt = $arrayListDetail[7];
		$cattrt = 0;
	    $codtrt = 0;
		$valtj = $arrayListDetail[5];
		$anteriortj = 1; //???
		$Obslintj = '';
		$rendt1 = 0;
		$codbonodd = 0;
		$detTrato=$arrayListDetail[3];
		//$siTj=$arrayListDetail[11];
		//  echo "Tipo siTj  ";
		//  echo $siTj;
		// $idLab=$arrayListDetail[38];
		//  $idLug=$arrayListDetail[10];
		// $idTar=$arrayListDetail[40];
		// $idUni=$arrayListDetail[41];
		// $idPro=$arrayListDetail[42];
		$idTar=$arrayListDetail[4];
		$sql3="SELECT id_buk2 FROM personal3
		WHERE rut_per=$rut_per";
		//echo $sql3;
		$arrayIdPer = executeSelect($sql3);
		$idBuk2=$arrayIdPer[0]['id_buk2'];
		//   echo "Id Buk 2";
		//   echo $idBuk2;


		if ($idTar>2){
		$sql2 = "SELECT codigo_labor, codigo_lugar, codigo_producto, codigo_unidad FROM TarifasBuk
		WHERE id_tarifa_buk=$idTar";

		//echo $sql;		
		$arrayIds = executeSelect($sql2);

		$idLab=$arrayIds[0]['codigo_labor'];
		$idLug=$arrayIds[0]['codigo_lugar'];
		$idPro=$arrayIds[0]['codigo_producto'];
		$idUni=$arrayIds[0]['codigo_unidad'];
		$jornadatj = $arrayListDetail[8];
		$hhtj = $arrayListDetail[10];
		$rendtj = $arrayListDetail[9];
		if ($jornadatj==1){
			$porcentajeWork=100;
		} else if($jornadatj==0.5){
			$porcentajeWork=50;
		}
		//if ($siTj==0){
		// echo "Prueba Id Labor";
		// echo $idLab;
		$json = array(
			'daily_base_floor' => true,  //Piso base Diario
			'discount_day_amount' => false,
		 'overwrite_existing' => true, //Que sobreescriba registros existentes
			'calculate_total_pay' => true,  // Calcular el pago total
			'sync_attendance' => 0,
			'day' => $fechatj,
			'employee_id' => $idBuk2,
			'worklogs' => array(
				array(
					'piecework_execution_id' => $idTar,
					'production' => round($rendtj,1),
					'rate_value' => round($valtj,1),
					'saved_in' => "porcentaje",
					'total_pay' => round(0,1),
					'work_type' => $detTrato,
					'worked_units' => $porcentajeWork
				)
			)
		);
   	//echo $json;
		$data =  json_encode($json);
	    //$jsonGuideRaw = bsalePOST('https://remuneracionagricola.buk.cl/api/v1/chile/piecework/worklogs', 'H1z6Gc8abmzd217CmKzxbzTj', $data);	
		//echo $data;
		//$siTj=1;
	//} else {

		// {
		// 	"daily_base_floor": false,
		// 	"discount_day_amount": false,
		// 	"calculate_total_pay": false,
		// 	"sync_attendance": 0,
		// 	"monetary_floor": 0,
		// 	"worklog": {
		// 	  "day": "2022-02-02",
		// 	  "work_type": "dia",
		// 	  "worked_units": 5,
		// 	  "production": 2,
		// 	  "total_pay": 100,
		// 	  "rate_value": 3
		// 	}
		//   }
		//'employee_id' => $idBuk2,
		//'monetary_floor' => 0,
		// echo "Entro al PUT   ";
		// echo $idBuk2;

		// $json = array(
		// 	'daily_base_floor' => true,  //Piso base Diario
		// 	'discount_day_amount' => false,
		// 	'calculate_total_pay' => true,  // Calcular el pago total
		// 	'sync_attendance' => 0,
		// 	'worklog' => array(
		// 		array(
		// 			'day' => $fechatj,
		// 			'work_type' => $detTrato,
		// 			'worked_units' => $porcentajeWork,
		// 			'production' => round($rendtj,1),
		// 			'total_pay' => round(0,1),
		// 			'rate_value' => round($valtj,1)	
		// 		)
		// 	)
		// );
   		// //echo $json;
		// $data =  json_encode($json);
	    //$jsonGuideRaw = bsalePUT('https://remuneracionagricola.buk.cl/api/v1/chile/piecework/worklogs/'.$idBuk2, 'H1z6Gc8abmzd217CmKzxbzTj', $data);	
		//echo $data;
	//}

		// if ($hhtj>0){
   
		   
		// 	$sql4 = "SELECT cc.codigo FROM (CCBuk cc
		// 	left join LugaresBuk as Lg on Lg.id_centro_costo=cc.id)
		// 	WHERE Lg.id_lugar=$idLug";
	
		// 	//echo $sql;		
		// 	//$arrayIdCC = executeSelect($sql4);
	
		// 	$idCC=$arrayIdCC[0]['codigo'];
		// 	// echo " Codigo CC  ";
		// 	// echo $idCC;
	
		// 	$sql5 = "SELECT * FROM HHextras
		// 	WHERE idTrabajador=$idBuk2 and aa=$year and mes=$month";
		// 	//echo $sql5;
		// 	//$arrayIdHH = executeSelect($sql5);
	
	
		// 	// echo "Reg. HH Extras   ";
		// 	// echo count($arrayIdHH);
		// 	if (count($arrayIdHH)==0){
	
		// 	$linkHH='https://remuneracionagricola.buk.cl/api/v1/chile/attendances/overtime';
		// 				//$jsonGuideRawDel = bsaleDELETE($linkBorrado, 'H1z6Gc8abmzd217CmKzxbzTj'); 
		// 				 $jsonHH = array(
		// 				 'month' => $month,
		// 				 'year' => $year,
		// 				 'hours' => $hhtj,
		// 				 'employee_id' => $idBuk2,
		// 				 'type_id' => 1,
		// 				 'centro_costo' => $idCC,
		// 				 );
		// 				 $dataHH = json_encode($jsonHH);
		// 				  //$jsonGuideRaw = bsalePOST($linkHH, 'H1z6Gc8abmzd217CmKzxbzTj', $dataHH);
		// 				 //echo $dataHH;
						
		// 				$sql6 = "INSERT INTO HHextras(aa,
		// 				mes,
		// 				idtrabajador,
		// 				ultfechaGuardado)
		// 				VALUES(".$year.",
		// 						".$month.",
		// 						".$idBuk2.",
		// 						'".$fechatj."')";
								
		// 				//executeSql($sql6);
	
		// 	} else {
	
		// 		$linkHH='https://remuneracionagricola.buk.cl/api/v1/chile/attendances/overtime';
		// 		//$jsonGuideRawDel = bsaleDELETE($linkBorrado, 'H1z6Gc8abmzd217CmKzxbzTj'); 
		// 		 $jsonHH = array(
		// 		 'month' => $month,
		// 		 'year' => $year,
		// 		 'hours' => $hhtj,
		// 		 'employee_id' => $idBuk2,
		// 		 'type_id' => 1,
		// 		 'centro_costo' => $idCC,
		// 		 );
		// 		 $dataHH = json_encode($jsonHH);
		// 		 //$jsonGuideRaw = bsalePUT($linkHH, 'H1z6Gc8abmzd217CmKzxbzTj', $dataHH);
		// 		 //echo $dataHH;
		// 	}
		// }
 

		} else {
			$idLab=0;
			$idLug=0;
			$idPro=0;
			$idUni=0;	
			$jornadatj = $arrayListDetail[8];
			$hhtj = 0.0;
			$rendtj = 0.0;
			if ($idTar==0){   //Post x Insistancia

				// 'application_date' => $arrayIna[$i]['fechatjf'],
				//  'day_percent' => "1",
					$linkBorrado='https://remuneracionagricola.buk.cl/api/v1/chile/absences/absence?employee_ids='.$idBuk2.'&start_date='.$fechatj;
					//$jsonGuideRawDel = bsaleDELETE($linkBorrado, 'H1z6Gc8abmzd217CmKzxbzTj'); 
					 $jsonIna = array(
					 'start_date' => $fechatj,
					 'days_count' => 1,
					 'justification' => "injustificada",
					 'employee_id' => $idBuk2,
					 'absence_type_id' => 8,
					 );
					 $dataIna = json_encode($jsonIna);
					 //$jsonGuideRaw = bsalePOST('https://remuneracionagricola.buk.cl/api/v1/chile/absences/absence', 'H1z6Gc8abmzd217CmKzxbzTj', $dataIna);
					 //echo $dataIna;
			}
			if ($idTar==1){  //Post x Licencia
				//'application_date' => $arrayLj[$i]['fechatjf'],
				//'justification' => "licencia valida",
				    $linkBorrado='https://remuneracionagricola.buk.cl/api/v1/chile/absences/licence?employee_ids='.$idBuk2.'&start_date='.$fechatj;
					// echo "Link Borrado:   ";
					// echo $linkBorrado;
					//$jsonGuideRawDel = bsaleDELETE($linkBorrado, 'H1z6Gc8abmzd217CmKzxbzTj');
					$json2L = array(
					'licence_type_id' => 5,
					'contribution_days' => 0,
					 'format' => "electronica",
					 'type' => "accidente_comun",
					 'start_date' => $fechatj,
					 'days_count' =>1,
					 'day_percent' => "1",
					 'employee_id' => $idBuk2,
					 );
					 $data2L =  json_encode($json2L);
					 //$jsonGuideRaw = bsalePOST('https://remuneracionagricola.buk.cl/api/v1/chile/absences/licence', 'H1z6Gc8abmzd217CmKzxbzTj', $data2L);
					 //echo $data2L;
		}
		// if ($idTar==2){   //Post x Vacaciones
		// 		$linkBorrado='https://remuneracionagricola.buk.cl/api/v1/chile/vacations?employee_id='.$idBuk2.'&start_date='.$fechatj.'&end_date='.$fechatj;
		// 		//$jsonGuideRawDel = bsaleDELETE($linkBorrado, 'H1z6Gc8abmzd217CmKzxbzTj'); 
		// 		$json2L = array(
		// 		'type' => "legales",
		// 		'start_date' => $fechatj,
		// 		'end_date' => $fechatj,
		// 		'percent_day' => 1,
		// 		'employee_id' => $idBuk2,
		// 	   );
		// 		$data2L =  json_encode($json2L);												
		// 		//$jsonGuideRaw = bsalePOST('https://remuneracionagricola.buk.cl/api/v1/chile/vacations', 'H1z6Gc8abmzd217CmKzxbzTj', $data2L);
		// 		//echo $data2L;
		// }
	}
		$sql = "INSERT INTO TARJASBUK2(cc1tj,
							fechatj,
							codtj,
							fichatj,
							nomtrabtj,
							tratotj,
							param1,
							param2,
							rut_per,
							jornadatj,
							hhtj,
							rendtj,
							cc1trt,
							cattrt,
							codtrt,
							valtj,
							anteriortj,
							Obslintj,
							rendt1,
							idLab,
							idLug,
							idTar,
							idPro,
							idUni,
							det_trato,
							codbonodd)

						VALUES('".$cc1tj."',
							'".$fechatj."',
							".$codtj.",
							'".$fichatj."',
							'".$nomtrabtj."',
							".$tratotj.",
							".$param1.",
							".$param2.",
							".$rut_per.",
							".$jornadatj.",
							".$hhtj.",
							".$rendtj.",
							'".$cc1trt."',
							".$cattrt.",
							".$codtrt.",
							".$valtj.",
							".$anteriortj.",
							'".$Obslintj."',
							".$rendt1.",
							".$idLab.",
							".$idLug.",
							".$idTar.",
							".$idPro.",
							".$idUni.",
							'".$detTrato."',
							".$codbonodd.")";

							 //echo $sql;

		executeSql($sql);
		
	}

	echo 'OK';

}elseif($_POST['type']=='close'){
	$plant = $_POST['plant'];

	//Server 3400
	//$date = explode("/", $_POST['date']);
	//$retardDate = $date[1]."/".$date[0]."/".$date[2];

	//local
	$date2=$_POST['date'];
	$date = explode("-", $_POST['date']);
	$retardDate = $date[1]."-".$date[0]."-".$date[2];
	$state = $_POST['state'];

	$usrvbtj = $_SESSION['userId'];
	$fecvbtj = date('d/m/Y');
	$timvbtj = date('H:i:s');
	$llave=1;


	if($state=='A'){
		$usrvbtj = '';
		$fechaFor=strtotime($date2);
		$mes=date("n",$fechaFor);
		$year=date("Y",$fechaFor);

		$sql="SELECT FORMAT(T.fechatj,'yyyy-mm-dd') AS fechatjf, t.rendtj, PE.id_buk2, t.idTar, t.valtj, t.det_trato, t.jornadatj, t.hhtj, t.idLug
				FROM (TARJASBUK2 AS t 
				LEFT JOIN PERSONAL3 AS PE ON PE.rut_per=t.rut_per) 
				WHERE t.cc1tj='".$plant."' 
				AND t.fechatj=#$retardDate# 
				AND t.hhtj>0
				ORDER BY t.rut_per, T.fechatj";
				
				echo $sql;
				//$arrayCen = executeSelect($sql);

				$resLab=0;

	// Devolver las horas extras al abrir tarja
		//
	 for($i=0;$i<count($arrayCen);$i++){ 
		if ($arrayCen[$i]['hhtj']>0){

			$idLug=$arrayCen[$i]['idLug'];
			//  echo "Id Lugar para HHExt ";
			// //  echo $idLug;
			
				$sql4 = "SELECT cc.codigo FROM (CCBuk cc
				left join LugaresBuk as Lg on Lg.id_centro_costo=cc.id)
				WHERE Lg.id_lugar=$idLug";
		
			 	//echo $sql;		
				 $arrayIdCC = executeSelect($sql4);		
				 $idCC=$arrayIdCC[0]['codigo'];
			
			$idBuk2=$arrayCen[$i]['id_buk2'];
			$sql5 = "SELECT * FROM HHextras
	 		WHERE idTrabajador=$idBuk2 and aa=$year and mes=$mes";
			// echo $sql5;
	 		$arrayIdHH = executeSelect($sql5);

			 $cantHoras=0.0;
			 $cantHoras=$arrayIdHH[0]['cantidad']-$arrayCen[$i]['hhtj'];
			 //  echo "Cantidad Horas";
			 //  echo "Id";
			 //  echo $idBuk2;
			 //  echo $arrayIdHH[0]['cantidad'];
			 //  echo $arrayCen[$i]['hhtj'];
			 //   echo $cantHoras;
			 //   echo "fin";
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

	//WHERE YEAR(T.fechatj)=".$year." AND MONTH(T.fechatj)=".$mes." and t.cc1trt='".trim($campoBuk)."' and t.idTar>2 ".$filter."
	executeSql("UPDATE TARJAS
				SET stattj='".$state."',
				usrvbtj='".$usrvbtj."',
				fecvbtj='".$fecvbtj."',
				timvbtj='".$timvbtj."'
				WHERE cc1tj='".$plant."' 
				AND fechatj=#$retardDate#");

	if($plant=='01' || $plant=='05'){
		$llave=0;
	}

	//  echo "Llave";
	//  echo $llave;
	if($state=='C' && $llave>0){

	$fechaFor=strtotime($date2);
	$mes=date("n",$fechaFor);
	$year=date("Y",$fechaFor);

    //  echo "Parametros: ";
	
    //   echo "   ";
	//   echo $year;
	//   echo "   ";
	//   echo $mes;
	//   echo "   ";
	
				$sql="SELECT FORMAT(T.fechatj,'yyyy-mm-dd') AS fechatjf, t.rendtj, PE.id_buk2, t.idTar, t.valtj, t.det_trato, t.jornadatj, t.hhtj, t.idLug
				FROM (TARJASBUK2 AS t 
				LEFT JOIN PERSONAL3 AS PE ON PE.rut_per=t.rut_per) 
				WHERE t.cc1tj='".$plant."' 
				AND t.fechatj=#$retardDate# 
				ORDER BY t.rut_per, T.fechatj";
				
				//echo $sql;
				//$CarCampo=trim($campoBuk);
				$arrayCen = executeSelect($sql);

				$resLab=0;

	// Envio de tarjas del Periodo activo a Buk
		//
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
	//  echo "Id Lugar para HHExt ";
	 
	// //  echo $idLug;

	
		$sql4 = "SELECT cc.codigo FROM (CCBuk cc
		left join LugaresBuk as Lg on Lg.id_centro_costo=cc.id)
		WHERE Lg.id_lugar=$idLug";

	// 	//echo $sql;		
	 	$arrayIdCC = executeSelect($sql4);

	 	$idCC=$arrayIdCC[0]['codigo'];
	// 	// echo " Codigo CC  ";
	// 	// echo $idCC;

		//metodo B
	    // $fec=$arrayCen[$i]['fechatjf'];
		// $sql5 = "SELECT * FROM HHextras
		// WHERE idTrabajador=484 and aa=year(#$fec#) and mes=MONTH(#$fec#)";

		$idBuk2=$arrayCen[$i]['id_buk2'];
	 	$sql5 = "SELECT * FROM HHextras
	 	WHERE idTrabajador=$idBuk2 and aa=$year and mes=$mes";
		// echo $sql5;
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
					
					$sql6 = "INSERT INTO HHextras(aa, mes, idtrabajador, cantidad,
					ultfechaGuardado)
					VALUES(".$year.",
							".$mes.",
							".$idBuk2.",
							".$arrayCen[$i]['hhtj'].",
							'".$arrayCen[$i]['fechatjf']."')";
							
					executeSql($sql6);

		} else {
			$cantHoras=0.0;
			$cantHoras=$arrayIdHH[0]['cantidad']+$arrayCen[$i]['hhtj'];
			//  echo "Cantidad Horas";
			//  echo "Id";
			//  echo $idBuk2;
			//  echo $arrayIdHH[0]['cantidad'];
			//  echo $arrayCen[$i]['hhtj'];
			//   echo $cantHoras;
			//   echo "fin";
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

	// //Envio de Inasistencias
	//WHERE YEAR(T.fechatj)=".$year." AND MONTH(T.fechatj)=".$mes." and t.cc1trt='".trim($campoBuk)."' and t.idTar=0 ".$filter."

	$sqlAus="SELECT FORMAT(T.fechatj,'yyyy-mm-dd') AS fechatjf, t.rendtj, PE.id_buk2, t.idTar, t.valtj, t.det_trato
	FROM (TARJASBUK2 AS t 
	LEFT JOIN PERSONAL3 AS PE ON PE.rut_per=t.rut_per) 
	WHERE t.cc1tj='".$plant."' 
	AND t.fechatj=#$retardDate# 
	and t.idTar=0
	ORDER BY t.rut_per, T.fechatj";

	//echo $sqlAus;
	$arrayAus = executeSelect($sqlAus);

	if (count($arrayAus)>0){
		//echo "  Tiene inasistencias.";
		for($i=0;$i<count($arrayAus);$i++){
		$linkBorrado='https://remuneracionagricola.buk.cl/api/v1/chile/absences/absence?employee_ids='.$arrayAus[$i]['id_buk2'].'&start_date='.$arrayAus[$i]['fechatjf'];
					 $jsonGuideRawDel = bsaleDELETE($linkBorrado, 'H1z6Gc8abmzd217CmKzxbzTj'); 
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
		//echo "  No Tiene inasistencia  ";
	}

	/// Envio de Licencias a Buk
	//WHERE YEAR(T.fechatj)=".$year." AND MONTH(T.fechatj)=".$mes." and t.cc1trt='".trim($campoBuk)."' and t.idTar=1 ".$filter."

	// $sqlLic="SELECT FORMAT(T.fechatj,'yyyy-mm-dd') AS fechatjf, t.rendtj, PE.id_buk2, t.idTar, t.valtj, t.det_trato
	// FROM (TARJASBUK2 AS t 
	// LEFT JOIN PERSONAL3 AS PE ON PE.rut_per=t.rut_per) 
	// WHERE t.cc1tj='".$plant."' 
	// AND t.fechatj=#$retardDate# 
	// and t.idTar=1
	
	// ORDER BY t.rut_per, T.fechatj";

	// //echo $sqlLic;
	// $arrayLic = executeSelect($sqlLic);

	// if (count($arrayLic)>0){
	// 	//echo "  Tiene licencias  ";

	// 	for($i=0;$i<count($arrayLic);$i++){
	// 	$linkBorrado='https://remuneracionagricola.buk.cl/api/v1/chile/absences/licence?employee_ids='.$arrayLic[$i]['id_buk2'].'&start_date='.$arrayLic[$i]['fechatjf'];
	// 	// echo "Link Borrado:   ";
	// 	// echo $linkBorrado;
	// 	$jsonGuideRawDel = bsaleDELETE($linkBorrado, 'H1z6Gc8abmzd217CmKzxbzTj');
	// 	$json2L = array(
	// 	'licence_type_id' => 5,
	// 	'contribution_days' => 0,
	// 	 'format' => "electronica",
	// 	 'type' => "accidente_comun",
	// 	 'start_date' =>$arrayLic[$i]['fechatjf'],
	// 	 'days_count' =>1,
	// 	 'day_percent' => "1",
	// 	 'employee_id' => $arrayLic[$i]['id_buk2'],
	// 	 );
	// 	 $data2L =  json_encode($json2L);
	// 	 $jsonGuideRaw = bsalePOST('https://remuneracionagricola.buk.cl/api/v1/chile/absences/licence', 'H1z6Gc8abmzd217CmKzxbzTj', $data2L);
	// 	 //echo $data2L;
	// 	}

	// 	//extraerLicencias(2022, $mes, $trabajador);
	// 	//envioLicencia($An, $trabajador);
	// } else {
	// 	//echo "  NO Tiene licencias  ";
	// }
	
	// 	/// Envio de Vacaciones a Buk
	// 	//WHERE YEAR(T.fechatj)=".$year." AND MONTH(T.fechatj)=".$mes." and t.cc1trt='".trim($campoBuk)."' and t.idTar=2 ".$filter."

	// 	$sqlVac="SELECT FORMAT(T.fechatj,'yyyy-mm-dd') AS fechatjf, t.rendtj, PE.id_buk2, t.idTar, t.valtj, t.det_trato
	// 	FROM (TARJASBUK2 AS t 
	// 	LEFT JOIN PERSONAL3 AS PE ON PE.rut_per=t.rut_per) 
	//    WHERE t.cc1tj='".$plant."' 
	//     AND t.fechatj=#$retardDate# 
	//     and t.idTar=2
	// 	ORDER BY t.rut_per, T.fechatj";
	
	// 	//echo $sqlVac;
	// 	$arrayVac = executeSelect($sqlVac);
	
	// 	if (count($arrayVac)>0){
	// 		//echo "  Tiene vacaciones registradas  ";
	
	// 		for($i=0;$i<count($arrayVac);$i++){
	// 			$linkBorrado='https://remuneracionagricola.buk.cl/api/v1/chile/vacations?employee_id='.$arrayVac[$i]['id_buk2'].'&start_date='.$arrayAus[$i]['fechatjf'].'&end_date='.$arrayAus[$i]['fechatjf'];
	// 			$jsonGuideRawDel = bsaleDELETE($linkBorrado, 'H1z6Gc8abmzd217CmKzxbzTj'); 
	// 			$json2L = array(
	// 			'type' => "legales",
	// 			'start_date' => $arrayAus[$i]['fechatjf'],
	// 			'end_date' => $arrayAus[$i]['fechatjf'],
	// 			'percent_day' => 1,
	// 			'employee_id' => $arrayVac[$i]['id_buk2'],
	// 		   );
	// 			$data2L =  json_encode($json2L);												
	// 			$jsonGuideRaw = bsalePOST('https://remuneracionagricola.buk.cl/api/v1/chile/vacations', 'H1z6Gc8abmzd217CmKzxbzTj', $data2L);
	// 			//echo $data2L;
	// 		}
	
	// 		//extraerLicencias(2022, $mes, $trabajador);
	// 		//envioLicencia($An, $trabajador);
	// 	} else {
	// 		//echo "  NO Tiene vacaciones  "; }
	//  	}
	}			

	echo 'OK';


}elseif($_POST['type']=='repeat'){
	$plant = $_POST['plant'];
	/*if($plant<10){
		$plant = "0".$plant;
	}*/
	
	// echo "Fecha   ";
	// echo $_POST['date'];
	

	// fecha access nuevas versiones
	//$retardDate = explode("-", $_POST['date']);
	$date = explode("-", $_POST['date']);
	$retardDate = $date[1]."-".$date[0]."-".$date[2];

	//Fechas access server :3400
	//$date = explode("/", $_POST['date']);
	//$retardDate = $date[1]."/".$date[0]."/".$date[2];


	$sql = "SELECT *,
			FORMAT(fechatj,'mm/dd/yyyy') AS tally_date
			FROM TARJAS
			WHERE cc1tj='$plant'
			AND NOT stattj='X' 
			ORDER BY fechatj DESC";
	//echo $sql;
	$arrayTallyOld = executeSelect($sql);

	$sql = "SELECT * FROM TARJASBUK2 WHERE cc1tj='$plant' AND fechatj=#".$arrayTallyOld[0]['tally_date']."#";
	//echo $sql;

	$arrayTallyDetailOld = executeSelect($sql);

	$cc1tj = $plant;
	$fechatj = $_POST['date'];
	$ultlintj = count($arrayTallyDetailOld);
	$usremitj = $_SESSION['userId'];
	$fecemitj = date('d/m/Y');
	$timemitj = date('H:i:s');
	$usrvbtj = '';
	$fecvbtj = date('d/m/Y');
	$timvbtj = date('H:i:s');
	$obstj = '';
	$stattj = 'A';

	$sql = "INSERT INTO TARJAS(cc1tj,
							fechatj,
							ultlintj,
							usremitj,
							fecemitj,
							timemitj,
							usrvbtj,
							fecvbtj,
							timvbtj,
							obstj,
							stattj)

							VALUES('".$cc1tj."',
							'".$fechatj."',
							".$ultlintj.",
							'".$usremitj."',
							'".$fecemitj."',
							'".$timemitj."',
							'".$usrvbtj."',
							'".$fecvbtj."',
							'".$timvbtj."',
							'".$obstj."',
							'".$stattj."')
							";
							
	executeSql($sql);
	
	$sql = "INSERT INTO TARJASBUK2(cc1tj,
							fechatj,
							codtj,
							fichatj,
							nomtrabtj,
							tratotj,
							param1,
							param2,
							rut_per,
							jornadatj,
							hhtj,
							rendtj,
							cc2tj,
							cc3tj,
							cc4tj,
							cc2,
							cc3,
							cc4,
							cc1trt,
							cattrt,
							codtrt,
							valtj,
							anteriortj,
							Obslintj,
							rendt1,
							idLab,
							idLug,
							idTar,
							idPro,
							idUni,
							det_trato,
							codbonodd)
					SELECT cc1tj,
							'".$fechatj."',
							codtj,
							fichatj,
							nomtrabtj,
							tratotj,
							param1,
							param2,
							rut_per,
							jornadatj,
							0,
							0,
							cc2tj,
							cc3tj,
							cc4tj,
							cc2,
							cc3,
							cc4,
							cc1trt,
							cattrt,
							codtrt,
							valtj,
							anteriortj,
							Obslintj,
							rendt1,
							idLab,
							idLug,
							idTar,
							idPro,
							idUni,
							det_trato,
							codbonodd
						FROM TARJASBUK2
						WHERE cc1tj='$plant' AND fechatj=#".$arrayTallyOld[0]['tally_date']."#";
						//echo $sql;

	executeSql($sql);
	echo 'OK';


}elseif($_POST['type']=='delete'){

	$plant = $_POST['plant'];

	//Server 3400
	//$date = explode("/", $_POST['date']);
	//$retardDate = $date[1]."/".$date[0]."/".$date[2];

	//local
	$date = explode("-", $_POST['date']);
	$retardDate = $date[1]."-".$date[0]."-".$date[2];

	$array = executeSelect("SELECT * FROM TARJAS
							WHERE cc1tj='".$plant."' 
							AND fechatj=#$retardDate#");

	if($_SESSION['profile']=='ADM' || $array[0]['stattj']=='A'){

		//RESPALDO DE TARJAS DE TRABAJADORES
		$sql = "INSERT INTO TARJAS1_HISTORIAL(cc1tj,
								fechatj,
								codtj,
								fichatj,
								nomtrabtj,
								tratotj,
								param1,
								param2,
								rut_per,
								jornadatj,
								hhtj,
								rendtj,
								cc2tj,
								cc3tj,
								cc4tj,
								cc2,
								cc3,
								cc4,
								cc1trt,
								cattrt,
								codtrt,
								valtj,
								anteriortj,
								Obslintj,
								rendt1,
								idLab,
								idLug,
								idTar,
								idPro,
								idUni,
								codbonodd)
						SELECT cc1tj,
								fechatj,
								codtj,
								fichatj,
								nomtrabtj,
								tratotj,
								param1,
								param2,
								rut_per,
								jornadatj,
								hhtj,
								rendtj,
								cc2tj,
								cc3tj,
								cc4tj,
								cc2,
								cc3,
								cc4,
								cc1trt,
								cattrt,
								codtrt,
								valtj,
								anteriortj,
								Obslintj,
								rendt1,
								idLab,
								idLug,
								idTar,
								idPro,
								idUni,
								codbonodd
							FROM TARJASBUK2
							WHERE cc1tj='".$plant."' 
							AND fechatj=#$retardDate#";
		executeSql($sql);

		executeSql("DELETE FROM TARJASBUK2
					WHERE cc1tj='".$plant."' 
					AND fechatj=#$retardDate#");

		executeSql("DELETE FROM TARJAS
					WHERE cc1tj='".$plant."' 
					AND fechatj=#$retardDate#");

		echo 'OK';
	}elseif($_SESSION['profile']!='ADM'){
		echo 'NO_ADMIN';
	}

}elseif($_POST['type']=='savePersonal'){
	$plant = $_POST['plant'];
	$month = $_POST['month'];
	$year = $_POST['year'];
	$rut = $_POST['rut'];
	$name = $_POST['name'];

	/*$date = explode("/", $_POST['date']);
	$retardDate = $date[1]."/".$date[0]."/".$date[2];*/

	$list = $_POST['list'];

	/*executeSql("DELETE FROM TARJAS
				WHERE cc1tj='".$plant."' 
				AND fechatj=#$retardDate#");*/

	/*$arrayOriginalTally = executeSelect("SELECT * FROM TARJAS
										WHERE cc1tj='".$plant."' 
										AND fechatj=#$retardDate#");*/

	//RESPALDO DE TARJAS DE TRABAJADORES
	$sql = "INSERT INTO TARJAS1_HISTORIAL(cc1tj,
							fechatj,
							codtj,
							fichatj,
							nomtrabtj,
							tratotj,
							param1,
							param2,
							rut_per,
							jornadatj,
							hhtj,
							rendtj,
							cc2tj,
							cc3tj,
							cc4tj,
							cc2,
							cc3,
							cc4,
							cc1trt,
							cattrt,
							codtrt,
							valtj,
							anteriortj,
							Obslintj,
							rendt1,
							idLab,
							idLug,
							idTar,
							idPro,
							idUni,
							det_trato,
							codbonodd)
					SELECT cc1tj,
							fechatj,
							codtj,
							fichatj,
							nomtrabtj,
							tratotj,
							param1,
							param2,
							rut_per,
							jornadatj,
							hhtj,
							rendtj,
							cc2tj,
							cc3tj,
							cc4tj,
							cc2,
							cc3,
							cc4,
							cc1trt,
							cattrt,
							codtrt,
							valtj,
							anteriortj,
							Obslintj,
							rendt1,
							idLab,
							idLug,
							idTar,
							idPro,
							idUni,
							det_trato,
							codbonodd
						FROM TARJASBUK2
						WHERE cc1tj='$plant'
						AND MONTH(fechatj)=$month
						AND YEAR(fechatj)=$year
						AND rut_per=$rut";

	executeSql($sql);

	
	$arrayList = explode('&&&&', $list);
	
	$cc1tj = $plant;
	/*$fechatj = $retardDate;
	$ultlintj = count($arrayList);
	$usremitj = $_SESSION['userId'];
	$fecemitj = date('d/m/Y');
	$timemitj = date('H:i:s');
	$usrvbtj = '';
	$fecvbtj = date('d/m/Y');
	$timvbtj = date('H:i:s');
	$obstj = '';
	$stattj = 'A';*/

// BORRADO PREVIO DE TARJAS

	  $sql = "DELETE * FROM TARJASBUK2
	  		WHERE cc1tj='$plant'
	  		AND MONTH(fechatj)=$month
	  		AND YEAR(fechatj)=$year
	  		AND rut_per=$rut";

	executeSql($sql);
	$cantidadHoras=0;

	for($i=0;$i<count($arrayList);$i++){
		$arrayListDetail = explode('&&', $arrayList[$i]);

		/*$date = explode("/", $arrayListDetail[1]);
		$retardDate = $date[1]."/".$date[0]."/".$date[2];
		$fechatj = $retardDate;*/
		$fechatj = $arrayListDetail[1];

		$codtj = $arrayListDetail[0];
		$fichatj = $rut;
		if(strlen($fichatj)==7) $fichatj="   ".$fichatj;
		if(strlen($fichatj)==8) $fichatj="  ".$fichatj;
		if(strlen($fichatj)==9) $fichatj=" ".$fichatj;
		$nomtrabtj = $name;
		$tratotj = 0;
		$param1 = 0;
		$param2 = 0;
		$rut_per = $rut;
		// $jornadatj = $arrayListDetail[5];
		// $hhtj = $arrayListDetail[7];
		// $rendtj = $arrayListDetail[6];
		
		 $cc1trt = $plant;
		// $cattrt = $arrayListDetail[5];
		// $codtrt = $arrayListDetail[6];
		//$siTj=$arrayListDetail[10];
		$anteriortj = 1; //???
		$Obslintj = '';
		$codbonodd = 0;
		$anteriortj = 1; //???
		$Obslintj = '';
		$rendt1 = 0;
		$codbonodd = 0;
		$detTrato=$arrayListDetail[2];
		$idTar=$arrayListDetail[3];
		$valtj = $arrayListDetail[4];
		$sql3="SELECT id_buk2 FROM personal3
		WHERE rut_per=$rut_per";
		$arrayIdPer = executeSelect($sql3);
		$idBuk2=$arrayIdPer[0]['id_buk2'];

		//  echo "Codigo Tarifa :";
	 	//  echo  $idTar;
		if ($idTar>2){
		$sql2 = "SELECT codigo_labor, codigo_lugar, codigo_producto, codigo_unidad FROM TarifasBuk
		WHERE id_tarifa_buk=$idTar";

		//echo $sql;		
		$arrayIds = executeSelect($sql2);

		$idLab=$arrayIds[0]['codigo_labor'];
		$idLug=$arrayIds[0]['codigo_lugar'];
		$idPro=$arrayIds[0]['codigo_producto'];
		$idUni=$arrayIds[0]['codigo_unidad'];
		$jornadatj = $arrayListDetail[7];
		$hhtj = $arrayListDetail[9];
		$rendtj = $arrayListDetail[8];
		if ($jornadatj==1){
			$porcentajeWork=100;
		} else if($jornadatj==0.5){
			$porcentajeWork=50;
		}

		//if ($siTj==0){
			// echo "Prueba Id Labor";
			// echo $idLab;
			$json = array(
				'daily_base_floor' => true,  //Piso base Diario
				'discount_day_amount' => false,
			 'overwrite_existing' => true, //Que sobreescriba registros existentes
				'calculate_total_pay' => true,  // Calcular el pago total
				'sync_attendance' => 0,
				'day' => $fechatj,
				'employee_id' => $idBuk2,
				'worklogs' => array(
					array(
						'piecework_execution_id' => $idTar,
						'production' => round($rendtj,1),
						'rate_value' => round($valtj,1),
						'saved_in' => "porcentaje",
						'total_pay' => round(0,1),
						'work_type' => $detTrato,
						'worked_units' => $porcentajeWork
					)
				)
			);
		   //echo $json;
			$data =  json_encode($json);
			//$jsonGuideRaw = bsalePOST('https://remuneracionagricola.buk.cl/api/v1/chile/piecework/worklogs', 'H1z6Gc8abmzd217CmKzxbzTj', $data);	
			//echo $data;
		// 	$siTj=1;
		// } else {
	
			// {
			// 	"daily_base_floor": false,
			// 	"discount_day_amount": false,
			// 	"calculate_total_pay": false,
			// 	"sync_attendance": 0,
			// 	"monetary_floor": 0,
			// 	"worklog": {
			// 	  "day": "2022-02-02",
			// 	  "work_type": "dia",
			// 	  "worked_units": 5,
			// 	  "production": 2,
			// 	  "total_pay": 100,
			// 	  "rate_value": 3
			// 	}
			//   }
			//'employee_id' => $idBuk2,
			// echo "Entro al PUT   ";
			// echo $idBuk2;
	
			// $json = array(
			// 	'daily_base_floor' => true,  //Piso base Diario
			// 	'discount_day_amount' => false,
			// 	'calculate_total_pay' => true,  // Calcular el pago total
			// 	'sync_attendance' => 0,
			// 	'worklog' => array(
			// 		array(
			// 			'day' => $fechatj,
			// 			'work_type' => $detTrato,
			// 			'worked_units' => $porcentajeWork,
			// 			'production' => round($rendtj,1),
			// 			'total_pay' => round(0,1),
			// 			'rate_value' => round($valtj,1)	
			// 		)
			// 	)
			// );
			//    //echo $json;
			// $data =  json_encode($json);
			//$jsonGuideRaw = bsalePUT('https://remuneracionagricola.buk.cl/api/v1/chile/piecework/worklogs/'.$idBuk2, 'H1z6Gc8abmzd217CmKzxbzTj', $data);	
			//echo $data;
		//}
		//echo $data;
		// echo "Valor HH: ";
		// echo $hhtj;

		// echo "C HH  ";
		// echo $cantidadHoras;

	//    if ($hhtj>0){
   
		   
	// 	   $sql4 = "SELECT cc.codigo FROM (CCBuk cc
	// 	   left join LugaresBuk as Lg on Lg.id_centro_costo=cc.id)
	// 	   WHERE Lg.id_lugar=$idLug";
   
	// 	   //echo $sql;		
	// 	   $arrayIdCC = executeSelect($sql4);
   
	// 	   $idCC=$arrayIdCC[0]['codigo'];
	// 	//    echo " Codigo CC  ";
	// 	//    echo $idCC;
   
	// 	   $sql5 = "SELECT * FROM HHextras
	// 	   WHERE idTrabajador=$idBuk2 and aa=$year and mes=$month";
	// 	   $arrayIdHH = executeSelect($sql5);
   
   
	// 	//    echo "Reg. HH Extras   ";
	// 	//    echo count($arrayIdHH);
	// 	   if (count($arrayIdHH)==0){
   
	// 	   $linkHH='https://remuneracionagricola.buk.cl/api/v1/chile/attendances/overtime';
	// 				   //$jsonGuideRawDel = bsaleDELETE($linkBorrado, 'H1z6Gc8abmzd217CmKzxbzTj'); 
	// 					$jsonHH = array(
	// 					'month' => $month,
	// 					'year' => $year,
	// 					'hours' => $hhtj,
	// 					'employee_id' => $idBuk2,
	// 					'type_id' => 1,
	// 					'centro_costo' => $idCC,
	// 					);
	// 					$dataHH = json_encode($jsonHH);
	// 					// $jsonGuideRaw = bsalePOST($linkHH, 'H1z6Gc8abmzd217CmKzxbzTj', $dataHH);
	// 					//echo $dataHH;
					   
	// 				   $sql6 = "INSERT INTO HHextras(aa,
	// 				   mes,
	// 				   idtrabajador,
	// 				   ultfechaGuardado)
	// 				   VALUES(".$year.",
	// 						   ".$month.",
	// 						   ".$idBuk2.",
	// 						   '".$fechatj."')";
							   
	// 				   //executeSql($sql6);
   
	// 	   } else {
   
	// 		   $linkHH='https://remuneracionagricola.buk.cl/api/v1/chile/attendances/overtime';
	// 		   //$jsonGuideRawDel = bsaleDELETE($linkBorrado, 'H1z6Gc8abmzd217CmKzxbzTj'); 
	// 			$jsonHH = array(
	// 			'month' => $month,
	// 			'year' => $year,
	// 			'hours' => $hhtj,
	// 			'employee_id' => $idBuk2,
	// 			'type_id' => 1,
	// 			'centro_costo' => $idCC,
	// 			);
	// 			$dataHH = json_encode($jsonHH);
	// 			//$jsonGuideRaw = bsalePUT($linkHH, 'H1z6Gc8abmzd217CmKzxbzTj', $dataHH);
	// 			//echo $dataHH;
	// 	   }
	//    }

		} else {
			$idLab=0;
			$idLug=0;
			$idPro=0;
			$idUni=0;	
			$jornadatj = $arrayListDetail[7];
			$hhtj = 0.0;
			$rendtj = 0.0;

			if ($idTar==0){   //Post x Insistancia

				// 'application_date' => $arrayIna[$i]['fechatjf'],
				//  'day_percent' => "1",
					$linkBorrado='https://remuneracionagricola.buk.cl/api/v1/chile/absences/absence?employee_ids='.$idBuk2.'&start_date='.$fechatj;
					//$jsonGuideRawDel = bsaleDELETE($linkBorrado, 'H1z6Gc8abmzd217CmKzxbzTj'); 
					 $jsonIna = array(
					 'start_date' => $fechatj,
					 'days_count' => 1,
					 'justification' => "injustificada",
					 'employee_id' => $idBuk2,
					 'absence_type_id' => 8,
					 );
					 $dataIna = json_encode($jsonIna);
					 //$jsonGuideRaw = bsalePOST('https://remuneracionagricola.buk.cl/api/v1/chile/absences/absence', 'H1z6Gc8abmzd217CmKzxbzTj', $dataIna);
					 //echo $dataIna;
			}
			if ($idTar==1){  //Post x Licencia
				//'application_date' => $arrayLj[$i]['fechatjf'],
				//'justification' => "licencia valida",
				    $linkBorrado='https://remuneracionagricola.buk.cl/api/v1/chile/absences/licence?employee_ids='.$idBuk2.'&start_date='.$fechatj;
					// echo "Link Borrado:   ";
					// echo $linkBorrado;
					//$jsonGuideRawDel = bsaleDELETE($linkBorrado, 'H1z6Gc8abmzd217CmKzxbzTj');
					$json2L = array(
					'licence_type_id' => 5,
					'contribution_days' => 0,
					 'format' => "electronica",
					 'type' => "accidente_comun",
					 'start_date' => $fechatj,
					 'days_count' =>1,
					 'day_percent' => "1",
					 'employee_id' => $idBuk2,
					 );
					 $data2L =  json_encode($json2L);
					 //$jsonGuideRaw = bsalePOST('https://remuneracionagricola.buk.cl/api/v1/chile/absences/licence', 'H1z6Gc8abmzd217CmKzxbzTj', $data2L);
					 //echo $data2L;
		}
		// if ($idTar==2){   //Post x Vacaciones
		// 		$linkBorrado='https://remuneracionagricola.buk.cl/api/v1/chile/absences/absence?employee_ids='.$idBuk2.'&start_date='.$fechatj.'&end_date='.$fechatj;
		// 		//$jsonGuideRawDel = bsaleDELETE($linkBorrado, 'H1z6Gc8abmzd217CmKzxbzTj'); 
		// 		$json2L = array(
		// 		'type' => "legales",
		// 		'start_date' => $fechatj,
		// 		'end_date' => $fechatj,
		// 		'percent_day' => 1,
		// 		'employee_id' => $idBuk2,
		// 	   );
		// 		$data2L =  json_encode($json2L);												
		// 		//$jsonGuideRaw = bsalePOST('https://remuneracionagricola.buk.cl/api/v1/chile/vacations', 'H1z6Gc8abmzd217CmKzxbzTj', $data2L);
		// 		//echo $data2L;
		// }	


		}

		$sql = "INSERT INTO TARJASBUK2(cc1tj,
							fechatj,
							codtj,
							fichatj,
							nomtrabtj,
							tratotj,
							param1,
							param2,
							rut_per,
							jornadatj,
							hhtj,
							rendtj,
							cc1trt,
							valtj,
							anteriortj,
							Obslintj,
							rendt1,
							idLab,
							idLug,
							idTar,
							idPro,
							idUni,
							det_trato,
							codbonodd)

						VALUES('".$cc1tj."',
							'".$fechatj."',
							".$codtj.",
							'".$fichatj."',
							'".$nomtrabtj."',
							".$tratotj.",
							".$param1.",
							".$param2.",
							".$rut_per.",
							".$jornadatj.",
							".$hhtj.",
							".$rendtj.",
							'".$cc1trt."',
							".$valtj.",
							".$anteriortj.",
							'".$Obslintj."',
							".$rendt1.",
							".$idLab.",
							".$idLug.",
							".$idTar.",
							".$idPro.",
							".$idUni.",
							'".$detTrato."',
							".$codbonodd.")";
//echo $sql;
		/*$sql = "UPDATE TARJAS1 SET
					tratotj=".$tratotj.",
					param1=".$param1.",
					param2=".$param2.",
					jornadatj=".$jornadatj.",
					hhtj=".$hhtj.",
					rendtj=".$rendtj.",
					cc2tj='".$cc2tj."',
					cc3tj='".$cc3tj."',
					cc4tj='".$cc4tj."',
					cc2='".$cc2."',
					cc3='".$cc3."',
					cc4='".$cc4."',
					cc1trt='".$cc1trt."',
					cattrt=".$cattrt.",
					codtrt=".$codtrt.",
					valtj=".$valtj.",
					anteriortj=".$anteriortj.",
					Obslintj='".$Obslintj."',
					rendt1=".$rendt1.",
					codbonodd=".$codbonodd."
				WHERE cc1tj='$plant'
				AND fechatj=#$retardDate#
				AND rut_per=$rut";*/

		executeSql($sql);
		
	}

	// {
	// 	"month": 0,
	// 	"year": 0,
	// 	"hours": 0,
	// 	"employee_id": 0,
	// 	"type_id": 0,
	// 	"centro_costo": "string"
	//   }
	
	
	

	echo 'OK';

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
	//echo $response;
    return $response;
}

// Delete de Json x Post
function bsaleDELETE($url, $access_token){

    // Inicia cURL
    $session = curl_init($url);


    // Indica a cURL que retorne data
    //curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false );
    // Configura cabeceras
    $headers = array(
        'auth_token: '.$access_token,
        'Accept: application/json',
        'Content-Type: application/json'
    );
	curl_setopt($session, CURLOPT_CUSTOMREQUEST, "DELETE");
	//curl_setopt($session, CURLOPT_POSTFIELDS, $data);
    curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
 	// Indica que se va ser una petición DELETE
    //curl_setopt($session, CURLOPT_CURLOPT_CUSTOMREQUEST, "DELETE");
	
	//curl_setopt($session, CURLOPT_DELETE, true);
	curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    // Agrega parámetros
    
    
    // Ejecuta cURL
    $response = curl_exec($session);

 	if ($response === false) $response = curl_error($session);
	
    // Cierra la sesión cURL
    curl_close($session);
	
    //Esto es sólo para poder visualizar lo que se está retornando
	//echo $response;
    return $response;
}

// Actualizacion de Json x Post
function bsalePUT($url, $access_token, $data){

    // Inicia cURL
    $session = curl_init($url);


    // Indica a cURL que retorne data
	
   
	curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false );
    // Configura cabeceras
    $headers = array(
        'auth_token: '.$access_token,
        'Accept: application/json',
        'Content-Type: application/json'
    );
	curl_setopt($session, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
 	// Indica que se va ser una petición POST
    //curl_setopt($session, CURLOPT_PUT, true);

    // Agrega parámetros
    curl_setopt($session, CURLOPT_POSTFIELDS, $data);
    
    // Ejecuta cURL
    $response = curl_exec($session);

 	if ($response === false) $response = curl_error($session);
	
    // Cierra la sesión cURL
    curl_close($session);
	
    //Esto es sólo para poder visualizar lo que se está retornando
	//echo $response;
    return $response;
}
?>