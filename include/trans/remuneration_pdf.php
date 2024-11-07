<?php
date_default_timezone_set('America/Santiago');
header('Content-Type: text/html; charset=utf8'); 
ini_set('max_execution_time', 300);
//include("../../connection/connection.php");

session_start();
if(!isset($_SESSION['userId'])){
	header('Location: ../../login.php');
}elseif($_SESSION['display']['remuneration']['view']!=''){
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

	if($month=="01") $monthString="Enero";
	if($month=="02") $monthString="Febrero";
	if($month=="03") $monthString="Marzo";
	if($month=="04") $monthString="Abril";
	if($month=="05") $monthString="Mayo";
	if($month=="06") $monthString="Junio";
	if($month=="07") $monthString="Julio";
	if($month=="08") $monthString="Agosto";
	if($month=="09") $monthString="Septiembre";
	if($month=="10") $monthString="Octubre";
	if($month=="11") $monthString="Noviembre";
	if($month=="12") $monthString="Diciembre";

	return $monthString;
}


class EnLetras 
{ 
	  var $Void = ""; 
	  var $SP = " "; 
	  var $Dot = "."; 
	  var $Zero = "0"; 
	  var $Neg = "Menos"; 
	   
	function ValorEnLetras($x, $Moneda )  
	{ 
	    $s=""; 
	    $Ent=""; 
	    $Frc=""; 
	    $Signo=""; 
	         
	    if(floatVal($x) < 0) 
	     $Signo = $this->Neg . " "; 
	    else 
	     $Signo = ""; 
	     
	    if(intval(number_format($x,2,'.','') )!=$x) //<- averiguar si tiene decimales 
	      $s = number_format($x,2,'.',''); 
	    else 
	      $s = number_format($x,2,'.',''); 
	        
	    $Pto = strpos($s, $this->Dot); 
	         
	    if ($Pto === false) 
	    { 
	      $Ent = $s; 
	      $Frc = $this->Void; 
	    } 
	    else 
	    { 
	      $Ent = substr($s, 0, $Pto ); 
	      $Frc =  substr($s, $Pto+1); 
	    } 

	    if($Ent == $this->Zero || $Ent == $this->Void) 
	       $s = "Cero "; 
	    elseif( strlen($Ent) > 7) 
	    { 
	       $s = $this->SubValLetra(intval( substr($Ent, 0,  strlen($Ent) - 6))) .  
	             "Millones " . $this->SubValLetra(intval(substr($Ent,-6, 6))); 
	    } 
	    else 
	    { 
	      $s = $this->SubValLetra(intval($Ent)); 
	    } 

	    if (substr($s,-9, 9) == "Millones " || substr($s,-7, 7) == "Mill&oacute;n ") 
	       $s = $s . "de "; 

        /*if($Moneda[0]=='D'){
        	$Moneda='D&oacute;lares';
        }*/
	    $s = $s . $Moneda; 

	    /*if($Frc != $this->Void) 
	    { 
	       $s = $s . " " . $Frc. "/100"; 
	       //$s = $s . " " . $Frc . "/100"; 
	    } */
	    //$letrass=$Signo . $s . " M.N."; 
	    //return ($Signo . $s . " M.N."); 
	    return ($s); 
	    
	} 


	function SubValLetra($numero)  
	{ 
	    $Ptr=""; 
	    $n=0; 
	    $i=0; 
	    $x =""; 
	    $Rtn =""; 
	    $Tem =""; 

	    $x = trim("$numero"); 
	    $n = strlen($x); 

	    $Tem = $this->Void; 
	    $i = $n; 
	     
	    while( $i > 0) 
	    { 
	       $Tem = $this->Parte(intval(substr($x, $n - $i, 1).  
	                           str_repeat($this->Zero, $i - 1 ))); 
	       If( $Tem != "Cero" ) 
	          $Rtn .= $Tem . $this->SP; 
	       $i = $i - 1; 
	    } 

	     
	    //--------------------- GoSub FiltroMil ------------------------------ 
	    $Rtn=str_replace(" Mil Mil", " Un Mil", $Rtn ); 
	    while(1) 
	    { 
	       $Ptr = strpos($Rtn, "Mil ");        
	       If(!($Ptr===false)) 
	       { 
	          If(! (strpos($Rtn, "Mil ",$Ptr + 1) === false )) 
	            $this->ReplaceStringFrom($Rtn, "Mil ", "", $Ptr); 
	          Else 
	           break; 
	       } 
	       else break; 
	    } 

	    //--------------------- GoSub FiltroCiento ------------------------------ 
	    $Ptr = -1; 
	    do{ 
	       $Ptr = strpos($Rtn, "Cien ", $Ptr+1); 
	       if(!($Ptr===false)) 
	       { 
	          $Tem = substr($Rtn, $Ptr + 5 ,1); 
	          if( $Tem == "M" || $Tem == $this->Void) 
	             ; 
	          else           
	             $this->ReplaceStringFrom($Rtn, "Cien", "Ciento", $Ptr); 
	       } 
	    }while(!($Ptr === false)); 

	    //--------------------- FiltroEspeciales ------------------------------ 
	    $Rtn=str_replace("Diez Un", "Once", $Rtn ); 
	    $Rtn=str_replace("Diez Dos", "Doce", $Rtn ); 
	    $Rtn=str_replace("Diez Tres", "Trece", $Rtn ); 
	    $Rtn=str_replace("Diez Cuatro", "Catorce", $Rtn ); 
	    $Rtn=str_replace("Diez Cinco", "Quince", $Rtn ); 
	    $Rtn=str_replace("Diez Seis", "Dieciseis", $Rtn ); 
	    $Rtn=str_replace("Diez Siete", "Diecisiete", $Rtn ); 
	    $Rtn=str_replace("Diez Ocho", "Dieciocho", $Rtn ); 
	    $Rtn=str_replace("Diez Nueve", "Diecinueve", $Rtn ); 
	    $Rtn=str_replace("Veinte Un", "Veintiun", $Rtn ); 
	    $Rtn=str_replace("Veinte Dos", "Veintidos", $Rtn ); 
	    $Rtn=str_replace("Veinte Tres", "Veintitres", $Rtn ); 
	    $Rtn=str_replace("Veinte Cuatro", "Veinticuatro", $Rtn ); 
	    $Rtn=str_replace("Veinte Cinco", "Veinticinco", $Rtn ); 
	    $Rtn=str_replace("Veinte Seis", "Veintiseis", $Rtn ); 
	    $Rtn=str_replace("Veinte Siete", "Veintisiete", $Rtn ); 
	    $Rtn=str_replace("Veinte Ocho", "Veintiocho", $Rtn ); 
	    $Rtn=str_replace("Veinte Nueve", "Veintinueve", $Rtn ); 

	    //--------------------- FiltroUn ------------------------------ 
	    If(substr($Rtn,0,1) == "M") $Rtn = "Un " . $Rtn; 
	    //--------------------- Adicionar Y ------------------------------ 
	    for($i=65; $i<=88; $i++) 
	    { 
	      If($i != 77) 
	         $Rtn=str_replace("a " . Chr($i), "* y " . Chr($i), $Rtn); 
	    } 
	    $Rtn=str_replace("*", "a" , $Rtn); 
	    return($Rtn); 
	} 


	function ReplaceStringFrom(&$x, $OldWrd, $NewWrd, $Ptr) 
	{ 
	  $x = substr($x, 0, $Ptr)  . $NewWrd . substr($x, strlen($OldWrd) + $Ptr); 
	} 


	function Parte($x) 
	{ 
	    $Rtn=''; 
	    $t=''; 
	    $i=''; 
	    Do 
	    { 
	      switch($x) 
	      { 
	         Case 0:  $t = "Cero";break; 
	         Case 1:  $t = "Un";break; 
	         Case 2:  $t = "Dos";break; 
	         Case 3:  $t = "Tres";break; 
	         Case 4:  $t = "Cuatro";break; 
	         Case 5:  $t = "Cinco";break; 
	         Case 6:  $t = "Seis";break; 
	         Case 7:  $t = "Siete";break; 
	         Case 8:  $t = "Ocho";break; 
	         Case 9:  $t = "Nueve";break; 
	         Case 10: $t = "Diez";break; 
	         Case 20: $t = "Veinte";break; 
	         Case 30: $t = "Treinta";break; 
	         Case 40: $t = "Cuarenta";break; 
	         Case 50: $t = "Cincuenta";break; 
	         Case 60: $t = "Sesenta";break; 
	         Case 70: $t = "Setenta";break; 
	         Case 80: $t = "Ochenta";break; 
	         Case 90: $t = "Noventa";break; 
	         Case 100: $t = "Cien";break; 
	         Case 200: $t = "Doscientos";break; 
	         Case 300: $t = "Trescientos";break; 
	         Case 400: $t = "Cuatrocientos";break; 
	         Case 500: $t = "Quinientos";break; 
	         Case 600: $t = "Seiscientos";break; 
	         Case 700: $t = "Setecientos";break; 
	         Case 800: $t = "Ochocientos";break; 
	         Case 900: $t = "Novecientos";break; 
	         Case 1000: $t = "Mil";break; 
	         Case 1000000: $t = "Mill&oacute;n";break; 
	      } 

	      If($t == $this->Void) 
	      { 
	        $i = $i + 1; 
	        $x = $x / 1000; 
	        If($x== 0) $i = 0; 
	      } 
	      else 
	         break; 
	            
	    }while($i != 0); 
	    
	    $Rtn = $t; 
	    Switch($i) 
	    { 
	       Case 0: $t = $this->Void;break; 
	       Case 1: $t = " Mil";break; 
	       Case 2: $t = " Millones";break; 
	       Case 3: $t = " Billones";break; 
	    } 
	    return($Rtn . $t); 
	} 

} 

$V=new EnLetras();


$where = "";


$filename = 0; //Para los casos en que se requiera (documentos por persona)
$arrayPersonal = 0;

$personalTable = "PERSONAL";
$personalTableDetail = "0 AS ID_FINIQUITO_PERSONAL";
$whereRUT = '';
$whereRUT2 = '';
$whereSettlement = 'AND (r.ID_FINIQUITO_PERSONAL=0 OR r.ID_FINIQUITO_PERSONAL IS NULL)';
$whereSettlement2 = 'AND (r2.ID_FINIQUITO_PERSONAL=0 OR r2.ID_FINIQUITO_PERSONAL IS NULL)';

if($_GET['type']=='all'){
	$year = $_GET['year'];
	$month = $_GET['month'];
	$monthString = getMonth($month);
	$arrayRUTS = executeSelect("SELECT rut, cc1rem FROM REM02_TEMPORAL");
	for($r=0;$r<count($arrayRUTS);$r++){
		if($r==0){
			$whereRUT = "AND VAL(r.rutrem) IN (".$arrayRUTS[$r]['rut'];
			$whereRUT2 = "AND VAL(r.cc1rem) IN (".$arrayRUTS[$r]['cc1rem'];
		}else{
			$whereRUT .= ",".$arrayRUTS[$r]['rut'];
			$whereRUT2 .= ",".$arrayRUTS[$r]['cc1rem'];
		}
	}
	$whereRUT .= ")";
	$whereRUT2 .= ")";


	$settlement = $_GET['settlement'];
	$whereSettlement = " AND p.ID_FINIQUITO_PERSONAL=r.ID_FINIQUITO_PERSONAL";
	$whereSettlement2 = " AND r2.ID_FINIQUITO_PERSONAL=r.ID_FINIQUITO_PERSONAL";
	$personalTable = "PERSONAL_HISTORICO";
	$personalTableDetail = "p.ID_FINIQUITO_PERSONAL";

	if($settlement==0){
		$whereSettlement = " AND (r.ID_FINIQUITO_PERSONAL=0 OR r.ID_FINIQUITO_PERSONAL IS NULL)";
		$whereSettlement2 = " AND (r2.ID_FINIQUITO_PERSONAL=0 OR r2.ID_FINIQUITO_PERSONAL IS NULL)";
		$personalTable = "PERSONAL";
		$personalTableDetail = "0 AS ID_FINIQUITO_PERSONAL";
	}


	$where = "WHERE r.aaaarem=$year AND r.mmrem=$month
			$whereRUT $whereRUT2 $whereSettlement";

}elseif($_GET['type']=='one'){
	$year = $_GET['year'];
	$month = $_GET['month'];
	$monthString = getMonth($month);
	
	$rut = $_GET['rut'];
	$costCenter = $_GET['costCenter'];
	$settlement = $_GET['settlement'];

	$whereSettlement = " AND p.ID_FINIQUITO_PERSONAL=".$settlement;
	$whereSettlement2 = " AND r2.ID_FINIQUITO_PERSONAL=".$settlement;
	$personalTable = "PERSONAL_HISTORICO";
	$personalTableDetail = "p.ID_FINIQUITO_PERSONAL";

	if($settlement==0){
		$whereSettlement = " AND (r.ID_FINIQUITO_PERSONAL=0 OR r.ID_FINIQUITO_PERSONAL IS NULL)";
		$whereSettlement2 = " AND (r2.ID_FINIQUITO_PERSONAL=0 OR r2.ID_FINIQUITO_PERSONAL IS NULL)";
		$personalTable = "PERSONAL";
		$personalTableDetail = "0 AS ID_FINIQUITO_PERSONAL";
	}

	$whereRUT = "AND VAL(r.rutrem)=$rut";
	$whereRUT2 = "AND VAL(r.cc1rem)=$costCenter";
	$where = "WHERE r.aaaarem=$year AND r.mmrem=$month
			$whereRUT $whereRUT2 $whereSettlement";

}elseif($_GET['type']=='historic'){ //Por trabajador y por rango de tiempo
	$rut = $_GET['rut'];
	$year1 = $_GET['year1'];
	$month1 = $_GET['month1'];
	$year2 = $_GET['year2'];
	$month2 = $_GET['month2'];

	if($year1==$year2){
		$where = "AND r.aaaarem=".$year1." AND r.mmrem BETWEEN ".$month1." AND ".$month2;
	}else{
		$where = "AND (";

		for($i=$month1;$i<=12;$i++){
			if($where!="AND ("){
				$where .= " OR ";
			}
			$where .= "(r.aaaarem=".$year1." AND r.mmrem=".$i.")";
		}

		for($j=1;$j<=$month2;$j++){
			$where .= " OR (r.aaaarem=".$year2." AND r.mmrem=".$j.")";
		}
		
		if($year1+1<$year2){
			$onlyYears = "";
			for($k=$year1+1;$k<$year2;$k++){
				if($onlyYears!=""){
					$onlyYears.=",";
				}
				$onlyYears.=$k;
			}	
			$where.= " OR r.aaaarem IN (".$onlyYears.")";
		}
		$where .= ")";
		
	}


	$where = "WHERE VAL(r.rutrem)=".$rut." ".$where;
}


$sql = "SELECT 
		e.EmpNombre AS enterprise,
		e.Emp_codigo AS enterprise_rut,
		e.Empdv AS enterprise_dv,
		t.PlNombre AS plant,
		r.cc1rem AS plant_id,
		VAL(r.rutrem) AS rut,
		p.dv_per AS rut_dv,
		p.Nom_per & ' ' & p.Apepat_per & ' ' & p.Apemat_per AS fullname,
		a.des_afp AS AFP,
		i.nom_isa AS health_system,
		(SELECT r2.valrem2 FROM REM021 r2 WHERE codhdrem='P002'
		AND r2.aaaarem=r.aaaarem AND r2.mmrem=r.mmrem AND r2.cc1rem=r.cc1rem AND r2.rutrem=r.rutrem $whereSettlement2)+
		(SELECT r2.valrem2 FROM REM021 r2 WHERE codhdrem='P150'
		AND r2.aaaarem=r.aaaarem AND r2.mmrem=r.mmrem AND r2.cc1rem=r.cc1rem AND r2.rutrem=r.rutrem $whereSettlement2) AS days_total,
		
		(SELECT r2.valrem2 FROM REM021 r2 WHERE codhdrem='H035'
		AND r2.aaaarem=r.aaaarem AND r2.mmrem=r.mmrem AND r2.cc1rem=r.cc1rem AND r2.rutrem=r.rutrem $whereSettlement2) AS total_h,
		(SELECT r2.valrem2 FROM REM021 r2 WHERE codhdrem='D045'
		AND r2.aaaarem=r.aaaarem AND r2.mmrem=r.mmrem AND r2.cc1rem=r.cc1rem AND r2.rutrem=r.rutrem $whereSettlement2) AS total_d,
		(SELECT r2.valrem2 FROM REM021 r2 WHERE codhdrem='H045'
		AND r2.aaaarem=r.aaaarem AND r2.mmrem=r.mmrem AND r2.cc1rem=r.cc1rem AND r2.rutrem=r.rutrem $whereSettlement2) AS total_rem,
		(SELECT r2.valrem2 FROM REM021 r2 WHERE codhdrem='D026'
		AND r2.aaaarem=r.aaaarem AND r2.mmrem=r.mmrem AND r2.cc1rem=r.cc1rem AND r2.rutrem=r.rutrem $whereSettlement2) AS total_advance,
		(SELECT r2.valrem2 FROM REM021 r2 WHERE codhdrem='H046'
		AND r2.aaaarem=r.aaaarem AND r2.mmrem=r.mmrem AND r2.cc1rem=r.cc1rem AND r2.rutrem=r.rutrem $whereSettlement2) AS total_topay,

		r.aaaarem,
		r.mmrem,
		$personalTableDetail

		FROM (((((REM02 r
		LEFT JOIN T0010 t ON t.Pl_codigo=VAL(r.cc1rem))
		LEFT JOIN $personalTable p ON p.rut_per=VAL(r.rutrem))
		LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
		LEFT JOIN AFP a ON a.cod_afp=p.afp_per)
		LEFT JOIN ISAPRES i ON i.cod_isa=p.isa_per)
		$where
		ORDER BY r.rutrem";

$arrayPersonal = executeSelect($sql);

$htmlFullAll = '';

for($i=0;$i<count($arrayPersonal);$i++){

	$daysTotal = number_format($arrayPersonal[$i]['days_total'],2,',','.');

	$htmlHead = '<tr>
					<td colspan="6" style="font-weight: bold;">'.$arrayPersonal[$i]['enterprise'].'</td>
					<td colspan="4"></td>
					<td colspan="1">Fecha</td>
					<td colspan="1">'.date('d/m/Y').'</td>
				</tr>
				<tr>
					<td colspan="6" style=" font-weight: bold;">RUT &nbsp;'.number_format($arrayPersonal[$i]["enterprise_rut"],0,'','.').'-'.$arrayPersonal[$i]["enterprise_dv"].'</td>
					<td colspan="4"></td>
					<td colspan="1">Hora</td>
					<td colspan="1">'.date('H:i:s').'</td>
				</tr>
				<tr>
					<td colspan="12" style=" text-align: center; font-weight: bold;">
					<br/>
					</td>
				</tr>
				<tr>
					<td colspan="12" style=" text-align: center; font-weight: bold; font-size: 25px;">
						LIQUIDACIÓN DE REMUNERACIONES
					</td>
				</tr>
				<tr>
					<td colspan="12" style=" text-align: center; font-weight: bold; font-size: 17px;">
						'.getMonth($arrayPersonal[$i]['mmrem']).' '.$arrayPersonal[$i]['aaaarem'].'
					</td>
				</tr>
				<tr>
					<td colspan="12" style=" text-align: center; font-weight: bold;">
						_______________________________________________________________________________________________________________________
					</td>
				</tr>
				<tr>
					<td colspan="1" style=" font-weight: bold;">RUT</td>
					<td colspan="5">&nbsp;&nbsp;'.number_format($arrayPersonal[$i]["rut"],0,'','.').'-'.$arrayPersonal[$i]["rut_dv"].'</td>
					<td colspan="3" style=" font-weight: bold;">CENTRO DE COSTO</td>
					<td colspan="3">'.$arrayPersonal[$i]['plant_id'].' - '.$arrayPersonal[$i]['plant'].'</td>
				</tr>
				<tr>
					<td colspan="1" style=" font-weight: bold;">NOMBRE</td>
					<td colspan="5">&nbsp;&nbsp;'.utf8_encode($arrayPersonal[$i]['fullname']).'</td>
					<td colspan="3" style=" font-weight: bold;">TOTAL DÍAS TRABAJADOS</td>
					<td colspan="3">'.$daysTotal.'</td>
				</tr>
				<tr>
					<td colspan="1" style=" font-weight: bold;">AFP</td>
					<td colspan="5">&nbsp;&nbsp;'.utf8_encode($arrayPersonal[$i]['AFP']).'</td>
					<td colspan="6"></td>
				</tr>
				<tr>
					<td colspan="1" style=" font-weight: bold;">SALUD</td>
					<td colspan="5">&nbsp;&nbsp;'.utf8_encode($arrayPersonal[$i]['health_system']).'</td>
					<td colspan="6"></td>
				</tr>
				<tr>
					<td colspan="12" style=" text-align: center; font-weight: bold;">
						<br/>
					</td>
				</tr>';

	$htmlBody1 = '<tr>
					<td colspan="6" style=" text-align: center; font-weight: bold; font-size: 17px; border: 1px solid black;">
						HABERES
					</td>
					<td colspan="6" style=" text-align: center; font-weight: bold; font-size: 17px; border: 1px solid black;">
						DESCUENTOS
					</td>
				</tr>';

	$whereSettlementDetail = " AND r.ID_FINIQUITO_PERSONAL=".$arrayPersonal[$i]['ID_FINIQUITO_PERSONAL'];
	if($arrayPersonal[$i]['ID_FINIQUITO_PERSONAL']==0){
		$whereSettlementDetail = " AND (r.ID_FINIQUITO_PERSONAL=0 OR r.ID_FINIQUITO_PERSONAL IS NULL)";
	}

	$arrayDetailH = executeSelect("SELECT *
							FROM REM021 r
							WHERE aaaarem=".$arrayPersonal[$i]['aaaarem']." 
							AND mmrem=".$arrayPersonal[$i]['mmrem']."
							AND cc1rem='".$arrayPersonal[$i]['plant_id']."'
							AND VAL(rutrem)=".intval($arrayPersonal[$i]['rut'])."
							AND codhdrem LIKE 'H%'
							AND liqrem<>0 AND (valrem<>0 OR valrem2<>0)
							$whereSettlementDetail
							ORDER BY liqrem");

	if(intval($arrayPersonal[$i]['plant_id'])==9){
		$valremH002 = 0;
		$valrem2H002 = 0;
		$indexH002 = 0;
		
		$valremH150 = 0;
		$valrem2H150 = 0;
		$indexH150 = 0;
		for($h=0;$h<count($arrayDetailH);$h++){
			if($arrayDetailH[$h]['codhdrem']=='H002'){
				$valremH002 = $arrayDetailH[$h]['valrem'];
				$valrem2H002 = $arrayDetailH[$h]['valrem2'];
				$indexH002 = $h;
			}elseif($arrayDetailH[$h]['codhdrem']=='H150'){
				$valremH150 = $arrayDetailH[$h]['valrem'];
				$valrem2H150 = $arrayDetailH[$h]['valrem2'];
				$indexH150 = $h;
			}
		}
		$arrayDetailH[$indexH002]['valrem'] = $valremH002 + $valremH150;
		$arrayDetailH[$indexH002]['valrem2'] = $valrem2H002 + $valrem2H150;
		unset($arrayDetailH[$indexH150]);
		$arrayDetailH = array_values($arrayDetailH); 
	}

	$arrayDetailD = executeSelect("SELECT *
							FROM REM021 r
							WHERE aaaarem=".$arrayPersonal[$i]['aaaarem']." 
							AND mmrem=".$arrayPersonal[$i]['mmrem']."
							AND cc1rem='".$arrayPersonal[$i]['plant_id']."'
							AND VAL(rutrem)=".intval($arrayPersonal[$i]['rut'])."
							AND codhdrem LIKE 'D%'
							AND liqrem<>0 AND (valrem<>0 OR valrem2<>0)
							$whereSettlementDetail
							ORDER BY liqrem");

	$detailLength = count($arrayDetailH);
	if(count($arrayDetailD)>count($arrayDetailH)){
		$detailLength = count($arrayDetailD);
	}

	for($j=0;$j<$detailLength;$j++){
		$Hvalrem = '';
		$Hdescriphdrem = '';
		$Hvalrem2 = '';
		$Hstyle1 = '';
		$Hstyle2 = '';
		if($j<count($arrayDetailH)){
			$Hvalrem = number_format($arrayDetailH[$j]['valrem'],2,',','.');
			$Hdescriphdrem = utf8_encode($arrayDetailH[$j]['descriphdrem']);
			$Hvalrem2 = number_format($arrayDetailH[$j]['valrem2'],0,'','.');
			if($arrayDetailH[$j]['descriphdrem'][0]==' '){
				$Hvalrem = '';
				$Hstyle1 = 'font-weight: bold;';
				$Hstyle2 = 'style="text-align: right; font-weight: bold;"';
			}
		}
		
		$Dvalrem = '';
		$Ddescriphdrem = '';
		$Dvalrem2 = '';
		$Dstyle1 = '';
		$Dstyle2 = '';
		if($j<count($arrayDetailD)){
			$Dvalrem = number_format($arrayDetailD[$j]['valrem'],2,',','.');
			$Ddescriphdrem = utf8_encode($arrayDetailD[$j]['descriphdrem']);
			$Dvalrem2 = number_format($arrayDetailD[$j]['valrem2'],0,'','.');
			if($arrayDetailD[$j]['descriphdrem'][0]==' '){
				$Dvalrem = '';
				$Dstyle1 = 'font-weight: bold;';
				$Dstyle2 = 'style="text-align: right; font-weight: bold;"';
			}
		}

		$htmlBody1 .= '<tr>
					<td colspan="1" style="text-align: center; '.$Hstyle1.'">
						'.$Hvalrem.'
					</td>
					<td colspan="4" '.$Hstyle2.'>
						'.$Hdescriphdrem.'
					</td>
					<td colspan="1" style="text-align: right; '.$Hstyle1.'">
						'.$Hvalrem2.'
					</td>

					<td colspan="1" style="text-align: center; '.$Dstyle1.'">
						'.$Dvalrem.'
					</td>
					<td colspan="4" '.$Dstyle2.'>
						'.$Ddescriphdrem.'
					</td>
					<td colspan="1" style="text-align: right; '.$Dstyle1.'">
						'.$Dvalrem2.'
					</td>

				</tr>';
	}

	if($detailLength<25){
		for($s=$detailLength;$s<25;$s++){
			$htmlBody1 .= '<tr>
					<td colspan="12" style=" text-align: center; font-weight: bold;">
						<br/>
					</td>
				</tr>';
		}
	}


	$htmlBody1 .= '<tr>
					<td colspan="12" style=" text-align: center; font-weight: bold;">
						<br/>
					</td>
				</tr>
				<tr>
					<td colspan="1" style="text-align: center; border-left: 1px solid black; border-top: 1px solid black; border-bottom: 1px solid black;">
					</td>
					<td colspan="4" style="text-align: center; font-size: 16px; font-weight: bold; border-top: 1px solid black; border-bottom: 1px solid black;">
						TOTAL HABERES
					</td>
					<td colspan="1" style="text-align: right; font-size: 16px; font-weight: bold; border-top: 1px solid black; border-bottom: 1px solid black;">
						'.number_format($arrayPersonal[$i]['total_h'],0,'','.').'
					</td>

					<td colspan="1" style="text-align: center; border-top: 1px solid black; border-bottom: 1px solid black;">
					</td>
					<td colspan="4" style="text-align: center; font-size: 16px; font-weight: bold; border-top: 1px solid black; border-bottom: 1px solid black;">
						TOTAL DESCUENTOS
					</td>
					<td colspan="1" style="text-align: right; font-size: 16px; font-weight: bold; border-right: 1px solid black; border-top: 1px solid black; border-bottom: 1px solid black;">
						'.number_format($arrayPersonal[$i]['total_d'],0,'','.').'
					</td>
				</tr>
				
				<tr>
					<td colspan="6">
					</td>
					<td colspan="2" style="text-align: center;">
					</td>
					<td colspan="3" style="font-size: 16px; font-weight: bold; border-left: 1px solid black; border-top: 1px solid black;">
						ALCANCE LÍQUIDO
					</td>
					<td colspan="1" style="text-align: right; font-size: 16px; font-weight: bold; border-right: 1px solid black; border-top: 1px solid black;">
						'.number_format($arrayPersonal[$i]['total_rem'],0,'','.').'
					</td>
				</tr>
				<tr>
					<td colspan="6">
					</td>
					<td colspan="2" style="text-align: center;">
					</td>
					<td colspan="3" style="font-size: 16px; font-weight: bold; border-left: 1px solid black;">
						ANTICIPO
					</td>
					<td colspan="1" style="text-align: right; font-size: 16px; font-weight: bold; border-right: 1px solid black;">
						'.number_format($arrayPersonal[$i]['total_advance'],0,'','.').'
					</td>
				</tr>
				<tr>
					<td colspan="6">
					</td>
					<td colspan="2" style="text-align: center;">
					</td>
					<td colspan="3" style="font-size: 16px; font-weight: bold; border-left: 1px solid black; border-bottom: 1px solid black;">
						LÍQUIDO A PAGO
					</td>
					<td colspan="1" style="text-align: right; font-size: 16px; font-weight: bold; border-right: 1px solid black; border-bottom: 1px solid black;">
						'.number_format($arrayPersonal[$i]['total_topay'],0,'','.').'
					</td>
				</tr>
				';

	$htmlFooter = '<tr>
					<td colspan="12">
						<b>SON:</b> '.strtoupper($V->ValorEnLetras(intval($arrayPersonal[$i]['total_topay']),'')).'
					</td>
				</tr>
				<tr>
					<td colspan="12" style=" text-align: center; font-weight: bold;">
						<br/>
					</td>
				</tr>
				<tr>
					<td colspan="12" style=" text-align: center; font-weight: bold;">
						<br/>
					</td>
				</tr>
				<tr>
					<td colspan="8">
					</td>
					<td colspan="4" style="text-align: center; font-weight: bold;">
						___________________________________________<br/>
						Firma Trabajador
					</td>
				</tr>';

	//$htmlFull = '<table align="center" style="width: 100%; font-size: 12px; font-family: \'Times New Roman\', Georgia, Serif; table-layout: fixed;">
	$htmlFull = '<table align="center" style="width: 100%; font-size: 13px; font-family: Arial; table-layout: fixed; border-collapse: collapse;">
				'.$htmlHead.$htmlBody1.'
				<tr><td colspan="12"><br/></td></tr>
				'.$htmlFooter.'
				</table>';

	if($i<count($arrayPersonal)-1){
		$htmlFullAll .= $htmlFull.'<div style="page-break-before: always;"></div>';
	}else{
		$htmlFullAll .= $htmlFull;
	}
}

//echo $htmlFullAll;
//exit();

/*if($_GET['type']=='historic'){
	echo '<style>@page {
	  size: portrait;
	}</style>
	<body onafterprint="window.close();">';
	echo $htmlFullAll;
	//exit();

	echo '<script type="text/javascript"> window.print();</script>';
}else{*/

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
//}

?>