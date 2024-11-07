<?php
header('Content-Type: text/html; charset=utf8'); 
session_start();

if(!isset($_SESSION['userId'])){
	header('Location: ../../login.php');
}elseif($_SESSION['display']['remunerationBook']['view']!=''){
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
	<script type="text/javascript" src="../../libs/moment.js"></script>
	<script type="text/javascript" src="../../libs/loadParameters.js"></script>
	<script type="text/javascript" src="../../libs/jquery.mask.js"></script>
	<link rel="stylesheet" href="../../libs/dynatable/jquery.dynatable.css"></script>
	<script type="text/javascript" src="../../libs/dynatable/jquery.dynatable.js"></script>	
	<script type="text/javascript" src="../../libs/jquery.table2excel.js"></script>	
	<script type="text/javascript" src="../../libs/bootstrap_multiselect/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../../libs/bootstrap_multiselect/css/bootstrap-multiselect.css">
	<title></title>
	<script type="text/javascript">

	var idList = 1;

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

			$(".datepickerTxt").focusout(function(){
				$(".datepickerTxt").datepicker('hide');
			});

			$("#btnFilter").click(function(event) {
				loadData();
			});			

			$("#toExcel").click(function() {
				//$("#tableDataExcel").table2excel({
				$("#tableData").table2excel({
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

			$('#listPaymentMode').change(function(event) {
				if($(this).val()=='CHEQUE'){
					$("#divBank").css('display', 'none');
					$("#divBankAccount").css('display', 'none');
					$("#txtBank").val('');
					$("#txtBankAccount").val('');
					$("#txtBankAccount").prop('disabled','false');

				}else if($(this).val()=='CUENTA RUT'){
					$("#divBank").css('display', 'none');
					$("#divBankAccount").css('display', 'block');
					$("#txtBank").val('');
					$("#txtBankAccount").val($("#txtRUT").val());
					$("#txtBankAccount").removeAttr('disabled');
					//$("#txtBankAccount").prop('disabled','true');

				}else if($(this).val()=='OTRA CUENTA'){
					$("#divBank").css('display', 'block');
					$("#divBankAccount").css('display', 'block');
					$("#txtBank").val('');
					$("#txtBankAccount").val('');
					$("#txtBankAccount").removeAttr('disabled');
				}
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

			$("#modalContactHide").click(function() {
				$("#listContact").val('NO CONTACTADO');
				$("#contactObservation").val('');
				$("#modalContact").modal('hide');	
			});

			$("#new").click(function() {
				$("#modalNew").modal('show');
			});

			$("#cancel").click(function() {
				cleanModal();
			});

			$("#delete").click(function() {
				var id = $("#modal-delete-id").text();
				$.post('../../phps/personal_Save.php', {type: 'delete', id: id}, function(data, textStatus, xhr) {
					if(data=='OK'){
						loadData();
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

			//loadData();
			/*loadRegions();
			loadCharges();
			loadHealthSystem();
			loadAFP();
			loadDriverLicense();*/
		});

		function loadData(){
			$("#modalProgress-text").html('<i class="fa fa-spinner fa-spin fa-2x"></i><br/>Cargando Registros');
			$("#modalProgress").modal('show');

			$.post('../../phps/remunerationBook_Load.php', {type: "all", state: $("#listState").val()}, function(data, textStatus, xhr) {
				
				console.log(data);
				if(data!=0){
					var data = JSON.parse(data);
					/*var dynatable = $("#tableData").dynatable({
				  		dataset: {
				    		records: data
				  		}
					}).data('dynatable');
	                dynatable.settings.dataset.originalRecords = data;
	                dynatable.process();
					/*$(function () {//Inicializa popover
						$('[data-toggle="popover"]').popover()
					});*/
					var list = "";
					
					for(i=0;i<data.length;i++){
						list = '<tr>' +
									'<td>'+data[i]['rutrem']+'</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>'+data[i]['RUT']+'</td>' +
									'<td>'+data[i]['Direc_per']+'</td>' +
									'<td>'+data[i]['comuna_per']+'</td>' +
									'<td>'+data[i]['ciudad_per']+'</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>'+data[i]['fecnac_per']+'</td>' +
									'<td>'+data[i]['sexo_per']+'</td>' +
									'<td>'+data[i]['desciv']+'</td>' +
									'<td>'+data[i]['nac_per']+'</td>' +
									'<td>&nbsp;</td>' +
									'<td>'+data[i]['fecing_per']+'</td>' +
									'<td>'+data[i]['fecing_per']+'</td>' +
									'<td>'+data[i]['fecing_per']+'</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>'+data[i]['Apepat_per']+'</td>' +
									'<td>'+data[i]['Apemat_per']+'</td>' +
									'<td>'+data[i]['Nom_per']+'</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>'+data[i]['cc1_per']+'</td>' +
									'<td>&nbsp;</td>' +
									'<td>'+data[i]['afp_per']+'</td>' +
									'<td>'+data[i]['porc_afp']+'</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>'+data[i]['isa_per']+'</td>' +
									'<td>'+data[i]['porc_isa_per']+'%</td>' +
									'<td>'+data[i]['peso_isa_per']+'</td>' +
									'<td>'+data[i]['uf_isa_per']+'</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>'+data[i]['porc_inp']+'</td>' +
									'<td>&nbsp;</td>' +
									'<td>'+data[i]['fecing_per']+'</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>'+data[i]['indef']+'</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
									'<td>&nbsp;</td>' +
								'</tr>';

						//$("#tableDataExcel").append(list);
						$("#tableData").append(list);
					}
					$("#modalProgress").modal('hide');
				
				}else{
					$("#modalProgress").modal('hide');
				}
			});
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
				<div class="panel-heading"><i class="fa fa-book fa-lg fa-fw"></i>&nbsp;&nbsp;Libro Remuneraciones</div>
				<div class="panel-body">	
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<select id="listYear" class="form-control">
		  						<option value="2012">2012</option>
		  						<option value="2013">2013</option>
		  						<option value="2014">2014</option>
		  						<option value="2015">2015</option>
		  						<option value="2016">2016</option>
		  						<option value="2017">2017</option>
		  						<option value="2018">2018</option>
		  						<option value="2019" selected>2019</option>
							</select>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<select id="listMonth" class="form-control">
		  						<option value="1">Enero</option>
		  						<option value="2">Febrero</option>
		  						<option value="3">Marzo</option>
		  						<option value="4">Abril</option>
		  						<option value="5">Mayo</option>
		  						<option value="6">Junio</option>
		  						<option value="7">Julio</option>
		  						<option value="8">Agosto</option>
		  						<option value="9">Septiembre</option>
		  						<option value="10" selected>Octubre</option>
		  						<option value="11">Noviembre</option>
		  						<option value="12">Diciembre</option>
							</select>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<select id="listState" class="form-control">
		  						<option value="V">Vigente</option>
		  						<option value="S">Finiquitado</option>
		  						<option value="T">Todos</option>
							</select>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<button id="btnFilter" class="btn btn-primary"><i class="fa fa-filter"></i>&nbsp;&nbsp; Filtrar</button>
						</div>
						<div class="col-xs-0 col-sm-0 col-md-4 col-lg-4"></div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<button id="toExcel" class="btn btn-success">Exportar a Excel  <img src="../../images/excel.ico"/></button>
						</div>
					</div>	
					<br/>
					<br/>
					<div class="table-responsive">
						<table id="tableData" class="table table-hover">
							<tr>
								<th>FICHA</th>
								<th>codigo del banco sucursal</th>
								<th>codigo estudios superiores</th>
								<th>codigo caja compensacion</th>
								<th>RUT</th>
								<th>Direc_per</th>
								<th>codigo comuna</th>
								<th>codigo ciudad</th>
								<th>telefono 1</th>
								<th>telefono 2</th>
								<th>telefono 3</th>
								<th>fax</th>
								<th>fecnac_per</th>
								<th>sexo</th>
								<th>estado civil</th>
								<th>nacionalidad</th>
								<th>situacion militar al dia</th>
								<th>fecha de ingreso</th>
								<th>fecha primer contrato</th>
								<th>fecha contrato vigente</th>
								<th>Código INE</th>
								<th>Tipo de pago</th>
								<th>Código de ex-caja</th>
								<th>Número de cuenta para depósito</th>
								<th>Número tarjeta control horario</th>
								<th>Tiene certificado de sueldos</th>
								<th>Tiene certificado de honorarios</th>
								<th>Tiene certificado de honorarios y participaciones</th>
								<th>APELLIDO PATERNO</th>
								<th>APELLIDO MATERNO</th>
								<th>NOMBRES</th>
								<th>Email</th>
								<th>WebSite</th>
								<th>Código de área de negocio</th>
								<th>Código de centro de costo</th>
								<th>Código de cargo</th>
								<th>Código de AFP</th>
								<th>Porcentaje de cotización AFP</th>
								<th>Porcentaje Seguro de Invalidez y Sobrevivencia + Comisión de la AFP</th>
								<th>Monto cotización voluntaria AFP en Pesos</th>
								<th>Monto cotización voluntaria AFP en UF</th>
								<th>Monto cotización voluntaria AFP en Porcentaje</th>
								<th>Código de Isapre</th>
								<th>Monto cotización Isapre (Porcentaje)</th>
								<th>Monto cotización Isapre (Pesos)</th>
								<th>Monto cotización Isapre (UF)</th>
								<th>Ficha tiene derecho al 2%</th>
								<th>Porcentaje a cotizar del 2%</th>
								<th>Monto a cotizar del 2%</th>
								<th>Porcentaje cotización INP</th>
								<th>Código régimen impositivo</th>
								<th>Fecha de inicio para el cálculo de vacaciones</th>
								<th>Total de años con otro empleador</th>
								<th>CodSucurBan</th>
								<th>Tipo de cuenta para depósito.</th>
								<th>Tipos de Vale Vista.</th>
								<th>Tipos de Efectivo.</th>
								<th>Nº de Días de Vacaciones Anuales</th>
								<th>Fecha Certificado Vac. Progresivas</th>
								<th>Fecha Término Contrato</th>
								<th>Afecto Art. 145L Código del Trabajo</th>
								<th>Anexo trabajador</th>
								<th>CAMPO</th>
								<th>ss</th>
								<th>planta_per</th>
								<th>stat_per</th>
								<th>cc1_per</th>
								<th>comuna_per</th>
								<th>hi_tpcargo</th>
								<th>afp_per</th>
								<th>isa_per</th>
								<th>sbase_per</th>
							</tr>
						</table>
 						<table id="tableDataExcel" style="display: none;">
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

	<div id="modalContact" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
	        	<div class="modal-body">
		    	    <p id="modal-contact-text"></p>
		    	    <label>Estado contacto:</label>
		    	    <select id="listContact" class="form-control">
  						<option value="NO CONTACTADO">NO CONTACTADO</option>
						<option value="CONTACTADO">CONTACTADO</option>
						<option value="VOLVER A CONTACTAR">VOLVER A CONTACTAR</option>
						<option value="NO CONTACTAR">NO CONTACTAR</option>
					</select>
					<label>Observación:</label>
					<textarea id="contactObservation" class="form-control rowText" maxlength="100"></textarea>
		      	</div>
		      	<div class="modal-footer">
		        	<button id="btnContactSave" type="button" class="btn btn-success">Almacenar</button>
		        	<button id="modalContactHide" type="button" class="btn btn-primary">Cancelar</button>
		      	</div>
		    </div>
		</div>
	</div>

	
</body>
</html>