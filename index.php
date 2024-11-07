<?php
header('Content-Type: text/html; charset=utf8'); 

session_start();

if(!isset($_SESSION['userId'])){
	header('Location: login.php');
}

?>

<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script type="text/javascript" src="libs/jquery-3.1.1.min.js"></script>
	<link rel="stylesheet" type="text/css" href="libs/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="libs/bootstrap/css/bootstrap-redto.css">
	<link rel="stylesheet" type="text/css" href="style/style.css">
	<link rel="stylesheet" href="libs/font-awesome-4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="libs/datepicker/css/datepicker.css">
	<script type="text/javascript" src="libs/bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="libs/datepicker/js/bootstrap-datepicker.js"></script>
	<script type="text/javascript" src="libs/loadParameters.js"></script>
	<title></title>	
	<script type="text/javascript">

	var idList = 1;

		$(document).ready(function() {
			loadMenu();
		});

	</script>

</head>
<body id="body">
	<div class="container-fluid">
		<div class="row">
			<div id="menuPrincipal">
			</div>
		</div>
	</div>
	<br/>	
	<!--<div class="container">
		<div class="row">
			<div class="panel panel-redto">-->
				<!--<div class="panel-heading"><span class="glyphicon glyphicon-paste" aria-hidden="true"></span>&nbsp;&nbsp; Facturas</div>-->
				<!--<div class="panel-body">
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>-->
</body>
</html>