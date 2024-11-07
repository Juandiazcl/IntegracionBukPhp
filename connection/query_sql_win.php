<?php

function executeSelect($query){
	$serverName = "localhost";
	$connectionInfo = array( "Database"=>"facts");
	$conn = sqlsrv_connect($serverName,$connectionInfo);

	$stmt = sqlsrv_query($conn,$query);
	//$array = array();

	$i=0;
	$array = null;
	//while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC)) {
	while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
		$array[$i] = $row;
		$i++;
	}
	/* Cierre de conexión*/
	sqlsrv_free_stmt($stmt);
	sqlsrv_close($conn);

	return $array;
}

function executeSql($query){
	$serverName = "localhost";
	$connectionInfo = array( "Database"=>"facts");
	$conn = sqlsrv_connect($serverName,$connectionInfo);
	sqlsrv_query($conn,$query);
	sqlsrv_close($conn);
}


?>
