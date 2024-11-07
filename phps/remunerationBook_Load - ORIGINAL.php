<?php
header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();

$year = $_POST['year'];
$month = $_POST['month'];

if($_POST['type']=='all'){
	
	$array1 = executeSelect("SELECT
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
							ORDER BY p.rut_per");


	$array2 = executeSelect("SELECT
							D001.cc1rem AS cc1rem,
							p.rut_per AS rutrem,
							p.dv_per AS Dv_per,
							p.Nom_per & ' ' & p.Apepat_per & ' ' & p.Apemat_per AS Nombre, 

							STR(H005.valrem2) AS H005,
							STR(H160.valrem2) AS H160,
							STR(H030.valrem2) AS H030,
							STR(P082.valrem2) AS P082,
							STR(H016.valrem2) AS H016,
							STR(H017.valrem2) AS H017,
							STR(H018.valrem2) AS H018,
							STR(H019.valrem2) AS H019,
							STR(H031.valrem2) AS H031,
							STR(H035.valrem2) AS H035,
							STR(D003.valrem) AS AFP,
							STR(D003.valrem2) AS D003,
							STR(D012.valrem2) AS D012

							FROM (((((((((((((PERSONAL p
							LEFT JOIN (SELECT rutrem, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D001') D001 ON D001.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='H005') AS H005 ON H005.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='H160') AS H160 ON H160.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='H030') AS H030 ON H030.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='P082') AS P082 ON P082.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='H016') AS H016 ON H016.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='H017') AS H017 ON H017.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='H018') AS H018 ON H018.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='H019') AS H019 ON H019.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='H031') AS H031 ON H031.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='H035') AS H035 ON H035.rutrem=p.ficha_per)

							LEFT JOIN (SELECT rutrem, valrem2, cc1rem, valrem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D003') AS D003 ON D003.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D012') AS D012 ON D012.rutrem=p.ficha_per)
							WHERE p.ficha_per IN (SELECT r.rutrem FROM REM021 r WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D001')

							AND D001.cc1rem=H005.cc1rem
							AND D001.cc1rem=H160.cc1rem
							AND D001.cc1rem=H030.cc1rem
							AND D001.cc1rem=P082.cc1rem
							AND D001.cc1rem=H016.cc1rem
							AND D001.cc1rem=H017.cc1rem
							AND D001.cc1rem=H018.cc1rem
							AND D001.cc1rem=H019.cc1rem
							AND D001.cc1rem=H031.cc1rem
							AND D001.cc1rem=H035.cc1rem
							AND D001.cc1rem=D003.cc1rem
							AND D001.cc1rem=D003.cc1rem
							AND D001.cc1rem=D012.cc1rem

							ORDER BY p.rut_per");

	$array3 = executeSelect("SELECT
							D001.cc1rem AS cc1rem,
							p.rut_per AS rutrem,
							p.dv_per AS Dv_per,
							p.Nom_per & ' ' & p.Apepat_per & ' ' & p.Apemat_per AS Nombre, 
																		
							STR(D004.valrem) AS Salud,
							STR(D004.valrem2) AS D004,
							STR(D005.valrem2) AS D005,
							STR(D006.valrem2) AS D006,
							STR(D007.valrem2) AS D007,
							STR(D008.valrem2) AS D008,
							STR(D009.valrem2) AS D009,
							STR(D025.valrem2) AS D025,
							STR(IIF(IsNull((SELECT D100.valrem2 FROM REM021 D100 WHERE D100.aaaarem=$year AND D100.mmrem=$month AND D100.codhdrem='D100' AND D100.rutrem=D001.rutrem AND D100.cc1rem=D001.cc1rem)),0,(SELECT D100.valrem2 FROM REM021 D100 WHERE D100.aaaarem=$year AND D100.mmrem=$month AND D100.codhdrem='D100' AND D100.rutrem=D001.rutrem AND D100.cc1rem=D001.cc1rem))) AS D100,
							STR(IIF(IsNull((SELECT D101.valrem2 FROM REM021 D101 WHERE D101.aaaarem=$year AND D101.mmrem=$month AND D101.codhdrem='D101' AND D101.rutrem=D001.rutrem AND D101.cc1rem=D001.cc1rem)),0,(SELECT D101.valrem2 FROM REM021 D101 WHERE D101.aaaarem=$year AND D101.mmrem=$month AND D101.codhdrem='D101' AND D101.rutrem=D001.rutrem AND D101.cc1rem=D001.cc1rem))) AS D101,
							STR(D102.valrem2) AS D102,
							STR(D110.valrem2) AS D110,
							STR(D121.valrem2) AS D121,
							STR(D122.valrem2) AS D122							

							FROM ((((((((((((PERSONAL p
							LEFT JOIN (SELECT rutrem, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D001') D001 ON D001.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem, valrem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D004') AS D004 ON D004.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D005') AS D005 ON D005.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D006') AS D006 ON D006.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D007') AS D007 ON D007.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D008') AS D008 ON D008.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D009') AS D009 ON D009.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D025') AS D025 ON D025.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D102') AS D102 ON D102.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D110') AS D110 ON D110.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D121') AS D121 ON D121.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D122') AS D122 ON D122.rutrem=p.ficha_per)
							WHERE p.ficha_per IN (SELECT r.rutrem FROM REM021 r WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D001')

							AND D001.cc1rem = D004.cc1rem
							AND D001.cc1rem = D004.cc1rem
							AND D001.cc1rem = D005.cc1rem
							AND D001.cc1rem = D006.cc1rem
							AND D001.cc1rem = D007.cc1rem
							AND D001.cc1rem = D008.cc1rem
							AND D001.cc1rem = D009.cc1rem
							AND D001.cc1rem = D025.cc1rem
							AND D001.cc1rem = D102.cc1rem
							AND D001.cc1rem = D110.cc1rem
							AND D001.cc1rem = D121.cc1rem
							AND D001.cc1rem = D122.cc1rem


							ORDER BY p.rut_per");

	$array4 = executeSelect("SELECT
							D001.cc1rem AS cc1rem,
							p.rut_per AS rutrem,
							p.dv_per AS Dv_per,
							p.Nom_per & ' ' & p.Apepat_per & ' ' & p.Apemat_per AS Nombre, 
								
							STR(D123.valrem2) AS D123,
							STR(D150.valrem2) AS D150,
							STR(D151.valrem2) AS D151,
							STR(D155.valrem2) AS D155,
							STR(D031.valrem2) AS D031,
							STR(D054.valrem2) AS D054,
							STR(D029.valrem2) AS D029,
							STR(D045.valrem2) AS D045,
							STR(D042.valrem2) AS D042,
							STR(H045.valrem2) AS H045,
							STR(D026.valrem2) AS D026,
							STR(H046.valrem2) AS H046

							FROM (((((((((((((PERSONAL p
							LEFT JOIN (SELECT rutrem, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D001') D001 ON D001.rutrem=p.ficha_per)							
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D123') AS D123 ON D123.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D150') AS D150 ON D150.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D151') AS D151 ON D151.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D155') AS D155 ON D155.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D031') AS D031 ON D031.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D054') AS D054 ON D054.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D029') AS D029 ON D029.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D045') AS D045 ON D045.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D042') AS D042 ON D042.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='H045') AS H045 ON H045.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D026') AS D026 ON D026.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='H046') AS H046 ON H046.rutrem=p.ficha_per)

							WHERE p.ficha_per IN (SELECT r.rutrem FROM REM021 r WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D001')

							AND D001.cc1rem = D123.cc1rem
							AND D001.cc1rem = D150.cc1rem
							AND D001.cc1rem = D151.cc1rem
							AND D001.cc1rem = D155.cc1rem
							AND D001.cc1rem = D031.cc1rem
							AND D001.cc1rem = D054.cc1rem
							AND D001.cc1rem = D029.cc1rem
							AND D001.cc1rem = D045.cc1rem
							AND D001.cc1rem = D042.cc1rem
							AND D001.cc1rem = H045.cc1rem
							AND D001.cc1rem = D026.cc1rem
							AND D001.cc1rem = H046.cc1rem
							ORDER BY p.rut_per");

	$array5 = executeSelect("SELECT
							D001.cc1rem AS cc1rem,
							p.rut_per AS rutrem,
							p.dv_per AS Dv_per,
							p.Nom_per & ' ' & p.Apepat_per & ' ' & p.Apemat_per AS Nombre, 
								
							$year AS aaaarem,
							$month AS mmrem,
							STR(H001.valrem2) AS H001,
							STR(P001.valrem2) AS P001,
							STR(P005.valrem2) AS P005,
							p.estado_per AS statrem,
							FORMAT(p.fecing_per,'dd/mm/yyyy') AS fecing_per,
							FORMAT(p.fec_fin_per,'dd/mm/yyyy') AS fec_fin_per

							FROM ((((PERSONAL p
							LEFT JOIN (SELECT rutrem, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D001') D001 ON D001.rutrem=p.ficha_per)							
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='H001') AS H001 ON H001.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='P001') AS P001 ON P001.rutrem=p.ficha_per)
							LEFT JOIN (SELECT rutrem, valrem2, cc1rem FROM REM021 WHERE aaaarem=$year AND mmrem=$month AND codhdrem='P005') AS P005 ON P005.rutrem=p.ficha_per)

							WHERE p.ficha_per IN (SELECT r.rutrem FROM REM021 r WHERE aaaarem=$year AND mmrem=$month AND codhdrem='D001')
							AND D001.cc1rem=H001.cc1rem
							AND D001.cc1rem=P001.cc1rem
							AND D001.cc1rem=P005.cc1rem
							ORDER BY p.rut_per");

	if(count($array1)>0){
		for($i=0;$i<count($array1);$i++){

			$array1[$i]["H002a"] = str_replace(".",",",$array1[$i]["H002a"]);
			$array1[$i]["P002"] = str_replace(".",",",$array1[$i]["P002"]);
			$array1[$i]["P003"] = str_replace(".",",",$array1[$i]["P003"]);
			$array1[$i]["P004"] = str_replace(".",",",$array1[$i]["P004"]);
			$array1[$i]["H002"] = str_replace(".",",",$array1[$i]["H002"]);
			$array1[$i]["H150a"] = str_replace(".",",",$array1[$i]["H150a"]);
			$array1[$i]["H150"] = str_replace(".",",",$array1[$i]["H150"]);
			$array1[$i]["H004"] = str_replace(".",",",$array1[$i]["H004"]);
			$array1[$i]["P007"] = str_replace(".",",",$array1[$i]["P007"]);
			$array1[$i]["H003"] = str_replace(".",",",$array1[$i]["H003"]);
			$array1[$i]["H155"] = str_replace(".",",",$array1[$i]["H155"]);
			$array1[$i]["H058"] = str_replace(".",",",$array1[$i]["H058"]);
			$array1[$i]["H007"] = str_replace(".",",",$array1[$i]["H007"]);
			$array1[$i]["H008"] = str_replace(".",",",$array1[$i]["H008"]);

			$array1[$i]["H005"] = str_replace(".",",",$array2[$i]["H005"]);
			$array1[$i]["H160"] = str_replace(".",",",$array2[$i]["H160"]);
			$array1[$i]["H030"] = str_replace(".",",",$array2[$i]["H030"]);
			$array1[$i]["P082"] = str_replace(".",",",$array2[$i]["P082"]);
			$array1[$i]["H016"] = str_replace(".",",",$array2[$i]["H016"]);
			$array1[$i]["H017"] = str_replace(".",",",$array2[$i]["H017"]);
			$array1[$i]["H018"] = str_replace(".",",",$array2[$i]["H018"]);
			$array1[$i]["H019"] = str_replace(".",",",$array2[$i]["H019"]);
			$array1[$i]["H031"] = str_replace(".",",",$array2[$i]["H031"]);
			$array1[$i]["H035"] = str_replace(".",",",$array2[$i]["H035"]);
			$array1[$i]["AFP"] = str_replace(".",",",$array2[$i]["AFP"]);
			$array1[$i]["D003"] = str_replace(".",",",$array2[$i]["D003"]);
			$array1[$i]["D012"] = str_replace(".",",",$array2[$i]["D012"]);

			$array1[$i]["Salud"] = str_replace(".",",",$array3[$i]["Salud"]);
			$array1[$i]["D004"] = str_replace(".",",",$array3[$i]["D004"]);
			$array1[$i]["D005"] = str_replace(".",",",$array3[$i]["D005"]);
			$array1[$i]["D006"] = str_replace(".",",",$array3[$i]["D006"]);
			$array1[$i]["D007"] = str_replace(".",",",$array3[$i]["D007"]);
			$array1[$i]["D008"] = str_replace(".",",",$array3[$i]["D008"]);
			$array1[$i]["D009"] = str_replace(".",",",$array3[$i]["D009"]);
			$array1[$i]["D025"] = str_replace(".",",",$array3[$i]["D025"]);
			$array1[$i]["D100"] = str_replace(".",",",$array3[$i]["D100"]);
			$array1[$i]["D101"] = str_replace(".",",",$array3[$i]["D101"]);
			$array1[$i]["D102"] = str_replace(".",",",$array3[$i]["D102"]);
			$array1[$i]["D110"] = str_replace(".",",",$array3[$i]["D110"]);
			$array1[$i]["D121"] = str_replace(".",",",$array3[$i]["D121"]);
			$array1[$i]["D122"] = str_replace(".",",",$array3[$i]["D122"]);

			$array1[$i]["D123"] = str_replace(".",",",$array4[$i]["D123"]);
			$array1[$i]["D150"] = str_replace(".",",",$array4[$i]["D150"]);
			$array1[$i]["D151"] = str_replace(".",",",$array4[$i]["D151"]);
			$array1[$i]["D155"] = str_replace(".",",",$array4[$i]["D155"]);
			$array1[$i]["D031"] = str_replace(".",",",$array4[$i]["D031"]);
			$array1[$i]["D054"] = str_replace(".",",",$array4[$i]["D054"]);
			$array1[$i]["D029"] = str_replace(".",",",$array4[$i]["D029"]);
			$array1[$i]["D045"] = str_replace(".",",",$array4[$i]["D045"]);
			$array1[$i]["D042"] = str_replace(".",",",$array4[$i]["D042"]);
			$array1[$i]["H045"] = str_replace(".",",",$array4[$i]["H045"]);
			$array1[$i]["D026"] = str_replace(".",",",$array4[$i]["D026"]);
			$array1[$i]["H046"] = str_replace(".",",",$array4[$i]["H046"]);
			
			$array1[$i]["aaaarem"] = $array5[$i]["aaaarem"];
			$array1[$i]["mmrem"] = $array5[$i]["mmrem"];
			$array1[$i]["H001"] = str_replace(".",",",$array5[$i]["H001"]);
			$array1[$i]["P001"] = str_replace(".",",",$array5[$i]["P001"]);
			$array1[$i]["P005"] = str_replace(".",",",$array5[$i]["P005"]);
			$array1[$i]["statrem"] = $array5[$i]["statrem"];
			$array1[$i]["fecing_per"] = $array5[$i]["fecing_per"];
			$array1[$i]["fec_fin_per"] = $array5[$i]["fec_fin_per"];

		}
		echo json_encode(utf8ize($array1));
	}else{
		echo 0;
	}


}

?>