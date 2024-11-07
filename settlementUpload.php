<?php
header('Content-Type: text/html; charset=utf8'); 
?>

<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script type="text/javascript" src="libs/jquery-3.1.1.min.js"></script>
	<script type="text/javascript" src="libs/bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="libs/datepicker/js/bootstrap-datepicker.js"></script>
	<link rel="stylesheet" type="text/css" href="libs/bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="style/style.css">
	<link rel="stylesheet" href="libs/font-awesome-4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="libs/datepicker/css/datepicker.css">
	<script type="text/javascript" src="libs/moment.js"></script>
	<script type="text/javascript" src="libs/loadParameters.js"></script>
	<script type="text/javascript" src="libs/jquery.mask.js"></script>
	<script type="text/javascript" src="libs/jquery.table2excel.js"></script>
	<link rel="stylesheet" type="text/css" href="libs/datatables/datatables.min.css"/>
 	<script type="text/javascript" src="libs/datatables/datatables.min.js"></script>
	<title></title>
	<script type="text/javascript">

	var idList = 1, calculation = false;

		$(document).ready(function() {
			$(function () {
			  $('[data-toggle="popover"]').popover()
			})

			startParameters();
			
			$('#txtRUT').focusout(function(event) {
				$('#txtRUT').val(orderRUT($('#txtRUT').val().replace(/\./g,'').replace('-','')));
				if(verifyRUT($('#txtRUT').val())==true){
					$.post('phps/upload_Load.php', {type: 'verifySettlement', rut: $('#txtRUT').val()}, function(data, textStatus, xhr) {
						console.log(data);
						if(data!=0){
							var data = JSON.parse(data);
						console.log(data);
							/*$("#modal-text").text("RUT ya está registrado en sistema");
							$("#modal").modal('show');*/
							$("#lblName").text(data[0]['Nom_per']+' '+data[0]['Apepat_per']+' '+data[0]['Apemat_per']);
							var list = '';
							for(i=0;i<data.length;i++){

								if(data[i]['fileState']=='NOT EXIST'){
									list += '<option value="'+data[i]['ID_FINIQUITO']+'">Finiquito ID '+data[i]['ID_FINIQUITO']+'</option>';
								}else if(data[i]['fileState']=='PENDING'){
									list += '<option value="0">Finiquito ID '+data[i]['ID_FINIQUITO']+' ya subido, a la espera de revisión por Administrador</option>';
								}else if(data[i]['fileState']=='EXIST'){
									//list += '<option value="0">ID no está pendiente</option>';
								}

								if(i+1==data.length){
									if(list==''){
										$("#listFiniquitos").html('<option value="0">No tiene finiquitos pendientes</option>');
									}else{
										$("#listFiniquitos").html(list);
									}
								}



								/*if(data[0]['ID_FINIQUITO']!=0){
									$("#listFiniquitos").html('<option value="'+data[0]['ID_FINIQUITO']+'">Finiquito ID '+data[0]['ID_FINIQUITO']+'</option>');
									Beastars
									Adventure Time
									Konosuba
									Initial D
									Asobi Asobase
									Madoka Magia Record
								}else{
									$("#listFiniquitos").html('<option value="0">No tiene finiquitos pendientes</option>');
								}*/
							}
						}else{
							$("#modal-text").text("RUT no registrado en sistema");
							$("#lblName").text('');
							$("#listFiniquitos").html('<option value="0"></option>');
						}
					});
				}else{
					if($('#txtRUT').val()!=''){
						$("#modal-text").text('RUT no válido');
						$("#modal").modal('show');
					}
					$("#lblName").text('');
					$("#listFiniquitos").html('<option value="0"></option>');
				}
			});
			$('#txtRUT').focus(function(event) {
				$('#txtRUT').val($('#txtRUT').val().replace('-',''));
				$('#txtRUT').val($('#txtRUT').val().replace(/\./g,''));
			});


			$('#txtID').focusout(function(event) {
				if($.isNumeric($('#txtID').val())){
					$.post('phps/upload_Load.php', {type: 'verifySettlement', id: $('#txtID').val()}, function(data, textStatus, xhr) {
						console.log(data);
						if(data!=0){
							var data = JSON.parse(data);
							$("#lblName").text(data[0]['Nom_per']+' '+data[0]['Apepat_per']+' '+data[0]['Apemat_per']);
							
							if(data[0]['fileState']=='NOT EXIST'){
								$("#listFiniquitos").html('<option value="'+data[0]['ID_FINIQUITO']+'">Finiquito ID '+data[0]['ID_FINIQUITO']+'</option>');
							}else if(data[0]['fileState']=='PENDING'){
								$("#listFiniquitos").html('<option value="0">Finiquito ya subido, a la espera de revisión por Administrador</option>');
							}else if(data[0]['fileState']=='EXIST'){
								$("#listFiniquitos").html('<option value="0">ID no está pendiente</option>');
							}

						}else{
							$("#modal-text").text("ID no registrado en sistema");
							$("#lblName").text('');
							$("#listFiniquitos").html('<option value="0"></option>');
						}
					});
				}else{
					if($('#txtID').val()!=''){
						$("#modal-text").text('ID no válido');
						$("#modal").modal('show');
					}
					$("#lblName").text('');
					$("#listFiniquitos").html('<option value="0"></option>');
				}
			});

			$("#modalHide").click(function() {
				$("#modal").modal('hide');	
			});

			$("#btnUpload").click(function() {
				if($("#listFiniquitos").val()!=0){
					if($("#fileToUpload").get(0).files.length===0) {
 						$("#modal-text").text('Debe selecionar un archivo');
						$("#modal").modal('show');
						return false;
					}else{
						$("#fileID").val($("#listFiniquitos").val());
						return true;
					}
				}else{
					$("#modal-text").text('Debe ingresar un RUT o ID de finiquito válido');
					$("#modal").modal('show');
					return false;
				}
			});

		});
	
	</script>

</head>
<body id="body">
	<div class="container vertical-center">
		<div class="row">

			<div class="col-xs-0 col-sm-0 col-md-3 col-lg-3"></div>
			<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
				<div class="panel panel-primary">
					<div class="panel-heading"><i class="fa fa-upload"></i>&nbsp;&nbsp;Ingreso de archivos de Finiquito</div>
					<div class="panel-body">
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
								Ingrese el RUT o ID de Finiquito del trabajador para asociar el archivo de finiquito
								<br/>
								<br/>
							</div>
							<div class="col-xs-12 col-sm-12 col-md-1 col-lg-1">
							  	<label>RUT</label>
							</div>
							<div class="col-xs-12 col-sm-12 col-md-5 col-lg-5">
								<input id="txtRUT" type="Name" class="form-control" style="text-align: right;">
							</div>
							<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
								<label>ID Finiquito</label>
							</div>
							<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
								 <input id="txtID" type="text" class="form-control">
							</div>
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="text-align: center;">
								<br/>
								<label id="lblName"></label>
								<br/>
								<!--<label id="lblFiniquito"></label>-->
								<select id="listFiniquitos" class="form-control">
									<option value="0"></option>
								</select>
							</div>
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
								<br/>
								<br/>
							</div>

							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="text-align: center;">
								<form action="phps/uploadSettlement.php" method="post" enctype="multipart/form-data">
								    Presione el botón "Seleccionar archivo" para subir el finiquito
								    <br/>
								    <label style="font-size: 11px; font-style: normal;">Formatos de archivo admitidos: imágenes jpg, jpeg, gif, png y documentos pdf</label>
									<div class="col-xs-12 col-sm-12 col-md-8 col-lg-8" style="text-align: center;">
									    <input class="btn btn-primary" type="file" name="fileToUpload" id="fileToUpload">
									    <input id="fileID" type="hidden" name="fileID">
								    </div>
									<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4" style="text-align: center;">
								    	<input id="btnUpload" class="btn btn-success" type="submit" value="Subir Archivo" name="submit
								    	">
								    </div>
								</form>
							</div>
						</div>
					</div>
				</div>
			<div class="col-xs-2 col-sm-2 col-md-3 col-lg-3"></div>

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