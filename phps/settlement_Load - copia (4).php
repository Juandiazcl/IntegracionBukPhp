<?php
ini_set('max_execution_time', 240);
header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();


if($_POST['type']=='all'){

	$state = $_POST['state'];
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
							p.rut_per & '-' & p.dv_per AS rut,
							p.Nom_per AS name, 
							p.Apepat_per AS lastname1,
							p.Apemat_per AS lastname2,
							p.Nom_per & ' ' & p.Apepat_per & ' ' & p.Apemat_per AS fullname,
							p.estado_per AS status,
							t.PlNombre AS plant,
							e.EmpSigla AS enterprise,
							p.sbase_per AS salary,
							IIF(p.indef = 1 , 'Indef.', 'Fijo') AS duration,
							FORMAT(p.fecvig_per,'dd/mm/yyyy') AS contractStart,
							IIF(p.indef = 1 , '-', p.fecter_per) AS contractEnd,
							(SELECT l.descriplb FROM LABOR l WHERE l.codlb=VAL(p.hi_tpcargo)) AS charge,
							(SELECT cf.finiq_descrip FROM CAUSASFIN cf WHERE cf.finiq_codigo=p.Causa_fin_per) AS codeEnd,
							STR(IIF(ISNULL((SELECT SUM(fp.Dias_Habiles)-SUM(fp.Dias_Progresivos) FROM FERIADO_PROPORCIONAL fp WHERE fp.Rut=p.rut_per AND (fp.ID_FINIQUITO_PERSONAL IS NULL OR fp.ID_FINIQUITO_PERSONAL=0))) , 0, (SELECT SUM(fp.Dias_Habiles)-SUM(fp.Dias_Progresivos) FROM FERIADO_PROPORCIONAL fp WHERE fp.Rut=p.rut_per AND (fp.ID_FINIQUITO_PERSONAL IS NULL OR fp.ID_FINIQUITO_PERSONAL=0)))) AS vacationDays

							FROM ((PERSONAL p
							LEFT JOIN T0010 t ON t.Pl_codigo=p.planta_per)
							LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
							$where
							ORDER BY p.rut_per");

	if(count($array)>0){
		for($i=0;$i<count($array);$i++){
			if($array[$i]['contractStart'][2]=='-'){
				$contractStart = explode('-', $array[$i]['contractStart']);
				$array[$i]['contractStart'] = $contractStart[0].'/'.$contractStart[1].'/'.$contractStart[2];
			}
			$array[$i]["select"]='<input type="checkbox"></input>';
			$array[$i]["view"]='<button class="btn btn-warning" onclick="viewRow(\''.$array[$i]['rut_per'].'\')"><i class="fa fa-eye fa-lg fa-fw"></i></button>';
		}
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}


}elseif($_POST['type']=='one'){


	$array = executeSelect("SELECT p.rut_per & '-' & p.dv_per AS rut,
						p.Nom_per & ' ' & p.Apepat_per & ' ' & p.Apemat_per AS name,
						e.EmpNombre AS enterprise,
						e.EmpSigla AS enterprise_initials,
						pl.PlNombre AS plant,
						Format(fp.fecha_inicio,'dd/mm/yyyy') AS contract_start,
						Format(fp.fecha_fin,'dd/mm/yyyy') AS contract_end,
						fp.sueldo_base,
						fp.vacaciones_proporcionales,
						fp.liquidaciones,
						f.articulo,
						fp.ID
						FROM ((((PERSONAL p
						LEFT JOIN FINIQUITO_PERSONAL fp ON fp.rut=p.rut_per)
						LEFT JOIN FINIQUITO f ON f.ID=fp.ID_FINIQUITO)
						LEFT JOIN T0009 e ON e.Emp_codigo=fp.empresa_rut)
						LEFT JOIN T0010 pl ON pl.Pl_codigo=fp.planta_id)
						WHERE p.rut_per=".$_POST['id']."
						ORDER BY fp.fecha_fin");

	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}


}elseif($_POST['type']=='salary'){

	$list = $_POST['list'];
	$endDate = explode('/', $_POST['endDate']);
	//$list = "'   4449905','  25813819','   4910564'";

	$ufMonth = intval($endDate[1]);
	$ufYear = intval($endDate[2]);
	if($ufMonth==1){
		$ufMonth = 12;
		$ufYear = $ufYear-1;
	}else{
		$ufMonth--;
	}


	$salaryYear1=$ufYear;
	$salaryMonth1=$ufMonth;
	$salaryYear2=0;
	$salaryMonth2=0;
	$salaryYear3=0;
	$salaryMonth3=0;

	if($salaryMonth1==12){
		$salaryYear1=$salaryYear1+1;
		$salaryMonth1=1;
	}


	if($salaryMonth1==1){
		$salaryYear2=$salaryYear1-1;
		$salaryMonth2=12;
	}else{
		$salaryYear2=$salaryYear1;
		$salaryMonth2=$salaryMonth1-1;
	}

	if($salaryMonth2==1){
		$salaryYear3=$salaryYear2-1;
		$salaryMonth3=12;
	}else{
		$salaryYear3=$salaryYear2;
		$salaryMonth3=$salaryMonth2-1;
	}
	if($salaryMonth3==1){
		$salaryYear4=$salaryYear3-1;
		$salaryMonth4=12;
	}else{
		$salaryYear4=$salaryYear3;
		$salaryMonth4=$salaryMonth2-1;
	}

	$salaryYearWhere = "AND ((((aaaarem=$salaryYear1 AND mmrem=$salaryMonth1) OR (aaaarem=$salaryYear2 AND mmrem=$salaryMonth2)) OR (aaaarem=$salaryYear3 AND mmrem=$salaryMonth3)) OR (aaaarem=$salaryYear4 AND mmrem=$salaryMonth4))";
	//if($endDate[1][0]==0) $endDate[1]=$endDate[1][1];7


	$arraySalary = executeSelect("SELECT *
								FROM REM021
								WHERE codhdrem IN ('H001','H004','H019','H018','H007','H008','H155','H046')
								AND rutrem IN ($list)
								$salaryYearWhere
								ORDER BY rutrem, aaaarem DESC, mmrem DESC, codhdrem");
	
	$arrayUF = executeSelect("SELECT *
								FROM CYTMES1
								WHERE codc='c001' AND cytmm=".$ufMonth."
								AND cytaa=".$ufYear);
	
	$maxUF = 90*$arrayUF[0]['ctnum6'];

	$array = array();
	$count = 0;
	$countSalary = 0;
	$index = 0;
	for($j=0;$j<count($arraySalary);$j=$j+8){
		$array[$index]['rut'] = $arraySalary[$j]['rutrem'];
		//echo $arraySalary[$j]['aaaarem'].'/'.$arraySalary[$j]['mmrem'].' - '.$endDate[2].'/'.$endDate[1].'<br/>';
		//if($arraySalary[$j]['aaaarem']!=$endDate[2] || $arraySalary[$j]['mmrem']!=$endDate[1]){
			if($count==0){

				$array[$index]['salary'] = $arraySalary[$j]['valrem2'];
				$array[$index]['gratification'] = $arraySalary[$j+1]['valrem2'];
				$array[$index]['collation'] = $arraySalary[$j+5]['valrem2'];
				$array[$index]['mobilization'] = $arraySalary[$j+4]['valrem2'];
				$array[$index]['salaryBonus'] = $arraySalary[$j]['valrem2']+$arraySalary[$j+2]['valrem2']+$arraySalary[$j+3]['valrem2']+$arraySalary[$j+7]['valrem2'];
				$array[$index]['salaryLast'] = '<option value="'.$arraySalary[$j+6]['mmrem'].'/'.$arraySalary[$j+6]['aaaarem'].'-'.intval($arraySalary[$j+6]['valrem2']).'" selected>'.$arraySalary[$j+6]['mmrem'].'/'.$arraySalary[$j+6]['aaaarem'].' ('.number_format($arraySalary[$j+6]['valrem2'], 0,'','.').')</option>';

				$countSalary++;
			}elseif($count<3) {
				$array[$index]['salary'] += $arraySalary[$j]['valrem2'];
				$array[$index]['gratification'] += $arraySalary[$j+1]['valrem2'];
				$array[$index]['collation'] += $arraySalary[$j+5]['valrem2'];
				$array[$index]['mobilization'] += $arraySalary[$j+4]['valrem2'];
				$array[$index]['salaryBonus'] += $arraySalary[$j]['valrem2']+$arraySalary[$j+2]['valrem2']+$arraySalary[$j+3]['valrem2']+$arraySalary[$j+7]['valrem2'];
				if($count==1){
					$array[$index]['salaryLast'] .= '<option value="'.$arraySalary[$j+6]['mmrem'].'/'.$arraySalary[$j+6]['aaaarem'].'-'.intval($arraySalary[$j+6]['valrem2']).'">'.$arraySalary[$j+6]['mmrem'].'/'.$arraySalary[$j+6]['aaaarem'].' ('.number_format($arraySalary[$j+6]['valrem2'], 0,'','.').')</option>';
				}
				$countSalary++;
			}
		//}
		$count++;

		if($j+8!=count($arraySalary)){
			if($array[$index]['rut']!=$arraySalary[$j+8]['rutrem']){
				if($countSalary>0){
					$array[$index]['salary'] = $array[$index]['salary']/$countSalary;
					if($array[$index]['salary']>$maxUF){
						$array[$index]['salary'] = $maxUF;
					}
					$array[$index]['gratification'] = $array[$index]['gratification']/$countSalary;
					$array[$index]['collation'] = $array[$index]['collation']/$countSalary;
					$array[$index]['mobilization'] = $array[$index]['mobilization']/$countSalary;
					$array[$index]['salaryBonus'] = $array[$index]['salaryBonus']/$countSalary;
				}else{
					$array[$index]['salary'] = 0;
					$array[$index]['gratification'] = 0;
					$array[$index]['collation'] = 0;
					$array[$index]['mobilization'] = 0;
					$array[$index]['salaryBonus'] = 0;
				}
				$count = 0;
				$countSalary = 0;

				$arrayLoan = executeSelect("SELECT SUM(pp.Monto) AS Saldo
										FROM PRESTAMO_PAGOS pp
										LEFT JOIN PRESTAMO p ON p.ID=pp.ID_PRESTAMO
										WHERE pp.Estado='IMPAGO' AND p.rut='".ltrim($array[$index]['rut'])."'");
				$array[$index]['loan'] = $arrayLoan[0]['Saldo'];

				$index++;
			}
		}else{
			if($countSalary>0){
				$array[$index]['salary'] = $array[$index]['salary']/$countSalary;
				if($array[$index]['salary']>$maxUF){
					$array[$index]['salary'] = $maxUF;
				}
				$array[$index]['gratification'] = $array[$index]['gratification']/$countSalary;
				$array[$index]['collation'] = $array[$index]['collation']/$countSalary;
				$array[$index]['mobilization'] = $array[$index]['mobilization']/$countSalary;
				$array[$index]['salaryBonus'] = $array[$index]['salaryBonus']/$countSalary;
			}else{
				$array[$index]['salary'] = 0;
				$array[$index]['gratification'] = 0;
				$array[$index]['collation'] = 0;
				$array[$index]['mobilization'] = 0;
				$array[$index]['salaryBonus'] = 0;
			}

			$arrayLoan = executeSelect("SELECT SUM(pp.Monto) AS Saldo
										FROM PRESTAMO_PAGOS pp
										LEFT JOIN PRESTAMO p ON p.ID=pp.ID_PRESTAMO
										WHERE pp.Estado='IMPAGO' AND p.rut='".ltrim($array[$index]['rut'])."'");
			$array[$index]['loan'] = $arrayLoan[0]['Saldo'];

		}
	}



	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}


}elseif($_POST['type']=='verifyPersonal'){
	$array = executeSelect("SELECT * FROM personal WHERE rut='".$_POST['rut']."' AND NOT id=".$_POST['id']);
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}

}elseif($_POST['type']=='verify'){
	$array = executeSelect("SELECT * FROM personal WHERE rut='".$_POST['rut']."'");
	if(count($array)>0){
		$arrayPersonal = executeSelect("SELECT * FROM contract_personal WHERE personal_id=".$array[0]['id']." AND NOT id=".$_POST['id']);

		if(count($arrayPersonal)>0){
			if($arrayPersonal[0]['contract_id']==$_POST['contractId']){
				echo 2;
			}else{
				echo 3;
			}
		}else{
			echo json_encode(utf8ize($array)); //1
		}
	}else{
		echo 0;
	}


}elseif($_POST['type']=='vacations'){
	$dateStart = explode("/", $_POST['start']);
	$vacationDays = $_POST['vacationDays'];
	//$totalDays = 0;
	
	$date=date_create($dateStart[2].'-'.$dateStart[1].'-'.$dateStart[0]);
	$retardDate = date_format($date,"m/d/Y");
	$arrayHolidays = executeSelect("SELECT * FROM DIAS_FESTIVOS WHERE Fecha >= #$retardDate# ORDER BY Fecha");


	for($i=0;$i<$vacationDays;$i++){
		$weekDay = date('w', strtotime(date_format($date,"Y-m-d")));
		if($weekDay==0 || $weekDay==6){
			$vacationDays++;
		}else{
			for($j=0;$j<count($arrayHolidays);$j++){
				$holiday = date_create($arrayHolidays[$j]['Fecha']);
				if(date_format($date,"Y/m/d")==date_format($holiday,"Y/m/d")){
					$vacationDays++;
					$j=count($arrayHolidays);
				}
			}
		}
		date_add($date,date_interval_create_from_date_string("1 day"));
	}

	date_add($date,date_interval_create_from_date_string("-1 day"));

	echo $vacationDays;

}elseif($_POST['type']=='list'){

	$array = executeSelect("SELECT f.ID,
							FORMAT(f.fecha_creacion,'dd/mm/yyyy') AS fecha_creacion,
							FORMAT(f.fecha_finiquito,'dd/mm/yyyy') AS fecha_finiquito,
							f.articulo,
							(SELECT COUNT(fp.ID) FROM FINIQUITO_PERSONAL fp WHERE fp.ID_FINIQUITO=f.ID) AS cantidad
							FROM FINIQUITO f");


	echo json_encode($array);

}


/*}elseif($_POST['type']=='vacations'){
	$dateStart = explode("/", $_POST['start']);
	$vacationDays = $_POST['vacationDays'];
	//$totalDays = 0;
	
	$date=date_create($dateStart[2].'-'.$dateStart[1].'-'.$dateStart[0]);
	
	for($i=0;$i<$vacationDays;$i++){
		$weekDay = date('w', strtotime(date_format($date,"Y-m-d")));
		if($weekDay==0 || $weekDay==6){
			$vacationDays++;
		}else{
			$retardDate = date_format($date,"m/d/Y");
			$array = executeSelect("SELECT * FROM DIAS_FESTIVOS WHERE Fecha = #$retardDate#");
			if(count($array)>0){
				$vacationDays++;
			}
		}
		date_add($date,date_interval_create_from_date_string("1 day"));
	}

	date_add($date,date_interval_create_from_date_string("-1 day"));

	echo $vacationDays;

}*/
?>