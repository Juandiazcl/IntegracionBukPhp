<?php
header('Content-Type: text/html; charset=UTF-8'); 

include("../connection/connection.php");

$type=$_POST['type'];

if($type=='save'){
	$RUT=$_POST['RUT'];
	$Tipo=$_POST['Tipo'];
	$Fecha_Inicio=$_POST['Fecha_Inicio'];
	$Fecha_Fin=$_POST['Fecha_Fin'];
	$Cuotas=$_POST['Cuotas'];
	$Valor_Total=$_POST['Valor_Total'];
	$unify=$_POST['unify'];

	executeSql("INSERT INTO PRESTAMO(ID_FINIQUITO_PERSONAL,RUT,Tipo,Estado,Fecha_Inicio,Fecha_Fin,Cuotas_Originales,Cuotas_Totales,Valor_Total) VALUES(0, '$RUT', '$Tipo', 'IMPAGO', '$Fecha_Inicio', '$Fecha_Fin', $Cuotas, $Cuotas, $Valor_Total)");


	$array = executeSelect("SELECT MAX(ID) AS lastID FROM PRESTAMO");
	saveDues($array[0]['lastID']);

	if($unify==true){
		$listUnify=$_POST['listUnify'];
		$unifyArray = explode("&&",$listUnify);

		for($i=0;$i<count($unifyArray)-1;$i++){
			$arrayTotalDues = executeSelect("SELECT COUNT(*) AS Contador, 
											IIF(COUNT(*)>0,SUM(Monto),0) AS Pagado, 
											IIF(COUNT(*)>0,MAX(Fecha),0) AS FechaFin
											FROM PRESTAMO_PAGOS WHERE ID_PRESTAMO=".$unifyArray[$i]." AND Estado='PAGADO'");
			/*if($arrayTotalDues[0]['Contador']>0){
				executeSql("UPDATE PRESTAMO SET Estado='PAGADO', Cuotas_Totales=".$arrayTotalDues[0]['Contador'].", Valor_Total=".$arrayTotalDues[0]['Pagado'].", Fecha_Fin='".$arrayTotalDues[0]['FechaFin']."' WHERE ID=".$unifyArray[$i]);
			}else{
				executeSql("UPDATE PRESTAMO SET Estado='PAGADO', Cuotas_Totales=".$arrayTotalDues[0]['Contador'].", Valor_Total=0 WHERE ID=".$unifyArray[$i]);
			}*/
			executeSql("UPDATE PRESTAMO SET Estado='UNIFICADO' WHERE ID=".$unifyArray[$i]);

			//executeSql("DELETE FROM PRESTAMO_PAGOS WHERE ID_PRESTAMO=".$unifyArray[$i]." AND Estado='IMPAGO'");
			executeSql("UPDATE PRESTAMO_PAGOS SET Estado='UNIFICADO' WHERE ID_PRESTAMO=".$unifyArray[$i]." AND Estado='IMPAGO'");
		}
	}

}elseif($type=='update'){
	$id=$_POST['id'];	
	$RUT=$_POST['RUT'];
	$Tipo=$_POST['Tipo'];
	$Fecha_Inicio=$_POST['Fecha_Inicio'];
	$Fecha_Fin=$_POST['Fecha_Fin'];
	$Cuotas=$_POST['Cuotas'];
	$Valor_Total=$_POST['Valor_Total'];

	executeSql("UPDATE PRESTAMO SET 
				Tipo='$Tipo', 
				Fecha_Inicio='$Fecha_Inicio', 
				Fecha_Fin='$Fecha_Fin', 
				Cuotas_Totales=$Cuotas,
				Valor_Total=$Valor_Total WHERE ID=$id");
	saveDues($id);

}elseif($type=='update_due'){

	$id=$_POST['id'];
	$Estado=$_POST['Estado'];
	executeSql("UPDATE PRESTAMO_PAGOS SET 
				Estado='$Estado'
				WHERE ID=$id");
	
	$arrayDue = executeSelect("SELECT ID_PRESTAMO, MONTH(Fecha) AS MonthX, YEAR(Fecha) AS YearX, Monto FROM PRESTAMO_PAGOS WHERE ID=$id");
	$array = executeSelect("SELECT * FROM PRESTAMO_PAGOS WHERE ID_PRESTAMO=".$arrayDue[0]['ID_PRESTAMO']);
	$state = 'IMPAGO';

	if($Estado=='PAGADO'){
		$state = 'PAGADO';
		for($i=0;$i<count($array);$i++){
			if($array[$i]['Estado']=='IMPAGO'){
				$state = 'IMPAGO';
			}
		}
		
		$arrayLoan = executeSelect("SELECT * FROM PRESTAMO WHERE ID=".$arrayDue[0]['ID_PRESTAMO']);
		$arrayMonth = executeSelect("SELECT * FROM T0058");
		if($arrayDue[0]['MonthX']==$arrayMonth[0]['Mes'] && $arrayDue[0]['YearX']==$arrayMonth[0]['ANO']){
			$rut = $arrayLoan[0]['RUT'];
			if(strlen($rut)==7) $rut="   ".$rut;
			if(strlen($rut)==8) $rut="  ".$rut;
			if(strlen($rut)==9) $rut=" ".$rut;

			$arrayAllDues = executeSelect("SELECT pp.* 
											FROM PRESTAMO_PAGOS pp
											LEFT JOIN PRESTAMO p ON p.ID=pp.ID_PRESTAMO
											WHERE p.RUT='".$arrayLoan[0]['RUT']."' AND MONTH(pp.Fecha)=".$arrayDue[0]['MonthX']." AND YEAR(pp.Fecha)=".$arrayDue[0]['YearX']." AND pp.Estado='PAGADO'");
			$amount=0;
			for($j=0;$j<count($arrayAllDues);$j++){
				$amount+=$arrayAllDues[$j]['Monto'];
			}
			updateSettlement($rut, $amount);
		}
	}

	executeSql("UPDATE PRESTAMO SET Estado='$state' WHERE ID=".$arrayDue[0]['ID_PRESTAMO']);

	echo $id.'-'.$state;

}elseif($type=='update_due_add'){

	$id=$_POST['id'];
	$amount=$_POST['amount'];
	$array = executeSelect("SELECT * FROM PRESTAMO_PAGOS WHERE ID=$id");
	//if($amount>=$array[0]['Monto']){
	if($amount>$array[0]['Monto']){
		echo 'Mayor';
	}else{
		$state = "ABONADO";
		if($amount==0){
			$state = "IMPAGO";
		}
		executeSql("UPDATE PRESTAMO_PAGOS 
					SET Estado='$state',
					Abono=$amount
					WHERE ID=$id");

		echo 'OK';
	}



}elseif($type=='payAllDues'){

	executeSql("UPDATE HYDF SET valremf2=0 WHERE codhdf='D110'");


	$month=$_POST['month'];
	$year=$_POST['year'];
	$retardDate = $month.'/01/'.$year;

	executeSql("UPDATE PRESTAMO_PAGOS SET Estado='PAGADO' WHERE Tipo='Cuota' AND NOT Estado='UNIFICADO' AND Fecha = #$retardDate#");

	$arrayRut = [];
	$array = executeSelect("SELECT pp.* 
							FROM PRESTAMO_PAGOS pp
							LEFT JOIN PRESTAMO p ON p.ID=pp.ID_PRESTAMO
							WHERE p.ID_FINIQUITO_PERSONAL=0 AND pp.Tipo='Cuota' AND pp.Estado='PAGADO' AND pp.Fecha = #$retardDate#");

	for($i=0;$i<count($array);$i++){
		$arrayDues = executeSelect("SELECT * FROM PRESTAMO_PAGOS WHERE ID_PRESTAMO=".$array[$i]['ID_PRESTAMO']);
		$state = 'PAGADO';
		for($j=0;$j<count($arrayDues);$j++){
			if($arrayDues[$j]['Estado']=='IMPAGO'){
				$state = 'IMPAGO';
			}
		}
		executeSql("UPDATE PRESTAMO SET Estado='$state' WHERE ID=".$array[$i]['ID_PRESTAMO']);
		$arrayLoan = executeSelect("SELECT * FROM PRESTAMO WHERE ID=".$array[$i]['ID_PRESTAMO']);
		$rut = $arrayLoan[0]['RUT'];
		if(strlen($rut)==7) $rut="   ".$rut;
		if(strlen($rut)==8) $rut="  ".$rut;
		if(strlen($rut)==9) $rut=" ".$rut;

		if(isset($arrayRut[$rut])){
			$arrayRut[$rut] += $array[$i]['Monto'];
		}else{
			$arrayRut[$rut] = $array[$i]['Monto'];
			//array_push($arrayRut[$rut],$array[$i]['Monto']);
		}
		//executeSql("UPDATE HYDF SET valremf2=0 WHERE ruthdf='".$rut."' AND codhdf='D099'");

		//updateSettlement($rut, $array[$i]['Monto']);
		updateSettlement($rut, $arrayRut[$rut]);
	}

	echo 'OK';

}elseif($type=='deleteVerify'){
	$id=$_POST['id'];	
	$array = executeSelect("SELECT * FROM PRESTAMO_PAGOS WHERE ID_PRESTAMO=$id AND Estado='PAGADO'");
	echo count($array);

}elseif($type=='delete'){
	$id=$_POST['id'];
	executeSql("DELETE FROM PRESTAMO WHERE ID=$id");
	executeSql("DELETE FROM PRESTAMO_PAGOS WHERE ID_PRESTAMO=$id");
	echo 'OK';

}elseif($type=='deletePayment'){
	$id=$_POST['id'];
	$idLoan=$_POST['idLoan'];
	executeSql("DELETE FROM PRESTAMO_ABONOS WHERE ID=$id");

	$array = executeSelect("SELECT * FROM PRESTAMO_ABONOS WHERE ID_PRESTAMO=$idLoan ORDER BY Numero");
	for($i=0;$i<count($array);$i++){
		executeSql("UPDATE PRESTAMO_ABONOS SET Numero=".($i+1)." WHERE ID=".$array[$i]['ID']);
	}
	executeSql("UPDATE PRESTAMO_PAGOS SET Estado='IMPAGO' WHERE ID_PRESTAMO=$idLoan");
	executeSql("UPDATE PRESTAMO SET Estado='IMPAGO' WHERE ID=$idLoan");


	echo 'OK';

}elseif($type=='savePayment'){
	$idLoan=$_POST['idLoan'];
	$number=$_POST['number'];
	$date=$_POST['date'];
	$amount=$_POST['amount'];
	$goPaid=$_POST['goPaid'];

	executeSql("INSERT INTO PRESTAMO_ABONOS(ID_PRESTAMO,Numero,Fecha,Monto)
		VALUES($idLoan,$number,'$date',$amount)");
		executeSql("UPDATE PRESTAMO_PAGOS SET Abono=$amount WHERE ID_PRESTAMO=$idLoan");

	if($goPaid){
		executeSql("UPDATE PRESTAMO_PAGOS SET Estado='PAGADO' WHERE ID_PRESTAMO=$idLoan");
		executeSql("UPDATE PRESTAMO SET Estado='PAGADO' WHERE ID=$idLoan");
	}
	echo 'OK';
}



function saveDues($id){
	executeSql("DELETE FROM PRESTAMO_PAGOS WHERE ID_PRESTAMO=$id");

	$duesList = $_POST['duesList'];
	$duesArray = explode("&&&&",$duesList);

	for($i=0;$i<count($duesArray)-1;$i++){//1er y último arreglo son vacíos
		$dues = explode("&&",$duesArray[$i]);

		executeSql("INSERT INTO PRESTAMO_PAGOS(ID_PRESTAMO, Tipo, Estado, Numero, Fecha, Monto)
					VALUES($id,'".$dues[1]."', '".$dues[4]."', '".$dues[0]."', '".$dues[2]."', ".$dues[3].")");
	}
	echo $id;
}



function updateSettlement($rut, $amount){

	$array = executeSelect("SELECT COUNT(ruthdf) AS quant FROM HYDF WHERE ruthdf='$rut' AND codhdf='D110'");

	if($array[0]['quant']==0){

		executeSql("INSERT INTO HYDF(ruthdf,
			codhdf,
			descriphdf,
			formatohdf,
			numenthdf,
			numdechdf,
			numcharhdf,
			vigenhdf,
			fecdesdehdf,
			fechastahdf,
			limpiezameshdf,
			formvalhdf,
			statushdf,
			liqhdf,
			linremf,
			valremf,
			valremf2,
			digitahdf,
			cc1hdf,
			abonohdf,
			abofechdf,
			totporcuotahdf)
			VALUES('$rut',
   			'D110',
   			'TOTAL PRESTAMOS',
   			'N',
   			10,
   			2,
   			0,
   			'P',
   			'01/01/100',
   			'01/01/100',
   			2,
   			'V',
   			'V',
   			10,
   			0,
   			1,
   			$amount,
   			'S',
   			'',
   			0,
   			'01/01/100',
   			100000)");

	}else{
		executeSql("UPDATE HYDF SET valremf2=$amount, valremf=1 WHERE ruthdf='$rut' AND codhdf='D110'");
	}

}

?>