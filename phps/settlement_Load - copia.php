<?php
header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();

function searchForId($id, $array) {
   foreach ($array as $key => $val) {
       if ($val['rutrem'] === $id) {
           return $key;
       }
   }
   return null;
}

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
							STR(IIF(ISNULL((SELECT SUM(fp.Dias_Habiles) FROM FERIADO_PROPORCIONAL fp WHERE fp.Rut=p.rut_per AND (fp.ID_FINIQUITO_PERSONAL IS NULL OR fp.ID_FINIQUITO_PERSONAL=0))) , 0, (SELECT SUM(fp.Dias_Habiles) FROM FERIADO_PROPORCIONAL fp WHERE fp.Rut=p.rut_per AND (fp.ID_FINIQUITO_PERSONAL IS NULL OR fp.ID_FINIQUITO_PERSONAL=0)))) AS vacationDays

							FROM ((PERSONAL p
							LEFT JOIN T0010 t ON t.Pl_codigo=p.planta_per)
							LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
							$where
							ORDER BY p.rut_per");

	$arraySalary = executeSelect("SELECT r.*
									FROM REM021 r
									WHERE r.codhdrem='H001'
									ORDER BY r.rutrem, r.aaaarem DESC, r.mmrem DESC");

	if(count($array)>0){
		for($i=0;$i<count($array);$i++){
			$array[$i]["select"]='<input type="checkbox"></input>';
			$array[$i]["view"]='<button class="btn btn-warning" onclick="viewRow(\''.$array[$i]['rut_per'].'\')"><i class="fa fa-eye fa-lg fa-fw"></i></button>';

			$rut = "";
			$salaryBase = 0;
			if(strlen($array[$i]['id'])==7) $rut = "   ".$array[$i]['id'];
			if(strlen($array[$i]['id'])==8) $rut = "  ".$array[$i]['id'];
			if(strlen($array[$i]['id'])==9) $rut = " ".$array[$i]['id'];
			//echo $rut;
			//echo searchForId($rut,$arraySalary);
			$count = 0;
			for($j=0;$j<count($arraySalary);$j++){
				if($rut==$arraySalary[$j]['rutrem']){
					$salaryBase += $arraySalary[$j]['valrem2'];
					$count++;
				}else{
					if($count>0){
						$j=count($arraySalary);
					}
				}
				if($count==3){
					$j=count($arraySalary);
				}
			}
			if($count==0){
				$array[$i]["salaryx"] = $array[$i]['salary'];
			}else{
				$array[$i]["salaryx"] = $salaryBase/$count;
			}
			/*$salaryBase = 0;
			$rut = "";
			if(strlen($array[$i]['id'])==7) $rut = "   ".$array[$i]['id'];
			if(strlen($array[$i]['id'])==8) $rut = "  ".$array[$i]['id'];
			if(strlen($array[$i]['id'])==9) $rut = " ".$array[$i]['id'];

			$arraySalary = executeSelect("SELECT TOP 3 r.*,
									(SELECT rm.valrem2 FROM REM021 rm WHERE rm.codhdrem='H001'
									AND rm.rutrem=r.rutrem AND rm.aaaarem=r.aaaarem AND rm.mmrem=r.mmrem
									AND rm.cc1rem=r.cc1rem) AS Sueldo
									FROM REM021 r
									WHERE r.rutrem='$rut'
									AND r.codhdrem='P004'
									AND r.valrem2=0
									ORDER BY r.aaaarem DESC, r.mmrem DESC");
			for($j=0;$j<count($arraySalary);$j++){
				$salaryBase += $arraySalary[$j]['Sueldo'];
			}
			$salaryBase = $salaryBase/count($arraySalary);
			$array[$i]["salaryx"] = $salaryBase;*/

			//$array[$i]["eliminar"]='<button class="btn btn-danger" onclick="deleteRow(\''.$array[$i]['id'].'\')"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';

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
	$arraySalary = executeSelect("SELECT r.*
								FROM REM021 r
								WHERE r.codhdrem='H001'
								ORDER BY r.rutrem, r.aaaarem DESC, r.mmrem DESC");


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

}

?>