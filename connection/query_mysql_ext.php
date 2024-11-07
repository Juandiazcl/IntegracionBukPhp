<?php

function executeSelect($query){
	$link =  mysql_connect('host.mygltda.com:3306', 'ipechicu_user', 'PentecostalChile2016');
	mysql_select_db("ipechicu_pentecostal");
	mysql_query('SET CHARACTER SET utf8');
	$result = mysql_query($query);

	$i=0;
	$array = null;

	while ($row = mysql_fetch_assoc($result)){
		$array[$i] = $row;
		$i++;
	}

	/* Cierre de conexión*/
	mysql_close($link);

	return $array;
}

function executeSql($query){
	$link =  mysql_connect('host.mygltda.com:3306', 'ipechicu_user', 'PentecostalChile2016');
	mysql_select_db("ipechicu_pentecostal");
	mysql_query('SET CHARACTER SET utf8');
	$result = mysql_query($query);
	mysql_close($link);
}


?>