<?php
header('Content-Type: text/html; charset=utf8'); 
session_start();

if(!isset($_SESSION['userId'])){
	header('Location: ../../login.php');
}elseif($_SESSION['display']['history']['view']!=''){
	header('Location: ../../index.php');
}

//include("../../connection/connection.php");

function executeSelect($query){
	//$db = getcwd() . "\\" . 'GX_DATA.mdb';
	$db = "C:\\xampp\www\Pulmodon\connection\GX_DATA.mdb";
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
	$plant = 98;	
	if(isset($_GET['plant'])){
		$plant = $_GET['plant'];
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
							p.dv_per,
							p.rut_per & '-' & p.dv_per AS rut,
							e.EmpSigla AS enterpriseInitials,
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

	$htmlHead = '<tr><th style="border: 1px solid black;">Empresa</th>
	<th style="border: 1px solid black;">Campo</th>
	<th style="border: 1px solid black;">RUT</th>
	<th style="border: 1px solid black;">Apellido Paterno</th>
	<th style="border: 1px solid black;">Apellido Materno</th>
	<th style="border: 1px solid black;">Nombres</th>
	<th style="border: 1px solid black;">Cargo</th>
	<th style="border: 1px solid black;">Estado</th>
	<th style="border: 1px solid black;">Duración</th>
	<th style="border: 1px solid black;">Inicio</th>
	<th style="border: 1px solid black;">Fin</th></tr>';

	$htmlBody1 = '';

	$totalAmount = 0;
	$totalBalance = 0;

	for($i=0;$i<count($array);$i++){
		$htmlBody1 .= '<tr>';
		$htmlBody1 .= '<td style="border: 1px solid black; text-align:center;">'.$array[$i]["enterpriseInitials"].'</td>';
		$htmlBody1 .= '<td style="border: 1px solid black; text-align:center;">'.$array[$i]["plant"].'</td>';
		$htmlBody1 .= '<td style="border: 1px solid black; text-align:right;">'.number_format($array[$i]["rut_per"], 0,'','.').'-'.$array[$i]["dv_per"].'</td>';
		$htmlBody1 .= '<td style="border: 1px solid black;">'.$array[$i]["lastname1"].'</td>';
		$htmlBody1 .= '<td style="border: 1px solid black;">'.$array[$i]["lastname2"].'</td>';
		$htmlBody1 .= '<td style="border: 1px solid black;">'.$array[$i]["name"].'</td>';
		$htmlBody1 .= '<td style="border: 1px solid black;">'.$array[$i]["charge"].'</td>';
		$htmlBody1 .= '<td style="border: 1px solid black;">'.$array[$i]["status"].'</td>';
		$htmlBody1 .= '<td style="border: 1px solid black;">'.$array[$i]["duration"].'</td>';
		if($array[$i]["contractStart"][2]=="-"){
			$contractStart = explode('-', $array[$i]["contractStart"]);
			$contractEnd = explode('-', $array[$i]["contractEnd"]);
			$htmlBody1 .= '<td style="border: 1px solid black; text-align:center;">'.$contractStart[0].'/'.$contractStart[1].'/'.$contractStart[2].'</td>';
			$htmlBody1 .= '<td style="border: 1px solid black; text-align:center;">'.$contractEnd[0].'/'.$contractEnd[1].'/'.$contractEnd[2].'</td>';
		}else{
			$htmlBody1 .= '<td style="border: 1px solid black; text-align:center;">'.$array[$i]["contractStart"].'</td>';
			$htmlBody1 .= '<td style="border: 1px solid black; text-align:center;">'.$array[$i]["contractEnd"].'</td>';
		}

		$htmlBody1 .= '</tr>';
	}


	$htmlFullAll = '<table align="center" style="font-size: 10px; font-family: arial, sans-serif; border: 1px solid black; border-collapse: collapse;">'.$htmlHead.$htmlBody1.'</table>';


}elseif($_GET['type']=='one'){
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
						WHERE ph.rut_per=".$_GET['id']."

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
						WHERE p.rut_per=".$_GET['id']);

	$htmlHead = '<tr>
				<th style="border: 1px solid black;">Registro</th>
				<th style="border: 1px solid black;">N° Ficha</th>
				<th style="border: 1px solid black;">RUT</th>
				<th style="border: 1px solid black;">Empresa</th>
				<th style="border: 1px solid black;">Campo</th>
				<th style="border: 1px solid black;">Apellido Paterno</th>
				<th style="border: 1px solid black;">Apellido Materno</th>
				<th style="border: 1px solid black;">Nombres</th>
				<th style="border: 1px solid black;">Dirección</th>
				<th style="border: 1px solid black;">Teléfono</th>
				<th style="border: 1px solid black;">Celular</th>
				<th style="border: 1px solid black;">Nacionalidad</th>
				<th style="border: 1px solid black;">Fecha Nacimiento</th>
				<th style="border: 1px solid black;">AFP</th>
				<th style="border: 1px solid black;">Salud</th>
				<th style="border: 1px solid black;">Salud UF</th>
				<th style="border: 1px solid black;">INP</th>
				<th style="border: 1px solid black;">Sueldo Base</th>
				<th style="border: 1px solid black;">Duración</th>
				<th style="border: 1px solid black;">Tipo</th>
				<th style="border: 1px solid black;">Inicio</th>
				<th style="border: 1px solid black;">Fin</th></tr>';
	
	$htmlBody1 = '';

	for($i=0;$i<count($array);$i++){
		$htmlBody1 .= '<tr>
					<td>'.$array[$i]['row'].'</td>
					<td>'.$array[$i]['sheet'].'</td>
					<td>'.$array[$i]['rut'].'</td>
					<td>'.$array[$i]['enterprise_initials'].'</td>
					<td>'.$array[$i]['plant'].'</td>
					<td>'.$array[$i]['lastname1'].'</td>
					<td>'.$array[$i]['lastname2'].'</td>
					<td>'.$array[$i]['name'].'</td>
					<td>'.$array[$i]['address'].', '.$array[$i]['city'].', Comuna de '.$array[$i]['commune'].'</td>
					<td>'.$array[$i]['phone'].'</td>
					<td>'.$array[$i]['cellphone'].'</td>
					<td>'.$array[$i]['nationality'].'</td>
					<td>'.$array[$i]['birthdate'].'</td>
					<td>'.$array[$i]['afp'].'</td>
					<td>'.$array[$i]['healthSystem'].'</td>
					<td>'.$array[$i]['healthSystemUF'].'</td>
					<td>'.$array[$i]['inp'].'</td>
					<td>'.$array[$i]['salary'].'</td>
					<td>'.$array[$i]['duration'].'</td>
					<td>'.$array[$i]['work'].'</td>
					<td>'.$array[$i]['contract_start'].'</td>
					<td>'.$array[$i]['contract_end'].'</td>
					</tr>';
	}
	$htmlFullAll = '<table align="center" style="font-size: 12px; font-family: arial, sans-serif; border-collapse: collapse;">'.$htmlHead.$htmlBody1.'</table>';
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
$dompdf->setPaper('letter','landscape');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
//$dompdf->stream();

// Output the generated PDF (1 = download and 0 = preview)
$dompdf->stream($filename, array("Attachment"=>0));


?>