<?php
header('Content-Type: text/html; charset=utf8'); 
session_start();

if(!isset($_SESSION['userId'])){
	header('Location: ../../login.php');
}elseif($_SESSION['display']['client']['view']!=''){
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
	<script type="text/javascript" src="../../libs/loadParameters.js"></script>
	<link rel="stylesheet" href="../../libs/dynatable/jquery.dynatable.css"></script>
	<script type="text/javascript" src="../../libs/dynatable/jquery.dynatable.js"></script>		
	<title></title>
	<script type="text/javascript">

	var idList = 1;

		$(document).ready(function() {
			loadMenu();
			startParameters();

			$("#logout").click(function() {
				$.post('../../phps/logout.php', {param: ""}, function(data, textStatus, xhr) {
					if(data==1){
						window.location="../../login.php";
					}
				});
			});	
			
			$("#modalHide").click(function() {
				$("#modal").modal('hide');	
			});

			$("#modalDeleteHide").click(function() {
				$("#modalDelete").modal('hide');	
			});

			$("#new").click(function() {
				$("#modalNew").modal('show');
			});

			$('#txtRUT').focusout(function(event) {
				$('#txtRUT').val(orderRUT($('#txtRUT').val()));
				if(verifyRUT($('#txtRUT').val())==true){
					var id = 0
					if($('#labelID').text()!=''){
						id=$('#labelID').text();
					}
					$.post('../../phps/enterprise_Load.php', {type: 'verify', id: id, rut: $('#txtRUT').val(), filter: 2}, function(data, textStatus, xhr) {
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

			$('#txtRepresentRUT').focusout(function(event) {
				$('#txtRepresentRUT').val(orderRUT($('#txtRepresentRUT').val()));
				if(verifyRUT($('#txtRepresentRUT').val())!=true){
					if($('#txtRepresentRUT').val()!=''){
						$("#modal-text").text('RUT inválido');
						$("#modal").modal('show');
					}
				}
			});

			$('#txtRepresentRUT').focus(function(event) {
				$('#txtRepresentRUT').val($('#txtRepresentRUT').val().replace('-',''));
				$('#txtRepresentRUT').val($('#txtRepresentRUT').val().replace(/\./g,''));
			});

			$("#cancel").click(function() {
				cleanModal()
			});

			$("#delete").click(function() {
				var id = $("#modal-delete-id").text();
				$.post('../../phps/enterprise_Save.php', {type: 'delete', id: id}, function(data, textStatus, xhr) {
					if(data=='OK'){
						loadRegistros();
						$("#modalDelete").modal('hide');
						$("#modal-text").text("Registro Eliminado Satisfactoriamente");
						$("#modal").modal('show');
					}else{
						$("#modalDelete").modal('hide');
						$("#modal-text").text("No puede eliminar un cliente asociado a algún contrato");
						$("#modal").modal('show');
					}
				});
			});

			$("#save").click(function() {
				$("#modalProgress").modal('show');
				var type="update";
				if($("#divID").css('display')=='none'){
					type='save';
				}

				if(verifyRUT($('#txtRUT').val())==true){
					var id = 0
					if($('#labelID').text()!=''){
						id=$('#labelID').text();
					}
					$.post('../../phps/enterprise_Load.php', {type: 'verify', id: id, rut: $('#txtRUT').val(), filter: 2}, function(data, textStatus, xhr) {
						if(data!=0){
							$("#modalProgress").modal('hide');
							$("#modal-text").text("RUT duplicado");
							$("#modal").modal('show');
							$("#modalNew").modal('show');
						}else{

							$.post('../../phps/enterprise_Save.php', {type: type, 
								filter: 2,
								id: $('#labelID').text(), 
								rut: $('#txtRUT').val(), 
								name: $('#txtName').val().toUpperCase(), 
								enterprise_type: $('#listType').val(),
								address: $('#txtAddress').val().toUpperCase(), 
								commune_id: $('#listCommune').val(),
								phone1: $('#txtPhone1').val().toUpperCase(), 
								phone2: $('#txtPhone2').val().toUpperCase(), 
								city: $('#txtCity').val().toUpperCase(), 
								legal_represent_rut: $('#txtRepresentRUT').val(), 
								legal_represent_name: $('#txtRepresentName').val().toUpperCase()}, function(data, textStatus, xhr) {
								$("#modalProgress").modal('hide');
								console.log(data);
								if(data=='OK'){
									loadRegistros();
									$("#modal-text").text("Almacenado");
									$("#modal").modal('show');
									cleanModal();
								}else{
									$("#modal-text").text("Datos de cliente duplicados");
									$("#modal").modal('show');
								}
							});			
						}
					});
				}else{
					if($('#txtRUT').val()!=''){
						$("#modal-text").text('RUT inválido');
						$("#modal").modal('show');
					}
				}
			});

			loadRegistros();
			loadTypes();
			loadRegions();
		});

		function loadRegistros(){
			//$("#tablaRegistros").html('<tr><th>ID</th><th>RUT</th><th>Nombre</th><th>Tipo</th><th>Editar</th><th>Eliminar</th></tr>');
			$("#tablaRegistros").html('<thead><tr>' +
				'<th data-dynatable-column="id">ID</th>' +
				'<th data-dynatable-column="name">Nombre</th>' +
				'<th data-dynatable-column="enterprise_type">Tipo</th>' +
				'<th data-dynatable-column="editar">Editar</th>' +
				'<th data-dynatable-column="eliminar">Eliminar</th>'+
				'</tr></thead><tbody id="tablaRegistrosBody"></tbody>');			
			$.post('../../phps/enterprise_Load.php', {type: "all", filter: 2}, function(data, textStatus, xhr) {
				if(data!=0){
					var data = JSON.parse(data);
					var dynatable = $("#tablaRegistros").dynatable({
				  		dataset: {
				    		records: data
				  		}
					}).data('dynatable');
	                dynatable.settings.dataset.originalRecords = data;
	                dynatable.process();
					/*var list = "";
					for(i=0;i<data.length;i++){
						list = "<tr id='id"+data[i]['id']+"'>";
						list += "<td>"+data[i]['id']+"</td>";
						list += "<td>"+data[i]['rut']+"</td>";
						list += "<td>"+data[i]['name']+"</td>";
						list += "<td>"+data[i]['enterprise_type']+"</td>";
						list += '<td><button class="btn btn-warning" onclick="editRow('+data[i]['id']+')"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button></td>';
						list += '<td><button id="edit" class="btn btn-danger" onclick="deleteRow('+data[i]['id']+')"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button></td><tr/>';
						$("#tablaRegistros").append(list);
					}*/
				}
			});
		}

		function editRow(id){
			$('#txtID').removeAttr('disabled');
			$("#divID").css('display','block');
			$("#labelID").text(id);
			$("#modalNew").modal('show');
			$.post('../../phps/enterprise_Load.php', {type: "one", id: id}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				$('#txtRUT').val(data[0]['rut']);
				$('#txtName').val(data[0]['name']);
				$('#listType').val(data[0]['type']);

				$('#listRegion').val(data[0]['region_id']);
				loadCommunes("selected",data[0]['commune_id'],0);

				$('#txtAddress').val(data[0]['address']);
				$('#txtPhone1').val(data[0]['phone1']);
				$('#txtPhone2').val(data[0]['phone2']);
				$('#txtCity').val(data[0]['city']);
				$('#txtRepresentRUT').val(data[0]['legal_represent_rut']);
				$('#txtRepresentName').val(data[0]['legal_represent_name']);
				
				
			});
		}

		function deleteRow(id){
			$("#modal-delete-text").text('¿Está seguro de eliminar el registro '+id+'?');
			$("#modal-delete-id").text(id);
			$("#modalDelete").modal('show');
		}

		function loadTypes(){
			$.post('../../phps/enterprise_type_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					$("#listType").append('<option value="'+data[i]["id"]+'">'+data[i]["name"]+'</option>');
				}
				$("#listType").val(2);
			});
		}


		function loadCommunes(type, id, idSector){
			if(type=="all"){
				$.post('../../phps/commune_Load.php', {type: "list", region_id: $("#listRegion").val()}, function(data, textStatus, xhr) {
					var data = JSON.parse(data);
					$("#listCommune").html('');
					for(i=0;i<data.length;i++){
						$("#listCommune").append('<option value="'+data[i]["id"]+'">'+data[i]["name"]+'</option>');
					}
				});
			}else{
				$.post('../../phps/commune_Load.php', {type: "list", region_id: $("#listRegion").val()}, function(data, textStatus, xhr) {
					var data = JSON.parse(data);
					$("#listCommune").html('');
					var selected = "";
					for(i=0;i<data.length;i++){
						if(id==data[i]["id"]) selected="selected";
						else selected="";
						$("#listCommune").append('<option value="'+data[i]["id"]+'" '+selected+'>'+data[i]["name"]+'</option>');
					}
				});
			}
		}

		function loadRegions(){
			$.post('../../phps/region_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					$("#listRegion").append('<option value="'+data[i]["id"]+'">'+data[i]["number"]+' - '+data[i]["name"]+'</option>');
				}
				loadCommunes('all',0);
			});
			$("#listRegion").change(function(){
				loadCommunes('all',0);
			});
		}

		function cleanModal(){
			$("#divID").css('display','none');
			$("#modalNew").modal('hide');
			$('#txtRUT').val('');
			$('#txtName').val('');
			$('#txtAddress').val('');
			$('#txtCity').val('');
			$('#txtPhone1').val('');
			$('#txtPhone2').val('');
			$('#txtRepresentRUT').val('');
			$('#txtRepresentName').val('');
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
			<div class="panel panel-redto">
				<div class="panel-heading"><i class="fa fa-user-secret fa-lg fa-fw"></i>&nbsp;&nbsp; Cliente</div>
				<div class="panel-body">
					<button id="new" class="btn btn-redto" <?php echo $_SESSION["display"]["client"]["insert"]; ?>><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>&nbsp;&nbsp; Ingresar Nuevo</button></td>			
					<br/>
					<br/>
					<div class="table-responsive">
						<table id="tablaRegistros" class="table table-hover">
							<tr>
								<th>ID</th>
								<th>RUT</th>
								<th>Nombre</th>
								<th>Tipo</th>
								<th>Editar</th>
								<th>Eliminar</th>
							</tr>
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
		        	<button id="modalHide" type="button" class="btn btn-redto">Aceptar</button>
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
		        	<button id="modalDeleteHide" type="button" class="btn btn-redto">Cancelar</button>
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
								<div class="panel panel-redto">
									<div class="panel-heading"><span class="glyphicon glyphicon-copy" aria-hidden="true"></span>&nbsp;&nbsp; Ingreso de Registro</div>
									<div class="panel-body">
										<div class="container-fluid">
											<div class="row">
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
										  			<label>RUT:</label>
					  								<input id="txtRUT" type="Name" class="form-control rutOnly" maxlength="45">	
												</div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
										  			<label>Nombre:</label>
					  								<input id="txtName" type="Name" class="form-control" maxlength="200">	
												</div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
										  			<label>Teléfono 1:</label>
					  								<input id="txtPhone1" type="Name" class="form-control" maxlength="45" >	
												</div>


												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4" style="display: none;">
										  			<label>Tipo:</label>
					  								<select id="listType" class="form-control" style="width: 100%;"></select>
												</div>

												<div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
										  			<label>Dirección(*):</label>
					  								<input id="txtAddress" type="Name" class="form-control mandatory" maxlength="300">
												</div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
										  			<label>Teléfono 2:</label>
					  								<input id="txtPhone2" type="Name" class="form-control" maxlength="45" >	
												</div>

												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
										  			<label>Región:</label>
					  								<select id="listRegion" class="form-control" style="width: 100%;"></select>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
										  			<label>Comuna:</label>
					  								<select id="listCommune" class="form-control" style="width: 100%;"></select>
												</div>

												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
										  			<label>Ciudad:</label>
					  								<input id="txtCity" type="Name" class="form-control" maxlength="45" >	
												</div>

												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
										  			<label>RUT Representante:</label>
					  								<input id="txtRepresentRUT" type="Name" class="form-control rutOnly" maxlength="45">	
												</div>
												<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
										  			<label>Nombre:</label>
					  								<input id="txtRepresentName" type="Name" class="form-control" maxlength="100">	
												</div>

											</div>
											<div class="row">
												<br/>
												<div id="divID" class="col-xs-4 col-sm-4 col-md-4 col-lg-4" style="display: none;">
										  			<label>ID:</label>
										  			<label id="labelID"></label>
												</div>												
											</div>
										</div>
										<br/>
										<div style="text-align:right;">
											<div style="display:inline-block;"><button id="save" class="btn btn-success"><span class="glyphicon glyphicon-save" aria-hidden="true"></span>&nbsp;&nbsp; Almacenar</button></div>
											<div style="display:inline-block;"><button id="cancel" class="btn btn-redto"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span>&nbsp;&nbsp; Cancelar</button></div>
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