<?php
header('Content-Type: text/html; charset=utf8'); 

//include("../../connection/connection.php");

session_start();

if(!isset($_SESSION['userId'])){
	header('Location: ../../login.php');
}elseif($_SESSION['display']['loan']['view']!=''){
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

$htmlFullAll = "";

if($_GET['type']=='all'){
	$state = $_GET['state'];
	$enterprise = $_GET['enterprise'];
	$loanState = $_GET['loanState'];
	$plant = 98;	
	if(isset($_GET['plant'])){
		$plant = $_GET['plant'];
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
							per.rut_per AS rut,
							per.dv_per AS dv,
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
							
							(SELECT SUM(Monto) FROM PRESTAMO_PAGOS pg WHERE pg.Estado='IMPAGO' AND pg.ID_PRESTAMO=p.ID) +
							IIF(ISNULL((SELECT SUM(Monto)-SUM(Abono) FROM PRESTAMO_PAGOS pg WHERE pg.ID_PRESTAMO=p.ID AND pg.Estado='ABONADO')),0,(SELECT SUM(Monto)-SUM(Abono) FROM PRESTAMO_PAGOS pg WHERE pg.ID_PRESTAMO=p.ID AND pg.Estado='ABONADO'))
							
							AS balance,
							(SELECT COUNT(*) FROM PRESTAMO_PAGOS pg WHERE pg.Estado IN ('IMPAGO','ABONADO') AND pg.ID_PRESTAMO=p.ID) AS balanceDues,
							(SELECT SUM(Monto) FROM PRESTAMO_ABONOS pa WHERE pa.ID_PRESTAMO=p.ID) AS payment
							FROM (((PRESTAMO p
							LEFT JOIN PERSONAL per ON per.rut_per=VAL(p.RUT))
							LEFT JOIN T0010 t ON t.Pl_codigo=per.planta_per)
							LEFT JOIN T0009 e ON e.Emp_codigo=per.emp_per)
							WHERE p.ID_FINIQUITO_PERSONAL=0 $where");

	$htmlHead = '<tr>
					<th style="border: 1px solid black;">Empresa</th>
					<th style="border: 1px solid black;">Campo</th>
					<th style="border: 1px solid black;">RUT</th>
					<th style="border: 1px solid black;">Nombre</th>
					<th style="border: 1px solid black;">Fecha Inicio</th>
					<th style="border: 1px solid black;">Fecha Fin</th>
					<th style="border: 1px solid black;">Tipo</th>
					<th style="border: 1px solid black;">Monto Total</th>
					<th style="border: 1px solid black;">N° Cuot.</th>
					<th style="border: 1px solid black;">Monto Cuota</th>
					<th style="border: 1px solid black;">Cuot. Pend.</th>
					<th style="border: 1px solid black;">Saldo Pendiente</th>
					<th>Estado</th>
				</tr>';
	$htmlBody1 = '';

	$totalAmount = 0;
	$totalBalance = 0;

	for($i=0;$i<count($array);$i++){
		$htmlBody1 .= '<tr>';
		$htmlBody1 .= '<td style="border: 1px solid black; text-align:center; font-size: 8px;">'.$array[$i]["enterpriseInitials"].'</td>';
		$htmlBody1 .= '<td style="border: 1px solid black; text-align:center; font-size: 8px;">'.$array[$i]["plant"].'</td>';
		$htmlBody1 .= '<td style="border: 1px solid black; text-align:right;">'.number_format($array[$i]["rut"], 0,'','.').'-'.$array[$i]["dv"].'</td>';
		$htmlBody1 .= '<td style="border: 1px solid black;">'.$array[$i]["fullname"].'</td>';
		if($array[$i]["startDate"][2]=="-"){
			$startDate = explode('-', $array[$i]["startDate"]);
			$endDate = explode('-', $array[$i]["endDate"]);
			$htmlBody1 .= '<td style="border: 1px solid black; text-align:center;">'.$startDate[0].'/'.$startDate[1].'/'.$startDate[2].'</td>';
			$htmlBody1 .= '<td style="border: 1px solid black; text-align:center;">'.$endDate[0].'/'.$endDate[1].'/'.$endDate[2].'</td>';
		}else{
			$htmlBody1 .= '<td style="border: 1px solid black; text-align:center;">'.$array[$i]["startDate"].'</td>';
			$htmlBody1 .= '<td style="border: 1px solid black; text-align:center;">'.$array[$i]["endDate"].'</td>';
		}

		if($array[$i]["typeLoan"]=='CUOTAS_AUTO'){
			$htmlBody1 .= '<td style="border: 1px solid black; text-align:center;">Cuotas</td>';
		}else{
			$htmlBody1 .= '<td style="border: 1px solid black; text-align:center;">A Cuenta</td>';
		}
		$htmlBody1 .= '<td style="border: 1px solid black; text-align:right;">'.number_format($array[$i]["amountTotal"], 0,'','.').'</td>';
		$htmlBody1 .= '<td style="border: 1px solid black; text-align:center;">'.$array[$i]["duesNumber"].'</td>';
		$htmlBody1 .= '<td style="border: 1px solid black; text-align:right;">'.number_format($array[$i]["amountDue"], 0,'','.').'</td>';
		$htmlBody1 .= '<td style="border: 1px solid black; text-align:center;">'.$array[$i]["balanceDues"].'</td>';
		if($array[$i]["typeLoan"]=='CUOTAS_AUTO'){
			$htmlBody1 .= '<td style="border: 1px solid black; text-align:right;">'.number_format($array[$i]["balance"], 0,'','.').'</td>';
		}else{
			if($array[$i]["balance"]==0){
				$htmlBody1 .= '<td style="border: 1px solid black; text-align:right;">'.number_format($array[$i]["balance"], 0,'','.').'</td>';
			}else{
				$payment = 0;
				if($array[$i]['payment']!=null){
					$array[$i]['balance'] = $array[$i]['balance']-$array[$i]['payment'];
				}
				$htmlBody1 .= '<td style="border: 1px solid black; text-align:right;">'.number_format($array[$i]["balance"], 0,'','.').'</td>';
			}
		}
		if($array[$i]["balance"]==0){
			$htmlBody1 .= '<td style="border: 1px solid black; text-align:right;">Pagado</td>';
		}else{
			$htmlBody1 .= '<td style="border: 1px solid black; text-align:right;">Pendiente</td>';
		}
		$htmlBody1 .= '</tr>';
		$totalAmount += $array[$i]["amountTotal"];
		$totalBalance += $array[$i]["balance"];
	}

	$htmlBody2 = '<tr><td colspan="13">&nbsp;</td></tr>';
	$htmlBody2 .= '<tr>';
	$htmlBody2 .= '<th colspan="5"></th>';
	$htmlBody2 .= '<th colspan="2">Total Préstamos</th>';
	$htmlBody2 .= '<th style="border: 1px solid black; text-align:right;">'.number_format($totalAmount, 0,'','.').'</th>';
	$htmlBody2 .= '<th></th>';
	$htmlBody2 .= '<th colspan="2">Saldo Total</th>';
	$htmlBody2 .= '<th style="border: 1px solid black; text-align:right;">'.number_format($totalBalance, 0,'','.').'</th>';
	$htmlBody2 .= '<th></th>';
	$htmlBody2 .= '</tr>';


	$htmlFullAll = '<table align="center" style="font-size: 9px; font-family: arial, sans-serif; border: 1px solid black; border-collapse: collapse;">'.$htmlHead.$htmlBody1.$htmlBody2.'</table>';


}elseif($_GET['type']=='one'){
	$rut = $_GET['rut'];
	$name = $_GET['name'];
	$typeLoan = $_GET['typeLoan'];

	$array = executeSelect("SELECT *, FORMAT(Fecha,'mm/yyyy') AS Fecha1, MONTH(Fecha) AS MonthX, YEAR(Fecha) AS YearX FROM PRESTAMO_PAGOS WHERE ID_PRESTAMO=".$_GET['id']." ORDER BY Fecha");
	
	$arrayMonth = executeSelect("SELECT * FROM T0058");

	for($i=0;$i<count($array);$i++){
		$array[$i]["status"] = "";
		if($array[$i]['Estado']=='PAGADO'){
			if($array[$i]['YearX']<=$arrayMonth[0]['ANO']){
				if($array[$i]['MonthX']<$arrayMonth[0]['Mes']){
					$array[$i]['status'] = "disabled";
				}
			}
		}
	}

	$arrayLoan = executeSelect("SELECT * FROM PRESTAMO WHERE ID=".$_GET['id']);


	if($_GET['period']==0){
		if(count($array)>0){
			$arrayEnterprise = executeSelect("SELECT e.*, t.PlNombre AS plant
											FROM ((PERSONAL p
											LEFT JOIN T0010 t ON t.Pl_codigo=p.planta_per)
											LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
											WHERE p.rut_per=".$arrayLoan[0]['RUT']."
											ORDER BY p.rut_per");
			$array[0]['enterpriseName']=$arrayEnterprise[0]['EmpNombre'];
			$array[0]['enterpriseRUT']=number_format($arrayEnterprise[0]['Emp_codigo'], 0,'','.').'-'.$arrayEnterprise[0]['Empdv'];
			$array[0]['plant']=$arrayEnterprise[0]['plant'];
		}
	}else{
		if(count($array)>0){
			$arrayEnterprise = executeSelect("SELECT e.*, t.PlNombre AS plant
											FROM ((PERSONAL_HISTORICO p
											LEFT JOIN T0010 t ON t.Pl_codigo=p.planta_per)
											LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
											WHERE p.rut_per='".$arrayLoan[0]['RUT']."'
											ORDER BY p.rut_per");
			$array[0]['enterpriseName']=$arrayEnterprise[0]['EmpNombre'];
			$array[0]['enterpriseRUT']=number_format($arrayEnterprise[0]['Emp_codigo'], 0,'','.').'-'.$arrayEnterprise[0]['Empdv'];
			$array[0]['plant']=$arrayEnterprise[0]['plant'];
		}
	}

	$htmlHead = '';
	$htmlHead .= '<tr>';
	$htmlHead .='<th></th>';
	$htmlHead .='<th colspan="3">Empresa</th>';
	$htmlHead .='<th></th>';
	$htmlHead .='<th>RUT</th>';
	$htmlHead .='<th></th>';
	$htmlHead .='</tr>';

	$htmlHead .= '<tr>';
	$htmlHead .='<td></td>';
	$htmlHead .='<td colspan="3">'.$array[0]['enterpriseName'].'</td>';
	$htmlHead .='<td></td>';
	$htmlHead .='<td style="text-align: center;">'.$array[0]['enterpriseRUT'].'</td>';
	$htmlHead .='<td></td>';
	$htmlHead .='</tr>';
	$htmlHead .='<tr><td colspan="7">&nbsp;</td></tr>';

	$htmlHead .= '<tr>';
	$htmlHead .='<th>Campo</th>';
	$htmlHead .='<th>RUT</th>';
	$htmlHead .='<th colspan="3">Nombre</th>';
	$htmlHead .='<th>Fecha de Hoy</th>';
	$htmlHead .='<th></th>';
	$htmlHead .='</tr>';

	$rut_dv = explode('-', $rut);

	$htmlHead .= '<tr>';
	$htmlHead .='<td>'.$array[0]['plant'].'</td>';
	$htmlHead .='<td style="text-align:center;">'.number_format($arrayLoan[0]['RUT'], 0,'','.').'-'.$rut_dv[1].'</td>';
	$htmlHead .='<td style="text-align:center;" colspan="3">'.$name.'</td>';
	$htmlHead .='<td style="text-align:center;">'.date('d/m/Y').'</td>';
	$htmlHead .='<td></td>';
	$htmlHead .='</tr>';
	$htmlHead .='<tr><td colspan="7">&nbsp;</td></tr>';

	$totalAmount = 0;
	$countDues = 0;
	$balance = 0;
	$htmlBody1 = '<tr>
					<td colspan="7">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="7" style="text-align: center;">Cuotas</td>
				</tr>
				<tr>
					<th></th>
					<th style="border: 1px solid black;">Número</th>
					<th style="border: 1px solid black;">Tipo</th>
					<th style="border: 1px solid black;">Fecha</th>
					<th style="border: 1px solid black;">Monto</th>
					<th style="border: 1px solid black;">Estado</th>
					<th></th>
				</tr>'; 
	$start = "";
	$end = "";
	for($i=0;$i<count($array);$i++){
		$htmlBody1 .= '<tr>';
		$htmlBody1 .= '<td></td>';
		$htmlBody1 .= '<td style="border: 1px solid black; text-align:center;">'.$array[$i]['Numero'].'</td>';
		$htmlBody1 .= '<td style="border: 1px solid black; text-align:center;">'.$array[$i]['Tipo'].'</td>';

		if($array[$i]['Fecha1'][2]=='/'){
			$htmlBody1 .= '<td style="border: 1px solid black; text-align:center;">'.$array[$i]['Fecha1'].'</td>';
			if($i==0){
				$start = $array[$i]['Fecha1'];
			}
			if($i+1==count($array)){
				$end = $array[$i]['Fecha1'];
			}
		}else{
			$htmlBody1 .= '<td style="border: 1px solid black; text-align:right;">'.str_replace('-','/',$array[$i]['Fecha1']).'</td>';
			if($i==0){
				$start = str_replace('-','/',$array[$i]['Fecha1']);
			}
			if($i+1==count($array)){
				$end = str_replace('-','/',$array[$i]['Fecha1']);
			}
		}


		$htmlBody1 .= '<td style="border: 1px solid black; text-align:right;">'.number_format($array[$i]['Monto'], 0,'','.').'</td>';
		if($array[$i]['Estado']=='ABONADO'){
			$htmlBody1 .= '<td style="border: 1px solid black; text-align:center;">'.$array[$i]['Estado'].'
						('.number_format($array[$i]['Abono'], 0,'','.').')
						</td>';
		}else{
			$htmlBody1 .= '<td style="border: 1px solid black; text-align:center;">'.$array[$i]['Estado'].'</td>';
		}
		$htmlBody1 .= '<td></td>';

		$totalAmount += intval($array[$i]['Monto']);
		if($array[$i]['Estado']=='IMPAGO'){
			$countDues++;
			$balance += intval($array[$i]['Monto']);
		}elseif($array[$i]['Estado']=='ABONADO'){
			$countDues++;
			$balance += intval($array[$i]['Monto'])-intval($array[$i]['Abono']);
		}
		$htmlBody1 .= '</tr>';
	}


	$htmlBody2 = '';
	$arrayPayment = executeSelect("SELECT *, FORMAT(Fecha,'dd/mm/yyyy') AS Fecha1 FROM PRESTAMO_ABONOS WHERE ID_PRESTAMO=".$_GET['id']);
	$payment = 0;
	if(count($arrayPayment)>0){
		$htmlBody2 = '<tr>
						<td colspan="7">&nbsp;</td>
					</tr>
					<tr>
						<td colspan="7" style="text-align: center;">Abonos</td>
					</tr>
					<tr>
						<th colspan="2"></th>
						<th style="border: 1px solid black;">Número</th>
						<th style="border: 1px solid black;">Fecha</th>
						<th style="border: 1px solid black;">Monto</th>
						<th colspan="2"></th>
					</tr>';

		for($p=0;$p<count($arrayPayment);$p++){
			$htmlBody2 .= '<tr>
							<td colspan="2"></td>
							<td style="border: 1px solid black; text-align: center;">'.$arrayPayment[$p]['Numero'].'</td>
							<td style="border: 1px solid black; text-align: center;">'.$arrayPayment[$p]['Fecha1'].'</td>
							<td style="border: 1px solid black; text-align: right;">'.number_format($arrayPayment[$p]['Monto'], 0,'','.').'</td>
							<td></td>
						</tr>';
			$payment += $arrayPayment[$p]['Monto'];
		}
	}

	if($array[0]['Estado']=='IMPAGO'){
		$balance -= $payment;
	}



	$htmlHead .= '<tr>';
	$htmlHead .= '<th style="border: 1px solid black;">Tipo Préstamo</th>';
	$htmlHead .= '<th style="border: 1px solid black;">Inicio</th>';
	$htmlHead .= '<th style="border: 1px solid black;">Fin</th>';
	$htmlHead .= '<th style="border: 1px solid black;">N° Cuotas</th>';
	$htmlHead .= '<th style="border: 1px solid black;">Monto Total</th>';
	$htmlHead .= '<th style="border: 1px solid black;">Cuotas Pendientes</th>';
	$htmlHead .= '<th style="border: 1px solid black;">Saldo Pendiente</th>';
	$htmlHead .= '</tr>';

	$htmlHead .= '<tr>';
	$htmlHead .= '<td style="border: 1px solid black; text-align:center;">'.$typeLoan.'</td>';
	$htmlHead .= '<td style="border: 1px solid black; text-align:center;">'.$start.'</td>';
	$htmlHead .= '<td style="border: 1px solid black; text-align:center;">'.$end.'</td>';
	$htmlHead .= '<td style="border: 1px solid black; text-align:center;">'.count($array).'</td>';
	$htmlHead .= '<td style="border: 1px solid black; text-align:center;">'.number_format($totalAmount, 0,'','.').'</td>';
	$htmlHead .= '<td style="border: 1px solid black; text-align:center;">'.$countDues.'</td>';
	$htmlHead .= '<td style="border: 1px solid black; text-align:center;">'.number_format($balance, 0,'','.').'</td>';
	$htmlHead .= '</tr>';
	$htmlHead .='<tr><td colspan="7"></td></tr>';

	$htmlFooter = '<tr>
						<td colspan="7" style="text-align: center; vertical-align: top">
							<br/><br/><br/><br/><br/><br/><br/><br/>
							____________________________
							<br/>
								Recibí Conforme
					</tr>';

	$htmlFullAll = '<table align="center" style="font-size: 12px; font-family: arial, sans-serif; border-collapse: collapse;">'.$htmlHead.$htmlBody1.$htmlBody2.$htmlFooter.'</table>';
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