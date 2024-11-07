<?php
header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();

if($_POST['type']=='all'){

	$where = "WHERE g.branch_id=0";
	$arrayBranch = executeSelect("SELECT * FROM dbo.user_branch WHERE user_id=".$_SESSION['userId']);

	for($b=0;$b<count($arrayBranch);$b++){
		if($b==0){
			$where = "WHERE g.branch_id IN (".$arrayBranch[$b]['branch_id'];
		}elseif($b==count($arrayBranch)-1){
			$where .= ",".$arrayBranch[$b]['branch_id'].")";
		}else{
			$where .= ",".$arrayBranch[$b]['branch_id'];
		}

		if(count($arrayBranch)==1){
			$where .=")";
		}
	}

	if($_POST['branch_id']!=0){
		$where = "WHERE g.branch_id=".$_POST['branch_id'];
	}
	$where .= " AND g.sii_type_code=2";

	$arrayEnterprise = executeSelect("SELECT * FROM dbo.user_enterprise WHERE user_id=".$_SESSION['userId']);

	if(count($arrayEnterprise)!=0){
		for($e=0;$e<count($arrayEnterprise);$e++){
			if($e==0){
				$where .= " AND c.enterprise_id IN (".$arrayEnterprise[$e]['enterprise_id'];
			}elseif($e==count($arrayEnterprise)-1){
				$where .= ",".$arrayEnterprise[$e]['enterprise_id'].")";
			}else{
				$where .= ",".$arrayEnterprise[$e]['enterprise_id'];
			}
			if(count($arrayEnterprise)==1){
				$where .=")";
			}
		}
	}else{
		$where .= " AND c.enterprise_id=0";
	}

	if($_POST['enterprise_id']!=0){
		$where .= " AND c.enterprise_id=".$_POST['enterprise_id'];
	}

	if($_POST['client_id']!=0){
		$where .= " AND c.client_id=".$_POST['client_id'];
	}

	if($_POST['year']!=0){
		$where .= " AND YEAR(c.date)=".$_POST['year'];
	}

	$sql = "SELECT g.*,
			c.code_internal,
			c.code_external,
			e.initials AS enterprise,
			cl.name AS client,
			b.name AS branch
			FROM dbo.guide g
			LEFT JOIN dbo.contract c ON c.id=g.contract_id
			LEFT JOIN dbo.enterprise e ON e.id=c.enterprise_id
			LEFT JOIN dbo.client cl ON cl.id=c.client_id
			LEFT JOIN dbo.branch b ON b.id=g.branch_id
			$where
			ORDER BY g.date, g.id";

	$array = executeSelect($sql);
	if(count($array)>0){
		for($i=0;$i<count($array);$i++){
			$array[$i]["edit"]='<button class="btn btn-warning" onclick="editRow(\''.$array[$i]['id'].'\')"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>';
			if(isset($array[$i]['url'])){
				$array[$i]["pdf"]='<button class="btn btn-danger" onclick="guidePDF(\''.$array[$i]['url'].'\')"><i class="fa fa-file-pdf-o fa-lg fa-fw"></i></button>';
				$array[$i]["delete"]='';

			}else{
				$array[$i]["pdf"]='<button class="btn btn-danger" title="Guía no generada" disabled><i class="fa fa-file-pdf-o fa-lg fa-fw"></i></button>';
				$array[$i]["delete"]='<button class="btn btn-danger" onclick="deleteRow(\''.$array[$i]['id'].'\')"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';
			}
		}
		echo json_encode($array);
	}else{
		echo 0;
	}	

}elseif($_POST['type']=='one'){
	$sql = "SELECT g.*,
			e.bsale_token AS token,
			c.client_id
			FROM dbo.guide g
			LEFT JOIN dbo.contract c ON c.id=g.contract_id
			LEFT JOIN dbo.enterprise e ON e.id=c.enterprise_id
			LEFT JOIN dbo.branch b ON b.id=g.branch_id
			WHERE g.id=".$_POST['id'];

	$array = executeSelect($sql);

	if(count($array)>0){
		echo json_encode($array);
	}else{
		echo 0;
	}	

}elseif($_POST['type']=='allItems'){
	$guide_id = $_POST['guide_id'];

	$array = executeSelect("SELECT * FROM dbo.guide_item WHERE guide_id=$guide_id ORDER BY id");

	if(count($array)>0){
		for($i=0;$i<count($array);$i++){
			$array[$i]["total"]=$array[$i]['weight']*$array[$i]['price'];
			if($array[$i]["total"]>intval($array[$i]["total"])){
				$array[$i]["total"] = number_format($array[$i]['total'], 2,',','.');
			}else{
				$array[$i]["total"] = number_format($array[$i]['total'], 0,',','.');
			}

			$array[$i]["weight"]=number_format($array[$i]['weight'], 0,'','.');
			$array[$i]["grade"]=number_format($array[$i]['grade'], 2,',','.');
			
			if($array[$i]["price"]>intval($array[$i]["price"])){
				$array[$i]["price"] = number_format($array[$i]['price'], 2,',','.');
			}else{
				$array[$i]["price"] = number_format($array[$i]['price'], 0,',','.');
			}

			$array[$i]["delete"]='<button class="btn btn-danger" onclick="deleteItem(this)"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';
		}
		echo json_encode($array);
	}else{
		echo 0;
	}	

}elseif($_POST['type']=='oneItem'){
	$id = $_POST['id'];

	$array = executeSelect("SELECT ci.*,
							0 dispatched
							FROM dbo.contract_item ci
							WHERE ci.id=$id
							ORDER BY ci.id");

	if(count($array)>0){
		echo json_encode($array);
	}else{
		echo 0;
	}	

}elseif($_POST['type']=='year'){
	$array = executeSelect("SELECT YEAR(date) AS year FROM dbo.contract GROUP BY YEAR(date)");

	if(count($array)>0){
		echo json_encode($array);
	}else{
		echo 0;
	}	

}elseif($_POST['type']=='excel'){

	/*$dateStart = $_POST['dateStart'];
	$dateEnd = $_POST['dateEnd'];*/
	$where = '';
	if($_POST['enterprise_id']!=0){
		if($where==''){
			$where = "WHERE c.enterprise_id=".$_POST['enterprise_id'];
		}else{
			$where .= " AND c.enterprise_id=".$_POST['enterprise_id'];
		}
	}
	if($_POST['client_id']!=0){
		if($where==''){
			$where = "WHERE c.client_id=".$_POST['client_id'];
		}else{
			$where .= " AND c.client_id=".$_POST['client_id'];
		}
	}
	if($_POST['branch_id']!=0){
		if($where==''){
			$where = "WHERE c.branch_id=".$_POST['branch_id'];
		}else{
			$where .= " AND c.branch_id=".$_POST['branch_id'];
		}
	}
	if($_POST['year']!=0){
		if($where==''){
			$where = "WHERE YEAR(c.date)=".$_POST['year'];
		}else{
			$where .= " AND YEAR(c.date)=".$_POST['year'];
		}
	}


	$sql = "SELECT 
			c.date,
			c.code_internal,
			e.name AS enterprise,
			b.name AS branch,
			cl.name AS client,
			p.name AS product,
			ci.weight,
			ci.hectare,
			0 AS dispatched,
			c.code_external,
			ct.name AS contract_type,
			cc.name AS contract_condition
			FROM dbo.contract_item ci
			LEFT JOIN dbo.contract c ON c.id=ci.contract_id
			LEFT JOIN dbo.enterprise e ON e.id=c.enterprise_id
			LEFT JOIN dbo.client cl ON cl.id=c.client_id
			LEFT JOIN dbo.branch b ON b.id=c.branch_id
			LEFT JOIN dbo.contract_type ct ON ct.id=c.contract_type_id
			LEFT JOIN dbo.contract_condition cc ON cc.id=c.contract_condition_id
			LEFT JOIN dbo.product p ON p.id=ci.product_id
			$where
			ORDER BY c.date";

	$array = executeSelect($sql);
	if(count($array)>0){
		echo json_encode($array);
	}else{
		echo 0;
	}	
}	

?>