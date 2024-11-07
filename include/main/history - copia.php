<?php
header('Content-Type: text/html; charset=utf8'); 
session_start();

if(!isset($_SESSION['userId'])){
	header('Location: ../../login.php');
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
			
			$('#txtCellphone').mask('00-0000000');
			$('#txtPhone').mask('00-0-0000000');

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

			$("#btnFilter").click(function(event) {
				loadRegistros();
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

			$('#txtRUT').focusout(function(event) {
				$('#txtRUT').val(orderRUT($('#txtRUT').val()));
				if(verifyRUT($('#txtRUT').val())==true){
					var id = 0
					if($('#labelID').text()!=''){
						id=$('#labelID').text();
					}
					$.post('../../phps/personal_Load.php', {type: 'verifyPersonal', id: id, rut: $('#txtRUT').val()}, function(data, textStatus, xhr) {
						if(data!=0){
							$("#modal-text").text("RUT duplicado");
							$("#modal").modal('show');
							$("#modalNew").modal('show');
						}
					});
				}else{
					if($('#txtRUT').val()!=''){
						$("#modal-text").text('RUT inválido');
						$("#modal").modal('show');
					}
				}
			});
			$('#txtRUT').focus(function(event) {
				$('#txtRUT').val($('#txtRUT').val().replace('-',''));
				$('#txtRUT').val($('#txtRUT').val().replace(/\./g,''));
			});


			$(".mandatory").keypress(function(event){
				$(this).parent().removeClass('has-error');	
			});
			
			$("#modalHide").click(function() {
				$("#modal").modal('hide');	
			});

			$("#modalDeleteHide").click(function() {
				$("#modalDelete").modal('hide');	
			});


			$("#new").click(function() {
				var count = 0;
				$('#tablaRegistros > tbody > tr').each(function() {
					if($(this).find('td').find(">:first-child").is(':checked')){
						if($($(this).children()[6]).html()=='V'){
							addRowPersonal($($(this).children()[1]).html(),$($(this).children()[2]).html(),$($(this).children()[3]).html(),$($(this).children()[4]).html(),$($(this).children()[8]).html(),$($(this).children()[9]).html());
							count++;
						}
					}
				});
				if(count>0){
					$("#modalNew").modal('show');
				}else{				
					$("#modal-text").text("Debe seleccionar al menos 1 o más trabajadores en estado vigente");
					$("#modal").modal('show');
				}
			});

			$("#cancel").click(function() {
				cleanModal();
			});

			$("#cancelView").click(function() {
				$("#modalView").modal('hide');
				$("#txtViewRUT").val('');
				$("#txtViewName").val('');
				$("#tableHistory").html('<thead><tr><th>Empresa</th><th>Campo</th><th>Sueldo Base</th><th>Inicio Contrato</th><th>Fin Contrato</th><th>Causa</th><th>Ver Ficha</th></tr></thead><tbody></tbody>');
			});

			$("#delete").click(function() {
				var id = $("#modal-delete-id").text();
				$.post('../../phps/personal_Save.php', {type: 'delete', id: id}, function(data, textStatus, xhr) {
					if(data=='OK'){
						loadRegistros();
						$("#modalDelete").modal('hide');
						$("#modal-text").text("Registro Eliminado Satisfactoriamente");
						$("#modal").modal('show');
					}else{
						$("#modalDelete").modal('hide');
						$("#modal-text").text("No puede eliminar una persona asociada a 1 o más contratos");
						$("#modal").modal('show');
					}
				});
			});

			$("#save").click(function() {
				var type="update", id=0;
				if($("#divID").css('display')=='none'){
					type='save';
				}else{
					id=$("#labelID").text();
				}
				if(calculation){
					var personalList = "";
					contador = 0;
					$('#tableSelected > tbody > tr').each(function() {
						contador = 0;
						$(this).find('td').each(function() {
							var cellValue = $(this).html(); //Valor de cada celda
							if(cellValue==""){
								personalList += "-";
							}else{
								personalList += cellValue;
							}
							if(contador<7){
								personalList += "&&";	
							}
							contador++;
						});
						personalList += "&&&&";
					});
					if(contador>0){
						$.post('../../phps/settlement_Save.php', {type: type,
							id: id,
							fecha_creacion: moment().format("DD/MM/YYYY"),
							fecha_finiquito: $("#txtFireDate").val(),
							articulo: $("#listEndCause").val(),
							personalList: personalList
						}, function(data, textStatus, xhr) {
							console.log(data);
							if($.isNumeric(data)){
								//loadRegistros();
								$("#divID").css('display','block');
								$("#labelID").text(data);
								$("#generatePDF").removeAttr('disabled');
								$("#modal-text").text("Almacenado");
								$("#modal").modal('show');
								//cleanModal();

							}else{
								$("#modal-text").text("Error");
								$("#modal").modal('show');
							}
						});
					}else{
						$("#modal-text").text("Debe ingresar 1 o más trabajadores");
						$("#modal").modal('show');
					}
				}else{
					$("#modal-text").text("Debe realizar cálculo antes de finiquitar");
					$("#modal").modal('show');
				}
			});

			$("#calculate").click(function(){
				$('#tableSelected > tbody > tr').each(function() {


					var dateStart = $($(this).children()[5]).html().split('/');
					var dateEnd = $('#txtFireDate').val().split('/');
					var start = moment([dateStart[2],dateStart[1]-1,dateStart[0]]);
					var end = moment([dateEnd[2],dateEnd[1]-1,dateEnd[0]]);
					var days = end.diff(start, 'days')+1;
					$($(this).children()[6]).html(days);
					var salaryDay = $($(this).children()[4]).html()/30;
					//if(days>=$("#txtMinDays").val()){
					//	var settlement = ((days/$("#txtMinDays").val())*1.75)*salaryDay;
					if(days>=30){
						var settlement = ((days/30)*1.75)*salaryDay;
						$($(this).children()[7]).html(parseInt(settlement));
					}else{
						$($(this).children()[7]).html(0);
					}

				});
				calculation = true;
			});

			$("#listState").change(function(){
				loadRegistros();
			});
			$("#listPlant").change(function(){
				loadRegistros();
			});

			$("#generatePDF").click(function() {
				if($("#divID").css('display')=='none'){
					$("#modal-text").text("Debe primero guardar Finiquito");
					$("#modal").modal('show');
				}else{
					generatePDF('all',$("#labelID").text());
				}
			});

			$("#btnSelectAll").click(function() {
				if($(this).text()=='Seleccionar Todo'){
					$(this).text('Borrar Selección');
					$('#tablaRegistros > tbody > tr').each(function() {
						$($(this).children()[0]).find(">:first-child").prop('checked', true);
					});
				}else{
					$(this).text('Seleccionar Todo');
					$('#tablaRegistros > tbody > tr').each(function() {
						$($(this).children()[0]).find(">:first-child").prop('checked', false);
					});
				}
			});

			loadPlant();
			loadRegistros();
			loadEndCause();
		});

		function loadRegistros(){
			$("#tablaRegistros").html('<thead><tr>' +
				'<th data-dynatable-column="enterprise">Empresa</th>' +
				'<th data-dynatable-column="plant">Campo</th>' +
				'<th data-dynatable-column="rut">RUT</th>' +
				'<th data-dynatable-column="fullname">Nombre</th>' +
				'<th data-dynatable-column="charge">Cargo/Labor</th>' +
				'<th data-dynatable-column="status">Stat</th>' +
				'<th data-dynatable-column="duration">Duración</th>' +
				'<th data-dynatable-column="salary">Sueldo Base</th>' +
				'<th data-dynatable-column="contractStart">Inicio</th>' +
				'<th data-dynatable-column="contractEnd">Fin</th>' +
				'<th data-dynatable-column="view">Ver</th>' +
				'</tr></thead><tbody id="tablaRegistrosBody"></tbody>');			

				var plant = 98;
				if($("#listPlant").val()!=null){
					plant = $("#listPlant").val();
				}
			$.post('../../phps/history_Load.php', {type: "all", state: $("#listState").val(), plant: plant}, function(data, textStatus, xhr) {
				
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

		function editRow(id){
			/*$('#txtID').removeAttr('disabled');
			$("#divID").css('display','block');
			$("#labelID").text(id);
			$("#modalNew").modal('show');
			$.post('../../phps/personal_Load.php', {type: "one", id: id}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				$("#labelState").text(data[0]['status']);
				$('#txtRUT').val(data[0]['rut']);
				$('#txtName').val(data[0]['name']);
				$('#txtLastname1').val(data[0]['lastname1']);
				$('#txtLastname2').val(data[0]['lastname2']);
				$('#txtBirthDate').val(data[0]['birthdate']);
				$('#listCivilStatus').val(data[0]['civil_status']);
			});*/
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
					list += '<td style="text-align: center;">'+data[i]['contract_start']+'</td>';
					if(data[i]['status']=='V'){
						list += '<td style="text-align: center;">-</td>';
					}else{
						list += '<td style="text-align: center;">'+data[i]['contract_end']+'</td>';
					}
					//list += '<td style="text-align: center;">'+data[i]['article']+'</td>';
					list += '<td><button class="btn btn-primary" onclick="viewSheet(\'one\','+data[i]['ID']+')"><i class="fa fa-folder-open-o fa-lg fa-fw"></i></button></td></tr>';
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

		function addRowPersonal(enterprise, plant, rut, name, salary, startDate){
			var tds = '<tr id="sel'+rut+'">';
				tds += '<td>'+enterprise+'</td>';
				tds += '<td>'+plant+'</td>';
				tds += '<td style="text-align: right;">'+rut+'</td>';
				tds += '<td>'+name+'</td>';
				tds += '<td style="text-align: right;">'+salary+'</td>';
				tds += '<td style="text-align: center;">'+startDate+'</td>';
				tds += '<td style="text-align: right;"></td>';
				tds += '<td style="text-align: right;"></td>';
				tds += '</tr>';
				$("#tableSelected").append(tds);
		}
		
		function generatePDF(type,id){
			window.open("format_pdf.php?type="+type+"&id="+id);
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

		function loadEndCause(){
			$.post('../../phps/endCause_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					$("#listEndCause").append('<option value="'+data[i]["finiq_codigo"]+'">'+data[i]["finiq_codigo"]+' - '+data[i]["finiq_descrip"]+'</option>');
				}
			});
		}

		function deleteRow(id){
			$("#modal-delete-text").text('¿Está seguro de eliminar el registro '+id+'?');
			$("#modal-delete-id").text(id);
			$("#modalDelete").modal('show');
		}

		function undoSettlement(id){
			$("#modal-delete-text").text('¿Está seguro de deshacer el último finiquito?');
			$("#modal-delete-id").text(id);
			$("#modalDelete").modal('show');
		}

		function cleanModal(){
			$("#divID").css('display','none');
			$("#labelID").text('');
			$("#labelState").text('DISPONIBLE');
			$("#modalNew").modal('hide');
			$('#generatePDF').attr('disabled', 'true');
			$('#txtFireDate').val(moment().format('DD/MM/YYYY'));
			calculation = false;
			$('#tableSelected').html('<thead><tr><th>Empresa</th><th>Campo</th><th>RUT</th><th>Nombre</th><th>Sueldo Base</th><th>Inicio Contrato</th><th>Días Trab.</th><th>Vacaciones Prop.</th></tr></thead><tbody></tbody>');
			loadRegistros();
		}

		function verifyData(){
			var result=true;
			if($("#txtName").val()==''){
				$("#txtName").parent().addClass('has-error');
				result=false;
			}
			if($("#txtLastname1").val()==''){
				$("#txtLastname1").parent().addClass('has-error');
				result=false;
			}
			if($("#txtLastname2").val()==''){
				$("#txtLastname2").parent().addClass('has-error');
				result=false;
			}
			if($("#txtAddress").val()==''){
				$("#txtAddress").parent().addClass('has-error');
				result=false;
			}
			return result;
		}

	</script>

</head>
<body id="body">
	<div class="container">
		<div class="row">
			<div id="menuPrincipal">
			</div>
		</div>
	</div>
	<div class="container">
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
						<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
							<label style="visibility: hidden;">Campo:</label>
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

	<div id="modalNew" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
	        	<div class="modal-body">
				   	<div id="addNew" class="container-fluid">
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
								<div class="panel panel-primary">
									<div class="panel-heading"><span class="glyphicon glyphicon-copy" aria-hidden="true"></span>&nbsp;&nbsp; Emisión Finiquito</div>
									<div class="panel-body">
										<div class="container-fluid">
											<div class="row">
												<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
													<label>Fecha Finiquito:</label>
					  								<div class="input-group">
														<input id="txtFireDate" type="text" class="form-control datepickerTxt">
														<span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></span>
													</div>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-9 col-lg-9">
										  			<label>Artículo - Causal:</label>
					  								<select id="listEndCause" class="form-control" style="width: 100%;">
					  								</select>
												</div>

												<!--<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
										  			<label>Vacaciones:</label>
					  								<input id="txtVacations" type="Name" class="form-control" maxlength="45">
												</div>
												<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
										  			<label>Indemnización:</label>
					  								<input id="txtCompensation" type="Name" class="form-control" maxlength="45">
												</div>
												<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
										  			<label>Otros:</label>
					  								<input id="txtOthers" type="Name" class="form-control" maxlength="45">
												</div>
												<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
										  			<label>Fórmula:</label>
					  								<input id="txtFormula" type="Name" class="form-control"  style="text-align: center;" maxlength="100" value="((DIAS/30)*1.75)*SBD" disabled>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
										  			<label>Días Mínimos:</label>
										  			<input id="txtMinDays" type="Name" class="form-control numbersOnly" value="30" style="text-align: right;">
												</div>-->
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">&nbsp;<br/></div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4" style="text-align: center;">
													<br/>
													<button id="calculate" class="btn btn-primary"><i class="fa fa-calculator fa-lg fa-fw"></i>&nbsp;&nbsp;Calcular</button>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">&nbsp;<br/></div>
												<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">&nbsp;<br/></div>
												<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
													<div class="panel panel-primary">
														<div class="panel-body">
															<table id="tableSelected" class="table" style="font-size: 12px;">
																<thead>
																	<tr>
																		<th>Empresa</th>
																		<th>Campo</th>
																		<th>RUT</th>
																		<th>Nombre</th>
																		<th>Sueldo Base</th>
																		<th>Inicio Contrato</th>
																		<th>Días Trab.</th>
																		<th>Vacaciones Prop.</th>
																	</tr>
																</thead>
																<tbody>
																</tbody>
															</table>
														</div>
													</div>
												</div>
											</div>
											<br/>
											<div class="row">
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4"></div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4" style="text-align: center;">
													<button id="save" class="btn btn-redto"><img src="../../images/fire.png"/><br/>Finiquitar</button>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">&nbsp;<br/>&nbsp;<br/>&nbsp;<br/>&nbsp;</div>

												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4"></div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4" style="text-align: center;">
													<button id="generatePDF" class="btn btn-danger" disabled><i class="fa fa-file-pdf-o fa-lg fa-fw"></i>&nbsp;&nbsp;Generar PDF</button>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4"></div>
											</div>
											<div style="text-align:right;">
												<div style="display:inline-block;"><button id="cancel" class="btn btn-primary"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span>&nbsp;&nbsp; Cancelar</button></div>
											</div>
											<div class="row">
												<div id="divID" class="col-xs-4 col-sm-4 col-md-4 col-lg-4" style="display: none;">
										  			<label>ID:</label>
										  			<label id="labelID"></label>
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
															<th>Inicio Contrato</th>
															<th>Fin Contrato</th>
															<!--<th>Causa</th>-->
															<th>Ver Ficha</th>
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