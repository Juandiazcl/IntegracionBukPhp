<?php
session_start();

if(!isset($_SESSION['userId'])){
	header('Location: login.php');
}

?>

<html>
<head>
	<script type="text/javascript" src="libs/jquery-2.1.3.min.js"></script>
	<script type="text/javascript" src="libs/bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="libs/datepicker/js/bootstrap-datepicker.js"></script>
	<link rel="stylesheet" type="text/css" href="libs/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="style/style.css">
	<link rel="stylesheet" href="libs/font-awesome-4.4.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="libs/datepicker/css/datepicker.css">
	<script type="text/javascript" src="libs/loadParameters.js"></script>
	<title></title>	
	<script type="text/javascript">

	var idList = 1;

		$(document).ready(function() {
			loadMenu();
			$('#txtFecha').datepicker({
				format: 'dd/mm/yyyy',
				weekStart: 1
			})
			$('#txtFecha').datepicker('setValue', '');

			$("#logout").click(function() {
				$.post('phps/logout.php', {param: ""}, function(data, textStatus, xhr) {
					if(data==1){
						window.location="login.php";
					}
				});
			});	
			
		
		});

	</script>

</head>
<body id="body">
	<span>No tiene suficientes permisos para acceder</span>
</body>
</html>