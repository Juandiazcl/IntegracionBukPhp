
<?php
header('Content-Type: text/html; charset=utf8'); 
session_start();

if(!isset($_SESSION['userId'])){
	header('Location: ../../login.php');
}elseif($_SESSION['display']['talliesBuk']['view']!=''){
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
	<!-- <link rel="stylesheet" type="text/css" href="../../libs/bootstrap/css/bootstrap-lucuma.css"> -->
	<link rel="stylesheet" type="text/css" href="../../style/style.css">
	<link rel="stylesheet" href="../../libs/font-awesome-4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="../../libs/datepicker/css/datepicker.css">
	<script type="text/javascript" src="../../libs/moment.js"></script>
	<script type="text/javascript" src="../../libs/loadParameters.js"></script>
	<script type="text/javascript" src="../../libs/jquery.mask.js"></script>
	<link rel="stylesheet" type="text/css" href="../../libs/datatables/datatables.min.css"/>
 	<script type="text/javascript" src="../../libs/datatables/datatables.min.js"></script>
 	<script type="text/javascript" src="../../libs/moment.js"></script>
 	<script type="text/javascript" src="../../libs/jquery.table2excel.js"></script>
	<title></title>
	<script type="text/javascript">

	// var listProducts = '', listProductsContract = '', listQuarters = '';
	// var displayUpdate = '
	//  var displayDelete = '
	

		$(document).ready(function() {
			//loadMenu();
			//startParameters();

			$("#modalHide").click(function() {
				$("#modal").modal('hide');
			});

			$("#modalDeleteHide").click(function() {
				$("#modalDelete").modal('hide');	
			});

			$("#modalCambiarPerHide").click(function() {
				$("#modalCambiarPer").modal('hide');	
			});

			$("#modalCambiarPerHide2").click(function() {
				$("#modalCambiarPer2").modal('hide');	
			});

			$("#modalCambiarPerHide3").click(function() {
				$("#modalCambiarPer3").modal('hide');	
			});

			$("#modalOldTallyHide").click(function() {
				$("#modalOldTally").modal('hide');	
			});

			$("#modalEnvioCCHide").click(function() {
				$("#modalEnvioCC").modal('hide');	
			});

			$("#new").click(function() {
				// loadProduct();
				// loadQuarter();
				$("#modalNew").modal('show');
			});
			$("#cancel").click(function() {
				$("#divID").css('display','none');
				$("#modalNew").modal('hide');
				// cleanModal();
			});

			$("#btnReload").click(function() {
				// loadData();
			});

			$("#btnExcel").click(function() {
				// loadDataExcel();
				//cambiarPeriodoSys();
				$("#modalCambiarPer").modal('show');
			});  

			$("#btnInformeAusencia").click(function() {
				// loadDataExcel();
				//cambiarPeriodoSys();
				$("#modalCambiarPer2").modal('show');
			});  

			$("#btnInformeHoras").click(function() {
				// loadDataExcel();
				//cambiarPeriodoSys();
				$("#modalCambiarPer3").modal('show');
			}); 

			$("#btnAdmTarifas").click(function() {
				
				//$("#modalProgress").modal('hide');
				$("#modalProgress").modal('show');
				$("#modalBajando").modal('show');
				$.post('../../phps/guideGenerate_Load.php', {type: 'admTarifas', 
					plantNvo: $("#listPlant6").val()
				}, function(data, textStatus, xhr) {
					console.log(data);
					if(data==1){
						//$("#modalProgress").modal('hide');
						$("#modal-text").text("Tarifas(s) descargadas de Buk.");
						$("#modal").modal('show');
						$("#modalBajando").modal('hide');
					}else{
						$("#modal-text").text("Error y no se pudo bajar.");
						$("#modal").modal('show');
						$("#modalBajando").modal('hide');
					}
				});
			});
			$("#btnWork").click(function() {
				// loadDataExcel();
				//cambiarPeriodoSys();
				//$("#modalCambiarPer").modal('show');
				//var id = $("#modal-change-id").text();
				$("#modalBajando").modal('show');
				if ($("#listMonth").val()==null){
				alert("Debes seleccionar un periodo.")
				$.post('../../phps/guideGenerate_Load.php', {type: 'worked', 
					plant: $("#listPlant").val(),
					plantNvo: $("#listPlant2").val(),
					plantNvo2: $("#listPlant3").val(),
					idArea:  $("#idArea").val(),
					idRole: $("#idRole").val(),
					idLeader: $("#idLeader").val(),
					year: $("#listMonth").val(),
			        job: $("#txtRut2").val()
				}, function(data, textStatus, xhr) {
					console.log(data);
					if(data==1){
						//loadData();
						$("#modalCambiarPer").modal('hide');
						$("#modal-text").text("Empleado(s) subido a Buk");
						$("#modal").modal('show');
						$("#modalBajando").modal('hide');
					}else{
						$("#modal-text").text("Error y no se pudo enviar.");
						$("#modal").modal('show');
						$("#modalBajando").modal('hide');
					}
				});
				return;
			}
			
				// alert(mm);
				// alert(nn);
				$("#modalBajando").modal('show');
				$.post('../../phps/guideGenerate_Load.php', {type: 'worked', 
					plant: $("#listPlant").val(),
					plantNvo: $("#listPlant2").val(),
					plantNvo2: $("#listPlant3").val(),
					idArea:  $("#idArea").val(),
					idRole: $("#idRole").val(),
					idLeader: $("#idLeader").val(),
					year: $("#listMonth").val(),
			        job: $("#txtRut2").val()
				}, function(data, textStatus, xhr) {
					console.log(data);
					if(data==1){work
						//loadData();
						$("#modalCambiarPer").modal('hide');
						$("#modal-text").text("Empleado(s) subido a Buk");
						$("#modal").modal('show');
						$("#modalBajando").modal('hide');
					}else{
						$("#modal-text").text("Error y no se pudo enviar.");
						$("#modal").modal('show');
						$("#modalBajando").modal('hide');
					}
				});
			});

			$("#btnExTarjas").click(function() {
				// loadDataExcel();
				//cambiarPeriodoSys();
				$("#modalOldTally").modal('show');
				
			});

			$("#btnEnvioCC").click(function() {
				// loadDataExcel();
				//cambiarPeriodoSys();
				$("#modalEnvioCC").modal('show');
				
			});

			$("#listBranch").change(function(){
				// loadClient();
				// loadQuarter();
			});

			$("#listClient").change(function(){
				// loadContract();
			});

			$("#listContract").change(function(){
				loadContractData(true);
			});

			$("#txtDate").change(function(){
			});

			$("#txtDate").datepicker().on('changeDate', function(ev){
			});


			$('#txtReceiverRUT').focusout(function(event) {
				$('#txtReceiverRUT').val(orderRUT($('#txtReceiverRUT').val()));
				if(verifyRUT($('#txtReceiverRUT').val())==false){
					$("#modal-text").text("RUT inválido, favor verificar");
					$("#modal").modal('show');
				}
			});

			$('#txtReceiverRUT').focus(function(event) {
				$('#txtReceiverRUT').val($('#txtReceiverRUT').val().replace('-',''));
				$('#txtReceiverRUT').val($('#txtReceiverRUT').val().replace(/\./g,''));
			});

			$("#delete").click(function() {
				var id = $("#modal-delete-id").text();
				$.post('../../phps/guide_Save.php', {type: 'delete', id: id}, function(data, textStatus, xhr) {
					if(data==1){
						loadData();
						$("#modalDelete").modal('hide');
						$("#modal-text").text("Registro Eliminado Satisfactoriamente");
						$("#modal").modal('show');
					}else{
						$("#modal-text").text("Error y no se pudo borrar.");
						$("#modal").modal('show');
					}
				});
			});

			$("#changePer").click(function() {
				var id = $("#modal-change-id").text();
				var mm = $("#txtMonth").val();
				var nn = $("#txtYear").val();
				
				// alert(mm);
				// alert(nn);
				$("#modalBajando").modal('show');
				$.post('../../phps/guide_Save.php', {type: 'change', 	
					mes: mm,
					an: nn
				}, function(data, textStatus, xhr) {
					console.log(data);
					if(data==1){
						//loadData();
						$("#modalCambiarPer").modal('hide');
						$("#modal-text").text("Periodo cambiado Satisfactoriamente.");
						$("#modal").modal('show');
						$("#modalBajando").modal('hide');
					}else{
						$("#modal-text").text("Error y no se pudo cambiar.");
						$("#modal").modal('show');
						$("#modalBajando").modal('hide');
					}
				});
			});

			$("#changePer2").click(function() {
				var id = $("#modal-change-id").text();
				var mm = $("#txtMonth3").val();
				var nn = $("#txtYear3").val();
				
				// alert(mm);
				// alert(nn);
				$("#modalBajando").modal('show');
				// $.post('../../phps/guide_Save.php', {type: 'verify', 	
				// 	mes: mm,
				// 	an: nn
				// }, function(data, textStatus, xhr) {
				// 	console.log(data);
				// 	if(data==1){
						//loadData();
						toexcel2(mm,nn);
						$("#modalCambiarPer2").modal('hide');
						$("#modal-text").text("Informe Listo.");
						$("#modal").modal('show');
						$("#modalBajando").modal('hide');
				// 	}else{
				// 		$("#modalCambiarPer2").modal('hide');
				// 		$("#modal-text").text("Periodo no tiene registros.");
				// 		$("#modal").modal('show');
				// 		$("#modalBajando").modal('hide');
				// 	}
				// });
			});

			$("#changePer3").click(function() {
				var id = $("#modal-change-id").text();
				var mm = $("#txtMonth4").val();
				var nn = $("#txtYear4").val();
				
				// alert(mm);
				// alert(nn);
				$("#modalBajando").modal('show');

						toexcel3(mm,nn);
						
						$("#modalCambiarPer3").modal('hide');
						$("#modal-text").text("Informe Listo.");
						$("#modal").modal('show');
						$("#modalBajando").modal('hide');
				
			});

			$("#combinarTally").click(function() {
				var id = $("#modal-change-id").text();
				var mm = $("#txtMonth2").val();
				var nn = $("#txtYear2").val();
				// alert(mm);
				// alert(nn);
				$("#modalOldTally").modal('hide');	
				$("#modalBajando").modal('show');
				$.post('../../phps/guideGenerate_Load.php', {type: 'oldTally', 	
					mes: mm,
					an: nn,
					plantB: $("#listPlantB").val(),
					plantNvo4: $("#listPlant4").val(),	
			        job: $("#txtRut3").val()
				}, function(data, textStatus, xhr) {
					
					console.log(data);
					
					if(data==1){
						//loadData();
						$("#modalCambiarPer").modal('hide');
						$("#modal-text").text("Tarjas antiguas extraidas OK.");
						$("#modal").modal('show');
						$("#modalBajando").modal('hide');
					}else{
						$("#modal-text").text(" 0 Tarjas Enviadas, no hay registros.");
						$("#modal").modal('show');
						$("#modalBajando").modal('hide');
					}
				});
			});

			$("#enviarCC").click(function() {
			
				var mm = $("#txtMonth33").val();
				var nn = $("#txtYear33").val();
			    //   alert(mm);
				//   alert(nn);
				$("#modalEnvioCC").modal('hide');	
				$("#modalBajando").modal('show');
				$.post('../../phps/guideGenerate_Load.php', {type: 'envioCC', 	
					mes: mm,
					an: nn,
					//plantB: $("#listPlantB").val(),
					plantNvo4: $("#listPlant5").val(),	
			        job: $("#txtRut4").val()
				}, function(data, textStatus, xhr) {
					
					console.log(data);
					$("#modalEnvioCC").modal('hide');
					toexcel4(mm,nn);
					//if(data==1){
						//loadData();
						$("#modal-text").text("CC para Importador Descargados OK.");
						$("#modal").modal('show');
						$("#modalBajando").modal('hide');
					// }else{
					// 	$("#modal-text").text(" 0 CC Enviado, no hay registros o error.");
					// 	$("#modal").modal('show');
					// 	$("#modalBajando").modal('hide');
					// }
				});
			});

			$("#btnAddItem").click(function(){
				if($("#listContract").val()!=0){
					var row = '<tr>' +
									'<td>N</td>' +
									'<td>'+listProductsContract+'</td>' +
									'<td>'+listProducts+'</td>' +
									'<td>'+listProducts+'</td>' +
									'<td>'+listQuarters+'</td>' +
									'<td><input style="width: 75px;"/></td>' +
									'<td><input style="width: 75px; text-align: right;" class="numbersOnlyFloat"  onfocus="returnNoSeparator()" onfocusout="returnSeparator(); calculate(this);"/></td>' +
									'<td><input style="width: 75px; text-align: right;" onfocus="returnNoSeparator()" onfocusout="returnSeparator();"/></td>' +
									'<td><input style="width: 75px; text-align: right;" class="numbersOnlyFloat"  onfocus="returnNoSeparator()" onfocusout="returnSeparator(); calculate(this);"/></td>' +
									'<td><input style="width: 75px; text-align: right;" disabled></td>' +
									'<td><input style="width: 200px;" maxlength="200"/></td>' +
									'<td><button class="btn btn-danger" onclick="deleteItem(this)"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button></td>' +
								'</tr>';

					$("#tableItemsBody").append(row);
					startParameters();
				}else{
					$("#modal-text").text("Debe seleccionar contrato para agregar ítems");
					$("#modal").modal('show');
				}
			});

			$("#btnGenerate").click(function() {
				$("#modal-text").text("Antes de Generate");
				generateGuide();
				// if($("#txtNumber").val()!='' && $("#txtNumber").val()!='0'){
				// 	$("#modal-text").text("Carga Buk ya generada");
				// 	$("#modal").modal('show');
				// 	return;
				// }else{
				// 	save(true);
				// }
			});

			$("#btnPreview").click(function() {
				window.open("guide_pdf.php?id="+$("#labelID").text());
			});

			$("#save").click(function() {
				save(false);
			});
			
			//loadEnterprise();
			loadPlantB();
			loadPlant();
			loadPlant2();
			loadPlant3();
			loadPlant4();
			loadPlant5();
			loadPlant6();
			loadPeriodosBuk();
		});
		function loadPersonal(type){
			
			$.post('../../phps/personal_Load.php', {
				type: "allTally",
				plant: $("#listPlant").val(),
				year: $("#listYear").val(),
		       	month: $("#listMonth").val()
			}, function(data, textStatus, xhr) {
				listPersonal = '<option value="0">SELECCIONE TRABAJADOR</option>';
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					var space = '';
					if(data[i]["rut"].length<12){
						space = '&nbsp';
					}
					listPersonal += '<option value="'+data[i]["rut_per"]+'" data-value-name="'+data[i]["fullname"]+'">'+space+data[i]["rut"]+' '+data[i]["fullname"]+'</option>';
				}
			});
		}
		function loadData(){

			var head =	'<thead>' +
							'<tr>' +
								'<th>N° Guía</th>' +
								'<th>Fecha</th>' +
								'<th>N° Contrato Int.</th>' +
								'<th>N° Contrato Ext.</th>' +
								'<th>Vendedor</th>' +
								'<th>Comprador</th>' +
								'<th>Campo</th>' +
								'<th>Editar</th>' +
								'<th>Guía PDF</th>' +
								'<th>Eliminar</th>' +
							'</tr>' +
						'</thead>';
			$("#tableData").html(head+'<tbody id="tableDataBody"></tbody>');
			
			$.post('../../phps/guide_Load.php', {type: "all", enterprise_id: $("#listFilterEnterprise").val(), client_id: $("#listFilterClient").val(), branch_id: $("#listFilterBranch").val(), year: $("#listFilterYear").val()}, function(data, textStatus, xhr) {
				
				if(data!=0){
					var data = JSON.parse(data);
					var list = "";
					for(i=0;i<data.length;i++){

						var dateArray = data[i]['date']['date'].split(' ')[0].split('-');
						var date = dateArray[2]+'/'+dateArray[1]+'/'+dateArray[0];
						list += '<tr id="id'+data[i]['id']+'">' +
									'<td>'+data[i]['number']+'</td>' +
									'<td>'+date+'</td>' +
									'<td>'+data[i]['code_internal']+'</td>' +
									'<td>'+data[i]['code_external']+'</td>' +
									'<td>'+data[i]['enterprise']+'</td>' +
									'<td>'+data[i]['client']+'</td>' +
									'<td>'+data[i]['branch']+'</td>' +
									'<td '+displayUpdate+'>'+data[i]['edit']+'</td>' +
									'<td>'+data[i]['pdf']+'</td>' +
									'<td '+displayDelete+'>'+data[i]['delete']+'</td>' +
								'</tr>';

						if(i+1==data.length){
							$("#tableDataBody").append(list);
							$('#tableData').dataTable().fnDestroy();
							$('#tableData').DataTable({
								"language": { "url": "../../libs/datatables/language/Spanish.json"}
							});

						}
					}
				}
			});
		}


		function save(generate){

			//$("#modalProgress").modal('show');
			returnNoSeparator();
			var type="update";
			if($("#divID").css('display')=='none'){
				type='save';
			}
			var date = $('#txtDate').val().split('/');
			date = date[2]+''+date[1]+''+date[0];

			// if($('#txtReceiverRUT').val()!=''){
			// 	if(verifyRUT($('#txtReceiverRUT').val())==false){
			// 		$("#modalProgress").modal('hide');
			// 		$("#modal-text").text("Debe ingresar un RUT de recepción válido");
			// 		$("#modal").modal('show');
			// 		returnSeparator();
			// 		return;
			// 	}else if($('#txtReceiverName').val()==''){
			// 		$("#modalProgress").modal('hide');
			// 		$("#modal-text").text("Debe ingresar un Nombre de recepción válido");
			// 		$("#modal").modal('show');
			// 		returnSeparator();
			// 		return;
			// 	}
			// }else if($('#txtReceiverName').val()!=''){
			// 	$("#modalProgress").modal('hide');
			// 	$("#modal-text").text("Debe ingresar un RUT de recepción válido");
			// 	$("#modal").modal('show');
			// 	returnSeparator();
			// 	return;
			// }

			var receiverDate = $('#txtReceiverDate').val().split('/');
			receiverDate = receiverDate[2]+''+receiverDate[1]+''+receiverDate[0];

			var items='';
			var itemsCount=$("#tableItemsBody > tr").length;
			var itCount = 0;

			// if(itemsCount==0){
			// 	$("#modalProgress").modal('hide');
			// 	$("#modal-text").text("Debe ingresar al menos 1 Variedad");
			// 	$("#modal").modal('show');
			// 	returnSeparator();
			// 	return;
			// }

			if($("#listBranch").val()==0 || $("#listBranch").val()==null || $("#listBranch").val()==undefined){
				$("#modalProgress").modal('hide');
				$("#modal-text").text("Debe seleccionar Campo");
				$("#modal").modal('show');
				returnSeparator();
				return;
			}

			if($("#listContract").val()==0 || $("#listContract").val()==null || $("#listContract").val()==undefined){
				$("#modalProgress").modal('hide');
				$("#modal-text").text("Debe seleccionar Contrato");
				$("#modal").modal('show');
				returnSeparator();
				return;
			}

			$("#tableItemsBody > tr").each(function() {
				if($($($(this).children()[1]).children()[0]).val()==0){
					$("#modalProgress").modal('hide');
					$("#modal-text").text("Debe seleccionar Variedad");
					$("#modal").modal('show');
					returnSeparator();
					return;
				}
				if($($($(this).children()[2]).children()[0]).val()==0){
					$("#modalProgress").modal('hide');
					$("#modal-text").text("Debe seleccionar Variedad Real");
					$("#modal").modal('show');
					returnSeparator();
					return;
				}
				if($($($(this).children()[4]).children()[0]).val()==0){
					$("#modalProgress").modal('hide');
					$("#modal-text").text("Debe ingresar Cuartel");
					$("#modal").modal('show');
					returnSeparator();
					return;
				}
				// if($($($(this).children()[5]).children()[0]).val()==0){
				// 	$("#modalProgress").modal('hide');
				// 	$("#modal-text").text("Debe ingresar cantidad de bins");
				// 	$("#modal").modal('show');
				// 	returnSeparator();
				// 	return;
				// }
				// if($.isNumeric($($($(this).children()[6]).children()[0]).val())==false || $($($(this).children()[6]).children()[0]).val()==0){
				// 	$("#modalProgress").modal('hide');
				// 	$("#modal-text").text("Debe ingresar una cantidad válida");
				// 	$("#modal").modal('show');
				// 	returnSeparator();
				// 	return;
				// }
				if($.isNumeric($($($(this).children()[7]).children()[0]).val())==false || $($($(this).children()[7]).children()[0]).val()==0){
					$($($(this).children()[7]).children()[0]).val(0);
				}
				// if($.isNumeric($($($(this).children()[8]).children()[0]).val())==false || $($($(this).children()[8]).children()[0]).val()==0){
				// 	$("#modalProgress").modal('hide');
				// 	$("#modal-text").text("Debe ingresar un precio válido");
				// 	$("#modal").modal('show');
				// 	returnSeparator();
				// 	return;
				// }
				var observationItem = $($($(this).children()[10]).children()[0]).val();
				if(observationItem==''){
					observationItem='-';
				}

				var variety = $($($(this).children()[1]).children()[0]).val().split('_');
				var varietyGuide = variety[0];
				var varietyContract = variety[1];

				items += $($(this).children()[0]).text()+'&&'+
						varietyGuide+'&&'+
						$($($(this).children()[2]).children()[0]).val()+'&&'+
						$($($(this).children()[3]).children()[0]).val()+'&&'+
						$($($(this).children()[4]).children()[0]).val()+'&&'+
						$($($(this).children()[5]).children()[0]).val()+'&&'+
						$($($(this).children()[6]).children()[0]).val()+'&&'+
						$($($(this).children()[7]).children()[0]).val()+'&&'+
						$($($(this).children()[8]).children()[0]).val()+'&&'+
						observationItem+'&&'+
						varietyContract+'&&&&';
				itCount++;

				if(itCount==itemsCount){

					$.post('../../phps/guide_Save.php', {type: type, 
						id: $('#labelID').text(),
						contract_id: $('#listContract').val(),
						branch_id: $('#listBranch').val(),
						date: date,
						guide_type: $('#listGuideType').val(),
						observation: $('#txtObservation').val(),
						receiver_name: $('#txtReceiverName').val(),
						receiver_rut: $('#txtReceiverRUT').val(),
						receiver_date: $('#txtReceiverDate').val(),
						receiver_address: $('#txtReceiverAddress').val(),
						receiver_plate1: $('#txtReceiverPlate1').val(),
						receiver_plate2: $('#txtReceiverPlate2').val(),
						items: items}, function(data, textStatus, xhr) {
						returnSeparator();
						
						if($.isNumeric(data)){
							loadData();
							$("#divID").css('display','block');
							$('#labelID').text(data);
							if(type=='save'){
								$("#btnPreview").removeAttr('disabled');
								$("#btnGenerate").removeAttr('disabled');
							}

							loadItems(data);
							if(generate){
								//generateGuide();
							}else{
								$("#modalProgress").modal('hide');
								$("#modal-text").text("Almacenado");
								$("#modal").modal('show');
							}
						}else{
							$("#modalProgress").modal('hide');
							$("#modal-text").text("Error al almacenar");
							$("#modal").modal('show');
						}
					});
				}
			});

		}

function toexcel2(mes,anho){
$("#modalProgress").modal('show');
$("#tableDataTallyExcel").html('<tr>' +
			'<th colspan="2">MES:</th>' +
			'<th>'+mes+'</th>' +
			'<th colspan="2">AÑO</th>' +
			'<th colspan="2">'+anho+'</th>' +
		'</tr>' +
		'<tr>' +
			'<th></th>' +
		'</tr>' +
		'<tr>' +
			'<th>Campo</th>' +
			'<th>RUT</th>' +
			'<th>Trabajador</th>' +
			'<th>Dia</th>' +
			'<th>Tipo</th>' +
		'</tr>');
console.log(mes);
console.log(anho);
$.post('../../phps/talliesb_Load.php', {
	type: 'oneExcel2',
	mes: mes,
	year: anho
}, function(data, textStatus, xhr) {
	var data = JSON.parse(data);
	console.log(data);
	// if(data.length==0){
	// 	$("#modal-text").text("Periodo no tiene registros.");
	// 	exit();
	// }
	for(i=0;i<data.length;i++){
		
		list = '<tr>' +
				'<td>'+data[i]['nomCampo']+'</td>' +
				'<td>'+data[i]['rut']+'</td>' +
				'<td>'+data[i]['nomtrabtj']+'</td>' +
				'<td>'+data[i]['dia']+'</td>' +
				'<td>'+data[i]['tipoAusencia']+'</td>' +
			'</tr>';

		$("#tableDataTallyExcel").append(list);

		if(i+1==data.length){
			$("#tableDataTallyExcel").table2excel({
				exclude: ".noExl",
				name: "Excel Document Name",
				filename: "Lista",
				fileext: ".xls",
				exclude_img: true,
				exclude_links: true,
				exclude_inputs: true
			});
			$("#modalProgress").modal('hide');
		}
	}
});

}

function toexcel3(mes,anho){
$("#modalProgress").modal('show');
$("#tableDataTallyExcel").html('<tr>' +
			'<th colspan="2">MES:</th>' +
			'<th>'+mes+'</th>' +
			'<th colspan="2">AÑO</th>' +
			'<th colspan="2">'+anho+'</th>' +
		'</tr>' +
		'<tr>' +
			'<th></th>' +
		'</tr>' +
		'<tr>' +
			'<th>RUT</th>' +
			'<th>Trabajador</th>' +
			'<th>Fecha</th>' +
			'<th>Campo</th>' +
			'<th>Horas Ext.</th>' +
		'</tr>');
console.log(mes);
console.log(anho);
$.post('../../phps/talliesb_Load.php', {
	type: 'oneExcel3',
	mes: mes,
	year: anho
}, function(data, textStatus, xhr) {
	var data = JSON.parse(data);
	console.log(data);
	// if(data.length==0){
	// 	$("#modal-text").text("Periodo no tiene registros.");
	// 	exit();
	// }
	for(i=0;i<data.length;i++){
		
		list = '<tr>' +
				'<td>'+data[i]['rut']+'</td>' +
				'<td>'+data[i]['nomtrabtj']+'</td>' +
				'<td>'+data[i]['dia']+'</td>' +
				'<td>'+data[i]['PlNombre']+'</td>' +
				'<td>'+data[i]['hExt']+'</td>' +
			'</tr>';

		$("#tableDataTallyExcel").append(list);

		if(i+1==data.length){
			$("#tableDataTallyExcel").table2excel({
				exclude: ".noExl",
				name: "Excel Document Name",
				filename: "Lista",
				fileext: ".xls",
				exclude_img: true,
				exclude_links: true,
				exclude_inputs: true
			});
			$("#modalProgress").modal('hide');
		}
	}
});

}

function toexcel4(mes,anho){
$("#modalProgress").modal('show');
$("#tableDataTallyExcel").html('<tr>' +
			'<th colspan="2">MES:</th>' +
			'<th>'+mes+'</th>' +
			'<th colspan="2">AÑO</th>' +
			'<th colspan="2">'+anho+'</th>' +
		'</tr>' +
		'<tr>' +
			'<th></th>' +
		'</tr>' +
		'<tr>' +
			'<th>RUT</th>' +
			'<th>CC</th>' +
			'<th>% Peso</th>' +
		'</tr>');
console.log(mes);
console.log(anho);
$.post('../../phps/talliesb_Load.php', {
	type: 'oneExcel4',
	mes: mes,
	year: anho
}, function(data, textStatus, xhr) {
	var data = JSON.parse(data);
	console.log(data);
	// if(data.length==0){
	// 	$("#modal-text").text("Periodo no tiene registros.");
	// 	exit();
	// }
	for(i=0;i<data.length;i++){
		
		list = '<tr>' +
				'<td>'+data[i]['rut']+'</td>' +
				'<td>'+data[i]['cc']+'</td>' +
				'<td>'+data[i]['pPeso']+'</td>' +
			'</tr>';

		$("#tableDataTallyExcel2").append(list);

		if(i+1==data.length){
			$("#tableDataTallyExcel2").table2excel({
				exclude: ".noExl",
				name: "Excel Document Name",
				filename: "Lista",
				fileext: ".xls",
				exclude_img: true,
				exclude_links: true,
				exclude_inputs: true
			});
			$("#modalProgress").modal('hide');
		}
	}
});

}


		function editRow(id){
			$("#divID").css('display','block');
			$("#labelID").text(id);
			$("#modalNew").modal('show');
			$('#txtID').prop('disabled', 'true');
			$.post('../../phps/guide_Load.php', {type: "one", id: id}, function(data, textStatus, xhr) {
				
				var data = JSON.parse(data);
				$('#listBranch').val(data[0]['branch_id']);
				loadClient(data[0]['client_id']);
				loadContract(data[0]['contract_id'],data[0]['client_id']);
				//$('#listContract').val(data[0]['contract_id']);
				var dateArray = data[0]['date']['date'].split(' ')[0].split('-');
				var date = dateArray[2]+'/'+dateArray[1]+'/'+dateArray[0];
				$('#txtDate').val(date);
				$('#listGuideType').val(data[0]['guide_type']);
				$('#txtNumber').val(data[0]['number']);
				$('#txtObservation').val(data[0]['observation']);
				$('#txtReceiverName').val(data[0]['receiver_name']);
				$('#txtReceiverRUT').val(data[0]['receiver_rut']);
				var dateReceiverArray = data[0]['receiver_date']['date'].split(' ')[0].split('-');
				var dateReceiver = dateReceiverArray[2]+'/'+dateReceiverArray[1]+'/'+dateReceiverArray[0];
				$('#txtReceiverDate').val(dateReceiver);
				$('#txtReceiverAddress').val(data[0]['receiver_address']);
				$('#txtReceiverPlate1').val(data[0]['receiver_plate1']);
				$('#txtReceiverPlate2').val(data[0]['receiver_plate2']);
				loadContractData(false, data[0]['contract_id']);
				//loadContractData(false, data[0]['contract_id']);
				loadProductContract(false, data[0]['contract_id']);
				loadQuarter();
				loadItems(id);
				
				$("#btnPreview").removeAttr('disabled');
				
				if($("#txtNumber").val()!='' && $("#txtNumber").val()!='0'){
					$(".guideClass").attr('disabled','disabled');

					//$("#btnPreview").attr('disabled','disabled');
					$("#btnGenerate").attr('disabled','disabled');
					$("#save").attr('disabled','disabled');
					$("#btnViewPDF").attr('onclick','guidePDF(\''+data[0]['url']+'\')');
					$("#btnViewPDF").removeAttr('disabled');
				}else{
					$("#save").removeAttr('disabled');
					if(data[0]['token']!=''){
						//$("#btnPreview").removeAttr('disabled');
						$("#btnGenerate").removeAttr('disabled');
					}
				}
			});
		}

		function loadItems(id){
			$("#tableItemsBody").html('');
			$.post('../../phps/guide_Load.php', {type: "allItems", guide_id: id}, function(data, textStatus, xhr) {
				
				if(data!=0){
					var data = JSON.parse(data);
					var list = "";
					for(i=0;i<data.length;i++){

						var dispatched = '';
						if($("#txtNumber").val()==0){
							dispatched = '<button class="btn btn-danger" onclick="deleteItem(this)"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';
						}
						list += '<tr>' +
									'<td>'+data[i]['id']+'</td>' +
									'<td>'+listProductsContract.replace('value="'+data[i]['product_id']+'_'+data[i]['contract_item_id']+'"','value="'+data[i]['product_id']+'_'+data[i]['contract_item_id']+'" selected')+'</td>' +
									'<td>'+listProducts.replace('value="'+data[i]['real_product_id']+'"','value="'+data[i]['real_product_id']+'" selected')+'</td>' +
									'<td>'+listProducts.replace('value="'+data[i]['real_product2_id']+'"','value="'+data[i]['real_product2_id']+'" selected')+'</td>' +
									'<td>'+listQuarters.replace('value="'+data[i]['quarter']+'"','value="'+data[i]['quarter']+'" selected')+'</td>' +
									'<td><input style="width: 75px;" value="'+data[i]['bin']+'"/></td>' +
									'<td><input style="width: 75px; text-align: right;" value="'+data[i]['weight']+'" class="numbersOnlyFloat" onfocus="returnNoSeparator()" onfocusout="returnSeparator(); calculate(this);"/></td>' +
									'<td><input style="width: 75px; text-align: right;" value="'+data[i]['grade']+'" class="numbersOnlyFloat" onfocus="returnNoSeparator()" onfocusout="returnSeparator()"/></td>' +
									'<td><input style="width: 75px; text-align: right;" value="'+data[i]['price']+'" class="numbersOnlyFloat" onfocus="returnNoSeparator()" onfocusout="returnSeparator(); calculate(this);"/></td>' +
									'<td><input style="width: 75px; text-align: right;" value="'+data[i]['total']+'" class="numbersOnlyFloat" disabled/></td>' +
									'<td><input style="width: 200px;" maxlength="200" value="'+data[i]['observation']+'"/></td>' +
									'<td>'+dispatched+'</td>' +
								'</tr>';



 						if(i+1==data.length){
							$("#tableItemsBody").append(list);
							startParameters();
							calculateTotal();
						}
					}
				}
			});
		}

		// $.post('../../phps/personal_Load.php', {
		// 		type: "allTally",
		// 		plant: $("#listPlant").val(),
		// 		year: $("#listYear").val(),
		//        	month: $("#listMonth").val()
		// 	}, function(data, textStatus, xhr) {
		function generateGuide(){
			//alert($("#listMonth").val());
			//$("#modalProgress").modal('show');
			if ($("#listMonth").val()==null){
				alert("Debes seleccionar un periodo.")
				return;
			}
			$.post('../../phps/guideGenerate_Load.php', {
			  type: 'one',
			  plant: $("#listPlant").val(),
			  plantNvo: $("#listPlant2").val(),
			  year: $("#listMonth").val(),
			  job: $("#txtRut").val(),
		      empresa: $("#listFilterEnterprise").val()
			},	function(data, textStatus, xhr) {
				$("#modalProgress").modal('hide');
				// var data = JSON.parse(data);
				console.log(data);
				//alert(data);
				// if(data==0){
				// 	$("#modal-text").text("No hay datos segun el filtro");
				// 	$("#modal").modal('show');		
				// } else {
					$("#modal-text").text("Integracion con Buk Terminada");
					$("#modal").modal('show');
				//}
				// }else if(data[0]=='h'){
					//var array = data.split('&&');

					$("#btnPreview").attr('disabled','disabled');
					//$("#btnGenerate").attr('disabled','disabled');
					//$("#save").attr('disabled','disabled');
					// $("#btnViewPDF").attr('onclick','guidePDF(\''+array[0]+'\')');
					//$("#btnViewPDF").removeAttr('disabled');
					//$("#txtNumber").val(array[1]);
					
				// }else{
				//	$("#modal-text").text("Ha ocurrido un error al integrar: "+data);
				//	$("#modal").modal('show');
				// }
				/*var data = JSON.parse(data);
				$("#listFilterEnterprise").append('<option value="0" selected>TODOS</option>');
				for(i=0;i<data.length;i++){
					$("#listFilterEnterprise").append('<option value="'+data[i]["id"]+'">'+data[i]["initials"]+'</option>');
				}*/
			});
			
		}

		function guidePDF(url){
			window.open(url);
		}

		function deleteRow(id){
			$("#modal-delete-text").text('¿Está seguro de eliminar el registro '+id+'?');
			$("#modal-delete-id").text(id);
			$("#modalDelete").modal('show');
		}

		function deleteItem(btn){
			if($("#txtNumber").val()==0){
				$(btn).parent().parent().remove();
			}
		}

		function loadEnterprise(){
			$.post('../../phps/buk_load.php', {type: "all"}, function(data, textStatus, xhr) {
				$("#listFilterEnterprise").append('<option value="0" selected>PULMODON</option>');
				if(data!=0){
					var data = JSON.parse(data);
				//	alert(data.lenght);
					for(i=0;i<data.length;i++){
						$("#listFilterEnterprise").append('<option value="0" selected>ESTRELLA</option>');
						//$("#listFilterEnterprise").append('<option value="'+data[i]["Emp_codigo"]+'">'+data[i]["EmpSigla"]+'</option>');
					}
				}
			});
		}

		function loadPlant(){
			$.post('../../phps/plant_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					if(data[i]["Pl_codigo"]<10){
						data[i]["Pl_codigo"] = '0'+data[i]["Pl_codigo"];
					}
					$("#listPlant").append('<option value="'+data[i]["Pl_codigo"]+'">'+data[i]["PlNombre"]+'</option>');
				}
				// if(userProfile=='ADM'){
				// 	$("#listPlant").val('09');
				// }
			});
		}

		function loadPlantB(){
			$.post('../../phps/plant_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					if(data[i]["Pl_codigo"]<10){
						data[i]["Pl_codigo"] = '0'+data[i]["Pl_codigo"];
					}
					$("#listPlantB").append('<option value="'+data[i]["Pl_codigo"]+'">'+data[i]["PlNombre"]+'</option>');
				}
				// if(userProfile=='ADM'){
				// 	$("#listPlant").val('09');
				// }
			});
		}
// Campos Buk 2
		function loadPlant2(){
			$.post('../../phps/plant_Load.php', {type: "allb"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					if(data[i]["Pl_codigo"]<10){
						data[i]["Pl_codigo"] = '0'+data[i]["Pl_codigo"];
					}
					$("#listPlant2").append('<option value="'+data[i]["Pl_codigo"]+'">'+data[i]["PlNombre"]+'</option>');
				}
				
			});
		}

// Campos Atr. Personalizado Buk 3
function loadPlant3(){
			$.post('../../phps/plant_Load.php', {type: "allb"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					if(data[i]["Pl_codigo"]<10){
						data[i]["Pl_codigo"] = '0'+data[i]["Pl_codigo"];
					}
					$("#listPlant3").append('<option value="'+data[i]["Pl_codigo"]+'">'+data[i]["PlNombre"]+'</option>');
				}
				
			});
		}

// Campos Atr. Personalizado Buk 4
function loadPlant4(){
			$.post('../../phps/plant_Load.php', {type: "allb"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					if(data[i]["Pl_codigo"]<10){
						data[i]["Pl_codigo"] = '0'+data[i]["Pl_codigo"];
					}
					$("#listPlant4").append('<option value="'+data[i]["Pl_codigo"]+'">'+data[i]["PlNombre"]+'</option>');
				}
				
			});
		}		

// Campos Atr. Personalizado Buk 4
function loadPlant5(){
			$.post('../../phps/plant_Load.php', {type: "allb"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					if(data[i]["Pl_codigo"]<10){
						data[i]["Pl_codigo"] = '0'+data[i]["Pl_codigo"];
					}
					$("#listPlant5").append('<option value="'+data[i]["Pl_codigo"]+'">'+data[i]["PlNombre"]+'</option>');
				}
				
			});
		}		

// Campos Atr. Personalizado Buk 3
function loadPlant6(){
			$.post('../../phps/plant_Load.php', {type: "allb"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					if(data[i]["Pl_codigo"]<10){
						data[i]["Pl_codigo"] = '0'+data[i]["Pl_codigo"];
					}
					$("#listPlant6").append('<option value="'+data[i]["Pl_codigo"]+'">'+data[i]["PlNombre"]+'</option>');
				}
				
			});
		}

// Campos Atr. Personalizado Buk
function loadPeriodosBuk(){
			$.post('../../phps/guideGenerate_Load.php', {type: "periodo"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					$("#listMonth").append('<option value="'+data[i]["mes"]+'">'+data[i]["mes"]+' - '+data[i]["statusF"]+'</option>');
				}
				
			});
		}

		function loadFilterClient(){
			$.post('../../phps/client_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				$("#listFilterClient").append('<option value="0" selected>TODOS</option>');
				for(i=0;i<data.length;i++){
					$("#listFilterClient").append('<option value="'+data[i]["id"]+'">'+data[i]["name"]+'</option>');
				}
			});
		}

		function loadBranch(){
			$.post('../../phps/branch_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				$("#listBranch").append('<option value="0" selected>SELECCIONE</option>');
				$("#listFilterBranch").append('<option value="0" selected>TODOS</option>');
				if(data!=0){
					var data = JSON.parse(data);
					for(i=0;i<data.length;i++){
						// $("#listBranch").append('<option value="'+data[i]["id"]+'">'+data[i]["name"]+'</option>');
						// $("#listFilterBranch").append('<option value="'+data[i]["id"]+'">'+data[i]["name"]+'</option>');
					}
				}
			});
		}

		function loadYears(){
			$.post('../../phps/contract_Load.php', {type: "year"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				$("#listFilterYear").append('<option value="0" selected>TODOS</option>');
				var d = new Date();
				var n = d.getFullYear();
				for(i=0;i<data.length;i++){
					if(n==data[i]["year"]){
						$("#listFilterYear").append('<option value="'+data[i]["year"]+'" selected>'+data[i]["year"]+'</option>');
					}else{
						$("#listFilterYear").append('<option value="'+data[i]["year"]+'">'+data[i]["year"]+'</option>');
					}
				}
			});
		}

		function loadClient(set){
			$("#listClient").html('<option value="0" selected>SELECCIONE</option>');
			if($("#listBranch").val()!=0 && $("#listBranch").val()!=null){
				$.post('../../phps/contract_Load.php', {type: "allGroupClient", enterprise_id: 0, client_id: 0, branch_id: $("#listBranch").val(), year: 0}, function(data, textStatus, xhr) {
					var data = JSON.parse(data);
					for(i=0;i<data.length;i++){
						if(set==data[i]["id"]){
							$("#listClient").append('<option value="'+data[i]["id"]+'" selected>'+data[i]["client"]+'</option>');
						}else{
							$("#listClient").append('<option value="'+data[i]["id"]+'">'+data[i]["client"]+'</option>');
						}
					}
				});
			}
		}

		function loadContract(set, client_id){
			if(client_id==undefined){
				client_id=$("#listClient").val();
			}
			$("#listContract").html('<option value="0" selected>SELECCIONE</option>');
			if(($("#listBranch").val()!=0 && $("#listBranch").val()!=null) && client_id!=0){
				$.post('../../phps/contract_Load.php', {type: "all", enterprise_id: 0, client_id: client_id, branch_id: $("#listBranch").val(), year: 0}, function(data, textStatus, xhr) {
					var data = JSON.parse(data);
					for(i=0;i<data.length;i++){
						if(set==data[i]["id"]){
							$("#listContract").append('<option value="'+data[i]["id"]+'" selected>'+data[i]["code_external"]+'</option>');
						}else{
							$("#listContract").append('<option value="'+data[i]["id"]+'">'+data[i]["code_external"]+'</option>');
						}
					}
				});
			}
			//loadContractData(true);
		}

		function loadContractData(goProduct, contractId){
			if($("#listContract").val()!=0 || contractId!=null){
				var contract_id = $("#listContract").val();
				if(contractId!=null){
					contract_id=contractId;
				}
				$.post('../../phps/contract_Load.php', {type: "oneGuide", contract_id: contract_id}, function(data, textStatus, xhr) {
					var data = JSON.parse(data);
					$("#lblEnterpriseRUT").text(data[0]['enterprise_rut']);
					$("#lblEnterpriseName").text(data[0]['enterprise_name']);
					$("#lblEnterpriseAddress").text(data[0]['enterprise_address']);
					$("#lblEnterpriseActivity").text(data[0]['enterprise_activity']);
					$("#lblClientRUT").text(data[0]['client_rut']);
					$("#lblClientName").text(data[0]['client_name']);
					$("#lblClientAddress").text(data[0]['client_address']);
					$("#lblClientActivity").text(data[0]['client_activity']);
					loadProductContract(goProduct);
				});
			}else{
				$("#lblEnterpriseRUT").text('');
				$("#lblEnterpriseName").text('');
				$("#lblEnterpriseAddress").text('');
				$("#lblEnterpriseActivity").text('');
				$("#lblClientRUT").text('');
				$("#lblClientName").text('');
				$("#lblClientAddress").text('');
				$("#lblClientActivity").text('');
				$("#tableItemsBody").html('');
			}
		}

		function loadContractItems(id){
			$("#tableItemsBody").html('');
			$.post('../../phps/contract_Load.php', {type: "allItems", contract_id: id}, function(data, textStatus, xhr) {
				
				if(data!=0){
					var data = JSON.parse(data);
					var list = "";
					/*for(i=0;i<data.length;i++){

						list += '<tr>' +
									'<td>N</td>' +
									'<td>'+listProductsContract.replace('value="'+data[i]['product_id']+'"','value="'+data[i]['product_id']+'" selected')+'</td>' +
									'<td>'+listProducts.replace('value="'+data[i]['product_id']+'"','value="'+data[i]['product_id']+'" selected')+'</td>' +
									'<td>'+listProducts+'</td>' +
									'<td>'+listQuarters.replace('value="'+data[i]['quarter']+'"','value="'+data[i]['quarter']+'" selected')+'</td>' +
									'<td><input style="width: 75px;"/></td>' +
									'<td><input style="width: 75px; text-align: right;" class="numbersOnlyFloat" onfocus="returnNoSeparator()" onfocusout="returnSeparator(); calculate(this);"/></td>' +
									'<td><input style="width: 75px; text-align: right;" class="numbersOnlyFloat" onfocus="returnNoSeparator()" onfocusout="returnSeparator()"/></td>' +
									'<td><input style="width: 75px; text-align: right;" class="numbersOnlyFloat" onfocus="returnNoSeparator()" onfocusout="returnSeparator(); calculate(this);"/></td>' +
									'<td><input style="width: 75px; text-align: right;" class="numbersOnlyFloat" value="0" disabled/></td>' +
									'<td><input style="width: 200px;" maxlength="200"/></td>' +
									'<td><button class="btn btn-danger" onclick="deleteItem(this)"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button></td>' +
								'</tr>';

 						if(i+1==data.length){
							$("#tableItemsBody").append(list);
							startParameters();
							calculateTotal();
						}
					}*/

					list += '<tr>' +
								'<td>N</td>' +
								'<td>'+listProductsContract+'</td>' +
								'<td>'+listProducts+'</td>' +
								'<td>'+listProducts+'</td>' +
								'<td>'+listQuarters+'</td>' +
								'<td><input style="width: 75px;"/></td>' +
								'<td><input style="width: 75px; text-align: right;" class="numbersOnlyFloat" onfocus="returnNoSeparator()" onfocusout="returnSeparator(); calculate(this);"/></td>' +
								'<td><input style="width: 75px; text-align: right;" class="numbersOnlyFloat" onfocus="returnNoSeparator()" onfocusout="returnSeparator()"/></td>' +
								'<td><input style="width: 75px; text-align: right;" class="numbersOnlyFloat" onfocus="returnNoSeparator()" onfocusout="returnSeparator(); calculate(this);"/></td>' +
								'<td><input style="width: 75px; text-align: right;" class="numbersOnlyFloat" value="0" disabled/></td>' +
								'<td><input style="width: 200px;" maxlength="200"/></td>' +
								'<td><button class="btn btn-danger" onclick="deleteItem(this)"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>' + 
								'</td>' +
							'</tr>';

					$("#tableItemsBody").append(list);
					startParameters();
					calculateTotal();


				}
			});
		}

		function loadProduct(){
			$.post('../../phps/product_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				listProducts = '<select><option value="0" selected>SELECCIONE</option>';
				for(i=0;i<data.length;i++){
					listProducts += '<option value="'+data[i]["id"]+'">'+data[i]["name"]+'</option>';
					if(i+1==data.length){
						listProducts += '</select>';
					}
				}
			});
		}

		function loadProductContract(goProduct, contract_id){
			if(contract_id==undefined){
				contract_id = $("#listContract").val();
			}
			$.post('../../phps/product_Load.php', {type: "allContract", contract_id: contract_id}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				listProductsContract = '<select><option value="0" selected>SELECCIONE</option>';
				for(i=0;i<data.length;i++){
					listProductsContract += '<option value="'+data[i]["id"]+'_'+data[i]["contract_item_id"]+'">'+data[i]["name"]+' ($'+data[i]["price"]+')</option>';
					if(i+1==data.length){
						listProductsContract += '</select>';
						if(goProduct){
							loadContractItems($("#listContract").val());
						}
					}
				}
			});
		}

		function loadQuarter(){
			$.post('../../phps/branch_Load.php', {type: "quarter", id: $("#listBranch").val()}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				listQuarters = '<select><option value="0" selected>SELECCIONE</option>';
				for(i=0;i<data.length;i++){
					listQuarters += '<option value="'+data[i]["id"]+'">'+data[i]["quarter"]+'</option>';
					if(i+1==data.length){
						listQuarters += '</select>';
					}
				}
			});
		}

		function loadDataExcel(){

			var head =	'<thead>' +
							'<tr>' +
								'<th>Fecha de Contrato</th>' +
								'<th>N° Contrato Interno</th>' +
								'<th>Empresa</th>' +
								'<th>Campo</th>' +
								'<th>Cliente</th>' +
								'<th>Variedad</th>' +
								'<th>Kilos</th>' +
								'<th>Hectareas</th>' +
								'<th>Kilos Despachados</th>' +
								'<th>N° Contrato Externo</th>' +
								'<th>Tipo Kilo</th>' +
								'<th>Tipo Contrato</th>' +
							'</tr>' +
						'</thead>';
			$("#tableDataExcel").html(head+'<tbody id="tableDataExcelBody"></tbody>');
			
			$.post('../../phps/contract_Load.php', {type: "excel", enterprise_id: $("#listFilterEnterprise").val(), client_id: $("#listFilterClient").val(), branch_id: $("#listFilterBranch").val(), year: $("#listFilterYear").val()}, function(data, textStatus, xhr) {
				
				if(data!=0){
					var data = JSON.parse(data);
					var list = "";
					for(i=0;i<data.length;i++){

						var dateArray = data[i]['date']['date'].split(' ')[0].split('-');
						var date = dateArray[2]+'/'+dateArray[1]+'/'+dateArray[0];
						list += '<tr>' +
									'<td>'+date+'</td>' +
									'<td>'+data[i]['code_internal']+'</td>' +
									'<td>'+data[i]['enterprise']+'</td>' +
									'<td>'+data[i]['branch']+'</td>' +
									'<td>'+data[i]['client']+'</td>' +
									'<td>'+data[i]['product']+'</td>' +
									'<td>'+data[i]['weight']+'</td>' +
									'<td>'+data[i]['hectare']+'</td>' +
									'<td>'+data[i]['dispatched']+'</td>' +
									'<td>'+data[i]['code_external']+'</td>' +
									'<td>'+data[i]['contract_type']+'</td>' +
									'<td>'+data[i]['contract_condition']+'</td>' +
								'</tr>';

						if(i+1==data.length){
							$("#tableDataExcelBody").append(list);
							$("#tableDataExcel").table2excel({
								exclude: ".noExl",
								name: "Excel Document Name",
								filename: "Lista",
								fileext: ".xls",
								exclude_img: true,
								exclude_links: true,
								exclude_inputs: true
							});
						}
					}
				}
			});
		}

		function calculate(input){
			var weight = parseFloat(toNoSeparator($($($(input).parent().parent().children()[6]).children()[0]).val()));
			var price = parseFloat(toNoSeparator($($($(input).parent().parent().children()[8]).children()[0]).val()));

			if($.isNumeric(weight) && $.isNumeric(price)){
				$($($(input).parent().parent().children()[9]).children()[0]).val(toSeparator(weight*price));
			}else{
				$($($(input).parent().parent().children()[9]).children()[0]).val(0);
			}
			calculateTotal();
		}

		function calculateTotal(){
			returnNoSeparator();
			var net = 0;
			$("#tableItemsBody > tr").each(function() {
				net += parseFloat($($($(this).children()[9]).children()[0]).val());
			});
			var iva = parseFloat(net*0.19);
			var total = net+iva;

			$("#txtTotalNet").val(net.toFixed(0));
			$("#txtTotalIVA").val(iva.toFixed(0));
			$("#txtTotal").val(total.toFixed(0));

			returnSeparator();			
		}

		function cleanModal(){
			$("#divID").css('display','none');
			$("#txtID").val('');
			$("#txtID").prop('disabled', 'false');
			$("#listEnterprise").val(0);
			$("#listBranch").val(0);
			$("#listClient").val(0);
			$("#listContract").html('<option value="0" selected>SELECCIONE</option>');
			$("#txtDate").val(moment().format('DD/MM/YYYY'));
			$("#txtNumber").val('');
			$("#txtObservation").val('');
			$("#lblEnterpriseRUT").text('');
			$("#lblEnterpriseName").text('');
			$("#lblEnterpriseAddress").text('');
			$("#lblEnterpriseActivity").text('');
			$("#lblClientRUT").text('');
			$("#lblClientName").text('');
			$("#lblClientAddress").text('');
			$("#lblClientActivity").text('');
			$("#tableItemsBody").html('');

			$('#txtReceiverName').val('');
			$('#txtReceiverRUT').val('');
			$('#txtReceiverDate').val('');
			$('#txtReceiverAddress').val('');
			$('#txtReceiverPlate1').val('');
			$('#txtReceiverPlate2').val('');

			$("#txtTotalNet").val(0);
			$("#txtTotalIVA").val(0);
			$("#txtTotal").val(0);
			
			$("#listGuideType").val('ELECTRONICA');



			$("#btnPreview").attr('disabled','disabled');
			$("#btnGenerate").attr('disabled','disabled');
			$("#save").removeAttr('disabled');
			$("#btnViewPDF").attr('onclick','');
			$("#btnViewPDF").attr('disabled','disabled');



			$(".guideClass").removeAttr('disabled');

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
			<div class="panel panel-lucuma">
				<div class="panel-heading"><i class="fa fa-file-text fa-lg fa-fw"></i>&nbsp;&nbsp; Integracion Buk</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<!-- <button id="new" class="btn btn-lucuma"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>&nbsp;&nbsp; Ingresar Nuevo</button></td>			 -->
							<br/>
							<br/>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-10 col-lg-10">
							<div class="panel panel-lucuma">
								<div class="row">
									<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
									    <label style="display: inline-block; width:35%;">ENVIAR TRATOS BUK</label>
										<p></p>
										<label style="display: inline-block; width:35%;">Campo Nvo. Buk:</label>
										<select id="listPlant2" class="form-control" style="display: inline-block; width:60%;">
										</select>
							  			
										<!--<select id="listFilterEnterprise" class="form-control" style="display: inline-block; width:60%;">
										</select> -->
										<br/>
										<label style="display: inline-block; width:35%;">Trabajador (99.999.999)</label>
										<input id="txtRut" type="number" min="1" max="99999999" class="form-control">
										<br/>
										<label style="display: inline-block; width:35%;">Periodo</label>
										<select id="listMonth" class="form-control">
										</select>
										<button id="btnGenerate" class="btn btn-danger" title="Cargar Tarjas a BUK" style="text-align: center;">
														<i class="fa fa-file-pdf-o fa-2x fa-fw"></i>
														<br/>Cargar Tarjas a BUK
										</button>
										<p></p>
										<p></p>
										<button id="btnExcel" class="btn btn-success">Cambiar Periodo Sys</button>		
										<br/>
										<p></p>
										<label style="display: inline-block; width:35%;">DESCARGA TARIFAS y OTROS BUK</label>
										<p></p>
										<label style="display: inline-block; width:35%;">Campo Nvo. Buk:</label>
										<select id="listPlant6" class="form-control" style="display: inline-block; width:60%;">
										</select>
										<p></p>
										<button id="btnAdmTarifas" class="btn btn-success">Descargar tarifas y ADM Buk</button>		
										<br/>
										<p></p>
										<label style="display: inline-block; width:35%;">DESCARGAR INF AUSENCIAS</label>
										<p></p>
										<button id="btnInformeAusencia" class="btn btn-success">Bajar Informe</button>		
										<br/><p></p>
										<label style="display: inline-block; width:35%;">DESCARGAR INF H. EXTRAS</label>
										<p></p>
										<button id="btnInformeHoras" class="btn btn-success">Bajar Informe</button>		
										<br/>

										<table id="tableDataTallyExcel" style="display: none;">
								        </table>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
										<label style="display: inline-block; width:35%;">ENVIAR EMPLEADOS BUK</label>
										<p></p>
							  			<label style="display: inline-block; width:35%;">Campo Antiguo</label>
										<select id="listPlant" class="form-control" style="display: inline-block; width:60%;">
										</select>
										<br/>
										<label style="display: inline-block; width:35%;">Trabajador (99.999.999)</label>
										<input id="txtRut2" type="number" min="1" max="99999999" class="form-control">
										<br/>
										<label style="display: inline-block; width:35%;">ID Area:</label>
										<input id="idArea" type="number" min="1" max="999" class="form-control">
										<br/>
										<label style="display: inline-block; width:35%;">ID Supervisor:</label>
										<input id="idLeader" type="number" min="1" max="999" class="form-control">
										<br/>
										<label style="display: inline-block; width:35%;">ID Cargo:</label>
										<input id="idRole" type="number" min="1" max="999" class="form-control">
										<br/>
										<label style="display: inline-block; width:35%;">Campo Nvo. Buk:</label>
										<select id="listPlant3" class="form-control" style="display: inline-block; width:60%;">
										</select>
										<button id="btnWork" class="btn btn-success">Enviar Empleados Buk</button>	

										<!-- <option value="1">julio 2022</option>
				    	    			<option value="2">Agosto 2022</option>	-->	<br/>
										</div>
										<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2" style="text-align: center;">
										<br/>
										<label style="display: inline-block; width:35%;">Campo Antiguo</label>
										<select id="listPlantB" class="form-control" style="display: inline-block; width:60%;">
										</select>
										<br/>
										
										<p></p>
										<label style="display: inline-block; width:35%;">Enviar a Campo Buk:</label>
										<select id="listPlant4" class="form-control" style="display: inline-block; width:60%;">
										</select>
										<label style="display: inline-block; width:35%;">Trabajador (99.999.999)</label>
										<input id="txtRut3" type="number" min="1" max="99999999" class="form-control">
										<br/>
										<p></p>
										<button id="btnExTarjas" class="btn btn-success">Extraer Tjs Sys</button>
										<p></p>
										<label style="display: inline-block; width:35%;">Enviar CC a Buk:</label>
										<select id="listPlant5" class="form-control" style="display: inline-block; width:60%;">
										</select>
										<label style="display: inline-block; width:35%;">Trabajador (99.999.999)</label>
										<input id="txtRut4" type="number" min="1" max="99999999" class="form-control">
										<br/>
										<p></p>
										<button id="btnEnvioCC" class="btn btn-success">Enviar CC</button>
										<table id="tableDataTallyExcel2" style="display: none;">
								        </table>
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
		        	<button id="modalHide" type="button" class="btn btn-lucuma">Aceptar</button>
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
		        	<button id="modalDeleteHide" type="button" class="btn btn-lucuma">Cancelar</button>
		      	</div>
		    </div>
		</div>
	</div>

	<div id="modalCambiarPer" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
	        	<div class="modal-body">
		    	    <p id="modal-change-text"></p>
					<label>Ingresa Nvo periodo de Sistema </label><p></p>
					<label>Mes: </label><input id="txtMonth" type="number" min="1" max="12" class="form-control"></input>
					<label>Año: </label><input id="txtYear" type="number" min="2022" max="2025"  class="form-control"></input>
		    	    <p id="modal-change-id" style="visibility: hidden"></p>
		      	</div>
		      	<div class="modal-footer">
		        	<button id="changePer" type="button" class="btn btn-danger">Cambiar</button>
		        	<button id="modalCambiarPerHide" type="button" class="btn btn-lucuma">Cancelar</button>
		      	</div>
		    </div>
		</div>
	</div>

	<div id="modalCambiarPer2" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
	        	<div class="modal-body">
		    	    <p id="modal-change-text"></p>
					<label>Periodo Informe Inasistencias </label><p></p>
					<label>Mes: </label><input id="txtMonth3" type="number" min="1" max="12" class="form-control"></input>
					<label>Año: </label><input id="txtYear3" type="number" min="2022" max="2025"  class="form-control"></input>
		    	    <p id="modal-change-id" style="visibility: hidden"></p>
		      	</div>
		      	<div class="modal-footer">
		        	<button id="changePer2" type="button" class="btn btn-danger">Cambiar</button>
		        	<button id="modalCambiarPerHide2" type="button" class="btn btn-lucuma">Cancelar</button>
		      	</div>
		    </div>
		</div>
	</div>

	<div id="modalCambiarPer3" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
	        	<div class="modal-body">
		    	    <p id="modal-change-text"></p>
					<label>Periodo Informe H. Extras </label><p></p>
					<label>Mes: </label><input id="txtMonth4" type="number" min="1" max="12" class="form-control"></input>
					<label>Año: </label><input id="txtYear4" type="number" min="2022" max="2025"  class="form-control"></input>
		    	    <p id="modal-change-id" style="visibility: hidden"></p>
		      	</div>
		      	<div class="modal-footer">
		        	<button id="changePer3" type="button" class="btn btn-danger">Cambiar</button>
		        	<button id="modalCambiarPerHide3" type="button" class="btn btn-lucuma">Cancelar</button>
		      	</div>
		    </div>
		</div>
	</div>

	<div id="modalBajando" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
	        	<div class="modal-body">
		    	    <p id="modal-change-text"></p>
					<label>Espere un momento... procesando la solicitud.</label><p></p>

				</div>		      
		    </div>
		</div>
	</div>

	<div id="modalOldTally" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
	        	<div class="modal-body">
		    	    <p id="modal-change-text"></p>
					<label>Elige Periodo a Capturar</label><p></p>
					<label>Mes: </label><input id="txtMonth2" type="number" min="1" max="12" class="form-control"></input>
					<label>Año: </label><input id="txtYear2" type="number" min="2022" max="2025"  class="form-control"></input>
		    	    <p id="modal-change-id" style="visibility: hidden"></p>
		      	</div>
		      	<div class="modal-footer">
		        	<button id="combinarTally" type="button" class="btn btn-danger">Extraer</button>
		        	<button id="modalOldTallyHide" type="button" class="btn btn-lucuma">Cancelar</button>
		      	</div>
		    </div>
		</div>
	</div>

	<div id="modalEnvioCC" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
	        	<div class="modal-body">
		    	    <p id="modal-change-text"></p>
					<label>Elige Periodo a Enviar CC</label><p></p>
					<label>Mes: </label><input id="txtMonth33" type="number" min="1" max="12" class="form-control"></input>
					<label>Año: </label><input id="txtYear33" type="number" min="2022" max="2025S" class="form-control"></input>
		    	    <p id="modal-change-id" style="visibility: hidden"></p>
		      	</div>
		      	<div class="modal-footer">
		        	<button id="enviarCC" type="button" class="btn btn-danger">Extraer</button>
		        	<button id="modalEnvioCCHide" type="button" class="btn btn-lucuma">Cancelar</button>
		      	</div>
		    </div>
		</div>
	</div>

	<div id="modalNew" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-xxl">
			<div class="modal-content">
	        	<div class="modal-body">
				   	<div id="addNew" class="container-fluid">
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
								<div class="panel panel-lucuma">
									<div class="panel-heading"><span class="glyphicon glyphicon-copy" aria-hidden="true"></span>&nbsp;&nbsp; Ingreso de Registro</div>
									<div class="panel-body">
										<div class="container-fluid">
											<div class="row">

												<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
										  			<label>Campo</label>
					  								<select id="listPlant" class="form-control guideClass">
													</select>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
										  			<label>Cliente</label>
					  								<select id="listClient" class="form-control guideClass">
													</select>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
										  			<label>Contrato</label>
					  								<select id="listContract" class="form-control guideClass">
													</select>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
										  			<label>Fecha</label>
													<input id="txtDate" type="text" class="form-control datepickerTxt guideClass">
												</div>
												<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
										  			<label>Tipo</label>
													<select id="listGuideType" class="form-control">
														<option value="ELECTRONICA">ELECTRONICA</option>
														<option value="MANUAL">MANUAL</option>
													</select>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
										  			<label>N°</label><br/>
													<input id="txtNumber" type="text" class="form-control" style="display: inline-block; width: 54%; text-align: center;" disabled>
													<button id="btnViewPDF" class="btn btn-danger" style="display: inline-block; width: 30%;" disabled><i class="fa fa-file-pdf-o fa-lg fa-fw"></i></button>
												</div>

												<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
													<br/>
												</div>

												<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
													<div class="panel panel-lucuma">
											  			<div>
											  				<label style="display: inline-block;">Empresa</label>
											  			</div>
											  			<br/>
											  			<label style="font-size: 13px; display: inline-block; width: 16%;">RUT:</label>
											  			<label id="lblEnterpriseRUT" style="font-size: 13px; display: inline-block; width: 82%;"></label>
											  			<label style="font-size: 13px; display: inline-block; width: 16%;">Nombre:</label>
											  			<label id="lblEnterpriseName" style="font-size: 13px; display: inline-block; width: 82%;"></label>
											  			
											  			<label style="font-size: 13px; display: inline-block; width: 16%;">Dirección:</label>
											  			<label id="lblEnterpriseAddress" style="font-size: 13px; display: inline-block; width: 82%;"></label>
											  			<label style="font-size: 13px; display: inline-block; width: 16%;">Giro:</label>
											  			<label id="lblEnterpriseActivity" style="font-size: 13px; display: inline-block; width: 82%;"></label>
													</div>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
													<div class="panel panel-lucuma">
											  			<div>
											  				<label style="display: inline-block;">Cliente</label>
											  			</div>
											  			<br/>
											  			<label style="font-size: 13px; display: inline-block; width: 20%;">RUT:</label>
											  			<label id="lblClientRUT" style="font-size: 13px; display: inline-block; width: 78%;"></label>
											  			<label style="font-size: 13px; display: inline-block; width: 20%;">Nombre:</label>
											  			<label id="lblClientName" style="font-size: 13px; display: inline-block; width: 78%;"></label>
											  			
											  			<label style="font-size: 13px; display: inline-block; width: 20%;">Dirección:</label>
											  			<label id="lblClientAddress" style="font-size: 13px; display: inline-block; width: 78%;"></label>
											  			<label style="font-size: 13px; display: inline-block; width: 20%;">Giro:</label>
											  			<label id="lblClientActivity" style="font-size: 13px; display: inline-block; width: 78%;"></label>
													</div>
												</div>
												
												<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
										  			<label style="display: inline-block; width: 12%;">Observaciones</label>
													<input id="txtObservation" type="text" class="form-control guideClass" style="display: inline-block; width: 86%;" maxlength="300">
												</div>
												<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
													<br/>
												</div>

												<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
													<div class="panel panel-lucuma">
														<div class="row">
															<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="text-align: center;">
																<button id="btnAddItem" class="btn btn-success guideClass"><i class="fa fa-plus fa-lg fa-fw"></i>&nbsp;&nbsp; Agregar Ítem</button>
															</div>
															<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
																<div style="overflow-y: scroll">
																	<table id="tableItems" class="table table-hover" style="font-size: 12px;">
																		<thead>
																			<tr>
																				<th>ID</th>
																				<th>Variedad Guía</th>
																				<th>Variedad Real 1</th>
																				<th>Variedad Real 2</th>
																				<th>Cuartel</th>
																				<th>Bins</th>
																				<th id="thQuantity">Kilos</th>
																				<th>Grado</th>
																				<th>Precio</th>
																				<th>Total</th>
																				<th>Observación</th>
																				<th>Eliminar</th>
																			</tr>
																		</thead>
																		<tbody id="tableItemsBody"></tbody>
																	</table>
																</div>
															</div>
														</div>
													</div>
												</div>

												<div class="col-xs-12 col-sm-12 col-md-5 col-lg-5">
													<div class="panel panel-lucuma">
														<div class="row">
															<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
																<label>Recibió:</label>
															</div>
															<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
																<label style="display: inline-block; width: 17%;">Nombre</label>
																<input id="txtReceiverName" type="text" class="form-control guideClass" style="display: inline-block; width: 76%;" maxlength="100">
															</div>
															<div class="col-xs-12 col-sm-12 col-md-7 col-lg-7">
																<label style="display: inline-block; width: 31%;">RUT</label>
																<input id="txtReceiverRUT" type="text" class="form-control guideClass" style="display: inline-block; width: 60%;" maxlength="100">
															</div>
															<div class="col-xs-12 col-sm-12 col-md-5 col-lg-5">
																<label style="display: inline-block; width: 24%;">Fecha</label>
																<input id="txtReceiverDate" type="text" class="form-control datepickerTxt guideClass" style="display: inline-block; width: 57%;">
															</div>
															<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
																<label style="display: inline-block; width: 17%;">Recinto</label>
																<input id="txtReceiverAddress" type="text" class="form-control guideClass" style="display: inline-block; width: 76%;" maxlength="100">
															</div>

															<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
																<label style="display: inline-block; width: 40%;">Pat. Camión</label>
																<input id="txtReceiverPlate1" type="text" class="form-control guideClass" style="display: inline-block; width: 50%;" maxlength="10">
															</div>
															<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
																<label style="display: inline-block; width: 40%;">Pat. Carro</label>
																<input id="txtReceiverPlate2" type="text" class="form-control guideClass" style="display: inline-block; width: 50%;" maxlength="10">
															</div>
														</div>
													</div>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3" style="text-align: center;">
													<button id="btnPreview" class="btn btn-primary" title="Generar Guía" style="text-align: center;" disabled>
														<i class="fa fa-search fa-2x fa-fw"></i>
														<br/>
														&nbsp;Previsualizar Guía
													</button>
													<br/>
													<br/>

													
												</div>
												<div class="col-xs-12 col-sm-12 col-md-1 col-lg-1">
													<br/>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
										  			<label style="display: inline-block; width:30%;">NETO</label>
									  				<input id="txtTotalNet" type="Name" class="form-control numbersOnlyFloat" style="display: inline-block; width:68%; text-align: right;" disabled/>
									  				<label style="display: inline-block; width:30%;">IVA</label>
									  				<input id="txtTotalIVA" type="Name" class="form-control numbersOnlyFloat" style="display: inline-block; width:68%; text-align: right;" disabled/>
									  				<label style="display: inline-block; width:30%;">TOTAL</label>
									  				<input id="txtTotal" type="Name" class="form-control numbersOnlyFloat" style="display: inline-block; width:68%; text-align: right;" disabled/>
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
											<div style="display:inline-block;"><button id="cancel" class="btn btn-lucuma"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span>&nbsp;&nbsp; Cancelar</button></div>
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