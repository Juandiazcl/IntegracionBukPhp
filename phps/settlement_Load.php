<?php
ini_set('max_execution_time', 240);
header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();


if($_POST['type']=='all' || $_POST['type']=='allPayment' ){

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

	$sql = "";

	if($state=='V'){
		$sql = "SELECT STR(p.rut_per) AS id,
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
				FORMAT(p.fecing_per,'dd/mm/yyyy') AS contractStart,
				IIF(p.indef = 1 , '-', p.fecter_per) AS contractEnd,
				'-' AS settlementDate,
				(SELECT l.descriplb FROM LABOR l WHERE l.codlb=VAL(p.hi_tpcargo)) AS charge,
				(SELECT cf.finiq_descrip FROM CAUSASFIN cf WHERE cf.finiq_codigo=p.Causa_fin_per) AS codeEnd,
				STR(IIF(ISNULL((SELECT SUM(fp.Dias_Habiles)-SUM(fp.Dias_Progresivos) FROM FERIADO_PROPORCIONAL fp WHERE fp.Rut=p.rut_per AND (fp.ID_FINIQUITO_PERSONAL IS NULL OR fp.ID_FINIQUITO_PERSONAL=0))) , 0, (SELECT SUM(fp.Dias_Habiles)-SUM(fp.Dias_Progresivos) FROM FERIADO_PROPORCIONAL fp WHERE fp.Rut=p.rut_per AND (fp.ID_FINIQUITO_PERSONAL IS NULL OR fp.ID_FINIQUITO_PERSONAL=0)))) AS vacationDays,

				IIF(ISNULL((SELECT TOP 1 fp.id FROM FINIQUITO_PERSONAL fp 
				WHERE fp.rut=p.rut_per ORDER BY fp.id DESC)),0,(SELECT TOP 1 fp.id FROM FINIQUITO_PERSONAL fp 
				WHERE fp.rut=p.rut_per ORDER BY fp.id DESC)) AS settlementID,

				(SELECT COUNT(fp.id) FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per) AS settlementCount

				FROM ((PERSONAL p
				LEFT JOIN T0010 t ON t.Pl_codigo=p.planta_per)
				LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
				$where
				ORDER BY p.rut_per";
	}else{

		$dateStart = explode("/", $_POST['startDate']);
		$dateEnd = explode("/", $_POST['endDate']);
		$retardDateStart = $dateStart[1]."/".$dateStart[0]."/".$dateStart[2];
		$retardDateEnd = $dateEnd[1]."/".$dateEnd[0]."/".$dateEnd[2];
		if($where==""){
			$where .= " WHERE (p.fecter_per BETWEEN #$retardDateStart# AND #$retardDateEnd#)";
		}else{
			$where .= " AND (p.fecter_per BETWEEN #$retardDateStart# AND #$retardDateEnd#)";
		}

		$sql = "SELECT STR(p.rut_per) AS id,
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
				FORMAT(p.fecing_per,'dd/mm/yyyy') AS contractStart,

				IIF(ISNULL((SELECT TOP 1 fp.id FROM FINIQUITO_PERSONAL fp 
				WHERE fp.rut=p.rut_per ORDER BY fp.id DESC)),0,(SELECT TOP 1 fp.id FROM FINIQUITO_PERSONAL fp 
				WHERE fp.rut=p.rut_per ORDER BY fp.id DESC)) AS settlementID,

				(SELECT COUNT(fp.id) FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per) AS settlementCount,

				IIF(ISNULL((SELECT TOP 1 fecha_fin FROM FINIQUITO_PERSONAL fp 
				WHERE fp.rut=p.rut_per ORDER BY fp.id DESC)),FORMAT(p.fecter_per,'dd/mm/yyyy'),(SELECT TOP 1 FORMAT(fecha_fin,'dd/mm/yyyy') FROM FINIQUITO_PERSONAL fp 
				WHERE fp.rut=p.rut_per ORDER BY fp.id DESC)) AS contractEnd,

				IIF(ISNULL((SELECT TOP 1 fecha_creacion FROM FINIQUITO_PERSONAL fp LEFT JOIN FINIQUITO f ON f.ID=fp.ID_FINIQUITO			WHERE fp.rut=p.rut_per ORDER BY fp.id DESC)),'-',(SELECT TOP 1 FORMAT(fecha_creacion,'dd/mm/yyyy') FROM FINIQUITO_PERSONAL fp LEFT JOIN FINIQUITO f ON f.ID=fp.ID_FINIQUITO WHERE fp.rut=p.rut_per ORDER BY fp.id DESC)) AS settlementDate,

				(SELECT l.descriplb FROM LABOR l WHERE l.codlb=VAL(p.hi_tpcargo)) AS charge,
				(SELECT cf.finiq_descrip FROM CAUSASFIN cf WHERE cf.finiq_codigo=p.Causa_fin_per) AS codeEnd,
				STR(IIF(ISNULL((SELECT SUM(fp.Dias_Habiles)-SUM(fp.Dias_Progresivos) FROM FERIADO_PROPORCIONAL fp WHERE fp.Rut=p.rut_per AND (fp.ID_FINIQUITO_PERSONAL IS NULL OR fp.ID_FINIQUITO_PERSONAL=0))) , 0, (SELECT SUM(fp.Dias_Habiles)-SUM(fp.Dias_Progresivos) FROM FERIADO_PROPORCIONAL fp WHERE fp.Rut=p.rut_per AND (fp.ID_FINIQUITO_PERSONAL IS NULL OR fp.ID_FINIQUITO_PERSONAL=0)))) AS vacationDays,

				IIF(ISNULL(IIF(p.estado_per='V',0,(SELECT TOP 1 fp.sueldo_base FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY id DESC))),0,(IIF(p.estado_per='V',0,(SELECT TOP 1 fp.sueldo_base FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY id DESC)))) AS salaryBase,
				IIF(ISNULL(IIF(p.estado_per='V',0,(SELECT TOP 1 fp.vacaciones_proporcionales FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY id DESC))),0,(IIF(p.estado_per='V',0,(SELECT TOP 1 fp.vacaciones_proporcionales FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY id DESC)))) AS vacationAmount,

				IIF(ISNULL(IIF(p.estado_per='V',0,(SELECT TOP 1 fp.liquidaciones FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY id DESC))),0,(IIF(p.estado_per='V',0,(SELECT TOP 1 fp.liquidaciones FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY id DESC)))) AS salaryPayment,


				IIF(ISNULL(IIF(p.estado_per='V','',(SELECT TOP 1 fp.liquidacion_fecha FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY id DESC))),0,(IIF(p.estado_per='V','-',(SELECT TOP 1 fp.liquidacion_fecha FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY id DESC)))) AS salaryPaymentDate,

				IIF(ISNULL(IIF(p.estado_per='V',0,(SELECT TOP 1 fp.gratificacion FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY id DESC))),0,(IIF(p.estado_per='V',0,(SELECT TOP 1 fp.gratificacion FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY id DESC)))) AS gratification,
				IIF(ISNULL(IIF(p.estado_per='V',0,(SELECT TOP 1 fp.colacion FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY id DESC))),0,(IIF(p.estado_per='V',0,(SELECT TOP 1 fp.colacion FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY id DESC)))) AS collation,
				IIF(ISNULL(IIF(p.estado_per='V',0,(SELECT TOP 1 fp.movilizacion FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY id DESC))),0,(IIF(p.estado_per='V',0,(SELECT TOP 1 fp.movilizacion FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY id DESC)))) AS mobilization,
				IIF(ISNULL(IIF(p.estado_per='V',0,(SELECT TOP 1 fp.indemnizacion_servicio FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY id DESC))),0,(IIF(p.estado_per='V',0,(SELECT TOP 1 fp.indemnizacion_servicio FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY id DESC)))) AS salaryService,
				IIF(ISNULL(IIF(p.estado_per='V',0,(SELECT TOP 1 fp.indemnizacion_aviso FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY id DESC))),0,(IIF(p.estado_per='V',0,(SELECT TOP 1 fp.indemnizacion_aviso FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY id DESC)))) AS salaryAdvice,
				IIF(ISNULL(IIF(p.estado_per='V',0,(SELECT TOP 1 fp.indemnizacion_voluntaria FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY id DESC))),0,(IIF(p.estado_per='V',0,(SELECT TOP 1 fp.indemnizacion_voluntaria FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY id DESC)))) AS salaryVoluntary,

				IIF(ISNULL(IIF(p.estado_per='V',0,(SELECT TOP 1 fp.indemnizacion_mes FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY id DESC))),0,(IIF(p.estado_per='V',0,(SELECT TOP 1 fp.indemnizacion_mes FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY id DESC)))) AS salaryMonth,

				IIF(ISNULL(IIF(p.estado_per='V',0,(SELECT TOP 1 fp.prestamo_empresa FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY id DESC))),0,(IIF(p.estado_per='V',0,(SELECT TOP 1 fp.prestamo_empresa FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY id DESC)))) AS loanEnterprise,
				IIF(ISNULL(IIF(p.estado_per='V',0,(SELECT TOP 1 fp.prestamo_caja FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY id DESC))),0,(IIF(p.estado_per='V',0,(SELECT TOP 1 fp.prestamo_caja FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY id DESC)))) AS loanCompensation,
				IIF(ISNULL(IIF(p.estado_per='V',0,(SELECT TOP 1 fp.afc FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY id DESC))),0,(IIF(p.estado_per='V',0,(SELECT TOP 1 fp.afc FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY id DESC)))) AS afc

				FROM ((PERSONAL p
				LEFT JOIN T0010 t ON t.Pl_codigo=p.planta_per)
				LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
				$where
				ORDER BY p.rut_per";

				
	}

	$array = executeSelect($sql);
     //echo $sql;
	$count = count($array);
	
	if(count($array)>0){
		for($i=0;$i<$count;$i++){
			if($array[$i]['contractStart'][2]=='-'){
				$contractStart = explode('-', $array[$i]['contractStart']);
				$array[$i]['contractStart'] = $contractStart[0].'/'.$contractStart[1].'/'.$contractStart[2];
			}
			$array[$i]["select"]='<input type="checkbox"></input>';
			$array[$i]["view"]='<button class="btn btn-warning" onclick="viewRow(\''.$array[$i]['rut_per'].'\')"><i class="fa fa-eye fa-lg fa-fw"></i></button>';

			$statePayment = ''; //Para el filtro de estado de pago/finiquito

			if($_POST['type']=='allPayment'){

				if($array[$i]["settlementID"]!=0){
					$result = glob('../documents/uploads/'.$array[$i]["settlementID"].'.*');
					
					if($result){
						$array[$i]["link"]='<button class="btn btn-success" title="Finalizado" onclick="viewFile(\''.$result[0].'\')"><i class="fa fa-file-text fa-lg fa-fw"></i></button>';
						$array[$i]["paymentState"]='PAGADO';
						$statePayment = 'PAGADO';
					}else{
						$array[$i]["link"]='<button class="btn btn-warning" title="Pendiente" onclick="uploadFile(\'upload\','.$array[$i]['settlementID'].')"><i class="fa fa-upload fa-lg fa-fw"></i></button>';
						$array[$i]["paymentState"]='PENDIENTE';
						$statePayment = 'PENDIENTE';

						/////Expiración/////
						$arrayDate = explode("/", $array[$i]["contractEnd"]);
						$dateEnd = strtotime($arrayDate[2]."/".$arrayDate[1]."/".$arrayDate[0]);
						$dateNow = strtotime(date('Y/m/d'));
						$days = 60 - round(abs($dateNow-$dateEnd)/86400);
						$array[$i]["expire"] = $days;
						if($days==1){
							$array[$i]["expireString"] = $days.' día';
						}else{
							$array[$i]["expireString"] = $days.' días';
						}
						////////////////////

					}
				}else{
					$array[$i]["link"]='<button class="btn btn-primary" title="Sin Finiquito"><i class="fa fa-check-circle-o fa-lg fa-fw"></i></button>';
					$array[$i]["paymentState"]='N/A';
					$statePayment = 'SIN';
				}

				$sql = "SELECT id FROM FINIQUITO_PERSONAL WHERE rut=".$array[$i]['rut_per'];
				$arraySettlement = executeSelect($sql);
				$valueSettlement = 0;
				for($j=0;$j<count($arraySettlement);$j++){
					$result = glob('../documents/uploads/'.$arraySettlement[$j]["id"].'.*');
					if($result){
						$valueSettlement++;
					}
				}
				$array[$i]['settlementTotal'] = $array[$i]['settlementCount'];
				$array[$i]['settlementPending'] = $valueSettlement;
				$array[$i]['settlementCount'] = $valueSettlement.' / '.$array[$i]['settlementCount'];
				if($valueSettlement!=$array[$i]['settlementTotal']){
					if($array[$i]["paymentState"]=='PAGADO'){
						$array[$i]["paymentState"]='PENDIENTE';
						$array[$i]["link"] = str_replace('success', 'default', $array[$i]["link"]);
						$statePayment = 'PARCIAL';
					}else{
						$statePayment = 'PENDIENTE';
						/////Expiración/////
						$arrayDate = explode("/", $array[$i]["contractEnd"]);
						$dateEnd = strtotime($arrayDate[2]."/".$arrayDate[1]."/".$arrayDate[0]);
						$dateNow = strtotime(date('Y/m/d'));
						$days = 60 - round(abs($dateNow-$dateEnd)/86400);
						$array[$i]["expire"] = $days;
						if($days==1){
							$array[$i]["expireString"] = $days.' d&iacute;a';
						}else{
							$array[$i]["expireString"] = $days.' d&iacute;as';
						}
						////////////////////
					}
				}

				if($_POST['statePayment']!='TODOS'){
					if($statePayment!=$_POST['statePayment']){
						unset($array[$i]);

					}
				}

			}

		}
		$array = array_values($array);
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
						fp.ID,
						fp.pago_estado
						FROM ((((PERSONAL p
						LEFT JOIN FINIQUITO_PERSONAL fp ON fp.rut=p.rut_per)
						LEFT JOIN FINIQUITO f ON f.ID=fp.ID_FINIQUITO)
						LEFT JOIN T0009 e ON e.Emp_codigo=fp.empresa_rut)
						LEFT JOIN T0010 pl ON pl.Pl_codigo=fp.planta_id)
						WHERE p.rut_per=".$_POST['id']."
						ORDER BY fp.fecha_fin");

	for($i=0;$i<count($array);$i++){
		//$target_file = '../documents/uploads/'.$array[$i]["ID"].'.*';

		$result = glob('../documents/uploads/'.$array[$i]["ID"].'.*');
		//if (file_exists($target_file)) {
		if($result){
			$array[$i]['link'] = $result[0];
		}else{
			$array[$i]['link'] = '';
		}
	}

	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}


}elseif($_POST['type']=='salary'){

	$list = $_POST['list'];
	$endDate = explode('/', $_POST['endDate']);
	//$list = "'   4449905','  25813819','   4910564'";

	/*$listDate = $_POST['listDate'];
	$listDate = explode(',', $listDate);
	$newListDate = '';
	for($d=0;$d<count($listDate)-1;$d++){
		$newListDate[$d] = explode("&&",$listDate[$d]);
	}*/

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
	$salaryYear4=0;
	$salaryMonth4=0;
	
	if($salaryMonth1==12){
		$salaryYear1=$salaryYear1+1;
		$salaryMonth1=1;
	}else{
		$salaryMonth1++;
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
		$salaryMonth4=$salaryMonth3-1;
	}

	$salaryYearWhere = "AND ((((r.aaaarem=$salaryYear1 AND r.mmrem=$salaryMonth1) OR (r.aaaarem=$salaryYear2 AND r.mmrem=$salaryMonth2)) OR (r.aaaarem=$salaryYear3 AND r.mmrem=$salaryMonth3)) OR (r.aaaarem=$salaryYear4 AND r.mmrem=$salaryMonth4))";
	//if($endDate[1][0]==0) $endDate[1]=$endDate[1][1];
	$listB = str_replace(" ", "", str_replace("'", "", $list));

	$sql = "SELECT r.*, FORMAT(p.fecing_per,'dd/mm/yyyy') AS contractStart, p.indef AS duration
			FROM REM021 r
			LEFT JOIN PERSONAL p ON p.cc1_per=r.cc1rem
			WHERE r.codhdrem IN ('H001','H004','H019','H018','H007','H008','H155','H046')
			AND r.rutrem IN ($list)
			$salaryYearWhere
			AND p.rut_per IN ($listB)
			AND p.rut_per =  VAL(TRIM(r.rutrem))
			ORDER BY r.rutrem, r.aaaarem DESC, r.mmrem DESC, r.codhdrem";

	$arraySalary = executeSelect($sql);
	//echo $sql;

/*echo "SELECT r.*, FORMAT(p.fecing_per,'dd/mm/yyyy') AS contractStart

								FROM REM021 r
								LEFT JOIN PERSONAL p ON p.cc1_per=r.cc1rem
								WHERE r.codhdrem IN ('H001','H004','H019','H018','H007','H008','H155','H046')
								AND r.rutrem IN ($list)
								$salaryYearWhere
								AND p.rut_per IN ($listB)
								ORDER BY r.rutrem, r.aaaarem DESC, r.mmrem DESC, r.codhdrem";
	/*$arraySalary = executeSelect("SELECT *
								FROM REM021
								WHERE codhdrem IN ('H001','H004','H019','H018','H007','H008','H155','H046')
								AND rutrem IN ($list)
								$salaryYearWhere
								ORDER BY rutrem, aaaarem DESC, mmrem DESC, codhdrem");*/
	

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
			/*$startDate = "";
			for($e=0;$e<count($newListDate);$e++){
				if($newListDate[$e][0]==$array[$index]['rut']){
					$startDate = $newListDate[$e][1];
				}
			}
			$year = explode("/",$startDate)[2];
			$month = explode("/",$startDate)[1];*/
			//echo $year.'/'.$month.' x '.$arraySalary[$j+6]['mmrem'].'/'.$arraySalary[$j+6]['aaaarem'].'<br/>';
			
			$year = explode("/",$arraySalary[$j]['contractStart'])[2];
			$month = explode("/",$arraySalary[$j]['contractStart'])[1];

			if($count==0){

				$array[$index]['salaryLast'] = '<option value="'.$arraySalary[$j+6]['mmrem'].'/'.$arraySalary[$j+6]['aaaarem'].'#'.intval($arraySalary[$j+6]['valrem2']).'" selected>'.$arraySalary[$j+6]['mmrem'].'/'.$arraySalary[$j+6]['aaaarem'].' ('.number_format($arraySalary[$j+6]['valrem2'], 0,'','.').')</option>';

			}elseif($count==1){
				if(intval($year)<intval($arraySalary[$j+6]['aaaarem'])){
					//if(intval($month)<=intval($arraySalary[$j+6]['mmrem'])){
						$array[$index]['salary'] = $arraySalary[$j]['valrem2'];
						$array[$index]['gratification'] = $arraySalary[$j+1]['valrem2'];
						$array[$index]['collation'] = $arraySalary[$j+5]['valrem2'];
						$array[$index]['mobilization'] = $arraySalary[$j+4]['valrem2'];
						$array[$index]['salaryBonus'] = $arraySalary[$j]['valrem2']+$arraySalary[$j+2]['valrem2']+$arraySalary[$j+3]['valrem2']+$arraySalary[$j+7]['valrem2'];
						$array[$index]['salaryLast'] .= '<option value="'.$arraySalary[$j+6]['mmrem'].'/'.$arraySalary[$j+6]['aaaarem'].'-'.intval($arraySalary[$j+6]['valrem2']).'">'.$arraySalary[$j+6]['mmrem'].'/'.$arraySalary[$j+6]['aaaarem'].' ('.number_format($arraySalary[$j+6]['valrem2'], 0,'','.').')</option>';
						$countSalary++;
					//}
				}elseif(intval($year)==intval($arraySalary[$j+6]['aaaarem'])){
					if(intval($month)<=intval($arraySalary[$j+6]['mmrem'])){
						$array[$index]['salary'] = $arraySalary[$j]['valrem2'];
						$array[$index]['gratification'] = $arraySalary[$j+1]['valrem2'];
						$array[$index]['collation'] = $arraySalary[$j+5]['valrem2'];
						$array[$index]['mobilization'] = $arraySalary[$j+4]['valrem2'];
						$array[$index]['salaryBonus'] = $arraySalary[$j]['valrem2']+$arraySalary[$j+2]['valrem2']+$arraySalary[$j+3]['valrem2']+$arraySalary[$j+7]['valrem2'];
						$array[$index]['salaryLast'] .= '<option value="'.$arraySalary[$j+6]['mmrem'].'/'.$arraySalary[$j+6]['aaaarem'].'-'.intval($arraySalary[$j+6]['valrem2']).'">'.$arraySalary[$j+6]['mmrem'].'/'.$arraySalary[$j+6]['aaaarem'].' ('.number_format($arraySalary[$j+6]['valrem2'], 0,'','.').')</option>';
						$countSalary++;
					}

				}
			}elseif($count<4) {
				if(intval($year)<intval($arraySalary[$j+6]['aaaarem'])){
					//if(intval($month)<=intval($arraySalary[$j+6]['mmrem'])){
						$array[$index]['salary'] += $arraySalary[$j]['valrem2'];
						$array[$index]['gratification'] += $arraySalary[$j+1]['valrem2'];
						$array[$index]['collation'] += $arraySalary[$j+5]['valrem2'];
						$array[$index]['mobilization'] += $arraySalary[$j+4]['valrem2'];
						$array[$index]['salaryBonus'] += $arraySalary[$j]['valrem2']+$arraySalary[$j+2]['valrem2']+$arraySalary[$j+3]['valrem2']+$arraySalary[$j+7]['valrem2'];
						$countSalary++;
					//}
				}elseif(intval($year)==intval($arraySalary[$j+6]['aaaarem'])){
					if(intval($month)<=intval($arraySalary[$j+6]['mmrem'])){
						$array[$index]['salary'] += $arraySalary[$j]['valrem2'];
						$array[$index]['gratification'] += $arraySalary[$j+1]['valrem2'];
						$array[$index]['collation'] += $arraySalary[$j+5]['valrem2'];
						$array[$index]['mobilization'] += $arraySalary[$j+4]['valrem2'];
						$array[$index]['salaryBonus'] += $arraySalary[$j]['valrem2']+$arraySalary[$j+2]['valrem2']+$arraySalary[$j+3]['valrem2']+$arraySalary[$j+7]['valrem2'];
						$countSalary++;
					}
				}
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
										WHERE pp.Estado='IMPAGO' AND p.rut='".ltrim($array[$index]['rut'])."' AND ID_FINIQUITO_PERSONAL=0");
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

	echo round($vacationDays,3);

}elseif($_POST['type']=='list'){

	$array = executeSelect("SELECT f.ID,
							FORMAT(f.fecha_creacion,'dd/mm/yyyy') AS fecha_creacion,
							FORMAT(f.fecha_finiquito,'dd/mm/yyyy') AS fecha_finiquito,
							f.articulo,
							(SELECT COUNT(fp.ID) FROM FINIQUITO_PERSONAL fp WHERE fp.ID_FINIQUITO=f.ID) AS cantidad
							FROM FINIQUITO f");


	echo json_encode($array);

}elseif($_POST['type']=='allHistoric'){

	$plant = 98;	
	if(isset($_POST['plant'])){
		$plant = $_POST['plant'];
	}

	$where = "";

	if($plant!=98){
		if($where==""){
			$where .= "WHERE p.planta_per=$plant";
		}else{
			$where .= " AND p.planta_per=$plant";
		}
	}

	$dateStart = explode("/", $_POST['startDate']);
	$dateEnd = explode("/", $_POST['endDate']);
	$retardDateStart = $dateStart[1]."/".$dateStart[0]."/".$dateStart[2];
	$retardDateEnd = $dateEnd[1]."/".$dateEnd[0]."/".$dateEnd[2];
	if($where==""){
		$where .= " WHERE (p.fecter_per BETWEEN #$retardDateStart# AND #$retardDateEnd#)";
	}else{
		$where .= " AND (p.fecter_per BETWEEN #$retardDateStart# AND #$retardDateEnd#)";
	}

	$sql = "SELECT STR(p.rut_per) AS id,
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
			FORMAT(p.fecing_per,'dd/mm/yyyy') AS contractStart,

			fp.id AS settlementID,
			1 AS settlementCount,
			FORMAT(fecha_fin,'dd/mm/yyyy') AS contractEnd,
			FORMAT(fecha_creacion,'dd/mm/yyyy') AS settlementDate,

			(SELECT l.descriplb FROM LABOR l WHERE l.codlb=VAL(p.hi_tpcargo)) AS charge,
			(SELECT cf.finiq_descrip FROM CAUSASFIN cf WHERE cf.finiq_codigo=f.articulo) AS codeEnd,
			STR(IIF(ISNULL((SELECT SUM(fp.Dias_Habiles)-SUM(fp.Dias_Progresivos) FROM FERIADO_PROPORCIONAL fp WHERE fp.Rut=p.rut_per AND (fp.ID_FINIQUITO_PERSONAL IS NULL OR fp.ID_FINIQUITO_PERSONAL=0))) , 0, (SELECT SUM(fp.Dias_Habiles)-SUM(fp.Dias_Progresivos) FROM FERIADO_PROPORCIONAL fp WHERE fp.Rut=p.rut_per AND (fp.ID_FINIQUITO_PERSONAL IS NULL OR fp.ID_FINIQUITO_PERSONAL=0)))) AS vacationDays,

			fp.sueldo_base AS salaryBase,
			fp.vacaciones_proporcionales AS vacationAmount,

			fp.liquidaciones AS salaryPayment,
			fp.liquidacion_fecha AS salaryPaymentDate,

			fp.gratificacion AS gratification,
			fp.colacion AS collation,
			fp.movilizacion AS mobilization,
			fp.indemnizacion_servicio AS salaryService,
			fp.indemnizacion_aviso AS salaryAdvice,
			fp.indemnizacion_voluntaria AS salaryVoluntary,

			fp.indemnizacion_mes AS salaryMonth,

			fp.prestamo_empresa AS loanEnterprise,
			fp.prestamo_caja AS loanCompensation,
			fp.afc AS afc

			FROM ((((FINIQUITO_PERSONAL fp 
			LEFT JOIN FINIQUITO f ON f.ID=fp.ID_FINIQUITO)
			LEFT JOIN PERSONAL_HISTORICO p ON p.ID_FINIQUITO_PERSONAL=fp.ID)
			LEFT JOIN T0010 t ON t.Pl_codigo=p.planta_per)
			LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
			$where
			ORDER BY p.rut_per, p.fecing_per";


	$array = executeSelect($sql);

	$count = count($array);
	
	if(count($array)>0){
		for($i=0;$i<$count;$i++){
			if($array[$i]['contractStart'][2]=='-'){
				$contractStart = explode('-', $array[$i]['contractStart']);
				$array[$i]['contractStart'] = $contractStart[0].'/'.$contractStart[1].'/'.$contractStart[2];
			}
			$array[$i]["select"]='<input type="checkbox"></input>';
			$array[$i]["view"]='<button class="btn btn-warning" onclick="viewRow(\''.$array[$i]['rut_per'].'\')"><i class="fa fa-eye fa-lg fa-fw"></i></button>';

			$statePayment = ''; //Para el filtro de estado de pago/finiquito

			if($_POST['type']=='allPayment'){

				if($array[$i]["settlementID"]!=0){
					$result = glob('../documents/uploads/'.$array[$i]["settlementID"].'.*');
					
					if($result){
						$array[$i]["link"]='<button class="btn btn-success" title="Finalizado" onclick="viewFile(\''.$result[0].'\')"><i class="fa fa-file-text fa-lg fa-fw"></i></button>';
						$array[$i]["paymentState"]='PAGADO';
						$statePayment = 'PAGADO';
					}else{
						$array[$i]["link"]='<button class="btn btn-warning" title="Pendiente" onclick="uploadFile(\'upload\','.$array[$i]['settlementID'].')"><i class="fa fa-upload fa-lg fa-fw"></i></button>';
						$array[$i]["paymentState"]='PENDIENTE';
						$statePayment = 'PENDIENTE';

						/////Expiración/////
						$arrayDate = explode("/", $array[$i]["contractEnd"]);
						$dateEnd = strtotime($arrayDate[2]."/".$arrayDate[1]."/".$arrayDate[0]);
						$dateNow = strtotime(date('Y/m/d'));
						$days = 60 - round(abs($dateNow-$dateEnd)/86400);
						$array[$i]["expire"] = $days;
						if($days==1){
							$array[$i]["expireString"] = $days.' día';
						}else{
							$array[$i]["expireString"] = $days.' días';
						}
						////////////////////

					}
				}else{
					$array[$i]["link"]='<button class="btn btn-primary" title="Sin Finiquito"><i class="fa fa-check-circle-o fa-lg fa-fw"></i></button>';
					$array[$i]["paymentState"]='N/A';
					$statePayment = 'SIN';
				}

				$sql = "SELECT id FROM FINIQUITO_PERSONAL WHERE rut=".$array[$i]['rut_per'];
				$arraySettlement = executeSelect($sql);
				$valueSettlement = 0;
				for($j=0;$j<count($arraySettlement);$j++){
					$result = glob('../documents/uploads/'.$arraySettlement[$j]["id"].'.*');
					if($result){
						$valueSettlement++;
					}
				}
				$array[$i]['settlementTotal'] = $array[$i]['settlementCount'];
				$array[$i]['settlementPending'] = $valueSettlement;
				$array[$i]['settlementCount'] = $valueSettlement.' / '.$array[$i]['settlementCount'];
				if($valueSettlement!=$array[$i]['settlementTotal']){
					if($array[$i]["paymentState"]=='PAGADO'){
						$array[$i]["paymentState"]='PENDIENTE';
						$array[$i]["link"] = str_replace('success', 'default', $array[$i]["link"]);
						$statePayment = 'PARCIAL';
					}else{
						$statePayment = 'PENDIENTE';
						/////Expiración/////
						$arrayDate = explode("/", $array[$i]["contractEnd"]);
						$dateEnd = strtotime($arrayDate[2]."/".$arrayDate[1]."/".$arrayDate[0]);
						$dateNow = strtotime(date('Y/m/d'));
						$days = 60 - round(abs($dateNow-$dateEnd)/86400);
						$array[$i]["expire"] = $days;
						if($days==1){
							$array[$i]["expireString"] = $days.' d&iacute;a';
						}else{
							$array[$i]["expireString"] = $days.' d&iacute;as';
						}
						////////////////////
					}
				}

				if($_POST['statePayment']!='TODOS'){
					if($statePayment!=$_POST['statePayment']){
						unset($array[$i]);

					}
				}

			}

		}
		$array = array_values($array);
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}
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