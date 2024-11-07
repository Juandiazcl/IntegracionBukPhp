<?php
include("../connection/connection.php");
session_start();
set_time_limit(40000);

//getIdEmpleadoAct($idCamp);

if($_POST['type']=='all'){

	$year = $_POST['year'];
	$month = $_POST['month'];
	$state = $_POST['state'];
	$plant = $_POST['plant'];
	//getIdEmpleadoAct($plant);
	if(isset($plant)){

		$plantWhere = '';
		if($plant!=0 && $plant!=98){
			$plantWhere = "AND VAL(t.cc1tj)=$plant";
		}



		$stateWhere = '';
		if($state!="0"){
			$stateWhere = "AND t.stattj='$state'";
		}

		$chkDate = $_POST['chkDate'];
		$dateWhere = '';
		if($chkDate==1){
			$date = explode("/", $_POST['date']);
			$retardDate = $date[1]."/".$date[0]."/".$date[2];
			$dateWhere = " AND t.fechatj=#$retardDate#";
		}


		$sql = "SELECT 
				cc.PlNombre AS plant,
				FORMAT(t.fechatj,'dd/mm/yyyy') AS tally_date,
				FORMAT(t.fechatj,'yyyy-mm-dd') AS tally_date2,
				t.usremitj AS tally_user,
				FORMAT(t.fecemitj,'dd/mm/yyyy') AS emission_date,
				t.usrvbtj AS vb_user,
				FORMAT(t.fecvbtj,'dd/mm/yyyy') AS vb_date,
				stattj,
				IIF(t.stattj = 'C' , 'Cerrada', 'En Digitación') AS state,
				(SELECT COUNT(t1.fichatj) FROM TARJASBUK2 t1 WHERE t1.cc1tj=t.cc1tj AND t1.fechatj=t.fechatj) AS personal_quantity,

				t.fechatj,
				t.cc1tj


				FROM (TARJAS t
				LEFT JOIN T0010b cc ON cc.Pl_codigo=VAL(t.cc1tj))

				WHERE YEAR(t.fechatj)=$year AND MONTH(t.fechatj)=$month
				$plantWhere $stateWhere $dateWhere

				ORDER BY t.fechatj DESC";

				//WHERE t.fechatj BETWEEN #08/01/2020# AND #08/25/2020#";

				//echo $sql;

		$array = executeSelect($sql);

		if(count($array)>0){

			for($i=0;$i<count($array);$i++){
				$tallyDate = new DateTime($array[$i]['tally_date2']);
				
				switch ($tallyDate->format('l')) {
					case 'Monday':
						$day = 'Lunes';
					break;
					case 'Tuesday':
						$day = 'Martes';
					break;
					case 'Wednesday':
						$day = 'Mi&eacute;rcoles';
					break;
					case 'Thursday':
						$day = 'Jueves';
					break;
					case 'Friday':
						$day = 'Viernes';
					break;
					case 'Saturday':
						$day = 'S&aacute;bado';
					break;
					case 'Sunday':
						$day = 'Domingo';
					break;
				}
				//echo $_SESSION['profile'];
				if($array[$i]['stattj']=="A"){
					$array[$i]["vb_date"] = '';
					$array[$i]["edit"] = '<button class="btn btn-warning" 
											onclick="modalTally(\'edit\',\''.$array[$i]["tally_date"].'\',\''.$array[$i]["cc1tj"].'\',\''.$array[$i]["plant"].'\',\''.$day.'\')" title="Editar">
										<i class="fa fa-edit fa-lg fa-fw"></i></button>';
					$array[$i]["excel"] = '<button class="btn btn-success" 
											onclick="toExcel(\''.$array[$i]["cc1tj"].'\',\''.$array[$i]["plant"].'\',\''.$array[$i]["tally_date"].'\')" title="Exportar detalle a Excel">
										<img src="../../images/excel.ico"/></button>';

					$array[$i]["close"] = '<button class="btn btn-primary" 
											onclick="closeTally(\''.$array[$i]["tally_date"].'\',\''.$array[$i]["cc1tj"].'\',\'close\')" title="Cerrar Tarja">
										<i class="fa fa-check fa-lg fa-fw"></i></button>';

					$array[$i]["delete"] = '<button class="btn btn-danger" 
											onclick="deleteTally(\''.$array[$i]["tally_date"].'\',\''.$array[$i]["cc1tj"].'\')" title="Eliminar Tarja">
										<i class="fa fa-remove fa-lg fa-fw"></i></button>';
				}else{
					$array[$i]["edit"] = '<button class="btn btn-primary" 
											onclick="modalTally(\'view\',\''.$array[$i]["tally_date"].'\',\''.$array[$i]["cc1tj"].'\',\''.$array[$i]["plant"].'\',\''.$day.'\')" title="Ver">
										<i class="fa fa-eye fa-lg fa-fw"></i></button>';

					$array[$i]["excel"] = '<button class="btn btn-success" 
											onclick="toExcel(\''.$array[$i]["cc1tj"].'\',\''.$array[$i]["plant"].'\',\''.$array[$i]["tally_date"].'\')" title="Exportar detalle a Excel">
										<img src="../../images/excel.ico"/></button>';

					if($_SESSION['profile']=='ADM'){
						$array[$i]["close"] = '<button class="btn btn-success" 
											onclick="closeTally(\''.$array[$i]["tally_date"].'\',\''.$array[$i]["cc1tj"].'\',\'open\')" title="Reabrir Tarja">
											<i class="fa fa-check fa-lg fa-fw"></i></button>';
						$array[$i]["delete"] = '<button class="btn btn-danger" 
											onclick="deleteTally(\''.$array[$i]["tally_date"].'\',\''.$array[$i]["cc1tj"].'\')" title="Eliminar Tarja">
										<i class="fa fa-remove fa-lg fa-fw"></i></button>';
					}else{
						$array[$i]["close"] = '<button class="btn btn-success" title="Tarja Cerrada" disabled>
												<i class="fa fa-check fa-lg fa-fw"></i></button>';
						$array[$i]["delete"] = '';
					}
				}

			}

			echo json_encode(utf8ize($array));
		}else{
			echo 0;
		}

	}else{
		echo 0;
	}


}elseif($_POST['type']=='one'){

	$date = $_POST['date'];
	$plant = $_POST['plant'];

	// echo "Fecha   ";
	// echo $date;
	
	 // $fecStr=date_format($date, 'dd/mm/yyyy');
	 
	//  
	if($date[2]=='/'){
		$date=str_replace("/","-",$date);
	}
	//  echo $aa;

	// fecha access nuevas versiones
	$retardDate = explode("-", $date);
	$retardDate = $retardDate[1]."-".$retardDate[0]."-".$retardDate[2];

	//Fechas access server :3400
	//$retardDate = explode("/", $date);
	//$retardDate = $retardDate[1]."/".$retardDate[0]."/".$retardDate[2];


	$sql = "SELECT tr.*, tr.det_trato, tr.idLab, tr.idTar, lg.nombre as nomLug, lb.descriptionL, un.nombre AS nomUni, pr.nombre as nomPro, tr.valtj, tr.rut_per FROM ((((TARJASBUK2 tr
			left join LugaresBuk lg on lg.id_lugar=tr.idLug)
			left join LaboresBuk lb on lb.id=tr.idLab)
			left join UnidadesBuk un on un.id_unidad=tr.idUni)
			left join ProductosBuk pr on pr.id_product=tr.idPro)
			WHERE VAL(cc1tj)=$plant
			AND fechatj=#$retardDate#
			ORDER BY codtj";

	//echo $sql;		

	$array = executeSelect($sql);

	if(count($array)>0){

		for($i=0;$i<count($array);$i++){
			
			//$array[$i]['fichatj'] = number_format($array[$i]['fichatj'],0,'','');
			$array[$i]['rut_per'] = number_format($array[$i]['rut_per'],0,'','');
			if(!is_int($array[$i]['jornadatj'])){
				$array[$i]['jornadatj'] = number_format($array[$i]['jornadatj'],2,',','.');
			}else{
				$array[$i]['jornadatj'] = number_format($array[$i]['jornadatj'],0,'','.');
			}
			if(!is_int($array[$i]['rendtj'])){
				$array[$i]['rendtj'] = number_format($array[$i]['rendtj'],2,',','.');
			}else{
				$array[$i]['rendtj'] = number_format($array[$i]['rendtj'],0,'','.');
			}
			if(!is_int($array[$i]['hhtj'])){
				$array[$i]['hhtj'] = number_format($array[$i]['hhtj'],2,',','.');
			}else{
				$array[$i]['hhtj'] = number_format($array[$i]['hhtj'],0,'','.');
			}

		}

		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}

}elseif($_POST['type']=='oneExcel'){
	//envioEmpleados();
	//envioEmpleadoE1(67);
	//envioEmpleadoE2(67);

	$date = $_POST['date'];
	$plant = $_POST['plant'];

	// fecha access nuevas versiones
	$retardDate = explode("-", $date);
	$retardDate = $retardDate[1]."-".$retardDate[0]."-".$retardDate[2];

	//Fechas access server :3400
	//$retardDate = explode("/", $date);
	//$retardDate = $retardDate[1]."/".$retardDate[0]."/".$retardDate[2];

	$sql = "SELECT t.*,
			(SELECT nombre FROM LugaresBuk cc2 WHERE cc2.id_lugar=t.idLug) AS CC2Descrip,
			(SELECT descriptionL FROM LaboresBuk cc3 WHERE cc3.id=t.idLab) AS CC3Descrip,
			(SELECT valor_tarifa FROM TarifasBuk t11 WHERE t11.id_tarifa_buk=t.idTar) AS T11Val
			FROM TARJASBUK2 t
			WHERE VAL(t.cc1tj)=$plant
			AND t.fechatj=#$retardDate#
			ORDER BY t.codtj";
	
	//echo $sql;

	$array = executeSelect($sql);

	if(count($array)>0){

		for($i=0;$i<count($array);$i++){
			
			//$array[$i]['fichatj'] = number_format($array[$i]['fichatj'],0,'','');
			$array[$i]['rut_per'] = number_format($array[$i]['rut_per'],0,'','');
			if(!is_int($array[$i]['jornadatj'])){
				$array[$i]['jornadatj'] = number_format($array[$i]['jornadatj'],2,',','.');
			}else{
				$array[$i]['jornadatj'] = number_format($array[$i]['jornadatj'],0,'','.');
			}
			if(!is_int($array[$i]['rendtj'])){
				$array[$i]['rendtj'] = number_format($array[$i]['rendtj'],2,',','.');
			}else{
				$array[$i]['rendtj'] = number_format($array[$i]['rendtj'],0,'','.');
			}
			if(!is_int($array[$i]['hhtj'])){
				$array[$i]['hhtj'] = number_format($array[$i]['hhtj'],2,',','.');
			}else{
				$array[$i]['hhtj'] = number_format($array[$i]['hhtj'],0,'','.');
			}

			if(!is_int($array[$i]['T11Val'])){
				$array[$i]['T11Val'] = number_format($array[$i]['T11Val'],2,',','.');
			}else{
				$array[$i]['T11Val'] = number_format($array[$i]['T11Val'],0,'','.');
			}
		}

		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}

}elseif($_POST['type']=='getLastTally'){

	$year = $_POST['year'];
	$month = $_POST['month'];
	$plant = $_POST['plant'];

	//getIdEmpleadoAct($plant);

	$arrayDate = executeSelect("SELECT * FROM T0058 WHERE Mes=$month AND ANO=$year");
	if(count($arrayDate)>0){

		$sql = "SELECT *,
				FORMAT(fechatj,'yyyy-mm-dd') AS tally_date
				FROM TARJAS
				WHERE YEAR(fechatj)=$year AND MONTH(fechatj)=$month
				AND VAL(cc1tj)=$plant
				AND NOT stattj='X' 
				ORDER BY fechatj DESC";
		$array = executeSelect($sql);

		//Buscar primer día disponible, sin contar domingos ni festivos
		if(count($array)>0){
			//echo json_encode(utf8ize($array));


			if($array[0]['stattj']=='C'){
				$actualDate = new DateTime($array[0]['tally_date']);
				$lastDate = new DateTime($actualDate->format('Y-m-t'));

				while($actualDate <= $lastDate){
					$actualDate->modify('+1 day');
				    $arrayHoliday = executeSelect("SELECT * FROM DIAS_FESTIVOS WHERE Fecha=#".$actualDate->format('m/d/Y')."#");
				    
				    if($actualDate->format('l')!='Sunday' && count($arrayHoliday)==0){//Si la fecha no es domingo ni festivo
						break;
					}
				}

				if($actualDate->format('m')==$month){
					$day = '';
					switch ($actualDate->format('l')) {
						case 'Monday':
							$day = 'Lunes';
						break;
						case 'Tuesday':
							$day = 'Martes';
						break;
						case 'Wednesday':
							$day = 'Miércoles';
						break;
						case 'Thursday':
							$day = 'Jueves';
						break;
						case 'Friday':
							$day = 'Viernes';
						break;
						case 'Saturday':
							$day = 'Sábado';
						break;
						case 'Sunday':
							$day = 'Domingo';
						break;
					}
					echo $actualDate->format('d/m/Y').'_'.$day; //Primera fecha disponible
				}else{
					echo 2; //No quedan fechas disponibles para el mes activo
				}
			}else{
				echo 1; //Indica que última tarja aún está en digitación
			}
		}else{
			$actualDate = new DateTime("$year-$month-01");
			$lastDate = new DateTime($actualDate->format('Y-m-t'));

			while($actualDate <= $lastDate){
			    //echo $actualDate->format('Y-m-d');
			    //echo $actualDate->format('l');

			    $arrayHoliday = executeSelect("SELECT * FROM DIAS_FESTIVOS WHERE Fecha=#".$actualDate->format('m/d/Y')."#");
			    if($actualDate->format('l')=='Sunday' || count($arrayHoliday)>0){
					$actualDate->modify('+1 day');
				}else{
					break;
				}
			}


			$day = '';
					switch ($actualDate->format('l')) {
						case 'Monday':
							$day = 'Lunes';
						break;
						case 'Tuesday':
							$day = 'Martes';
						break;
						case 'Wednesday':
							$day = 'Miércoles';
						break;
						case 'Thursday':
							$day = 'Jueves';
						break;
						case 'Friday':
							$day = 'Viernes';
						break;
						case 'Saturday':
							$day = 'Sábado';
						break;
						case 'Sunday':
							$day = 'Domingo';
						break;
					}
			echo $actualDate->format('d/m/Y').'_'.$day;
			//echo $actualDate->format('d/m/Y'); //Primera fecha disponible
		}



	}else{
		echo 0; //Creación de tarja en mes no habilitado
	}

}elseif($_POST['type']=='personal'){

	$year = $_POST['year'];
	$month = $_POST['month'];
	$state = 'V';
	$plant = 98;
	if(isset($_POST['plant'])){
		$plant = $_POST['plant'];
	}
//echo $plant;
	$treg=getIdEmpleadoAct($plant);
	//echo $treg; 

	if ($treg==0){
		echo 0;
		exit();
	} else {
	$where = "";
	if($state!='T'){
		$where .= "WHERE p.estado_per='$state'";
	}

	if($plant!=98){
		if($where==""){
			$where .= "WHERE p.planta_per=$plant";
		}else{
			$where .= " AND p.planta_per=$plant";
		}
	}
	$sqlk="SELECT STR(p.rut_per) AS id,
	p.rut_per,
	p.dv_per,
	p.Nom_per & ' ' & p.Apepat_per & ' ' & p.Apemat_per AS fullname,
	p.estado_per AS status,
	t.PlNombre AS plant,
	t.Pl_codigo AS plant_id,
	(SELECT l.descriplb FROM LABOR l WHERE l.codlb=1) AS charge,
	(SELECT SUM(ta.jornadatj) FROM TARJASBUK2 ta WHERE ta.rut_per=p.rut_per AND MONTH(ta.fechatj)=$month AND YEAR(ta.fechatj)=$year) AS workingDays

	FROM (PERSONAL3 p
	LEFT JOIN T0010b t ON t.Pl_codigo=VAL(p.planta_per))
	$where
	ORDER BY p.rut_per";
	//echo $sqlk;
	//VAL(p.hi_tpcargo)
	$array = executeSelect("SELECT STR(p.rut_per) AS id,
							p.rut_per,
							p.dv_per,
							p.Nom_per & ' ' & p.Apepat_per & ' ' & p.Apemat_per AS fullname,
							p.estado_per AS status,
							t.PlNombre AS plant,
							t.Pl_codigo AS plant_id,
							(SELECT l.descriplb FROM LABOR l WHERE l.codlb=1) AS charge,
							(SELECT SUM(ta.jornadatj) FROM TARJASBUK2 ta WHERE ta.rut_per=p.rut_per AND MONTH(ta.fechatj)=$month AND YEAR(ta.fechatj)=$year) AS workingDays

							FROM (PERSONAL3 p
							LEFT JOIN T0010b t ON t.Pl_codigo=VAL(p.planta_per))
							$where
							ORDER BY p.rut_per");

	if(count($array)>0){
		for($i=0;$i<count($array);$i++){

			$array[$i]['rut'] = number_format($array[$i]['rut_per'],0,'','.').'-'.$array[$i]['dv_per'];
			$array[$i]['rut_per'] = number_format($array[$i]['rut_per'],0,'','');
			if(is_float($array[$i]['workingDays'])){
				$array[$i]['workingDays'] = number_format($array[$i]['workingDays'],2,',','');
			}else{
				$array[$i]['workingDays'] = number_format($array[$i]['workingDays'],0,'','');
			}


			if($_SESSION['profile']=='ADM'){
				$array[$i]["edit"] = '<button class="btn btn-warning" 
											onclick="modalTallyOne(\'edit\',\''.$array[$i]['rut'].'\',\''.$array[$i]['rut_per'].'\',\''.$array[$i]['fullname'].'\',\''.$array[$i]['plant'].'\',\''.$plant.'\',\''.$month.'\',\''.$year.'\')" title="Editar">
										<i class="fa fa-edit fa-lg fa-fw"></i></button>';
			}else{
				$array[$i]["edit"] = '<button class="btn btn-primary" 
											onclick="modalTallyOne(\'view\',\''.$array[$i]['rut'].'\',\''.$array[$i]['rut_per'].'\',\''.$array[$i]['fullname'].'\',\''.$array[$i]['plant'].'\',\''.$plant.'\',\''.$month.'\',\''.$year.'\')" title="Editar">
										<i class="fa fa-eye fa-lg fa-fw"></i></button>';
			}
			$array[$i]["excel"] = '<button class="btn btn-success" 
										onclick="toExcel(\''.$array[$i]['rut'].'\',\''.$array[$i]['rut_per'].'\',\''.$array[$i]['fullname'].'\',\''.$array[$i]['plant'].'\',\''.$plant.'\',\''.$month.'\',\''.$year.'\')" title="Exportar detalle a Excel">
									<img src="../../images/excel.ico"/></button>';
			//onclick="modalTallyOne(\'edit\',\''.$array[$i]['rut'].'\',\''.$array[$i]['rut_per'].'\',\''.$array[$i]['fullname'].'\',\''.$array[$i]['plant'].'\',\''.$array[$i]['plant_id'].'\',\''.$month.'\',\''.$year.'\')" title="Editar">
		}
	}

	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}
}

}elseif($_POST['type']=='onePersonal'){

	$month = $_POST['month'];
	$year = $_POST['year'];
	$rut = $_POST['rut'];
	$plant = $_POST['plant'];

	//getIdLugares();
	//getIdLabores();

	$sql = "SELECT *, FORMAT(fechatj,'dd/mm/yyyy') AS tally_date, un.nombre as nomUni, pr.nombre as nomPro, tr.valtj as valtjF, tr.idTar as idTarF, tr.siTj  FROM ((TARJASBUK2 tr
			left join UnidadesBuk un on un.id_unidad=tr.idUni)
			left join ProductosBuk pr on pr.id_product=tr.idPro)
			WHERE cc1tj='$plant'
			AND MONTH(fechatj)=$month
			AND YEAR(fechatj)=$year
			AND rut_per=$rut
			ORDER BY fechatj";

	//echo $sql;

	$array = executeSelect($sql);

	if(count($array)>0){

		for($i=0;$i<count($array);$i++){
			
			//$array[$i]['fichatj'] = number_format($array[$i]['fichatj'],0,'','');
			$array[$i]['rut_per'] = number_format($array[$i]['rut_per'],0,'','');
			if(!is_int($array[$i]['jornadatj'])){
				$array[$i]['jornadatj'] = number_format($array[$i]['jornadatj'],2,',','.');
			}else{
				$array[$i]['jornadatj'] = number_format($array[$i]['jornadatj'],0,'','.');
			}
			if(!is_int($array[$i]['rendtj'])){
				$array[$i]['rendtj'] = number_format($array[$i]['rendtj'],2,',','.');
			}else{
				$array[$i]['rendtj'] = number_format($array[$i]['rendtj'],0,'','.');
			}
			if(!is_int($array[$i]['hhtj'])){
				$array[$i]['hhtj'] = number_format($array[$i]['hhtj'],2,',','.');
			}else{
				$array[$i]['hhtj'] = number_format($array[$i]['hhtj'],0,'','.');
			}

		}

		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}


}elseif($_POST['type']=='onePersonalExcel'){

	$month = $_POST['month'];
	$year = $_POST['year'];
	$rut = $_POST['rut'];
	$plant = $_POST['plant'];

	// getIdLugares();
	// getIdLabores();
	// getIdtarifas();

	// -- SELECT cc4.Labor2 FROM CC22 cc4 WHERE cc4.cc2=t.cc2tj AND cc4.cc3=t.cc3tj AND cc4.cc4=t.cc4tj) AS CC4Descrip,
	// 		-- (SELECT t1.descattrt FROM TRATOS1 t1 WHERE t1.cc1trt=t.cc1tj AND t1.cattrt=t.cattrt) AS T1Descrip,
	// 		-- (SELECT t11.desctrt FROM TRATOS11 t11 WHERE t11.cc1trt=t.cc1tj AND t11.cattrt=t.cattrt AND t11.codtrt=t.codtrt) AS T11Descrip,
	// 		-- (SELECT t11.val1trt FROM TRATOS11 t11 WHERE t11.cc1trt=t.cc1tj AND t11.cattrt=t.cattrt AND (t11.codtrt=t.codtrt) AS T11Val
	//(SELECT formula FROM TarifasBuk t11 WHERE t11.codigo_lugar=t. AND t11.cattrt=t.cattrt AND (t11.codtrt=t.codtrt) AS T11Val


	$sql = "SELECT t.*, FORMAT(t.fechatj,'dd/mm/yyyy') AS tally_date
			(SELECT nombre FROM LugaresBuk cc2 WHERE cc2.code=t.LugarBuk) AS CC2Descrip,
			(SELECT descriptionL FROM LaboresBuk cc3 WHERE cc3.code=t.codigo_labor_buk) AS CC3Descrip,
			(SELECT valor_tarifa FROM TarifasBuk t11 WHERE t11.id_tarifa_buk=t.idTar) AS T11Val
			FROM TARJASBUK2 t
			WHERE t.cc1tj='$plant'
			AND MONTH(t.fechatj)=$month
			AND YEAR(t.fechatj)=$year
			AND t.rut_per=$rut
			ORDER BY t.fechatj";

//echo $sql;

$array = executeSelect($sql);



	if(count($array)>0){

		$arrayTotal['jornadatjTotal'] = 0;
		$arrayTotal['rendtjTotal'] = 0;
		$arrayTotal['hhtjTotal'] = 0;
		$arrayTotal['rendTotal'] = 0;

		for($i=0;$i<count($array);$i++){
			
			
			//$array[$i]['fichatj'] = number_format($array[$i]['fichatj'],0,'','');
			$array[$i]['rut_per'] = number_format($array[$i]['rut_per'],0,'','');
			$array[$i]['rendTotal'] = $array[$i]['rendtj'] * $array[$i]['T11Val'];

			$arrayTotal['jornadatjTotal'] += $array[$i]['jornadatj'];
			$arrayTotal['rendtjTotal'] += $array[$i]['rendtj'];
			$arrayTotal['hhtjTotal'] += $array[$i]['hhtj'];
			$arrayTotal['rendTotal'] += $array[$i]['rendTotal'];

			if(!is_int($array[$i]['jornadatj'])){
				$array[$i]['jornadatj'] = number_format($array[$i]['jornadatj'],2,',','.');
			}else{
				$array[$i]['jornadatj'] = number_format($array[$i]['jornadatj'],0,'','.');
			}
			if(!is_int($array[$i]['rendtj'])){
				$array[$i]['rendtj'] = number_format($array[$i]['rendtj'],2,',','.');
			}else{
				$array[$i]['rendtj'] = number_format($array[$i]['rendtj'],0,'','.');
			}
			if(!is_int($array[$i]['hhtj'])){
				$array[$i]['hhtj'] = number_format($array[$i]['hhtj'],2,',','.');
			}else{
				$array[$i]['hhtj'] = number_format($array[$i]['hhtj'],0,'','.');
			}
			if(!is_int($array[$i]['T11Val'])){
				$array[$i]['T11Val'] = number_format($array[$i]['T11Val'],2,',','.');
			}else{
				$array[$i]['T11Val'] = number_format($array[$i]['T11Val'],0,'','.');
			}
			
			$array[$i]['rendTotal'] = number_format($array[$i]['rendTotal'],0,'','.');
			
		}

		$arrayTotal['jornadatjTotal'] = number_format($arrayTotal['jornadatjTotal'],2,',','.');
		$arrayTotal['rendtjTotal'] = number_format($arrayTotal['rendtjTotal'],2,',','.');
		$arrayTotal['hhtjTotal'] = number_format($arrayTotal['hhtjTotal'],2,',','.');
		$arrayTotal['rendTotal'] = number_format($arrayTotal['rendTotal'],0,'','.');

		array_push($array, $arrayTotal);

		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}
} elseif($_POST['type']=='newWorked'){
	$plant = $_POST['plant'];
	getIdEmpleadoAct2($plant);
} elseif($_POST['type']=='oneExcel2'){
	$mes = $_POST['mes'];
	$year = $_POST['year'];

	// fecha access nuevas versiones
	// $retardDate = explode("-", $date);
	// $retardDate = $retardDate[1]."-".$retardDate[0]."-".$retardDate[2];

	//Fechas access server :3400
	//$retardDate = explode("/", $date);
	//$retardDate = $retardDate[1]."/".$retardDate[0]."/".$retardDate[2];

	$sql = "SELECT format(t.fechatj,'dd/mm/yyyy') as dia, t.nomtrabtj, t.fichatj as rut, t.idtar,
			IIF(t.idtar > 0 , 'Licencia', 'Inasistencia') AS tipoAusencia,
			(SELECT PlNombre FROM t0010b cam WHERE cam.Pl_codigo=val(t.cc1tj)) AS nomCampo				
			FROM TARJASBUK2 t
			WHERE det_trato='ausencia' and idtar<>2
			AND YEAR(T.fechatj)=".$year." AND MONTH(T.fechatj)=".$mes."
			ORDER BY t.cc1tj";
	
	//echo $sql;

	$array = executeSelect($sql);

	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}
} elseif($_POST['type']=='oneExcel3'){
	$mes = $_POST['mes'];
	$year = $_POST['year'];

	// fecha access nuevas versiones
	// $retardDate = explode("-", $date);
	// $retardDate = $retardDate[1]."-".$retardDate[0]."-".$retardDate[2];

	//Fechas access server :3400
	//$retardDate = explode("/", $date);
	//$retardDate = $retardDate[1]."/".$retardDate[0]."/".$retardDate[2];

	$sql = "select t.fichatj as rut, t.nomtrabtj, format(t.fechatj,'dd/mm/yyyy') as dia , tc.PlNombre,  round(t.hhtj, 1) as hExt from tarjasbuk2 t
	left join t0010b as tc on tc.pl_codigo=val(t.cc1tj)
	WHERE HHTJ>0 AND YEAR(t.fechatj)=".$year." AND MONTH(t.fechatj)=".$mes."
	ORDER BY t.RUT_PER";
	
	//echo $sql;

	$array = executeSelect($sql);


	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}
} elseif($_POST['type']=='oneExcel4'){
	$mes = $_POST['mes'];
	$year = $_POST['year'];

	// fecha access nuevas versiones
	// $retardDate = explode("-", $date);
	// $retardDate = $retardDate[1]."-".$retardDate[0]."-".$retardDate[2];

	//Fechas access server :3400
	//$retardDate = explode("/", $date);
	//$retardDate = $retardDate[1]."/".$retardDate[0]."/".$retardDate[2];

	$sql = "select trim(cc.rutEmpleado)+'-'+PE.dv_per as rut, cc.cc,  round(cc.porcPeso, 1) as pPeso from (CCXempleado cc
	LEFT JOIN PERSONAL3 AS PE ON PE.rut_per=cc.rutEmpleado) 
	ORDER BY rutEmpleado";
	
	//echo $sql;

	$array = executeSelect($sql);


	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}
}
//exit();
// Trae empleados segun campo.
	function getIdEmpleadoAct($idCampo){

	$treg=0;
	$j=1;
	$pag='';
	$cont=0;
	//echo 'test GetIdEmpleadoAct';
	//executeSql("DELETE from PERSONAL3");

	while($cont==0){
	$pag=intval($j);
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
    $LinkEmp='https://remuneracionagricola.buk.cl/api/v1/chile/employees?custom_attr_job_name=Campo&custom_attr_job_value='.$camFiltrado.'&page_size=100&page='.$pag;
	//echo $LinkEmp;
	$jsonGuide = json_decode(bsaleGET($LinkEmp, 'H1z6Gc8abmzd217CmKzxbzTj'), true);

	//echo "Detalle JsonGuide:   ";

	//echo var_dump($jsonGuide);
	//echo "Total registros: ";
	//echo count($jsonGuide['data']);
	
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
		if (count($jsonGuide['data'])<100){
			$cont=1;
			$treg=$treg+count($jsonGuide['data']);
			//echo " Total de registros Api: ";
			//echo $treg;
		   }
			else{
			$treg=$treg+100;
			$j=$j+1;	
			}
	}
	if(count($jsonGuide['data'])>0){
		return 1;
	} else {
		return 0;
	}
	}

// Trae empleados segun campo V2.
function getIdEmpleadoAct2($idCampo){

	$treg=0;
	$j=1;
	$pag='';
	$cont=0;
	//echo 'test GetIdEmpleadoAct';
	executeSql("DELETE from PERSONAL3 WHERE planta_per=$idCampo");

	while($cont==0){
	$pag=intval($j);
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
    $LinkEmp='https://remuneracionagricola.buk.cl/api/v1/chile/employees?custom_attr_job_name=Campo&custom_attr_job_value='.$camFiltrado.'&page_size=100&page='.$pag;
	//echo $LinkEmp;
	$jsonGuide = json_decode(bsaleGET($LinkEmp, 'H1z6Gc8abmzd217CmKzxbzTj'), true);

	//echo "Detalle JsonGuide:   ";

	//echo var_dump($jsonGuide);
	//echo "Total registros: ";
	//echo count($jsonGuide['data']);
	
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
		if (count($jsonGuide['data'])<100){
			$cont=1;
			$treg=$treg+count($jsonGuide['data']);
			//echo " Total de registros Api: ";
			//echo $treg;
		   }
			else{
			$treg=$treg+100;
			$j=$j+1;	
			}
	}
	if(count($jsonGuide['data'])>0){
		return 1;
	} else {
		return 0;
	}
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
	//echo $response;
    return $response;
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
	function getIdtarifas(){
	 $treg=0;
	 $j=1;
	 $pag='';
	 $cont=0;
	 //echo "Prueba   ";
	 executeSql("delete from TarifasBuk"); 
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
		   executeSql("INSERT INTO TarifasBuk (id_tarifa_buk, codigo_labor, codigo_unidad, codigo_lugar, codigo_producto, valor_tarifa, fecha_inicio, tipo_tarifa) values (" 
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

function envioEmpleados(){
	//***** Envio Api EMpleados *****
		 // $data3a =  json_encode($jsona);
		 // echo $data3a;
	$sql3="SELECT *, FORMAT(fecnac_per,'yyyy-mm-dd') as fecnac_perf, FORMAT(fecing_per,'yyyy-mm-dd') as fecing_perf FROM PERSONAL 
	where rut_per=16228495";
	$array3 = executeSelect($sql3);
	
	
		 // echo "Numero de registros";
		//  echo count($array3);
		  for($i=0;$i<count($array3);$i++){
		   $rutComp=strval(round($array3[$i]['rut_per']))."-".$array3[$i]['dv_per'];
		 echo "    ";
		 echo $rutComp;
		 echo "    ";
		 echo $array3[$i]['Nom_per'];
		 echo "    ";
	
	
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
			   'personal_email' => 'a00009@gmail.com',
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
		 //json_encode($json3);
		  
	   $jsonGuideRaw = bsalePOST('https://remuneracionagricola.buk.cl/api/v1/chile/employees', 'H1z6Gc8abmzd217CmKzxbzTj', $data3);
	   echo $data3;
		 }
		// executeSql("UPDATE PERSONAL SET id_buk=0");
	}
	
	
	//***** Envio Api EMpleados endpoint Jobs Necesita Id Buk *****
	function envioEmpleadoE1($idTra){
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
			$idBu=$idTra;
			$sql3b="SELECT *, FORMAT(fecing_per,'yyyy-mm-dd') as fechaing FROM PERSONAL where rut_per=8257475";
			 $array3b = executeSelect($sql3b);
	
	
		//  echo "Numero de registros";
		//  echo count($array3b);
		//'location_id '=> 143,
		 //echo "< Link para Post >";
			$linkBu="https://remuneracionagricola.buk.cl/api/v1/chile/employees/".$idBu."/jobs";
		echo $linkBu;
		  for($i=0;$i<count($array3b);$i++){
		   $rutComp=strval(round($array3b[$i]['rut_per']))."-".$array3b[$i]['dv_per'];
		 echo "    ";
		 echo $rutComp;
		 echo "    ";
		 echo $array3b[$i]['Nom_per'];
		 echo "    ";
	//	
	
		   $json3b = array(
			   'company_id' => 1,
			'start_date' => $array3b[$i]['fechaing'],
			   'type_of_contract' => 'Indefinido',
			   'end_of_contract' => $array3b[$i]['fec_fin_per'],
			'end_of_contract_2' => $array3b[$i]['fec_fin_per'],
			 'periodicity' => 'diaria',
			 'regular_hours' => 45,
			   'type_of_working_day' =>'ordinaria_art_22',
			'other_type_of_working_day' =>'extraordinaria_art_30',
			   'area_id' => 59,
			   'role_id' => 41,
			   'leader_id' => 39,
			   'wage' => $array3b[$i]['sbase_per'],
			 'currency' => 'peso',
			   'without_wage' => TRUE,
			   'reward_concept' => 'articulo_47',
			   'reward_payment_period' => 'gratificacion_mensual'
			   );
	
			
		  $data3 = json_encode(utf8ize($json3b)); 
		 //json_encode($json3b);
		  
	   $jsonGuideRaw = bsalePOST($linkBu, 'H1z6Gc8abmzd217CmKzxbzTj', $data3);
	   echo $data3;
		 }
	}
	
	//***** Envio Api Empleados endpoint Plan Necesita Id Buk *****
	function envioEmpleadoE2($idTra){
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
		$idBu=$idTra;
		$sql3b="SELECT IIF(p.afp_per = '000' , 'no_cotiza', 'afp') AS TipoPrev, FORMAT(p.fecing_per,'yyyy-mm-dd') as fechaing,  IIF(p.afp_per<> '000', a.des_afpBuk,' ') as NomAfp, i.nom_isaBuk as Isapre, p.UF_isa_per As IsaUF, p.peso_isa_per as IsaClp, p.porc_isa_per as IsaPor from ((personal as p
		left join afp as a on a.cod_afp=p.afp_per)
		left join isapres as i on   i.cod_isa=p.isa_per)
		where rut_per=8257475";
		$array3b = executeSelect($sql3b);
	
	
	 echo "Numero de registros";
	 echo count($array3b);
	 echo "< Link para Post >";
	 //https://remuneracionagricola.buk.cl/api/v1/chile/employees/1/plans
		$linkBu="https://remuneracionagricola.buk.cl/api/v1/chile/employees/".$idBu."/plans";
	echo $linkBu;
	  for($i=0;$i<count($array3b);$i++){
	 //  $rutComp=strval(round($array3b[$i]['rut_per']))."-".$array3b[$i]['dv_per'];
	//  echo "    ";
	//  echo $rutComp;
	//  echo "    ";
	//  echo $array3b[$i]['Nom_per'];
	//  echo "    ";
	//  'start_date' => $array3b[$i]['fechaing'],
	
	   $json3b = array(
		   'pension_scheme' => $array3b[$i]['TipoPrev'],
		   'fund_quote' => $array3b[$i]['NomAfp'],
		   'health_company' => $array3b[$i]['Isapre'],
		'health_company_plan' => $array3b[$i]['IsaUF'],
		 'health_company_plan_currency' => $array3b[$i]['IsaClp'],
		 'health_company_plan_percentage' => $array3b[$i]['IsaPor'],
		   'afc' =>'normal',
		'disability' => false,
		'invalidity '=> 'no',
		   );
	
		
	  $data3 = json_encode(utf8ize($json3b)); 
	 //json_encode($json3b);
	 
	$jsonGuideRaw = bsalePOST($linkBu, 'H1z6Gc8abmzd217CmKzxbzTj', $data3);
	echo $data3;
	 }
	}
?>