<?php
header('Content-Type: text/html; charset=UTF-8'); 

include("../connection/connection.php");
session_start();
$type=$_POST['type'];

if($type=='save'){
	$contract_id=$_POST['contract_id'];
	$branch_id=$_POST['branch_id'];
	$date=$_POST['date'];
	$number = 0;
	$observation=$_POST['observation'];
	$receiver_name=$_POST['receiver_name'];
	$receiver_rut=$_POST['receiver_rut'];
	$receiver_date=$_POST['receiver_date'];
	$receiver_address=$_POST['receiver_address'];
	$receiver_plate1=$_POST['receiver_plate1'];
	$receiver_plate2=$_POST['receiver_plate2'];	
	$guide_type=$_POST['guide_type'];
	$items=$_POST['items'];

	$sql = "INSERT INTO dbo.guide(
						contract_id,
						branch_id,
						date,
						number,
						observation,
						receiver_name,
						receiver_rut,
						receiver_date,
						receiver_address,
						receiver_plate1,
						receiver_plate2,
						guide_type,
						sii_type,
						sii_type_code)
						VALUES(
						$contract_id,
						$branch_id,
						'$date',
						$number,
						'$observation',
						'$receiver_name',
						'$receiver_rut',
						'$receiver_date',
						'$receiver_address',
						'$receiver_plate1',
						'$receiver_plate2',
						'$guide_type',
						'Ventas por Efectuar',
						2)";
		
	executeSql($sql);

	$array = executeSelect("SELECT TOP 1 * FROM dbo.guide WHERE contract_id=$contract_id AND branch_id=$branch_id AND date='$date' AND observation='$observation' ORDER BY id DESC");

	if($items!=''){
		saveItems($array[0]['id'],$items);
	}

	echo $array[0]['id'];

}elseif($type=='update'){
	$id=$_POST['id'];
	$contract_id=$_POST['contract_id'];
	$branch_id=$_POST['branch_id'];
	$date=$_POST['date'];
	$observation=$_POST['observation'];
	$receiver_name=$_POST['receiver_name'];
	$receiver_rut=$_POST['receiver_rut'];
	$receiver_date=$_POST['receiver_date'];
	$receiver_address=$_POST['receiver_address'];
	$receiver_plate1=$_POST['receiver_plate1'];
	$receiver_plate2=$_POST['receiver_plate2'];
	$guide_type=$_POST['guide_type'];
	$items=$_POST['items'];

	$sql = "UPDATE dbo.guide SET 
			contract_id = $contract_id,
			branch_id = $branch_id,
			date = '$date',
			observation = '$observation',
			receiver_name = '$receiver_name',
			receiver_rut = '$receiver_rut',
			receiver_date = '$receiver_date',
			receiver_address = '$receiver_address',
			receiver_plate1 = '$receiver_plate1',
			receiver_plate2 = '$receiver_plate2',
			guide_type = '$guide_type'
			WHERE id=$id";
	executeSql($sql);

	if($items!=''){
		saveItems($id,$items);
	}

	echo $id;

}elseif($type=='delete'){
	$id=$_POST['id'];
	$count = executeSelect("SELECT COUNT(*) AS count FROM dbo.guide WHERE id=$id");
	if($count[0]["count"]==1){
		$sql = "DELETE FROM dbo.guide WHERE id=$id";
		$sqlItems = "DELETE FROM dbo.guide_item WHERE guide_id=$id";
		executeSql($sql);
		executeSql($sqlItems);
		
		echo 1;
	}else{
		echo 'ERROR';
	}
} elseif($type=='change'){
		$mes=$_POST['mes'];
		$an=$_POST['an'];
		// echo "Mes y Año ";
		// echo $mes;
		// echo $an;
		$sql = "update T0058 set MES=$mes, ANO=$an";

		//echo $sql;
		executeSql($sql);	
		echo 1;		
} elseif($type=='verify'){
	$mes=$_POST['mes'];
	$an=$_POST['an'];
	// echo "Mes y Año ";
	// echo $mes;
	// echo $an;
	$sql = "select * from T0058 where MES=$mes and ANO=$an";

	//echo $sql;
	$periodoBuk=executeSelect($sql);	
	if (count($periodoBuk)>0){
		echo 1;
	} else {
		echo 0;
	}
			
}



function saveItems($guide_id, $items){
	$arrayItems = explode('&&&&', $items);
	$itemsIds = '';
	for($i=0;$i<count($arrayItems)-1;$i++){
		$arrayItem = explode('&&',$arrayItems[$i]);

		if($arrayItem!='N'){
			if($i==0){
				$itemsIds = $arrayItem[0];
			}else{
				$itemsIds .= ",".$arrayItem[0];
			}
		}
	}
	$sqlDelete = "DELETE FROM guide_item 
				WHERE guide_id=$guide_id 
				AND NOT id IN ($itemsIds)";
	executeSql($sqlDelete);


	for($j=0;$j<count($arrayItems)-1;$j++){
		$arrayItem = explode('&&',$arrayItems[$j]);
		$sql = "";
		if($arrayItem[9]=='-'){
			$arrayItem[9] = '';
		}
		if($arrayItem[0]=='N'){
			$sql = "INSERT INTO guide_item(
					guide_id,
					product_id,
					real_product_id,
					real_product2_id,
					quarter,
					bin,
					weight,
					grade,
					price,
					observation,
					contract_item_id)
					VALUES(
					$guide_id,
					".$arrayItem[1].",
					".$arrayItem[2].",
					".$arrayItem[3].",
					'".$arrayItem[4]."',
					'".$arrayItem[5]."',
					".$arrayItem[6].",
					".$arrayItem[7].",
					".$arrayItem[8].",
					'".$arrayItem[9]."',
					".$arrayItem[10]."
				)";
		}else{
			$sql = "UPDATE guide_item SET
					product_id=".$arrayItem[1].",
					real_product_id=".$arrayItem[2].",
					real_product2_id=".$arrayItem[3].",
					quarter='".$arrayItem[4]."',
					bin='".$arrayItem[5]."',
					weight=".$arrayItem[6].",
					grade=".$arrayItem[7].",
					price=".$arrayItem[8].",
					observation='".$arrayItem[9]."',
					contract_item_id=".$arrayItem[10]."
					WHERE id=".$arrayItem[0];
		}

		executeSql($sql);
	}
}
?>

