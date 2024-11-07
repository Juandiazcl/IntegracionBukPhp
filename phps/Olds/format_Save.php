<?php
header('Content-Type: text/html; charset=UTF-8'); 

include("../connection/connection.php");

$type=$_POST['type'];
if($type!='delete' && $type!='copy'){
	$id=$_POST['id'];
	$name=$_POST['name'];
	$enterprise1=$_POST['enterprise1'];
	$enterprise2=$_POST['enterprise2'];
	$format_type=$_POST['format_type'];
	$state=$_POST['state'];
	$title=$_POST['title'];
	$footer1=$_POST['footer1'];
	$footer2=$_POST['footer2'];
	$firm_ok=$_POST['firm_ok'];
	$firm_url=$_POST['firm_url'];
	$font_size=$_POST['font_size'];
}

if($type=='save'){
	$count = executeSelect("SELECT COUNT(*) AS count FROM format WHERE name='$name'");
	if($count[0]["count"]==0){
		$lastId = executeSql("INSERT INTO format(name, enterprise1, enterprise2, type, title, footer1, footer2, state, firm_ok, firm_url, font_size) VALUES('$name', $enterprise1, $enterprise2, $format_type, '$title', '$footer1', '$footer2', '$state', $firm_ok, '$firm_url', $font_size)");

		saveItems($lastId);
		

		echo 'OK';
	}else{
		echo 'ERROR';
	}
}elseif($type=='update'){
	$id=$_POST['id'];
	$count = executeSelect("SELECT COUNT(*) AS count FROM format WHERE name='$name' AND NOT id=$id");
	if($count[0]["count"]==0){
		executeSql("UPDATE format 
					SET name='$name', 
					enterprise1=$enterprise1, 
					enterprise2=$enterprise2, 
					type=$format_type, 
					title='$title', 
					footer1='$footer1', 
					footer2='$footer2', 
					state='$state',
					firm_ok=$firm_ok,
					firm_url='$firm_url',
					font_size=$font_size
					WHERE id=$id");
		saveItems($id);
		echo 'OK';
	}else{
		echo 'ERROR';
	}
}elseif($type=='delete'){
	$id=$_POST['id'];
	executeSql("DELETE FROM format WHERE id=$id");

}elseif($type=='copy'){
	$id=$_POST['id'];

	$format = executeSelect("SELECT * FROM format WHERE id=$id");

	$name = $format[0]['name'];
	$enterprise1 = $format[0]['enterprise1'];
	$enterprise2 = $format[0]['enterprise2'];
	$format_type = $format[0]['type'];
	$state = $format[0]['state'];
	$title = $format[0]['title'];
	$footer1 = $format[0]['footer1'];
	$footer2 = $format[0]['footer2'];
	$firm_ok = $format[0]['firm_ok'];
	$firm_url = $format[0]['firm_url'];
	$font_size = $format[0]['font_size'];
	$lastId = executeSql("INSERT INTO format(name, enterprise1, enterprise2, type, title, footer1, footer2, state, firm_ok, firm_url, font_size) VALUES('$name', $enterprise1, $enterprise2, $format_type, '$title', '$footer1', '$footer2', '$state', $firm_ok, '$firm_url',$font_size)");

	$rows = executeSelect("SELECT * FROM format_row WHERE format_id=$id");
		
	for($i=0;$i<count($rows);$i++){
		executeSql("INSERT INTO format_row(number, text, format_id) VALUES(".$rows[$i]['number'].", '".$rows[$i]['text']."', $lastId)");
	}

	echo 'OK';

}
//echo json_encode($array);

function saveItems($id){
	executeSql("DELETE FROM format_row WHERE format_id=$id");
	$rows=$_POST['rows'];
	$itemsArray = explode("INIT",$rows);

	for($i=1;$i<count($itemsArray);$i++){//1er y último arreglo son vacíos
		$itemArray = explode("&-&",$itemsArray[$i]);
		executeSql("INSERT INTO format_row(number, text, format_id) VALUES(".$itemArray[0].", '".$itemArray[1]."', $id)");
	}
}




?>