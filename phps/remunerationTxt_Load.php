<?php
//header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

if($_POST['type']=='setTxt'){

	$enterprise = $_POST['enterprise'];
	$year = $_POST['year'];
	$month = $_POST['month'];
	$state = $_POST['state'];
	//$plant = $_POST['plant'];
	$pay = $_POST['pay'];

	$plantWhere = '';
	/*if($plant!=0 && $plant!=98){
		$plantWhere = "AND VAL(r.cc1rem)=$plant";
	}*/
	$enterpriseWhere = "AND p.emp_per=$enterprise";

	$list = $_POST['list'];

	$paySql = "0";
	if($pay=='D026_D029'){
		$paySql = "VAL((SELECT r2.valrem2 FROM REM021 r2 WHERE codhdrem = 'D026'
				AND r2.aaaarem=r.aaaarem AND r2.mmrem=r.mmrem AND r2.cc1rem=r.cc1rem AND r2.rutrem=r.rutrem)) +
				VAL((SELECT r2.valrem2 FROM REM021 r2 WHERE codhdrem = 'D029'
				AND r2.aaaarem=r.aaaarem AND r2.mmrem=r.mmrem AND r2.cc1rem=r.cc1rem AND r2.rutrem=r.rutrem))";
	}elseif($pay=='D026'){
		$paySql = "VAL((SELECT r2.valrem2 FROM REM021 r2 WHERE codhdrem = 'D026'
				AND r2.aaaarem=r.aaaarem AND r2.mmrem=r.mmrem AND r2.cc1rem=r.cc1rem AND r2.rutrem=r.rutrem))";
	}elseif($pay=='D029'){
		$paySql = "VAL((SELECT r2.valrem2 FROM REM021 r2 WHERE codhdrem = 'D029'
				AND r2.aaaarem=r.aaaarem AND r2.mmrem=r.mmrem AND r2.cc1rem=r.cc1rem AND r2.rutrem=r.rutrem))";
	}else{
		$paySql = "VAL((SELECT r2.valrem2 FROM REM021 r2 WHERE codhdrem='$pay'
				AND r2.aaaarem=r.aaaarem AND r2.mmrem=r.mmrem AND r2.cc1rem=r.cc1rem AND r2.rutrem=r.rutrem))";
	}

	$sql = "SELECT 
			VAL(r.rutrem) AS RUT,
			p.cta_numero AS CtaCte,
			IIF(b.codRRW='001','01CtaCte.Banco Chile',IIF(b.Banco='CREDICHILE','11Credichile','07CtaCte.Otro Banco')) AS CtaTipo,
			'' AS CodigoValeVista,
			IIF(b.Banco='CREDICHILE','001',b.codRRW) AS CodigoBanco,
			IIF(p.mail_per='','pulmodon@rrwine.cl',p.mail_per) AS Mail,
			r.mmrem AS NDoc,
			$paySql AS Total,
			'Remuneración Mes' AS TipoP,
			p.Nom_per & ' ' & p.Apepat_per & ' ' & p.Apemat_per AS Emitir,
			VAL(r.rutrem) AS RUTP,
			p.dv_per AS dvP

			FROM (((REM02 r
			LEFT JOIN T0010 t ON t.Pl_codigo=VAL(r.cc1rem))
			LEFT JOIN PERSONAL p ON p.rut_per=VAL(r.rutrem))
			LEFT JOIN BANCOS b ON p.cta_banco=b.Banco)
			WHERE r.aaaarem=$year AND r.mmrem=$month AND r.statrem='$state' AND NOT cta_tipo='servipag' AND NOT cta_tipo IS NULL
			$plantWhere $enterpriseWhere AND VAL(r.rutrem) IN ($list)";


	$array = executeSelect($sql);
	
	$arrayParameters = executeSelect("SELECT * FROM T0058");

	if(count($array)>0){

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
		$FechaNomina = date('Ymd');
		//$myfile = fopen("../files".$enterprise."/OC".$FechaNomina.".txt", "w") or die("Unable to open file!");

		@$myfile = fopen("../files/".$enterprise."/OC".$FechaNomina.".txt", "x");
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
			@$myfile = fopen("../files/".$enterprise."/OC".$FechaNomina."_".$nameCorrelative.".txt", "x");
			$fileCorrelative++;
		}

		$SumaTotal = 0;


		for($i=0;$i<count($array);$i++){
			$SumaTotal += $array[$i]["Total"];
		}
		if (strlen($SumaTotal) < 11){
			$actual_length = strlen($SumaTotal);
			$zero = "";
			for($j=11;$j>$actual_length;$j--){
				$zero.="0";
			}
			$SumaTotal = $zero.$SumaTotal;
		}


		if($enterprise==3045710){
			fwrite($myfile, 
		"010030457102001".$PreNumeroNomina.$NumeroNomina."EUGENIO VALENZUELA SOMAR.01".$FechaNomina.$SumaTotal."00   N                                                                                                                                                                                                                                                                                                                                    0101".PHP_EOL);
		}elseif($enterprise==59097850){
			fwrite($myfile, 
		"010590978507001".$PreNumeroNomina.$NumeroNomina."RODEGOLD CHILE S.A.     .01".$FechaNomina.$SumaTotal."00   N                                                                                                                                                                                                                                                                                                                                    0101".PHP_EOL);
		}elseif($enterprise==76125892){
			fwrite($myfile, 
		"010761258923001".$PreNumeroNomina.$NumeroNomina."AGRICOLA PULMODON LTDA  .01".$FechaNomina.$SumaTotal."00   N                                                                                                                                                                                                                                                                                                                                    0101".PHP_EOL);
		}elseif($enterprise==76427839){
			fwrite($myfile, 
		"010764278399001".$PreNumeroNomina.$NumeroNomina."AGRICOLA ESTRELLA SPA   .01".$FechaNomina.$SumaTotal."00   N                                                                                                                                                                                                                                                                                                                                    0101".PHP_EOL);
		}

		for($i=0;$i<count($array);$i++){
			$text = "";
			if($enterprise==3045710){
				$text = "020030457102001";
			}elseif($enterprise==59097850){
				$text = "020590978507001";
			}elseif($enterprise==76125892){
				$text = "020761258923001";
			}elseif($enterprise==76427839){
				$text = "020764278399001";
			}							

			$text .= "  ";
			$text .= $PreNumeroNomina.$NumeroNomina; //N° de Documento
			$text .= substr($array[$i]["CtaTipo"], 0, 2)."0";

			$array[$i]["RUTP"] = intval($array[$i]["RUTP"]);
			if (strlen($array[$i]["RUTP"]) < 8){
				$actual_length = strlen($array[$i]["RUTP"]);
				$zero = "";
				for($j=8;$j>$actual_length;$j--){
					$zero.="0";
				}
				$array[$i]["RUTP"] = $zero.$array[$i]["RUTP"];
			}
			$text .= $array[$i]["RUTP"].$array[$i]["dvP"];

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
				if (strlen(trim($array[$i]["CtaCte"])) > 22){
					$array[$i]["CtaCte"] = substr(trim($array[$i]["CtaCte"]), 0, 22);
				}else{
					$actual_length = strlen(trim($array[$i]["CtaCte"]));
					$blank = "";
					for($j=22;$j>$actual_length;$j--){
						$blank.=" ";
					}
					$array[$i]["CtaCte"] = trim($array[$i]["CtaCte"]).$blank;
				}
				$text .= $array[$i]["CtaCte"];
			}else{
				$text .= $array[$i]["CodigoValeVista"];
				$text .= $array[$i]["CodigoBanco"]; //Código de Banco 3 caracteres
				$text .= "                      ";
			}
			
			$array[$i]["Total"] = intval($array[$i]["Total"]);
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
			$msg = mb_convert_encoding("_Pagado por Nómina de Remuneraciones N°_00","Windows-1252","UTF-8");
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


			if($enterprise==3045710){
				$text = "030030457102001";
			}elseif($enterprise==59097850){
				$text = "030590978507001";
			}elseif($enterprise==76125892){
				$text = "030761258923001";
			}elseif($enterprise==76427839){
				$text = "030764278399001";
			}		

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

}



?>