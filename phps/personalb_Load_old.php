<?php
header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();

if($_POST['type']=='all'){

	$state = $_POST['state'];
	$plant = $_POST['plant'];
	$where = '';
	if($state!='T'){
		$where = "WHERE p.estado_per='$state'";
	}
	if($plant!=0 && $plant!=98){
		if($where==''){
			$where = "WHERE VAL(p.planta_per)=$plant";
		}else{
			$where .= " AND VAL(p.planta_per)=$plant";
		}
	}

	$array = executeSelect("SELECT STR(p.rut_per) AS id,
							p.rut_per,
							p.rut_per & '-' & p.dv_per AS rut,
							p.Nom_per AS name, 
							p.Apepat_per AS lastname1,
							p.Apemat_per AS lastname2,
							p.Nom_per & ' ' & p.Apepat_per & ' ' & p.Apemat_per AS fullname,
							p.estado_per AS status,
							t.PlNombre AS plant,
							e.EmpSigla AS enterprise,
							p.sbase_per AS salary,
							IIF(p.indef = 1 , 'Indef.', 'Fijo') AS duration,
							FORMAT(P.fecvig_per,'dd/mm/yyyy') AS contractStart,
							IIF(p.indef = 1 , '-', p.fecter_per) AS contractEnd,
							(SELECT l.descriplb FROM LABOR l WHERE l.codlb=VAL(p.hi_tpcargo)) AS charge,
							(SELECT cf.finiq_descrip FROM CAUSASFIN cf WHERE cf.finiq_codigo=p.Causa_fin_per) AS codeEnd,

							IIF(ISNULL(p.cta_tipo),'servipag',p.cta_tipo) AS accountType,
							IIF(ISNULL(p.cta_banco),'',p.cta_banco) AS accountBank,
							IIF(ISNULL(p.cta_numero),p.rut_per,p.cta_numero) AS accountNumber

							FROM ((PERSONAL p
							LEFT JOIN T0010 t ON t.Pl_codigo=p.planta_per)
							LEFT JOIN T0009 e ON e.Emp_codigo=p.emp_per)
							$where
							ORDER BY p.rut_per");

	$listBanks = getBanks();

	if(count($array)>0){
		for($i=0;$i<count($array);$i++){
			$array[$i]["select"]='<input type="checkbox"></input>';
		}
		for($i=0;$i<count($array);$i++){
			$array[$i]["edit"]='<button class="btn btn-warning btn-sm" onclick="editRow(\''.$array[$i]['rut_per'].'\')"><i class="fa fa-edit fa-lg fa-fw"></i></button>';
			$array[$i]["remuneration"]='<button class="btn btn-danger" onclick="viewRemuneration(\''.$array[$i]['id'].'\')"><i class="fa fa-file-pdf-o fa-lg fa-fw"></i></button>';


			$selected1 = '';
			$selected2 = '';
			$selected3 = '';
			$selected4 = '';
			$disabled1 = '';
			if($array[$i]["accountType"]=='servipag'){
				$selected1='selected';
				$disabled1='disabled';
			}
			if($array[$i]["accountType"]=='rut'){
				$selected2='selected';
				$disabled1='disabled';
			}
			if($array[$i]["accountType"]=='vista') $selected3='selected';
			if($array[$i]["accountType"]=='corriente') $selected4='selected';

			$array[$i]["accountType"]='<select class="form-control input-sm" onchange="changeAccount(this,'.intval($array[$i]['rut_per']).')">
											<option value="servipag" '.$selected1.'>SERVIPAG</option>
											<option value="rut" '.$selected2.'>Cuenta RUT</option>
											<option value="vista" '.$selected3.'>Cuenta Vista</option>
											<option value="corriente" '.$selected4.'>Cuenta Corriente</option>
										</select>';
			$array[$i]["accountBank"]='<select class="form-control input-sm" '.$disabled1.'>'.str_replace('value="'.$array[$i]["accountBank"].'"','value="'.$array[$i]["accountBank"].'" selected',$listBanks).'</select>';

			$array[$i]["accountNumber"]='<input class="form-control input-sm" value="'.$array[$i]['accountNumber'].'" maxlength="50" '.$disabled1.'></input>';
			$array[$i]["saveAccount"]='<button class="btn btn-success btn-sm" onclick="saveAccount(\''.intval($array[$i]['rut_per']).'\', this)"><i class="fa fa-save fa-lg fa-fw"></i></button>';
			//$array[$i]["eliminar"]='<button class="btn btn-danger" onclick="deleteRow(\''.$array[$i]['id'].'\')"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';
		}
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}


}elseif($_POST['type']=='one'){

	$array = executeSelect("SELECT
						p.rut_per,
						p.dv_per,
						p.emp_per,
						p.planta_per,
						p.estado_per,

						p.Apepat_per,
						p.Apemat_per,
						p.Nom_per,
						p.Direc_per,
						p.comuna_per,
						p.ciudad_per,
						p.fono_per,
						p.cel_per,
						p.mail_per,
						p.hi_tpcargo,
						p.Cestudio,
						p.nac_per,
						Format(p.fecnac_per,'dd/mm/yyyy') AS fecnac_per,
						p.escciv_per,
						p.sexo_per,
						p.SMcodigo,

						p.tramo_cargfam,
						p.carg_ulin,
						p.cenco_per,
						Format(p.fecing_per,'dd/mm/yyyy') AS fecing_per,
						Format(p.fecter_per,'dd/mm/yyyy') AS fecter_per,

						p.afp_per,
						p.cotizpevol_per,
						p.isa_per,
						p.porc_isa_per,
						p.peso_isa_per,
						p.uf_isa_per,

						p.caja_per,
						p.porc_inp,
						p.sbase_per,
						p.indef,
						p.labor_per,
						p.codtrt_per,
						p.codtrt2_per,
						p.codtrt3_per,
						p.codtrt4_per,
						p.Labortxt,
						p.Labortxt2,
						p.Labortxt3,
						p.Labortxt4,
						p.preclabor,
						p.preclabor2,
						p.preclabor3,
						p.preclabor4,

						p.cta_tipo,
						p.cta_banco,
						p.cta_numero

						FROM PERSONAL p
						WHERE p.rut_per=".$_POST['id']."
						ORDER BY p.rut_per");

	if(count($array)>0){
		$array[0]['rut'] = number_format($array[0]['rut_per'],0,'','.').'-'.$array[0]['dv_per'];
		$array[0]['file'] = number_format($array[0]['rut_per'],0,'','');
		$array[0]['cotizpevol_per'] = number_format($array[0]['cotizpevol_per'],2,'.','');
		$array[0]['porc_isa_per'] = number_format($array[0]['porc_isa_per'],2,'.','');
		$array[0]['uf_isa_per'] = number_format($array[0]['uf_isa_per'],2,'.','');
		$array[0]['porc_inp'] = number_format($array[0]['porc_inp'],2,'.','');
		$array[0]['cotizpevol_per'] = number_format($array[0]['cotizpevol_per'],2,'.','');
		$array[0]['preclabor'] = number_format($array[0]['preclabor'],2,'.','');
		$array[0]['preclabor2'] = number_format($array[0]['preclabor2'],2,'.','');
		$array[0]['preclabor3'] = number_format($array[0]['preclabor3'],2,'.','');
		$array[0]['preclabor4'] = number_format($array[0]['preclabor4'],2,'.','');


		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}

}elseif($_POST['type']=='verifyPersonal'){
	$rut = explode('-', $_POST['rut']);
	$rut = str_replace('.', '', $rut[0]);

	$array = executeSelect("SELECT * FROM PERSONAL WHERE rut_per=".$rut." AND NOT rut_per=".$_POST['id']);
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}

}elseif($_POST['type']=='allTally'){
	$plant = $_POST['plant'];
	$year = $_POST['year'];
	$month = $_POST['month'];
	//$date = explode("/", $_POST['date']);
	//$retardDate = $date[1]."/".$date[0]."/".$date[2];

	/*$array = executeSelect("SELECT 	
							rut_per,
							dv_per,
							Nom_per & ' ' & Apepat_per & ' ' & Apemat_per AS fullname
							FROM PERSONAL
							WHERE estado_per='V'
							AND VAL(cc1_per)=$plant");*/

	$sql="SELECT rut_per, dv_per, fullname
	FROM (
	SELECT 	
	rut_per,
	dv_per,
	Nom_per & ' ' & Apepat_per & ' ' & Apemat_per AS fullname
	FROM PERSONAL3
	WHERE estado_per='V'
	AND planta_per=$plant

	UNION ALL

	SELECT
	rut_per,
	(SELECT dv_per FROM PERSONAL3 p WHERE p.rut_per=t.rut_per) AS dv_per,
	nomtrabtj AS fullname
	FROM TARJASBUK2 t
	WHERE cc1tj='$plant'
	AND YEAR(t.fechatj)=$year AND MONTH(t.fechatj)=$month)

	GROUP BY rut_per, dv_per, fullname";

	//echo $sql;

	$array = executeSelect("SELECT rut_per, dv_per, fullname
							FROM (
							SELECT 	
							rut_per,
							dv_per,
							Nom_per & ' ' & Apepat_per & ' ' & Apemat_per AS fullname
							FROM PERSONAL3
							WHERE estado_per='V'
							AND planta_per=$plant

							UNION ALL

							SELECT
							rut_per,
							(SELECT dv_per FROM PERSONAL3 p WHERE p.rut_per=t.rut_per) AS dv_per,
							nomtrabtj AS fullname
							FROM TARJASBUK2 t
							WHERE cc1tj='$plant'
							AND YEAR(t.fechatj)=$year AND MONTH(t.fechatj)=$month)

							GROUP BY rut_per, dv_per, fullname");

	//echo count($array);

	if(count($array)>0){
		for($i=0;$i<count($array);$i++){
			$array[$i]['rut_per'] = number_format($array[$i]['rut_per'],0,'','');
			$array[$i]['rut'] = number_format($array[$i]['rut_per'],0,'','.').'-'.$array[$i]['dv_per'];
		}
		echo json_encode(utf8ize($array));
	}else{

		if($month==1){
			$month=12;
			$year--;
		}else{
			$month--;
		}

		$arrayB = executeSelect("SELECT rut_per, dv_per, fullname
							FROM (
							SELECT 	
							rut_per,
							dv_per,
							Nom_per & ' ' & Apepat_per & ' ' & Apemat_per AS fullname
							FROM PERSONAL3
							WHERE estado_per='V'
							AND planta_per=$plant

							UNION ALL

							SELECT
							rut_per,
							(SELECT dv_per FROM PERSONAL3 p WHERE p.rut_per=t.rut_per) AS dv_per,
							nomtrabtj AS fullname
							FROM TARJASBUK2 t
							WHERE cc1tj='$plant'
							AND YEAR(t.fechatj)=$year AND MONTH(t.fechatj)=$month)

							GROUP BY rut_per, dv_per, fullname");

		if(count($arrayB)>0){
			for($i=0;$i<count($arrayB);$i++){
				$arrayB[$i]['rut_per'] = number_format($arrayB[$i]['rut_per'],0,'','');
				$arrayB[$i]['rut'] = number_format($arrayB[$i]['rut_per'],0,'','.').'-'.$arrayB[$i]['dv_per'];
			}
			echo json_encode(utf8ize($arrayB));
		}else{
			echo 0;
		}
	}


}

function getBanks(){
	$list='';
	$array = executeSelect("SELECT * FROM BANCOS ORDER BY Banco");
	if(count($array)>0){
		for($i=0;$i<count($array);$i++){
			$list .= '<option value="'.$array[$i]['Banco'].'">'.$array[$i]['Banco'].'</option>';
		}
	}

	return $list;
}

?>