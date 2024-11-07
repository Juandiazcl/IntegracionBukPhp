<?php
header('Content-Type: text/html; charset=utf8'); 

include("../connection/connection.php");

session_start();

if($_POST['type']=='all'){
	
	$filterData = $_POST['filter'];
	$filter = '';
	$filter_state = '';
	$whereOk = '';
	if($filterData!=''){
		$filter = " WHERE (p.rut LIKE '%$filterData%' OR p.name LIKE '%$filterData%' OR p.lastname1 LIKE '%$filterData%' OR 	p.lastname2 LIKE '%$filterData%') ";
	}
	if(isset($_POST['filter_state'])){
		$filterState = $_POST['filter_state'];
		if($filterState!='TODOS'){
			if($filter==''){
				$filter_state = " WHERE p.state='$filterState'";
			}else{
				$filter_state = " AND p.state='$filterState'";
			}
		}
	}

	if($_POST['expired_type']=='before_expired'){

		$arrayExpired = executeSelect("SELECT cp.*
						FROM contract_personal cp
						LEFT JOIN contract_process cpr ON cpr.contract_personal_id=cp.id
						WHERE state IN ('CONTRATADO','PREFINIQUITADO') AND
						(CASE WHEN end_type='FAENA' THEN
						CONCAT((CASE WHEN
						(CASE WHEN work2='ENERO' THEN 1 ELSE
						CASE WHEN work2='FEBRERO' THEN 2 ELSE
						CASE WHEN work2='MARZO' THEN 3 ELSE
						CASE WHEN work2='ABRIL' THEN 4 ELSE
						CASE WHEN work2='MAYO' THEN 5 ELSE
						CASE WHEN work2='JUNIO' THEN 6 ELSE
						CASE WHEN work2='JULIO' THEN 7 ELSE
						CASE WHEN work2='AGOSTO' THEN 8 ELSE
						CASE WHEN work2='SEPTIEMBRE' THEN 9 ELSE
						CASE WHEN work2='OCTUBRE' THEN 10 ELSE
						CASE WHEN work2='NOVIEMBRE' THEN 11 ELSE
						CASE WHEN work2='DICIEMBRE' THEN 12
						END END END END END END
						END END END END END END)-EXTRACT(MONTH FROM date_start)<0 THEN EXTRACT(YEAR FROM date_start)+1
						ELSE EXTRACT(YEAR FROM date_start) END),'-',
						(CASE WHEN work2='ENERO' THEN '01-31' ELSE
						CASE WHEN work2='FEBRERO' THEN '02-28' ELSE
						CASE WHEN work2='MARZO' THEN '03-31' ELSE
						CASE WHEN work2='ABRIL' THEN '04-30' ELSE
						CASE WHEN work2='MAYO' THEN '05-31' ELSE
						CASE WHEN work2='JUNIO' THEN '06-30' ELSE
						CASE WHEN work2='JULIO' THEN '07-31' ELSE
						CASE WHEN work2='AGOSTO' THEN '08-31' ELSE
						CASE WHEN work2='SEPTIEMBRE' THEN '09-30' ELSE
						CASE WHEN work2='OCTUBRE' THEN '10-31' ELSE
						CASE WHEN work2='NOVIEMBRE' THEN '11-30' ELSE
						CASE WHEN work2='DICIEMBRE' THEN '12-31'
						END END END END END END
						END END END END END END))

						ELSE date_end
						END) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 5 DAY)");
	}else{
		$arrayExpired = executeSelect("SELECT cp.*
						FROM contract_personal cp
						LEFT JOIN contract_process cpr ON cpr.contract_personal_id=cp.id
						WHERE state IN ('CONTRATADO','PREFINIQUITADO') AND
						(CASE WHEN end_type='FAENA' THEN
						CONCAT((CASE WHEN
						(CASE WHEN work2='ENERO' THEN 1 ELSE
						CASE WHEN work2='FEBRERO' THEN 2 ELSE
						CASE WHEN work2='MARZO' THEN 3 ELSE
						CASE WHEN work2='ABRIL' THEN 4 ELSE
						CASE WHEN work2='MAYO' THEN 5 ELSE
						CASE WHEN work2='JUNIO' THEN 6 ELSE
						CASE WHEN work2='JULIO' THEN 7 ELSE
						CASE WHEN work2='AGOSTO' THEN 8 ELSE
						CASE WHEN work2='SEPTIEMBRE' THEN 9 ELSE
						CASE WHEN work2='OCTUBRE' THEN 10 ELSE
						CASE WHEN work2='NOVIEMBRE' THEN 11 ELSE
						CASE WHEN work2='DICIEMBRE' THEN 12
						END END END END END END
						END END END END END END)-EXTRACT(MONTH FROM date_start)<0 THEN EXTRACT(YEAR FROM date_start)+1
						ELSE EXTRACT(YEAR FROM date_start) END),'-',
						(CASE WHEN work2='ENERO' THEN '01-31' ELSE
						CASE WHEN work2='FEBRERO' THEN '02-28' ELSE
						CASE WHEN work2='MARZO' THEN '03-31' ELSE
						CASE WHEN work2='ABRIL' THEN '04-30' ELSE
						CASE WHEN work2='MAYO' THEN '05-31' ELSE
						CASE WHEN work2='JUNIO' THEN '06-30' ELSE
						CASE WHEN work2='JULIO' THEN '07-31' ELSE
						CASE WHEN work2='AGOSTO' THEN '08-31' ELSE
						CASE WHEN work2='SEPTIEMBRE' THEN '09-30' ELSE
						CASE WHEN work2='OCTUBRE' THEN '10-31' ELSE
						CASE WHEN work2='NOVIEMBRE' THEN '11-30' ELSE
						CASE WHEN work2='DICIEMBRE' THEN '12-31'
						END END END END END END
						END END END END END END))

						ELSE date_end
						END)<=CURDATE()");
	}

	if(count($arrayExpired)>0){
		for($k=0;$k<count($arrayExpired);$k++){
			$whereOk .= $arrayExpired[$k]['personal_id'].',';
		}
	}

	if($whereOk==''){
		$whereOk = '0';
	}
	$whereOk = rtrim($whereOk, ",");
	if($filter=='' && $filter_state==''){
		$whereOk = " WHERE p.id IN ($whereOk)";
	}else{
		$whereOk = " AND p.id IN ($whereOk)";
	}



	$array = executeSelect("SELECT IFNULL(p.id,0) AS id, IFNULL(p.rut,'') AS rut, IFNULL(p.name,'') AS name, 
							IFNULL(p.lastname1,'') AS lastname1, IFNULL(p.lastname2,'') AS lastname2, 
							IFNULL(p.birthdate,'') AS birthdate, IFNULL(p.civil_status,'') AS civil_status, 
							IFNULL(p.gender,'') AS gender, IFNULL(p.address,'') AS address, IFNULL(p.turn,'') AS turn, 
							IFNULL(p.phone,'') AS phone, IFNULL(p.cellphone,'') AS cellphone, IFNULL(p.mail,'') AS mail, 
							IFNULL(p.shoe_size,'') AS shoe_size, IFNULL(p.clothing_size,'') AS clothing_size, 
							IFNULL(p.state,'') AS state, IFNULL(p.driver_license_date,0) AS driver_license_date,
							IFNULL(r.name,0) AS region_name, IFNULL(c.name,0) AS commune_name, 
							IFNULL(s.name,0) AS sector_name, IFNULL(a.name,0) AS afp_name,
							IFNULL(h.name,0) AS health_system_name, IFNULL(ch.name,0) AS charge_name,
							IFNULL(d.name,0) AS driver_license,
							CONCAT(p.lastname1,' ',p.lastname2) AS lastnames,
							CONCAT(p.name,' ',p.lastname1,' ',p.lastname2) AS fullname,
							IFNULL(p.payment_type,'') AS payment_type,
							IFNULL(p.payment_bank,'') AS payment_bank,
							IFNULL(p.payment_account,'') AS payment_account
							FROM personal p
							LEFT JOIN commune c ON c.id=p.commune_id
							LEFT JOIN region r ON r.id=c.region_id
							LEFT JOIN sector s ON s.id=p.sector_id
							LEFT JOIN afp a ON a.id=p.afp_id
							LEFT JOIN health_system h ON h.id=p.health_system_id
							LEFT JOIN driver_license d ON d.id=p.driver_license_id
							LEFT JOIN charge ch ON ch.id=p.charge_id
							$filter $filter_state $whereOk
							ORDER BY p.lastname1");

	if(count($array)>0){
		for($i=0;$i<count($array);$i++){
			$contact_color="default";
			if(isset($array[$i]["contacted_state"])){
				if($array[$i]["contacted_state"]=='CONTACTADO') $contact_color='success';
				if($array[$i]["contacted_state"]=='VOLVER A CONTACTAR') $contact_color='warning';
				if($array[$i]["contacted_state"]=='NO CONTACTAR') $contact_color='danger';
			}else{
				$array[$i]["contacted_state"]='NO CONTACTADO';
			}
			if(!isset($array[$i]["contacted_observation"])) $array[$i]["contacted_observation"]='';
			//$array[$i]["contact"]='<button class="btn btn-'.$contact_color.'" onclick="contactRow('.$array[$i]['id'].')"><i class="fa fa-phone fa-lg fa-fw"></i></button>';
			$array[$i]["contact"]='<a class="btn btn-'.$contact_color.'" data-toggle="popover" title="Observación" data-trigger="hover" tabindex="0" role="button" data-content="'.$array[$i]['contacted_observation'].'" data-html="true" data-placement="top" id="contactId_'.$array[$i]['id'].'" onclick="contactRow('.$array[$i]['id'].',\''.$array[$i]['contacted_state'].'\',\''.$array[$i]['contacted_observation'].'\')"><i class="fa fa-phone fa-lg fa-fw"></i></a>';
			$array[$i]["editar"]='<button class="btn btn-warning" onclick="editRow('.$array[$i]['id'].')" '.$_SESSION["display"]["personal"]["update"].'><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>';
			$array[$i]["eliminar"]='<button id="edit" class="btn btn-danger" onclick="deleteRow('.$array[$i]['id'].')" '.$_SESSION["display"]["personal"]["delete"].'><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';
			/*Estados de contacto
			- No contactado (gris)
			- Contactado (verde)
			- Volver a contactar (amarillo)
			- No contactar (rojo)
			*/
		}
		echo json_encode($array);
	}else{
		echo 0;
	}

}elseif($_POST['type']=='one'){
	$array = executeSelect("SELECT p.*, r.id AS region_id
							FROM personal p
							LEFT JOIN commune c ON c.id=p.commune_id
							LEFT JOIN region r ON r.id=c.region_id
							WHERE p.id=".$_POST['id']);

	if(count($array)>0){
		echo json_encode($array);
	}else{
		echo 0;
	}


}elseif($_POST['type']=='verifyPersonal'){
	$array = executeSelect("SELECT * FROM personal WHERE rut='".$_POST['rut']."' AND NOT id=".$_POST['id']);


}elseif($_POST['type']=='verify'){
	$array = executeSelect("SELECT * FROM personal WHERE rut='".$_POST['rut']."'");
	if(count($array)>0){
		$arrayPersonal = executeSelect("SELECT * FROM contract_personal WHERE personal_id=".$array[0]['id']." AND NOT id=".$_POST['id']);

		if(count($arrayPersonal)>0){
			if($arrayPersonal[0]['contract_id']==$_POST['contractId']){
				echo 2;
			}else{
				echo 3;
			}
		}else{
			echo json_encode($array); //1
		}
	}else{
		echo 0;
	}

}

?>