<?php
include("../connection/connection.php");
session_start();

if($_POST['type']=='all'){

	$year = $_POST['year'];
	$month = $_POST['month'];
	$state = $_POST['state'];
	$plant = $_POST['plant'];
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
				(SELECT COUNT(t1.fichatj) FROM TARJAS1 t1 WHERE t1.cc1tj=t.cc1tj AND t1.fechatj=t.fechatj) AS personal_quantity,

				t.fechatj,
				t.cc1tj


				FROM (TARJAS t
				LEFT JOIN T0010 cc ON cc.Pl_codigo=VAL(t.cc1tj))

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

	$retardDate = explode("/", $date);
	$retardDate = $retardDate[1]."/".$retardDate[0]."/".$retardDate[2];

	$sql = "SELECT * FROM TARJAS1
			WHERE VAL(cc1tj)=$plant
			AND fechatj=#$retardDate#
			ORDER BY codtj";

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

	$date = $_POST['date'];
	$plant = $_POST['plant'];

	$retardDate = explode("/", $date);
	$retardDate = $retardDate[1]."/".$retardDate[0]."/".$retardDate[2];

	$sql = "SELECT t.*,
			(SELECT cc2.UnidAnalisis FROM CC2 cc2 WHERE cc2.cc2=t.cc2tj) AS CC2Descrip,
			(SELECT cc3.Labor FROM CC21 cc3 WHERE cc3.cc2=t.cc2tj AND cc3.cc3=t.cc3tj) AS CC3Descrip,
			(SELECT cc4.Labor2 FROM CC22 cc4 WHERE cc4.cc2=t.cc2tj AND cc4.cc3=t.cc3tj AND cc4.cc4=t.cc4tj) AS CC4Descrip,
			(SELECT t1.descattrt FROM TRATOS1 t1 WHERE t1.cc1trt=t.cc1tj AND t1.cattrt=t.cattrt) AS T1Descrip,
			(SELECT t11.desctrt FROM TRATOS11 t11 WHERE t11.cc1trt=t.cc1tj AND t11.cattrt=t.cattrt AND t11.codtrt=t.codtrt) AS T11Descrip,
			(SELECT t11.val1trt FROM TRATOS11 t11 WHERE t11.cc1trt=t.cc1tj AND t11.cattrt=t.cattrt AND t11.codtrt=t.codtrt) AS T11Val
			
			FROM TARJAS1 t
			WHERE VAL(t.cc1tj)=$plant
			AND t.fechatj=#$retardDate#
			ORDER BY t.codtj";

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

	$array = executeSelect("SELECT STR(p.rut_per) AS id,
							p.rut_per,
							p.dv_per,
							p.Nom_per & ' ' & p.Apepat_per & ' ' & p.Apemat_per AS fullname,
							p.estado_per AS status,
							t.PlNombre AS plant,
							t.Pl_codigo AS plant_id,
							(SELECT l.descriplb FROM LABOR l WHERE l.codlb=VAL(p.hi_tpcargo)) AS charge,
							(SELECT SUM(ta.jornadatj) FROM TARJAS1 ta WHERE ta.rut_per=p.rut_per AND MONTH(ta.fechatj)=$month AND YEAR(ta.fechatj)=$year) AS workingDays

							FROM (PERSONAL p
							LEFT JOIN T0010 t ON t.Pl_codigo=p.planta_per)
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

}elseif($_POST['type']=='onePersonal'){

	$month = $_POST['month'];
	$year = $_POST['year'];
	$rut = $_POST['rut'];
	$plant = $_POST['plant'];

	$sql = "SELECT *, FORMAT(fechatj,'dd/mm/yyyy') AS tally_date FROM TARJAS1
			WHERE cc1tj='$plant'
			AND MONTH(fechatj)=$month
			AND YEAR(fechatj)=$year
			AND rut_per=$rut
			ORDER BY fechatj";

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

	$sql = "SELECT t.*, FORMAT(t.fechatj,'dd/mm/yyyy') AS tally_date,
			(SELECT cc2.UnidAnalisis FROM CC2 cc2 WHERE cc2.cc2=t.cc2tj) AS CC2Descrip,
			(SELECT cc3.Labor FROM CC21 cc3 WHERE cc3.cc2=t.cc2tj AND cc3.cc3=t.cc3tj) AS CC3Descrip,
			(SELECT cc4.Labor2 FROM CC22 cc4 WHERE cc4.cc2=t.cc2tj AND cc4.cc3=t.cc3tj AND cc4.cc4=t.cc4tj) AS CC4Descrip,
			(SELECT t1.descattrt FROM TRATOS1 t1 WHERE t1.cc1trt=t.cc1tj AND t1.cattrt=t.cattrt) AS T1Descrip,
			(SELECT t11.desctrt FROM TRATOS11 t11 WHERE t11.cc1trt=t.cc1tj AND t11.cattrt=t.cattrt AND t11.codtrt=t.codtrt) AS T11Descrip,
			(SELECT t11.val1trt FROM TRATOS11 t11 WHERE t11.cc1trt=t.cc1tj AND t11.cattrt=t.cattrt AND t11.codtrt=t.codtrt) AS T11Val
			FROM TARJAS1 t
			WHERE t.cc1tj='$plant'
			AND MONTH(t.fechatj)=$month
			AND YEAR(t.fechatj)=$year
			AND t.rut_per=$rut
			ORDER BY t.fechatj";

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
}

?>