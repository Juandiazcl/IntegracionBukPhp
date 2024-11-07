<?php
header('Content-Type: text/html; charset=utf8'); 
session_start();

if(!isset($_SESSION['userId'])){
	header('Location: ../../login.php');
}elseif($_SESSION['display']['healthSystem']['view']!=''){
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
				$("#divID").css('display','none');
				$("#modalNew").modal('hide');
				$('#txtName').val('');
				$('#txtNumber').val('');
			});

			$("#delete").click(function() {
				var id = $("#modal-delete-id").text();
				$.post('../../phps/healthSystem_Save.php', {type: 'delete', id: id}, function(data, textStatus, xhr) {
					if(data=='OK'){
						loadRegistros();
						$("#modalDelete").modal('hide');
						$("#modal-text").text("Registro Eliminado Satisfactoriamente");
						$("#modal").modal('show');
					}else{
						$("#modalDelete").modal('hide');
						$("#modal-text").text("No puede eliminar un sistema de salud asociado a persona");
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
				$.post('../../phps/healthSystem_Save.php', {type: type, id: $('#labelID').text(), name: $('#txtName').val().toUpperCase()}, function(data, textStatus, xhr) {
					$("#modalProgress").modal('hide');
					if(data=='OK'){
						loadRegistros();
						$("#modal-text").text("Almacenado");
						$("#modal").modal('show');
						$("#modalNew").modal('hide');
						$("#divID").css('display','none');
						$('#txtName').val('');
						$('#txtNumber').val('');
					}else{
						$("#modal-text").text("Datos de sistema de salud duplicados");
						$("#modal").modal('show');
					}
				});
			});

			loadRegistros();
			
		});

		function loadRegistros(){
			//$("#tablaRegistros").html('<tr><th>ID</th><th>Nombre</th><th>Editar</th><th>Eliminar</th></tr>');
			$("#tablaRegistros").html('<thead><tr>' +
				'<th data-dynatable-column="id">ID</th>' +
				'<th data-dynatable-column="name">Nombre</th>' +
				'<th data-dynatable-column="editar">Editar</th>' +
				'<th data-dynatable-column="eliminar">Eliminar</th>'+
				'</tr></thead><tbody id="tablaRegistrosBody"></tbody>');			
			$.post('../../phps/healthSystem_Load.php', {type: "all"}, function(data, textStatus, xhr) {
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
						list += "<td>"+data[i]['name']+"</td>";
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
			$.post('../../phps/healthSystem_Load.php', {type: "one", id: id}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				$('#txtName').val(data[0]['name']);
			});
		}

		function deleteRow(id){
			$("#modal-delete-text").text('¿Está seguro de eliminar el registro '+id+'?');
			$("#modal-delete-id").text(id);
			$("#modalDelete").modal('show');
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
				<div class="panel-heading"><i class="fa fa-medkit fa-lg fa-fw"></i>&nbsp;&nbsp; Sistemas de Salud</div>
				<div class="panel-body">
					<button id="new" class="btn btn-redto" <?php echo $_SESSION["display"]["healthSystem"]["insert"]; ?>><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>&nbsp;&nbsp; Ingresar Nuevo</button></td>			
					<br/>
					<br/>
					<div class="table-responsive">
						<table id="tablaRegistros" class="table table-hover">
							<tr>
								<th>ID</th>
								<th>Nombre</th>
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
										  			<label>Nombre:</label>
					  								<input id="txtName" type="Name" class="form-control" maxlength="80">	
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