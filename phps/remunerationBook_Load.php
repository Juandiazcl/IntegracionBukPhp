<?php
header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();

$where = '';
if($_POST['state']!='T'){
	$where = "AND p.estado_per='".$_POST['state']."' ";
}



if($_POST['type']=='all'){
	
	/*$array1 = executeSelect("SELECT
							D001.cc1rem AS cc1rem,
							STR(p.rut_per) AS rutrem,
							p.dv_per AS Dv_per,
							p.Nom_per & ' ' & p.Apepat_per & ' ' & p.Apemat_per AS Nombre, 
							STR(H002.valrem2) AS H002a,
							STR(P002.valrem2) AS P002,
							STR(P003.valrem2) AS P003,
							STR(P004.valrem2) AS P004,
							STR(H002.valrem2) AS H002,
							STR(H150.valrem) AS H150a,
							STR(H150.valrem2) AS H150,
							STR(H004.valrem2) AS H004,
							STR(P007.valrem2) AS P007,
							STR(H003.valrem2) AS H003,
							STR(H155.valrem2) AS H155,
							STR(H058.valrem2) AS H058,
							STR(H007.valrem2) AS H007,
							STR(H008.valrem2) AS H008						


							FROM (((((((((((((PERSONAL p
							LEFT JOIN (SELECT rutrem, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D001') D001 ON D001.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='P002') P002 ON P002.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='P003') P003 ON P003.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='P004') P004 ON P004.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem, valrem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='H002') H002 ON H002.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem, valrem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='H150') AS H150 ON H150.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='H004') AS H004 ON H004.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='P007') AS P007 ON P007.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='H003') AS H003 ON H003.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='H155') AS H155 ON H155.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='H058') AS H058 ON H058.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='H007') AS H007 ON H007.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='H008') AS H008 ON H008.rutrem=p.ficha_per)
							WHERE p.ficha_per IN (SELECT r.rutrem FROM REM021 r WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D001')
							AND D001.cc1rem=P002.cc1rem
							AND D001.cc1rem=P003.cc1rem
							AND D001.cc1rem=P004.cc1rem
							AND D001.cc1rem=H002.cc1rem
							AND D001.cc1rem=H150.cc1rem
							AND D001.cc1rem=H004.cc1rem
							AND D001.cc1rem=P007.cc1rem
							AND D001.cc1rem=H003.cc1rem
							AND D001.cc1rem=H155.cc1rem
							AND D001.cc1rem=H058.cc1rem
							AND D001.cc1rem=H007.cc1rem
							AND D001.cc1rem=H008.cc1rem
							ORDER BY p.rut_per");*/
	$sql = "SELECT
			STR(p.rut_per) AS rutrem,
			p.dv_per AS Dv_per,

			p.Direc_per,
			'' AS comuna_per,
			p.ciudad_per,
			FORMAT(p.fecnac_per,'dd/mm/yyyy') AS fecnac_per,
			p.sexo_per,
			ec.desciv,
			p.nac_per,
			FORMAT(p.fecing_per,'dd/mm/yyyy') AS fecing_per,
			p.Apepat_per,
			p.Apemat_per,
			p.Nom_per,
			p.cc1_per,
			p.afp_per,
			a.fac1_afp+a.fac3_afp AS porc_afp,
			p.isa_per,
			p.porc_isa_per,
			p.peso_isa_per,
			p.uf_isa_per,
			p.porc_inp,
			IIF(p.indef = 1 , 'Indef.', 'Fijo') AS indef

			FROM ((PERSONAL p
			LEFT JOIN (SELECT * FROM ESTCIV) AS ec ON ec.Estciv=p.escciv_per)
			LEFT JOIN (SELECT * FROM AFP) AS a ON a.cod_afp=p.afp_per)
			WHERE 1=1 $where
		
			ORDER BY p.rut_per";

			//echo $sql;


	$array1 = executeSelect($sql);

	if(count($array1)>0){
		for($i=0;$i<count($array1);$i++){

			$array1[$i]["rutrem"] = str_replace(".",",",$array1[$i]["rutrem"]);
			$array1[$i]["RUT"] = str_replace(".",",",$array1[$i]["rutrem"]).'-'.$array1[$i]["Dv_per"];
			$array1[$i]["Direc_per"] = str_replace(".",",",$array1[$i]["Direc_per"]);
			$array1[$i]["comuna_per"] = str_replace(".",",",$array1[$i]["ciudad_per"]);//Se intercambia con ciudad
			$array1[$i]["ciudad_per"] = str_replace(".",",",$array1[$i]["ciudad_per"]);//Se intercambia con comuna (error de bd)
			$array1[$i]["fecnac_per"] = str_replace(".",",",$array1[$i]["fecnac_per"]);
			$array1[$i]["sexo_per"] = str_replace(".",",",$array1[$i]["sexo_per"]);
			$array1[$i]["desciv"] = str_replace(".",",",$array1[$i]["desciv"]);
			$array1[$i]["nac_per"] = str_replace(".",",",$array1[$i]["nac_per"]);
			$array1[$i]["fecing_per"] = str_replace(".",",",$array1[$i]["fecing_per"]);//x3
			$array1[$i]["Apepat_per"] = str_replace(".",",",$array1[$i]["Apepat_per"]);
			$array1[$i]["Apemat_per"] = str_replace(".",",",$array1[$i]["Apemat_per"]);
			$array1[$i]["Nom_per"] = str_replace(".",",",$array1[$i]["Nom_per"]);
			$array1[$i]["cc1_per"] = str_replace(".",",",$array1[$i]["cc1_per"]);
			$array1[$i]["afp_per"] = str_replace(".",",",$array1[$i]["afp_per"]);
			$array1[$i]["porc_afp"] = str_replace(".",",",$array1[$i]["porc_afp"]);
			$array1[$i]["isa_per"] = str_replace(".",",",$array1[$i]["isa_per"]);
			$array1[$i]["porc_isa_per"] = str_replace(".",",",$array1[$i]["porc_isa_per"]);
			$array1[$i]["peso_isa_per"] = str_replace(".",",",$array1[$i]["peso_isa_per"]);
			$array1[$i]["uf_isa_per"] = str_replace(".",",",$array1[$i]["uf_isa_per"]);
			$array1[$i]["porc_inp"] = str_replace(".",",",$array1[$i]["porc_inp"]);
			//$array1[$i]["fecing_per"] = str_replace(".",",",$array1[$i]["fecing_per"]);//

			/*$array1[$i]["aaaarem"] = $array5[$i]["aaaarem"];
			$array1[$i]["mmrem"] = $array5[$i]["mmrem"];
			$array1[$i]["H001"] = str_replace(".",",",$array5[$i]["H001"]);
			$array1[$i]["P001"] = str_replace(".",",",$array5[$i]["P001"]);
			$array1[$i]["P005"] = str_replace(".",",",$array5[$i]["P005"]);
			$array1[$i]["statrem"] = $array5[$i]["statrem"];
			$array1[$i]["fecing_per"] = $array5[$i]["fecing_per"];
			$array1[$i]["fec_fin_per"] = $array5[$i]["fec_fin_per"];*/

		}
		echo json_encode(utf8ize($array1));
	}else{
		echo 0;
	}


}

?>