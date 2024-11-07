<?php

function executeSelect($query){
	$mysqli = new mysqli('127.0.0.1', 'root', '123','roquefort');

	if ($mysqli->connect_error) {
	    die('Connect Error (' . $mysqli->connect_errno . ') '
	            . $mysqli->connect_error);
	}else{
		mysqli_set_charset($mysqli,"utf8");
		$result = mysqli_query($mysqli,$query);
		$i=0;
		$array = null;

		while ($row = mysqli_fetch_assoc($result)){
			$array[$i] = $row;
			$i++;
		}

		mysqli_close($mysqli);
		return $array;
	}


}


function executeSql($query){
	$link = new mysqli('127.0.0.1', 'root', '123','roquefort');
	$result = mysqli_query($link,$query);
	$last_id = $link->insert_id;
	mysqli_close($link);
	return $last_id;
}