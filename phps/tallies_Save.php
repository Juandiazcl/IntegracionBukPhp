<?php
include("../connection/connection.php");

set_time_limit(500);
session_start();

if($_POST['type']=='save'){
	$plant = $_POST['plant'];

	$date = explode("/", $_POST['date']);
	$retardDate = $date[1]."/".$date[0]."/".$date[2];

	$list = $_POST['list'];

	/*executeSql("DELETE FROM TARJAS
				WHERE cc1tj='".$plant."' 
				AND fechatj=#$retardDate#");*/

	$arrayOriginalTally = executeSelect("SELECT * FROM TARJAS
										WHERE cc1tj='".$plant."' 
										AND fechatj=#$retardDate#");

	//RESPALDO DE TARJAS DE TRABAJADORES
	$sql = "INSERT INTO TARJAS1_HISTORIAL(cc1tj,
							fechatj,
							codtj,
							fichatj,
							nomtrabtj,
							tratotj,
							param1,
							param2,
							rut_per,
							jornadatj,
							hhtj,
							rendtj,
							cc2tj,
							cc3tj,
							cc4tj,
							cc2,
							cc3,
							cc4,
							cc1trt,
							cattrt,
							codtrt,
							valtj,
							anteriortj,
							Obslintj,
							rendt1,
							codbonodd)
					SELECT cc1tj,
							fechatj,
							codtj,
							fichatj,
							nomtrabtj,
							tratotj,
							param1,
							param2,
							rut_per,
							jornadatj,
							hhtj,
							rendtj,
							cc2tj,
							cc3tj,
							cc4tj,
							cc2,
							cc3,
							cc4,
							cc1trt,
							cattrt,
							codtrt,
							valtj,
							anteriortj,
							Obslintj,
							rendt1,
							codbonodd
						FROM TARJAS1
						WHERE cc1tj='".$plant."' 
						AND fechatj=#$retardDate#";
	executeSql($sql);

	$arrayList = explode('&&&&', $list);
	
	$cc1tj = $plant;
	$fechatj = $_POST['date'];
	$ultlintj = count($arrayList);
	$usremitj = $_SESSION['userId'];
	$fecemitj = date('d/m/Y');
	$timemitj = date('H:i:s');
	$usrvbtj = '';
	$fecvbtj = date('d/m/Y');
	$timvbtj = date('H:i:s');
	$obstj = '';
	$stattj = 'A';

	if(count($arrayOriginalTally)==0){

		executeSql("INSERT INTO TARJAS(cc1tj,
								fechatj,
								ultlintj,
								usremitj,
								fecemitj,
								timemitj,
								usrvbtj,
								fecvbtj,
								timvbtj,
								obstj,
								stattj)

								VALUES('".$cc1tj."',
								'".$fechatj."',
								".$ultlintj.",
								'".$usremitj."',
								'".$fecemitj."',
								'".$timemitj."',
								'".$usrvbtj."',
								'".$fecvbtj."',
								'".$timvbtj."',
								'".$obstj."',
								'".$stattj."')
								");
	}else{
		executeSql("UPDATE TARJAS SET 
						ultlintj=".$ultlintj.",
						usremitj='".$usremitj."',
						fecemitj='".$fecemitj."',
						timemitj='".$timemitj."',
						usrvbtj='".$usrvbtj."',
						fecvbtj='".$fecvbtj."',
						timvbtj='".$timvbtj."',
						obstj='".$obstj."',
						stattj='".$stattj."'
					WHERE cc1tj='".$plant."' 
					AND fechatj=#$retardDate#");
	}

	//Se eliminan los registros para reemplazarlos con los nuevos
	executeSql("DELETE FROM TARJAS1
				WHERE cc1tj='".$plant."' 
				AND fechatj=#$retardDate#");

	for($i=0;$i<count($arrayList);$i++){
		$arrayListDetail = explode('&&', $arrayList[$i]);
		
		$cc1tj = $plant;
		$fechatj = $_POST['date'];
		$codtj = $arrayListDetail[0];
		$fichatj = $arrayListDetail[1];
		if(strlen($fichatj)==7) $fichatj="   ".$fichatj;
		if(strlen($fichatj)==8) $fichatj="  ".$fichatj;
		if(strlen($fichatj)==9) $fichatj=" ".$fichatj;
		$nomtrabtj = $arrayListDetail[2];
		$tratotj = 0;
		$param1 = 0;
		$param2 = 0;
		$rut_per = $arrayListDetail[1];
		$jornadatj = $arrayListDetail[8];
		$hhtj = $arrayListDetail[10];
		$rendtj = $arrayListDetail[9];
		
		$cc2tj = $arrayListDetail[3];
		$cc3tj = $arrayListDetail[4];
		$cc4tj = $arrayListDetail[5];
		$cc2 = $arrayListDetail[3];
		$cc3 = $arrayListDetail[4];
		$cc4 = $arrayListDetail[5];
		$cc1trt = $plant;
		$cattrt = $arrayListDetail[6];
		$codtrt = $arrayListDetail[7];
		$valtj = 0;
		$anteriortj = 1; //???
		$Obslintj = '';
		$rendt1 = 0;
		$codbonodd = 0;

		$sql = "INSERT INTO TARJAS1(cc1tj,
							fechatj,
							codtj,
							fichatj,
							nomtrabtj,
							tratotj,
							param1,
							param2,
							rut_per,
							jornadatj,
							hhtj,
							rendtj,
							cc2tj,
							cc3tj,
							cc4tj,
							cc2,
							cc3,
							cc4,
							cc1trt,
							cattrt,
							codtrt,
							valtj,
							anteriortj,
							Obslintj,
							rendt1,
							codbonodd)

						VALUES('".$cc1tj."',
							'".$fechatj."',
							".$codtj.",
							'".$fichatj."',
							'".$nomtrabtj."',
							".$tratotj.",
							".$param1.",
							".$param2.",
							".$rut_per.",
							".$jornadatj.",
							".$hhtj.",
							".$rendtj.",
							'".$cc2tj."',
							'".$cc3tj."',
							'".$cc4tj."',
							'".$cc2."',
							'".$cc3."',
							'".$cc4."',
							'".$cc1trt."',
							".$cattrt.",
							".$codtrt.",
							".$valtj.",
							".$anteriortj.",
							'".$Obslintj."',
							".$rendt1.",
							".$codbonodd.")";

							//echo $sql;

		executeSql($sql);
		
	}

	echo 'OK';

}elseif($_POST['type']=='close'){
	$plant = $_POST['plant'];

	$date = explode("/", $_POST['date']);
	$retardDate = $date[1]."/".$date[0]."/".$date[2];
	$state = $_POST['state'];

	$usrvbtj = $_SESSION['userId'];
	$fecvbtj = date('d/m/Y');
	$timvbtj = date('H:i:s');

	if($state=='A'){
		$usrvbtj = '';
	}

	executeSql("UPDATE TARJAS
				SET stattj='".$state."',
				usrvbtj='".$usrvbtj."',
				fecvbtj='".$fecvbtj."',
				timvbtj='".$timvbtj."'
				WHERE cc1tj='".$plant."' 
				AND fechatj=#$retardDate#");
	
	echo 'OK';


}elseif($_POST['type']=='repeat'){
	$plant = $_POST['plant'];
	/*if($plant<10){
		$plant = "0".$plant;
	}*/

	$date = explode("/", $_POST['date']);
	$retardDate = $date[1]."/".$date[0]."/".$date[2];


	$sql = "SELECT *,
			FORMAT(fechatj,'mm/dd/yyyy') AS tally_date
			FROM TARJAS
			WHERE cc1tj='$plant'
			AND NOT stattj='X' 
			ORDER BY fechatj DESC";
	$arrayTallyOld = executeSelect($sql);
	$sql = "SELECT * FROM TARJAS1 WHERE cc1tj='$plant' AND fechatj=#".$arrayTallyOld[0]['tally_date']."#";

	$arrayTallyDetailOld = executeSelect($sql);

	$cc1tj = $plant;
	$fechatj = $_POST['date'];
	$ultlintj = count($arrayTallyDetailOld);
	$usremitj = $_SESSION['userId'];
	$fecemitj = date('d/m/Y');
	$timemitj = date('H:i:s');
	$usrvbtj = '';
	$fecvbtj = date('d/m/Y');
	$timvbtj = date('H:i:s');
	$obstj = '';
	$stattj = 'A';

	$sql = "INSERT INTO TARJAS(cc1tj,
							fechatj,
							ultlintj,
							usremitj,
							fecemitj,
							timemitj,
							usrvbtj,
							fecvbtj,
							timvbtj,
							obstj,
							stattj)

							VALUES('".$cc1tj."',
							'".$fechatj."',
							".$ultlintj.",
							'".$usremitj."',
							'".$fecemitj."',
							'".$timemitj."',
							'".$usrvbtj."',
							'".$fecvbtj."',
							'".$timvbtj."',
							'".$obstj."',
							'".$stattj."')
							";
							
	executeSql($sql);
	
	$sql = "INSERT INTO TARJAS1(cc1tj,
							fechatj,
							codtj,
							fichatj,
							nomtrabtj,
							tratotj,
							param1,
							param2,
							rut_per,
							jornadatj,
							hhtj,
							rendtj,
							cc2tj,
							cc3tj,
							cc4tj,
							cc2,
							cc3,
							cc4,
							cc1trt,
							cattrt,
							codtrt,
							valtj,
							anteriortj,
							Obslintj,
							rendt1,
							codbonodd)
					SELECT cc1tj,
							'".$fechatj."',
							codtj,
							fichatj,
							nomtrabtj,
							tratotj,
							param1,
							param2,
							rut_per,
							jornadatj,
							0,
							0,
							cc2tj,
							cc3tj,
							cc4tj,
							cc2,
							cc3,
							cc4,
							cc1trt,
							cattrt,
							codtrt,
							valtj,
							anteriortj,
							Obslintj,
							rendt1,
							codbonodd
						FROM TARJAS1
						WHERE cc1tj='$plant' AND fechatj=#".$arrayTallyOld[0]['tally_date']."#";

	executeSql($sql);
	echo 'OK';


}elseif($_POST['type']=='delete'){

	$plant = $_POST['plant'];

	$date = explode("/", $_POST['date']);
	$retardDate = $date[1]."/".$date[0]."/".$date[2];

	$array = executeSelect("SELECT * FROM TARJAS
							WHERE cc1tj='".$plant."' 
							AND fechatj=#$retardDate#");

	if($_SESSION['profile']=='ADM' || $array[0]['stattj']=='A'){

		//RESPALDO DE TARJAS DE TRABAJADORES
		$sql = "INSERT INTO TARJAS1_HISTORIAL(cc1tj,
								fechatj,
								codtj,
								fichatj,
								nomtrabtj,
								tratotj,
								param1,
								param2,
								rut_per,
								jornadatj,
								hhtj,
								rendtj,
								cc2tj,
								cc3tj,
								cc4tj,
								cc2,
								cc3,
								cc4,
								cc1trt,
								cattrt,
								codtrt,
								valtj,
								anteriortj,
								Obslintj,
								rendt1,
								codbonodd)
						SELECT cc1tj,
								fechatj,
								codtj,
								fichatj,
								nomtrabtj,
								tratotj,
								param1,
								param2,
								rut_per,
								jornadatj,
								hhtj,
								rendtj,
								cc2tj,
								cc3tj,
								cc4tj,
								cc2,
								cc3,
								cc4,
								cc1trt,
								cattrt,
								codtrt,
								valtj,
								anteriortj,
								Obslintj,
								rendt1,
								codbonodd
							FROM TARJAS1
							WHERE cc1tj='".$plant."' 
							AND fechatj=#$retardDate#";
		executeSql($sql);

		executeSql("DELETE FROM TARJAS1
					WHERE cc1tj='".$plant."' 
					AND fechatj=#$retardDate#");

		executeSql("DELETE FROM TARJAS
					WHERE cc1tj='".$plant."' 
					AND fechatj=#$retardDate#");

		echo 'OK';
	}elseif($_SESSION['profile']!='ADM'){
		echo 'NO_ADMIN';
	}

}elseif($_POST['type']=='savePersonal'){
	$plant = $_POST['plant'];
	$month = $_POST['month'];
	$year = $_POST['year'];
	$rut = $_POST['rut'];
	$name = $_POST['name'];

	/*$date = explode("/", $_POST['date']);
	$retardDate = $date[1]."/".$date[0]."/".$date[2];*/

	$list = $_POST['list'];

	/*executeSql("DELETE FROM TARJAS
				WHERE cc1tj='".$plant."' 
				AND fechatj=#$retardDate#");*/

	/*$arrayOriginalTally = executeSelect("SELECT * FROM TARJAS
										WHERE cc1tj='".$plant."' 
										AND fechatj=#$retardDate#");*/

	//RESPALDO DE TARJAS DE TRABAJADORES
	$sql = "INSERT INTO TARJAS1_HISTORIAL(cc1tj,
							fechatj,
							codtj,
							fichatj,
							nomtrabtj,
							tratotj,
							param1,
							param2,
							rut_per,
							jornadatj,
							hhtj,
							rendtj,
							cc2tj,
							cc3tj,
							cc4tj,
							cc2,
							cc3,
							cc4,
							cc1trt,
							cattrt,
							codtrt,
							valtj,
							anteriortj,
							Obslintj,
							rendt1,
							codbonodd)
					SELECT cc1tj,
							fechatj,
							codtj,
							fichatj,
							nomtrabtj,
							tratotj,
							param1,
							param2,
							rut_per,
							jornadatj,
							hhtj,
							rendtj,
							cc2tj,
							cc3tj,
							cc4tj,
							cc2,
							cc3,
							cc4,
							cc1trt,
							cattrt,
							codtrt,
							valtj,
							anteriortj,
							Obslintj,
							rendt1,
							codbonodd
						FROM TARJAS1
						WHERE cc1tj='$plant'
						AND MONTH(fechatj)=$month
						AND YEAR(fechatj)=$year
						AND rut_per=$rut";

	executeSql($sql);

	
	$arrayList = explode('&&&&', $list);
	
	$cc1tj = $plant;
	/*$fechatj = $retardDate;
	$ultlintj = count($arrayList);
	$usremitj = $_SESSION['userId'];
	$fecemitj = date('d/m/Y');
	$timemitj = date('H:i:s');
	$usrvbtj = '';
	$fecvbtj = date('d/m/Y');
	$timvbtj = date('H:i:s');
	$obstj = '';
	$stattj = 'A';*/

	$sql = "DELETE * FROM TARJAS1
			WHERE cc1tj='$plant'
			AND MONTH(fechatj)=$month
			AND YEAR(fechatj)=$year
			AND rut_per=$rut";

	executeSql($sql);

	for($i=0;$i<count($arrayList);$i++){
		$arrayListDetail = explode('&&', $arrayList[$i]);

		/*$date = explode("/", $arrayListDetail[1]);
		$retardDate = $date[1]."/".$date[0]."/".$date[2];
		$fechatj = $retardDate;*/
		$fechatj = $arrayListDetail[1];

		$codtj = $arrayListDetail[0];
		$fichatj = $rut;
		if(strlen($fichatj)==7) $fichatj="   ".$fichatj;
		if(strlen($fichatj)==8) $fichatj="  ".$fichatj;
		if(strlen($fichatj)==9) $fichatj=" ".$fichatj;
		$nomtrabtj = $name;
		$tratotj = 0;
		$param1 = 0;
		$param2 = 0;
		$rut_per = $rut;
		$jornadatj = $arrayListDetail[7];
		$hhtj = $arrayListDetail[9];
		$rendtj = $arrayListDetail[8];
		
		$cc2tj = $arrayListDetail[2];
		$cc3tj = $arrayListDetail[3];
		$cc4tj = $arrayListDetail[4];
		$cc2 = $arrayListDetail[2];
		$cc3 = $arrayListDetail[3];
		 $cc4 = $arrayListDetail[4];
		 $cc1trt = $plant;
		 $cattrt = $arrayListDetail[5];
		 $codtrt = $arrayListDetail[6];
		$valtj = 0;
		$anteriortj = 1; //???
		$Obslintj = '';
		$rendt1 = 0;
		$codbonodd = 0;

		$sql = "INSERT INTO TARJAS1(cc1tj,
							fechatj,
							codtj,
							fichatj,
							nomtrabtj,
							tratotj,
							param1,
							param2,
							rut_per,
							jornadatj,
							hhtj,
							rendtj,
							cc2tj,
							cc3tj,
							cc4tj,
							cc2,
							cc3,
							cc4,
							cc1trt,
							cattrt,
							codtrt,
							valtj,
							anteriortj,
							Obslintj,
							rendt1,
							codbonodd)

						VALUES('".$cc1tj."',
							'".$fechatj."',
							".$codtj.",
							'".$fichatj."',
							'".$nomtrabtj."',
							".$tratotj.",
							".$param1.",
							".$param2.",
							".$rut_per.",
							".$jornadatj.",
							".$hhtj.",
							".$rendtj.",
							'".$cc2tj."',
							'".$cc3tj."',
							'".$cc4tj."',
							'".$cc2."',
							'".$cc3."',
							'".$cc4."',
							'".$cc1trt."',
							".$cattrt.",
							".$codtrt.",
							".$valtj.",
							".$anteriortj.",
							'".$Obslintj."',
							".$rendt1.",
							".$codbonodd.")";

		/*$sql = "UPDATE TARJAS1 SET
					tratotj=".$tratotj.",
					param1=".$param1.",
					param2=".$param2.",
					jornadatj=".$jornadatj.",
					hhtj=".$hhtj.",
					rendtj=".$rendtj.",
					cc2tj='".$cc2tj."',
					cc3tj='".$cc3tj."',
					cc4tj='".$cc4tj."',
					cc2='".$cc2."',
					cc3='".$cc3."',
					cc4='".$cc4."',
					cc1trt='".$cc1trt."',
					cattrt=".$cattrt.",
					codtrt=".$codtrt.",
					valtj=".$valtj.",
					anteriortj=".$anteriortj.",
					Obslintj='".$Obslintj."',
					rendt1=".$rendt1.",
					codbonodd=".$codbonodd."
				WHERE cc1tj='$plant'
				AND fechatj=#$retardDate#
				AND rut_per=$rut";*/

		executeSql($sql);
		
	}

	echo 'OK';

}

?>