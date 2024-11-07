<?php
//header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

if($_POST['type']=='setTxt'){

	$where = "";

	$array = executeSelect("SELECT STR(d.prv_rut) AS RUT,
							p.Prv_nom AS Proveedor,
							p.prv_banco AS Banco,
							p.prv_ctacte AS CtaCte,
							p.prv_tipcta AS CtaTipo,
							p.prv_acteco AS CodigoValeVista,
							b.codRRW AS CodigoBanco,
							p.Prv_mail AS Mail,
							t.tipdoc2 AS DT,
							STR(d.Numdcto) AS NDoc,
							FORMAT(d.Fecdcto,'dd/mm/yyyy') AS Emision,
							FORMAT(d.dcfecrec,'dd/mm/yyyy') AS Recepcion,
							FORMAT(d.dcfecvnc,'dd/mm/yyyy') AS Vencimiento,
							STR(d.Netodcto+d.ivadcto+IIF(ISNULL(d.exento),0,d.exento)+IIF(ISNULL(d.especifico),0,d.especifico)) AS Total,
							d.dctoc_folio AS NOC,
							d.statc AS VB,
							IIF(d.dcstatus ='S' , 'Si', 'No') AS Cont,
							SWITCH(d.statp='X','No',d.statp='Q','CT',d.statp='P','NP') AS Pago,
							d.dccta AS Tipo,
							d.dccta2 AS Cuenta,
							d.dccta3 AS CCosto,
							d.dc_obs1 AS Observaciones,
							d.dc_a1user AS MarcaPago,
							FORMAT(d.dc_a1fec,'dd/mm/yyyy') AS FecAut,
							d.dc_a1hra AS HoraAut,
							'' AS OC,
							STR(p.Prv_dv) AS Digito,
							d.dccondpago AS CondPagoOC,
							FORMAT(d.dc_a2fec,'dd/mm/yyyy') AS Fecha,
							d.dc_a2hra AS Hora,
							d.dc_a2user AS Usuario,
							d.statp AS Pag,
							d.tipdoc AS TipoP,
							d.dcstatus AS Stat,
							p.prvemich AS Emitir,
							STR(p.prv_emichrut) AS RUTP,
							p.prv_emichdv AS dvP,
							STR(d.ivadcto) AS IVA,
							STR(((d.Netodcto+d.ivadcto)/1.19)*0.19) AS IVAAutom,
							d.dcmoneda,
							STR(d.tc_neto) AS tc_neto,
							STR(d.tc_iva) AS tc_iva,
							STR(d.tc_total) AS tc_total,
							d.ID

							FROM (((DOCTOS d
							LEFT JOIN PROVEED p ON p.prv_rut=d.prv_rut)
							LEFT JOIN TIPDOC t ON t.tipdoc=d.tipdoc)
							LEFT JOIN BANCOS b ON b.Banco=p.prv_banco)
							WHERE d.statp='P' 
							$where
							ORDER BY d.prv_rut, d.tipdoc, d.Numdcto");

	$arrayParameters = executeSelect("SELECT * FROM T0058");

	if(count($array)>0){
		for($z=0;$z<count($array);$z++){
			if($array[$z]["dcmoneda"]!='Pesos'){
				$array[$z]["Total"] = $array[$z]["tc_total"];
			}
		}

		$NumeroNomina = $arrayParameters[0]['folio_eg'];
		$PreNumeroNomina = '';
		if($NumeroNomina<10000){
			$PreNumeroNomina = '0';
			if($NumeroNomina<1000){
				$PreNumeroNomina .= '0';
				if($NumeroNomina<100){
					$PreNumeroNomina .= '0';
					if($NumeroNomina<10){
						$PreNumeroNomina .= '0';
					}
				}
			}
		}
		//$FechaNomina = "20170630";
		//echo $dateStartEmi[2].$dateStartEmi[1].$dateStartEmi[0];
		$FechaNomina = $dateStartEmi[2].$dateStartEmi[1].$dateStartEmi[0];
		//$myfile = fopen("../files".$enterprise."/OC".$FechaNomina.".txt", "w") or die("Unable to open file!");
		@$myfile = fopen("../files".$enterprise."/OC".$FechaNomina.".txt", "x");
		$fileCorrelative = 1;
		while (!$myfile) {
			$nameCorrelative = $fileCorrelative;
			if(strlen($fileCorrelative) < 3){
				$actual_length = strlen($fileCorrelative);
				$zero = "";
				for($j=3;$j>$actual_length;$j--){
					$zero.="0";
				}
				$nameCorrelative = $zero.$nameCorrelative;
			}
			@$myfile = fopen("../files".$enterprise."/OC".$FechaNomina."_".$nameCorrelative.".txt", "x");
			$fileCorrelative++;
		}

		$SumaTotal = 0;

		for($i=0;$i<count($array);$i++){
			if($array[$i]["DT"]=='NC' || $array[$i]["DT"]=='EN'){
				$SumaTotal -= $array[$i]["Total"];
			}else{
				$SumaTotal += $array[$i]["Total"];
			}
		}
		if (strlen($SumaTotal) < 11){
			$actual_length = strlen($SumaTotal);
			$zero = "";
			for($j=11;$j>$actual_length;$j--){
				$zero.="0";
			}
			$SumaTotal = $zero.$SumaTotal;
		}

		if($_SESSION['enterprise']==78478460){
			fwrite($myfile, 
		"010784784606002".$PreNumeroNomina.$NumeroNomina."RR WINE LIMITADA        .01".$FechaNomina.$SumaTotal."00   N                                                                                                                                                                                                                                                                                                                                    0201".PHP_EOL);
		}elseif($_SESSION['enterprise']==76865975){
			fwrite($myfile, 
		"010768659753002".$PreNumeroNomina.$NumeroNomina."ANDES QUALITY WINES SPA .01".$FechaNomina.$SumaTotal."00   N                                                                                                                                                                                                                                                                                                                                    0201".PHP_EOL);
		}elseif($_SESSION['enterprise']==3045710){
			fwrite($myfile, 
		"010030457102002".$PreNumeroNomina.$NumeroNomina."EUGENIO VALENZUELA SOMAR.01".$FechaNomina.$SumaTotal."00   N                                                                                                                                                                                                                                                                                                                                    0201".PHP_EOL);
		}elseif($_SESSION['enterprise']==59097850){
			fwrite($myfile, 
		"010590978507002".$PreNumeroNomina.$NumeroNomina."RODEGOLD CHILE S.A.     .01".$FechaNomina.$SumaTotal."00   N                                                                                                                                                                                                                                                                                                                                    0201".PHP_EOL);
		}elseif($_SESSION['enterprise']==76125892){
			fwrite($myfile, 
		"010761258923002".$PreNumeroNomina.$NumeroNomina."AGRICOLA PULMODON LTDA  .01".$FechaNomina.$SumaTotal."00   N                                                                                                                                                                                                                                                                                                                                    0201".PHP_EOL);
		}elseif($_SESSION['enterprise']==76427839){
			fwrite($myfile, 
		"010764278399002".$PreNumeroNomina.$NumeroNomina."AGRICOLA ESTRELLA SPA   .01".$FechaNomina.$SumaTotal."00   N                                                                                                                                                                                                                                                                                                                                    0201".PHP_EOL);
		}
		/*	//A DEFINIR SEGÚN 
			fwrite($myfile, 
		"010784784606002".$PreNumeroNomina.$NumeroNomina."RR WINE LIMITADA        .01".$FechaNomina.$SumaTotal."00   N                                                                                                                                                                                                                                                                                                                                    0201".PHP_EOL);
		}*/

		for($i=0;$i<count($array);$i++){
			$text = "";
			if($_SESSION['enterprise']==78478460){
				$text = "020784784606002";
			}elseif($_SESSION['enterprise']==76865975){
				$text = "020768659753002";
			}elseif($_SESSION['enterprise']==3045710){
				$text = "020030457102002";
			}elseif($_SESSION['enterprise']==59097850){
				$text = "020590978507002";
			}elseif($_SESSION['enterprise']==76125892){
				$text = "020761258923002";
			}elseif($_SESSION['enterprise']==76427839){
				$text = "020764278399002";
			}							
			/*}else{
				//A DEFINIR
				$text = "020784784606002";
			}*/

			$text .= "  ";
			$text .= $PreNumeroNomina.$NumeroNomina; //N° de Documento
			$text .= substr($array[$i]["CtaTipo"], 0, 2)."0";

			$array[$i]["RUTP"] = trim($array[$i]["RUTP"]);
			if (strlen($array[$i]["RUTP"]) < 8){
				$actual_length = strlen($array[$i]["RUTP"]);
				$zero = "";
				for($j=8;$j>$actual_length;$j--){
					$zero.="0";
				}
				$array[$i]["RUTP"] = $zero.$array[$i]["RUTP"];
			}
			$text .= $array[$i]["RUTP"].trim($array[$i]["dvP"]);

			$emisor = $array[$i]["Emitir"];
			if (strlen($emisor) > 60){
				$emisor = substr($emisor, 0, 60);
			}else{
				$actual_length = strlen($emisor);
				$blank = "";
				for($j=60;$j>$actual_length;$j--){
					$blank.=" ";
				}
				$emisor = $emisor.$blank;
			}
			$text .= mb_convert_encoding($emisor, "Windows-1252"); //Máximo 60 caracteres, nombre de titular cuenta
			$text .= "0                                                                        "; //O+72espacios
			
			if(substr($array[$i]["CtaTipo"], 0, 2)!="02"){
				$text .= "  ";
				$text .= $array[$i]["CodigoBanco"]; //Código de Banco 3 caracteres
				if (strlen($array[$i]["CtaCte"]) > 22){
					$array[$i]["CtaCte"] = substr($array[$i]["CtaCte"], 0, 22);
				}else{
					$actual_length = strlen($array[$i]["CtaCte"]);
					$blank = "";
					for($j=22;$j>$actual_length;$j--){
						$blank.=" ";
					}
					$array[$i]["CtaCte"] = $array[$i]["CtaCte"].$blank;
				}
				$text .= $array[$i]["CtaCte"];
			}else{
				$text .= $array[$i]["CodigoValeVista"];
				$text .= $array[$i]["CodigoBanco"]; //Código de Banco 3 caracteres
				$text .= "                      ";
			}
			
			$array[$i]["Total"] = trim($array[$i]["Total"]);
			if (strlen($array[$i]["Total"]) < 14){
				$actual_length = strlen($array[$i]["Total"]);
				$zero = "";
				for($j=14;$j>$actual_length;$j--){
					$zero.="0";
				}
				$array[$i]["Total"] = $zero.$array[$i]["Total"];
			}
			$text .= $array[$i]["Total"]."00";

			$NumeroDocumento= trim($array[$i]["NDoc"]);
			if (strlen($NumeroDocumento) < 10){
				$actual_length = strlen($NumeroDocumento);
				$zero = "";
				for($j=10;$j>$actual_length;$j--){
					$zero.="0";
				}
				$NumeroDocumento = $zero.$NumeroDocumento;
			}
			$msg = mb_convert_encoding("_Pagado por Nómina de Proveedores N°_00","Windows-1252","UTF-8");
			$text .= "  _".$NumeroDocumento.$msg.$PreNumeroNomina.$NumeroNomina;
			$text .= "                                                                "; //64 espacios
			$number = (string)($i+1);
			if (strlen($number) < 4){
				$actual_length = strlen($number);
				$zero = "";
				for($j=4;$j>$actual_length;$j--){
					$zero.="0";
				}
				$number = $zero.$number;
			}
			$text .= $number."N";//Número de línea
			$text .= "             +000000S                                             ";
			fwrite($myfile, $text.PHP_EOL);

		}



		for($i=0;$i<count($array);$i++){

			$text = "";


		if($_SESSION['enterprise']==78478460){
				$text = "030784784606002";
			}elseif($_SESSION['enterprise']==76865975){
				$text = "030768659753002";
			}elseif($_SESSION['enterprise']==3045710){
				$text = "030030457102002";
			}elseif($_SESSION['enterprise']==59097850){
				$text = "030590978507002";
			}elseif($_SESSION['enterprise']==76125892){
				$text = "030761258923002";
			}elseif($_SESSION['enterprise']==76427839){
				$text = "030764278399002";
			}		


			/*if($_SESSION['enterprise']==78478460){
				$text = "030784784606002";
			}elseif($_SESSION['enterprise']==76865975){
				$text = "030768659753002";
			}else{
				//A DEFINIR
				$text = "030784784606002";
			}*/

			$text .= "  ";
			$number = (string)($i+1);
			if (strlen($number) < 4){
				$actual_length = strlen($number);
				$zero = "";
				for($j=4;$j>$actual_length;$j--){
					$zero.="0";
				}
				$number = $zero.$number;
			}			
			$text .= $PreNumeroNomina.$NumeroNomina.$number."EMA"; //N° de Documento

			if (strlen($array[$i]["Mail"]) > 96){
				$array[$i]["Mail"] = substr($array[$i]["Mail"], 0, 96);
			}else{
				$actual_length = strlen($array[$i]["Mail"]);
				$blank = "";
				for($j=96;$j>$actual_length;$j--){
					$blank.=" ";
				}
				$array[$i]["Mail"] = $array[$i]["Mail"].$blank;
			}
			$text .= $array[$i]["Mail"];

			$text .= "PAGO DE ";
			//$text .= mb_convert_encoding($array[$i]["TipoP"], "Windows-1252","UTF-8");
			$text .= $array[$i]["TipoP"];


			$array[$i]["NDoc"] = trim($array[$i]["NDoc"]);
			if (strlen($array[$i]["NDoc"]) < 10){
				$actual_length = strlen($array[$i]["NDoc"]);
				$blank = "";
				for($j=10;$j>$actual_length;$j--){
					$blank.=" ";
				}
				$array[$i]["NDoc"] = $blank.$array[$i]["NDoc"];
			}
			$text .= mb_convert_encoding(" N° ", "Windows-1252","UTF-8");

			$detail = $array[$i]["NDoc"]." Emitido a ".$array[$i]["Emitir"];
			$detail .= mb_convert_encoding(" PAGADO EN NOMINA N° ", "Windows-1252","UTF-8");;
			$detail .= $PreNumeroNomina.$NumeroNomina;

			if (strlen($detail) > 252){
				$detail = substr($detail, 0, 252);
			}else{
				$actual_length = strlen($detail);
				$blank = "";
				for($j=252;$j>$actual_length;$j--){
					$blank.=" ";
				}
				$detail = $detail.$blank;
			}

			//$text .= mb_convert_encoding($detail, "Windows-1252","UTF-8");
			$text .= $detail;
			$text .= " 000....................";
			fwrite($myfile, $text.PHP_EOL);

		}
		fclose($myfile);


		echo "OK";
	}else{
		echo 0;
	}




/*


//}else if($_POST['type']=='setTxt2'){
	$array = executeSelect("SELECT STR(d.prv_rut) AS RUT,
							p.Prv_nom AS Proveedor,
							p.prv_banco AS Banco,
							p.prv_ctacte AS CtaCte,
							p.prv_tipcta AS CtaTipo,
							p.prv_acteco AS CodigoValeVista,
							b.codRRW AS CodigoBanco,
							p.Prv_mail AS Mail,
							t.tipdoc2 AS DT,
							STR(d.Numdcto) AS NDoc,
							FORMAT(d.Fecdcto,'dd/mm/yyyy') AS Emision,
							FORMAT(d.dcfecrec,'dd/mm/yyyy') AS Recepcion,
							FORMAT(d.dcfecvnc,'dd/mm/yyyy') AS Vencimiento,
							STR(d.Netodcto+d.ivadcto+IIF(ISNULL(d.exento),0,d.exento)+IIF(ISNULL(d.especifico),0,d.especifico)) AS Total,
							d.dctoc_folio AS NOC,
							d.statc AS VB,
							IIF(d.dcstatus ='S' , 'Si', 'No') AS Cont,
							SWITCH(d.statp='X','No',d.statp='Q','CT',d.statp='P','NP') AS Pago,
							d.dccta AS Tipo,
							d.dccta2 AS Cuenta,
							d.dccta3 AS CCosto,
							d.dc_obs1 AS Observaciones,
							d.dc_a1user AS MarcaPago,
							FORMAT(d.dc_a1fec,'dd/mm/yyyy') AS FecAut,
							d.dc_a1hra AS HoraAut,
							'' AS OC,
							STR(p.Prv_dv) AS Digito,
							d.dccondpago AS CondPagoOC,
							FORMAT(d.dc_a2fec,'dd/mm/yyyy') AS Fecha,
							d.dc_a2hra AS Hora,
							d.dc_a2user AS Usuario,
							d.statp AS Pag,
							d.tipdoc AS TipoP,
							d.dcstatus AS Stat,
							p.prvemich AS Emitir,
							STR(p.prv_emichrut) AS RUTP,
							p.prv_emichdv AS dvP,
							STR(d.ivadcto) AS IVA,
							STR(((d.Netodcto+d.ivadcto)/1.19)*0.19) AS IVAAutom,
							d.dcmoneda,
							STR(d.tc_neto) AS tc_neto,
							STR(d.tc_iva) AS tc_iva,
							STR(d.tc_total) AS tc_total,
							d.ID

							FROM (((DOCTOS d
							LEFT JOIN PROVEED p ON p.prv_rut=d.prv_rut)
							LEFT JOIN TIPDOC t ON t.tipdoc=d.tipdoc)
							LEFT JOIN BANCOS b ON b.Banco=p.prv_banco)
							WHERE d.statp='P'
							$where
							ORDER BY d.prv_rut, d.tipdoc, d.Numdcto");



	if(count($array)>0){
		for($z=0;$z<count($array);$z++){
			if($array[$z]["dcmoneda"]!='Pesos'){
				$array[$z]["Total"] = $array[$z]["tc_total"];
			}
		}
		$NumeroNomina = $arrayParameters[0]['folio_eg'];
		$PreNumeroNomina = '';
		if($NumeroNomina<10000){
			$PreNumeroNomina = '0';
			if($NumeroNomina<1000){
				$PreNumeroNomina .= '0';
				if($NumeroNomina<100){
					$PreNumeroNomina .= '0';
					if($NumeroNomina<10){
						$PreNumeroNomina .= '0';
					}
				}
			}
		}
		//$FechaNomina = "20170630";
		//$FechaNomina2 = "30/06/17";
		$FechaNomina = $dateStartEmi[2].$dateStartEmi[1].$dateStartEmi[0];
		$FechaNomina2 = $dateStartEmi[0].$dateStartEmi[1].$dateStartEmi[2][2].$dateStartEmi[2][1];
		//$myfile = fopen("../files".$enterprise."/CP".$FechaNomina.".txt", "w") or die("Unable to open file!");
		@$myfile = fopen("../files".$enterprise."/CP".$FechaNomina.".txt", "x");
		$fileCorrelative = 1;
		while (!$myfile) {
			$nameCorrelative = $fileCorrelative;
			if(strlen($fileCorrelative) < 3){
				$actual_length = strlen($fileCorrelative);
				$zero = "";
				for($j=3;$j>$actual_length;$j--){
					$zero.="0";
				}
				$nameCorrelative = $zero.$nameCorrelative;
			}
			@$myfile = fopen("../files".$enterprise."/CP".$FechaNomina."_".$nameCorrelative.".txt", "x");
			$fileCorrelative++;
		}
		$SumaTotal = 0;

		for($i=0;$i<count($array);$i++){
			if($array[$i]["DT"]=='NC' || $array[$i]["DT"]=='EN'){
				$SumaTotal -= $array[$i]["Total"];
			}else{
				$SumaTotal += $array[$i]["Total"];
			}
		}



		for($i=0;$i<count($array);$i++){
			$text = "";
			$text = str_replace('X','',$array[$i]["Tipo"]); //Cuenta de cargo
			$text .= "         ,";
			$text .= trim($array[$i]["Total"]); //Total Documento
			$text .= ",0,"; //Total Todo?
			$text .= "PAGA:_".$array[$i]["DT"];

			$NumeroDocumento = trim($array[$i]["NDoc"]);
			if (strlen($NumeroDocumento) < 10){
				$actual_length = strlen($NumeroDocumento);
				$blank = "";
				for($j=10;$j>$actual_length;$j--){
					$blank.=" ";
				}
				$NumeroDocumento = $blank.$NumeroDocumento;
			}
			$text .= $NumeroDocumento;
			$text .= "A: ";
			$emisor = $array[$i]["Emitir"];
			if (strlen($emisor) > 34){
				$emisor = substr($emisor, 0, 34);
			}else{
				$actual_length = strlen($emisor);
				$blank = "";
				for($j=34;$j>$actual_length;$j--){
					$blank.=" ";
				}
				$emisor = $emisor.mb_convert_encoding("_NN°","Windows-1252","UTF-8").$blank;
			}
			$text .= $emisor; //Máximo 60 caracteres, nombre de titular cuenta

			$text .= "     ";
			$text .= ",1,0,0,   ,    ,   ,          ,   ,0,        ,0,        ,  ,0,";

			$array[$i]["RUTP"] = trim($array[$i]["RUTP"]);
			if (strlen($array[$i]["RUTP"]) < 0){
				if (strlen($array[$i]["RUTP"]) < 10){
					$actual_length = strlen($array[$i]["RUTP"]);
					$blank = "";
					for($j=10;$j>$actual_length;$j--){
						$blank.=" ";
					}
					$array[$i]["RUTP"] = $blank.$array[$i]["RUTP"];
				}
			}else{
				$array[$i]["RUTP"] = trim($array[$i]["RUT"]);
				if (strlen($array[$i]["RUTP"]) < 10){
					$actual_length = strlen($array[$i]["RUTP"]);
					$blank = "";
					for($j=10;$j>$actual_length;$j--){
						$blank.=" ";
					}
					$array[$i]["RUTP"] = $blank.$array[$i]["RUTP"];
				}
			}
			$text .= $array[$i]["RUTP"];
			$text .= ",MB,";
			$text .= trim($array[$i]["NDoc"]);
			$text .= ",".$FechaNomina2.",".$FechaNomina2;
			$text .= ",".$array[$i]["DT"].",".trim($array[$i]["NDoc"]);
			$text .= ",        ,0,0,0,0,0,0,0,0,0,0,S,N,          ,0,          ,0,          ,0,          ,0,          ,0,          ,0,          ,0,          ,0,          ,0,          ,0";

			fwrite($myfile, $text.PHP_EOL);

		}




		$last_text = "";
		$last_text = "1-1-01-05"; //Cuenta de cargo
		if($_SESSION['enterprise']!=78478460 && $_SESSION['enterprise']!=76865975){
			$last_text = "1-1-01-06"; //Cuenta de cargo
		}
		$last_text .= "         ,";
		$last_text .= "0,"; //Total Documento
		//$last_text .= trim($array[count($array)-1]["Total"]); //Total Documento
		$last_text .= $SumaTotal.","; //Total Todo
		$last_text .= "PAGA:_".$array[count($array)-1]["DT"];

		$NumeroDocumento = trim($array[count($array)-1]["NDoc"]);
		if (strlen($NumeroDocumento) < 10){
			$actual_length = strlen($NumeroDocumento);
			$blank = "";
			for($j=10;$j>$actual_length;$j--){
				$blank.=" ";
			}
			$NumeroDocumento = $blank.$NumeroDocumento;
		}
		$last_text .= $NumeroDocumento;
		$last_text .= "A: ";
		$emisor = $array[count($array)-1]["Emitir"];
		if (strlen($emisor) > 34){
			$emisor = substr($emisor, 0, 34);
		}else{
			$actual_length = strlen($emisor);
			$blank = "";
			for($j=34;$j>$actual_length;$j--){
				$blank.=" ";
			}
			$emisor = $emisor.mb_convert_encoding("_NN°","Windows-1252","UTF-8").$blank;
		}
		$last_text .= $emisor; //Máximo 60 caracteres, nombre de titular cuenta

		$last_text .= "     ";
		$last_text .= ",1,0,0,   ,    ,   ,          ,   ,0,        ,0,        ,MB,";
		$last_text .= $FechaNomina;
		$last_text .= ",          ,  ,                ,          ,          ,  ,0,      ,0,0,0,0,0,0,0,0,0,";
		$last_text .= $SumaTotal; //Total Todo
		$last_text .= ",S,N,          ,0,          ,0,          ,0,          ,0,          ,0,          ,0,          ,0,          ,0,          ,0,          ,0";

		fwrite($myfile, $last_text.PHP_EOL);

		fclose($myfile);


		$whereUpdate = "";
		if($dateEmi=="true"){
			$whereUpdate = "AND (Fecdcto BETWEEN #$retardDateStartEmi# AND #$retardDateEndEmi#)";
		}
		if($dateRec=="true"){
			$whereUpdate .= " AND (dcfecrec BETWEEN #$retardDateStartRec# AND #$retardDateEndRec#)";
		}
		if($dateExp=="true"){
			$whereUpdate .= " AND (dcfecvnc BETWEEN #$retardDateStartExp# AND #$retardDateEndExp#)";
		}

		executeSql("UPDATE DOCTOS SET nomina=$NumeroNomina WHERE statp='P' $whereUpdate");

		$NumeroNomina++;
		executeSql("UPDATE T0058 SET folio_eg=$NumeroNomina");

		echo "OK";
	}else{
		echo 0;
	}*/
}



?>