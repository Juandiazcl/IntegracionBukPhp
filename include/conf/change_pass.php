<?php
header('Content-Type: text/html; charset=utf8'); 
session_start();

if(!isset($_SESSION['userId'])){
	header('Location: ../../login.php');
}elseif($_SESSION['display']['change_pass']['view']!=''){
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

			$("#save").click(function() {
				if($('#txtPasswordNew').val()!=''){
					if($('#txtPasswordNew').val()!='' && $('#txtPasswordNew').val()==$('#txtPasswordRepeat').val()){
						$("#modalProgress").modal('show');
						$.post('../../phps/pass_Save.php', {
							id: $('#labelID').text(), 
							password: $('#txtPassword').val(),
							newPassword: $('#txtPasswordNew').val()}, function(data, textStatus, xhr) {
							$("#modalProgress").modal('hide');
							if(data=='OK'){
								$("#modal-text").text("Almacenado");
								$("#modal").modal('show');
								$("#modalNew").modal('hide');
							}else{
								$("#modal-text").text("Contraseña actual inválida");
								$("#modal").modal('show');
							}
						});
					}else{
						$("#modal-text").text("Contraseñas no coinciden");
						$("#modal").modal('show');
					}
				}else{
					$("#modal-text").text("Contraseña nueva no puede estar en blanco");
					$("#modal").modal('show');
				}
			});
			loadRegistros();
		});

		function loadRegistros(){
			$("#divID").css('display','block');
			$("#labelID").text('<?php echo $_SESSION["userId"];?>');
			$("#txtUsername").val('<?php echo $_SESSION["userName"];?>');
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
				<div class="panel-heading"><i class="fa fa-key fa-lg fa-fw"></i>&nbsp;&nbsp; Cambiar Contraseña</div>
				<div class="panel-body">
					<div class="container-fluid">
						<div class="row">
							<div class="col-xs-0 col-sm-0 col-md-5 col-lg-5"></div>
							<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
					  			<label>Nombre Usuario:</label>
  								<input id="txtUsername" type="Name" class="form-control" maxlength="45" disabled>	
							</div>
							<div class="col-xs-0 col-sm-0 col-md-5 col-lg-5"><br/><br/><br/></div>
							<div class="col-xs-0 col-sm-0 col-md-5 col-lg-5"></div>
							<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
					  			<label>Contraseña Actual:</label>
  								<input id="txtPassword" type="Password" class="form-control" maxlength="45">	
							</div>
							<div class="col-xs-0 col-sm-0 col-md-5 col-lg-5"><br/><br/><br/></div>
							<div class="col-xs-0 col-sm-0 col-md-5 col-lg-5"></div>
							<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
					  			<label>Nueva Contraseña:</label>
  								<input id="txtPasswordNew" type="Password" class="form-control" maxlength="45">	
							</div>
							<div class="col-xs-0 col-sm-0 col-md-5 col-lg-5"><br/><br/><br/></div>
							<div class="col-xs-0 col-sm-0 col-md-5 col-lg-5"></div>
							<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
					  			<label>Repetir Nueva Contraseña:</label>
  								<input id="txtPasswordRepeat" type="Password" class="form-control" maxlength="45">	
							</div>
							<div class="col-xs-0 col-sm-0 col-md-5 col-lg-5"><br/><br/><br/></div>


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
						<!--<div style="display:inline-block;"><button id="cancel" class="btn btn-primary"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span>&nbsp;&nbsp; Cancelar</button></div>-->
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

</body>
</html>