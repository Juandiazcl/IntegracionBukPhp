<?php

header('Content-Type: text/html; charset=utf8'); 
?>

<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script type="text/javascript" src="libs/jquery-3.1.1.min.js"></script>
	<script type="text/javascript" src="libs/bootstrap/js/bootstrap.min.js"></script>
	<link rel="stylesheet" type="text/css" href="libs/bootstrap/css/bootstrap.min.css">
	<!--<link rel="stylesheet" type="text/css" href="libs/bootstrap/css/bootstrap-primary.css">-->
	<link rel="stylesheet" type="text/css" href="style/style.css">
	<title></title>
	<script type="text/javascript">

		$(document).ready(function() {
			$("#login").click(function() {
				login();
			});	
			$("#userText").keypress(function(e) {
			 	if(e.which == 13) {
			 		if($('#modal').css("display")=="none"){
		       			login();
		       		}else{
		       			$("#modal").modal('hide');
		       		}
		       	}
		    });
			$("#passText").keypress(function(e) {
			 	if(e.which == 13) {
			 		if($('#modal').css("display")=="none"){
		       			login();
		       		}else{
		       			$("#modal").modal('hide');
		       		}
		       	}
		    });

			$("#modalHide").click(function() {
				$("#modal").modal('hide');	
			});

		});
		
		function login(){
			var user = $("#userText").val();
			var password = $("#passText").val();
			if(user!="" && password!=""){
				$.post('phps/login_check.php', {user: user,password: password}, function(data, textStatus, xhr) {
					console.log(data);
					if(data==1){
						window.location="index.php";
					}else{
						$("#modal-text").text("Usuario y/o contraseña incorrectos");
						$("#modal").modal('show');
					}
				});
			}else{
				$("#modal-text").text("Debe ingresar ambos datos");
				$("#modal").modal('show');
			};
		}

	</script>

</head>
<body id="body">
	<div class="container-fluid vertical-center">
		<div class="row">
			<div class="col-xs-0 col-sm-0 col-md-4 col-lg-4"></div>
			<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
				<div class="panel panel-primary">
					<div class="panel-heading"><span class="glyphicon glyphicon-user" aria-hidden="true"></span>&nbsp;&nbsp;Panel de Ingreso</div>
					<div class="panel-body">
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-5 col-lg-5" style="text-align: center;">
								<br/>
								<img src="images/logoLogin.png" style="width:100%">
							</div>
							<div class="col-xs-12 col-sm-12 col-md-7 col-lg-7">
								<div class="form-group">
								  	<label for="usr">Usuario:</label>
								  	<input id="userText" type="text" class="form-control" id="user">
								  	<label for="pwd">Contraseña:</label>
								  	<input id="passText" type="password" class="form-control" id="password">
								</div>
							</div>
							<div class="col-xs-0 col-sm-0 col-md-8 col-lg-8"></div>
							<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
								<div style="text-align: right;"><button id="login" class="btn btn-primary">Ingresar</button></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xs-2 col-sm-2 col-md-4 col-lg-4"></div>

		</div>
	</div>

	<div id="modal" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
		    	<!--<div class="modal-header">
		        	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		        	<h4 class="modal-title">Modal title</h4>
		      	</div>-->
	        	<div class="modal-body">
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