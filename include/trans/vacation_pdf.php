<?php
header('Content-Type: text/html; charset=utf8'); 

//include("../../connection/connection.php");
session_start();

if(!isset($_SESSION['userId'])){
	header('Location: ../../login.php');
}elseif($_SESSION['display']['vacations']['view']!=''){
	header('Location: ../../index.php');
}


function executeSelect($query){
	//$db = getcwd() . "\\" . 'GX_DATA.mdb';
	//$db = "C:\\xampp\www\Pulmodon\connection\GX_DATA.mdb";
	$db = "C:\\Program Files (x86)\Personal y Remuneraciones\GX_DATA.mdb";

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

function getFullDate($date){
	$todayDay = ""; 
	$todayMonth = "";
	if(date('w', strtotime($date))==1) $todayDay="lunes";
	if(date('w', strtotime($date))==2) $todayDay="martes";
	if(date('w', strtotime($date))==3) $todayDay="miércoles";
	if(date('w', strtotime($date))==4) $todayDay="jueves";
	if(date('w', strtotime($date))==5) $todayDay="viernes";
	if(date('w', strtotime($date))==6) $todayDay="sábado";
	if(date('w', strtotime($date))==0) $todayDay="domingo";

	if(date('m', strtotime($date))==1) $todayMonth="enero";
	if(date('m', strtotime($date))==2) $todayMonth="febrero";
	if(date('m', strtotime($date))==3) $todayMonth="marzo";
	if(date('m', strtotime($date))==4) $todayMonth="abril";
	if(date('m', strtotime($date))==5) $todayMonth="mayo";
	if(date('m', strtotime($date))==6) $todayMonth="junio";
	if(date('m', strtotime($date))==7) $todayMonth="julio";
	if(date('m', strtotime($date))==8) $todayMonth="agosto";
	if(date('m', strtotime($date))==9) $todayMonth="septiembre";
	if(date('m', strtotime($date))==10) $todayMonth="octubre";
	if(date('m', strtotime($date))==11) $todayMonth="noviembre";
	if(date('m', strtotime($date))==12) $todayMonth="diciembre";

	return $todayDay.", ".date('d',strtotime($date))." de ".$todayMonth." de ".date('Y',strtotime($date));
}

$id = $_GET['id'];
$arrayVacation = executeSelect("SELECT 
							fp.ID,
							Format(fp.Fecha_Inicio,'dd/mm/yyyy') AS FechaInicio,
							Format(fp.Fecha_Inicio,'mm/dd/yyyy') AS FechaInicio_Retard,
							Format(fp.Fecha_Fin,'dd/mm/yyyy') AS FechaFin,
							Format(fp.Fecha_Reintegracion,'dd/mm/yyyy') AS FechaReintegracion,
							fp.Dias_Habiles,
							fp.Dias_Inhabiles,
							fp.Dias_Progresivos,
							fp.Periodo_Inicio,
							fp.Periodo_Fin,
							fp.ID_FINIQUITO_PERSONAL,
							fp.Rut
							FROM FERIADO_PROPORCIONAL fp WHERE fp.ID=$id");

$sql = '';
$arrayAllVacation = '';
$lastVacationBusiness = '-';
$lastVacationNoBusiness = '-';
$lastVacationTotal = '-';
$lastVacationPeriod1 = '-';
$lastVacationPeriod2 = '-';
$daysUsed = 0;
$daysProgressive = 0;

if($arrayVacation[0]['ID_FINIQUITO_PERSONAL']==NULL || $arrayVacation[0]['ID_FINIQUITO_PERSONAL']==0){

	$arrayAllVacation = executeSelect("SELECT 
						fp.ID,
						Format(fp.Fecha_Inicio,'dd/mm/yyyy') AS FechaInicio,
						Format(fp.Fecha_Fin,'dd/mm/yyyy') AS FechaFin,
						Format(fp.Fecha_Reintegracion,'dd/mm/yyyy') AS FechaReintegracion,
						fp.Dias_Habiles,
						fp.Dias_Inhabiles,
						fp.Dias_Progresivos,
						fp.Periodo_Inicio,
						fp.Periodo_Fin,
						fp.ID_FINIQUITO_PERSONAL,
						fp.Rut
						FROM FERIADO_PROPORCIONAL fp WHERE fp.Rut=".$arrayVacation[0]['Rut']." AND (fp.ID_FINIQUITO_PERSONAL IS NULL OR fp.ID_FINIQUITO_PERSONAL=0) AND fp.Fecha_Inicio<=#".$arrayVacation[0]['FechaInicio_Retard']."# ORDER BY Fecha_Inicio DESC");
	
	for($j=0;$j<count($arrayAllVacation);$j++){
		$daysUsed += $arrayAllVacation[$j]['Dias_Habiles'];
		$daysProgressive += $arrayAllVacation[$j]['Dias_Progresivos'];
		if($j==1){
			$lastVacationBusiness = $arrayAllVacation[$j]['Dias_Habiles'];
			$lastVacationNoBusiness = $arrayAllVacation[$j]['Dias_Inhabiles'];
			$lastVacationTotal = $lastVacationBusiness+$lastVacationNoBusiness;
			$lastVacationPeriod1 = $arrayAllVacation[$j]['Periodo_Inicio'];
			$lastVacationPeriod2 = $arrayAllVacation[$j]['Periodo_Fin'];
		}
	}

	$sql = "SELECT 
			'Actual' AS row,
			STR(p.ficha_per) AS sheet,
			p.dv_per AS dv,
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
			Format(p.fecing_per,'dd/mm/yyyy') AS contract_start,
			Format(p.fecter_per,'dd/mm/yyyy') AS contract_end,
			p.Causa_fin_per AS article,
			p.estado_per AS status
			
			FROM ((PERSONAL p
			LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
			LEFT JOIN T0010 pl ON pl.Pl_codigo=p.planta_per)
			WHERE p.rut_per=".$arrayVacation[0]['Rut'];

}else{
	$arrayAllVacation = executeSelect("SELECT 
						fp.ID,
						Format(fp.Fecha_Inicio,'dd/mm/yyyy') AS FechaInicio,
						Format(fp.Fecha_Fin,'dd/mm/yyyy') AS FechaFin,
						Format(fp.Fecha_Reintegracion,'dd/mm/yyyy') AS FechaReintegracion,
						fp.Dias_Habiles,
						fp.Dias_Inhabiles,
						fp.Dias_Progresivos,
						fp.Periodo_Inicio,
						fp.Periodo_Fin,
						fp.ID_FINIQUITO_PERSONAL,
						fp.Rut
						FROM FERIADO_PROPORCIONAL fp WHERE ID_FINIQUITO_PERSONAL=".$arrayVacation[0]['ID_FINIQUITO_PERSONAL']." AND  fp.Fecha_Inicio<=#".$arrayVacation[0]['FechaInicio_Retard']."# ORDER BY Fecha_Inicio DESC");

	for($j=0;$j<count($arrayAllVacation);$j++){
		$daysUsed += $arrayAllVacation[$j]['Dias_Habiles'];
		$daysProgressive += $arrayAllVacation[$j]['Dias_Progresivos'];
		if($j==1){
			$lastVacationBusiness = $arrayAllVacation[$j]['Dias_Habiles'];
			$lastVacationNoBusiness = $arrayAllVacation[$j]['Dias_Inhabiles'];
			$lastVacationTotal = $lastVacationBusiness+$lastVacationNoBusiness;
			$lastVacationPeriod1 = $arrayAllVacation[$j]['Periodo_Inicio'];
			$lastVacationPeriod2 = $arrayAllVacation[$j]['Periodo_Fin'];
		}
	}

	$sql = "SELECT 
			'Histórico' AS row,
			STR(ph.ficha_per) AS sheet,
			ph.dv_per AS dv,
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
			Format(ph.fecing_per,'dd/mm/yyyy') AS contract_start,
			Format(ph.fecter_per,'dd/mm/yyyy') AS contract_end,
			ph.Causa_fin_per AS article,
			ph.estado_per AS status

			FROM ((PERSONAL_HISTORICO ph
			LEFT JOIN T0009 e ON e.Emp_codigo=ph.emp_per)
			LEFT JOIN T0010 pl ON pl.Pl_codigo=ph.planta_per)
			WHERE ph.ID_FINIQUITO_PERSONAL=".$arrayVacation[0]['ID_FINIQUITO_PERSONAL'];

}

$arrayPersonal = executeSelect($sql);

if($arrayPersonal[0]["contract_start"][2]=='-'){
	$date1Array = explode('-', $arrayPersonal[0]["contract_start"]);
	$date2Array = explode('-', $arrayVacation[0]["FechaInicio"]);
	$dateReArray = explode('-', $arrayVacation[0]["FechaReintegracion"]);
	$dateEndArray = explode('-', $arrayVacation[0]["FechaFin"]);
}else{
	$date1Array = explode('/', $arrayPersonal[0]["contract_start"]);
	$date2Array = explode('/', $arrayVacation[0]["FechaInicio"]);
	$dateReArray = explode('/', $arrayVacation[0]["FechaReintegracion"]);
	$dateEndArray = explode('/', $arrayVacation[0]["FechaFin"]);
}
$date1 = new DateTime($date1Array[2].'-'.$date1Array[1].'-'.$date1Array[0]);
$date2 = new DateTime($date2Array[2].'-'.$date2Array[1].'-'.$date2Array[0]);
$interval = $date1->diff($date2);

$years = "";
$months = "";
$days = "";
if($interval->y>0){
	if($interval->y==1){
		$years = "1 AÑO";
	}else{
		$years = $interval->y." AÑOS";
	}
}

if($interval->m>0){
	if($interval->m==1){
		$months = "1 MES";
	}else{
		$months = $interval->m." MESES";
	}
	if($years!=""){
		$months = ", ".$months;
	}
}


$days = (strtotime($date1Array[2].'-'.$date1Array[1].'-'.$date1Array[0])-strtotime($date2Array[2].'-'.$date2Array[1].'-'.$date2Array[0]))/86400;
$days = abs($days); 
$daysTotal = floor($days);	

//$daysProgressive = $arrayVacation[0]["Dias_Progresivos"];

//Se realiza cálculo exclusivo para los días pendientes, tomando la fecha de contrato-la fecha de inicio del contrato
$daysFromEnd = (strtotime($date1Array[2].'-'.$date1Array[1].'-'.$date1Array[0])-strtotime($dateEndArray[2].'-'.$dateEndArray[1].'-'.$dateEndArray[0]))/86400;
$daysFromEnd = abs($daysFromEnd); 
$daysFromEndTotal = floor($daysFromEnd);


//$daysPending = round(((($daysTotal+1)/365)*12*1.25)-$daysUsed,2);
//$daysPending = round(((15/12/30)*($daysTotal+1))-$daysUsed,2)+$daysProgressive;
$daysPending = round(((15/12/30)*($daysFromEndTotal+1))-$daysUsed,2)+$daysProgressive;

$serviceTime = $years.$months;

$periodMessage = '';
if($arrayVacation[0]["Periodo_Inicio"]!=$arrayVacation[0]["Periodo_Fin"]){
	$periodMessage = 'Período que corresponde a las vacaciones del año <b>'.$arrayVacation[0]["Periodo_Inicio"].'</b> al año <b>'.$arrayVacation[0]["Periodo_Fin"].'</b>';
}else{
	$periodMessage = 'Período que corresponde a las vacaciones del año <b>'.$arrayVacation[0]["Periodo_Inicio"].'</b>';
}

$lastPeriodMessage = '';
if($lastVacationPeriod1!='-'){
	if($lastVacationPeriod1!=$lastVacationPeriod2){
		$lastPeriodMessage = 'Período que corresponde a las vacaciones del año <b>'.$lastVacationPeriod1.'</b> al año <b>'.$lastVacationPeriod2.'</b>';
	}else{
		$lastPeriodMessage = 'Período que corresponde a las vacaciones del año <b>'.$lastVacationPeriod1.'</b>';
	}
}

$htmlFullAll = '';

$htmlHead = '
			<tr>
				<th colspan="4">
					<img src="../../images/logo.png" style="width 100%"/>
				</th>
				<th colspan="6"></th>
				<th colspan="2" style="text-align: right; font-weight: normal">Código Interno Papeleta: '.$arrayVacation[0]["ID"].'</th>
			</tr>
			<tr>
				<th colspan="12" style="text-align: center; font-weight: bold; font-size: 14px;">
					<p>'.utf8_encode($arrayPersonal[0]["enterprise"]).'</p>
				</th>
			</tr>
			<tr>
				<th colspan="12" style="text-align: center; font-weight: bold; font-size: 14px;">
					<p>PAPELETA DE VACACIONES</p>
				</th>
			</tr>
			<tr>
				<th colspan="6"></th>
				<th colspan="6" style="text-align: right; font-size: 11px;">Santiago, '.getFullDate(date('Y-m-d')).'</th>
			</tr>
			<tr>
				<th colspan="12">
					<br/>
				</th>
			</tr>';

$htmlBody1 = '	<tr>
					<td colspan="4">RUT:</td>
					<td colspan="8" style="font-weight: bold;">'.number_format($arrayPersonal[0]["sheet"], 0,'','.').'-'.$arrayPersonal[0]["dv"].'</td>
				</tr>
				<tr>
					<td colspan="4">Nombre del Empleado:</td>
					<td colspan="8" style="font-weight: bold;">'.utf8_encode($arrayPersonal[0]["fullname"]).'</td>
				</tr>
				<tr>
					<td colspan="4">Años de Servicio:</td>
					<td colspan="8" style="font-weight: bold;">'.$serviceTime.'</td>
				</tr>
				<tr>
					<td colspan="4">Fecha de Ingreso:</td>
					<td colspan="8" style="font-weight: bold;">'.getFullDate($date1Array[2].'-'.$date1Array[1].'-'.$date1Array[0]).'</td>
				</tr>			
				<tr>
					<th colspan="12">
						<br/>
					</th>
				</tr>';

$htmlBody2 = '<tr>
				<td colspan="12" style="border: 1px solid black;">
					<table align="center" style="width: 100%; font-size: 13px; font-family: arial, sans-serif; table-layout: fixed;">
						<tr>
							<td colspan="2"></td>
							<td colspan="6" style="text-align: center; font-weight: bold;">Detalle de vacaciones</td>
							<td colspan="4"></td>
						</tr>
						<tr>
							<th colspan="2"></th>
							<th colspan="2">Hábiles</th>
							<th colspan="2">Inhábiles</th>
							<th colspan="2">Total</th>
							<th colspan="4"></th>
						</tr>
						<tr style="text-align: center;">
							<td colspan="2"></td>
							<td colspan="2" style="border: 1px solid black;">'.$arrayVacation[0]["Dias_Habiles"].'</td>
							<td colspan="2" style="border: 1px solid black;">'.$arrayVacation[0]["Dias_Inhabiles"].'</td>
							<td colspan="2" style="border: 1px solid black;">'.($arrayVacation[0]["Dias_Habiles"]+$arrayVacation[0]["Dias_Inhabiles"]).'</td>
							<td colspan="4"></td>
						</tr>
						<tr>
							<td colspan="9">'.$periodMessage.'</td>
							<td colspan="3"></td>
						</tr>
						<tr>
							<th colspan="12">
								<br/>
							</th>
						</tr>
						
						<tr>
							<td colspan="3" style="font-weight: bold;">Inicio de Vacaciones</td>
							<td colspan="6" style="text-align: center;">Desde el <b>'.$arrayVacation[0]["FechaInicio"].'</b> hasta el <b>'.$arrayVacation[0]["FechaFin"].'</b></td>
							<td colspan="3"></td>
						</tr>
						
						<tr>
							<td colspan="3" style="font-weight: bold;">Fecha Reintegración</td>
							<td colspan="6" style="text-align: center;">'.getFullDate($dateReArray[2].'-'.$dateReArray[1].'-'.$dateReArray[0]).'</td>
							<td colspan="3"></td>
						</tr>
						<tr>
							<td colspan="3" style="font-weight: bold;">Días Progresivos Utilizados</td>
							<td colspan="6" style="text-align: center;">'.$arrayVacation[0]["Dias_Progresivos"].'</td>
							<td colspan="3"></td>
						</tr>
						<tr>
							<th colspan="12">
								<br/>
							</th>
						</tr>
						<tr>
							<td colspan="5" style="font-weight: bold;">Vacaciones pendientes por tomar</td>
							<td colspan="5" style="font-weight: bold;">'.$daysPending.'&nbsp;días hábiles</td>
							<td colspan="2"></td>
						</tr>
					</table>
				</td>
			</tr>

			';

$htmlBody3 = '
			<tr>
				<th colspan="12">
					<br/>
				</th>
			</tr>
			<tr>
				<td colspan="2"></td>
				<td colspan="6" style="text-align: center; font-weight: bold;">Resumen vacaciones anteriores</td>
				<td colspan="4"></td>
			</tr>
			<tr>
				<th colspan="2"></th>
				<th colspan="2">Hábiles</th>
				<th colspan="2">Inhábiles</th>
				<th colspan="2">Total</th>
				<th colspan="4"></th>
			</tr>
			<tr style="text-align: center;">
				<td colspan="2"></td>
				<td colspan="2" style="border: 1px solid black;">'.$lastVacationBusiness.'</td>
				<td colspan="2" style="border: 1px solid black;">'.$lastVacationNoBusiness.'</td>
				<td colspan="2" style="border: 1px solid black;">'.$lastVacationTotal.'</td>
				<td colspan="4"></td>
			</tr>
			<tr>
				<td colspan="9">'.$lastPeriodMessage.'</td>
				<td colspan="3"></td>
			</tr>
			<tr>
				<th colspan="12">
					<br/>
					<p>Por el presente expreso mi conformidad de solicitar y gozar mis vacaciones, en conformidad a lo dispuesto en el Título I del capítulo VII del feriado anual y de los permisos legales</p>
				</th>
			</tr>';

$htmlFooter = '<tr>
				<th colspan="12">
					<br/>
					<br/>
					<br/>
				</th>
			</tr>
			<tr>
				<td colspan="4" style="text-align: center; vertical-align: top;">
					____________________________
					<br/>
					Firma de conformidad<br/>del Empleado
				</td>
				<td colspan="4" style="text-align: center; vertical-align: top;">
					____________________________
					<br/>
						Firma de Autorización<br/>del Empleador
				</td>
				<td colspan="4" style="text-align: center; vertical-align: top;">
					____________________________
					<br/>
						Vo. Bo. Recursos Humanos
				</td>
			</tr>';


$htmlFullAll = '<table align="center" style="width: 90%; font-size: 13px; font-family: arial, sans-serif; table-layout: fixed;">'.$htmlHead.$htmlBody1.$htmlBody2.$htmlBody3.'<tr><td colspan="6"><br/></td></tr>'.$htmlFooter.'</table>';

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