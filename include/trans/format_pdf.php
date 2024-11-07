<?php
header('Content-Type: text/html; charset=utf8'); 
ini_set('max_execution_time', 300);
//include("../../connection/connection.php");

session_start();

if(!isset($_SESSION['userId'])){
	header('Location: ../../login.php');
}elseif($_SESSION['display']['settlementPayment']['view']!='' && $_SESSION['display']['settlement']['view']!=''){
	header('Location: ../../index.php');
}

function executeSelect($query){
	//$db = getcwd() . "\\" . 'GX_DATA.mdb';
	$db = "C:\Program Files (x86)\Personal y Remuneraciones\GX_DATA.mdb";
	//$db = "C:\\xampp\www\Pulmodon\connection\GX_DATA.mdb";
	$dsn = "DRIVER={Microsoft Access Driver (*.mdb)};
	DBQ=$db";
	$conn = odbc_connect($dsn,'','');
	if(!$conn){ 
		exit("Error al conectar: ".$conn);
	}
	
	$rs = odbc_exec($conn, utf8_decode($query));
	if(!$rs){ 
		exit("Error en la consulta SQL");
	}
	$i=0;
	$array = null;

	while($row = odbc_fetch_array($rs)){
		$array[$i] = $row;
		$i++;
	}
	odbc_close($conn);

	return $array;
}

function getSettlementVacations($date,$days){
	if($date[2]=="/"){
		$dateStart = explode("/", $date);
	}else{
		$dateStart = explode("-", $date);
	}
	$vacationDays = $days;
	$fecha = new DateTime($dateStart[2].'-'.$dateStart[1].'-'.$dateStart[0]);
	$fecha->add(new DateInterval('P1D'));

	$date=date_create($fecha->format('Y-m-d'));
	$retardDate=$fecha->format('m/d/Y');
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

	return $vacationDays;
}


$filename = 0; //Para los casos en que se requiera (documentos por persona)
$arrayPersonal = 0;
if($_GET['type']=='all'){
	$id = $_GET['id'];
	$arrayPersonal = executeSelect("SELECT 
									FORMAT(f.fecha_creacion,'dd/mm/yyyy') AS Creacion,
									fp.rut,
									FORMAT(fp.fecha_inicio,'dd/mm/yyyy') AS Inicio,
									FORMAT(fp.fecha_fin,'dd/mm/yyyy') AS Fin,
									fp.sueldo_base,
									fp.vacaciones_proporcionales,
									fp.liquidaciones,
									fp.liquidacion_fecha,
									fp.gratificacion,
									fp.colacion,
									fp.movilizacion,
									fp.indemnizacion_servicio,
									fp.indemnizacion_aviso,
									fp.indemnizacion_voluntaria,
									fp.indemnizacion_mes,
									fp.prestamo_empresa,
									fp.prestamo_caja,
									fp.afc,
									fp.cargo,
									fp.empresa_rut,
									c.finiq_descrip,
									p.Nom_per & ' ' & p.Apepat_per & ' ' & p.Apemat_per AS Nombre,
									p.rut_per,
									p.dv_per,
									e.Empdv,
									e.EmpNombre,
									e.Empdir,
									pl.PlNombre,
									pl.Pldir,
									fp.ID AS ID_FINIQUITO_PERSONAL
								 	FROM (((((FINIQUITO_PERSONAL fp 
								 	LEFT JOIN FINIQUITO f ON f.ID=fp.ID_FINIQUITO)
								 	LEFT JOIN CAUSASFIN c ON c.finiq_codigo=f.articulo)
								 	LEFT JOIN PERSONAL p ON p.rut_per=fp.rut)
								 	LEFT JOIN T0009 e ON e.Emp_codigo=fp.empresa_rut)
								 	LEFT JOIN T0010 pl ON pl.Pl_codigo=fp.planta_id)

								 	WHERE fp.ID_FINIQUITO=$id");

}elseif($_GET['type']=='group'){
	$id = $_GET['id'];
	$arraySettlementID=executeSelect("SELECT (SELECT TOP 1 id FROM FINIQUITO_PERSONAL fp WHERE fp.rut=p.rut_per ORDER BY fp.id DESC) AS lastId FROM personal p WHERE p.rut_per IN ($id)");

	$listEmployee="";
	$valid=0;
	for($s=0;$s<count($arraySettlementID);$s++){
		if(isset($arraySettlementID[$s]["lastId"])){
			if($valid==0){
				$listEmployee = $arraySettlementID[$s]["lastId"];
			}else{
				$listEmployee .= ",".$arraySettlementID[$s]["lastId"];
			}
			$valid++;
		}
	}

	
	$arrayPersonal = executeSelect("SELECT 
									FORMAT(f.fecha_creacion,'dd/mm/yyyy') AS Creacion,
									fp.rut,
									FORMAT(fp.fecha_inicio,'dd/mm/yyyy') AS Inicio,
									FORMAT(fp.fecha_fin,'dd/mm/yyyy') AS Fin,
									fp.sueldo_base,
									fp.vacaciones_proporcionales,
									fp.liquidaciones,
									fp.liquidacion_fecha,
									fp.gratificacion,
									fp.colacion,
									fp.movilizacion,
									fp.indemnizacion_servicio,
									fp.indemnizacion_aviso,
									fp.indemnizacion_voluntaria,
									fp.indemnizacion_mes,
									fp.prestamo_empresa,
									fp.prestamo_caja,
									fp.afc,
									fp.cargo,
									fp.empresa_rut,
									c.finiq_descrip,
									p.Nom_per & ' ' & p.Apepat_per & ' ' & p.Apemat_per AS Nombre,
									p.rut_per,
									p.dv_per,
									e.Empdv,
									e.EmpNombre,
									e.Empdir,
									pl.PlNombre,
									pl.Pldir,
									fp.ID AS ID_FINIQUITO_PERSONAL
								 	FROM (((((FINIQUITO_PERSONAL fp 
								 	LEFT JOIN FINIQUITO f ON f.ID=fp.ID_FINIQUITO)
								 	LEFT JOIN CAUSASFIN c ON c.finiq_codigo=f.articulo)
								 	LEFT JOIN PERSONAL p ON p.rut_per=fp.rut)
								 	LEFT JOIN T0009 e ON e.Emp_codigo=fp.empresa_rut)
								 	LEFT JOIN T0010 pl ON pl.Pl_codigo=fp.planta_id)

								 	WHERE fp.ID IN ($listEmployee)");


}elseif($_GET['type']=='one'){
	$id = $_GET['id'];
	$arrayPersonal = executeSelect("SELECT 
									FORMAT(f.fecha_creacion,'dd/mm/yyyy') AS Creacion,
									fp.rut,
									FORMAT(fp.fecha_inicio,'dd/mm/yyyy') AS Inicio,
									FORMAT(fp.fecha_fin,'dd/mm/yyyy') AS Fin,
									fp.sueldo_base,
									fp.vacaciones_proporcionales,
									fp.liquidaciones,
									fp.liquidacion_fecha,
									fp.gratificacion,
									fp.colacion,
									fp.movilizacion,
									fp.indemnizacion_servicio,
									fp.indemnizacion_aviso,
									fp.indemnizacion_voluntaria,
									fp.indemnizacion_mes,
									fp.prestamo_empresa,
									fp.prestamo_caja,
									fp.cargo,
									fp.afc,
									fp.empresa_rut,
									c.finiq_descrip,
									p.Nom_per & ' ' & p.Apepat_per & ' ' & p.Apemat_per AS Nombre,
									p.rut_per,
									p.dv_per,
									e.Empdv,
									e.EmpNombre,
									e.Empdir,
									pl.PlNombre,
									pl.Pldir,
									fp.ID AS ID_FINIQUITO_PERSONAL
								 	FROM (((((FINIQUITO_PERSONAL fp 
								 	LEFT JOIN FINIQUITO f ON f.ID=fp.ID_FINIQUITO)
								 	LEFT JOIN CAUSASFIN c ON c.finiq_codigo=f.articulo)
								 	LEFT JOIN PERSONAL p ON p.rut_per=fp.rut)
								 	LEFT JOIN T0009 e ON e.Emp_codigo=fp.empresa_rut)
								 	LEFT JOIN T0010 pl ON pl.Pl_codigo=fp.planta_id)

								 	WHERE fp.ID=$id");
}

$htmlFullAll = '';

for($i=0;$i<count($arrayPersonal);$i++){

	$dateCreation = $arrayPersonal[$i]["Creacion"];
	if($_GET['date']!='-'){
		$dateCreation=$_GET['date'];
	}
//for($i=0;$i<1;$i++){
	$htmlHead = '<tr>
					<td colspan="4"></td>
					<td colspan="2" style="text-align: right;">Código Interno: '.$arrayPersonal[$i]["ID_FINIQUITO_PERSONAL"].'</td>
				</tr>
				<tr>
					<td colspan="6" style="text-align: center; font-weight: bold; font-size: 25px;">
						FINIQUITO
					<td>
				</tr>';

	$htmlBody1 = '<tr>
					<td colspan="6" style="text-align: justify;">';

	if($arrayPersonal[$i]["ID_FINIQUITO_PERSONAL"]==1700){
		$htmlBody1 .= '<p>En Santiago, a <b>'.$dateCreation.'</b>, entre <b>'.utf8_encode($arrayPersonal[$i]["EmpNombre"]).'</b>, <b>RUT '.number_format($arrayPersonal[$i]["empresa_rut"], 0,'','.').'-'.$arrayPersonal[$i]["Empdv"].'</b>, con domicilio en <b>'.utf8_encode($arrayPersonal[$i]["Empdir"]).'</b>, y <b>'.utf8_encode($arrayPersonal[$i]["Nombre"]).'</b>, <b>N° PASAPORTE AU917837</b>, se acuerda del siguiente finiquito:</p>';
	}else{
		$htmlBody1 .= '<p>En Santiago, a <b>'.$dateCreation.'</b>, entre <b>'.utf8_encode($arrayPersonal[$i]["EmpNombre"]).'</b>, <b>RUT '.number_format($arrayPersonal[$i]["empresa_rut"], 0,'','.').'-'.$arrayPersonal[$i]["Empdv"].'</b>, con domicilio en <b>'.utf8_encode($arrayPersonal[$i]["Empdir"]).'</b>, y <b>'.utf8_encode($arrayPersonal[$i]["Nombre"]).'</b>, <b>RUT '.number_format($arrayPersonal[$i]["rut"], 0,'','.').'-'.$arrayPersonal[$i]["dv_per"].'</b>, se acuerda del siguiente finiquito:</p>';
	}
	if($arrayPersonal[$i]["ID_FINIQUITO_PERSONAL"]==1085){
		$htmlBody1 .= '	<p><b>PRIMERO: Don(a) '.utf8_encode($arrayPersonal[$i]["Nombre"]).'</b> declara haberle prestado servicios a <b>'.utf8_encode($arrayPersonal[$i]["EmpNombre"]).'</b> en calidad de <b>'.utf8_encode($arrayPersonal[$i]["cargo"]).'</b> en establecimiento ubicado en '.utf8_encode($arrayPersonal[$i]["Pldir"]).', desde el <b>'.$arrayPersonal[$i]["Inicio"].'</b> hasta el <b>'.$arrayPersonal[$i]["Fin"].'</b>, fecha esta última de terminación de los servicios por la siguiente causa, de acuerdo a lo dispuesto en el <b>Artículo 160 N° 1 letra c) del Código del Trabajo, "Vías de hecho ejercidas por el trabajador en contra del empleador o de cualquier trabajador que se desempeñe en la misma empresa"</b></p>';
	
	}elseif($arrayPersonal[$i]["ID_FINIQUITO_PERSONAL"]==1443 || $arrayPersonal[$i]["ID_FINIQUITO_PERSONAL"]==1665){
		$htmlBody1 .= '	<p><b>PRIMERO: Don(a) '.utf8_encode($arrayPersonal[$i]["Nombre"]).'</b> declara haberle prestado servicios a <b>'.utf8_encode($arrayPersonal[$i]["EmpNombre"]).'</b> en calidad de <b>'.utf8_encode($arrayPersonal[$i]["cargo"]).'</b> en establecimiento ubicado en '.utf8_encode($arrayPersonal[$i]["Pldir"]).', desde el <b>'.$arrayPersonal[$i]["Inicio"].'</b> hasta el <b>'.$arrayPersonal[$i]["Fin"].'</b>, fecha esta última de terminación de los servicios por la siguiente causa, de acuerdo a lo dispuesto en el <b>'.utf8_encode($arrayPersonal[$i]["finiq_descrip"]).'.</b>. Los hechos en que se funda la causal invocada consisten en la necesidad de mejorar la competitividad en el negocio, por lo que la empresa ha decidido racionalizar y reestructurar el área de administración, finanzas y contabilidad, lo que ha hecho necesario la reducción de personal</p>';
	}else{
		$htmlBody1 .= '	<p><b>PRIMERO: Don(a) '.utf8_encode($arrayPersonal[$i]["Nombre"]).'</b> declara haberle prestado servicios a <b>'.utf8_encode($arrayPersonal[$i]["EmpNombre"]).'</b> en calidad de <b>'.utf8_encode($arrayPersonal[$i]["cargo"]).'</b> en establecimiento ubicado en '.utf8_encode($arrayPersonal[$i]["Pldir"]).', desde el <b>'.$arrayPersonal[$i]["Inicio"].'</b> hasta el <b>'.$arrayPersonal[$i]["Fin"].'</b>, fecha esta última de terminación de los servicios por la siguiente causa, de acuerdo a lo dispuesto en el <b>'.utf8_encode($arrayPersonal[$i]["finiq_descrip"]).'.</b></p>';
	}

	$htmlBody1 .= '	<p><b>SEGUNDO: Don(a) '.utf8_encode($arrayPersonal[$i]["Nombre"]).'</b> declara recibir en este acto, a su entera satisfacción, de parte de <b>'.utf8_encode($arrayPersonal[$i]["EmpNombre"]).'</b> las sumas que a continuación se indican, por los siguientes conceptos:</p>
					</td>
				</tr>';

$sign = "";

$text = "";
$textValue = "";

$htmlDetail_Liq = "0";
$htmlDetail_IndSer = "0";
$htmlDetail_IndAvi = "0";
$htmlDetail_IndVol = "0";
$htmlDetail_IndMes = "0";
$htmlDetail_PreEmp = "0";
$htmlDetail_PreCaj = "0";
$htmlDetail_AFC = "0";
$htmlDetail_Vac = "0";
if($arrayPersonal[$i]["liquidaciones"]<>0){
	$paymentDate = explode('/',$arrayPersonal[$i]["liquidacion_fecha"]);
	if($paymentDate[0]==1) $paymentDate[0]='enero';
	if($paymentDate[0]==2) $paymentDate[0]='febrero';
	if($paymentDate[0]==3) $paymentDate[0]='marzo';
	if($paymentDate[0]==4) $paymentDate[0]='abril';
	if($paymentDate[0]==5) $paymentDate[0]='mayo';
	if($paymentDate[0]==6) $paymentDate[0]='junio';
	if($paymentDate[0]==7) $paymentDate[0]='julio';
	if($paymentDate[0]==8) $paymentDate[0]='agosto';
	if($paymentDate[0]==9) $paymentDate[0]='septiembre';
	if($paymentDate[0]==10) $paymentDate[0]='octubre';
	if($paymentDate[0]==11) $paymentDate[0]='noviembre';
	if($paymentDate[0]==12) $paymentDate[0]='diciembre';
	$text .= "<br/>Liquidación de sueldo ".$paymentDate[0]." de ".$paymentDate[1];
	$textValue .= "<br/>".number_format($arrayPersonal[$i]["liquidaciones"], 0,'','.').".-";
	$sign .= "<br/>$";

	$htmlDetail_Liq = number_format($arrayPersonal[$i]["liquidaciones"], 0,'','.').", correspondiente al mes de ".$paymentDate[0]." de ".$paymentDate[1];
}

////////////////////////////Cálculo de últimos 3 sueldos/////////////////////////////////////
	$rut_per = intval($arrayPersonal[$i]["rut_per"]);
	
	if(strlen($rut_per)==7) {
		$rut_per='   '.$rut_per;
	}elseif(strlen($rut_per)==8) {
		$rut_per='  '.$rut_per;
	}

	$endDate="";
	if($arrayPersonal[$i]["Fin"][2]=='/'){
		$endDate = explode('/', $arrayPersonal[$i]["Fin"]);
	}else{
		$endDate = explode('-',$arrayPersonal[$i]["Fin"]);
	}

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

	$rut_perB = str_replace(" ", "", str_replace("'", "", $rut_per));


	$salaryYearWhere = "AND ((((aaaarem=$salaryYear1 AND mmrem=$salaryMonth1) OR (aaaarem=$salaryYear2 AND mmrem=$salaryMonth2)) OR (aaaarem=$salaryYear3 AND mmrem=$salaryMonth3)))";

	
	$arraySalary = executeSelect("SELECT r.*, FORMAT(p.fecvig_per,'dd/mm/yyyy') AS contractStart
							FROM REM021 r
							LEFT JOIN PERSONAL p ON p.cc1_per=r.cc1rem
							WHERE r.codhdrem IN ('H001','H004','H007','H008','H018','H019','H155')
							AND r.rutrem = '".$rut_per."'
							$salaryYearWhere
							AND p.rut_per = $rut_perB
							AND p.rut_per =  VAL(TRIM(r.rutrem))
							ORDER BY r.rutrem, r.aaaarem DESC, r.mmrem DESC, r.codhdrem");

	$max = count($arraySalary);
	$year = '';
	$month = '';
	if($max>21) $max=21;
	if($max==0){
		$max=1;
		$arrayP = executeSelect("SELECT *, FORMAT(fecvig_per,'dd/mm/yyyy') AS contractStart FROM PERSONAL WHERE rut_per = $rut_perB");

		$year = explode("/",$arrayP[0]['contractStart'])[2];
		$month = explode("/",$arrayP[0]['contractStart'])[1];
	}else{
		$year = explode("/",$arraySalary[0]['contractStart'])[2];
		$month = explode("/",$arraySalary[0]['contractStart'])[1];
	}


	$maxSalary = 0;
	$data='<tr><th colspan="6">Sueldos incluídos en cálculo:</th></tr>';
	$dataVacaciones='<tr><th colspan="6">Sueldos incluídos en cálculo:</th></tr>';
	$data.='<tr><th>Mes</th><th>Sueldo Base</th><th>Gratificación</th><th>Colación</th><th>Movilización</th><th>Total</th></tr>';
	$dataVacaciones.='<tr><th>Mes</th><th>Sueldo Base</th><th>Bono Empresa</th><th>Bono</th><th>Trato</th><th>Total</th></tr>';
	$dataSueldoBase = 0;
	$dataGratificacion = 0;
	$dataColacion = 0;
	$dataMovilizacion = 0;
	$dataTotal = 0;
	$dataSueldoVacaciones = 0;
	$dataBonoEmpresa = 0;
	$dataBono = 0;
	$dataBonoTrato = 0;

	for($s=0;$s<$max;$s=$s+7){

		if(intval($year)<intval($arraySalary[$s]['aaaarem'])){
			//if(intval($month)<=intval($arraySalary[$s]['mmrem'])){

				$data .= '<tr><td style="text-align: center; border: 1px solid black;">'.$arraySalary[$s]["mmrem"].'/'.$arraySalary[$s]["aaaarem"].'</td>
						<td style="text-align: right; border: 1px solid black;">'.number_format(intval($arraySalary[$s]["valrem2"]), 0,'','.').'</td>
						<td style="text-align: right; border: 1px solid black;">'.number_format(intval($arraySalary[$s+1]["valrem2"]), 0,'','.').'</td>
						<td style="text-align: right; border: 1px solid black;">'.number_format(intval($arraySalary[$s+5]["valrem2"]), 0,'','.').'</td>
						<td style="text-align: right; border: 1px solid black;">'.number_format(intval($arraySalary[$s+4]["valrem2"]), 0,'','.').'</td>
						<td style="text-align: right; border: 1px solid black;">'.number_format(intval($arraySalary[$s]["valrem2"]+$arraySalary[$s+1]["valrem2"]+$arraySalary[$s+5]["valrem2"]+$arraySalary[$s+4]["valrem2"]), 0,'','.').'</td></tr>';

				$dataVacaciones .= '<tr><td style="text-align: center; border: 1px solid black;">'.$arraySalary[$s]["mmrem"].'/'.$arraySalary[$s]["aaaarem"].'</td>
						<td style="text-align: right; border: 1px solid black;">'.number_format(intval($arraySalary[$s]["valrem2"]), 0,'','.').'</td>
						<td style="text-align: right; border: 1px solid black;">'.number_format(intval($arraySalary[$s+2]["valrem2"]), 0,'','.').'</td>
						<td style="text-align: right; border: 1px solid black;">'.number_format(intval($arraySalary[$s+3]["valrem2"]), 0,'','.').'</td>
						<td style="text-align: right; border: 1px solid black;">'.number_format(intval($arraySalary[$s+6]["valrem2"]), 0,'','.').'</td>
						<td style="text-align: right; border: 1px solid black;">'.number_format(intval($arraySalary[$s]["valrem2"]+$arraySalary[$s+2]["valrem2"]+$arraySalary[$s+3]["valrem2"]+$arraySalary[$s+6]["valrem2"]), 0,'','.').'</td></tr>';

				$dataSueldoBase += $arraySalary[$s]["valrem2"];
				$dataGratificacion += $arraySalary[$s+1]["valrem2"];
				$dataColacion += $arraySalary[$s+5]["valrem2"];
				$dataMovilizacion += $arraySalary[$s+4]["valrem2"];
				$dataTotal += $arraySalary[$s]["valrem2"]+$arraySalary[$s+1]["valrem2"]+$arraySalary[$s+5]["valrem2"]+$arraySalary[$s+4]["valrem2"];
				$dataSueldoVacaciones += $arraySalary[$s]["valrem2"]+$arraySalary[$s+2]["valrem2"]+$arraySalary[$s+3]["valrem2"]+$arraySalary[$s+6]["valrem2"];
				$dataBonoEmpresa += $arraySalary[$s+2]["valrem2"];
				$dataBono += $arraySalary[$s+3]["valrem2"];
				$dataBonoTrato += $arraySalary[$s+6]["valrem2"];
				$maxSalary++;
			//}
		
		}elseif(intval($year)==intval($arraySalary[$s]['aaaarem'])){
			if(intval($month)<=intval($arraySalary[$s]['mmrem'])){

				$data .= '<tr><td style="text-align: center; border: 1px solid black;">'.$arraySalary[$s]["mmrem"].'/'.$arraySalary[$s]["aaaarem"].'</td>
						<td style="text-align: right; border: 1px solid black;">'.number_format(intval($arraySalary[$s]["valrem2"]), 0,'','.').'</td>
						<td style="text-align: right; border: 1px solid black;">'.number_format(intval($arraySalary[$s+1]["valrem2"]), 0,'','.').'</td>
						<td style="text-align: right; border: 1px solid black;">'.number_format(intval($arraySalary[$s+5]["valrem2"]), 0,'','.').'</td>
						<td style="text-align: right; border: 1px solid black;">'.number_format(intval($arraySalary[$s+4]["valrem2"]), 0,'','.').'</td>
						<td style="text-align: right; border: 1px solid black;">'.number_format(intval($arraySalary[$s]["valrem2"]+$arraySalary[$s+1]["valrem2"]+$arraySalary[$s+5]["valrem2"]+$arraySalary[$s+4]["valrem2"]), 0,'','.').'</td></tr>';

				$dataVacaciones .= '<tr><td style="text-align: center; border: 1px solid black;">'.$arraySalary[$s]["mmrem"].'/'.$arraySalary[$s]["aaaarem"].'</td>
						<td style="text-align: right; border: 1px solid black;">'.number_format(intval($arraySalary[$s]["valrem2"]), 0,'','.').'</td>
						<td style="text-align: right; border: 1px solid black;">'.number_format(intval($arraySalary[$s+2]["valrem2"]), 0,'','.').'</td>
						<td style="text-align: right; border: 1px solid black;">'.number_format(intval($arraySalary[$s+3]["valrem2"]), 0,'','.').'</td>
						<td style="text-align: right; border: 1px solid black;">'.number_format(intval($arraySalary[$s+6]["valrem2"]), 0,'','.').'</td>
						<td style="text-align: right; border: 1px solid black;">'.number_format(intval($arraySalary[$s]["valrem2"]+$arraySalary[$s+2]["valrem2"]+$arraySalary[$s+3]["valrem2"]+$arraySalary[$s+6]["valrem2"]), 0,'','.').'</td></tr>';

				$dataSueldoBase += $arraySalary[$s]["valrem2"];
				$dataGratificacion += $arraySalary[$s+1]["valrem2"];
				$dataColacion += $arraySalary[$s+5]["valrem2"];
				$dataMovilizacion += $arraySalary[$s+4]["valrem2"];
				$dataTotal += $arraySalary[$s]["valrem2"]+$arraySalary[$s+1]["valrem2"]+$arraySalary[$s+5]["valrem2"]+$arraySalary[$s+4]["valrem2"];
				$dataSueldoVacaciones += $arraySalary[$s]["valrem2"]+$arraySalary[$s+2]["valrem2"]+$arraySalary[$s+3]["valrem2"]+$arraySalary[$s+6]["valrem2"];
				$dataBonoEmpresa += $arraySalary[$s+2]["valrem2"];
				$dataBono += $arraySalary[$s+3]["valrem2"];
				$dataBonoTrato += $arraySalary[$s+6]["valrem2"];
				$maxSalary++;
			}
		}
	}

	if($maxSalary==0) $maxSalary=1;
	$data .= '<tr><th style="text-align: center; border: 1px solid black;">PROMEDIO</th>
				<th style="text-align: right; border: 1px solid black;">'.number_format(intval($dataSueldoBase/($maxSalary)), 0,'','.').'</th>
				<th style="text-align: right; border: 1px solid black;">'.number_format(intval($dataGratificacion/($maxSalary)), 0,'','.').'</th>
				<th style="text-align: right; border: 1px solid black;">'.number_format(intval($dataColacion/($maxSalary)), 0,'','.').'</th>
				<th style="text-align: right; border: 1px solid black;">'.number_format(intval($dataMovilizacion/($maxSalary)), 0,'','.').'</th>
				<th style="text-align: right; border: 1px solid black;">'.number_format(intval($dataTotal/($maxSalary)), 0,'','.').'</th></tr>';

	$dataSueldoVacacionesPromedio = round($dataSueldoVacaciones/($maxSalary));
	$dataVacaciones .= '<tr><th style="text-align: center; border: 1px solid black;">PROMEDIO</th>
				<th style="text-align: right; border: 1px solid black;">'.number_format(intval($dataSueldoBase/($maxSalary)), 0,'','.').'</th>
				<th style="text-align: right; border: 1px solid black;">'.number_format(intval($dataBonoEmpresa/($maxSalary)), 0,'','.').'</th>
				<th style="text-align: right; border: 1px solid black;">'.number_format(intval($dataBono/($maxSalary)), 0,'','.').'</th>
				<th style="text-align: right; border: 1px solid black;">'.number_format(intval($dataBonoTrato/($maxSalary)), 0,'','.').'</th>
				<th style="text-align: right; border: 1px solid black;">'.number_format($dataSueldoVacacionesPromedio, 0,'','.').'</th></tr>';

	$htmlDetail_IndSer .= '<table style="border: 1px solid black; border-collapse: collapse; margin: 0 auto;">'.$data.'</table>';
	if($dataTotal==0) $dataTotal = 1;
	$serviceYears = $arrayPersonal[$i]["indemnizacion_servicio"] / intval($dataTotal/($maxSalary));
	$htmlDetail_IndSer .= 'Años de servicio X Valor Sueldo:<br/>
						<b>'.$serviceYears.' X '.intval($dataTotal/($maxSalary)).' = '.number_format($arrayPersonal[$i]["indemnizacion_servicio"], 0,'','.').'</b>';

if($arrayPersonal[$i]["indemnizacion_servicio"]>0){
	if($serviceYears>1){
		$text .= "<br/>Indemnización Legal por años de servicio (".$serviceYears." años)";
	}else{
		$text .= "<br/>Indemnización Legal por años de servicio (".$serviceYears." año)";
	}
	$textValue .= "<br/>".number_format($arrayPersonal[$i]["indemnizacion_servicio"], 0,'','.').".-";
	$sign .= "<br/>$";
}else{
	$htmlDetail_IndSer="0";
}

	$arrayAllVacation = executeSelect("SELECT 
									fp.ID,
									Format(fp.Fecha_Inicio,'dd/mm/yyyy') AS FechaInicio,
									Format(fp.Fecha_Fin,'dd/mm/yyyy') AS FechaFin,
									fp.Dias_Habiles,
									fp.Dias_Progresivos,
									fp.ID_FINIQUITO_PERSONAL,
									fp.Rut
									FROM FERIADO_PROPORCIONAL fp WHERE ID_FINIQUITO_PERSONAL=".$arrayPersonal[$i]['ID_FINIQUITO_PERSONAL']." ORDER BY Fecha_Inicio DESC");
	
	$daysUsed=0;
	$daysProgressive=0;
	if(count($arrayAllVacation>0)){
		for($j=0;$j<count($arrayAllVacation);$j++){
			$daysUsed += $arrayAllVacation[$j]['Dias_Habiles'];
			$daysProgressive += $arrayAllVacation[$j]['Dias_Progresivos'];
			
		}
	}

if($arrayPersonal[$i]["Inicio"][2]=='-'){
	$date1Array = explode('-', $arrayPersonal[$i]["Inicio"]);
	$date2Array = explode('-', $arrayPersonal[$i]["Fin"]);
}else{
	$date1Array = explode('/', $arrayPersonal[$i]["Inicio"]);
	$date2Array = explode('/', $arrayPersonal[$i]["Fin"]);
}
	$date1 = new DateTime($date1Array[2].'-'.$date1Array[1].'-'.$date1Array[0]);
	$date2 = new DateTime($date2Array[2].'-'.$date2Array[1].'-'.$date2Array[0]);

	$days = "";

	//$days = (strtotime($date1Array[2].'-'.$date1Array[1].'-'.$date1Array[0])-strtotime($date2Array[2].'-'.$date2Array[1].'-'.$date2Array[0]))/86400;

	$date1X = new DateTime($date1Array[2].'-'.$date1Array[1].'-'.$date1Array[0]);
	$date2X = new DateTime($date2Array[2].'-'.$date2Array[1].'-'.$date2Array[0]);
	$diffX = $date1X->diff($date2X);
	// will output 2 days
	//echo $diff->days . ' days ';
	$daysTotal = $diffX->days;

	//$days = abs($days); 
	//$daysTotal = floor($days);	

	if($daysTotal>=30){
		if($date1Array[1]==$date2Array[1] && $date1Array[2]==$date2Array[2]){ //Casos que trabajen 1 sólo mes pero completo
			$daysVacations = 0;
			$daysPending = 0;
			$daysUsed = 0;
		}else{
			$daysVacations = round(((15/12/30)*($daysTotal+1)),3)+$daysProgressive;
			$daysPending = round(((15/12/30)*($daysTotal+1))-$daysUsed,3)+$daysProgressive;
			$daysUsed -= $daysProgressive;
		}
	}else{		
		$daysVacations = 0;
		$daysPending = 0;
		$daysUsed = 0;
	}



	$htmlDetail_Vac = number_format($arrayPersonal[$i]["vacaciones_proporcionales"], 0,'','.');
	$htmlDetail_Vac .= '<table style="border: 1px solid black; border-collapse: collapse; margin: 0 auto;">'.$dataVacaciones.'</table><br/>';
	$htmlDetail_Vac .= '<table style="border: 1px solid black; border-collapse: collapse; margin: 0 auto;"><tr><th>Item</th><th>Total</th><th>Fórmula</th><th>Cálculo</th></tr>';
	$htmlDetail_Vac .= '<tr>
		<td style="border: 1px solid black;">Días Trabajados:</td>
		<td style="border: 1px solid black; text-align: right;">'.number_format($daysTotal, 0,'','.').'</td>
		<td style="border: 1px solid black; text-align: center;">Fin Contrato - Inicio Contrato</td>
		<td style="border: 1px solid black; text-align: center;">'.$arrayPersonal[$i]["Fin"].'-'.$arrayPersonal[$i]["Inicio"].'</td></tr>';
	$htmlDetail_Vac .= '<tr>
		<td style="border: 1px solid black;">Vacaciones Acumuladas:</td>
		<td style="border: 1px solid black; text-align: right;">'.number_format($daysVacations, 3,',','.').'</td>
		<td style="border: 1px solid black; text-align: center;">(Días Trabajados+1)*(Días Anuales/Meses Año/Días por Mes)+Días Progresivos</td>
		<td style="border: 1px solid black; text-align: center;">('.number_format($daysTotal, 0,'','.').'+1)*(15/12/30)+'.$daysProgressive.'</td></tr>';

	$htmlDetail_Vac .= '<tr>
		<td style="border: 1px solid black;">Días Utilizados:</td>
		<td style="border: 1px solid black; text-align: right;">'.number_format($daysUsed, 0,'','.').'</td>
		<td style="border: 1px solid black; text-align: center;">Suma días hábiles Vacaciones</td>
		<td style="border: 1px solid black; text-align: center;"></td></tr>';
	$htmlDetail_Vac .= '<tr>
		<td style="border: 1px solid black;">Días Progresivos:</td>
		<td style="border: 1px solid black; text-align: right;">'.number_format($daysProgressive, 0,'','.').'</td>
		<td style="border: 1px solid black; text-align: center;"></td>
		<td style="border: 1px solid black; text-align: center;"></td></tr>';
	$htmlDetail_Vac .= '<tr>
		<td style="border: 1px solid black;">Días Restantes:</td>
		<td style="border: 1px solid black; text-align: right;">'.number_format($daysPending, 3,',','.').'</td>
		<td style="border: 1px solid black; text-align: center;">Días Acumulados-(Días Utilizados+Días Progresivos)</td>
		<td style="border: 1px solid black; text-align: center;">'.number_format($daysVacations, 3,',','.').'-('.number_format($daysUsed, 0,'','.').'+'.number_format($daysProgressive, 0,'','.').')</td></tr>';

	$htmlDetail_Vac .= '<tr>
		<td style="border: 1px solid black;">Sueldo Vacaciones:</td>
		<td style="border: 1px solid black; text-align: right;">'.number_format($dataSueldoVacacionesPromedio, 3,',','.').'</td>
		<td style="border: 1px solid black; text-align: center;">Sueldo Promedio 3 últimos meses (incluye bonos)</td><td style="border: 1px solid black; text-align: center;"></td></tr>';
	$htmlDetail_Vac .= '<tr>
		<td style="border: 1px solid black;">Valor Día:</td>
		<td style="border: 1px solid black; text-align: right;">'.number_format(($dataSueldoVacacionesPromedio/30), 3,',','.').'</td>
		<td style="border: 1px solid black; text-align: center;">Sueldo Vacaciones / 30</td>
		<td style="border: 1px solid black; text-align: center;">'.number_format($dataSueldoVacacionesPromedio, 3,',','.').'/30</td></tr>';

	$htmlDetail_Vac .= '<tr>
		<td style="border: 1px solid black;">Pago Vacaciones Proporcionales:</td>
		<td style="border: 1px solid black; text-align: right;">'.number_format(getSettlementVacations($arrayPersonal[$i]["Fin"],$daysPending)*($dataSueldoVacacionesPromedio/30), 3,',','.').'</td>
		<td style="border: 1px solid black; text-align: center;">(Días Restantes (hábiles) + Días inhábiles)* Valor Día</td>
		<td style="border: 1px solid black; text-align: center;">('.number_format($daysPending, 3,',','.').'+'.number_format((getSettlementVacations($arrayPersonal[$i]["Fin"],$daysPending)-$daysPending), 0,'','.').')x'.number_format(($dataSueldoVacacionesPromedio/30), 3,',','.').'</td>
	</tr>';

	$htmlDetail_Vac .= '</table>';
	

/////////////////////////////////////////////////////////////////////////////////////////////////////////


//}
if($arrayPersonal[$i]["indemnizacion_aviso"]>0){
	$text .= "<br/>Indemnización Sustitutiva del Aviso Previo";
	$textValue .= "<br/>".number_format($arrayPersonal[$i]["indemnizacion_aviso"], 0,'','.').".-";
	$sign .= "<br/>$";
	$htmlDetail_IndAvi = number_format($arrayPersonal[$i]["indemnizacion_aviso"], 0,'','.');
}

if($arrayPersonal[$i]["indemnizacion_voluntaria"]>0){
	$text .= "<br/>Indemnización Voluntaria";
	$textValue .= "<br/>".number_format($arrayPersonal[$i]["indemnizacion_voluntaria"], 0,'','.').".-";
	$sign .= "<br/>$";
	$htmlDetail_IndVol = number_format($arrayPersonal[$i]["indemnizacion_voluntaria"], 0,'','.');
}

if($arrayPersonal[$i]["indemnizacion_mes"]>0){
	$text .= "<br/>Indemnización por mes trabajado";
	$textValue .= "<br/>".number_format($arrayPersonal[$i]["indemnizacion_mes"], 0,'','.').".-";
	$sign .= "<br/>$";

	$months = $arrayPersonal[$i]["indemnizacion_mes"]/(($arrayPersonal[$i]["sueldo_base"]+$arrayPersonal[$i]["gratificacion"]+$arrayPersonal[$i]["colacion"]+$arrayPersonal[$i]["movilizacion"])/30);

	$htmlDetail_IndMes = number_format($months, 0,'','.')." * (".number_format($arrayPersonal[$i]["sueldo_base"], 0,'','.')."+".number_format($arrayPersonal[$i]["gratificacion"], 0,'','.')."+".number_format($arrayPersonal[$i]["colacion"], 0,'','.')."+".number_format($arrayPersonal[$i]["movilizacion"], 0,'','.').") / 30) = ".number_format($arrayPersonal[$i]["indemnizacion_mes"], 0,'','.');
}

if($arrayPersonal[$i]["prestamo_empresa"]>0){
	if($arrayPersonal[$i]["ID_FINIQUITO_PERSONAL"]==1935){
		$text .= "<br/>Descuento Anticipo Febrero 2020";
	}else{
		$text .= "<br/>Descuento préstamo";
	}
	$textValue .= "<br/>- ".number_format($arrayPersonal[$i]["prestamo_empresa"], 0,'','.').".-";
	$sign .= "<br/>$";
	$htmlDetail_PreEmp = number_format($arrayPersonal[$i]["prestamo_empresa"], 0,'','.');

	$arrayLoan = executeSelect("SELECT p.*,
							(SELECT COUNT(pp.ID) FROM PRESTAMO_PAGOS pp WHERE pp.ID_PRESTAMO=p.ID AND Estado='IMPAGO') AS CuotasImpagas,
							(SELECT SUM(pp.Monto) FROM PRESTAMO_PAGOS pp WHERE pp.ID_PRESTAMO=p.ID AND Estado='IMPAGO') AS Saldo,
							FORMAT(p.Fecha_Inicio,'mm/yyyy') AS Fecha1
							FROM PRESTAMO p
							WHERE p.ID_FINIQUITO_PERSONAL=".$arrayPersonal[$i]['ID_FINIQUITO_PERSONAL']." AND p.Estado='IMPAGO'");

	$htmlDetail_PreEmp .= '<table style="border: 1px solid black; border-collapse: collapse; margin: 0 auto;"><tr><th>N°</th><th>Tipo</th><th>Inicio</th><th>Cuotas Pend.</th><th>Saldo</th></tr>';
	for($l=0;$l<count($arrayLoan);$l++){
		$tipo = $arrayLoan[$l]["Tipo"];
		if($tipo=='CUOTAS_AUTO'){
			$tipo = 'Cuotas';
		}else{
			$tipo = 'A Cuenta';
		}
		$htmlDetail_PreEmp .= '<tr>
								<td style="border: 1px solid black; text-align: center;">'.($l+1).'</td>
								<td style="border: 1px solid black; text-align: center;">'.$tipo.'</td>
								<td style="border: 1px solid black; text-align: center;">'.$arrayLoan[$l]["Fecha1"].'</td>
								<td style="border: 1px solid black; text-align: center;">'.$arrayLoan[$l]["CuotasImpagas"].'</td>
								<td style="border: 1px solid black; text-align: right;">'.number_format($arrayLoan[$l]["Saldo"],0,'','.').'</td>
							</tr>';
	}
	$htmlDetail_PreEmp .= '</table>';
}
if($arrayPersonal[$i]["prestamo_caja"]>0){
	if($arrayPersonal[$i]["ID_FINIQUITO_PERSONAL"]==1420){
		$text .= "<br/>Descuento por adelanto de Finiquito";
	}else{
		$text .= "<br/>Descuento por caja CCAF";
	}
	
	$textValue .= "<br/>- ".number_format($arrayPersonal[$i]["prestamo_caja"], 0,'','.').".-";
	$sign .= "<br/>$";
	$htmlDetail_PreCaj = number_format($arrayPersonal[$i]["prestamo_caja"], 0,'','.');
}
if($arrayPersonal[$i]["afc"]>0){
	$text .= "<br/>Descuento AFC";
	$textValue .= "<br/>- ".number_format($arrayPersonal[$i]["afc"], 0,'','.').".-";
	$sign .= "<br/>$";
	$htmlDetail_AFC = number_format($arrayPersonal[$i]["afc"], 0,'','.');
}

if($arrayPersonal[$i]["ID_FINIQUITO_PERSONAL"]==212){
	$text .= "<br/>Pago por préstamo no cubierto";
	$textValue .= "<br/>".number_format(-1*(($arrayPersonal[$i]["vacaciones_proporcionales"]+$arrayPersonal[$i]["liquidaciones"]+$arrayPersonal[$i]["indemnizacion_servicio"]+$arrayPersonal[$i]["indemnizacion_aviso"]+$arrayPersonal[$i]["indemnizacion_voluntaria"])-($arrayPersonal[$i]["prestamo_empresa"]+$arrayPersonal[$i]["prestamo_caja"]+$arrayPersonal[$i]["afc"])), 0,'','.').'.-';
	$sign .= "<br/>$";
}


$vacationDaysTotal = $daysPending + (getSettlementVacations($arrayPersonal[$i]["Fin"],$daysPending)-$daysPending);

	$htmlBody2 = '<tr style="font-weight: bold;">
					<td colspan="3">
						Vacaciones Proporcionales ('.number_format($vacationDaysTotal, 2,',','.').' días)'.$text.'
						<br/>
						<br/>
						TOTAL
					</td>
					<td style="text-align: right;">
						$'.$sign.'<br/><br/>$
					</td>
					<td style="text-align: right;">
						'.number_format($arrayPersonal[$i]["vacaciones_proporcionales"], 0,'','.').'.-'.$textValue.'<br/>
						<br/>';

if($arrayPersonal[$i]["ID_FINIQUITO_PERSONAL"]==212){
	$htmlBody2 .= '0.-
					</td>
					<td>&nbsp;</td>
				</tr>';
}else{
	$htmlBody2 .= number_format(($arrayPersonal[$i]["vacaciones_proporcionales"]+$arrayPersonal[$i]["liquidaciones"]+$arrayPersonal[$i]["indemnizacion_servicio"]+$arrayPersonal[$i]["indemnizacion_aviso"]+$arrayPersonal[$i]["indemnizacion_voluntaria"]+$arrayPersonal[$i]["indemnizacion_mes"])-($arrayPersonal[$i]["prestamo_empresa"]+$arrayPersonal[$i]["prestamo_caja"]+$arrayPersonal[$i]["afc"]), 0,'','.').'.-
					</td>
					<td>&nbsp;</td>
				</tr>';
}


	$htmlBody3 = '<tr>
					<td colspan="6" style="text-align: justify;">
						<p><b>TERCERO : Asimismo el empleador declara bajo juramento, no tener conocimiento de la existencia</b>de retenciones que hayan sido notificadas a la empresa para atender el pago de pensiones <b> alimenticias del trabajador(a) '.utf8_encode($arrayPersonal[$i]["nombre"]).', C.I. N° '.number_format($arrayPersonal[$i]["rut"], 0,'','.').'-'.$arrayPersonal[$i]["dv_per"].'</b>Esta declaración la formula en conformidad con lo dispuesto en el artículo 13 de la Ley 14.908 <b>modificada por Ley 21.389 de 17 de noviembre de 2021.</b></p>
					</td>
				</tr>';
	$htmlBody4 = '<tr>
					<td colspan="6" style="text-align: justify;">
						<p><b>CUARTO : Don(a) '.utf8_encode($arrayPersonal[$i]["Nombre"]).'</b>, estando en completo acuerdo con la causal de término que se aplica, deja constancia que durante todo el tiempo que le presto servicios a <b>'.utf8_encode($arrayPersonal[$i]["EmpNombre"]).'</b>, recibió de él, correcta y oportunamente el total de las remuneraciones convenidas, de acuerdo con su contrato de trabajo, clase de trabajo ejecutado, reajustes legales, pago de asignaciones familiares autorizadas por la respectiva Institución de Previsión, horas extraordinarias cuando las trabajo, feriados legales, gratificaciones y participaciones, y se pagaron oportunamente todas sus imposiciones previsionales, que en conformidad a la ley fueron procedentes y que nada se le adeuda por los conceptos antes indicados, ni por ninguno otro, sea de origen legal o contractual derivado de la prestación de sus servicios y motivo por el cual, no teniendo reclamo alguno que formular en contra de <b>'.utf8_encode($arrayPersonal[$i]["EmpNombre"]).'</b>, le otorga el más amplio y total finiquito, declaración que formula libre y espontáneamente, en perfecto y cabal conocimiento de cada uno y de todos sus derechos.</p>

						<p>Para constancia firman las partes el presente finiquito en tres ejemplares, quedando uno de ellos en poder de cada parte, y el tercero ante la Notaría.</p>
					</td>
				</tr>';

		



	$htmlFooter = '<tr>
						<td colspan="2" style="text-align: center; vertical-align: top">
							____________________________
							<br/>
								Firma Trabajador
							<br/>
								<b>'.utf8_encode($arrayPersonal[$i]["Nombre"]).'</b>
							<br/>';

	if($arrayPersonal[$i]["ID_FINIQUITO_PERSONAL"]==1700){
		$htmlFooter .= '<b>N° PASAPORTE AU917837</b>';
	}else{
		$htmlFooter .= '<b>'.number_format($arrayPersonal[$i]["rut"], 0,'','.').'-'.$arrayPersonal[$i]["dv_per"].'</b>';
	}
		$htmlFooter .= '</td>
						<td colspan="2" >
						</td>
						<td colspan="2" style="text-align: center; vertical-align: top">
							____________________________
							<br/>
								Firma Empleador
							<br/>
								<b>'.utf8_encode($arrayPersonal[$i]["EmpNombre"]).'</b>
							<br/>
								<b>'.number_format($arrayPersonal[$i]["empresa_rut"], 0,'','.').'-'.$arrayPersonal[$i]["Empdv"].'</b>
						</td>
					</tr>';


$htmlFull = '<table align="center" style="width: 90%; font-size: 14px; font-family: "Times New Roman", Georgia, Serif; table-layout: fixed;">'.$htmlHead.$htmlBody1.$htmlBody2.$htmlBody3.$htmlBody4.'<tr><td colspan="6"><br/></td></tr>'.$htmlFooter.'</table>';

if($_GET['type']=='one'){
	$htmlFull .= '<div style="page-break-before: always;"></div>';


	$htmlHeadDetail = '<tr>
							<th colspan="6" style="text-align: center; font-weight: bold; font-size: 25px;">
								<p>DETALLE DE CÁLCULOS EN FINIQUITO</p>
							<th>
						</tr>';

	$htmlBodyDetail1 = '<tr>
							<td colspan="6">Liquidación: '.$htmlDetail_Liq.'</td>
						</tr>
						<tr>
							<td colspan="6" style="border: 1px solid black; text-align: center;">Indemnización por años de servicio: '.$htmlDetail_IndSer.'</td>
						</tr>
						<tr>
							<td colspan="6" style="border: 1px solid black; text-align: center;">Indemnización por mes de aviso:
								<br/>Meses trabajados * (Sueldo Base + Gratificación+ Colación + Movilización) / 30) 
								<br/>'.$htmlDetail_IndMes.'</td>
						</tr>
						<tr>
							<td colspan="6">Indemnización por mes de aviso: '.$htmlDetail_IndAvi.'</td>
						</tr>
						<tr>
							<td colspan="6">Indemnización voluntaria: '.$htmlDetail_IndVol.'</td>
						</tr>
						<tr>
							<td colspan="6" style="border: 1px solid black; text-align: center;">Préstamo Empresa: '.$htmlDetail_PreEmp.'</td>
						</tr>
						<tr>
							<td colspan="6">Préstamo Caja: '.$htmlDetail_PreCaj.'</td>
						</tr>
						<tr>
							<td colspan="6">AFC: '.$htmlDetail_AFC.'</td>
						</tr>
						<tr>
							<td colspan="6" style="border: 1px solid black; text-align: center;">Vacaciones proporcionales: '.$htmlDetail_Vac.'</td>
						</tr>';



	$htmlFull .= '<table align="center" style="width: 90%; font-size: 14px; font-family: "Times New Roman", Georgia, Serif; table-layout: fixed;">'.$htmlHeadDetail.$htmlBodyDetail1.'<tr><td colspan="6"><br/></td></tr></table>';
}

	if($i<count($arrayPersonal)-1){
		$htmlFullAll .= $htmlFull.'<div style="page-break-before: always;"></div>';
	}else{
		$htmlFullAll .= $htmlFull;
	}
}

//echo $htmlFullAll;
//exit();

// include autoloader
include("../../libs/dompdf/autoload.inc.php");
//require_once '..\..\libs\dompdf\autoload.inc.php';

// reference the Dompdf namespace
use Dompdf\Dompdf;

// instantiate and use the dompdf class
$dompdf = new Dompdf();

$dompdf->loadHtml($htmlFullAll);

// (Optional) Setup the paper size and orientation
$dompdf->setPaper('letter','portrait');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
//$dompdf->stream();

// Output the generated PDF (1 = download and 0 = preview)
$dompdf->stream($filename, array("Attachment"=>0));


?>