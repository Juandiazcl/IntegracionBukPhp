<?php
include("../connection/connection.php");

if($_POST['type']=='all'){

	$enterprise = $_POST['enterprise'];
	$year = $_POST['year'];
	$month = $_POST['month'];
	$state = $_POST['state'];
	$plant = $_POST['plant'];
	$accountType = $_POST['accountType'];
	$zeroValues = $_POST['zeroValues'];
	$onlyCalculate = $_POST['onlyCalculate'];
	$pay = $_POST['pay'];

	$plantWhere = '';
	$plantWhere2 = '';

	$plantArray = explode('&&', $plant)	;

	if(count($plantArray)==1){
		if($plant!=0 && $plant!=98){
			$plantWhere = "AND VAL(r.cc1rem)=$plant";
			$plantWhere2 = "AND p.planta_per=$plant";
		}
	}else{
		$plantWhere = "AND VAL(r.cc1rem) IN (";
		$plantWhere2 = "AND p.planta_per IN (";
		for($i=0;$i<count($plantArray);$i++){
			if($i>0){
				$plantWhere .= ",";
				$plantWhere2 .= ",";
			}
			$plantWhere .= $plantArray[$i];
			$plantWhere2 .= $plantArray[$i];
		}
		$plantWhere .= ")";
		$plantWhere2 .= ")";	
	}


	$enterpriseWhere = '';
	if($enterprise!=0){
		$enterpriseWhere = "AND p.emp_per=$enterprise";
	}

	$accountTypeWhere = '';
	if($onlyCalculate==0){
		if($accountType=='banco'){
			$accountTypeWhere = "AND NOT p.cta_tipo='servipag'";
		}else{
			$accountTypeWhere = "AND (p.cta_tipo='servipag' OR p.cta_tipo IS NULL)";
		}
	}

	$zeroValuesWhere = '';
	$sql2 = "";
	if($zeroValues=='1'){
		if($state=='A'){
			$sql2 = "UNION ALL
					SELECT 
					e.EmpSigla AS enterprise,
					t.PlNombre AS plant,
					p.planta_per AS plant_id,
					p.rut_per AS rut,
					p.dv_per AS rut_dv,
					p.Nom_per & ' ' & p.Apepat_per & ' ' & p.Apemat_per AS fullname,

					0 AS days_worked,
					0 AS days_seventh,
					0 AS days_license,
					0 AS days_abscent,
					0 AS rem_total,
					0 AS rem_advance,
					0 AS rem_bonus,
					0 AS rem_topay,
					0 AS ID_FINIQUITO_PERSONAL

					FROM ((PERSONAL p
					LEFT JOIN T0010 t ON t.Pl_codigo=p.planta_per)
					LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
					WHERE 
					p.estado_per='V' $plantWhere2 $enterpriseWhere $accountTypeWhere
					AND NOT p.rut_per IN 
					(SELECT 
					VAL(r.rutrem) AS rut
					FROM (((REM02 r
					LEFT JOIN T0010 t ON t.Pl_codigo=VAL(r.cc1rem))
					LEFT JOIN PERSONAL p ON p.rut_per=VAL(r.rutrem))
					LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
					WHERE r.aaaarem=$year AND r.mmrem=$month AND r.statrem='A' $plantWhere $enterpriseWhere $accountTypeWhere)";
		}else{

			$sql2 = "UNION ALL
					SELECT 
					e.EmpSigla AS enterprise,
					t.PlNombre AS plant,
					p.planta_per AS plant_id,
					p.rut_per AS rut,
					p.dv_per AS rut_dv,
					p.Nom_per & ' ' & p.Apepat_per & ' ' & p.Apemat_per AS fullname,

					0 AS days_worked,
					0 AS days_seventh,
					0 AS days_license,
					0 AS days_abscent,
					0 AS rem_total,
					0 AS rem_advance,
					0 AS rem_bonus,
					0 AS rem_topay,

					p.ID_FINIQUITO_PERSONAL

					FROM ((PERSONAL_HISTORICO p
					LEFT JOIN T0010 t ON t.Pl_codigo=p.planta_per)
					LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
					WHERE 1=1 $plantWhere2 $enterpriseWhere $accountTypeWhere
					AND (YEAR(p.fecing_per)<$year OR (YEAR(p.fecing_per)=$year AND MONTH(p.fecing_per)<=$month))
					AND (YEAR(p.fecter_per)>$year OR (YEAR(p.fecter_per)=$year AND MONTH(p.fecter_per)>=$month))
					AND NOT p.rut_per IN 
					(SELECT 
					VAL(r.rutrem) AS rut
					FROM (((REM02 r
					LEFT JOIN T0010 t ON t.Pl_codigo=VAL(r.cc1rem))
					LEFT JOIN PERSONAL_HISTORICO p ON p.rut_per=VAL(r.rutrem))
					LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
					WHERE r.aaaarem=$year AND r.mmrem=$month $plantWhere $enterpriseWhere $accountTypeWhere
					AND (YEAR(p.fecing_per)<$year OR (YEAR(p.fecing_per)=$year AND MONTH(p.fecing_per)<=$month))
					AND (YEAR(p.fecter_per)>$year OR (YEAR(p.fecter_per)=$year AND MONTH(p.fecter_per)>=$month))
					AND r.ID_FINIQUITO_PERSONAL=p.ID_FINIQUITO_PERSONAL)";
		}
	}else{
		if($pay=='D026_D029'){
			$zeroValuesWhere = "WHERE rem_advance>0 OR rem_bonus>0";
		}elseif($pay=='D026'){
			$zeroValuesWhere = "WHERE rem_advance>0";
		}elseif($pay=='D029'){
			$zeroValuesWhere = "WHERE rem_bonus>0";
		}else{
			$zeroValuesWhere = "WHERE rem_topay>0";
		}
	}

	
	$sql = "SELECT * FROM (SELECT 
			e.EmpSigla AS enterprise,
			t.PlNombre AS plant,
			r.cc1rem AS plant_id,
			VAL(r.rutrem) AS rut,
			p.dv_per AS rut_dv,
			p.Nom_per & ' ' & p.Apepat_per & ' ' & p.Apemat_per AS fullname,

			(SELECT r2.valrem2 FROM REM021 r2 WHERE codhdrem='P002'
			AND r2.aaaarem=r.aaaarem AND r2.mmrem=r.mmrem AND r2.cc1rem=r.cc1rem AND r2.rutrem=r.rutrem AND (r2.ID_FINIQUITO_PERSONAL=0 OR r2.ID_FINIQUITO_PERSONAL IS NULL)) AS days_worked,
			(SELECT r2.valrem2 FROM REM021 r2 WHERE codhdrem='P150'
			AND r2.aaaarem=r.aaaarem AND r2.mmrem=r.mmrem AND r2.cc1rem=r.cc1rem AND r2.rutrem=r.rutrem AND (r2.ID_FINIQUITO_PERSONAL=0 OR r2.ID_FINIQUITO_PERSONAL IS NULL)) AS days_seventh,
			(SELECT r2.valrem2 FROM REM021 r2 WHERE codhdrem='P003'
			AND r2.aaaarem=r.aaaarem AND r2.mmrem=r.mmrem AND r2.cc1rem=r.cc1rem AND r2.rutrem=r.rutrem AND (r2.ID_FINIQUITO_PERSONAL=0 OR r2.ID_FINIQUITO_PERSONAL IS NULL)) AS days_license,
			(SELECT r2.valrem2 FROM REM021 r2 WHERE codhdrem='P004'
			AND r2.aaaarem=r.aaaarem AND r2.mmrem=r.mmrem AND r2.cc1rem=r.cc1rem AND r2.rutrem=r.rutrem AND (r2.ID_FINIQUITO_PERSONAL=0 OR r2.ID_FINIQUITO_PERSONAL IS NULL)) AS days_abscent,

			(SELECT r2.valrem2 FROM REM021 r2 WHERE codhdrem='H045'
			AND r2.aaaarem=r.aaaarem AND r2.mmrem=r.mmrem AND r2.cc1rem=r.cc1rem AND r2.rutrem=r.rutrem AND (r2.ID_FINIQUITO_PERSONAL=0 OR r2.ID_FINIQUITO_PERSONAL IS NULL)) AS rem_total,
			(SELECT r2.valrem2 FROM REM021 r2 WHERE codhdrem='D026'
			AND r2.aaaarem=r.aaaarem AND r2.mmrem=r.mmrem AND r2.cc1rem=r.cc1rem AND r2.rutrem=r.rutrem AND (r2.ID_FINIQUITO_PERSONAL=0 OR r2.ID_FINIQUITO_PERSONAL IS NULL)) AS rem_advance,
			(SELECT r2.valrem2 FROM REM021 r2 WHERE codhdrem='D029'
			AND r2.aaaarem=r.aaaarem AND r2.mmrem=r.mmrem AND r2.cc1rem=r.cc1rem AND r2.rutrem=r.rutrem AND (r2.ID_FINIQUITO_PERSONAL=0 OR r2.ID_FINIQUITO_PERSONAL IS NULL)) AS rem_bonus,
			(SELECT r2.valrem2 FROM REM021 r2 WHERE codhdrem='H046'
			AND r2.aaaarem=r.aaaarem AND r2.mmrem=r.mmrem AND r2.cc1rem=r.cc1rem AND r2.rutrem=r.rutrem AND (r2.ID_FINIQUITO_PERSONAL=0 OR r2.ID_FINIQUITO_PERSONAL IS NULL)) AS rem_topay,
			0 AS ID_FINIQUITO_PERSONAL
			
			FROM (((REM02 r
			LEFT JOIN T0010 t ON t.Pl_codigo=VAL(r.cc1rem))
			LEFT JOIN PERSONAL p ON p.rut_per=VAL(r.rutrem))
			LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
			WHERE r.aaaarem=$year AND r.mmrem=$month AND r.statrem='$state' $plantWhere $enterpriseWhere $accountTypeWhere
			AND (r.ID_FINIQUITO_PERSONAL=0 OR r.ID_FINIQUITO_PERSONAL IS NULL)) AS X
			$zeroValuesWhere
			$sql2";

			//ORDER BY rut";

	if($state=='F'){
		$sql = "SELECT * FROM (SELECT 
			e.EmpSigla AS enterprise,
			t.PlNombre AS plant,
			r.cc1rem AS plant_id,
			VAL(r.rutrem) AS rut,
			p.dv_per AS rut_dv,
			p.Nom_per & ' ' & p.Apepat_per & ' ' & p.Apemat_per AS fullname,

			(SELECT r2.valrem2 FROM REM021 r2 WHERE codhdrem='P002'
			AND r2.aaaarem=r.aaaarem AND r2.mmrem=r.mmrem 
			AND r2.cc1rem=r.cc1rem AND r2.rutrem=r.rutrem AND r2.ID_FINIQUITO_PERSONAL=p.ID_FINIQUITO_PERSONAL) AS days_worked,
			(SELECT r2.valrem2 FROM REM021 r2 WHERE codhdrem='P150'
			AND r2.aaaarem=r.aaaarem AND r2.mmrem=r.mmrem 
			AND r2.cc1rem=r.cc1rem AND r2.rutrem=r.rutrem AND r2.ID_FINIQUITO_PERSONAL=p.ID_FINIQUITO_PERSONAL) AS days_seventh,
			(SELECT r2.valrem2 FROM REM021 r2 WHERE codhdrem='P003'
			AND r2.aaaarem=r.aaaarem AND r2.mmrem=r.mmrem 
			AND r2.cc1rem=r.cc1rem AND r2.rutrem=r.rutrem AND r2.ID_FINIQUITO_PERSONAL=p.ID_FINIQUITO_PERSONAL) AS days_license,
			(SELECT r2.valrem2 FROM REM021 r2 WHERE codhdrem='P004'
			AND r2.aaaarem=r.aaaarem AND r2.mmrem=r.mmrem 
			AND r2.cc1rem=r.cc1rem AND r2.rutrem=r.rutrem AND r2.ID_FINIQUITO_PERSONAL=p.ID_FINIQUITO_PERSONAL) AS days_abscent,

			(SELECT r2.valrem2 FROM REM021 r2 WHERE codhdrem='H045'
			AND r2.aaaarem=r.aaaarem AND r2.mmrem=r.mmrem 
			AND r2.cc1rem=r.cc1rem AND r2.rutrem=r.rutrem AND r2.ID_FINIQUITO_PERSONAL=p.ID_FINIQUITO_PERSONAL) AS rem_total,
			(SELECT r2.valrem2 FROM REM021 r2 WHERE codhdrem='D026'
			AND r2.aaaarem=r.aaaarem AND r2.mmrem=r.mmrem 
			AND r2.cc1rem=r.cc1rem AND r2.rutrem=r.rutrem AND r2.ID_FINIQUITO_PERSONAL=p.ID_FINIQUITO_PERSONAL) AS rem_advance,
			(SELECT r2.valrem2 FROM REM021 r2 WHERE codhdrem='D029'
			AND r2.aaaarem=r.aaaarem AND r2.mmrem=r.mmrem 
			AND r2.cc1rem=r.cc1rem AND r2.rutrem=r.rutrem AND r2.ID_FINIQUITO_PERSONAL=p.ID_FINIQUITO_PERSONAL) AS rem_bonus,
			(SELECT r2.valrem2 FROM REM021 r2 WHERE codhdrem='H046'
			AND r2.aaaarem=r.aaaarem AND r2.mmrem=r.mmrem 
			AND r2.cc1rem=r.cc1rem AND r2.rutrem=r.rutrem AND r2.ID_FINIQUITO_PERSONAL=p.ID_FINIQUITO_PERSONAL) AS rem_topay,

			p.ID_FINIQUITO_PERSONAL

			FROM (((REM02 r
			LEFT JOIN T0010 t ON t.Pl_codigo=VAL(r.cc1rem))
			LEFT JOIN PERSONAL_HISTORICO p ON p.rut_per=VAL(r.rutrem))
			LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
			WHERE r.aaaarem=$year AND r.mmrem=$month
			
			AND (YEAR(p.fecing_per)<$year OR (YEAR(p.fecing_per)=$year AND MONTH(p.fecing_per)<=$month))
			AND (YEAR(p.fecter_per)>$year OR (YEAR(p.fecter_per)=$year AND MONTH(p.fecter_per)>=$month))
			AND r.ID_FINIQUITO_PERSONAL=p.ID_FINIQUITO_PERSONAL

			$plantWhere $enterpriseWhere $accountTypeWhere) AS X
			$zeroValuesWhere
			$sql2";
	}

	//AND r.ID_FINIQUITO_PERSONAL=p.ID_FINIQUITO_PERSONAL
	//echo $sql;

	$array = executeSelect($sql);

	if(count($array)>0){

		for($i=0;$i<count($array);$i++){

			if(strlen($array[$i]["plant_id"])==1){
				$array[$i]["plant_id"] = '0'.$array[$i]["plant_id"];
			}

			$array[$i]["sel"] = '<input type="checkbox"></input>';
			
			$array[$i]["days_worked"] = number_format($array[$i]["days_worked"],0,'','.');
			$array[$i]["days_seventh"] = number_format($array[$i]["days_seventh"],0,'','.');
			$array[$i]["days_license"] = number_format($array[$i]["days_license"],0,'','.');
			$array[$i]["days_abscent"] = number_format($array[$i]["days_abscent"],0,'','.');
			$array[$i]["rem_total"] = number_format($array[$i]["rem_total"],0,'','.');

			$array[$i]["rem_advance"] = '<label style="font-weight: normal;" class="classD026">'.number_format($array[$i]["rem_advance"],0,'','.').'</label>';
			$array[$i]["rem_bonus"] = '<label style="font-weight: normal;" class="classD029">'.number_format($array[$i]["rem_bonus"],0,'','.').'</label>';
			$array[$i]["rem_topay"] = '<label style="font-weight: normal;" class="classH046">'.number_format($array[$i]["rem_topay"],0,'','.').'</label>';
			
			$array[$i]["manual"] = '<button class="btn btn-primary" 
										onclick="modalManual('.$year.','.$month.','.$array[$i]["plant_id"].',
										'.intval($array[$i]["rut"]).',
										\''.number_format($array[$i]["rut"],0,'','.').'-'.$array[$i]["rut_dv"].'\',
										\''.$array[$i]["fullname"].'\','.$array[$i]["ID_FINIQUITO_PERSONAL"].')">
									<i class="fa fa-edit fa-lg fa-fw"></i></button>';

			$array[$i]["pdf"] = '<button class="btn btn-danger" onclick="generatePDFLink(\'one\','.$year.','.$month.','.intval($array[$i]["rut"]).','.intval($array[$i]["plant_id"]).','.$array[$i]["ID_FINIQUITO_PERSONAL"].')"><i class="fa fa-file-pdf-o fa-lg fa-fw"></i></button>';
			
			$array[$i]["rut"] = '<label style="display: none">'.intval($array[$i]["rut"]).'</label>
								<label style="display: none">'.$array[$i]["rut_dv"].'</label>
								<label style="display: none">'.intval($array[$i]["rem_topay"]).'</label>
								<label style="display: none">'.intval($array[$i]["rem_advance"]).'</label>
								<label style="display: none">'.number_format($array[$i]["rut"],0,'','.').'-'.$array[$i]["rut_dv"].'</label>
								<label style="display: none">'.$array[$i]["plant_id"].'</label>
								<label style="display: none">'.$array[$i]["ID_FINIQUITO_PERSONAL"].'</label>
								'.number_format($array[$i]["rut"],0,'','.').'-'.$array[$i]["rut_dv"];
		}

		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}

}elseif($_POST['type']=='year'){
	$array = executeSelect("SELECT aaaarem FROM REM02 GROUP BY aaaarem ORDER BY aaaarem");
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}

}elseif($_POST['type']=='oneManual'){
	$year = $_POST['year'];
	$month = $_POST['month'];
	$costCenter = $_POST['costCenter'];
	$rut = $_POST['rut'];
	$settlement = $_POST['settlement'];

	$whereSettlement = "AND ID_FINIQUITO_PERSONAL=".$settlement;
	if($settlement==0){
		$whereSettlement = "AND (ID_FINIQUITO_PERSONAL=0 OR ID_FINIQUITO_PERSONAL IS NULL)";
	}

	$array = executeSelect("SELECT * FROM REM021_MANUAL_ORIGINAL ORDER BY codhdrem");
	$j = 200; //Dummy de linrem, en caso de que no exista aún el registro manual en la liquidación
	if(count($array)>0){
		for($i=0;$i<count($array);$i++){
			//$arrayData = executeSelect("SELECT * FROM REM021_MANUAL
			$arrayData = executeSelect("SELECT * FROM REM021
										WHERE codhdrem='".$array[$i]['codhdrem']."'
										AND aaaarem=".$year."
										AND mmrem=".$month."
										AND VAL(cc1rem)=".$costCenter."
										AND VAL(rutrem)=".$rut."
										".$whereSettlement);
			
			$array[$i]['valrem2Manual'] = 0;
			if(count($arrayData)>0){
				$array[$i]['linrem'] = $arrayData[0]['linrem'];
				if($arrayData[0]['valrem2']!=0){
					$array[$i]['valrem2Manual'] = number_format($arrayData[0]['valrem2'],2,',','.');
				}
			}else{
				$array[$i]['linrem'] = $j;
				$j++;
			}
		}
	}
	echo json_encode(utf8ize($array));

}elseif($_POST['type']=='fullManual'){
	$year = $_POST['year'];
	$month = $_POST['month'];
	$codhdrem = $_POST['manual'];
	$list = $_POST['list'];
	$listCC = $_POST['listCostCenter'];

	$arrayList = explode(',', $list);
	$arrayListCC = explode(',', $listCC);
	$array = array();
	for($i=0;$i<count($arrayList);$i++){
		$arrayData = executeSelect("SELECT * FROM REM021_MANUAL
									WHERE codhdrem='".$codhdrem."'
									AND aaaarem=".$year."
									AND mmrem=".$month."
									AND VAL(cc1rem)=".$arrayListCC[$i]."
									AND VAL(rutrem)=".$arrayList[$i]);
		$array[$i]['rut'] = $arrayList[$i];
		$array[$i]['costCenter'] = $arrayListCC[$i];
		if(count($arrayData)>0){
			$array[$i]['manual'] = number_format($arrayData[0]['valrem2'],2,',','.');
		}else{
			$array[$i]['manual'] = 0;
		}
	}
	echo json_encode(utf8ize($array));

}elseif($_POST['type']=='historic'){
	$rut = $_POST['rut'];
	$year1 = $_POST['year1'];
	$month1 = $_POST['month1'];
	$year2 = $_POST['year2'];
	$month2 = $_POST['month2'];

	$where = "";

	if($year1==$year2){
		$where = "AND aaaarem=".$year1." AND mmrem BETWEEN ".$month1." AND ".$month2;
	}else{
		$where = "AND (";

		for($i=$month1;$i<=12;$i++){
			if($where!="AND ("){
				$where .= " OR ";
			}
			$where .= "(aaaarem=".$year1." AND mmrem=".$i.")";
		}

		for($j=1;$j<=$month2;$j++){
			$where .= " OR (aaaarem=".$year2." AND mmrem=".$j.")";
		}
		
		if($year1+1<$year2){
			$onlyYears = "";
			for($k=$year1+1;$k<$year2;$k++){
				if($onlyYears!=""){
					$onlyYears.=",";
				}
				$onlyYears.=$k;
			}	
			$where.= " OR aaaarem IN (".$onlyYears.")";
		}
		$where .= ")";
	}

	$sql = "SELECT 
			r.aaaarem,
			r.mmrem,
			t.PlNombre AS plant,
			r.cc1rem AS plant_id
			FROM (REM02 r
			LEFT JOIN T0010 t ON t.Pl_codigo=VAL(r.cc1rem))
			WHERE VAL(rutrem)=".$rut."
			$where
			ORDER BY aaaarem, mmrem";
//echo $sql;
	$array = executeSelect($sql);

	for($i=0;$i<count($array);$i++){
		$array[$i]["pdf"] = '<button class="btn btn-danger" onclick="generatePDFLink(\'one\','.$array[$i]["aaaarem"].','.$array[$i]["mmrem"].','.$rut.','.intval($array[$i]["plant_id"]).')"><i class="fa fa-file-pdf-o fa-lg fa-fw"></i></button>';
	}

	echo json_encode(utf8ize($array));
}

?>