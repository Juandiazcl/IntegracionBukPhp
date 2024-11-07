<?php
header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");
set_time_limit(40000);

session_start();

if($_POST['type']=='all'){

	$state = $_POST['state'];
	$enterprise = $_POST['enterprise'];
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
	if($enterprise!=0){
		if($where==""){
			$where .= "WHERE p.emp_per=$enterprise";
		}else{
			$where .= " AND p.emp_per=$enterprise";
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
							(SELECT cf.finiq_descrip FROM CAUSASFIN cf WHERE cf.finiq_codigo=p.Causa_fin_per) AS codeEnd

							FROM ((PERSONAL p
							LEFT JOIN T0010 t ON t.Pl_codigo=p.planta_per)
							LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
							$where
							ORDER BY p.rut_per");
	if(count($array)>0){
		for($i=0;$i<count($array);$i++){
			$array[$i]["view"]='<button class="btn btn-warning" onclick="viewRow(\''.$array[$i]['rut_per'].'\',\''.$array[$i]['rut'].'\',\''.$array[$i]['fullname'].'\')"><i class="fa fa-eye fa-lg fa-fw"></i></button>';
			$array[$i]["excel"]='<button class="btn btn-success" onclick="toExcelOne(\''.$array[$i]['rut_per'].'\',\''.$array[$i]['rut'].'\',\''.$array[$i]['fullname'].'\')"><i class="fa fa-file-excel-o fa-lg fa-fw"></i></button>';
			//$array[$i]["eliminar"]='<button class="btn btn-danger" onclick="deleteRow(\''.$array[$i]['id'].'\')"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';
		}
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}


}elseif($_POST['type']=='one'){
	$sql="";
	$where="";
	if($_POST['period']=='0'){
		$sql="SELECT 
			'Actual' AS row,
			STR(p.ficha_per) AS sheet,
			p.rut_per & '-' & p.dv_per AS rut,
			e.EmpNombre AS enterprise,
			e.EmpSigla AS enterprise_initials,
			pl.PlNombre AS plant,
			p.Nom_per & ' ' & p.Apepat_per & ' ' & p.Apemat_per AS fullname,
			p.Apepat_per AS lastname1,
			p.Apemat_per AS lastname2,
			p.Nom_per AS name, 
			p.Direc_per AS address,
			p.comuna_per AS commune,
			(SELECT c.cod_ciu FROM CIUDAD c WHERE c.cod_ciu=p.ciudad_per) AS city,
			p.fono_per AS phone,
			p.cel_per AS cellphone,
			p.nac_per AS nationality,
			Format(p.fecnac_per,'dd/mm/yyyy') AS birthdate,
			(SELECT a.des_afp FROM AFP a WHERE a.cod_afp=p.afp_per) AS afp,
			(SELECT i.nom_isa FROM ISAPRES i WHERE i.cod_isa=p.isa_per) AS healthSystem,
			p.uf_isa_per AS healthSystemUF,
			p.porc_inp AS inp,
			p.sbase_per AS salary,
			IIF(p.indef = 1 , 'Indef.', 'Fijo') AS duration,
			IIF(p.labor_per = 1 , 'Labor', 'Trato') AS work,
			Format(p.fecing_per,'dd/mm/yyyy') AS contract_entry,
			Format(p.fecvig_per,'dd/mm/yyyy') AS contract_start,
			Format(p.fecter_per,'dd/mm/yyyy') AS contract_end,
			p.Causa_fin_per AS article,
			p.estado_per AS status
			
			FROM ((PERSONAL p
			LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
			LEFT JOIN T0010 pl ON pl.Pl_codigo=p.planta_per)
			WHERE p.rut_per=".$_POST['id'];

			$where = "WHERE Rut=".$_POST['id']." AND ID_FINIQUITO_PERSONAL=0";
	}else{
		$sql = "SELECT 
				'Histórico' AS row,
				STR(ph.ficha_per) AS sheet,
				ph.rut_per & '-' & ph.dv_per AS rut,
				e.EmpNombre AS enterprise,
				e.EmpSigla AS enterprise_initials,
				pl.PlNombre AS plant,
				ph.Nom_per & ' ' & ph.Apepat_per & ' ' & ph.Apemat_per AS fullname,
				ph.Apepat_per AS lastname1,
				ph.Apemat_per AS lastname2,
				ph.Nom_per AS name, 
				ph.Direc_per AS address,
				ph.comuna_per AS commune,
				(SELECT c.ciu_des FROM CIUDAD c WHERE c.cod_ciu=ph.ciudad_per) AS city,
				ph.fono_per AS phone,
				ph.cel_per AS cellphone,
				ph.nac_per AS nationality,
				Format(ph.fecnac_per,'dd/mm/yyyy') AS birthdate,
				(SELECT a.des_afp FROM AFP a WHERE a.cod_afp=ph.afp_per) AS afp,
				(SELECT i.nom_isa FROM ISAPRES i WHERE i.cod_isa=ph.isa_per) AS healthSystem,
				ph.uf_isa_per AS healthSystemUF,
				ph.porc_inp AS inp,
				ph.sbase_per AS salary,
				IIF(ph.indef = 1 , 'Indef.', 'Fijo') AS duration,
				IIF(ph.labor_per = 1 , 'Labor', 'Trato') AS work,
				Format(ph.fecing_per,'dd/mm/yyyy') AS contract_entry,
				Format(ph.fecvig_per,'dd/mm/yyyy') AS contract_start,
				Format(ph.fecter_per,'dd/mm/yyyy') AS contract_end,
				ph.Causa_fin_per AS article,
				ph.estado_per AS status

				FROM ((PERSONAL_HISTORICO ph
				LEFT JOIN T0009 e ON e.Emp_codigo=ph.emp_per)
				LEFT JOIN T0010 pl ON pl.Pl_codigo=ph.planta_per)
				WHERE ph.ID_FINIQUITO_PERSONAL=".$_POST['period'];
				$where="WHERE ID_FINIQUITO_PERSONAL=".$_POST['period'];
	}
	
	$array = executeSelect($sql);
	
	if(count($array)>0){

		$arrayVacation = executeSelect("SELECT 
					ID,
					Format(Fecha_Inicio,'dd/mm/yyyy') AS FechaInicio,
					Format(Fecha_Fin,'dd/mm/yyyy') AS FechaFin,
					Format(Fecha_Reintegracion,'dd/mm/yyyy') AS FechaReintegracion,
					Dias_Habiles,
					Dias_Inhabiles,
					Dias_Progresivos,
					Periodo_Inicio & ' - ' & Periodo_Fin AS Periodo
					FROM FERIADO_PROPORCIONAL $where
					ORDER BY Fecha_Inicio");
		if(count($arrayVacation)>0){
			for($i=0;$i<count($arrayVacation);$i++){
				$arrayVacation[$i]["rut"]=$array[0]['rut'];
				$arrayVacation[$i]["contract_start"]=$array[0]['contract_start'];
				$arrayVacation[$i]["contract_entry"]=$array[0]['contract_entry'];

				$result = glob('../documents/uploads/vacations/'.$arrayVacation[$i]["ID"].'.*');
				//if (file_exists($target_file)) {
				if($result){
					$arrayVacation[$i]['link'] = $result[0];
				}else{
					$arrayVacation[$i]['link'] = '';
				}

			}

			echo json_encode(utf8ize($arrayVacation));
		}else{
			echo json_encode(utf8ize($array));
		}
	}else{
		echo 0;
	}

}elseif($_POST['type']=='verify'){
	$dateStart = explode("/", $_POST['start']);
	$dateEnd = explode("/", $_POST['end']);
	$retardDateStart = $dateStart[1]."/".$dateStart[0]."/".$dateStart[2];
	$retardDateEnd = $dateEnd[1]."/".$dateEnd[0]."/".$dateEnd[2];
	$array = executeSelect("SELECT Format(Fecha,'dd/mm/yyyy') AS FechaX FROM DIAS_FESTIVOS WHERE (Fecha BETWEEN #$retardDateStart# AND #$retardDateEnd#)");
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}


}elseif($_POST['type']=='allDetail'){
	$sql="";

	$state = $_POST['state'];
	$enterprise = $_POST['enterprise'];
	$plant = 98;	
	if(isset($_POST['plant'])){
		$plant = $_POST['plant'];
	}

	$where = "";
	if($state!='T'){
		$where .= " AND p.estado_per='$state'";
	}
	if($plant!=98){
		$where .= " AND p.planta_per=$plant";
	}
	if($enterprise!=0){
		$where .= " AND p.emp_per=$enterprise";
	}

	$lastDate1 = date('d/m/Y');
	$lastDate2 = date('d-m-Y');
	$date = $_POST['date'];
	$whereDateDetail = "";
	if($date!=0){
		$dateArray = explode("/", $date);
		$dateFinal = $dateArray[1]."/".$dateArray[0]."/".$dateArray[2];
		$where .= " AND p.fecvig_per<=#".$dateFinal."#";
		$whereDateDetail = " AND Fecha_Inicio<=#".$dateFinal."#";

		$lastDate1 = $date;
		$lastdate2 = $dateArray[0]."-".$dateArray[1]."-".$dateArray[2];
	}

	$sql="SELECT 
		'Actual' AS row,
		p.ficha_per,
		STR(p.ficha_per) AS sheet,
		p.rut_per & '-' & p.dv_per AS rut,
		e.EmpNombre AS enterprise,
		e.EmpSigla AS enterprise_initials,
		pl.PlNombre AS plant,
		p.Nom_per & ' ' & p.Apepat_per & ' ' & p.Apemat_per AS fullname,
		p.Apepat_per AS lastname1,
		p.Apemat_per AS lastname2,
		p.Nom_per AS name, 
		p.Direc_per AS address,
		p.comuna_per AS commune,
		(SELECT c.cod_ciu FROM CIUDAD c WHERE c.cod_ciu=p.ciudad_per) AS city,
		p.fono_per AS phone,
		p.cel_per AS cellphone,
		p.nac_per AS nationality,
		Format(p.fecnac_per,'dd/mm/yyyy') AS birthdate,
		(SELECT a.des_afp FROM AFP a WHERE a.cod_afp=p.afp_per) AS afp,
		(SELECT i.nom_isa FROM ISAPRES i WHERE i.cod_isa=p.isa_per) AS healthSystem,
		p.uf_isa_per AS healthSystemUF,
		p.porc_inp AS inp,
		p.sbase_per AS salary,
		IIF(p.indef = 1 , 'Indef.', 'Fijo') AS duration,
		IIF(p.labor_per = 1 , 'Labor', 'Trato') AS work,
		Format(p.fecing_per,'dd/mm/yyyy') AS contract_entry,
		Format(p.fecvig_per,'dd/mm/yyyy') AS contract_start,
		Format(p.fecter_per,'dd/mm/yyyy') AS contract_end,
		p.Causa_fin_per AS article,
		p.estado_per AS status
		
		FROM ((PERSONAL p
		LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
		LEFT JOIN T0010 pl ON pl.Pl_codigo=p.planta_per)
		WHERE 1=1 $where";
		//WHERE p.rut_per=".$_POST['id'];
	//echo $sql;
	$array = executeSelect($sql);
	
	if(count($array)>0){

		for($i=0;$i<count($array);$i++){

			$arrayVacation = executeSelect("SELECT 
											SUM(Dias_Habiles) AS used,
											SUM(Dias_Progresivos) AS extra,
											(SELECT TOP 1 Format(Fecha_Inicio,'dd/mm/yyyy') 
											FROM FERIADO_PROPORCIONAL WHERE Rut=".$array[$i]["ficha_per"]." AND ID_FINIQUITO_PERSONAL=0 ORDER BY Fecha_Inicio DESC) AS last
											FROM FERIADO_PROPORCIONAL
											WHERE Rut=".$array[$i]["ficha_per"]." AND ID_FINIQUITO_PERSONAL=0 $whereDateDetail");

			if($arrayVacation[0]["used"]>0){

				$array[$i]["used"]=$arrayVacation[0]['used']-$arrayVacation[0]['extra'];
				$date1Array = explode('/', $array[$i]["contract_start"]);
				$date2Array = explode('/', $arrayVacation[0]["last"]);
				if($array[$i]["status"]=='V'){
					$date2Array = explode('/', $lastDate1);
				}else{
					$date2Array = explode('/', $arrayVacation[0]["last"]);
				}
				if($array[$i]["contract_start"][2]=="-"){
					$date1Array = explode('-', $array[$i]["contract_start"]);
					if($array[$i]["status"]=='V'){
						$date2Array = explode('-', $lastDate2);
					}else{
						$date2Array = explode('-', $arrayVacation[0]["last"]);
					}
				}


				$days = (strtotime($date1Array[2].'-'.$date1Array[1].'-'.$date1Array[0])-strtotime($date2Array[2].'-'.$date2Array[1].'-'.$date2Array[0]))/86400;
				$days = abs($days); 
				$daysTotal = floor($days);	
				$daysUsed = $arrayVacation[0]["used"];
				$daysProgressive = $arrayVacation[0]["extra"];

				$daysPending = round(((15/12/30)*($daysTotal+1))-$daysUsed,2)+$daysProgressive;
				$array[$i]["pending"]=number_format($daysPending, 2,',','.');
			}else{
				$array[$i]["used"] = 0;
				
				$date1Array = explode('/', $array[$i]["contract_start"]);
				if($array[$i]["contract_start"][2]=="-"){
					$date1Array = explode('-', $array[$i]["contract_start"]);
				}
				$days = (strtotime($date1Array[2].'-'.$date1Array[1].'-'.$date1Array[0])-strtotime('today'))/86400;
				$days = abs($days); 
				$daysTotal = floor($days);
				if($daysTotal>=30){
					$daysPending = round(((15/12/30)*($daysTotal+1)),2);
					$array[$i]["pending"]=number_format($daysPending, 2,',','.');
				}else{
					$array[$i]["pending"]=0;
				}
			}
		}
		echo json_encode(utf8ize($array));

	}else{
		echo 0;
	}



}elseif($_POST['type']=='AllVacations'){
	$state = $_POST['state'];
	$enterprise = $_POST['enterprise'];
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
	if($enterprise!=0){
		if($where==""){
			$where .= "WHERE p.emp_per=$enterprise";
		}else{
			$where .= " AND p.emp_per=$enterprise";
		}
	}

	$array = executeSelect("SELECT STR(p.rut_per) AS idrut,
						fp.ID,
						Format(Fecha_Inicio,'dd/mm/yyyy') AS FechaInicio,
						Format(Fecha_Fin,'dd/mm/yyyy') AS FechaFin,
						Format(Fecha_Reintegracion,'dd/mm/yyyy') AS FechaReintegracion,
						fp.Dias_Habiles,
						fp.Dias_Inhabiles,
						fp.Dias_Progresivos,
						fp.Periodo_Inicio & ' - ' & Periodo_Fin AS Periodo,
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
						(SELECT cf.finiq_descrip FROM CAUSASFIN cf WHERE cf.finiq_codigo=p.Causa_fin_per) AS codeEnd

						FROM (((FERIADO_PROPORCIONAL fp
						LEFT JOIN PERSONAL p ON p.rut_per=fp.Rut)
						LEFT JOIN T0010 t ON t.Pl_codigo=p.planta_per)
						LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
						$where
						ORDER BY p.rut_per, Fecha_Inicio");
	
	/*$array = executeSelect("SELECT 
					ID,
					Format(Fecha_Inicio,'dd/mm/yyyy') AS FechaInicio,
					Format(Fecha_Fin,'dd/mm/yyyy') AS FechaFin,
					Format(Fecha_Reintegracion,'dd/mm/yyyy') AS FechaReintegracion,
					Dias_Habiles,
					Dias_Inhabiles,
					Dias_Progresivos,
					Periodo_Inicio & ' - ' & Periodo_Fin AS Periodo,
					FROM FERIADO_PROPORCIONAL fp");*/
//					ORDER BY Fecha_Inicio");
	if(count($array)>0){
		for($i=0;$i<count($array);$i++){
			$result = glob('../documents/uploads/vacations/'.$array[$i]["ID"].'.*');
			//if (file_exists($target_file)) {
			if($result){
				$array[$i]['vacationStatus'] = 'OK';
			}else{
				$array[$i]['vacationStatus'] = 'PENDIENTE';
			}

		}


		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}
}

?>