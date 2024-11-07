<?php
header('Content-Type: text/html; charset=utf8'); 

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
	    var pressed = false; 
	    var chars = []; 
	    $(window).keypress(function(e) {
	        if (e.which >= 48 && e.which <= 57) {
	            chars.push(String.fromCharCode(e.which));
	        }
	        console.log(e.which + ":" + chars.join("|"));
	        if (pressed == false) {
	            setTimeout(function(){
	                if (chars.length >= 10) {
	                    var barcode = chars.join("");
	                    console.log("Barcode Scanned: " + barcode);
	                    // assign value to some input (or do whatever you want)
	                    $("#barcode").val(barcode);
	                }
	                chars = [];
	                pressed = false;
	            },500);
	        }
	        pressed = true;
	    });
	});
	$("#barcode").keypress(function(e){
	    if ( e.which === 13 ) {
	        console.log("Prevent form submit.");
	        e.preventDefault();
	    }
	});

		/*$(document).ready(function() {
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
			
		
		});*/

	</script>

</head>
<body id="body">
	<input type="X" name="" value="" placeholder="">
	<input id="barcode" type="text" name="" value="" placeholder="">
	<div align="right"><button id="logout" class="btn btn-primary"><i class="fa fa-remove"></i>&nbsp;&nbsp;Desconectarse</button></div>
	<br/>	
	<div class="container">
		<div class="row">
			<nav id="menuPrincipal" class="navbar navbar-default">
			</nav>
		</div>
	</div>
	<br/>	
	<!--<div class="container">
		<div class="row">
			<div class="panel panel-primary">-->
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