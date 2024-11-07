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

	if($_POST['paymentZero']==0){
		if($where==""){
			$where = "WHERE ";
		}else{
			$where = " AND ";
		}
		$where .= "(fp.vacaciones_proporcionales+fp.liquidaciones+fp.gratificacion+fp.colacion+fp.movilizacion+fp.indemnizacion_servicio+fp.indemnizacion_aviso+fp.indemnizacion_voluntaria+fp.afc+fp.indemnizacion_mes) - (fp.otros_descuentos+fp.prestamo_empresa+fp.prestamo_caja)>0";
	}

	$sql = "SELECT
			STR(pa.rut_per) AS id,
			pa.rut_per,
			pa.rut_per & '-' & p.dv_per AS rut,
			p.Nom_per AS name, 
			p.Apepat_per AS lastname1,
			p.Apemat_per AS lastname2,
			pa.Nom_per & ' ' & pa.Apepat_per & ' ' & pa.Apemat_per AS fullname,
			pa.estado_per AS status,
			t.PlNombre AS plant,
			e.EmpSigla AS enterprise,
			p.sbase_per AS salary,
			IIF(p.indef = 1 , 'Indef.', 'Fijo') AS duration,
			FORMAT(p.fecvig_per,'dd/mm/yyyy') AS contractStart,
			IIF(ISNULL(fp.id),0,fp.id) AS settlementID,

			IIF(ISNULL(fp.fecha_fin),FORMAT(p.fecter_per,'dd/mm/yyyy'),FORMAT(fp.fecha_fin,'dd/mm/yyyy')) AS contractEnd,
			IIF(ISNULL(f.fecha_creacion),'-',FORMAT(f.fecha_creacion,'dd/mm/yyyy')) AS settlementDate,
			l.descriplb AS charge,
			cf.finiq_descrip AS codeEnd,

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
			fp.prestamo_empresa AS loanEnterprise,
			fp.prestamo_caja AS loanCompensation,
			fp.afc,
			fp.pago_estado AS adviceState,
			(fp.vacaciones_proporcionales+fp.liquidaciones+fp.gratificacion+fp.colacion+fp.movilizacion+fp.indemnizacion_servicio+fp.indemnizacion_aviso+fp.indemnizacion_voluntaria+fp.afc+fp.indemnizacion_mes) - (fp.otros_descuentos+fp.prestamo_empresa+fp.prestamo_caja) AS total

			FROM (((((((FINIQUITO_PERSONAL fp
			LEFT JOIN PERSONAL_HISTORICO p ON p.ID_FINIQUITO_PERSONAL=fp.ID)
			LEFT JOIN T0010 t ON t.Pl_codigo=p.planta_per)
			LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
			LEFT JOIN FINIQUITO f ON f.ID=fp.ID_FINIQUITO)
			LEFT JOIN LABOR l ON l.codlb=VAL(p.hi_tpcargo))
			LEFT JOIN CAUSASFIN cf ON cf.finiq_codigo=p.Causa_fin_per)
			LEFT JOIN PERSONAL pa ON pa.rut_per=fp.rut)

			$where
			ORDER BY p.rut_per";

	//echo $sql;
	$array = executeSelect($sql);

	$count = count($array);

	if(count($array)>0){
		for($i=0;$i<$count;$i++){

			$statePayment = ''; //Para el filtro de estado de pago/finiquito
			$statePayment2 = ''; //Para el filtro de estado de pago/finiquito

			
			if($array[$i]["settlementID"]!=0){
				$result = glob('../documents/uploads/'.$array[$i]["settlementID"].'.*');
				
				if($result){
					$array[$i]["link"]='<button class="btn btn-warning" title="Finalizado" onclick="viewFile(\''.$result[0].'\')"><i class="fa fa-file-text fa-lg fa-fw"></i></button>';
					$array[$i]["paymentState"]='PAGADO';
					$statePayment = 'PAGADO';
					$statePayment2 = 'PAGADO';
					
				}else{
					$resultB = glob('../documents/uploads/_'.$array[$i]["settlementID"].'.*');
				
					if($resultB){
						$array[$i]["link"]='<button class="btn btn-warning" title="Revisar" onclick="viewFile(\''.$resultB[0].'\')"><i class="fa fa-file-text fa-lg fa-fw"></i></button>';
						$array[$i]["paymentState"]='REVISION';
						$statePayment = 'REVISION';
						$statePayment2 = 'REVISION';
					}else{
						if($array[$i]["adviceState"]=='PENDIENTE'){
							$array[$i]["link"]='<button class="btn btn-warning" title="Pendiente" onclick="uploadFile(\'upload\','.$array[$i]['settlementID'].')"><i class="fa fa-upload fa-lg fa-fw"></i></button>';
							$array[$i]["paymentState"]='PENDIENTE';
							$statePayment = 'PENDIENTE';
							$statePayment2 = 'PENDIENTE';
						}else{
							$array[$i]["link"]='<button class="btn btn-primary" title="Avisado" onclick="uploadFile(\'upload\','.$array[$i]['settlementID'].')"><i class="fa fa-upload fa-lg fa-fw"></i></button>';
							$array[$i]["paymentState"]='PENDIENTE';
							$statePayment = 'AVISADO';
							$statePayment2 = 'AVISADO';
						}

						/////Expiración/////
						if($array[$i]["total"]>0){
							$arrayDate = explode("/", $array[$i]["settlementDate"]);
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
							if($statePayment=='PENDIENTE'){
								if($days<45){
									$statePayment = 'PENDIENTE_AVISAR';
									$statePayment2 = 'PENDIENTE';
								}
							}
						}else{
							if($statePayment=='PENDIENTE' || $statePayment=='AVISADO'){
								$title = 'Pendiente';
								if($statePayment=='AVISADO'){
									$title = 'Avisado';
								}
								$array[$i]["link"]='<button class="btn btn-success" title="'.$title.'" onclick="uploadFile(\'upload\','.$array[$i]['settlementID'].')"><i class="fa fa-upload fa-lg fa-fw"></i></button>';
								$statePayment = 'PAGO EN 0';
								$statePayment2 = 'PAGO EN 0';
								$array[$i]["expireString"] = 'PAGO EN 0';
							}
						}
						////////////////////
					}
				}
			}/*else{
				$array[$i]["link"]='<button class="btn btn-primary" title="Sin Finiquito"><i class="fa fa-check-circle-o fa-lg fa-fw"></i></button>';
				$array[$i]["paymentState"]='N/A';
				$statePayment = 'SIN';

			}
	
			/*$result = glob('../documents/uploads/'.$array[$i]["settlementID"].'.*');
			if(!$result){
				if($array[$i]["paymentState"]=='PAGADO'){
					$array[$i]["paymentState"]='PENDIENTE';
					$array[$i]["link"] = str_replace('success', 'default', $array[$i]["link"]);
					$statePayment = 'PARCIAL';
				}else{
					$statePayment = 'PENDIENTE';
				}
			}*/

			if($arrayStatePayment!=''){
				if($arrayStatePayment[0]!='TODOS'){
					$unset = true;
					for($k=0;$k<count($arrayStatePayment)-1;$k++){
						if($statePayment==$arrayStatePayment[$k]){
							$unset = false;
						}elseif($statePayment2==$arrayStatePayment[$k]){
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