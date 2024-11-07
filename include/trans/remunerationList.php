<?php
header('Content-Type: text/html; charset=utf8'); 
session_start();

if(!isset($_SESSION['userId'])){
	header('Location: ../../login.php');
}elseif($_SESSION['display']['remuneration']['view']!=''){
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
	<script type="text/javascript" src="../../libs/jquery.table2excel.js"></script>
	<link rel="stylesheet" type="text/css" href="../../libs/datatables/datatables.min.css"/>
 	<script type="text/javascript" src="../../libs/datatables/datatables.min.js"></script>
	<title></title>
	<script type="text/javascript">

		$(document).ready(function() {
			$(function () {
			  $('[data-toggle="popover"]').popover()
			})

			loadMenu();
			startParameters();
			
			$(".datepickerTxt").datepicker({
				format: 'dd/mm/yyyy',
				weekStart: 1
			})
			$(".datepickerTxt").datepicker('setValue', '');

			$("#listEnterprise").change(function() {
				loadData();
			});

			loadEnterprise();
		});

		function loadData(){
			if($("#listEnterprise").val()!=0){
				$("#modalProgress-text").html('<i class="fa fa-spinner fa-spin fa-2x"></i><br/>Cargando Registros');
				$("#modalProgress").modal('show');
				$("#tableData").html('<thead><tr>' +
					'<th>Fecha</th>'+
					'<th>Archivo</th>'+
					'</tr></thead><tbody id="tableDataBody"></tbody>');
				$.post('../../phps/remunerationList_Load.php', {type: "all", enterprise: $("#listEnterprise").val()}, function(data, textStatus, xhr) {
					if(data!=0){
						var data = JSON.parse(data);
						var list = '';
		                for(i=0;i<data.length;i++){
							list += '<tr>' +
										'<td>'+data[i]['FileName']+'</td>' +
										'<td>'+data[i]['File1']+'</td>' +
									'</tr>';
							if(i+1==data.length){
								$("#tableDataBody").append(list);
								$('#tableData').dataTable().fnDestroy();
								$('#tableData').DataTable({
									"order": [[ 1, "desc" ]],
									"language": { "url": "../../libs/datatables/language/Spanish.json"},
									"pageLength": 25
								});
		                		$("#modalProgress").modal('hide');
							}
						}					
						$("#modalProgress").modal('hide');
					
					}else{
						$("#modalProgress").modal('hide');
					}
				});
			}else{
				$('#tableData').dataTable().fnDestroy();
			}
		}

		function loadEnterprise(){
			$.post('../../phps/enterprise_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				$("#listEnterprise").append('<option value="0">SELECCIONE</option>');
				for(i=0;i<data.length;i++){
					$("#listEnterprise").append('<option value="'+parseInt(data[i]["Emp_codigo"])+'">'+data[i]["EmpSigla"]+'</option>');
				}
				$("#listEnterprise").val(0);
			});
		}

	</script>

</head>
<body id="body">
	
	<div class="container">
		<div class="row">
			<div class="panel panel-primary">
				<div class="panel-heading"><i class="fa fa-calendar-check-o fa-lg fa-fw"></i>&nbsp;&nbsp;Lista</div>
				<div class="panel-body">	
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<label>Empresa:</label>
				    	    <select id="listEnterprise" class="form-control">
							</select>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<div class="table-responsive">
							<table id="tableData" class="table table-hover">
							</table>
						</div>
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

	<div id="modalTxt" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
	        	<div id="modal-body-Text" class="modal-body">
		      	</div>
		      	<div class="modal-footer">
		        	<button id="doTxt" type="button" class="btn btn-success">Generar</button>
		        	<button id="modalTxtHide" type="button" class="btn btn-primary">Cancelar</button>
		      	</div>
		    </div>
		</div>
	</div>

</body>
</html>