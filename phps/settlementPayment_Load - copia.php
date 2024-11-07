<?php
ini_set('max_execution_time', 240);
header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();


if($_POST['type']=='allPayment' ){
	
	$arrayStatePayment = '';
	if($_POST['statePayment']!=''){
		$arrayStatePayment = explode('-',$_POST['statePayment']);
	}

	$plant = 98;	
	if(isset($_POST['plant'])){
		$plant = $_POST['plant'];
	}

	$where = "";

	if($plant!=98){
		$where .= "WHERE p.planta_per=$plant";
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
			FORMAT(p.fecvig_per,'dd/mm/yyyy') AS contractStart,
			IIF(ISNULL(fp.id),0,fp.id) AS settlementID,
			fpa.count AS settlementCount,
			IIF(ISNULL(fp.fecha_fin),FORMAT(p.fecter_per,'dd/mm/yyyy'),FORMAT(fp.fecha_fin,'dd/mm/yyyy')) AS contractEnd,
			IIF(ISNULL(f.fecha_creacion),'-',FORMAT(f.fecha_creacion,'dd/mm/yyyy')) AS settlementDate,
			l.descriplb AS charge,
			cf.finiq_descrip AS codeEnd,
			STR(IIF(ISNULL(fep.vacations),0,fep.vacations)) AS vacationDays,
			IIF(p.estado_per='V',0,IIF(ISNULL(fp.sueldo_base),0,fp.sueldo_base)) AS salaryBase,
			IIF(p.estado_per='V',0,IIF(ISNULL(fp.vacaciones_proporcionales),0,fp.vacaciones_proporcionales)) AS vacationAmount,
			IIF(p.estado_per='V',0,IIF(ISNULL(fp.liquidaciones),0,fp.liquidaciones)) AS salaryPayment,
			IIF(p.estado_per='V','',IIF(ISNULL(fp.liquidacion_fecha),0,fp.liquidacion_fecha)) AS salaryPaymentDate,
			IIF(p.estado_per='V',0,IIF(ISNULL(fp.gratificacion),0,fp.gratificacion)) AS gratification,
			IIF(p.estado_per='V',0,IIF(ISNULL(fp.colacion),0,fp.colacion)) AS collation,
			IIF(p.estado_per='V',0,IIF(ISNULL(fp.movilizacion),0,fp.movilizacion)) AS mobilization,
			IIF(p.estado_per='V',0,IIF(ISNULL(fp.indemnizacion_servicio),0,fp.indemnizacion_servicio)) AS salaryService,
			IIF(p.estado_per='V',0,IIF(ISNULL(fp.indemnizacion_aviso),0,fp.indemnizacion_aviso)) AS salaryAdvice,
			IIF(p.estado_per='V',0,IIF(ISNULL(fp.indemnizacion_voluntaria),0,fp.indemnizacion_voluntaria)) AS salaryVoluntary,
			IIF(p.estado_per='V',0,IIF(ISNULL(fp.prestamo_empresa),0,fp.prestamo_empresa)) AS loanEnterprise,
			IIF(p.estado_per='V',0,IIF(ISNULL(fp.prestamo_caja),0,fp.prestamo_caja)) AS loanCompensation,
			IIF(p.estado_per='V',0,IIF(ISNULL(fp.afc),0,fp.afc)) AS afc

			FROM (((((((PERSONAL p
			LEFT JOIN T0010 t ON t.Pl_codigo=p.planta_per)
			LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
			LEFT JOIN (SELECT fpa.* FROM FINIQUITO_PERSONAL fpa WHERE fpa.ID IN (SELECT MAX(fpb.ID) FROM FINIQUITO_PERSONAL fpb WHERE fpb.rut=fpa.rut)) fp ON fp.rut=p.rut_per)
			LEFT JOIN FINIQUITO f ON f.ID=fp.ID_FINIQUITO)
			LEFT JOIN LABOR l ON l.codlb=VAL(p.hi_tpcargo))
			LEFT JOIN CAUSASFIN cf ON cf.finiq_codigo=p.Causa_fin_per)
			LEFT JOIN (SELECT rut, COUNT(id) AS count FROM FINIQUITO_PERSONAL GROUP BY rut) AS fpa ON fpa.rut=p.rut_per)
			LEFT JOIN (SELECT Rut, SUM(Dias_Habiles)-SUM(Dias_Progresivos) AS vacations FROM FERIADO_PROPORCIONAL WHERE (ID_FINIQUITO_PERSONAL IS NULL OR ID_FINIQUITO_PERSONAL=0) GROUP BY Rut) AS fep ON fep.Rut=p.rut_per
			$where
			ORDER BY p.rut_per";
	$array = executeSelect($sql);

	$count = count($array);

	$sql = "SELECT rut, ID FROM FINIQUITO_PERSONAL ORDER BY rut";
	$arraySettlement = executeSelect($sql);
	
	$j = 0; //Corresponde al índice de finiquitos
	//var_dump($arraySettlement);
	if(count($array)>0){
		for($i=0;$i<$count;$i++){
			/*if($array[$i]['contractStart'][2]=='-'){
				$contractStart = explode('-', $array[$i]['contractStart']);
				$array[$i]['contractStart'] = $contractStart[0].'/'.$contractStart[1].'/'.$contractStart[2];
			}
			$array[$i]["select"]='<input type="checkbox"></input>';
			$array[$i]["view"]='<button class="btn btn-warning" onclick="viewRow(\''.$array[$i]['rut_per'].'\')"><i class="fa fa-eye fa-lg fa-fw"></i></button>';*/

			$statePayment = ''; //Para el filtro de estado de pago/finiquito

			
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
					if($days<=0){
						$array[$i]["expireString"] = 'Caducado';
					}elseif($days==1){
						$array[$i]["expireString"] = $days.' d&iacute;a';
					}else{
						$array[$i]["expireString"] = $days.' d&iacute;as';
					}
					////////////////////
				}
			}else{
				$array[$i]["link"]='<button class="btn btn-primary" title="Sin Finiquito"><i class="fa fa-check-circle-o fa-lg fa-fw"></i></button>';
				$array[$i]["paymentState"]='N/A';
				$statePayment = 'SIN';

			}
	
			$valueSettlement = 0;
			if(count($arraySettlement)>$j){
				if($arraySettlement[$j]['rut']==$array[$i]['rut_per']){
					while (count($arraySettlement)>$j && $arraySettlement[$j]['rut']==$array[$i]['rut_per']) {
						$result = glob('../documents/uploads/'.$arraySettlement[$j]["ID"].'.*');
						if($result){
							$valueSettlement++;
						}
						$j++;
					}
				}
			}

			$array[$i]['settlementPending'] = $valueSettlement;

			if(isset($array[$i]['settlementCount'])){
				$array[$i]['settlementTotal'] = $array[$i]['settlementCount'];
				$array[$i]['settlementPending'] = $valueSettlement;
				$array[$i]['settlementCount'] = $valueSettlement.' / '.$array[$i]['settlementCount'];
			}else{
				$array[$i]['settlementTotal'] = 0;
				$array[$i]['settlementCount'] = '0 / 0';
			}
			if($valueSettlement!=$array[$i]['settlementTotal']){
				if($array[$i]["paymentState"]=='PAGADO'){
					$array[$i]["paymentState"]='PENDIENTE';
					$array[$i]["link"] = str_replace('success', 'default', $array[$i]["link"]);
					$statePayment = 'PARCIAL';
				}else{
					$statePayment = 'PENDIENTE';
				}
			}

			if($arrayStatePayment!=''){
				if($arrayStatePayment[0]!='TODOS'){
					$unset = true;
					for($k=0;$k<count($arrayStatePayment)-1;$k++){
						if($statePayment==$arrayStatePayment[$k]){
							$unset = false;
						}
					}
					if($unset){
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


}

?>