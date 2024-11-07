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
	$db = "C:\Program Files (x86)\Personal y Remuneraciones\GX_DATA.mdb";
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

if($_GET['type']=='allDetail'){
	$sql="";

	$state = $_GET['state'];
	$enterprise = $_GET['enterprise'];
	$plant = 98;	
	if(isset($_GET['plant'])){
		$plant = $_GET['plant'];
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
		Format(p.fecing_per,'dd/mm/yyyy') AS contract_start,
		Format(p.fecter_per,'dd/mm/yyyy') AS contract_end,
		p.Causa_fin_per AS article,
		p.estado_per AS status
		
		FROM ((PERSONAL p
		LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
		LEFT JOIN T0010 pl ON pl.Pl_codigo=p.planta_per)
		WHERE p.estado_per='V' $where";
		//WHERE p.rut_per=".$_GET['id'];
	
	$array = executeSelect($sql);
	
	for($i=0;$i<count($array);$i++){

		$arrayVacation = executeSelect("SELECT 
										SUM(Dias_Habiles) AS used,
										SUM(Dias_Progresivos) AS extra,
										(SELECT TOP 1 Format(Fecha_Inicio,'dd/mm/yyyy') FROM FERIADO_PROPORCIONAL WHERE Rut=".$array[$i]["ficha_per"]." AND ID_FINIQUITO_PERSONAL=0 ORDER BY Fecha_Inicio DESC) AS last
										FROM FERIADO_PROPORCIONAL
										WHERE Rut=".$array[$i]["ficha_per"]." AND ID_FINIQUITO_PERSONAL=0");
		if($arrayVacation[0]["used"]>0){

			$array[$i]["used"]=$arrayVacation[0]['used']-$arrayVacation[0]['extra'];
			$date1Array = explode('/', $array[$i]["contract_start"]);
			if($array[$i]["status"]=='V'){
				$date2Array = explode('/', date('d/m/Y'));
			}else{
				$date2Array = explode('/', $arrayVacation[0]["last"]);
			}
			if($array[$i]["contract_start"][2]=="-"){
				$date1Array = explode('-', $array[$i]["contract_start"]);
				if($array[$i]["status"]=='V'){
					$date2Array = explode('-', date('d-m-Y'));
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

	$htmlHead = '<tr>
					<th style="border: 1px solid black;">Empresa</th>
					<th style="border: 1px solid black;">Campo</th>
					<th style="border: 1px solid black;">RUT</th>
					<th style="border: 1px solid black;">Nombre</th>
					<th style="border: 1px solid black;">Inicio Contrato</th>
					<th style="border: 1px solid black;">Utilizados</th>
					<th style="border: 1px solid black;">Pendientes</th>
				</tr>';
	$htmlBody1 = '';

	for($i=0;$i<count($array);$i++){


		$htmlBody1 .= '<tr>';
		$htmlBody1 .= '<td style="border: 1px solid black;">'.$array[$i]["enterprise_initials"].'</td>';
		$htmlBody1 .= '<td style="border: 1px solid black;">'.$array[$i]["plant"].'</td>';
		$htmlBody1 .= '<td style="border: 1px solid black;">'.$array[$i]["rut"].'</td>';
		$htmlBody1 .= '<td style="border: 1px solid black;">'.$array[$i]["fullname"].'</td>';
		if($array[$i]["contract_start"][2]=="-"){
			$contract_start = explode('-',$array[$i]["contract_start"]);
			$htmlBody1 .= '<td style="border: 1px solid black; text-align: center;">'.$contract_start[0].'/'.$contract_start[1].'/'.$contract_start[2].'</td>';
		}else{
			$htmlBody1 .= '<td style="border: 1px solid black; text-align: center;">'.$array[$i]["contract_start"].'</td>';
		}
		$htmlBody1 .= '<td style="border: 1px solid black; text-align: right;">'.$array[$i]["used"].'</td>';
		$htmlBody1 .= '<td style="border: 1px solid black; text-align: right;">'.$array[$i]["pending"].'</td>';
		$htmlBody1 .= '</tr>';
	}

	$htmlFullAll = '<table align="center" style="font-size: 10px; font-family: arial, sans-serif; border: 1px solid black; border-collapse: collapse; width: 100%;">'.$htmlHead.$htmlBody1.'</table>';

}elseif($_GET['type']=='one'){

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