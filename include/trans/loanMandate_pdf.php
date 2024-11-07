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


function getMonth($month){
	$monthString = '';

	if($month=="01") $monthString="enero";
	if($month=="02") $monthString="febrero";
	if($month=="03") $monthString="marzo";
	if($month=="04") $monthString="abril";
	if($month=="05") $monthString="mayo";
	if($month=="06") $monthString="junio";
	if($month=="07") $monthString="julio";
	if($month=="08") $monthString="agosto";
	if($month=="09") $monthString="septiembre";
	if($month=="10") $monthString="octubre";
	if($month=="11") $monthString="noviembre";
	if($month=="12") $monthString="diciembre";

	return $monthString;
}

$htmlFullAll = "";

$rut = $_GET['rut'];
$name = $_GET['name'];
$typeLoan = $_GET['typeLoan'];
$date = '';
if($_GET['date'][2]=='-'){
	$date = explode('-',$_GET['date']);
}else{
	$date = explode('/',$_GET['date']);
}

$array = executeSelect("SELECT *, FORMAT(Fecha,'mm/yyyy') AS Fecha1, MONTH(Fecha) AS MonthX, YEAR(Fecha) AS YearX FROM PRESTAMO_PAGOS WHERE ID_PRESTAMO=".$_GET['id']." ORDER BY Fecha");

$arrayMonth = executeSelect("SELECT * FROM T0058");

$totalAmount = 0;

for($i=0;$i<count($array);$i++){
	$array[$i]["status"] = "";
	if($array[$i]['Estado']=='PAGADO'){
		if($array[$i]['YearX']<=$arrayMonth[0]['ANO']){
			if($array[$i]['MonthX']<$arrayMonth[0]['Mes']){
				$array[$i]['status'] = "disabled";
			}
		}
	}
	$totalAmount += $array[$i]['Monto'];
}

$arrayLoan = executeSelect("SELECT * FROM PRESTAMO WHERE ID=".$_GET['id']);


if($_GET['period']==0){
	if(count($array)>0){

		$arrayEnterprise = executeSelect("SELECT e.*, 
											t.PlNombre AS plant, 
											FORMAT(p.fecing_per,'dd/mm/yyyy') AS contractStart,
											FORMAT(p.fecing_per,'yyyy-mm-dd') AS contractStartB,
											IIF(p.indef = 1 , 'Indef.', 'Fijo') AS contractDuration
										FROM ((PERSONAL p
										LEFT JOIN T0010 t ON t.Pl_codigo=p.planta_per)
										LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
										WHERE p.rut_per=".$arrayLoan[0]['RUT']."
										ORDER BY p.rut_per");
		$array[0]['enterpriseName']=$arrayEnterprise[0]['EmpNombre'];
		$array[0]['enterpriseRUT']=number_format($arrayEnterprise[0]['Emp_codigo'], 0,'','.').'-'.$arrayEnterprise[0]['Empdv'];
		$array[0]['plant']=$arrayEnterprise[0]['plant'];
		$array[0]['contractStart']=$arrayEnterprise[0]['contractStart'];
		$array[0]['contractStartB']=$arrayEnterprise[0]['contractStartB'];
		//$array[0]['contractStart']=$arrayEnterprise[0]['contractStart'];
		$array[0]['contractDuration']=$arrayEnterprise[0]['contractDuration'];
	}
}else{
	if(count($array)>0){
		$arrayEnterprise = executeSelect("SELECT e.*, 
											t.PlNombre AS plant,
											FORMAT(p.fecing_per,'dd/mm/yyyy') AS contractStart,
											FORMAT(p.fecing_per,'yyyy-mm-dd') AS contractStartB,
											IIF(p.indef = 1 , 'Indef.', 'Fijo') AS contractDuration
										FROM ((PERSONAL_HISTORICO p
										LEFT JOIN T0010 t ON t.Pl_codigo=p.planta_per)
										LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
										WHERE p.rut_per='".$arrayLoan[0]['RUT']."'
										ORDER BY p.rut_per");
		$array[0]['enterpriseName']=$arrayEnterprise[0]['EmpNombre'];
		$array[0]['enterpriseRUT']=number_format($arrayEnterprise[0]['Emp_codigo'], 0,'','.').'-'.$arrayEnterprise[0]['Empdv'];
		$array[0]['plant']=$arrayEnterprise[0]['plant'];
		$array[0]['contractStart']=$arrayEnterprise[0]['contractStart'];
		$array[0]['contractStartB']=$arrayEnterprise[0]['contractStartB'];
		//$array[0]['contractStart']=$arrayEnterprise[0]['contractStart'];
		$array[0]['contractDuration']=$arrayEnterprise[0]['contractDuration'];
	}
}

$rut_dv = explode('-', $rut);


$htmlHead = '<tr>
				<td colspan="10" style="text-align: center; font-weight: bold; font-size: 18px;">
					MANDATO DESCUENTO POR PLANILLA
					<br/>
					<br/>
				<td>
			</tr>
			<tr>
				<td colspan="3">
					Comparece Don(ña)
				</td>
				<td colspan="1">
					:
				</td>
				<td colspan="6">
					'.$name.'
				</td>
			</tr>
			<tr>
				<td colspan="3">
					Cédula Nacional Identidad N°
				</td>
				<td colspan="1">
					:
				</td>
				<td colspan="6">
					'.number_format($arrayLoan[0]['RUT'], 0,'','.').'-'.$rut_dv[1].'
				</td>
			</tr>
			<tr>
				<td colspan="10">
					<br/>
					<br/>
				</td>
			</tr>';

if($typeLoan=='Cuotas'){

	$textDues = '';
	$differentDue = false;
	if(count($array)==1){
		$textDues = '1 cuota por un valor de $'.number_format($array[$j]['Monto'], 0,'','.').'.-';
	}else{
		$actualValue = 0;
		$actualDue = 0;
		for($j=0;$j<count($array);$j++){

			if($j==0){
				$actualValue = $array[$j]['Monto'];
				$actualDue++;
			}else{
				if($actualValue==$array[$j]['Monto']){
					$actualDue++;
					if($j+1==count($array)){
						$textDues .= $actualDue.' cuotas por un valor de $'.number_format($array[$j]['Monto'], 0,'','.').'.- ';
					}
				}else{
					$textDues .= $actualDue.' cuotas por un valor de $'.number_format($actualValue, 0,'','.').'.- ';
					$actualValue = $array[$j]['Monto'];
					$actualDue = 1;

					if($j+1==count($array)){
						$textDues .= ' y 1 cuota por un valor de $'.number_format($array[$j]['Monto'], 0,'','.').'.- ';
					}
				}
			}
		}
	}

	$htmlBody1 = '<tr>
					<td colspan="10">
						En calidad de actual deudor de Crédito Social de <b>$'.number_format($totalAmount, 0,'','.').'.- (pesos)</b>, autorizo en forma expresa e irrevocable a mi empleador, <b>'.$array[0]['enterpriseName'].'</b>, para que descuente mensualmente de mi remuneración a contar de 01 de '.getMonth($array[0]['Fecha1'][0].$array[0]['Fecha1'][1]).' de '.$array[0]['Fecha1'][3].$array[0]['Fecha1'][4].$array[0]['Fecha1'][5].$array[0]['Fecha1'][6].' un total de '.$textDues.'
						<br/> 
						<br/> 
					</td>
				</tr>';
}else{

	$htmlBody1 = '<tr>
					<td colspan="10">
						En calidad de actual deudor de Crédito Social de <b>$'.number_format($totalAmount, 0,'','.').'.- (pesos)</b>, autorizo en forma expresa e irrevocable a mi empleador, <b>'.$array[0]['enterpriseName'].'</b>, a ejecutar descuentos de mi remuneración según acuerdo previo, en uno o varios pagos, hasta completar el monto adeudado. 
						<br/> 
						<br/> 
					</td>
				</tr>';
}

$htmlBody1 .= '<tr>
				<td colspan="10">
					En el evento que mi contrato de trabajo termine por cualquier causa o motivo, faculto irrevocablemente a mi empleador a fin de que con cargo a las remuneraciones y/o indemnizaciones o recargos que por cualquier concepto me correspondieren, descuente el monto correspondiente al valor total de la cuota del período más el saldo insoluto, pendiente de pago del crédito individualizado más arriba, y proceda a pagar el citado crédito, remesando dicha cantidad a <b>'.$array[0]['enterpriseName'].'</b>, directamente, quien imputará los valores según corresponda.
					<br/> 
					<br/> 
				</td>
			</tr>
			<tr>
				<td colspan="10">
					El simple retardo y/o mora en el pago íntegro y oportuno de todo o parte de una de las cuotas en la época pactada para ello, dará derecho a esta institución a exigir sin más trámite el pago total de la deuda o del saldo a que se halle reducida, considerándose en tal evento la obligación como de plazo vencido. 
					<br/> 
					<br/> 
				</td>
			</tr>
			<tr>
				<td colspan="10">
					La Empresa autoriza, reconoce y acepta este mandato irrevocable otorgado por el trabajador, y se obliga al fiel cumplimiento del mismo en todas sus partes. 
					<br/> 
					<br/> 
				</td>
			</tr>';


/*
$totalAmount = 0;
$countDues = 0;
$balance = 0;
$htmlBody1 .= '<tr>
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
	$htmlBody1 .= '<td style="border: 1px solid black; text-align:center;">'.$array[$i]['Estado'].'</td>';
	$htmlBody1 .= '<td></td>';

	$totalAmount += intval($array[$i]['Monto']);
	if($array[$i]['Estado']=='IMPAGO'){
		$countDues++;
		$balance += intval($array[$i]['Monto']);
	}
	$htmlBody1 .= '</tr>';
}

*/

$d1 = new DateTime($array[0]['contractStartB']);
$d2 = new DateTime($array[0]['Fecha1'][3].$array[0]['Fecha1'][4].$array[0]['Fecha1'][5].$array[0]['Fecha1'][6].'-'.$array[0]['Fecha1'][0].$array[0]['Fecha1'][1].'-02'); //Se agrega 1 día a la fecha de inicio de préstamo por temas de cálculo (ej. si inició contrato un día 1 no considera mes cumplido)




$diff = $d2->diff($d1);

$years = "";
$months = "";

if($diff->y==1){
	$years = "1 año";
}elseif($diff->y>1){
	$years = $diff->y." años";
}

if($diff->m==1){
	$months = "1 mes";
}elseif($diff->m>1){
	$months = $diff->m." meses";
}

$contractTime =	"";

if($years!="" && $months!=""){
	$contractTime = $years.", ".$months;
}else{
	$contractTime = $years.$months;
}



// Return array years and months

$htmlFooter = '<tr>
					<td colspan="10" style="vertical-align: top">

						Santiago, '.$date[0].' de '.getMonth($date[1]).' de '.$date[2].'.
						<br/>
						<br/>
						Autorizo Firma de: <b>'.$name.'</b>.
						<br/>
						Con fecha: '.$date[0].' de '.getMonth($date[1]).' de '.$date[2].'.
						<br/>
						<br/>
						Fecha de Ingreso a la Empresa: '.$array[0]['contractStart'].'
						<br/>
						Antigüedad Laboral: '.$contractTime.' (a la fecha de inicio del préstamo)
				</tr>
				<tr>
					<td colspan="3"></td>
					<td colspan="4" style="text-align: center;">
						<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
						_____________________________
						<br/>
						Firma
						<br/>
						'.$name.'
						<br/>
						'.number_format($arrayLoan[0]['RUT'], 0,'','.').'-'.$rut_dv[1].'
					</td>
					<td colspan="3"></td>
				</tr>
				';

//Tipo de Contrato: '.$array[0]['contractDuration'].'

$htmlFullAll = '<table align="center" style="font-size: 12px; font-family: arial, sans-serif; border-collapse: collapse; ">'.$htmlHead.$htmlBody1.$htmlFooter.'</table>';

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