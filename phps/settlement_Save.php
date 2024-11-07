<?php
header('Content-Type: text/html; charset=UTF-8'); 

include("../connection/connection.php");

$type=$_POST['type'];
if($type!='delete' && $type!='updateAdvice'){
	$fecha_creacion=$_POST['fecha_creacion'];
	$fecha_finiquito=$_POST['fecha_finiquito'];
	$articulo=$_POST['articulo'];
}

if($type=='save'){
	executeSql("INSERT INTO FINIQUITO(fecha_creacion, fecha_finiquito, articulo) VALUES('$fecha_creacion', '$fecha_finiquito', '$articulo')");
	$array = executeSelect("SELECT MAX(ID) AS lastID FROM FINIQUITO");
	//savePersonal($array[0]['lastID'], $fecha_finiquito);
	savePersonal($array[0]['lastID'], $fecha_finiquito, $articulo);

}elseif($type=='update'){
	$id=$_POST['id'];
	executeSql("UPDATE FINIQUITO SET fecha_creacion='$fecha_creacion', fecha_finiquito='$fecha_finiquito', articulo='$articulo' WHERE ID=$id");
	savePersonal($id, $fecha_finiquito, $articulo);

}elseif($type=='delete'){
	$id=$_POST['id'];
	$arrayData = executeSelect("SELECT * FROM PERSONAL_HISTORICO WHERE ID_FINIQUITO_PERSONAL=".$id);
	
	executeSql("UPDATE PERSONAL SET estado_per='V' WHERE rut_per=".intval($arrayData[0]['rut_per']));
	executeSql("UPDATE PRESTAMO SET ID_FINIQUITO_PERSONAL='0' WHERE ID_FINIQUITO_PERSONAL=".$id);
	executeSql("UPDATE FERIADO_PROPORCIONAL SET ID_FINIQUITO_PERSONAL='0' WHERE ID_FINIQUITO_PERSONAL=".$id);
	executeSql("DELETE FROM PERSONAL_HISTORICO WHERE ID_FINIQUITO_PERSONAL=".$id);
	executeSql("DELETE FROM FINIQUITO_PERSONAL WHERE ID=".$id);
	executeSql("UPDATE REM02 SET ID_FINIQUITO_PERSONAL=0, statrem='A' WHERE ID_FINIQUITO_PERSONAL=".$id);
	executeSql("UPDATE REM021 SET ID_FINIQUITO_PERSONAL=0 WHERE ID_FINIQUITO_PERSONAL=".$id);

	echo 'OK';

}elseif($type=='updateAdvice'){
	$id=$_POST['id'];
	$date=$_POST['date'];
	$state=$_POST['state'];
	if($date=='-'){
		executeSql("UPDATE FINIQUITO_PERSONAL SET pago_fecha=fecha_fin, pago_estado='$state' WHERE ID=$id");
	}else{
		executeSql("UPDATE FINIQUITO_PERSONAL SET pago_fecha='$date', pago_estado='$state' WHERE ID=$id");
	}
	echo 'OK';
}

function savePersonal($id, $fecha_finiquito, $articulo){

	executeSql("DELETE FROM FINIQUITO_PERSONAL WHERE ID_FINIQUITO=$id");

	$personalList = $_POST['personalList'];
	$personalArray = explode("&&&&",$personalList);

	for($i=0;$i<count($personalArray)-1;$i++){//1er y último arreglo son vacíos
		$personal = explode("&&",$personalArray[$i]);
		$rut = explode("-",$personal[2]);

		$liquidacion_fecha = explode("#",$personal[8]);
		if(count($liquidacion_fecha)==1){
			$liquidacion_fecha[0]=0;
			$liquidacion_fecha[1]=0;
		}

		$arrayData = executeSelect("SELECT * FROM PERSONAL WHERE rut_per=".$rut[0]);

		if($personal[13]=='-'){
			$personal[13]=0;
		}



		executeSql("INSERT INTO FINIQUITO_PERSONAL(ID_FINIQUITO, 
					rut, 
					fecha_inicio, 
					fecha_fin, 
					sueldo_base, 
					vacaciones_proporcionales, 
					liquidaciones,
					liquidacion_fecha,
					gratificacion,
					colacion,
					movilizacion,
					indemnizacion_servicio,
					indemnizacion_aviso,
					indemnizacion_voluntaria,
					indemnizacion_mes,
					prestamo_empresa,
					prestamo_caja,
					afc,
					cargo,
					empresa_rut,
					planta_id,
					pago_estado,
					pago_fecha) 
					VALUES($id,
					'".$rut[0]."', 
					'".$personal[5]."', 
					'".$fecha_finiquito."', 
					".$personal[9].",
					".$personal[13].", 
					".$liquidacion_fecha[1].", 
					'".$liquidacion_fecha[0]."', 
					".$personal[10].", 
					".$personal[11].", 
					".$personal[12].", 
					".$personal[14].", 
					".$personal[15].", 
					".$personal[17].", 
					".$personal[16].",
					".$personal[18].", 
					".$personal[19].", 
					".$personal[20].", 
					'".$personal[21]."', 
					".$arrayData[0]['emp_per'].",
					".$arrayData[0]['planta_per'].",
					'PENDIENTE',
					'".$fecha_finiquito."')");

		//Cambio de estado
		executeSql("UPDATE PERSONAL SET fecter_per='".$fecha_finiquito."', estado_per='S', Causa_fin_per='".$articulo."' WHERE rut_per=".$rut[0]);
		
		$arrayPersonal = executeSelect("SELECT MAX(ID) AS lastID FROM FINIQUITO_PERSONAL");
		executeSql("UPDATE PRESTAMO SET ID_FINIQUITO_PERSONAL=".$arrayPersonal[0]['lastID']." WHERE RUT='".$rut[0]."' AND NOT ID_FINIQUITO_PERSONAL>0");
		executeSql("UPDATE FERIADO_PROPORCIONAL SET ID_FINIQUITO_PERSONAL=".$arrayPersonal[0]['lastID']." WHERE Rut=".$rut[0]." AND NOT ID_FINIQUITO_PERSONAL>0");

		$rutString = $rut[0];
		if(strlen($rutString)==7) $rutString="   ".$rutString;
		if(strlen($rutString)==8) $rutString="  ".$rutString;
		if(strlen($rutString)==9) $rutString=" ".$rutString;
		executeSql("UPDATE REM02 SET ID_FINIQUITO_PERSONAL=".$arrayPersonal[0]['lastID'].", statrem='F' WHERE rutrem='".$rutString."' AND (ID_FINIQUITO_PERSONAL=0 OR ID_FINIQUITO_PERSONAL IS NULL)");
		executeSql("UPDATE REM021 SET ID_FINIQUITO_PERSONAL=".$arrayPersonal[0]['lastID'].", statrem='F' WHERE rutrem='".$rutString."' AND (ID_FINIQUITO_PERSONAL=0 OR ID_FINIQUITO_PERSONAL IS NULL)");

		executeSql("INSERT INTO PERSONAL_HISTORICO(ID_FINIQUITO_PERSONAL, 
					rut_per,dv_per,Nom_per,Apepat_per,Apemat_per,user_per,carg_ulin,cont_ulin,ficha_per,fecnac_per,nac_per,Direc_per,comuna_per,ciudad_per,fono_per,cel_per,fax_per,mail_per,escciv_per,scony_per,afp_per,isa_per,foto_per,foto_ruta,per_ucreacion,per_feccreacion,per_hracreacion,fecont_per,hi_cenco,hi_tpcargo,hi_tpcateg,tipocont_per,plazo_per,plazo2_per,user_sol_con,Causa_fin_per,fec_fin_per,Obs_fin_per,cenco_fin,user_fin_per,fec_user_fin_per,hra_user_fin_per,obs_user_fin_per,user_vbfin,fec_vbfin,hra_vbfin,obs_vbfin,sw_vbfin,monto_planUF,monto_planPE,tramo_cargfam,otrof_per,sexo_per,Cestudio,SMcodigo,fecing_per,fecpri_per,fecvig_per,fecter_per,usercrea_per,feccrea_per,hracrea_per,estado_per,RolPriv_per,diasVac_per,fecinivac_per,formpago_per,porc_isa_per,peso_isa_per,uf_isa_per,cenco_per,planta_per,caja_per,porc_inp,cotizpevol_per,cotizprevmon,stat_per,cod_banco_dep,cod_INE,num_cta_cte,num_tarjeta_horario,certif_renta_sueldo,certif_renta_hono,certif_renta_honoypart,cod_excaja,cod_areanegocio,tipo_cuenta_dep,tipo_vale_vista,tipo_efectivo,codsucurban,aa_conotroEmp,AFP1,AFP2,cc1_per,emp_per,indef,sbase_per,labor_per,Observ_per,Labortxt,preclabor,codtrt_per,unidtrt_per,Labortxt2,Labortxt3,Labortxt4,codtrt2_per,codtrt3_per,codtrt4_per,preclabor2,preclabor3,preclabor4,vactomadas_per,cta_tipo,cta_numero,cta_banco) 
					SELECT ".$arrayPersonal[0]['lastID'].",rut_per,dv_per,
					Nom_per,Apepat_per,Apemat_per,user_per,carg_ulin,cont_ulin,ficha_per,fecnac_per,nac_per,Direc_per,comuna_per,ciudad_per,fono_per,cel_per,fax_per,mail_per,escciv_per,scony_per,afp_per,isa_per,foto_per,foto_ruta,per_ucreacion,per_feccreacion,per_hracreacion,fecont_per,hi_cenco,hi_tpcargo,hi_tpcateg,tipocont_per,plazo_per,plazo2_per,user_sol_con,Causa_fin_per,fec_fin_per,Obs_fin_per,cenco_fin,user_fin_per,fec_user_fin_per,hra_user_fin_per,obs_user_fin_per,user_vbfin,fec_vbfin,hra_vbfin,obs_vbfin,sw_vbfin,monto_planUF,monto_planPE,tramo_cargfam,otrof_per,sexo_per,Cestudio,SMcodigo,fecing_per,fecpri_per,fecvig_per,fecter_per,usercrea_per,feccrea_per,hracrea_per,estado_per,RolPriv_per,diasVac_per,fecinivac_per,formpago_per,porc_isa_per,peso_isa_per,uf_isa_per,cenco_per,planta_per,caja_per,porc_inp,cotizpevol_per,cotizprevmon,stat_per,cod_banco_dep,cod_INE,num_cta_cte,num_tarjeta_horario,certif_renta_sueldo,certif_renta_hono,certif_renta_honoypart,cod_excaja,cod_areanegocio,tipo_cuenta_dep,tipo_vale_vista,tipo_efectivo,codsucurban,aa_conotroEmp,AFP1,AFP2,cc1_per,emp_per,indef,sbase_per,labor_per,Observ_per,Labortxt,preclabor,codtrt_per,unidtrt_per,Labortxt2,Labortxt3,Labortxt4,codtrt2_per,codtrt3_per,codtrt4_per,preclabor2,preclabor3,preclabor4,vactomadas_per,cta_tipo,cta_numero,cta_banco FROM PERSONAL WHERE rut_per=".$rut[0]);
	}
	echo $id;
}

?>