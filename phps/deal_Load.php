<?php
//header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();

if($_POST['type']=='all'){
	$where = "";
	if(isset($_POST['available'])){
		$where = "WHERE vigtrt='".$_POST['available']."'";
	}


	$array = executeSelect("SELECT * FROM TRATOS11 $where ORDER BY desctrt");
	if(count($array)>0){
		for($i=0;$i<count($array);$i++){
			$array[$i]['select'] = '<button class="btn btn-primary" onclick="selectLabour('.$array[$i]["codtrt"].',\''.$array[$i]["desctrt"].'\','.number_format($array[$i]["val1trt"],2).')"><i class="fa fa-check"></i>';
			$array[$i]['val1trt'] = number_format($array[$i]["val1trt"],2);
		}

		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}
}elseif($_POST['type']=='one'){
	$array = executeSelect("SELECT * FROM TRATOS11 WHERE codtrt='".$_POST['codtrt']."'");
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}
}

?>