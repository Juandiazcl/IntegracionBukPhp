<?php
header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();

if($_POST['type']=='all'){

	$state = $_POST['state'];
	$enterprise = $_POST['enterprise'];
	$loanState = $_POST['loanState'];
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

	if($loanState!='T'){
		if($loanState=='V'){
			if($where==""){
				$where .= "WHERE (SELECT COUNT(pr.ID) FROM PRESTAMO pr WHERE VAL(pr.RUT)=p.rut_per AND pr.Estado='IMPAGO' AND pr.ID_FINIQUITO_PERSONAL=0)>0";
			}else{
				$where .= " AND (SELECT COUNT(pr.ID) FROM PRESTAMO pr WHERE VAL(pr.RUT)=p.rut_per AND pr.Estado='IMPAGO' AND pr.ID_FINIQUITO_PERSONAL=0)>0";
			}
		}else{
			if($where==""){
				$where .= "WHERE (SELECT COUNT(pr.ID) FROM PRESTAMO pr WHERE VAL(pr.RUT)=p.rut_per AND pr.Estado='IMPAGO')=0 AND (SELECT COUNT(pr.ID) FROM PRESTAMO pr WHERE VAL(pr.RUT)=p.rut_per AND pr.Estado='PAGADO')>0";
			}else{
				$where .= " AND (SELECT COUNT(pr.ID) FROM PRESTAMO pr WHERE VAL(pr.RUT)=p.rut_per AND pr.Estado='IMPAGO')=0 AND (SELECT COUNT(pr.ID) FROM PRESTAMO pr WHERE VAL(pr.RUT)=p.rut_per AND pr.Estado='PAGADO')>0";
			}
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
							(SELECT COUNT(pr.ID) FROM PRESTAMO pr WHERE VAL(pr.RUT)=p.rut_per AND pr.Estado='IMPAGO' AND ID_FINIQUITO_PERSONAL=0) AS loans
							FROM ((PERSONAL p
							LEFT JOIN T0010 t ON t.Pl_codigo=p.planta_per)
							LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
							$where
							ORDER BY p.rut_per");
	if(count($array)>0){
		for($i=0;$i<count($array);$i++){
			$array[$i]["view"]='<button class="btn btn-warning" onclick="viewRow(\''.intval($array[$i]['rut_per']).'\',\''.$array[$i]['rut'].'\',\''.$array[$i]['fullname'].'\')"><i class="fa fa-eye fa-lg fa-fw"></i></button>';
			$array[$i]["excel"]='<button class="btn btn-success" onclick="toExcelOne(\''.intval($array[$i]['rut_per']).'\',\''.$array[$i]['rut'].'\',\''.$array[$i]['fullname'].'\')"><i class="fa fa-file-excel-o fa-lg fa-fw"></i></button>';
			//$array[$i]["eliminar"]='<button class="btn btn-danger" onclick="deleteRow(\''.$array[$i]['id'].'\')"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';
		}
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}


}elseif($_POST['type']=='one'){

	/*$array = executeSelect("SELECT p.*, 
							FORMAT(p.Fecha_Inicio,'dd/mm/yyyy') AS FechaInicio,
							FORMAT(p.Fecha_Fin,'dd/mm/yyyy') AS FechaFin,
							(SELECT SUM(Monto) FROM PRESTAMO_PAGOS pp WHERE pp.ID_PRESTAMO=p.ID AND pp.Estado='IMPAGO') AS Saldo
							FROM PRESTAMO p WHERE p.RUT='".$_POST['id']."' AND p.ID_FINIQUITO_PERSONAL=".$_POST['period']);*/
	

	$array = executeSelect("SELECT p.*, 
							FORMAT(p.Fecha_Inicio,'dd/mm/yyyy') AS FechaInicio,
							FORMAT(p.Fecha_Fin,'dd/mm/yyyy') AS FechaFin,
							IIF(p.Tipo='A_CUENTA',
							Valor_Total-IIF(ISNULL((SELECT SUM(Monto) FROM PRESTAMO_ABONOS pp WHERE pp.ID_PRESTAMO=p.ID)),0,(SELECT SUM(Monto) FROM PRESTAMO_ABONOS pp WHERE pp.ID_PRESTAMO=p.ID)),

							(SELECT SUM(Monto) FROM PRESTAMO_PAGOS pp WHERE pp.ID_PRESTAMO=p.ID AND pp.Estado='IMPAGO') +

							IIF(ISNULL((SELECT SUM(Monto)-SUM(Abono) FROM PRESTAMO_PAGOS pp WHERE pp.ID_PRESTAMO=p.ID AND pp.Estado='ABONADO')),0,(SELECT SUM(Monto)-SUM(Abono) FROM PRESTAMO_PAGOS pp WHERE pp.ID_PRESTAMO=p.ID AND pp.Estado='ABONADO'))

							) AS Saldo
							
							FROM PRESTAMO p WHERE p.RUT='".$_POST['id']."' AND p.ID_FINIQUITO_PERSONAL=".$_POST['period']);


	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}

}elseif($_POST['type']=='oneLoan'){
	$array = executeSelect("SELECT p.*, 
							FORMAT(p.Fecha_Inicio,'dd/mm/yyyy') AS FechaInicio,
							FORMAT(p.Fecha_Fin,'dd/mm/yyyy') AS FechaFin,
							IIF(p.Tipo='A_CUENTA',
							Valor_Total-IIF(ISNULL((SELECT SUM(Monto) FROM PRESTAMO_ABONOS pp WHERE pp.ID_PRESTAMO=p.ID)),0,(SELECT SUM(Monto) FROM PRESTAMO_ABONOS pp WHERE pp.ID_PRESTAMO=p.ID)),
							(SELECT SUM(Monto) FROM PRESTAMO_PAGOS pp WHERE pp.ID_PRESTAMO=p.ID AND pp.Estado='IMPAGO')) AS Saldo
							FROM PRESTAMO p WHERE p.ID=".$_POST['id']);


	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}

}elseif($_POST['type']=='dues'){

	$array = executeSelect("SELECT *, FORMAT(Fecha,'mm/yyyy') AS Fecha1, MONTH(Fecha) AS MonthX, YEAR(Fecha) AS YearX FROM PRESTAMO_PAGOS WHERE ID_PRESTAMO=".$_POST['id']." ORDER BY Fecha");
	
	$arrayMonth = executeSelect("SELECT * FROM T0058");

	for($i=0;$i<count($array);$i++){
		$array[$i]["status"] = "";
		if($array[$i]['Estado']=='PAGADO'){
			if($array[$i]['YearX']==$arrayMonth[0]['ANO']){
				if($array[$i]['MonthX']<$arrayMonth[0]['Mes']){
					$array[$i]['status'] = "disabled";
				}
			}elseif($array[$i]['YearX']<$arrayMonth[0]['ANO']){
				$array[$i]['status'] = "disabled";
			}
		}
	}

	$arrayLoan = executeSelect("SELECT * FROM PRESTAMO WHERE ID=".$_POST['id']);


	if($_POST['period']==0){
		if(count($array)>0){
			$arrayEnterprise = executeSelect("SELECT e.*
											FROM ((PERSONAL p
											LEFT JOIN T0010 t ON t.Pl_codigo=p.planta_per)
											LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
											WHERE p.rut_per=".$arrayLoan[0]['RUT']."
											ORDER BY p.rut_per");
			$array[0]['enterpriseName']=$arrayEnterprise[0]['EmpNombre'];
			$array[0]['enterpriseRUT']=number_format($arrayEnterprise[0]['Emp_codigo'], 0,'','.').'-'.$arrayEnterprise[0]['Empdv'];
		}
	}else{
		if(count($array)>0){
			$arrayEnterprise = executeSelect("SELECT e.*
											FROM ((PERSONAL_HISTORICO p
											LEFT JOIN T0010 t ON t.Pl_codigo=p.planta_per)
											LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
											WHERE p.rut_per='".$arrayLoan[0]['RUT']."'
											ORDER BY p.rut_per");
			$array[0]['enterpriseName']=$arrayEnterprise[0]['EmpNombre'];
			$array[0]['enterpriseRUT']=number_format($arrayEnterprise[0]['Emp_codigo'], 0,'','.').'-'.$arrayEnterprise[0]['Empdv'];
		}
	}
	
	$array[0]['payment'] = 0;
	
	if($arrayLoan[0]['Tipo']=='A_CUENTA'){
		$arrayPayment = executeSelect("SELECT SUM(Monto) AS Abono FROM PRESTAMO_ABONOS WHERE ID_PRESTAMO=".$_POST['id']);
		if(count($arrayPayment)>0){
			$array[0]['payment'] = $arrayPayment[0]['Abono'];
		}
	}

	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}

}elseif($_POST['type']=='getActualMonth'){

	$array = executeSelect("SELECT * FROM T0058");
	if($array[0]['Mes']<10) $array[0]['Mes']="0".$array[0]['Mes'];
	$array[0]['month'] = $array[0]['Mes'];
	$array[0]['year'] = $array[0]['ANO'];


	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}


}elseif($_POST['type']=='allDetail'){
	$state = $_POST['state'];
	$enterprise = $_POST['enterprise'];
	$loanState = $_POST['loanState'];
	$plant = 98;	
	if(isset($_POST['plant'])){
		$plant = $_POST['plant'];
	}

	$where = "";
	if($state!='T'){
		$where .= " AND per.estado_per='$state'";
	}

	if($plant!=98){
		$where .= " AND per.planta_per=$plant";
	}
	if($enterprise!=0){
		$where .= " AND per.emp_per=$enterprise";
	}

	if($loanState!='T'){
		if($loanState=='V'){
			$where .= " AND (SELECT COUNT(*) FROM PRESTAMO_PAGOS pg WHERE pg.Estado='IMPAGO' AND pg.ID_PRESTAMO=p.ID)>0";
		}else{
			$where .= " AND (SELECT COUNT(*) FROM PRESTAMO_PAGOS pg WHERE pg.Estado='IMPAGO' AND pg.ID_PRESTAMO=p.ID)=0";
		}
	}

	$array = executeSelect("SELECT 
							per.Nom_per & ' ' & per.Apepat_per & ' ' & per.Apemat_per AS fullname,
							per.rut_per & '-' & per.dv_per AS rut,
							e.EmpSigla AS enterpriseInitials,
							e.EmpNombre AS enterpriseName,
							e.Emp_codigo & '-' & e.Empdv AS enterpriseRUT,
							FORMAT(p.Fecha_Inicio,'dd/mm/yyyy') AS startDate,
							FORMAT(p.Fecha_Fin,'dd/mm/yyyy') AS endDate,
							p.Cuotas_Totales AS duesNumber,
							p.Valor_Total AS amountTotal,
							t.PlNombre AS plant,
							p.Tipo AS typeLoan,
							(SELECT TOP 1 Monto FROM PRESTAMO_PAGOS pg WHERE pg.ID_PRESTAMO=p.ID ORDER BY Numero ASC) AS amountDue,
							(SELECT SUM(Monto) FROM PRESTAMO_PAGOS pg WHERE pg.Estado='IMPAGO' AND pg.ID_PRESTAMO=p.ID)+
							IIF(ISNULL((SELECT SUM(Monto)-SUM(Abono) FROM PRESTAMO_PAGOS pg WHERE pg.ID_PRESTAMO=p.ID AND pg.Estado='ABONADO')),0,(SELECT SUM(Monto)-SUM(Abono) FROM PRESTAMO_PAGOS pg WHERE pg.ID_PRESTAMO=p.ID AND pg.Estado='ABONADO'))
							AS balance,
							(SELECT COUNT(*) FROM PRESTAMO_PAGOS pg WHERE pg.Estado IN ('IMPAGO','ABONADO') AND pg.ID_PRESTAMO=p.ID) AS balanceDues,
							(SELECT SUM(Monto) FROM PRESTAMO_ABONOS pa WHERE pa.ID_PRESTAMO=p.ID) AS payment
							FROM (((PRESTAMO p
							LEFT JOIN PERSONAL per ON per.rut_per=VAL(p.RUT))
							LEFT JOIN T0010 t ON t.Pl_codigo=per.planta_per)
							LEFT JOIN T0009 e ON e.Emp_codigo=per.emp_per)
							WHERE p.ID_FINIQUITO_PERSONAL=0 $where");

	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}

}elseif($_POST['type']=='payments'){
	$id = $_POST['id'];
	$array = executeSelect("SELECT *, FORMAT(Fecha,'dd/mm/yyyy') AS Fecha1 FROM PRESTAMO_ABONOS WHERE ID_PRESTAMO=$id");

	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}	

}elseif($_POST['type']=='fullAdd'){
	$id = $_POST['id'];
	$array = executeSelect("SELECT SUM(Abono) AS Abono_Total FROM PRESTAMO_PAGOS WHERE ID_PRESTAMO=$id AND Estado='ABONADO'");

	if(count($array)>0){
		if(is_numeric($array[0]['Abono_Total'])){
			echo $array[0]['Abono_Total'];
		}else{
			echo 0;
		}
	}else{
		echo 0;
	}	
}



?>