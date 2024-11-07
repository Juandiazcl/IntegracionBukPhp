<?php
include("../connection/connection.php");

set_time_limit(500);


if($_POST['type']=='generate'){

	$list = $_POST['list'];
	$listCC = $_POST['listCostCenter'];

	executeSql("DELETE FROM REM02_TEMPORAL");

	$arrayList = explode(',', $list);
	$arrayListCC = explode(',', $listCC);

	for($i=0;$i<count($arrayList);$i++){
		executeSql("INSERT INTO REM02_TEMPORAL(rut,cc1rem) VALUES(".$arrayList[$i].",".$arrayListCC[$i].")");
	}

	echo 'OK';

}elseif($_POST['type']=='clean'){
	$list = $_POST['list'];
	$listCostCenter = $_POST['listCostCenter'];
	$listSettlement = $_POST['listSettlement'];
	$year = $_POST['year'];
	$month = $_POST['month'];

	$arrayList = explode(',', $list);
	$arrayListCC = explode(',', $listCostCenter);
	$arrayListSettlement = explode(',', $listSettlement);

	for($i=0;$i<count($arrayList);$i++){
		$whereSettlement = "AND (ID_FINIQUITO_PERSONAL=0 OR ID_FINIQUITO_PERSONAL IS NULL)";
		if($arrayListSettlement[$i]!=0){
			$whereSettlement = "AND ID_FINIQUITO_PERSONAL=".$arrayListSettlement[$i];
		}

		//Limpieza registro antiguo
		executeSql("DELETE FROM REM02
					WHERE VAL(rutrem)=".$arrayList[$i]." 
					AND aaaarem=".$year." 
					AND mmrem=".$month." 
					AND VAL(cc1rem)=".$arrayListCC[$i]."
					".$whereSettlement);

		executeSql("DELETE FROM REM021
					WHERE VAL(rutrem)=".$arrayList[$i]." 
					AND aaaarem=".$year." 
					AND mmrem=".$month." 
					AND VAL(cc1rem)=".$arrayListCC[$i]."
					".$whereSettlement."
					AND NOT codhdrem IN
					('H007','H008','H017','H018','H019','H020','H040','H160','D015','D026','D027','D028','D029','D030','D031','D054','D055','D121','D122','D123','D150','D151','D155')");
					
	}

	echo 'OK';

}elseif($_POST['type']=='calculate'){

	$list = $_POST['list'];
	$listCostCenter = $_POST['listCostCenter'];
	$year = $_POST['year'];
	$month = $_POST['month'];
	$settlement = $_POST['settlement'];
	//executeSql("DELETE FROM REM02_TEMPORAL");
	$finalSql = "";


	$arrayList = explode(',', $list);
	$arrayListCC = explode(',', $listCostCenter);

	$arrayLiq = executeSelect("SELECT 1 AS Tipo, * FROM HYD WHERE codhd LIKE '%P%'
								UNION ALL
								SELECT 2 AS Tipo, * FROM HYD WHERE codhd LIKE '%H%'
								UNION ALL
								SELECT 3 AS Tipo, * FROM HYD WHERE codhd LIKE '%D%'
								ORDER BY Tipo, codhd");

	//Carga de topes legales
	$c000 = 0;
	$c001 = 0;
	$c006 = 0;
	$c007 = 0;
	$c008 = 0;
	$c009 = 0;
	$c010 = 0;
	$c011 = 0;
	$c100 = 0;
	$c101 = 0;
	$arrayCyt = executeSelect("SELECT *	FROM CYTMES1 WHERE cytaa=$year AND cytmm=$month");
	for($c=0;$c<count($arrayCyt);$c++){
		if($arrayCyt[$c]['codc']=='c000'){
			$c000 = $arrayCyt[$c]['ctnum6'];
		}
		if($arrayCyt[$c]['codc']=='c001'){
			$c001 = $arrayCyt[$c]['ctnum6'];
		}
		if($arrayCyt[$c]['codc']=='c006'){
			$c006 = $arrayCyt[$c]['ctnum6'];
		}
		if($arrayCyt[$c]['codc']=='c007'){
			$c007 = $arrayCyt[$c]['ctnum6'];
		}
		if($arrayCyt[$c]['codc']=='c008'){
			$c008 = $arrayCyt[$c]['ctnum6'];
		}
		if($arrayCyt[$c]['codc']=='c009'){
			$c009 = $arrayCyt[$c]['ctnum6'];
		}
		if($arrayCyt[$c]['codc']=='c010'){
			$c010 = $arrayCyt[$c]['ctnum6'];
		}
		if($arrayCyt[$c]['codc']=='c011'){
			$c011 = $arrayCyt[$c]['ctnum6'];
		}
		if($arrayCyt[$c]['codc']=='c100'){
			$c100 = $arrayCyt[$c]['ctnum6'];
		}
		if($arrayCyt[$c]['codc']=='c101'){
			$c101 = $arrayCyt[$c]['ctnum6'];
		}
	}

	for($i=0;$i<count($arrayList);$i++){
		//Limpieza registro antiguo
		/*executeSql("DELETE FROM REM02
					WHERE VAL(rutrem)=".$arrayList[$i]." 
					AND aaaarem=".$year." 
					AND mmrem=".$month." 
					AND cc1rem='".$arrayListCC[$i]."'");

		executeSql("DELETE FROM REM021
					WHERE VAL(rutrem)=".$arrayList[$i]." 
					AND aaaarem=".$year." 
					AND mmrem=".$month." 
					AND cc1rem='".$arrayListCC[$i]."'");*/

		//Carga de datos personales
		//FORMAT(fecvig_per,'dd/mm/yyyy') AS InicioContrato,
		$fromTable = "FROM PERSONAL WHERE";
		$whereManual = "";
		if($settlement!=0){
			$fromTable = "FROM PERSONAL_HISTORICO WHERE ID_FINIQUITO_PERSONAL=".$settlement." AND ";
			$whereManual = "AND ID_FINIQUITO_PERSONAL=".$settlement;
		}

		$sql = "SELECT *,
				FORMAT(fecing_per,'dd/mm/yyyy') AS InicioContrato,
				fecing_per,
				FORMAT(fecter_per,'dd/mm/yyyy') AS TerminoContrato
				".$fromTable." rut_per=".$arrayList[$i];

		$arrayPersonal = executeSelect($sql);
		$rutrem = strval($arrayList[$i]);
		if(strlen($rutrem)==6){
			$rutrem = "    ".$rutrem;
		}elseif(strlen($rutrem)==7){
			$rutrem = "   ".$rutrem;
		}elseif(strlen($rutrem)==8){
			$rutrem = "  ".$rutrem;
		}

		//Carga de datos cargas familiares
		/*$arrayPersonal2 = executeSelect("SELECT * FROM PERSONAL2 WHERE rut_per=".$arrayList[$i]."
										AND (YEAR(vigdesde_carg)<".$year." OR (YEAR(vigdesde_carg)=".$year." AND MONTH(vigdesde_carg)<".$month."))
										AND	(YEAR(vighasta_carg)>".$year." OR (YEAR(vighasta_carg)=".$year." AND MONTH(vighasta_carg)>=".$month."))");*/

		$arrayPersonal2 = executeSelect("SELECT * FROM PERSONAL2 WHERE rut_per=".$arrayList[$i]." AND stat_carg='V'");
		
		
		//Sólo para valor P015
		$arrayPersonal2b = executeSelect("SELECT * FROM PERSONAL2 WHERE rut_per=".$arrayList[$i]);
		

		//Carga de calendario
		$arrayCalendar = executeSelect("SELECT * FROM CALENDARIO WHERE cmm=".$month." AND caaaa=".$year);
		$calHab1 = 0; //Se sumarán para días hábiles trabajados
		$calHab2 = 0;
		$calHab3 = 0;
		$calHab4 = 0;
		$calHab5 = 0;
		$calHab6 = 0;

		//Carga de tarjas
		$dateStart = explode("/", $arrayPersonal[0]["InicioContrato"]);
		$retardDateStart = $dateStart[1]."/".$dateStart[0]."/".$dateStart[2];
		$dateEnd = explode("/", $arrayPersonal[0]["TerminoContrato"]);
		$retardDateEnd = $dateEnd[1]."/".$dateEnd[0]."/".$dateEnd[2];
		$dateWhere = " AND t.fechatj BETWEEN #$retardDateStart# AND #$retardDateEnd#";
		if($arrayPersonal[0]["estado_per"]=='V'){
			$dateWhere = " AND t.fechatj >= #$retardDateStart#";
		}

		$sql = "SELECT *, t1.fechatj AS fechaTarja 
				FROM TARJAS1 t1
				LEFT JOIN TARJAS t ON t.cc1tj=t1.cc1tj
				WHERE VAL(t1.rut_per)=".$arrayList[$i]." AND VAL(t1.cc1tj)=".$arrayListCC[$i]."
				AND MONTH(t1.fechatj)=".$month." AND YEAR(t1.fechatj)=".$year." 
				AND t.fechatj=t1.fechatj
				".$dateWhere."
				ORDER BY t1.fechatj";
//echo $sql;
		$arrayTally = executeSelect($sql);
		
		
		//Carga de datos manuales
		$manualH007 = 0;
		$manualH008 = 0;
		$manualH017 = 0;
		$manualH018 = 0;
		$manualH019 = 0;
		$manualH020 = 0;
		$manualH040 = 0;
		$manualH160 = 0;
		$manualD015 = 0;
		$manualD026 = 0;
		$manualD027 = 0;
		$manualD028 = 0;
		$manualD029 = 0;
		$manualD030 = 0;
		$manualD031 = 0;
		$manualD054 = 0;
		$manualD055 = 0;
		$manualD121 = 0;
		$manualD122 = 0;
		$manualD123 = 0;
		$manualD150 = 0;
		$manualD151 = 0;
		$manualD155 = 0;

		/*$arrayManual = executeSelect("SELECT * FROM REM021_MANUAL 
									WHERE VAL(rutrem)=".$arrayList[$i]."
									AND aaaarem=".$year."
									AND mmrem=".$month."
									AND VAL(cc1rem)=".$arrayListCC[$i]);*/

		$arrayManual = executeSelect("SELECT * FROM REM021
									WHERE VAL(rutrem)=".$arrayList[$i]." 
									AND aaaarem=".$year." 
									AND mmrem=".$month." 
									AND VAL(cc1rem)=".$arrayListCC[$i]."
									".$whereManual."
									AND codhdrem IN
									('H007','H008','H017','H018','H019','H020','H040','H160',
									'D015','D026','D027','D028','D029','D030','D031','D054',
									'D055','D121','D122','D123','D150','D151','D155')");

		for($m=0;$m<count($arrayManual);$m++){
			switch($arrayManual[$m]["codhdrem"]){
				case "H007":
					$manualH007 = $arrayManual[$m]['valrem2'];
				break;
				case "H008":
					$manualH008 = $arrayManual[$m]['valrem2'];
				break;
				case "H017":
					$manualH017 = $arrayManual[$m]['valrem2'];
				break;
				case "H018":
					$manualH018 = $arrayManual[$m]['valrem2'];
				break;
				case "H019":
					$manualH019 = $arrayManual[$m]['valrem2'];
				break;
				case "H020":
					$manualH020 = $arrayManual[$m]['valrem2'];
				break;
				case "H040":
					$manualH040 = $arrayManual[$m]['valrem2'];
				break;
				case "H160":
					$manualH160 = $arrayManual[$m]['valrem2'];
				break;
				case "D015":
					$manualD015 = $arrayManual[$m]['valrem2'];
				break;
				case "D026":
					$manualD026 = $arrayManual[$m]['valrem2'];
				break;
				case "D027":
					$manualD027 = $arrayManual[$m]['valrem2'];
				break;
				case "D028":
					$manualD028 = $arrayManual[$m]['valrem2'];
				break;
				case "D029":
					$manualD029 = $arrayManual[$m]['valrem2'];
				break;
				case "D030":
					$manualD030 = $arrayManual[$m]['valrem2'];
				break;
				case "D031":
					$manualD031 = $arrayManual[$m]['valrem2'];
				break;
				case "D054":
					$manualD054 = $arrayManual[$m]['valrem2'];
				break;
				case "D055":
					$manualD055 = $arrayManual[$m]['valrem2'];
				break;
				case "D121":
					$manualD121 = $arrayManual[$m]['valrem2'];
				break;
				case "D122":
					$manualD122 = $arrayManual[$m]['valrem2'];
				break;
				case "D123":
					$manualD123 = $arrayManual[$m]['valrem2'];
				break;
				case "D150":
					$manualD150 = $arrayManual[$m]['valrem2'];
				break;
				case "D151":
					$manualD151 = $arrayManual[$m]['valrem2'];
				break;
				case "D155":
					$manualD155 = $arrayManual[$m]['valrem2'];
				break;
			}
		}

		//LIMPIEZA Registros Manuales
		executeSql("DELETE * FROM REM021
					WHERE VAL(rutrem)=".$arrayList[$i]." 
					AND aaaarem=".$year." 
					AND mmrem=".$month." 
					AND VAL(cc1rem)=".$arrayListCC[$i]."
					".$whereManual."
					AND codhdrem IN
					('H007','H008','H017','H018','H019','H020','H040','H160','D015','D026','D027','D028','D029','D030','D031','D054','D055','D121','D122','D123','D150','D151','D155')");

		///////////////CÁLCULO DE PARÁMETROS//////////////////
		$valP001 = cal_days_in_month(CAL_GREGORIAN, $month, $year); //Días del mes
		//$valP001 = 30; //Días del mes
		$valP002 = 0; //Días trabajados
		$valP002a = 0; //Días trabajados - AUX
		$valP003 = 0; //Licencia
		$valP004 = 0; //Inasistencia
		$valP007 = 0; //Horas extra 50%
		$valP007a = 0; //Horas extra 50% - AUX
		$valP008 = 0; //Horas extra 100%
		$valP009 = 0; //UF APV
		$valP010 = round(round($c006/12)*4.75); //TOPE GRATIFICACIÓN
		$valP011 = 0; //GRATIFICACIÓN 25%
		$valP014 = $arrayPersonal[0]["tramo_cargfam"]; //TRAMO ASIG. FAM (A, B, C, D)
		$valP015 = 0;
		if($arrayPersonal[0]["tramo_cargfam"]=='A'){
			$valP015 = 1;
		}elseif($arrayPersonal[0]["tramo_cargfam"]=='B'){
			$valP015 = 2;
		}elseif($arrayPersonal[0]["tramo_cargfam"]=='C'){
			$valP015 = 3;
		}elseif($arrayPersonal[0]["tramo_cargfam"]=='D'){
			$valP015 = 4;
		}

		$cargaSimple = 0;
		$cargaInvalidez = 0;
		$cargaMaternal = 0;

		$cargaSimpleH016 = 0;
		$cargaInvalidezH016 = 0;
		$cargaMaternalH016 = 0;
		if($valP014!='D'){
			for($p=0;$p<count($arrayPersonal2);$p++){
				if($arrayPersonal2[$p]['stat_carg']=='V'){
					if($arrayPersonal2[$p]['tip_carg']==1){
						$cargaSimple++;
						$cargaSimpleH016++;
					}elseif($arrayPersonal2[$p]['tip_carg']==2){
						$cargaInvalidez++;
						$cargaInvalidezH016++;
					}elseif($arrayPersonal2[$p]['tip_carg']==3){
						$cargaMaternal++;
						$cargaMaternalH016++;
					}
					//$valP015++;
				}
			}
		}else{
			for($p=0;$p<count($arrayPersonal2);$p++){
				if($arrayPersonal2[$p]['stat_carg']=='V'){
					if($arrayPersonal2[$p]['tip_carg']==1){
						$cargaSimpleH016++;
					}elseif($arrayPersonal2[$p]['tip_carg']==2){
						$cargaInvalidezH016++;
					}elseif($arrayPersonal2[$p]['tip_carg']==3){
						$cargaMaternalH016++;
					}
				}
			}
			if(count($arrayPersonal2b)>0){
				$valP015 = 4;
			}
		}


		$valP016 = 0; //TRAMO 1 ASIG. FAMILIAR
		$valP017 = 0; //TRAMO 2 ASIG. FAMILIAR
		$valP018 = 0; //TRAMO 3 ASIG. FAMILIAR
		if($valP015==1){
			$valP016 = ($cargaSimple*$c008) + (($cargaInvalidez*$c008)*2) + ($cargaMaternal*$c008); //TRAMO 1 ASIG. FAMILIAR
		}elseif($valP015==2){
			$valP017 = ($cargaSimple*$c009) + (($cargaInvalidez*$c009)*2) + ($cargaMaternal*$c009); //TRAMO 2 ASIG. FAMILIAR
		}elseif($valP015==3){
			$valP018 = ($cargaSimple*$c010) + (($cargaInvalidez*$c010)*2) + ($cargaMaternal*$c010); //TRAMO 3 ASIG. FAMILIAR
		}
		$valP019 = $valP016 + $valP017 + $valP018;

		$valP020 = 1; //AFC? (0=SI , 1=NO)
		$valP021 = $arrayPersonal[0]["indef"]; //TIPO CONTRATO (0=PLAZO FIJO,1=INDEF.)
		if($valP021==1){//Si es indefinido, se calculará si los años de servicio son iguales o mayores a 11 años
			$valP020 = 0;
			$arrayInicioContrato = explode("/", $arrayPersonal[0]["InicioContrato"]);
			if($year-$arrayInicioContrato[2]<11){
				$valP020 = 0;
			}elseif($year-$arrayInicioContrato[2]==11){
				if($month>$arrayInicioContrato[1]){
					$valP020 = 1;
				}else{
					$valP020 = 0;
				}
			}else{
				$valP020 = 1;
			}
		}

		$valP022 = 0; //BASE IMP. MES ANTERIOR (LICENCIA)
		$valP023 = 0; //BASE IMP. AFC EMPRESA
		$valP024 = 0; //AFC EMPRESA
		$valP025 = 0; //AFC EMPRESA (PLANILLA AFP)
		$valP030 = round($c001 * $c100); //TOPE SALUD
		$valP031 = round($c001 * $c101); //TOPE AFC
		$valP035 = 0; //COD. MOV. AFP
		$valP036 = 0; //FECHA INICIO AFP
		$valP037 = 0; //FECHA TERMINO AFP
		$valP038 = 0; //COD. MOV. ISAPRE
		$valP039 = 0; //FECHA INICIO ISAPRE
		$valP040 = 0; //FECHA TERMINO ISAPRE
		$valP041 = 0; //COD. MOV. CCAF
		$valP042 = 0; //FECHA INICIO CCAF
		$valP043 = 0; //FECHA TERMINO CCAF
		$valP044 = 0; //RUT ENTIDAD PAGADORA SUBSIDIO
		$valP045 = 0; //COD. AFP SEG. CESANTIA (TRAB INP)
		$valP046 = 0; //SERVICIO ISAPRE
		$valP047 = 0; //TIPO TRABAJADOR (TRAB INP)
		$valP048 = 0; //CENTRO COSTO TRABAJ.
		$valP049 = 0; //RENTA IMP. DESAHUCIO
		$valP050 = 0; //MONT BONIFICACION ART 19 LEY.......
		$valP051 = 0; //COTIZACION DESAHUCIO
		$valP052 = 0; //AÑOS SERVICIO
		$valP053 = 0; //MESES SERVICIO
		$valP054 = 0; //RENTA IMP. (CTA INDEM)
		$valP055 = 0; //TASA PACTADA (CTA. INDEM.)
		$valP056 = 0; //Nº PERIOODOS ANT. (CTA. INDEM.)
		$valP057 = 0; //PERIODOS DESDE ANT. (CTA. INDEM.)
		$valP058 = 0; //PERIODOS HASTA ANT (CTA. INDEM.)
		$valP059 = 0; //INSTITUCION AUTORIZADA (TRAB. INP)
		$valP060 = 0; //DEPOSITO CONVENIDO
		$valP061 = 0; //COTIZ. VOLUNTARIA (TABAJ. INP)
		$valP062 = 0; //CARGA FAM. INVALIDA
		$valP063 = 0; //CARGA FAM. MATERNAL
		$valP069 = 0; //2% ADICIONAL ISAPRE
		$valP070 = 0; //2% REAL ISAPRE
		$valP071 = 0; //CALCULO I.S.T.
		$valP080 = 0; //% AFP
		$valP081 = 0; //PACTADO ISAPRE
		//$valP082 = $cargaSimple + ($cargaInvalidez*2) + $cargaMaternal; //Nº CARGAS FAM.
		$valP082 = $cargaSimpleH016 + ($cargaInvalidezH016*2) + $cargaMaternalH016; //Nº CARGAS FAM.
		
		$valP085 = 0; //TOTAL APV (LIBRO REMUN )
		$valP086 = 0; //BASE HH
		$valP087 = 0; //af para previred
		$valP088 = 0; //tasainp
		$valP089 = 0; //base imponible inp
		$valP090 = 0; //dias por cuenta trabajador
		$valP091 = 0; //leyes sociales
		$valP092 = 0; //tiempo de atraso
		$valP093 = 0; //TRAMO AF
		$valP094 = 0; //NACIONALIDAD
		$valP095 = 0; //CONTRATO
		$valP096 = 0; //trabajador exento (1 si 0 no)
		$valP097 = 0; //Empleador asume costo SIS ( 1=SI , 0=NO)
		$valP098 = 0; //afp cotis sis empleador
		$valP099 = 0; //monto cotis sis trabajador
		$valP100 = 0; //afp monto cotiza comision
		$valP101 = 0; //ultima base imponible
		$valP102 = 0; //base inp

		$valP103 = 0; //dias cuenta trabajador Requiere P103b
		$valP103a = 0; //dias cuenta trabajador - AUX Requiere D003a
		$valP103b = 0; //dias cuenta trabajador - AUX Requiere P103a y H057
		$valP103t = 0; //dias cuenta trabajador - AUX Requiere P103 y recálculo de tarjas


		$valP104 = 0; //dias cuenta licencia
		$valP104_Temporal = 0; //dias cuenta licencia
		$valP105 = 0; //Vacaciones Proporcionales
		$valP106 = 0; //Promedio trimestre
		$valP107 = 0; //Finiquito - Valor Vacaciones prop.
		$valP108 = 0; //numero de hh 50%
		$valP150 = 0; //Días séptimos

		/////////Recorrido de tarjas/////////
		for($t=0;$t<count($arrayTally);$t++){
			//$valP002 += $arrayTally[$t]["jornadatj"]; MALO
			if($arrayTally[$t]["cc3"]=="12"){
				if($arrayTally[$t]["cc4"]=="02"){
					$valP003 = round($valP003 + $arrayTally[$t]["jornadatj"],2);
				}
				if($arrayTally[$t]["cc4"]=="03"){
					$valP004 = round($valP004 + $arrayTally[$t]["jornadatj"],2);
				}
			}
			$valP007 += $arrayTally[$t]["hhtj"];
			if($arrayTally[$t]["stattj"]=="C"){
				if($arrayTally[$t]["jornadatj"]>0){
					$valP104_Temporal += 1;
					$valP002a = round($valP002a + $arrayTally[$t]["jornadatj"],2);
				}
			}

			//Cálculo de días hábiles trabajados
			$calHabX = 0;
			if($arrayTally[$t]["jornadatj"]>0){
				if($arrayTally[$t]["cc3"]=="12"){
					if($arrayTally[$t]["cc4"]=="02" || $arrayTally[$t]["cc4"]=="03"){
						//0
					}else{
						$calHabX = $arrayTally[$t]["jornadatj"];
					}
				}else{
					$calHabX = $arrayTally[$t]["jornadatj"];
				}
			}

			if($arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['lun1'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['mar1'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['mie1'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['jue1'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['vie1'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['sab1']){
				$calHab1 += $calHabX;
			}
			if($arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['lun2'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['mar2'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['mie2'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['jue2'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['vie2'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['sab2']){
				$calHab2 += $calHabX;
			}
			if($arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['lun3'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['mar3'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['mie3'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['jue3'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['vie3'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['sab3']){
				$calHab3 += $calHabX;
			}
			if($arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['lun4'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['mar4'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['mie4'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['jue4'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['vie4'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['sab4']){
				$calHab4 += $calHabX;
			}
			if($arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['lun5'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['mar5'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['mie5'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['jue5'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['vie5'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['sab5']){
				$calHab5 += $calHabX;
			}
			if($arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['lun6'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['mar6'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['mie6'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['jue6'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['vie6'] || $arrayTally[$t]["fechaTarja"]==$arrayCalendar[0]['sab6']){
				$calHab6 += $calHabX;
			}

		}

		//echo $valP002a.'-('.$valP003.'+'.$valP004.')';
		$valP002 = $valP002a - ($valP003 + $valP004);


		//Cálculo de días séptimos válidos
		$calSep1 = 0;
		$calSep2 = 0;
		$calSep3 = 0;
		$calSep4 = 0;
		$calSep5 = 0;
		$calSep6 = 0;
		if($arrayCalendar[0]['hab1']>0){
			$calSep1 = ((1 / $arrayCalendar[0]['hab1']) * $calHab1) * $arrayCalendar[0]['sep1'];
		}else{
			//Sólo para los casos con días feriados "séptimos" en el inicio de mes (ejemplo, 1 de noviembre del 2020)
			$arrayInicioContrato = explode("/", $arrayPersonal[0]["InicioContrato"]);
			//Se verifica si el trabajador inició este mismo mes su contrato, de ser así se evalúa si le corresponde los primeros séptimos del mes

			//Se utilizan los días hábiles de la 2da semana para el cálculo
			if($year==$arrayInicioContrato[2] && $month==$arrayInicioContrato[1]){
				if($arrayInicioContrato[0]<3){
					//$calSep1 = $arrayCalendar[0]['sep1'];
					$calSep1 = ((1 / $arrayCalendar[0]['hab2']) * $calHab2) * $arrayCalendar[0]['sep1'];
				}
			}else{
				//$calSep1 = 0;
				//$calSep1 = $arrayCalendar[0]['sep1'];
				$calSep1 = ((1 / $arrayCalendar[0]['hab2']) * $calHab2) * $arrayCalendar[0]['sep1'];
			}
		}

		if($arrayCalendar[0]['hab2']>0){
			$calSep2 = ((1 / $arrayCalendar[0]['hab2']) * $calHab2) * $arrayCalendar[0]['sep2'];
			//$calSep2 = ((1 / $arrayCalendar[0]['hab2']) * $calHab2) * 0;
		}
		if($arrayCalendar[0]['hab3']>0){
			$calSep3 = ((1 / $arrayCalendar[0]['hab3']) * $calHab3) * $arrayCalendar[0]['sep3'];
		}
		if($arrayCalendar[0]['hab4']>0){
			$calSep4 = ((1 / $arrayCalendar[0]['hab4']) * $calHab4) * $arrayCalendar[0]['sep4'];
		}
		if($arrayCalendar[0]['hab5']>0){
			if($arrayCalendar[0]['hab6']>0){
				$calSep5 = ((1 / $arrayCalendar[0]['hab5']) * $calHab5) * $arrayCalendar[0]['sep5'];
			}else{
				$calSep5 = ((1 / $arrayCalendar[0]['hab5']) * $calHab5) * ($arrayCalendar[0]['sep5'] + $arrayCalendar[0]['sep6']);
			}
		}
		if($arrayCalendar[0]['hab6']>0){
			$calSep6 = ((1 / $arrayCalendar[0]['hab6']) * $calHab6) * $arrayCalendar[0]['sep6'];
		}

		$valP150 = $calSep1 + $calSep2 + $calSep3 + $calSep4 + $calSep5 + $calSep6;
		$valP104 = round(($valP104_Temporal - ($valP003 + $valP004)) + $valP150, 2);


		///////////////CÁLCULO DE HABERES////////////////
		$valH001 = $arrayPersonal[0]["sbase_per"]; //SUELDO PACTADO
		$valH002 = round(($valH001 / $valP001) * $valP002); //SUELDO MES
		$valH003 = 0; //HORAS EXTRAS 50%
		$valH003a = 0; //HORAS EXTRAS 50% - AUX
		if($valP007>($valP002 * 2)){
			$valP007a = $valP007 - ($valP002 * 2);
			$valH003 = ($c007 * $valH001) * ($valP002 * 2);
			$valH003a = $valP002 * 2;
		}else{
			$valP007a = 0;
			$valH003 = $valP007 * ($c007 * $valH001);
			$valH003a = $valP007;
		}
		$valH004 = 0; //GRATIFICACION
		$valH005 = $valP007a * ($c007 * $valH001); //BONO
		$valH005a = $valP007a; //BONO - AUX
		$valH006 = 0; //VACACIONES
		$valH007 = $manualH007; //BONO EMPRESA - MANUAL
		$valH008 = $manualH008; //BONO - MANUAL
		$valH009 = 0; //.
		$valH010 = 0; //TRATOS
		$valH011 = 0; //SEPTIMOS DE TRATO
		$valH012 = 0; //HORAS EXTRAS 100%
		$valH015 = 0; //.
		$valH016 = $valP019; //ASIG. FAMILIAR
		$valH016a = $cargaSimpleH016 + ($cargaInvalidezH016*2) + $cargaMaternalH016;
		$valH017 = $manualH017; //ASIG. FAM. RETROACTIVA - MANUAL
		$valH018 = $manualH018; //MOVILIZACION - MANUAL
		$valH019 = $manualH019; //COLACION - MANUAL
		$valH020 = $manualH020; //OTROS HABERES NO IMPONIBLES - MANUAL
		$valH026 = 0; //. Siempre 0
		$valH028 = 0; //TOTAL IMPONIBLE 1
		$valH029 = 0; //TOTAL IMPONIBLE 2 Siempre 0
		$valH030 = 0; //TOTAL HABERES IMPONIBLES - Requiere H155 y H058
		$valH031 = $valH016 + $valH017 + $valH018 + $valH019 + $valH020; //TOTAL HABERES NO IMP.
		$valH035 = 0; //TOTAL HABERES - Requiere H030
		$valH038 = 0; //BASE IMPONIBLE
		$valH039 = 0; //BASE TRIBUTABLE
		$valH040 = $manualH040; //BASE IMPONIBLE LICENCIA - MANUAL
		$valH041 = 0; //BASE IMPONIBLE AFC - Requiere H030
		$valH045 = 0; //ALCANCE LIQUIDO - Requiere D045
		$valH046 = 0; //LIQUIDO A PAGAR - Requiere H045 y D026
		$valH050 = 0; //HONORARIO BRUTO Siempre 0
		$valH051 = 0; //HONORARIO LIQUIDO Siempre 0
		$valH055 = 0; //PART. Y ASIG. BRUTA Siempre 0
		$valH056 = 0; //IMPONIBLE CAJA LOS ANDES Siempre 0
		$valH057 = round($valH001 * 0.25) + $valH001; //GRATIFICACION L.
		$valH058 = 0; //BONO x GRATIFICACION - Requiere H155
		$valH059 = 0; //. Siempre 0
		$valH060 = 0; //Finiquitos - Haberes Siempre 0
		$valH061 = 0; //. Siempre 0
		$valH062 = 0; //. Siempre 0
		$valH063 = 0; //. Siempre 0
		$valH064 = 0; //. Siempre 0
		$valH065 = 0; //. Siempre 0
		$valH066 = 0; //. Siempre 0
		$valH067 = 0; //BASE GRATIFICACION Siempre 0
		$valH068 = 0; //BASE GRAT 2 Siempre 0
		$valH069 = 0; //TOTAL BASES GRAT - Requiere H155
		$valH069a = 0; //TOTAL BASES GRAT - AUX Requiere H155
		$valH070 = 0; //. Siempre 0
		$valH071 = 0; //SEMANA CORRIDA Siempre 0
		$valH072 = 0; //..-- Siempre 0, pero se calcula en tratos
		$valH073 = 0; //HH al 50% Siempre 0
		$valH074 = 0; //todas las hh Siempre 0
		$valH145 = 0; //LIQUIDO2 (Anticipos en Cto.Indefinido) - Requiere D045
		$valH150 = 0; //Días Séptimos
		if($valP001>0){
			$valH150 = round(($valH001 / $valP001) * $valP150);
		}
		$valH151 = 0; //Días Séptimos (2) Siempre 0
		$valH155 = 0; //TRATOS - Requiere cálculo de tratos y descuentos D003
		$valH160 = $manualH160; //Bonificaciones - MANUAL

		$valH162 = 0; //Otro162 Siempre 0
		$valH900 = 0; //Bonificación  Siempre 0


		
		////////////////CÁLCULO DE DESCUENTOS//////////////////
		$valD001 = 0; //FONDO PENSIONES
		$valD002 = 0; //ADICIONAL SEGURO
		$valD003 = 0; //AFP
		$valD003a = 0; //AFP - AUXILIAR
		$valD003b = 0; //AFP - AUXILIAR
		$valD003rem = 0; //AFP valrem
		$valD004 = 0; //FONASA
		$valD004rem = 0; //FONASA - valrem
		$valD005 = 0; //ISAPRE
		$valD005rem = 0; //ISAPRE - valrem
		$valD006 = 0; //PACTADO PROP. ISAPRE
		$valD006rem = 0; //PACTADO PROP. ISAPRE - valrem
		$valD007 = 0; //7% ISAPRE
		$valD008 = 0; //ADICIONAL ISAPRE
		$valD009 = 0; //INP
		$valD010 = 0; //SALUD PARA IMPUESTO Siempre 0
		$valD012 = 0; //AFC TRABAJADOR
		$valD012rem = 0; //AFC TRABAJADOR - valrem
		$valD013 = 0; //APV AFP Siempre 0
		$valD014 = 0; //APV (EN UF) Siempre 0
		$valD015 = $manualD015; //APV 2 - MANUAL
		$valD016 = 0; //COTIZ. VOLUNTARIA AFP Siempre 0
		$valD020 = 0; //PRESTAMO FONASA Siempre 0
		$valD025 = 0; //IMPUESTO UNICO
		$valD025a = 0; //IMPUESTO UNICO - AUXILIAR
		$valD025b = 0; //IMPUESTO UNICO - AUXILIAR
		$valD026 = $manualD026; //ANTICIPO - MANUAL
		$valD027 = $manualD027; //AHORRO AFP - MANUAL
		$valD028 = $manualD028; //FULL AHORRO CCAF - MANUAL
		$valD029 = $manualD029; //ANTICIPO Bonificación - MANUAL
		$valD030 = $manualD030; //PRESTAMO CCAF - MANUAL
		$valD031 = $manualD031; //PRESTAMO INTERNO - MANUAL
		$valD032 = 0; //SEG. VIDA CCAF Siempre 0
		$valD033 = 0; //ATRASOS Siempre 0
		$valD034 = 0; //HORAS DE PERMISOS Siempre 0
		$valD035 = 0; //ANTICIPO COLACION Siempre 0
		$valD036 = 0; //ANTICIPOS Siempre 0
		$valD039 = 0; //OTROS DESCUENTOS Siempre 0
		$valD040 = 0; //TOTAL DESC. PREVISIONALES
		$valD041 = 0; //DESC. TRIBUTABLES Siempre 0
		$valD042 = 0; //TOTAL OTROS DESCUENTOS
		$valD045 = 0; //TOTAL DESCUENTOS

		$valD046 = 0; //AJUSTE LEY INGRESO MINIMO (Menos) Siempre 0
		$valD047 = 0; //PACTADO CON LA ISAPRE Siempre 0
		$valD048 = 0; //ATRASOS Siempre 0
		$valD049 = 0; //ATRASOS (CALCULO) Siempre 0
		$valD050 = 0; //APORTES Siempre 0
		$valD051 = 0; //SEGURO DE SALUD Siempre 0
		$valD052 = 0; //SEG. HOGAR CCAF Siempre 0
		$valD053 = 0; //PACTADO ISAPRE Siempre 0
		$valD054 = $manualD054; //DESCUENTOS IMPLEMENTOS - MANUAL
		$valD055 = $manualD055; //DIFERENCIA MES ANTERIOR - MANUAL

		$valD100 = 0; //PRESTAMO EN CUOTAS - NO SE USA
		$valD101 = 0; //PRESTAMO EN CUOTAS - NO SE USA
		$valD102 = 0; //PRESTAMO - NO SE USA
		$valD110 = 0; //TOTAL PRESTAMOS EMPRESA - Se calcula aparte, a revisar
		$valD121 = $manualD121; //DESCUENTO CCAF LOS ANDES - MANUAL
		$valD122 = $manualD122; //Ahorro Previsional Voluntario - MANUAL
		$valD123 = $manualD123; //SEGURO COMPLEMENTARIO - MANUAL
		$valD150 = $manualD150; //DESCUENTO CEMENTERIO - MANUAL
		$valD151 = $manualD151; //DESCUENTOS CCAF LA ARAUCANA - MANUAL
		$valD155 = $manualD155; //Prestamos por Caja Chica - MANUAL
		$valD156 = 0; //FONDO PENSIONES Siempre 0
		$valD900 = 0; //Bonificación  Siempre 0


		////PRÉSTAMOS////
		if($settlement==0){//Se aduce que el préstamo se restó en el finiquito para los casos de finiquitados
			$arrayLoan = executeSelect("SELECT pp.* 
						FROM PRESTAMO_PAGOS pp
						LEFT JOIN PRESTAMO p ON p.ID=pp.ID_PRESTAMO
						WHERE p.ID_FINIQUITO_PERSONAL=0 AND pp.Tipo='Cuota' AND pp.Estado='PAGADO' 
						AND p.RUT='".$arrayList[$i]."'
						AND YEAR(pp.Fecha) = ".$year."
						AND MONTH(pp.Fecha) = ".$month);
			for($l=0;$l<count($arrayLoan);$l++){
				if($arrayLoan[$l]['Estado']=='PAGADO'){
					$valD110 += $arrayLoan[$l]['Monto'];
				}
			}
		}

		////COTIZACIONES PREVISIONALES////
		$arrayAFP = executeSelect("SELECT * FROM AFP WHERE cod_afp = '".$arrayPersonal[0]["afp_per"]."'");
		$afpFac1 = $arrayAFP[0]['fac1_afp'];
		$afpFac2 = $arrayAFP[0]['fac2_afp'];
		$afpFac3 = $arrayAFP[0]['fac3_afp'];
		$arrayHealthSystem = executeSelect("SELECT * FROM ISAPRES WHERE cod_isa = '".$arrayPersonal[0]["isa_per"]."'");
		$healthPercentage = $arrayHealthSystem[0]['porc_isa'];
		$healthUF = $arrayPersonal[0]['uf_isa_per'];
		if($arrayPersonal[0]['porc_isa_per']>0){
			$healthPercentage = $arrayPersonal[0]['porc_isa_per'];
		}

		$valD003a = round($afpFac1 + $afpFac3, 2);
		//Cálculo de AFC
		//if($valP021==1){
		if($valP020==0 && $valP021==1){
			$valD003a += ($c011 * 100);
		}
		$valD003a = ($valD003a + $healthPercentage) + $arrayPersonal[0]['porc_inp'];
		$valD003b = (100 - $valD003a) / 100;
		$valP103a = round($valD003a / 100, 4); 
		$valP103b = round($valH057 * $valP103a);
		$valP103 = round($valH057 - $valP103b) / $valP001;
		$valP103t = 0; //dias cuenta trabajador - AUX


		//Recálculo de Tarjas y Tratos
		$t = 0;
		for($t=0;$t<count($arrayTally);$t++){
			if($arrayTally[$t]["stattj"]=="C"){
				//Cálculo de trato
				$arrayDeals11 = executeSelect("SELECT * FROM TRATOS11 
											WHERE cc1trt = '".$arrayTally[$t]["cc1trt"]."'
											AND cattrt = ".$arrayTally[$t]["cattrt"]."
											AND codtrt = ".$arrayTally[$t]["codtrt"]);
				$val1trt = 0;
				if(count($arrayDeals11)>0){
					$val1trt = $arrayDeals11[0]['val1trt'];
				}
				$rendtj = round($arrayTally[$t]["rendtj"] * $val1trt, 2);

				//Cálculo bono día				
				$val1b = 0;
				if($arrayTally[$t]["codbonodd"]!=''){
					$arrayBonusDay = executeSelect("SELECT * FROM BONODIA WHERE codbonodd = ".$arrayTally[$t]["codbonodd"]);
					if(count($arrayBonusDay)>0){
						$val1b = $arrayBonusDay[0]['val1bonodd'];
					}
				}
				

				if($rendtj>0){
					//Cálculo de rendimiento de jornada
					if($arrayTally[$t]["jornadatj"]<1){
						$valP103t = $valP103 * $arrayTally[$t]["jornadatj"];
					}else{
						$valP103t = $valP103;
					}

					//Cálculo rendimiento trato
					$rendimientoTj = 0;
					if($rendtj > $valP103t){
						$rendimientoTj = round($rendtj - $valP103t, 2);
					}
					$valH155 = $valH155 + $rendimientoTj + $val1b;
				}
				$valH072 = $valH072 + $val1b;
			}
		}

		//Requieren D003b
		$valH155 = round($valH155 / $valD003b);
		$valH155 = round($valH155 / 1.25);

				
		//Requiren H155 y H058
		$valH069 = $valH002 + $valH003 + $valH005 + $valH006 + $valH008 + $valH012 + $valH150 + $valH155 + $valH160;
		$valH069a = $valH002 + $valH003 + $valH005 + $valH006 + $valH012 + $valH150 + $valH155 + $valH160;

		$valP011 = round($valH069 * 0.25, 2);
		//$valP011a = round($valH069a * 0.25, 2); NO SE USA
		if($valP010>$valP011){
			$valH004 = round($valP011);
		}else{
			$valH004 = round($valP010);
		}

		$valH028 = $valH001 + $valH004; //TOTAL IMPONIBLE 1


		if(($valH008>0 || $valH005>0) && $valH004==$valP010){
			if($valP011>$valP010){
				$valH058 = round($valH155 * 0.25);
			}
			if($valH155==0){
				$valH058 = 0;
			}
		}else{
			if($valP011>$valP010 && $valH155>0){
				$valH058 = round($valP011 - $valP010);
			}
			if($valH155==0 && $valH002>=$valP030){
				$valH058 = 0;
			}
		}

		$valH030 = round(($valH002 + $valH003 + $valH005 + $valH012 + $valH150 + $valH160) + ($valH155 + $valH007 + $valH008 + $valH058) + $valH004);


		//Requieren H030
		$valH035 = round($valH030 + $valH031); //TOTAL HABERES
		if($valH030>$valP031){
			$valH041 = $valP031;
		}else{
			$valH041 = $valH030;
		}

		if($arrayAFP[0]['cod_afp']=='000' && $arrayHealthSystem[0]['cod_isa']=='016'){ //Sin AFP ni Salud
			$valD009 = round(($arrayPersonal[0]['porc_inp'] * $valH030) / 100, 2); //INP
		}else{
			$val030temp = $valP030;
			if($valH030<$valP030){
				$val030temp = $valH030;
			}

			$valD001 = round(($afpFac1 * $val030temp) / 100, 2);
			$valD002 = round(($afpFac3 * $val030temp) / 100, 2);

			$valD003rem = round($afpFac1 + $afpFac3, 2);
			$valD003 = round($valD001 + $valD002);

			if($arrayPersonal[0]['porc_inp']==0){
				if($arrayPersonal[0]['isa_per']=='000'){
					$valD004rem = $healthPercentage;
					$valD004 = round(($val030temp * $healthPercentage) / 100);
				}else{
					$valD005rem = $healthPercentage;
					$valD005 = round(($val030temp * $healthPercentage) / 100);
				}
			}
		}

		$valD006temp = 0; //"Totia"
		if($healthUF>0){
			$valD006temp = round($healthUF * $c001,2);
			//$valD006 = round($valP030 * 0.07,2);
			$valD006rem = $healthUF;
			$valD006 = $valD006temp;
			$valD007 = round($valD006temp - $valD005,2);

			$valD005rem = $healthPercentage;
			$valD005 = $valD005;//Se reasigna??

			$valD005rem = $healthPercentage;
			$valD005 = $valD005;//Se reasigna??
		}
		if($valP002+$valP003==0){
			$valD007 = 0;
		}
		if($valD006temp<$valD005){
			$valD007 = 0;
		}
		if($valP003>0){
			$valD007temp = ($valD006temp / $valP001) * $valP104;
			$valD007 = round($valD007temp - $valD005, 2);
		}
		if($valD007<0){
			$valD007 = 0;
		}

		//Requiere H041
		if($valP020==0 && $valP021==1){//0=aplica AFC
			if($arrayPersonal[0]['porc_inp']<1){
				$valD012rem = 0.6;
				$valD012 = round($c011 * $valH041, 3);
			}
		}

		//echo $rutrem.": ".$valD003."+".$valD004."+".$valD005."+".$valD009."+".$valD012."+".$valD015;

		$valD040 = round($valD003) + round($valD004) + round($valD005) + round($valD009) + round($valD012) + round($valD015); //Total Descuentos

		////Cálculo impuesto único////
		$valD025a = round($valH030 - $valD040);
		//$valD025a = round($valH030 - ($valD003 + $valD004 + $valD005 + $valD009 + $valD012 + $valD015));
		$arrayTabImp1 = executeSelect("SELECT *	FROM TABIMP1 WHERE Im_aaaa=$year AND Im_mm=$month");
		for($ti=0;$ti<count($arrayTabImp1);$ti++){
			$valMin = round($c000 * $arrayTabImp1[$ti]['Im_topemin']);
			$valMax = round($c000 * $arrayTabImp1[$ti]['Im_topemax']);
			$valPercentage = $arrayTabImp1[$ti]['Im_porc'] / 100;
			$valDiscount = round($c000 * $arrayTabImp1[$ti]['Im_rebaja']);
			if($valD025a>=$valMin && $valD025a<=$valMax){
				$valD025b = round($valD025a * $valPercentage);
				$valD025 = round($valD025b - $valDiscount);
			}
		}

		
		//Requieren H155 y P010
		

		
		//Requiere muuuuchas cosas
		$valD042 = round($valD007 + $valD025 + $valD029 + $valD030 + $valD031 + $valD054 + $valD055 + $valD100 + $valD101 + $valD102 + $valD110 + $valD121 + $valD122 + $valD123 + $valD150 + $valD151 + $valD155);
		$valD045 = $valD040 + $valD042;

		//Se revierten los datos D007 y D008
		$valD008 = $valD007;
		$valD007 = 0;

		//Requieren D045
		$valH045 = round($valH035 - $valD045);
		$valH046 = round($valH045 - $valD026);
		$valH145 = $valH045 + $valD042;



		/////////////Creación de registros según tabla HYD////////////
		for($j=0;$j<count($arrayLiq);$j++){
			$linrem = $j+1;
			$valrem = 0;
			$valremc = '';
			$codhdrem = $arrayLiq[$j]["codhd"];
			$descriphdrem = utf8_encode($arrayLiq[$j]["descriphd"]);
			$valrem2 = 0;
			$liqrem = $arrayLiq[$j]["liqhd"];
			$formvalrem = $arrayLiq[$j]["formvalhd"];
			$digitarem = ''; //?????

			switch($arrayLiq[$j]["codhd"]){
				//REGISTROS P - PARÁMETROS
				case "P001": //DÍAS TOTALES MES
					$valrem2 = $valP001;
				break;
				case "P002": //DÍAS TRABAJADOS
					$valrem2 = $valP002;
				break;
				case "P003": //DÍAS DE LICENCIAS
					$valrem2 = $valP003;
				break;
				case "P004": //DÍAS INASISTENCIAS
					$valrem2 = $valP004;
				break;
				case "P005": //DÍAS ASIGNACIÓN FAMILIAR
					if($valP104<25){
						$valrem2 = $valP104;
					}else{
						$valrem2 = 30;
					}
				break;
				case "P007": //HORAS EXTRAS 50%
					$valrem2 = $valP007;
				break;
				case "P008": //HORAS EXTRAS 100% Siempre 0
					$valrem2 = $valP008;
				break;
				case "P009": //UF APV Siempre 0
					$valrem2 = $valP009;
				break;
				case "P010": //TOPE GRATIFICACIÓN
					$valrem2 = $valP010;
				break;
				case "P011": //GRATIFICACIÓN 25%
					$valrem2 = $valP011;
				break;
				case "P014": //ASIGNACIÓN FAM (A,B,C,D)
					$valremc = $valP014;
				break;
				case "P015": //ASIGNACIÓN FAM (1,2,3,4)
					$valrem2 = $valP015;
				break;
				
				case "P016": //TRAMO 1 ASIG. FAMILIAR
					$valrem2 = $valP016;
				break;
				case "P017": //TRAMO 2 ASIG. FAMILIAR
					$valrem2 = $valP017;
				break;
				case "P018": //TRAMO 3 ASIG. FAMILIAR
					$valrem2 = $valP018;
				break;
				case "P019": //TOTAL ASIG. FAMILIAR
					$valrem2 = $valP019;
				break;
				case "P020": //AFC? (0=SI , 1=NO)
					$valrem2 = $valP020;
				break;
				case "P021": //TIPO CONTRATO (0=PLAZO FIJO,1=INDEF.)
					$valrem2 = $valP021;
				break;
				case "P022": //BASE IMP. MES ANTERIOR (LICENCIA) Siempre 0
					$valrem2 = $valP022;
				break;
				case "P023": //BASE IMP. AFC EMPRESA Siempre 0
					$valrem2 = $valP023;
				break;
				case "P024": //AFC EMPRESA Siempre 0
					$valrem2 = $valP024;
				break;
				case "P025": //AFC EMPRESA (PLANILLA AFP) Siempre 0
					$valrem2 = $valP025;
				break;
				case "P030": //TOPE SALUD
					$valrem2 = $valP030;
				break;
				case "P031": //TOPE AFC
					$valrem2 = $valP031;
				break;
				case "P035": //COD. MOV. AFP Siempre 0
					$valrem2 = $valP035;
				break;
				case "P036": //FECHA INICIO AFP Siempre 0
					$valrem2 = $valP036;
				break;
				case "P037": //FECHA TERMINO AFP Siempre 0
					$valrem2 = $valP037;
				break;
				case "P038": //COD. MOV. ISAPRE Siempre 0
					$valrem2 = $valP038;
				break;
				case "P039": //FECHA INICIO ISAPRE Siempre 0
					$valrem2 = $valP039;
				break;

				case "P040": //FECHA TERMINO ISAPRE Siempre 0
					$valrem2 = $valP040;
				break;
				case "P041": //COD. MOV. CCAF Siempre 0
					$valrem2 = $valP041;
				break;
				case "P042": //FECHA INICIO CCAF Siempre 0
					$valrem2 = $valP042;
				break;
				case "P043": //FECHA TERMINO CCAF Siempre 0
					$valrem2 = $valP043;
				break;
				case "P044": //RUT ENTIDAD PAGADORA SUBSIDIO Siempre 0
					$valrem2 = $valP044;
				break;
				case "P045": //COD. AFP SEG. CESANTIA (TRAB INP) Siempre 0
					$valrem2 = $valP045;
				break;
				case "P045": //COD. AFP SEG. CESANTIA (TRAB INP)
					$valrem2 = $valP045;
				break;
				case "P046": //SERVICIO ISAPRE
					$valrem2 = $valP046;
				break;
				case "P047": //TIPO TRABAJADOR (TRAB INP)
					$valrem2 = $valP047;
				break;
				case "P048": //CENTRO COSTO TRABAJ.
					$valrem2 = $valP048;
				break;
				case "P049": //RENTA IMP. DESAHUCIO
					$valrem2 = $valP049;
				break;
				case "P050": //MONT BONIFICACION ART 19 LEY.......
					$valrem2 = $valP050;
				break;
				case "P051": //COTIZACION DESAHUCIO
					$valrem2 = $valP051;
				break;
				case "P052": //AÑOS SERVICIO
					$valrem2 = $valP052;
				break;
				case "P053": //MESES SERVICIO
					$valrem2 = $valP053;
				break;
				case "P054": //RENTA IMP. (CTA INDEM)
					$valrem2 = $valP054;
				break;
				case "P055": //TASA PACTADA (CTA. INDEM.)
					$valrem2 = $valP055;
				break;
				case "P056": //Nº PERIOODOS ANT. (CTA. INDEM.)
					$valrem2 = $valP056;
				break;
				case "P057": //PERIODOS DESDE ANT. (CTA. INDEM.)
					$valrem2 = $valP057;
				break;
				case "P058": //PERIODOS HASTA ANT (CTA. INDEM.)
					$valrem2 = $valP058;
				break;
				case "P059": //INSTITUCION AUTORIZADA (TRAB. INP)
					$valrem2 = $valP059;
				break;
				case "P060": //DEPOSITO CONVENIDO
					$valrem2 = $valP060;
				break;
				case "P061": //COTIZ. VOLUNTARIA (TABAJ. INP)
					$valrem2 = $valP061;
				break;
				case "P062": //CARGA FAM. INVALIDA
					$valrem2 = $valP062;
				break;
				case "P063": //CARGA FAM. MATERNAL
					$valrem2 = $valP063;
				break;
				case "P069": //2% ADICIONAL ISAPRE
					$valrem2 = $valP069;
				break;
				case "P070": //2% REAL ISAPRE
					$valrem2 = $valP070;
				break;
				case "P071": //CALCULO I.S.T.
					$valrem2 = $valP071;
				break;
				case "P080": //% AFP
					$valrem2 = $valP080;
				break;
				case "P081": //PACTADO ISAPRE
					$valrem2 = $valP081;
				break;
				case "P082": //Nº CARGAS FAM.
					$valrem2 = $valP082;
				break;
				case "P085": //TOTAL APV (LIBRO REMUN )
					$valrem2 = $valP085;
				break;
				case "P086": //BASE HH
					$valrem2 = $valP086;
				break;
				case "P087": //af para previred
					$valrem2 = $valP087;
				break;
				case "P088": //tasainp
					$valrem2 = $valP088;
				break;
				case "P089": //base imponible inp
					$valrem2 = $valP089;
				break;
				case "P090": //dias por cuenta trabajador
					$valrem2 = $valP090;
				break;
				case "P091": //leyes sociales
					$valrem2 = $valP091;
				break;
				case "P092": //tiempo de atraso
					$valrem2 = $valP092;
				break;
				case "P093": //TRAMO AF
					$valrem2 = $valP093;
				break;
				case "P094": //NACIONALIDAD
					$valrem2 = $valP094;
				break;
				case "P095": //CONTRATO
					$valrem2 = $valP095;
				break;
				case "P096": //trabajador exento (1 si 0 no)
					$valrem2 = $valP096;
				break;
				case "P097": //Empleador asume costo SIS ( 1=SI , 0=NO)
					$valrem2 = $valP097;
				break;
				case "P098": //afp cotis sis empleador
					$valrem2 = $valP098;
				break;
				case "P099": //monto cotis sis trabajador
					$valrem2 = $valP099;
				break;
				case "P100": //afp monto cotiza comision
					$valrem2 = $valP100;
				break;
				case "P101": //ultima base imponible
					$valrem2 = $valP101;
				break;
				case "P102": //base inp
					$valrem2 = $valP102;
				break;
				case "P103": //dias cuenta de trabajador
					$valrem2 = $valP103;
				break;
				case "P104": //dias cuenta licencia
					$valrem2 = $valP104;
				break;
				case "P105": //Vacaciones Proporcionales Siempre 0, antiguamente calculado
					$valrem2 = $valP105;
				break;
				case "P106": //Promedio trimestre
					$valrem2 = $valP106;
				break;
				case "P107": //Finiquito - Valor Vacaciones prop. Siempre 0, antiguamente calculado
					$valrem2 = $valP107;
				break;
				case "P108": //numero de hh 50%
					$valrem2 = $valP108;
				break;

				case "P150": //Días Séptimos
					$valrem2 = $valP150;
				break;

				//REGISTROS H - HABERES
				case "H001": //SUELDO PACTADO
					$valrem2 = $valH001;
				break;
				case "H002": //SUELDO MES
					$valrem = $valP002;
					$valrem2 = $valH002;
				break;
				case "H003": //HORAS EXTRAS 50%
					$valrem = $valH003a;
					$valrem2 = $valH003;
				break;
				case "H004": //GRATIFICACION
					$valrem2 = $valH004; //Requiere P011
				break;
				case "H005": //BONO
					$valrem = $valH005a;
					$valrem2 = $valH005;
				break;
				case "H006": //VACACIONES Siempre 0
					$valrem2 = $valH006;
				break;
				case "H007": //BONO EMPRESA - MANUAL
					$valrem2 = $valH007;
				break;
				case "H008": //BONO - MANUAL
					$valrem2 = $valH008;
				break;
				case "H009": //. Siempre 0
					$valrem2 = $valH009;
				break;
				case "H010": //TRATOS Siempre 0
					$valrem2 = $valH010;
				break;
				case "H011": //SEPTIMOS DE TRATO Siempre 0
					$valrem2 = $valH011;
				break;
				case "H012": //HORAS EXTRAS 100% Siempre 0
					$valrem2 = $valH012;
				break;
				case "H015": //. Siempre 0
					$valrem2 = $valH015;
				break;
				case "H016": //ASIG. FAMILIAR
					$valrem = $valH016a;
					$valrem2 = $valH016;
				break;

				case "H017": //ASIG. FAM. RETROACTIVA - MANUAL
					$valrem2 = $valH017;
				break;
				case "H018": //MOVILIZACION - MANUAL
					$valrem2 = $valH018;
				break;
				case "H019": //COLACION - MANUAL
					$valrem2 = $valH019;
				break;
				case "H020": //OTROS HABERES NO IMPONIBLES - MANUAL
					$valrem2 = $valH020;
				break;
				case "H026": //. Siempre 0
					$valrem2 = $valH026;
				break;
				case "H028": //TOTAL IMPONIBLE 1
					$valrem2 = $valH028;
				break;
				case "H029": //TOTAL IMPONIBLE 2 Siempre 0
					$valrem2 = $valH029;
				break;
				case "H030": //TOTAL HABERES IMPONIBLES
					$valrem2 = $valH030;
				break;
				case "H031": //TOTAL HABERES NO IMP.
					$valrem2 = $valH031;
				break;
				case "H035": //TOTAL HABERES
					$valrem2 = $valH035;
				break;

				case "H038": //BASE IMPONIBLE
					$valrem2 = $valH038;
				break;
				case "H039": //BASE TRIBUTABLE
					$valrem2 = $valH039;
				break;
				case "H040": //BASE IMPONIBLE LICENCIA Siempre 0
					$valrem2 = $valH040;
				break;

				case "H041": //BASE IMPONIBLE AFC
					$valrem2 = $valH041;
				break;
				case "H045": //ALCANCE LIQUIDO
					$valrem2 = $valH045;
				break;
				case "H046": //LIQUIDO A PAGAR
					$valrem2 = $valH046;
				break;
				case "H050": //HONORARIO BRUTO Siempre 0
					$valrem2 = $valH050;
				break;
				case "H051": //HONORARIO LIQUIDO Siempre 0
					$valrem2 = $valH051;
				break;
				case "H055": //PART. Y ASIG. BRUTA Siempre 0
					$valrem2 = $valH055;
				break;
				case "H056": //IMPONIBLE CAJA LOS ANDES Siempre 0
					$valrem2 = $valH056;
				break;
				case "H057": //GRATIFICACION L.
					$valrem2 = $valH057;
				break;
				case "H058": //BONO x GRATIFICACION
					$valrem2 = $valH058;
				break;
				case "H059": //. Siempre 0
					$valrem2 = $valH059;
				break;
				case "H060": //Finiquitos - Haberes Siempre 0
					$valrem2 = $valH060;
				break;
				case "H061": //. Siempre 0
					$valrem2 = $valH061;
				break;
				case "H062": //. Siempre 0
					$valrem2 = $valH062;
				break;
				case "H063": //. Siempre 0
					$valrem2 = $valH063;
				break;
				case "H064": //. Siempre 0
					$valrem2 = $valH064;
				break;
				case "H065": //. Siempre 0
					$valrem2 = $valH065;
				break;
				case "H066": //. Siempre 0
					$valrem2 = $valH066;
				break;
				case "H067": //BASE GRATIFICACION Siempre 0
					$valrem2 = $valH067;
				break;
				case "H068": //BASE GRAT 2 Siempre 0
					$valrem2 = $valH068;
				break;

				case "H069": //BASE GRAT 2 Siempre 0
					$valrem2 = $valH069;
				break;
				case "H070": //. GRAT
					$valrem2 = $valH070;
				break;
				case "H071": //SEMANA CORRIDA Siempre 0
					$valrem2 = $valH071;
				break;
				case "H072": //..-- Siempre 0
					$valrem2 = $valH072;
				break;
				case "H073": //HH al 50% Siempre 0
					$valrem2 = $valH073;
				break;
				case "H074": //todas las hh Siempre 0
					$valrem2 = $valH074;
				break;
				case "H145": //LIQUIDO2 (Anticipos en Cto.Indefinido)
					$valrem2 = $valH145;
				break;
				case "H150": ///Días Séptimos
					if($valP001>0){
						$valrem = $valP150;
					}else{
						$valrem = 0;
					}
					$valrem2 = $valH150;
				break;
				case "H151": //Días Séptimos(2) Siempre 0
					$valrem2 = $valH151;
				break;

				case "H155": //TRATOS
					$valrem2 = $valH155;
				break;

				case "H160": //OTROS HABERES NO IMPONIBLES - MANUAL
					$valrem2 = $valH160;
				break;
				case "H162": //Otro162 Siempre 0
					$valrem2 = $valH162;
				break;
				case "H900": //Bonificación  Siempre 0
					$valrem2 = $valH900;
				break;

				//REGISTROS D - DESCUENTOS
				case "D001": //FONDO PENSIONES
					$valrem2 = $valD001;
				break;
				case "D002": //ADICIONAL SEGURO
					$valrem2 = $valD002;
				break;
				case "D003": //AFP
					$valrem = $valD003rem;
					$valrem2 = $valD003;
				break;
				case "D004": //FONASA
					$valrem = $valD004rem;
					$valrem2 = $valD004;
				break;
				case "D005": //ISAPRE
					$valrem = $valD005rem;
					$valrem2 = $valD005;
				break;
				case "D006": //PACTADO PROP. ISAPRE
					$valrem = $valD006rem;
					$valrem2 = $valD006;
				break;
				case "D007": //7% ISAPRE
					$valrem2 = $valD007;
				break;
				case "D008": //ADICIONAL ISAPRE
					$valrem2 = $valD008;
				break;
				case "D009": //INP
					$valrem = $arrayPersonal[0]['porc_inp'];
					$valrem2 = $valD009;
				break;
				case "D010": //SALUD PARA IMPUESTO
					$valrem2 = $valD010;
				break;
				case "D012": //AFC TRABAJADOR
					$valrem = $valD012rem;
					$valrem2 = $valD012;
				break;
				case "D013": //APV AFP
					$valrem2 = $valD013;
				break;
				case "D014": //APV (EN UF)
					$valrem2 = $valD014;
				break;
				case "D015": //APV 2 - MANUAL
					$valrem2 = $valD015;
				break;
				case "D016": //COTIZ. VOLUNTARIA AFP
					$valrem2 = $valD016;
				break;
				case "D020": //PRESTAMO FONASA
					$valrem2 = $valD020;
				break;
				case "D025": //IMPUESTO UNICO
					$valrem2 = $valD025;
				break;
				case "D026": //ANTICIPO - MANUAL
					$valrem2 = $valD026;
				break;

				case "D027": //AHORRO AFP - MANUAL
					$valrem2 = $valD027;
				break;
				case "D028": //FULL AHORRO CCAF - MANUAL
					$valrem2 = $valD028;
				break;
				case "D029": //ANTICIPO Bonificación - MANUAL
					$valrem2 = $valD029;
				break;
				case "D030": //PRESTAMO CCAF - MANUAL
					$valrem2 = $valD030;
				break;
				case "D031": //PRESTAMO INTERNO - MANUAL
					$valrem2 = $valD031;
				break;

				case "D032": //SEG. VIDA CCAF Siempre 0
					$valrem2 = $valD032;
				break;
				case "D033": //ATRASOS Siempre 0
					$valrem2 = $valD033;
				break;
				case "D034": //HORAS DE PERMISOS Siempre 0
					$valrem2 = $valD034;
				break;
				case "D035": //ANTICIPO COLACION Siempre 0
					$valrem2 = $valD035;
				break;
				case "D036": //ANTICIPOS Siempre 0
					$valrem2 = $valD036;
				break;
				case "D039": //OTROS DESCUENTOS Siempre 0
					$valrem2 = $valD039;
				break;
				case "D040": //TOTAL DESC. PREVISIONALES
					$valrem2 = $valD040;
				break;
				case "D041": //DESC. TRIBUTABLES Siempre 0
					$valrem2 = $valD041;
				break;
				case "D042": //TOTAL OTROS DESCUENTOS
					$valrem2 = $valD042;
				break;
				case "D045": //TOTAL DESCUENTOS
					$valrem2 = $valD045;
				break;
				case "D046": //AJUSTE LEY INGRESO MINIMO (Menos) Siempre 0
					$valrem2 = $valD046;
				break;
				case "D047": //PACTADO CON LA ISAPRE Siempre 0
					$valrem2 = $valD047;
				break;
				case "D048": //ATRASOS Siempre 0
					$valrem2 = $valD048;
				break;
				case "D049": //ATRASOS (CALCULO) Siempre 0
					$valrem2 = $valD049;
				break;
				case "D050": //APORTES Siempre 0
					$valrem2 = $valD050;
				break;
				case "D051": //SEGURO DE SALUD Siempre 0
					$valrem2 = $valD051;
				break;
				case "D052": //SEG. HOGAR CCAF Siempre 0
					$valrem2 = $valD052;
				break;
				case "D053": //PACTADO ISAPRE Siempre 0
					$valrem2 = $valD053;
				break;
				case "D054": //DESCUENTOS IMPLEMENTOS - MANUAL
					$valrem2 = $valD054;
				break;
				case "D055": //DIFERENCIA MES ANTERIOR - MANUAL
					$valrem2 = $valD055;
				break;
				case "D100": //PRESTAMO EN CUOTAS
					$valrem2 = $valD100; 
				break;
				case "D101": //PRESTAMO EN CUOTAS
					$valrem2 = $valD101; 
				break;
				case "D102": //PRESTAMO
					$valrem2 = $valD102; 
				break;
				case "D110": //TOTAL PRESTAMOS EMPRESA
					$valrem2 = $valD110; 
				break;
				case "D121": //DESCUENTO CCAF LOS ANDES - MANUAL
					$valrem2 = $valD121;
				break;
				case "D122": //Ahorro Previsional Voluntario - MANUAL
					$valrem2 = $valD122;
				break;
				case "D123": //SEGURO COMPLEMENTARIO - MANUAL
					$valrem2 = $valD123;
				break;
				case "D150": //DESCUENTO CEMENTERIO - MANUAL
					$valrem2 = $valD150;
				break;
				case "D151": //DESCUENTOS CCAF LA ARAUCANA - MANUAL
					$valrem2 = $valD151;
				break;
				case "D155": //Prestamos por Caja Chica - MANUAL
					$valrem2 = $valD155;
				break;
				case "D156": //FONDO PENSIONES Siempre 0
					$valrem2 = $valD156;
				break;
				case "D900": //Bonificación  Siempre 0
					$valrem2 = $valD900;
				break;


				/*default:
					echo "Your favorite color is neither red, blue, nor green!";*/
			}
			
			if($finalSql!=''){
				$finalSql .= ' UNION ';
			}
			$finalSql .= insertValues($year,$month,$arrayListCC[$i],$rutrem,$linrem,$valrem,$valremc,$codhdrem,$descriphdrem,$valrem2,$liqrem,$formvalrem,$digitarem,$settlement);


			if($j%20==0 || $j+1==count($arrayLiq)){
				$finalSql = "INSERT INTO REM021(
						aaaarem,
						mmrem,
						cc1rem,
						rutrem,
						linrem,
						valrem,
						valremc,
						codhdrem,
						descriphdrem,
						valrem2,
						liqrem,
						formvalrem,
						digitarem,
						cc1rem2,
						ID_FINIQUITO_PERSONAL) 
					SELECT aaaarem,
						mmrem,
						cc1rem,
						rutrem,
						linrem,
						valrem,
						valremc,
						codhdrem,
						descriphdrem,
						valrem2,
						liqrem,
						formvalrem,
						digitarem,
						cc1rem2,
						ID_FINIQUITO_PERSONAL
					FROM (".$finalSql.")";
				//echo $finalSql.'<br>EEEEEEEEEEEEEEEEEEEEEEEE';
				executeSql($finalSql);

				/*if($arrayList[$i]==12780458){
					echo $finalSql.'<br>EEEEEEEEEEEEEEEEEEEEEEEE';
				}*/

				$finalSql = '';
			}
		}
		
		executeSql("INSERT INTO REM02(
					aaaarem,
					mmrem,
					cc1rem,
					rutrem,
					statrem,
					ulinrem,
					ID_FINIQUITO_PERSONAL) 
					VALUES(
					".$year.",
					".$month.",
					'".$arrayListCC[$i]."',
					'".$rutrem."',
					'A',
					".count($arrayLiq).",
					".$settlement.")");
		
		//executeSelect("SELECT * FROM REM02 WHERE aaaarem=");
		//executeSql("INSERT INTO REM02_TEMPORAL(rut) VALUES(".$arrayList[$i].")");
	}

	echo 'OK';

}elseif($_POST['type']=='oneManual'){
	$year = $_POST['year'];
	$month = $_POST['month'];
	$costCenter = $_POST['costCenter'];
	$rut = $_POST['rut'];
	$list = $_POST['list'];
	$settlement = $_POST['settlement'];

	$arrayList = explode('&&&&', $list);

	$whereSettlement = "AND ID_FINIQUITO_PERSONAL=".$settlement;
	if($settlement==0){
		$whereSettlement = "AND (ID_FINIQUITO_PERSONAL=0 OR ID_FINIQUITO_PERSONAL IS NULL)";
	}
	//executeSql("DELETE FROM REM021_MANUAL
	executeSql("DELETE FROM REM021
				WHERE VAL(rutrem)=".$rut." 
				AND aaaarem=".$year." 
				AND mmrem=".$month." 
				AND VAL(cc1rem)=".$costCenter." 
				".$whereSettlement."
				AND codhdrem IN ('H007','H008','H017','H018','H019','H020','H040','H160','D015','D026','D027','D028','D029','D030','D031','D054','D055','D121','D122','D123','D150','D151','D155')");

	for($i=0;$i<count($arrayList);$i++){

		$arrayListDetail = explode('&&', $arrayList[$i]);

		executeSql("INSERT INTO REM021(
					rutrem,
					aaaarem,
					mmrem,
					cc1rem,
					linrem,
					codhdrem,
					valrem,
					valremc,
					valrem2,
					ID_FINIQUITO_PERSONAL)
					VALUES(
					'".$rut."',
					".$year.",
					".$month.",
					'".$costCenter."',
					'".$arrayListDetail[2]."',
					'".$arrayListDetail[0]."',
					0,
					'',
					".$arrayListDetail[1].",
					".$settlement."
					)");
		/*executeSql("INSERT INTO REM021_MANUAL(
					rutrem,
					aaaarem,
					mmrem,
					cc1rem,
					codhdrem,
					valrem,
					valremc,
					valrem2,
					ID_FINIQUITO_PERSONAL)
					VALUES(
					'".$rut."',
					".$year.",
					".$month.",
					'".$costCenter."',
					'".$arrayListDetail[0]."',
					0,
					'',
					".$arrayListDetail[1].",
					".$settlement."
					)");*/
	}

	echo 'OK';

}elseif($_POST['type']=='fullManual'){
	$year = $_POST['year'];
	$month = $_POST['month'];
	$manual = $_POST['manual'];
	$listCC = $_POST['listCostCenter'];
	$list = $_POST['list'];
	$listValues = $_POST['listValues'];

	$arrayList = explode(',', $list);
	$arrayListCC = explode(',', $listCC);
	$arrayListValues = explode(',', $listValues);

	for($i=0;$i<count($arrayList);$i++){

		$arrayData = executeSelect("SELECT * FROM REM021_MANUAL
									WHERE VAL(rutrem)=".$arrayList[$i]." 
									AND aaaarem=".$year." 
									AND mmrem=".$month." 
									AND VAL(cc1rem)=".$arrayListCC[$i]."
									AND codhdrem='".$manual."'");

		if(count($arrayData)>0){
			executeSql("UPDATE REM021_MANUAL
						SET valrem2 = ".$arrayListValues[$i]."
						WHERE VAL(rutrem)=".$arrayList[$i]." 
						AND aaaarem=".$year." 
						AND mmrem=".$month." 
						AND VAL(cc1rem)=".$arrayListCC[$i]."
						AND codhdrem='".$manual."'");

		}else{
			executeSql("INSERT INTO REM021_MANUAL(
						rutrem,
						aaaarem,
						mmrem,
						cc1rem,
						codhdrem,
						valrem,
						valremc,
						valrem2)
						VALUES(
						'".$arrayList[$i]."',
						".$year.",
						".$month.",
						'".$arrayListCC[$i]."',
						'".$manual."',
						0,
						'',
						".$arrayListValues[$i]."
						)");
		}
	}

	echo 'OK';
}


function insertValues($aaaarem,$mmrem,$cc1rem,$rutrem,$linrem,$valrem,$valremc,$codhdrem,$descriphdrem,$valrem2,$liqrem,$formvalrem,$digitarem,$settlement){

	$cc1rem2 = '';
	$sql = "SELECT TOP 1
			".$aaaarem." AS aaaarem,
			".$mmrem." AS mmrem,
			'".$cc1rem."' AS cc1rem,
			'".$rutrem."' AS rutrem,
			".$linrem." AS linrem,
			".$valrem." AS valrem,
			'".$valremc."' AS valremc,
			'".$codhdrem."' AS codhdrem,
			'".$descriphdrem."' AS descriphdrem,
			".$valrem2." AS valrem2,
			".$liqrem." AS liqrem,
			'".$formvalrem."' AS formvalrem,
			'".$digitarem."' AS digitarem,
			'".$cc1rem2."' AS cc1rem2,
			".$settlement." AS ID_FINIQUITO_PERSONAL
			FROM ENTIDADES";
	//echo $sql;

	return $sql;

}


function insertValuesOld($aaaarem,$mmrem,$cc1rem,$rutrem,$linrem,$valrem,$valremc,$codhdrem,$descriphdrem,$valrem2,$liqrem,$formvalrem,$digitarem){

	$cc1rem2 = '';
	$sql = "INSERT INTO REM021(
				aaaarem,
				mmrem,
				cc1rem,
				rutrem,
				linrem,
				valrem,
				valremc,
				codhdrem,
				descriphdrem,
				valrem2,
				liqrem,
				formvalrem,
				digitarem,
				cc1rem2) 
				VALUES(
				".$aaaarem.",
				".$mmrem.",
				'".$cc1rem."',
				'".$rutrem."',
				".$linrem.",
				".$valrem.",
				'".$valremc."',
				'".$codhdrem."',
				'".$descriphdrem."',
				".$valrem2.",
				".$liqrem.",
				'".$formvalrem."',
				'".$digitarem."',
				'".$cc1rem2."')";
	//echo $sql;

	executeSql($sql);

}

?>