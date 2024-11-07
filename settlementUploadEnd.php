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


		$(document).ready(function() {
			var message=window.location.href.split('?')[1].split('=')[1];

			if(message==1){
				$("#lblMessage").text('Error al subir, el archivo es demasiado pesado, favor seleccione un archivo de menor tamaño (máximo 24MB)');
			}else if(message==2){
				$("#lblMessage").text('Error al subir, el archivo debe estar en formato JPG, JPEG, PNG, GIF ó PDF');
			}else if(message==3){
				$("#lblMessage").text('Error al subir, el archivo de finiquito ya existe');
			}else if(message==4){
				$("#lblMessage").text('Error al subir, contacte al administrador');
			}else if(message==5){
				$("#lblMessage").text('Archivo subido correctamente');
			}else if(message==6){
				$("#lblMessage").text('El finiquito a subir ya está en sistema a la espera de ser revisado');
			}

		});


	</script>

</head>
<body id="body">
	<div class="container vertical-center">
		<div class="row">

			<div class="col-xs-0 col-sm-0 col-md-3 col-lg-3"></div>
			<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
				<div class="panel panel-primary">
					<div class="panel-heading"><i class="fa fa-info-circle fa-2x"></i>&nbsp;&nbsp;Mensaje</div>
					<div class="panel-body">
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="text-align: center;">
							  	<label id="lblMessage"></label>
							  	<br/>
							  	<br/>
							  	<br/>
							</div>
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="text-align: center;">
								<form action="settlementUpload.php">
							        <button type="submit" class="btn btn-danger"><i class="fa fa-reply"></i>&nbsp;&nbsp;Volver</button>
							    </form>
							</div>

							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="text-align: center;">
								
							</div>
						</div>
					</div>
				</div>
			<div class="col-xs-2 col-sm-2 col-md-3 col-lg-3"></div>

		</div>
	</div>


</body>
</html>