<?php

$enterprise = "/".intval($_POST['enterprise']);

$directorio = opendir("../files/".$enterprise); //ruta actual
$array = array();
$i = 0;


while ($archivo = readdir($directorio)) //obtenemos un archivo y luego otro sucesivamente
{
    if (is_dir($archivo)){
        //echo "[".$archivo."]<br />"; //de ser un directorio lo envolvemos entre corchetes
    }else{
     		$fileName = $archivo[8].$archivo[9].'/'.$archivo[6].$archivo[7].'/'.$archivo[2].$archivo[3].$archivo[4].$archivo[5];
   			$array[$i]["FileName"] = $fileName;
   			$array[$i]["File1"] = '<a href="../../files/'.$enterprise.'/'.$archivo.'" download>'.$archivo.'</a>';
 	    	$i++;

    }
}
echo json_encode($array);

?>