<?php
header('Content-Type: text/html; charset=utf8'); 
session_start();

if(!isset($_SESSION['userId'])){
	header('Location: ../../login.php');
}elseif($_SESSION['display']['users']['view']!=''){
	header('Location: ../../index.php');
}

?>

<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script type="text/javascript" src="../../libs/jquery-3.1.1.min.js"></script>
	<script type="text/javascript" src="../../libs/bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="../../libs/datepicker/js/bootstrap-datepicker.js"></script>
	<link rel="stylesheet" type="text/css" href="../../libs/bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="../../style/style.css">
	<link rel="stylesheet" href="../../libs/font-awesome-4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="../../libs/datepicker/css/datepicker.css">
	<link rel="stylesheet" type="text/css" href="../../libs/datatables/datatables.min.css"/>
 	<script type="text/javascript" src="../../libs/datatables/datatables.min.js"></script>
	<script type="text/javascript" src="../../libs/loadParameters.js"></script>
	<title></title>
	<style type="text/css">
		.classCenter{
			text-align: center;
		}
	</style>
	<script type="text/javascript">

	var idList = 1;

		$(document).ready(function() {
			loadMenu();
			startParameters();

			$("#modalHide").click(function() {
				$("#modal").modal('hide');	
			});

			$("#modalDeleteHide").click(function() {
				$("#modalDelete").modal('hide');	
			});

			$("#new").click(function() {
				$("#modalNew").modal('show');
			});
			$("#cancel").click(function() {
				cleanModal();
			});

			$("#btnSelectAllInsert").click(function(){
				if($(this).html()=='<i class="fa fa-square-o fa-lg fa-fw"></i>'){
					$(".insertClass").prop('checked',true);
					$(this).html('<i class="fa fa-check-square-o fa-lg fa-fw"></i>');
				}else{
					$(".insertClass").prop('checked',false);
					$(this).html('<i class="fa fa-square-o fa-lg fa-fw"></i>');
				}
			});

			$("#btnSelectAllUpdate").click(function(){
				if($(this).html()=='<i class="fa fa-square-o fa-lg fa-fw"></i>'){
					$(".updateClass").prop('checked',true);
					$(this).html('<i class="fa fa-check-square-o fa-lg fa-fw"></i>');
				}else{
					$(".updateClass").prop('checked',false);
					$(this).html('<i class="fa fa-square-o fa-lg fa-fw"></i>');
				}
			});

			$("#btnSelectAllDelete").click(function(){
				if($(this).html()=='<i class="fa fa-square-o fa-lg fa-fw"></i>'){
					$(".deleteClass").prop('checked',true);
					$(this).html('<i class="fa fa-check-square-o fa-lg fa-fw"></i>');
				}else{
					$(".deleteClass").prop('checked',false);
					$(this).html('<i class="fa fa-square-o fa-lg fa-fw"></i>');
				}
			});

			$("#btnSelectAllView").click(function(){
				if($(this).html()=='<i class="fa fa-square-o fa-lg fa-fw"></i>'){
					$(".viewClass").prop('checked',true);
					$(this).html('<i class="fa fa-check-square-o fa-lg fa-fw"></i>');
				}else{
					$(".viewClass").prop('checked',false);
					$(this).html('<i class="fa fa-square-o fa-lg fa-fw"></i>');
				}
			});

			$("#delete").click(function() {
				var id = $("#modal-delete-id").text();
				$.post('../../phps/users_Save.php', {type: 'delete', id: id}, function(data, textStatus, xhr) {
					loadData();
					$("#modalDelete").modal('hide');
					$("#modal-text").text("Registro Eliminado Satisfactoriamente");
					$("#modal").modal('show');
				});
			});

			$("#save").click(function() {
				$("#modalProgress").modal('show');
				var type="update";
				if($("#divID").css('display')=='none'){
					type='save';
				}

				var modules = "";
				$('#tableModule > tbody > tr').each(function() {
					if($(this).find(">:first-child").attr("id")!=undefined){
						contador = 0;
						modules += $(this).find(">:first-child").attr("id")+"&&";

						$(this).find('td').each(function() {
							if(contador>0){
								var cellValue = $(this).find(">:first-child").val(); //Valor de cada celda
								if($(this).find(">:first-child").prop('checked')) {
									modules+=1
								}else{
									modules+=0
								}
								modules+="&&";
							}
							contador++;
						});
						modules+="&&";
					}
				});

				var listPlant = "";
				$('.checkPlant').each(function() {
					if($(this).prop('checked')){
						listPlant += $(this).val()+'-';
					}
				});

				/*console.log(listPlant);
				$("#modalProgress").modal('hide');
				return;*/

				$.post('../../phps/users_Save.php', {
					type: type, 
					id: $('#labelID').text(), 
					name: $('#txtName').val(), 
					username: $('#txtUsername').val(), 
					password: $('#txtPassword').val(), 
					usertype: $('#listType').val(),
					modules: modules,
					listPlant: listPlant
				}, function(data, textStatus, xhr) {
					
					$("#modalProgress").modal('hide');
					if(data=='OK'){
						loadData();
						$("#modal-text").text("Almacenado");
						$("#modal").modal('show');
						cleanModal();
					}else{
						$("#modal-text").text("Nombre usuario duplicado");
						$("#modal").modal('show');
					}
				});
			});

			loadPlant();

			loadTypes();
			loadData();
			
		});

		function loadData(){
			
			$("#tableData").html('<thead><tr>' +
				'<th>ID</th>' +
				'<th>Usuario</th>' +
				'<th>Tipo</th>' +
				'<th>Editar</th>' +
				'<th>Eliminar</th>'+
				'</tr></thead>');

			$('#tableData').dataTable({
				destroy: true,
				pageLength: 50,
				language: { "url": "../../libs/datatables/language/Spanish.json"},
                ajax: {
		            "url": "../../phps/users_Load.php",
		            "type": "POST",
		            "data": {
		            	type: "all"
					},
		            "dataSrc": ""
		        },
		        /*columnDefs: [
					{
						targets: [5,6,7,8,9,10,11],
						className: 'text-right'
				    }
				],*/
                columns: [
					{"data" : "id"},
					{"data" : "name"},
					{"data" : "usertype_Name"},
					{"data" : "editar"},
					{"data" : "eliminar"}
                ],
                "fnInitComplete": function(oSettings, json) {
					$("#modalProgress").modal('hide');
			    }
            });
		}

		function editRow(id){
			$("#divID").css('display','block');
			$("#labelID").text(id);
			$("#modalNew").modal('show');
			$('#txtUsername').val(id);
			$('#txtID').prop('disabled', 'true');
			$.post('../../phps/users_Load.php', {type: "one", id: id}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				$('#txtUsername').val(data[0]['id']);
				$('#txtName').val(data[0]['name']);
				$('#listType').val(data[0]['usertype']);

				$.post('../../phps/users_Load.php', {type: "modules", id: id}, function(dataModules, textStatus, xhr) {
					var dataModules = JSON.parse(dataModules);
					var countTd = 0;
					var countInsert = true;
					var countUpdate = true;
					var countDelete = true;
					var countView = true;
					
					for(i=0;i<dataModules.length;i++){
						countTd = 0;
						$("#"+dataModules[i]["Modulo"]).parent().find('td').each(function() {
							var modData = 0;
							if(countTd==1) modData = dataModules[i]["Insertar"];
							if(countTd==2) modData = dataModules[i]["Modificar"];
							if(countTd==3) modData = dataModules[i]["Eliminar"];
							if(countTd==4) modData = dataModules[i]["Ver"];

							if(modData==1) {
								$(this).find(">:first-child").prop('checked',true);
							}else{
								if(countTd==1) countInsert = false;
								if(countTd==2) countUpdate = false;
								if(countTd==3) countDelete = false;
								if(countTd==4) countView = false;
							}
							countTd++;
							if(i==dataModules.length-1 && countTd==5){
								if(countInsert) $("#btnSelectAllInsert").html('<i class="fa fa-check-square-o fa-lg fa-fw"></i>');
								if(countUpdate) $("#btnSelectAllUpdate").html('<i class="fa fa-check-square-o fa-lg fa-fw"></i>');
								if(countDelete) $("#btnSelectAllDelete").html('<i class="fa fa-check-square-o fa-lg fa-fw"></i>');
								if(countView) $("#btnSelectAllView").html('<i class="fa fa-check-square-o fa-lg fa-fw"></i>');
							}
						});
					}

					/*$(".checkPlant").prop('checked', false);
					if(data[0]['listBranches']!=null){
						for(j=0;j<data[0]['listBranches'].length;j++){
							$("#checkPlant"+data[0]['listBranches'][j]['ID_Sucursal']).prop('checked', true);
						}
					}*/

				});
				
				$(".checkPlant").prop('checked', false);
				$.post('../../phps/users_Load.php', {type: "plants", Usr_codigo: id}, function(dataX, textStatus, xhr) {
					console.log(dataX);
					var dataX = JSON.parse(dataX);
					for(j=0;j<dataX.length;j++){
						$("#checkPlant"+dataX[j]['Pl_codigo']).prop('checked', true);
					}
				});
			});
		}

		function deleteRow(id){
			$("#modal-delete-text").text('¿Está seguro de eliminar el registro '+id+'?');
			$("#modal-delete-id").text(id);
			$("#modalDelete").modal('show');
		}

		function loadTypes(){
			$.post('../../phps/usertypes_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					$('#listType').append('<option value="'+data[i]["id"]+'">'+data[i]["id"]+' - '+data[i]["name"]+'</option>');
				}
			});
		}

		function loadPlant(){
			$("#divPlant").html('');
			$.post('../../phps/plant_Load.php', {type: "allUser"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					$("#divPlant").append('<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">'+
										'<input id="checkPlant'+data[i]["Pl_codigo"]+'" class="checkPlant" type="checkbox" value="'+data[i]["Pl_codigo"]+'">&nbsp;&nbsp;'+data[i]["PlNombre"]+'<br/>' +
										'</div>');
				}
			});
		}

		function cleanModal(){
			$("#divID").css('display','none');
			$("#modalNew").modal('hide');
			$('#txtID').val('');
			$('#txtID').prop('disabled', 'false');
			$('#txtUsername').val('');
			$('#txtPassword').val('');
			$('#txtName').val('');
			$('#listType').val(1);

			$('#tableModule > tbody > tr').each(function() {
				if($(this).find(">:first-child").attr("id")!=undefined){
					contador = 0;
					$(this).find('td').each(function() {
						if(contador>0){
							$(this).find(">:first-child").prop('checked',false);
						}
						contador++;
					});
				}			
			});

			$("#btnSelectAllInsert").html('<i class="fa fa-square-o fa-lg fa-fw"></i>');
			$("#btnSelectAllUpdate").html('<i class="fa fa-square-o fa-lg fa-fw"></i>');
			$("#btnSelectAllDelete").html('<i class="fa fa-square-o fa-lg fa-fw"></i>');
			$("#btnSelectAllView").html('<i class="fa fa-square-o fa-lg fa-fw"></i>');

			$(".checkPlant").prop('checked', false);
			$(".checkPlant").removeAttr('disabled');
		}



	</script>

</head>
<body id="body">
	<div class="container-fluid">
		<div class="row">
			<div id="menuPrincipal">
			</div>
		</div>
	</div>
	<div class="container-fluid">
		<div class="row">
			<div class="panel panel-primary">
				<div class="panel-heading"><i class="fa fa-group fa-lg fa-fw"></i>&nbsp;&nbsp; Usuarios</div>
				<div class="panel-body">
					<button id="new" class="btn btn-primary btn-sm"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>&nbsp;&nbsp; Ingresar Nuevo</button></td>			
					<br/>
					<br/>
					<div class="table-responsive">
						<table id="tableData" class="table table-hover" style="font-size: 12px;">
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>


	<div id="modal" class="modal fade" data-backdrop="static" style="z-index: 1051">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
	        	<div id="modal_body" class="modal-body">
		    	    <p id="modal-text"></p>
		      	</div>
		      	<div class="modal-footer">
		        	<!--<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>-->
		        	<button id="modalHide" type="button" class="btn btn-primary">Aceptar</button>
		      	</div>
		    </div>
		</div>
	</div>

	<div id="modalDelete" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
	        	<div class="modal-body">
		    	    <p id="modal-delete-text"></p>
		    	    <p id="modal-delete-id" style="visibility: hidden"></p>
		      	</div>
		      	<div class="modal-footer">
		        	<button id="delete" type="button" class="btn btn-danger">Eliminar</button>
		        	<button id="modalDeleteHide" type="button" class="btn btn-primary">Cancelar</button>
		      	</div>
		    </div>
		</div>
	</div>

	<div id="modalNew" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
	        	<div class="modal-body">
				   	<div id="addNew" class="container-fluid">
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
								<div class="panel panel-primary">
									<div class="panel-heading"><span class="glyphicon glyphicon-copy" aria-hidden="true"></span>&nbsp;&nbsp; Ingreso de Registro</div>
									<div class="panel-body">
										<div class="container-fluid">
											<div class="row">
												<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
										  			<label style="font-size: 12px">Nombre Usuario:</label>
					  								<input id="txtUsername" type="Name" class="form-control input-sm" maxlength="45">
												</div>
												<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
										  			<label style="font-size: 12px">Contraseña:</label>
					  								<input id="txtPassword" type="Password" class="form-control input-sm" maxlength="45">	
												</div>
												<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
										  			<label style="font-size: 12px">Nombre Completo:</label>
					  								<input id="txtName" type="Name" class="form-control input-sm" maxlength="45">
												</div>
												<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
													<label style="font-size: 12px">Tipo:</label>
													<select id="listType" class="form-control input-sm" style="width: 100%;">
													</select>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
													<label>Campos:</label>
										  			<div id="divPlant" class="row"></div>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
													<div class="panel panel-primary">
														<div class="panel-body">
															<div class="table-responsive">
																<table id="tableModule" class="table table-hover" style="font-size: 12px">
																	<tr>
																		<th>Módulo</th>
																		<th class="classCenter">
																			<button id="btnSelectAllInsert" type="button" class="btn btn-primary btn-sm"><i class="fa fa-square-o fa-lg fa-fw"></i></button>
																			<br/>Insertar
																		</th>
																		<th class="classCenter">
																			<button id="btnSelectAllUpdate" type="button" class="btn btn-primary btn-sm"><i class="fa fa-square-o fa-lg fa-fw"></i></button>
																			<br/>Editar</th>
																		<th class="classCenter">
																			<button id="btnSelectAllDelete" type="button" class="btn btn-primary btn-sm"><i class="fa fa-square-o fa-lg fa-fw"></i></button>
																			<br/>Eliminar</th>
																		<th class="classCenter">
																			<button id="btnSelectAllView" type="button" class="btn btn-primary btn-sm"><i class="fa fa-square-o fa-lg fa-fw"></i></button>
																			<br/>Ver</th>
																	</tr>
																	
																	<tr>
																		<th colspan="5" class="info">Mantención de Fichas</th>
																	</tr>
																	<tr>
																		<td id="personal">Personal</td>
																		<td class="classCenter"><input type="checkbox" class="insertClass"></td>
																		<td class="classCenter"><input type="checkbox" class="updateClass"></td>
																		<td class="classCenter"><input type="checkbox" class="deleteClass"></td>
																		<td class="classCenter"><input type="checkbox" class="viewClass"></td>
																	</tr>

																	<tr>
																		<th colspan="5" class="info">Remuneraciones</th>
																	</tr>
																	<tr>
																		<td id="remunerationBook">Libro de Remuneraciones</td>
																		<td class="classCenter"><input type="checkbox" class="insertClass"></td>
																		<td class="classCenter"><input type="checkbox" class="updateClass"></td>
																		<td class="classCenter"><input type="checkbox" class="deleteClass"></td>
																		<td class="classCenter"><input type="checkbox" class="viewClass"></td>
																	</tr>
																	<tr>
																		<td id="remuneration">Liquidaciones</td>
																		<td class="classCenter"><input type="checkbox" class="insertClass"></td>
																		<td class="classCenter"><input type="checkbox" class="updateClass"></td>
																		<td class="classCenter"><input type="checkbox" class="deleteClass"></td>
																		<td class="classCenter"><input type="checkbox" class="viewClass"></td>
																	</tr>
																	<tr>
																		<td id="settlement">Finiquitos</td>
																		<td class="classCenter"><input type="checkbox" class="insertClass"></td>
																		<td class="classCenter"><input type="checkbox" class="updateClass"></td>
																		<td class="classCenter"><input type="checkbox" class="deleteClass"></td>
																		<td class="classCenter"><input type="checkbox" class="viewClass"></td>
																	</tr>
																	<tr>
																		<td id="settlementPayment">Cta Corriente Finiquitos</td>
																		<td class="classCenter"><input type="checkbox" class="insertClass"></td>
																		<td class="classCenter"><input type="checkbox" class="updateClass"></td>
																		<td class="classCenter"><input type="checkbox" class="deleteClass"></td>
																		<td class="classCenter"><input type="checkbox" class="viewClass"></td>
																	</tr>
																	<tr>
																		<td id="vacations">Vacaciones</td>
																		<td class="classCenter"><input type="checkbox" class="insertClass"></td>
																		<td class="classCenter"><input type="checkbox" class="updateClass"></td>
																		<td class="classCenter"><input type="checkbox" class="deleteClass"></td>
																		<td class="classCenter"><input type="checkbox" class="viewClass"></td>
																	</tr>
																	<tr>
																		<td id="loan">Préstamos</td>
																		<td class="classCenter"><input type="checkbox" class="insertClass"></td>
																		<td class="classCenter"><input type="checkbox" class="updateClass"></td>
																		<td class="classCenter"><input type="checkbox" class="deleteClass"></td>
																		<td class="classCenter"><input type="checkbox" class="viewClass"></td>
																	</tr>

																	<tr>
																		<th colspan="5" class="info">Tarjas</th>
																	</tr>
																	<tr>
																		<td id="talliesBuk">Proceso de Tarjas</td>
																		<td class="classCenter"><input type="checkbox" class="insertClass"></td>
																		<td class="classCenter"><input type="checkbox" class="updateClass"></td>
																		<td class="classCenter"><input type="checkbox" class="deleteClass"></td>
																		<td class="classCenter"><input type="checkbox" class="viewClass"></td>
																	</tr>
																	<tr>
																		<td id="talliesPersonal">Tarjas por Trabajador</td>
																		<td class="classCenter"><input type="checkbox" class="insertClass"></td>
																		<td class="classCenter"><input type="checkbox" class="updateClass"></td>
																		<td class="classCenter"><input type="checkbox" class="deleteClass"></td>
																		<td class="classCenter"><input type="checkbox" class="viewClass"></td>
																	</tr>
																	<tr>
																		<td id="tallies">Carga para Buk</td>
																		<td class="classCenter"><input type="checkbox" class="insertClass"></td>
																		<td class="classCenter"><input type="checkbox" class="updateClass"></td>
																		<td class="classCenter"><input type="checkbox" class="deleteClass"></td>
																		<td class="classCenter"><input type="checkbox" class="viewClass"></td>
																	</tr>

																	<tr>
																		<th colspan="5" class="info">Personal</th>
																	</tr>
																	<tr>
																		<td id="history">Historial</td>
																		<td class="classCenter"><input type="checkbox" class="insertClass"></td>
																		<td class="classCenter"><input type="checkbox" class="updateClass"></td>
																		<td class="classCenter"><input type="checkbox" class="deleteClass"></td>
																		<td class="classCenter"><input type="checkbox" class="viewClass"></td>
																	</tr>

																	<tr>
																		<th colspan="5" class="info">Configuración</th>
																	</tr>
																	<tr>
																		<td id="holidays">Días Festivos</td>
																		<td class="classCenter"><input type="checkbox" class="insertClass"></td>
																		<td class="classCenter"><input type="checkbox" class="updateClass"></td>
																		<td class="classCenter"><input type="checkbox" class="deleteClass"></td>
																		<td class="classCenter"><input type="checkbox" class="viewClass"></td>
																	</tr>
																	<tr>
																		<td id="users">Usuarios</td>
																		<td class="classCenter"><input type="checkbox" class="insertClass"></td>
																		<td class="classCenter"><input type="checkbox" class="updateClass"></td>
																		<td class="classCenter"><input type="checkbox" class="deleteClass"></td>
																		<td class="classCenter"><input type="checkbox" class="viewClass"></td>
																	</tr>
																	<tr>
																		<td id="user_types">Tipos de Usuario</td>
																		<td class="classCenter"><input type="checkbox" class="insertClass"></td>
																		<td class="classCenter"><input type="checkbox" class="updateClass"></td>
																		<td class="classCenter"><input type="checkbox" class="deleteClass"></td>
																		<td class="classCenter"><input type="checkbox" class="viewClass"></td>
																	</tr>
																	<tr>
																		<td id="change_pass">Días Festivos</td>
																		<td class="classCenter"><input type="checkbox" class="insertClass"></td>
																		<td class="classCenter"><input type="checkbox" class="updateClass"></td>
																		<td class="classCenter"><input type="checkbox" class="deleteClass"></td>
																		<td class="classCenter"><input type="checkbox" class="viewClass"></td>
																	</tr>
																	<tr>
																		<th colspan="5" class="info">Alertas</th>
																	</tr>
																	<tr>
																		<td id="alert">Alerta Finiquitos</td>
																		<td class="classCenter"><input type="checkbox" class="insertClass"></td>
																		<td class="classCenter"><input type="checkbox" class="updateClass"></td>
																		<td class="classCenter"><input type="checkbox" class="deleteClass"></td>
																		<td class="classCenter"><input type="checkbox" class="viewClass"></td>
																	</tr>
																</table>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="row">
												<br/>
												<div id="divID" class="col-xs-4 col-sm-4 col-md-4 col-lg-4" style="display: none;">
										  			<label style="font-size: 12px">ID:</label>
										  			<label id="labelID" style="font-size: 12px"></label>
												</div>												
											</div>
										</div>
										<br/>
										<div style="text-align:right;">
											<div style="display:inline-block;"><button id="save" class="btn btn-success"><span class="glyphicon glyphicon-save" aria-hidden="true"></span>&nbsp;&nbsp; Almacenar</button></div>
											<div style="display:inline-block;"><button id="cancel" class="btn btn-primary"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span>&nbsp;&nbsp; Cancelar</button></div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
		      	</div>
		    </div>
		</div>
	</div>
</body>
</html>