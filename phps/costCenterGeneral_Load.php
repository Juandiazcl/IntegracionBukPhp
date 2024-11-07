<?php
//header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();

if($_POST['type']=='allCC2'){
	$array = executeSelect("SELECT * FROM CC2 ORDER BY cc2");
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}

}elseif($_POST['type']=='allCC21'){
	$cc2 = $_POST['cc2'];
	$array = executeSelect("SELECT * FROM CC21 WHERE cc2='$cc2' ORDER BY cc3");
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}

}elseif($_POST['type']=='allCC22'){
	$cc2 = $_POST['cc2'];
	$cc3 = $_POST['cc3'];
	$array = executeSelect("SELECT * FROM CC22 WHERE cc2='$cc2' AND cc3='$cc3' ORDER BY cc4");
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}

}elseif($_POST['type']=='allCC21Full'){
	$array = executeSelect("SELECT * FROM CC21 ORDER BY cc2, cc3");
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}

}elseif($_POST['type']=='allCC22Full'){
	$array = executeSelect("SELECT * FROM CC22 ORDER BY cc2,cc3,cc4");
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}

}elseif($_POST['type']=='allDeal1'){
	$cc1trt = $_POST['cc1trt'];
	$array = executeSelect("SELECT * FROM TRATOS1 WHERE VAL(cc1trt)=$cc1trt ORDER BY cattrt");
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}

}elseif($_POST['type']=='allDeal1Full'){
	$array = executeSelect("SELECT * FROM TRATOS1 ORDER BY cattrt");
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}

}elseif($_POST['type']=='allDeal11'){
	$cc1trt = $_POST['cc1trt'];
	$cattrt = $_POST['cattrt'];
	$array = executeSelect("SELECT * FROM TRATOS11 WHERE VAL(cc1trt)=$cc1trt AND cattrt=$cattrt ORDER BY codtrt");
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}

}elseif($_POST['type']=='allDeal11Full'){
	$array = executeSelect("SELECT * FROM TRATOS11 ORDER BY cattrt, codtrt");
	if(count($array)>0){
		echo json_encode(utf8ize($array));
	}else{
		echo 0;
	}
}
?>