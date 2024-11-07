
<?php
header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();

if($_POST['type']=='all'){
	// $filter = $_POST['filter'];

	// $filterString = " WHERE e.type=$filter";

	$array = executeSelect("SELECT EmpSigla
							FROM t0009 order by EmpSigla");
 echo count($array);    
 echo "Multiricachon";                       
	if(count($array)>0){
		for($i=0;$i<count($array);$i++){
            //ECHO $array[$i]["EmpSigla"];
			//  $array[$i]["editar"]='<button class="btn btn-warning" onclick="editRow('.$array[$i]['id'].')" '.$_SESSION["display"]["enterprise"]["update"].'><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>';
			//  $array[$i]["eliminar"]='<button id="edit" class="btn btn-danger" onclick="deleteRow('.$array[$i]['id'].')" '.$_SESSION["display"]["enterprise"]["delete"].'><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';
		}
		echo json_encode($array);
	}else{
		echo 0;
	}	
}

    ?>  