<?php
header('Content-Type: text/html; charset=utf8'); 
session_start();

if(!isset($_SESSION['userId'])){
	header('Location: ../../login.php');
}elseif($_SESSION['display']['history']['view']!=''){
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
	<link rel="stylesheet" type="text/css" href="../../libs/bootstrap/css/bootstrap-redto.css">
	<link rel="stylesheet" type="text/css" href="../../style/style.css">
	<link rel="stylesheet" href="../../libs/font-awesome-4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="../../libs/datepicker/css/datepicker.css">
	<script type="text/javascript" src="../../libs/moment.js"></script>
	<script type="text/javascript" src="../../libs/loadParameters.js"></script>
	<script type="text/javascript" src="../../libs/jquery.mask.js"></script>
	<link rel="stylesheet" href="../../libs/dynatable/jquery.dynatable.css"></script>
	<script type="text/javascript" src="../../libs/dynatable/jquery.dynatable.js"></script>	
	<script type="text/javascript" src="../../libs/jquery.table2excel.js"></script>	
	<title></title>
	<script type="text/javascript">

	var idList = 1, calculation = false;

		$(document).ready(function() {
			$(function () {
			  $('[data-toggle="popover"]').popover()
			})

			loadMenu();
			startParameters();
			
			$(".datepickerTxt").datepicker({
				format: 'dd/mm/yyyy',
				weekStart: 1
			})
			$(".datepickerTxt").datepicker('setValue', '');

			$(".datepickerTxt").on('changeDate', function(ev) {
				$(".datepickerTxt").datepicker('hide');
			});

			/*$(".datepickerTxt").focusout(function(){
				$(".datepickerTxt").datepicker('hide');
			});*/

			$("#toPDFAll").click(function() {
				toPDFAll();
			});
			$("#toPDFOne").click(function() {
				toPDFOne();
			});

			$("#toExcel").click(function() {
				$("#tablaRegistrosExcel").table2excel({
					exclude: ".noExl",
					name: "Excel Document Name",
					filename: "Lista",
					fileext: ".xls",
					exclude_img: true,
					exclude_links: true,
					exclude_inputs: true
				});
			});

			$("#toExcelHistory").click(function() {
				$("#tableHistoryExcel").table2excel({
					exclude: ".noExl",
					name: "Excel Document Name",
					filename: "Lista",
					fileext: ".xls",
					exclude_img: true,
					exclude_links: true,
					exclude_inputs: true
				});
			});
	
			$("#modalHide").click(function() {
				$("#modal").modal('hide');	
			});

			$("#cancel").click(function() {
				cleanModal();
			});

			$("#cancelView").click(function() {
				$("#modalView").modal('hide');
				$("#txtViewRUT").val('');
				$("#txtViewName").val('');
				$("#tableHistory").html('<thead><tr><th>Empresa</th><th>Campo</th><th>AFP</th><th>Salud</th><th>Salud UF</th><th>INP</th><th>Duración</th><th>Sueldo Base</th><th>Inicio Contrato</th><th>Fin Contrato</th><!--<th>Ver Ficha</th>--></tr></thead><tbody></tbody>');
			});

			$("#listState").change(function(){
				loadRegistros();
			});
			$("#listPlant").change(function(){
				loadRegistros();
			});
			$("#listEnterprise").change(function(){
				loadRegistros();
			});

			loadPlant();
			loadEnterprise();
			loadRegistros();
		});

		function loadRegistros(){
			$("#tablaRegistros").html('<thead><tr>' +
				'<th data-dynatable-column="enterprise">Empresa</th>' +
				'<th data-dynatable-column="plant">Campo</th>' +
				'<th data-dynatable-column="rut">RUT</th>' +
				'<th data-dynatable-column="fullname">Nombre</th>' +
				'<th data-dynatable-column="status">Stat</th>' +
				'<th data-dynatable-column="contractStart">Inicio</th>' +
				'<th data-dynatable-column="contractEnd">Fin</th>' +
				'<th data-dynatable-column="view">Ver</th>' +
				'</tr></thead><tbody id="tablaRegistrosBody"></tbody>');			
				//'<th data-dynatable-column="salary">Sueldo Base</th>' +
				var plant = 98;
				if($("#listPlant").val()!=null){
					plant = $("#listPlant").val();
				}
			$.post('../../phps/history_Load.php', {type: "all", state: $("#listState").val(), plant: plant, enterprise: $("#listEnterprise").val()}, function(data, textStatus, xhr) {
				
				if(data!=0){
					var data = JSON.parse(data);
					var dynatable = $("#tablaRegistros").dynatable({
				  		dataset: {
				    		records: data
				  		}
					}).data('dynatable');
	                dynatable.settings.dataset.originalRecords = data;
	                dynatable.process();
					$(function () {//Inicializa popover
						$('[data-toggle="popover"]').popover()
					});
					var list = "";
					$("#tablaRegistrosExcel").html('<tr><th>Empresa</th><th>Campo</th><th>RUT</th><th>Apellido Paterno</th><th>Apellido Materno</th><th>Nombres</th><th>Cargo</th><th>Estado</th><th>Duración</th><th>Inicio</th><th>Fin</th></tr>');
					for(i=0;i<data.length;i++){
						list = "<tr id='id"+data[i]['rut']+"'>";
						list += "<td>"+data[i]['enterprise']+"</td>";
						list += "<td>"+data[i]['plant']+"</td>";
						list += "<td>"+data[i]['rut']+"</td>";
						list += "<td>"+data[i]['lastname1']+"</td>";
						list += "<td>"+data[i]['lastname2']+"</td>";
						list += "<td>"+data[i]['name']+"</td>";
						list += "<td>"+data[i]['charge']+"</td>";
						list += "<td>"+data[i]['status']+"</td>";
						list += "<td>"+data[i]['duration']+"</td>";
						list += "<td>"+data[i]['contractStart']+"</td>";
						list += "<td>"+data[i]['contractEnd']+"</td></tr>";
						$("#tablaRegistrosExcel").append(list);
					}
				}
			});
		}

		function viewRow(id, rut, name){
			$("#modalView").modal('show');
			$.post('../../phps/history_Load.php', {type: "one", id: id}, function(data, textStatus, xhr) {
				console.log(data);
				var data = JSON.parse(data);
				$('#txtViewRUT').val(rut);
				$('#txtViewName').val(name);

				$("#tableHistoryExcel").html('<tr><th>Registro</th><th>N° Ficha</th><th>RUT</th><th>Empresa</th><th>Campo</th><th>Apellido Paterno</th><th>Apellido Materno</th><th>Nombres</th><th>Dirección</th><th>Teléfono</th><th>Celular</th><th>Nacionalidad</th><th>Fecha Nacimiento</th><th>AFP</th><th>Salud</th><th>Salud UF</th><th>INP</th><th>Sueldo Base</th><th>Duración</th><th>Tipo</th><th>Inicio</th><th>Fin</th></tr>');
				for(i=0;i<data.length;i++){

					var list = '<tr>';
					if(data[i]['row']=='Actual'){
						list = '<tr class="info">';
					}
					list += '<td>'+data[i]['enterprise_initials']+'</td>';
					list += '<td>'+data[i]['plant']+'</td>';
					list += '<td style="text-align: right;">'+data[i]['salary']+'</td>';
					list += '<td>'+data[i]['afp']+'</td>';
					list += '<td>'+data[i]['healthSystem']+'</td>';
					list += '<td>'+data[i]['healthSystemUF']+'</td>';
					list += '<td>'+data[i]['inp']+'</td>';
					list += '<td>'+data[i]['duration']+'</td>';
					list += '<td style="text-align: center;">'+data[i]['contract_start']+'</td>';
					if(data[i]['status']=='V'){
						list += '<td style="text-align: center;">-</td>';
					}else{
						list += '<td style="text-align: center;">'+data[i]['contract_end']+'</td>';
					}
					//list += '<td style="text-align: center;">'+data[i]['article']+'</td>';
					//list += '<td><button class="btn btn-primary" onclick="viewSheet(\'one\','+data[i]['ID']+')"><i class="fa fa-folder-open-o fa-lg fa-fw"></i></button></td></tr>';
					$("#tableHistory").append(list);


					list = '<tr>';
					list += '<td>'+data[i]['row']+'</td>';
					list += '<td>'+data[i]['sheet']+'</td>';
					list += '<td>'+data[i]['rut']+'</td>';
					list += '<td>'+data[i]['enterprise_initials']+'</td>';
					list += '<td>'+data[i]['plant']+'</td>';
					list += '<td>'+data[i]['lastname1']+'</td>';
					list += '<td>'+data[i]['lastname2']+'</td>';
					list += '<td>'+data[i]['name']+'</td>';
					list += '<td>'+data[i]['address']+', '+data[i]['city']+', Comuna de '+data[i]['commune']+'</td>';
					list += '<td>'+data[i]['phone']+'</td>';
					list += '<td>'+data[i]['cellphone']+'</td>';
					list += '<td>'+data[i]['nationality']+'</td>';
					list += '<td>'+data[i]['birthdate']+'</td>';
					list += '<td>'+data[i]['afp']+'</td>';
					list += '<td>'+data[i]['healthSystem']+'</td>';
					list += '<td>'+data[i]['healthSystemUF']+'</td>';
					list += '<td>'+data[i]['inp']+'</td>';
					list += '<td>'+data[i]['salary']+'</td>';
					list += '<td>'+data[i]['duration']+'</td>';
					list += '<td>'+data[i]['work']+'</td>';
					list += '<td>'+data[i]['contract_start']+'</td>';
					list += '<td>'+data[i]['contract_end']+'</td>';
					list += '</tr>';
					$("#tableHistoryExcel").append(list);

				}

			});
		}

		function viewSheet(){
			$("#modal-text").text("En construcción");
			$("#modal").modal('show');
		}

		function loadPlant(){
			$.post('../../phps/plant_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					$("#listPlant").append('<option value="'+data[i]["Pl_codigo"]+'">'+data[i]["PlNombre"]+'</option>');
				}
				$("#listPlant").val(98);
			});
		}

		function loadEnterprise(){
			$.post('../../phps/enterprise_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				$("#listEnterprise").append('<option value="0">TODAS</option>');
				for(i=0;i<data.length;i++){
					$("#listEnterprise").append('<option value="'+data[i]["Emp_codigo"]+'">'+data[i]["EmpSigla"]+'</option>');
				}
				$("#listEnterprise").val(0);
			});
		}

		function loadEndCause(){
			$.post('../../phps/endCause_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					$("#listEndCause").append('<option value="'+data[i]["finiq_codigo"]+'">'+data[i]["finiq_codigo"]+' - '+data[i]["finiq_descrip"]+'</option>');
				}
			});
		}

		function toPDFAll(){
			var plant = 98;
			if($("#listPlant").val()!=null){
				plant = $("#listPlant").val();
			}
			window.open("history_pdf.php?type=all&state="+$("#listState").val()+"&plant="+plant+"&enterprise="+$("#listEnterprise").val());
		}

		function toPDFOne(){
			var rut = $("#txtViewRUT").val().split('-');
			window.open("history_pdf.php?type=one&id="+rut[0]);
		}

		function cleanModal(){
			$("#divID").css('display','none');
			$("#labelID").text('');
			$("#modalNew").modal('hide');
			loadRegistros();
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
				<div class="panel-heading"><i class="fa fa-history fa-lg fa-fw"></i>&nbsp;&nbsp; Historial</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<!--<button id="new" class="btn btn-primary"><i class="fa fa-user-times fa-lg fa-fw"></i>&nbsp;&nbsp; Crear Finiquitos</button></td>-->
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<label>Estado contrato:</label>
				    	    <select id="listState" class="form-control">
								<option value="T">TODOS</option>
		  						<option value="V">VIGENTE</option>
								<option value="S">FINIQUITADO</option>
							</select>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<label>Campo:</label>
				    	    <select id="listPlant" class="form-control">
							</select>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<label>Empresa:</label>
				    	    <select id="listEnterprise" class="form-control">
							</select>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<button id="toPDFAll" class="btn btn-danger">&nbsp;PDF Resumen&nbsp;<i class="fa fa-file-pdf-o fa-lg fa-fw"></i></button>
							<select class="form-control" style="visibility: hidden;"></select>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<button id="toExcel" class="btn btn-success">Exportar a Excel  <img src="../../images/excel.ico"/></button>
							<select class="form-control" style="visibility: hidden;"></select>
						</div>
					</div>	
					<br/>
					<br/>
					<div class="table-responsive">
						<table id="tablaRegistros" class="table table-hover" style="font-size: 12px;">
						</table>
						<table id="tablaRegistrosExcel" style="display: none;">
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

	<div id="modalView" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
	        	<div class="modal-body">
					<div class="panel panel-primary">
						<div class="panel-heading"><i class="fa fa-clock-o fa-lg fa-fw"></i>&nbsp;&nbsp; Ver Historial</div>
						<div class="panel-body">
							<div class="container-fluid">
								<div class="row">
									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							  			<label>RUT:</label>
		  								<input id="txtViewRUT" type="Name" class="form-control"  style="text-align: right;" disabled>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
							  			<label>Nombre:</label>
							  			<input id="txtViewName" type="Name" class="form-control" disabled>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							  			<label style="visibility: hidden;">Nombre:</label>
										<button id="toExcelHistory" class="btn btn-success">Exportar a Excel  <img src="../../images/excel.ico"/></button>
										<select class="form-control" style="visibility: hidden;"></select>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-9 col-lg-9">
							  			<label style="visibility: hidden;">Nombre:</label>
										<select class="form-control" style="visibility: hidden;"></select>
									</div>
									<!--<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
										<button id="toPDFOne" class="btn btn-danger">Exportar a PDF&nbsp;<i class="fa fa-file-pdf-o fa-lg fa-fw"></i></button>
										<select class="form-control" style="visibility: hidden;"></select>
									</div>-->

									<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">&nbsp;<br/></div>
									<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
										<div class="panel panel-primary">
											<div class="panel-body">
												<table id="tableHistory" class="table" style="font-size: 12px;">
													<thead>
														<tr>
															<th>Empresa</th>
															<th>Campo</th>
															<th>Sueldo Base</th>
															<th>AFP</th>
															<th>Salud</th>
															<th>Salud UF</th>
															<th>INP</th>
															<th>Duración</th>
															<th>Inicio Contrato</th>
															<th>Fin Contrato</th>
															<!--<th>Causa</th>-->
															<!--<th>Ver Ficha</th>-->
														</tr>
													</thead>
													<tbody>
													</tbody>
												</table>
												<table id="tableHistoryExcel" style="display: none;">
													<tr>
														<th>Registro</th>
														<th>N° Ficha</th>
														<th>RUT</th>
														<th>Empresa</th>
														<th>Campo</th>
														<th>Apellido Paterno</th>
														<th>Apellido Materno</th>
														<th>Nombres</th>
														<th>Dirección</th>
														<th>Teléfono</th>
														<th>Celular</th>
														<th>Nacionalidad</th>
														<th>Fecha Nacimiento</th>
														<th>AFP</th>
														<th>Salud</th>
														<th>Salud UF</th>
														<th>INP</th>
														<th>Sueldo Base</th>
														<th>Duración</th>
														<th>Tipo</th>
														<th>Inicio</th>
														<th>Fin</th>
													</tr>
												</table>
											</div>
										</div>
									</div>
								</div>
								<br/>
								<div style="text-align:right;">
									<div style="display:inline-block;"><button id="cancelView" class="btn btn-primary"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span>&nbsp;&nbsp; Salir</button></div>
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