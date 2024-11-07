<?php
header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

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
			//$array[$i]["eliminar"]='<button class="btn btn-danger" onclick="deleteRow(\''.$array[$i]['id'].'\')"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';
		}
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}


}elseif($_POST['type']=='one'){


	$array = executeSelect("SELECT 
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
						Format(ph.fecvig_per,'dd/mm/yyyy') AS contract_start,
						Format(ph.fecter_per,'dd/mm/yyyy') AS contract_end,
						ph.Causa_fin_per AS article,
						ph.estado_per AS status

						FROM ((PERSONAL_HISTORICO ph
						LEFT JOIN T0009 e ON e.Emp_codigo=ph.emp_per)
						LEFT JOIN T0010 pl ON pl.Pl_codigo=ph.planta_per)
						WHERE ph.rut_per=".$_POST['id']."

						UNION ALL
						
						SELECT 
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
						Format(p.fecvig_per,'dd/mm/yyyy') AS contract_start,
						Format(p.fecter_per,'dd/mm/yyyy') AS contract_end,
						p.Causa_fin_per AS article,
						p.estado_per AS status
						
						FROM ((PERSONAL p
						LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
						LEFT JOIN T0010 pl ON pl.Pl_codigo=p.planta_per)
						WHERE p.rut_per=".$_POST['id']);


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