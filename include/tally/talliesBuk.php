<?php
header('Content-Type: text/html; charset=utf8'); 
session_start();

if(!isset($_SESSION['userId'])){
	header('Location: ../../login.php');
}elseif($_SESSION['display']['tallies']['view']!=''){
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
	<link rel="stylesheet" type="text/css" href="../../libs/datatables/datatables.min.css"/>
 	<script type="text/javascript" src="../../libs/datatables/datatables.min.js"></script>
	<script type="text/javascript" src="../../libs/jquery.table2excel.js"></script>	
	<link rel="stylesheet" type="text/css" href="../../libs/bootstrap-select/css/bootstrap-select.css"/>
 	<script type="text/javascript" src="../../libs/bootstrap-select/js/bootstrap-select.js"></script>
	<title></title>
	<script type="text/javascript">

	var listPersonal = '', listLabour = ''; listTipoTra = ''; listfechaBuk='';
	var userProfile = '<?php echo $_SESSION["profile"]; ?>';
	var maxExtraHours = 5;
	if(userProfile=='ADM'){
		maxExtraHours = 1000000;
	}

		$(document).ready(function() {
			$(function () {
			  $('[data-toggle="popover"]').popover()
			})

			loadMenu();
			startParameters();

			/*if(userProfile=='ADM'){
				$("#txtExtraHours").focusout(function(){
					verifyValue(this,$(this).val(),maxExtraHours,0);
				});
			}*/
			
			$(".datepickerTxt").datepicker({
				format: 'dd/mm/yyyy',
				weekStart: 1
			})
			$(".datepickerTxt").datepicker('setValue', '');

			$(".datepickerTxt").on('changeDate', function(ev) {
				$(".datepickerTxt").datepicker('hide');
			});

			$("#btnRefresh").click(function() {
				loadPersonal('full');
				loadData();
				loadLabour();
				//loadPeriodoBuk();
				//alert($("#listfechaBuk").val());
				
			});

			$("#btnRefreshBuk").click(function() {
				// loadPersonal('full');
				// loadData();
				// loadLabour();
				$("#modalProgress").modal('show');
				$.post('../../phps/talliesB_Load.php', {
				type: "newWorked",
				plant: $("#listPlant").val()
			    }, function(data, textStatus, xhr) {
				
				console.log(data);
				$("#modalProgress").modal('hide');
				$("#modal-text").text("Trabajadores de campo seleccionado Actualizados.");	
				$("#modal").modal('show');
				});
				//loadWorkedBuk();
				
				
			});

			
			$("#modalHide").click(function() {
				$("#modal").modal('hide');	
			});

			$("#modalListHide").click(function() {
				$("#modalList").modal('hide');	
			});

			$(".datepickerTxt").focusout(function (e) {
		       	if (moment($(this).val(), 'DD/MM/YYYY', true).isValid()) {
					//console.log("válida");
				} else {
					//console.log("no válida");
					$("#modal-text").text("Fecha ingresada no válida");
					$("#modal").modal('show');
					$(this).val(moment().format('DD/MM/YYYY'));
				}
		  	});

			$("#btnSelectAll").click(function() {
				if($(this).html()=='<i class="fa fa-check-square-o fa-lg fa-fw"></i>&nbsp;Seleccionar Todo'){
					$(this).html('<i class="fa fa-square-o fa-lg fa-fw"></i>&nbsp;Borrar Selección');
					$('#tableTallyBody > tr').each(function() {
						$($(this).children()[0]).find(">:first-child").prop('checked', true);
					});
				}else{
					$(this).html('<i class="fa fa-check-square-o fa-lg fa-fw"></i>&nbsp;Seleccionar Todo');
					$('#tableTallyBody > tr').each(function() {
						$($(this).children()[0]).find(">:first-child").prop('checked', false);
					});
				}
			});

			$("#modalTallyHide").click(function() {
				cleanModalTally();
			});


			$("#btnExcel").click(function() {
				toExcel($("#lblTallyPlantID").text(),$("#lblTallyPlant").text(),$("#lblTallyDate").text());
			});

			$("#btnAddTally").click(function() {
				if($("#listPlant").val()!=0 && $("#listPlant").val()!=98){


					$.post('../../phps/talliesb_Load.php', {
						type: 'getLastTally', 
						year: $("#listYear").val(),
		            	month: $("#listMonth").val(),
		            	plant: $("#listPlant").val()
					}, function(data, textStatus, xhr) {
						if(data==0){
							$("#modal-text").text("Mes seleccionado no está activo");
							$("#modal").modal('show');
						}else if(data==1){
							$("#modal-text").text("Última tarja aún está en Digitación");
							$("#modal").modal('show');
						}else if(data==2){
							$("#modal-text").text("No quedan fechas disponibles para el mes activo");
							$("#modal").modal('show');
						}else{
							$("#lblTallyNewPlant").text($("#listPlant option:selected").text());
							$("#lblTallyNewPlantID").text($("#listPlant").val());
							var data = data.split('_');
							$("#lblTallyNewDate").text(data[0]);
							$("#lblTallyNewDateDay").text(data[1]);
							
							$("#modalTallyNew").modal('show');
						}
					});


					
				}else{
					$("#modal-text").text("Debe seleccionar un Campo");
					$("#modal").modal('show');
				}
			});


			$("#btnTallyNewCancel").click(function() {
				$("#modalTallyNew").modal('hide');
				$("#lblTallyNewPlant").text('');
				$("#lblTallyNewDate").text('');
				$("#lblTallyNewDateDay").text('');
				$("#lblTallyNewPlantID").text('');
			});	

			$("#btnTallyNewNo").click(function() {
				$("#modalTallyNew").modal('hide');
				$("#lblTallyPlant").text($("#lblTallyNewPlant").text());
				$("#lblTallyDate").text($("#lblTallyNewDate").text());
				$("#lblTallyDateDay").text($("#lblTallyNewDateDay").text());
				$("#lblTallyPlantID").text($("#lblTallyNewPlantID").text());
				$("#lblTallyNewPlant").text('');
				$("#lblTallyNewDate").text('');
				$("#lblTallyNewDateDay").text('');
				$("#lblTallyNewPlantID").text('');

				$("#modalTally").modal('show');
			});

			$("#btnTallyNewYes").click(function() {
				$("#modalTallyNew").modal('hide');
				$("#lblTallyPlant").text($("#lblTallyNewPlant").text());
				$("#lblTallyDate").text($("#lblTallyNewDate").text());
				$("#lblTallyDateDay").text($("#lblTallyNewDateDay").text());
				$("#lblTallyPlantID").text($("#lblTallyNewPlantID").text());
				$("#lblTallyNewPlant").text('');
				$("#lblTallyNewDate").text('');
				$("#lblTallyNewDateDay").text('');
				$("#lblTallyNewPlantID").text('');
				loadPersonal('full');

				$("#modalProgress").modal('show');
				var analisisDate=$("#lblTallyDate").text();
				var aa=analisisDate.substring(2, 3);
				//alert(aa);
				if(aa=='/'){
					var arrayDate = $("#lblTallyDate").text().replaceAll('/','-');
				}
				//alert(arrayDate);
				$.post('../../phps/talliesb_Save.php', {
					type: 'repeat',
	            	date: arrayDate,
	            	plant: $("#lblTallyPlantID").text()
	            }, function(data, textStatus, xhr) {
	            	console.log(data)
	            	if(data=='OK'){
						modalTally('edit',$("#lblTallyDate").text(),$("#lblTallyPlantID").text(),$("#lblTallyPlant").text(),$("#lblTallyDateDay").text());
	            	}else{
	            		$("#modalProgress").modal('hide');
						$("#modal-text").text("Error al almacenar, contacte al administrador");
						$("#modal").modal('show');
	            	}
	            });
				$("#modalTally").modal('show');
			});

			$("#btnTallyCloseYes").click(function() {
				var tally_date = $("#lblTallyCloseDate").text();
				var plant_id = $("#lblTallyClosePlantID").text();
				var state = 'C';
				if($("#lblTallyCloseText").text()=='¿Desea reabrir esta Tarja?'){
					state = 'A';
				}
				$("#modalProgress").modal('show');
				$.post('../../phps/talliesb_Save.php', {
					type: 'close',
	            	date: tally_date,
	            	plant: plant_id,
	            	state: state
	            }, function(data, textStatus, xhr) {
	            	$("#modalTallyClose").modal('hide');
					console.log(data);
	            	if(data=='OK'){
						$("#modal-text").text("Tarja cerrada y Enviada A Buk.");
						//$("#modalProgress").modal('hide');
	            		loadData();
	            	}else{
						//$("#modalProgress").modal('hide');
						$("#modal-text").text("Error al almacenar, contacte al administrador");
						$("#modal").modal('show');
						loadData();

	            	}
	            });
			});

			$("#btnTallyCloseNo").click(function() {
				$("#lblTallyCloseDate").text('');
				$("#lblTallyClosePlantID").text('');
				$("#modalTallyClose").modal('hide');
				$("#modalProgress").modal('hide');
			});

			$("#btnDeleteTally").click(function() {
				deleteTally($("#lblTallyDate").text(),$("#lblTallyPlantID").text());
			});

			$("#btnTallyDeleteYes").click(function() {
				var tally_date = $("#lblTallyDeleteDate").text();
				var plant_id = $("#lblTallyDeletePlantID").text();
				
				$.post('../../phps/talliesb_Save.php', {
					type: 'delete',
	            	date: tally_date,
	            	plant: plant_id
	            }, function(data, textStatus, xhr) {
	            	$("#modalTallyDelete").modal('hide');
	            	console.log(data)
	            	if(data=='OK'){
	            		cleanModalTally();
	            		loadData();
	            	
	            	}else if(data=='NO_ADMIN'){
						$("#modal-text").text("No cuenta con permisos para eliminar, contacte al administrador");
						$("#modal").modal('show');
	            	
	            	}else{
						$("#modal-text").text("Error al almacenar, contacte al administrador");
						$("#modal").modal('show');
	            	}
	            });
			});

			$("#btnTallyDeleteNo").click(function() {
				$("#modalTallyDelete").modal('hide');
			});


			$("#btnAddPersonal").click(function() {
				var rowNumber = $("#tableTallyBody > tr").length + 1;
// 							'<td style="width: 15%; overflow: hidden;"><input class="form-control input-sm numbersOnlyFloat2 focused" style="text-align: right;" onfocusout="verifyValue(this,this.value,1,0,0)"></input></td>' +
//'<td style="width: 1%; overflow: hidden;"><input class="form-control input-sm numbersOnlyFloat2 focused" style="text-align: right;" disabled></input></td>' +	
				var row = '<tr>' +
							'<td style="width: 2%; overflow: hidden;"><input type="checkbox" class="focused"></input></td>' +
							'<td style="width: 2%; overflow: hidden;">'+rowNumber+'</td>' +
							'<td style="width: 20%; overflow: hidden;"><select id="listPersonal'+rowNumber+'" class="focused" data-live-search="true" data-width="fit" data-size="5" data-style="btn btn-default btn-xs" data-live-search-style="btn btn-default btn-xs" data-container="body">'+listPersonal+'</select></td>' +
							'<td style="width: 8%; overflow: hidden;"><select id="listTipoTra'+rowNumber+'" class="focused" data-live-search="true" data-width="fit" data-size="5" data-style="btn btn-default btn-xs" data-container="body" onChange="setLabourList(this)">'+listTipoTra+'</select></td>' +
							'<td style="width: 25%; overflow: hidden;"><select id="listLabour'+rowNumber+'" class="focused" data-live-search="true" data-width="fit" data-size="5" data-style="btn btn-default btn-xs" data-container="body" onChange="cambiarProductoUnidad(this)">'+listLabour+'</select></td>' +
							'<td style="width: 10%; overflow: hidden;"><input class="form-control input-sm numbersOnlyFloat2 focused" style="text-align: right;" onfocusout="verifyValue(this,this.value,10000,0,0) " disabled></input></td>' +
							'<td style="width: 10%; overflow: hidden;"><input class="form-control input-sm numbersOnlyFloat2 focused" style="text-align: right;" disabled></input></td>' +
							'<td style="width: 8%; overflow: hidden;"><input class="form-control input-sm numbersOnlyFloat2 focused" style="text-align: right;" disabled></input></td>' +
							'<td style="width: 5%; overflow: hidden;"><input class="form-control input-sm numbersOnlyFloat2 focused" style="text-align: right;" onfocusout="verifyValue(this,this.value,1,0,0) value="1"></input></td>' +
							'<td style="width: 5%; overflow: hidden;"><input class="form-control input-sm numbersOnlyFloat2 focused" style="text-align: right;" onfocusout="verifyValue(this,this.value,50000,0,0)"></input></td>' +
							'<td style="width: 5%; overflow: hidden;"><input class="form-control input-sm numbersOnlyFloat2 focused" style="text-align: right;" onfocusout="verifyValue(this,this.value,'+maxExtraHours+',0,1)"></input></td>' +
							
						'</tr>';
				$("#tableTallyBody").append(row);
				$("#listPersonal"+rowNumber).selectpicker();
				$("#listTipoTra"+rowNumber).selectpicker();
				$("#listLabour"+rowNumber).selectpicker();
				var modalProgressHide = false;
				startParameters();

				$('button[data-id=listPersonal'+rowNumber+']').focus(function(event){
					$($(".focused").parent().parent().children()).css('background-color','transparent');
					$(".info").css('background-color','#D9EDF7');
					$(".warning").css('background-color','#FCF8E3');
					$($(this).parent().parent().parent().children()).css('background-color','#ccff9a');
				});

				$('button[data-id=listTipoTra'+rowNumber+']').focus(function(event){
					$($(".focused").parent().parent().children()).css('background-color','transparent');
					$(".info").css('background-color','#D9EDF7');
					$(".warning").css('background-color','#FCF8E3');
					$($(this).parent().parent().parent().children()).css('background-color','#ccff9a');
				});

				$('button[data-id=listLabour'+rowNumber+']').focus(function(event){
					$($(".focused").parent().parent().children()).css('background-color','transparent');
					$(".info").css('background-color','#D9EDF7');
					$(".warning").css('background-color','#FCF8E3');
					$($(this).parent().parent().parent().children()).css('background-color','#ccff9a');
				});

				$(".focused").focus(function(event){
					$($(".focused").parent().parent().children()).css('background-color','transparent');
					$(".info").css('background-color','#D9EDF7');
					$(".warning").css('background-color','#FCF8E3');
					$($(this).parent().parent().children()).css('background-color','#ccff9a');
				});
				
			});
			
			$("#btnDeletePersonal").click(function() {
				var count = 0;
				var total = $('#tableTallyBody > tr').length;
				$('#tableTallyBody > tr').each(function() {
					if($(this).find('td').find(">:first-child").is(':checked')){ //Eliminación de filas seleccionadas
						$(this).remove();
					}
					count++;
					if(count==total){
						var rowNumber = 1;
						$('#tableTallyBody > tr').each(function() { //Reasignación de número e IDs de cada objeto de la fila
							if($($(this).children()[1]).text()!=rowNumber){

								var oldRowNumber = $($(this).children()[1]).text();

								$("#listPersonal"+oldRowNumber).selectpicker('destroy');
								$("#listPersonal"+oldRowNumber).prop('id','listPersonal'+rowNumber);
								$("#listPersonal"+rowNumber).selectpicker();

								$("#listLabour"+oldRowNumber).selectpicker('destroy');
								$("#listLabour"+oldRowNumber).prop('id','listLabour'+rowNumber);
							//	$("#listLabour"+rowNumber).attr('onchange','loadLabourCategory('+rowNumber+',this.value,0)');
								$("#listLabour"+rowNumber).selectpicker();

								$("#listTipoTra"+oldRowNumber).selectpicker('destroy');
								$("#listTipoTra"+oldRowNumber).prop('id','listTipoTra'+rowNumber);
							//	$("#listLabour"+rowNumber).attr('onchange','loadLabourCategory('+rowNumber+',this.value,0)');
								$("#listTipoTra"+rowNumber).selectpicker();

								$($(this).children()[1]).text(rowNumber);


							}

							rowNumber++;						
						});
					}
				});
			});

			$("#btnTallySave").click(function() {
				
				//$('#btnTallySave').prop('disabled', true);
				$("#modalProgress").modal('show');
				var count = 0;
				var total = $('#tableTallyBody > tr').length;
				var list = '';
				if(total>0){
					
					returnNoSeparator();
					var arrayList = {};
					var messageListPersonal = '';
					//alert("Todavia OK");
					// + '&&' +  $($($(this).children()[9]).children()[0]).val()

					$('#tableTallyBody > tr').each(function() {
						var rowNumber = $($(this).children()[1]).text();
						if(count>0){
							list += '&&&&';
						}

						list += $($(this).children()[1]).text() + '&&' +
								$("#listPersonal"+rowNumber).val() + '&&' +
								$("#listPersonal"+rowNumber+" option:selected").attr('data-value-name') + '&&' +
								$("#listTipoTra"+rowNumber).val() + '&&' +
								$("#listLabour"+rowNumber).val() + '&&' +
								$($($(this).children()[5]).children()[0]).val() + '&&' +
								$($($(this).children()[6]).children()[0]).val() + '&&' +
								$($($(this).children()[7]).children()[0]).val() + '&&' +
								$($($(this).children()[8]).children()[0]).val() + '&&' +
								$($($(this).children()[9]).children()[0]).val() + '&&' +
								$($($(this).children()[10]).children()[0]).val();

						//alert (list);
						labInd=$("#listLabour"+rowNumber).val();
						//alert (labInd);

						if($("#listPersonal"+rowNumber).val()==0){
							messageListPersonal += '<br/>' + $($(this).children()[1]).text();
						}

						if($("#listPersonal"+rowNumber).val() in arrayList ){
							arrayList[$("#listPersonal"+rowNumber).val()]['jornadatj'] += parseFloat($($($(this).children()[8]).children()[0]).val());
						}else{
							arrayList[$("#listPersonal"+rowNumber).val()] = {};
							arrayList[$("#listPersonal"+rowNumber).val()]['name'] = $("#listPersonal"+rowNumber+" option:selected").attr('data-value-name');
							arrayList[$("#listPersonal"+rowNumber).val()]['jornadatj'] = parseFloat($($($(this).children()[8]).children()[0]).val());						
						}

						count++;
						if(count==total){
							returnSeparator();
							if(messageListPersonal!=''){
								$("#modalProgress").modal('hide');
    							$("#modalList-text").html("Debe seleccionar trabajador en línea(s):" + messageListPersonal);
								$("#modalList").modal('show');
								return;
							} 

							var messageList = '';
							//Revisión de jornada
							for (key in arrayList){
    							if(arrayList[key]['jornadatj']!=1){
    								messageList += '<br/>' + arrayList[key]['name'] + " - " + toSeparator(arrayList[key]['jornadatj']);
    							}
    						}
							//alert(messageList);
							//alert (labInd);
    						 if(messageList!='' && labInd>2){
							 	$("#modalProgress").modal('hide');
    						 	$("#modalList-text").html("Hay trabajadores con Asistencias erróneas o incompletas:" + messageList);
							 	$("#modalList").modal('show');
							 	return;
							 }

							$.post('../../phps/talliesb_Save.php', {
								type: 'save', 
				            	plant: $("#lblTallyPlantID").text(),
								month: $("#listMonth").val(),
								year: $("#listYear").val(),
								date: $("#lblTallyDate").text(),
				            	total: total,
				            	list: list
							}, function(data, textStatus, xhr) {
								console.log(data);
								$("#modalProgress").modal('hide');
								if(data=='OK'){
									$("#modal-text").text("Registros almacenados correctamente");
									$("#modal").modal('show');
									$('#btnTallySave').prop('disabled', false);
									//cleanModalTally();
									//return;
								}else{
									$("#modal-text").text("Ha ocurrido un error, favor reintente (si el problema persiste, contacte al administrador)");
									$("#modal").modal('show');
									$('#btnTallySave').prop('disabled', false);
									//cleanModalTally();
									//return;
									
								}
							});
						}
					});
				}else{
					$("#modalProgress").modal('hide');
					$("#modal-text").text("Debe ingresar al menos 1 trabajador");
					$("#modal").modal('show');
				}
			});
			//alert("Aqui para ver q se ve despues de grabar");
			loadPlant();
			loadYear();
			loadTipTra();
			//loadLabour();
			
		});

function setLabourList(select){

let rowNumber = $(select).attr('id').split('listTipoTra')[1]

$('#listLabour'+rowNumber).selectpicker('destroy');
$('#listLabour'+rowNumber).html(listLabour)
if($(select).val()=='dia'){
	$('#listLabour'+rowNumber+' > option').each(function() {
		if($(this).attr('data-tarifa')!=0 && $(this).val()!=-1){
			$(this).remove()
		}
	});
}else if($(select).val()=='trato'){
	$('#listLabour'+rowNumber+' > option').each(function() {
		if($(this).attr('data-tarifa')==0 || $(this).val()==0 || $(this).val()==1 || $(this).val()==2){
			$(this).remove()
		}
	});
}else if($(select).val()=='ausencia'){
	$('#listLabour'+rowNumber+' > option').each(function() {
		if($(this).val()!=0 && $(this).val()!=1 && $(this).val()!=2 && $(this).val()!=-1){
			$(this).remove()
		}
	});
}
$('#listLabour'+rowNumber).selectpicker();

}

		function cambiarProductoUnidad(select){
		// console.log($(select).val());
		// console.log($(select).find('option:selected').attr('data-producto'));
		// console.log($(select).find('option:selected').attr('data-unidad'));
	    var nombreProducto = $(select).find('option:selected').attr('data-producto');
 		var nombreUnidad = $(select).find('option:selected').attr('data-unidad');
		var valorTarifa = $(select).find('option:selected').attr('data-tarifa');
		//console.log(valorTarifa);
		//console.log(nombreUnidad);
		
		//$($($(this).children()[4]).children()[0]).val() + '&&'
		
		if (nombreProducto==null){
			nombreProducto='No Aplica';
		}
		if (nombreUnidad==null){
			nombreUnidad='No Aplica';
		}
		if (valorTarifa==null){
			valorTarifa=0;
		}
		$($($(select).parent().parent().parent().children()[5]).children()[0]).val(valorTarifa); //Asignación de Valor Tarifa
 		$($($(select).parent().parent().parent().children()[6]).children()[0]).val(nombreProducto); //Asignación de nombre
 		$($($(select).parent().parent().parent().children()[7]).children()[0]).val(nombreUnidad); //Asignación de unidad
		
		// if (nombreProducto=='A' || nombreProducto=='L' || nombreProducto=='V'){
		// $($($(select).parent().parent().parent().children()[7]).children()[0]).prop('disabled', true); //Asignación de 
		// $($($(select).parent().parent().parent().children()[8]).children()[0]).val(0).prop('disabled', true);
		// $($($(select).parent().parent().parent().children()[9]).children()[0]).val(0).prop('disabled', true);	
		// }
		// $($($(select).parent().parent().parent().children()[8]).children()[0]).val(0); //Asignación de 
		// $($($(select).parent().parent().parent().children()[9]).children()[0]).val(0);
		// $($($(select).parent().parent().parent().children()[10]).children()[0]).val(0);
		
}
		function loadData(){

			$("#modalProgress").modal('show');
			$("#tableData").html('<thead><tr>' +
						'<th>Campo</th>' +
						'<th>Fecha Tarja</th>' +
						'<th>N° Trab.</th>' +
						'<th>Emitió</th>' +
						'<th>Fecha Emisión</th>' +
						'<th>Autorizó</th>' +
						'<th>Fecha V.B.</th>' +
						'<th>Estado</th>' +
						'<th>Ver/Editar</th>' +
						'<th>Excel</th>' +
						'<th>Cerrar</th>' +
						'<th>Eliminar</th>' +
					'</tr></thead><tbody id="tableDataBody"></tbody>');

			var chkDate = 0;
			if($("#chkDate").is(':checked')){
				chkDate = 1;
				if(userProfile!='ADM'){
					if(moment($("#txtDate").val(), 'DD/MM/YYYY', true).isSame($("#listYear").val()+'-'+$("#listMonth").val()+'-01', 'year')==false || moment($("#txtDate").val(), 'DD/MM/YYYY', true).isSame($("#listYear").val()+'-'+$("#listMonth").val()+'-01', 'month')==false){
						$("#modal-text").text("Debe seleccionar una fecha correspondiente al mes activo");
						$("#modal").modal('show');
						return;
					}
				}
			}

			$('#tableData').dataTable({
				destroy: true,
				paging: false,
				language: { "url": "../../libs/datatables/language/Spanish.json"},
                ajax: {
		            "url": "../../phps/talliesb_Load.php",
		            "type": "POST",
		            "data": {
		            	type: 'all',
		            	year: $("#listYear").val(),
		            	month: $("#listMonth").val(),
		            	state: $("#listState").val(),
		            	plant: $("#listPlant").val(),
		            	chkDate: chkDate,
		            	date: $("#txtDate").val()
					},
		            "dataSrc": ""
		        },
		        columnDefs: [
					{
						targets: [1,2,4,6],
						className: 'text-center'
				    }
				],
                columns: [
	                {"data" : "plant"},
					{"data" : "tally_date"},
					{"data" : "personal_quantity"},
					{"data" : "tally_user"},
					{"data" : "emission_date"},
					{"data" : "vb_user"},
					{"data" : "vb_date"},
					{"data" : "state"},
					{"data" : "edit"},
					{"data" : "excel"},
					{"data" : "close"},
					{"data" : "delete"}
                ],
                "fnInitComplete": function(oSettings, json) {
					$("#modalProgress").modal('hide');
			    }/*,
			     "fnSubmitError": function(oSettings, json) {
			     	console.log('error');
					$("#modalProgress").modal('hide');
			    }*/
            });
		}

		function modalTally(type,tally_date,plant_id,plant,day){
			if(type=='edit'){
				$("#btnTallySave").css('display','inline-block');
				$("#tableTally").css('pointer-events','auto');
				$("#btnSelectAll").css('display','inline-block');
				$("#btnAddPersonal").css('display','inline-block');
				$("#btnDeletePersonal").css('display','inline-block');
				
				$("#btnDeleteTally").css('display','inline-block');
			}else{
				$("#btnTallySave").css('display','none');
				$("#tableTally").css('pointer-events','none');
				$("#btnSelectAll").css('display','none');
				$("#btnAddPersonal").css('display','none');
				$("#btnDeletePersonal").css('display','none');
			}

			$("#modalTallyNew").modal('hide');
			$("#lblTallyPlant").text(plant);
			$("#lblTallyDate").text(tally_date);
			$("#lblTallyDateDay").text(day);
			$("#lblTallyPlantID").text(plant_id);
			$("#modalTally").modal('show');


			$("#modalProgress").modal('show');


			$.post('../../phps/talliesb_Load.php', {
				type: 'one',
            	date: tally_date,
            	plant: plant_id}, function(data, textStatus, xhr) {
            		//console.log(data);
				if(data!=0){
					var data = JSON.parse(data);
					for(i=0;i<data.length;i++){
		        		var rowNumber = data[i]["codtj"];
						//var yesTj=data[i]["siTj"];
						//	'<td style="width: 15%; overflow: hidden;"><input class="form-control input-sm numbersOnlyFloat2 focused" style="text-align: right;" onfocusout="verifyValue(this,this.value,1,0,0)" value="'+data[i]["nomLug"]+'"></input></td>' +
						// '<td style="width: 0%; overflow: hidden;"><input class="form-control input-sm numbersOnlyFloat2 focused" style="text-align: right;" onfocusout="verifyValue(this,this.value,'+maxExtraHours+',0,1)" value="'+data[i]["idLug"]+'"></input></td>' +
							//alert(data[i]["det_trato"]);  '<td style="width: 1%; overflow: hidden;"><input class="form-control input-sm numbersOnlyFloat2 focused" style="text-align: right;" value="'+data[i]["siTj"]+'" disabled></input></td>' +	
		        		console.log(listPersonal)
						var row = '<tr>' +
									'<td style="width: 2%; overflow: hidden;"><input type="checkbox" class="focused"></input></td>' +
									'<td style="width: 2%; overflow: hidden;">'+rowNumber+'</td>' +
									'<td style="width: 20%; overflow: hidden;"><select id="listPersonal'+rowNumber+'" class="focused" data-live-search="true" data-width="fit" data-size="5" data-style="btn btn-default btn-xs" data-live-search-style="btn btn-default btn-xs" data-container="body">'+listPersonal+'</select></td>' +
									'<td style="width: 8%; overflow: hidden;"><select id="listTipoTra'+rowNumber+'" class="focused" data-live-search="true" data-width="fit" data-size="5" data-style="btn btn-default btn-xs" data-container="body" onChange="setLabourList(this)">'+listTipoTra+'</select></td>' +
									'<td style="width: 25%; overflow: hidden;"><select id="listLabour'+rowNumber+'" class="focused" data-live-search="true" data-width="fit" data-size="5" data-style="btn btn-default btn-xs" data-container="body" onChange="cambiarProductoUnidad(this)">'+listLabour+'</select></td>' +
									'<td style="width: 8%; overflow: hidden;"><input class="form-control input-sm" style="text-align: right;"  value="'+data[i]["valtj"]+'" disabled></input></td>' +
									'<td style="width: 10%; overflow: hidden;"><input class="form-control input-sm" style="text-align: right;" value="'+data[i]["nomPro"]+'" disabled></input></td>' +
									'<td style="width: 10%; overflow: hidden;"><input class="form-control input-sm" style="text-align: right;" value="'+data[i]["nomUni"]+'" disabled></input></td>' +
									'<td style="width: 5%; overflow: hidden;"><input class="form-control input-sm numbersOnlyFloat2 focused" style="text-align: right;" onfocusout="verifyValue(this,this.value,1,0,0)" value="'+data[i]["jornadatj"]+'"></input></td>' +
									'<td style="width: 5%; overflow: hidden;"><input class="form-control input-sm numbersOnlyFloat2 focused" style="text-align: right;" onfocusout="verifyValue(this,this.value,50000,0,0)" value="'+data[i]["rendtj"]+'"></input></td>' +
									'<td style="width: 5%; overflow: hidden;"><input class="form-control input-sm numbersOnlyFloat2 focused" style="text-align: right;" onfocusout="verifyValue(this,this.value,'+maxExtraHours+',0,1)" value="'+data[i]["hhtj"]+'"></input></td>' +
									
								'</tr>';

						$("#tableTallyBody").append(row);
						$("#listPersonal"+rowNumber).val(data[i]["rut_per"]);
						$("#listPersonal"+rowNumber).selectpicker();
						$("#listTipoTra"+rowNumber).val(data[i]["det_trato"]);
						$("#listTipoTra"+rowNumber).selectpicker();
						$("#listLabour"+rowNumber).val(data[i]["idTar"]);
						//alert(data[i]["idLab"]);
						//console.log($("#listLabour"+rowNumber).val(data[i]["idLab"]));
						$("#listLabour"+rowNumber).selectpicker();
						
						var modalProgressHide = false;

						if(i+1==data.length){
							modalProgressHide = true;
							$("#modalProgress").modal('hide')
						}
						
						//Aplicación de color en filas al enfocar elementos (listas, inputs)
						$('button[data-id=listPersonal'+rowNumber+']').focus(function(event){
							$($(".focused").parent().parent().children()).css('background-color','transparent');
							$(".info").css('background-color','#D9EDF7');
							$(".warning").css('background-color','#FCF8E3');
							$($(this).parent().parent().parent().children()).css('background-color','#ccff9a');
						});
						$('button[data-id=listLabour'+rowNumber+']').focus(function(event){
							$($(".focused").parent().parent().children()).css('background-color','transparent');
							$(".info").css('background-color','#D9EDF7');
							$(".warning").css('background-color','#FCF8E3');
							$($(this).parent().parent().parent().children()).css('background-color','#ccff9a');
						});
						if(i+1==data.length){
							$(".focused").focus(function(event){
								$($(".focused").parent().parent().children()).css('background-color','transparent');
								$(".info").css('background-color','#D9EDF7');
								$(".warning").css('background-color','#FCF8E3');
								$($(this).parent().parent().children()).css('background-color','#CCFF9A');
							});
							//startParameters();
							calculateTotal();
						}
					}
				}
			});
		}

		function closeTally(tally_date,plant_id,type){
			$("#lblTallyCloseDate").text(tally_date);
			$("#lblTallyClosePlantID").text(plant_id);
			if(type=='close'){
				$("#lblTallyCloseText").text('¿Desea cerrar esta Tarja?');
			}else{
				$("#lblTallyCloseText").text('¿Desea reabrir esta Tarja?');
			}
			$("#modalTallyClose").modal('show');
		}


		function deleteTally(tally_date,plant_id){
			$("#lblTallyDeleteDate").text(tally_date);
			$("#lblTallyDeletePlantID").text(plant_id);
			$("#modalTallyDelete").modal('show');
		}

		function loadPlant(){
			$.post('../../phps/plant_Load.php', {type: "allb"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					if(data[i]["Pl_codigo"]<10){
						data[i]["Pl_codigo"] = '0'+data[i]["Pl_codigo"];
					}
					$("#listPlant").append('<option value="'+data[i]["Pl_codigo"]+'">'+data[i]["PlNombre"]+'</option>');
					if(i+1==data.length){
						loadLabour()
					}
				}
				// if(userProfile=='ADM'){
				// 	$("#listPlant").val('09');
				// 	//$("#listPlant").val(1);
				// }
			});
		}

		function loadYear(){
			$.post('../../phps/remuneration_Load.php', {type: "year"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					$("#listYear").append('<option value="'+data[i]["aaaarem"]+'">'+data[i]["aaaarem"]+'</option>');

					if(i+1==data.length){
						$.post('../../phps/parameters_Load.php', {type: "all"}, function(data, textStatus, xhr) {
							var data = JSON.parse(data);
							$("#listMonth").val(data[0]["Mes"]);
							$("#listYear").val(data[0]["ANO"]);
							if(userProfile=='ADM'){
								$("#listMonth").removeAttr('disabled');
								$("#listYear").removeAttr('disabled');
								$("#btnDeleteTally").css('display','inline-block');
							}
						});
						
					}	
				}
			});
		}

		function loadPeriodoBuk(){
			$.post('../../phps/parameters_Load.php', {type: "periodo"}, function(data, textStatus, xhr) {

				console.log(data)
				var data = JSON.parse(data);
				$("#listfechaBuk").val(data[0]["mes"]);						
			});
		}

		function loadWorkedBuk(){
			$.post('../../phps/talliesB_Load.php', {type: "newWorked"}, function(data, textStatus, xhr) {

				console.log(data)
				var data = JSON.parse(data);
				$("#listfechaBuk").val(data[0]["mes"]);						
			});
		}

		function loadPersonal(type){
			$.post('../../phps/personalb_Load.php', {
				type: "allTally",
				plant: $("#listPlant").val(),
				year: $("#listYear").val(),
		       	month: $("#listMonth").val()
			}, function(data, textStatus, xhr) {
				console.log(data)
				listPersonal = '<option value="0">SELECCIONE TRABAJADOR</option>';
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					console.log(i,data[i])
					var space = '';
					if(data[i]["rut"].length<12){
						space = '&nbsp';
					}
					listPersonal += '<option value="'+data[i]["rut_per"]+'" data-value-name="'+data[i]["fullname"]+'">'+space+data[i]["rut"]+' '+data[i]["fullname"]+'</option>';
				}
			});
		}

		function loadLabour(){
			console.log($("#listPlant").val());
			$.post('../../phps/labourBUK_Load.php', {
				type: "labourBUK",
				plant: $("#listPlant").val()
			}, function(data, textStatus, xhr) {
				console.log(data);
				//console.log($("#listPlant").val());
				listLabour = '<option value="-1">SEL. LABOR</option>';
				listLabour += '<option value="0" data-producto="No aplica" data-unidad="No aplica" data-tarifa="0">INASISTENCIA</option>';
				listLabour += '<option value="1" data-producto="No Aplica" data-unidad="No aplica" data-tarifa="0">LICENCIA</option>';
				listLabour += '<option value="2" data-producto="No Aplica" data-unidad="No aplica" data-tarifa="0">VACACIONES</option>';
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					listLabour += '<option value="'+data[i]["id_tarifa_buk"]+'" data-producto="'+data[i]["nomPro"]+'" data-unidad="'+data[i]["nomUni"]+'" data-tarifa="'+data[i]["tarif"]+'">'+data[i]["descriptionL"]+' - '+data[i]["nomLug"]+' - '+data[i]["tarif"]+'</option>';
				}
			});
		}

		function loadTipTra(){
			// $.post('../../phps/labourBUK_Load.php', {
			// 	type: "labourBUK"
			// }, function(data, textStatus, xhr) {
			// 	console.log(data)
			listTipoTra = '<option value="-1">SEL. JORNADA</option>';
			listTipoTra += '<option value="ausencia">AUSENCIA</option>';
			listTipoTra += '<option value="dia">DIA     </option>';
			listTipoTra += '<option value="trato">TRATO   </option>';
			//listTipoTra += '<option value="bono">BONO    </option>';
			// 	var data = JSON.parse(data);
			// 	for(i=0;i<data.length;i++){
			// 		listLabour += '<option value="'+data[i]["id_tarifa_buk"]+'" data-producto="'+data[i]["nomPro"]+'" data-unidad="'+data[i]["nomUni"]+'">'+data[i]["descriptionL"]+' - '+data[i]["nomLug"]+'</option>';
			// 	}
			// });
		}

		function verifyValue(input,value,maxvalue,minvalue,type){
			if(type==1 && userProfile=='ADM'){
				maxvalue = maxExtraHours;
			}
			if(toNoSeparatorFloat(value)>maxvalue){
				$(input).val(toSeparator(maxvalue));
			}
			if(toNoSeparatorFloat(value)<minvalue){
				$(input).val(toSeparator(minvalue));
			}
			calculateTotal();
		}

		function calculateTotal(){
			var val1 = 0, val2 = 0, val3 = 0;
			$('#tableTallyBody > tr').each(function() {

				val1 += toNoSeparatorFloat($($($(this).children()[8]).children()[0]).val());
				val2 += toNoSeparatorFloat($($($(this).children()[9]).children()[0]).val());
				val3 += toNoSeparatorFloat($($($(this).children()[10]).children()[0]).val());
				$("#lblTotal1").text("Asistencias: "+val1);
				$("#lblTotal2").text("Rendimiento: "+val2);
				$("#lblTotal3").text("Horas Extra: "+val3);
			});
		}

		function cleanModalTally(){
			$("#modalTally").modal('hide');

			$("#lblTallyPlant").text('');
			$("#lblTallyDate").text('');
			$("#lblTallyDateDay").text('');
			$("#tableTallyBody").html('');
			$("#txtWorkingDay").val(0);
			$("#txtPerformance").val(0);
			$("#txtExtraHours").val(0);
			if(userProfile!='ADM'){
				$("#btnDeleteTally").css('display','none');
			}

			$("#lblTotal1").text("Asistencias: ");
			$("#lblTotal2").text("Rendimiento: ");
			$("#lblTotal3").text("Horas Extra: ");
		}

		function toExcel(plant_id,plant,tally_date){

$("#modalProgress").modal('show');
$("#tableDataTallyExcel").html('<tr>' +
			'<th colspan="2">CAMPO:</th>' +
			'<th>'+plant+'</th>' +
			'<th colspan="2">Fecha</th>' +
			'<th colspan="2">'+tally_date+'</th>' +
		'</tr>' +
		'<tr>' +
			'<th></th>' +
		'</tr>' +
		'<tr>' +
			'<th>Código</th>' +
			'<th>RUT</th>' +
			'<th>Trabajador</th>' +
			'<th>Lugar</th>' +
			'<th>Labor</th>' +
			'<th>Valor $</th>' +
			'<th>Jornada</th>' +
			'<th>Rendimiento</th>' +
			'<th>Horas Extras</th>' +
		'</tr>');

$.post('../../phps/talliesb_Load.php', {
	type: 'oneExcel',
	date: tally_date,
	plant: plant_id
}, function(data, textStatus, xhr) {
	var data = JSON.parse(data);
	for(i=0;i<data.length;i++){
		
		list = '<tr>' +
				'<td>'+data[i]['codtj']+'</td>' +
				'<td>'+data[i]['rut_per']+'</td>' +
				'<td>'+data[i]['nomtrabtj']+'</td>' +
				'<td>'+data[i]['CC2Descrip']+'</td>' +
				'<td>'+data[i]['CC3Descrip']+'</td>' +
				'<td>'+data[i]['T11Val']+'</td>' +
				'<td>'+data[i]['jornadatj']+'</td>' +
				'<td>'+data[i]['rendtj']+'</td>' +
				'<td>'+data[i]['hhtj']+'</td>' +
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
				<div class="panel-heading"><i class="fa fa-address-book-o fa-lg"></i>&nbsp;&nbsp; Proceso de Tarjas</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<label style="font-size: 12px;">Campo:</label>
				    	    <select id="listPlant" class="form-control input-sm">
							</select>
						</div>

						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<label style="font-size: 12px;">Mes:</label>
				    	    <select id="listMonth" class="form-control input-sm">
				    	    	<option value="1">Enero</option>
				    	    	<option value="2">Febrero</option>
				    	    	<option value="3">Marzo</option>
				    	    	<option value="4">Abril</option>
				    	    	<option value="5">Mayo</option>
				    	    	<option value="6">Junio</option>
				    	    	<option value="7">Julio</option>
				    	    	<option value="8">Agosto</option>
				    	    	<option value="9">Septiembre</option>
				    	    	<option value="10">Octubre</option>
				    	    	<option value="11">Noviembre</option>
				    	    	<option value="12">Diciembre</option>
							</select>
						</div>

						<div class="col-xs-12 col-sm-12 col-md-1 col-lg-1">
							<label style="font-size: 12px;">Año:</label>
				    	    <select id="listYear" class="form-control input-sm">
							</select>
						</div>

						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<label style="font-size: 12px;">Filtrar por Fecha:</label>
							<input id="chkDate" type="checkbox"/>
				    	   	<input id="txtDate" type="text" class="form-control datepickerTxt input-sm">
						</div>

						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<label style="font-size: 12px;">Estado:</label>
				    	  	<select id="listState" class="form-control input-sm">
				    	  		<option value="0">Todos</option>
				    	    	<option value="A">En Digitación</option>
				    	    	<option value="C">Cerrada</option>
							</select>
						</div>

						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<br/>
							<button id="btnRefresh" class="btn btn-primary"><i class="fa fa-refresh fa-lg fa-fw"></i>&nbsp;&nbsp;Recargar</button>
							<br/>
							<br/>
							<button id="btnRefreshBuk" class="btn btn-primary"><i class="fa fa-refresh fa-lg fa-fw"></i>&nbsp;&nbsp;Recargar Buk</button>
							<br/>
						</div>

						<div class="col-xs-12 col-sm-12 col-md-1 col-lg-1">
							<br/>
							<br/>
							<br/>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<br/>
							<button id="btnAddTally" class="btn btn-success"><i class="fa fa-plus fa-lg fa-fw"></i>&nbsp;&nbsp;Nueva Tarja</button>
							<br/>
							<br/>
						</div>
						
					</div>	
					<br/>
					<br/>
					<div class="table-responsive">
						<table id="tableData" class="table table-hover" style="font-size: 12px;">
						</table>
						<table id="tablaTallyExcel" style="display: none;">
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>


	<div id="modal" class="modal fade" data-backdrop="static" style="z-index: 1051">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
	        	<div class="modal-body">
		    	    <p id="modal-text"></p>
		      	</div>
		      	<div class="modal-footer">
		        	<button id="modalHide" type="button" class="btn btn-primary">Aceptar</button>
		      	</div>
		    </div>
		</div>
	</div>

	<div id="modalList" class="modal fade" data-backdrop="static" style="z-index: 1051">
		<div class="modal-dialog modal-md">
			<div class="modal-content">
	        	<div class="modal-body">
		    	    <p id="modalList-text"></p>
		      	</div>
		      	<div class="modal-footer">
		        	<button id="modalListHide" type="button" class="btn btn-primary">Aceptar</button>
		      	</div>
		    </div>
		</div>
	</div>

	<div id="modalTallyNew" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-md">
			<div class="modal-content">
	    	    <div class="modal-body">
	        		<div class="panel panel-primary">
						<div class="panel-body">
							<div class="row">
								<div class="col-xs-0 col-sm-0 col-md-2 col-lg-2">
								</div>
								<div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
									Se creará una nueva Tarja con los siguientes datos:
									<br/>
									<br/>
									Campo: <label id="lblTallyNewPlant"></label> 
									<br/>
									Fecha: <label id="lblTallyNewDate"></label>&nbsp;-&nbsp;<label id="lblTallyNewDateDay"></label>
									<label id="lblTallyNewPlantID" style="display: none;"></label>
									<br/>
									¿Desea copiar la información de la última tarja?
									<!--<br/>
									(Se omitirán los trabajadores finiquitados)-->
								</div>
							</div>
						</div>
					</div>
		      	</div>
		      	<div class="modal-footer">
		        	<button id="btnTallyNewYes" type="button" class="btn btn-success"><i class="fa fa-check fa-lg"></i>&nbsp;Sí</button>
		        	<button id="btnTallyNewNo" type="button" class="btn btn-primary"><i class="fa fa-remove fa-lg"></i>&nbsp;No</button>
		        	<button id="btnTallyNewCancel" type="button" class="btn btn-danger"><i class="fa fa-remove fa-lg"></i>&nbsp;Cancelar</button>
		      	</div>
		    </div>
		</div>
	</div>

	<div id="modalTally" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-xxl" style="width: 100% !important; height: 100% !important; margin: 0; padding: 0;">
			<div class="modal-content" style="height: auto; min-height: 100%; border-radius: 0;">
	    	    <div class="modal-body">
	        		<div class="panel panel-primary">
						<div class="panel-heading"><i class="fa fa-address-book-o fa-lg"></i>&nbsp;&nbsp; TARJA:&nbsp; 
							<label id="lblTallyPlant"></label>
							<label id="lblTallyDate"></label>
							<label id="lblTallyDateDay"></label>
							<label id="lblTallyPlantID" style="display: none;"></label>
						</div>
						<div class="panel-body">
							<div class="row">
								<div class="col-xs-12 col-sm-12 col-md-1 col-lg-1">
									<button id="btnSelectAll" class="btn btn-primary btn-sm"><i class="fa fa-check-square-o fa-lg fa-fw"></i>&nbsp;Sel. Todo</button>
								</div>
								<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
									<button id="btnAddPersonal" class="btn btn-success btn-sm"><i class="fa fa-plus fa-lg fa-fw"></i>&nbsp;&nbsp;Agregar Trabajador</button>
								</div>
								<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
									<button id="btnDeletePersonal" class="btn btn-danger btn-sm"><i class="fa fa-minus fa-lg fa-fw"></i>&nbsp;&nbsp;Quitar Trabajadores Seleccionados</button>
								</div>
								<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
									<button id="btnExcel" class="btn btn-success btn-sm">Exportar a Excel <img src="../../images/excel.ico"/></button>
								</div>
								<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
									<!--<table id="tableTally" class="table table-hover" style="font-size: 12px;">-->
									<table id="tableTallyHeader" class="table table-hover" style="font-size: 12px; width: 100%; table-layout: fixed;">
										<thead>	
											<tr>

												<th style="width: 2%;">Sel.</th>
												<th style="width: 2%;">N°</th>
												<th style="width: 20%;">Trabajador</th>
												<th style="width: 8%; text-align: center;">Jornada</th>
												<th style="width: 25%; text-align: center;">Labor  /  Lugar</th>
												<th style="width: 8%; text-align: center;">Tarifa</th>
												<th style="width: 10%; text-align: center;">Produc.</th>
												<th style="width: 10%; text-align: center;">Unid.</th>
												<th style="width: 5%; text-align: center;">Asis.</th>
												<th style="width: 5%; text-align: center;">Rend.</th>
												<th style="width: 5%; text-align: center;">Hrs.</th>
											</tr>
										</thead>
									</table>
								</div>
								<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="overflow: auto; height: 60%;">
									<table id="tableTally" class="table table-hover" style="font-size: 12px; width: 100%; table-layout: fixed; ">
										<tbody id="tableTallyBody">
											
										</tbody>
									</table>
								</div>
								
								<div class="col-xs-12 col-sm-12 col-md-9 col-lg-9">
									&nbsp;
								</div>
								<div class="col-xs-12 col-sm-12 col-md-1 col-lg-1">
									<label>Totales</label>
								</div>
								<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
									<label id="lblTotal1">Asistencias:</label><br/>
									<label id="lblTotal2">Rendimiento:</label><br/>
									<label id="lblTotal3">Horas:</label>
								</div>

								<table id="tableDataTallyExcel" style="display: none;">
								</table>
							</div>
						</div>
					</div>
		      	</div>
		      	<div class="modal-footer">
		      		<button id="btnDeleteTally" class="btn btn-danger pull-left" style="display: none;"><i class="fa fa-remove fa-lg fa-fw"></i>&nbsp;&nbsp;Eliminar Tarja</button>
		        	<button id="btnTallySave" type="button" class="btn btn-success"><i class="fa fa-save fa-lg"></i>&nbsp;Almacenar</button>
		        	<button id="modalTallyHide" type="button" class="btn btn-primary"><i class="fa fa-remove fa-lg"></i>&nbsp;Salir</button>
		      	</div>
		    </div>
		</div>
	</div>


	<div id="modalTallyClose" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-md">
			<div class="modal-content">
	    	    <div class="modal-body">
	        		<div class="panel panel-primary">
						<div class="panel-body">
							<div class="row">
								<div class="col-xs-0 col-sm-0 col-md-2 col-lg-2">
								</div>
								<div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
									<label id="lblTallyCloseText" style="font-weight: normal;">¿Desea cerrar esta Tarja?</label>
									<label id="lblTallyClosePlantID" style="display: none;"></label>
									<label id="lblTallyCloseDate" style="display: none;"></label>
								</div>
							</div>
						</div>
					</div>
		      	</div>
		      	<div class="modal-footer">
		        	<button id="btnTallyCloseYes" type="button" class="btn btn-success"><i class="fa fa-check fa-lg"></i>&nbsp;Sí</button>
		        	<button id="btnTallyCloseNo" type="button" class="btn btn-primary"><i class="fa fa-remove fa-lg"></i>&nbsp;No</button>
		      	</div>
		    </div>
		</div>
	</div>

	<div id="modalTallyDelete" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-md">
			<div class="modal-content">
	    	    <div class="modal-body">
					<div class="row">
						<div class="col-xs-0 col-sm-0 col-md-2 col-lg-2">
						</div>
						<div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
							<label id="lblTallyDeleteText" style="font-weight: normal;">¿Desea eliminar esta Tarja?</label>
							<label style="font-weight: normal;">Fecha:</label>
							<label id="lblTallyDeleteDate"></label>
							<label id="lblTallyDeletePlantID" style="display: none;"></label>
						</div>
					</div>
		      	</div>
		      	<div class="modal-footer">
		        	<button id="btnTallyDeleteYes" type="button" class="btn btn-danger"><i class="fa fa-check fa-lg"></i>&nbsp;Sí</button>
		        	<button id="btnTallyDeleteNo" type="button" class="btn btn-primary"><i class="fa fa-remove fa-lg"></i>&nbsp;No</button>
		      	</div>
		    </div>
		</div>
	</div>
</body>
</html>