<?php
header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

$type = $_POST['type'];
$id = $_POST['id'];

executeSql("DELETE FROM contract_personal WHERE id=$id");


?>