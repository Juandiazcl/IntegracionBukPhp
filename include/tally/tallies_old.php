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

	var listPersonal = '', listAnalysisUnit = '', listLabourCategoryArray = {}, listLabourDetailArray = {}, listDealCategoryArray = {}, listDealDetailArray = {};
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


					$.post('../../phps/tallies_Load.php', {
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
				loadDealCategoryMassive($("#lblTallyPlantID").text());

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

				loadDealCategoryMassive($("#lblTallyPlantID").text());
				$("#modalProgress").modal('show');
				$.post('../../phps/tallies_Save.php', {
					type: 'repeat',
	            	date: $("#lblTallyDate").text(),
	            	plant: $("#lblTallyPlantID").text()
	            }, function(data, textStatus, xhr) {
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
				
				$.post('../../phps/tallies_Save.php', {
					type: 'close',
	            	date: tally_date,
	            	plant: plant_id,
	            	state: state
	            }, function(data, textStatus, xhr) {
	            	$("#modalTallyClose").modal('hide');
	            	if(data=='OK'){
	            		loadData();
	            	}else{
						$("#modal-text").text("Error al almacenar, contacte al administrador");
						$("#modal").modal('show');
	            	}
	            });
			});

			$("#btnTallyCloseNo").click(function() {
				$("#lblTallyCloseDate").text('');
				$("#lblTallyClosePlantID").text('');
				$("#modalTallyClose").modal('hide');
			});

			$("#btnDeleteTally").click(function() {
				deleteTally($("#lblTallyDate").text(),$("#lblTallyPlantID").text());
			});

			$("#btnTallyDeleteYes").click(function() {
				var tally_date = $("#lblTallyDeleteDate").text();
				var plant_id = $("#lblTallyDeletePlantID").text();
				
				$.post('../../phps/tallies_Save.php', {
					type: 'delete',
	            	date: tally_date,
	            	plant: plant_id
	            }, function(data, textStatus, xhr) {
	            	$("#modalTallyDelete").modal('hide');
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

				var row = '<tr>' +
							'<td style="width: 2%; overflow: hidden;"><input type="checkbox" class="focused"></input></td>' +
							'<td style="width: 2%; overflow: hidden;">'+rowNumber+'</td>' +
							'<td style="width: 32%; overflow: hidden;"><select id="listPersonal'+rowNumber+'" class="focused" data-live-search="true" data-width="fit" data-size="5" data-style="btn btn-default btn-xs" data-live-search-style="btn btn-default btn-xs" data-container="body">'+listPersonal+'</select></td>' +
							'<td style="width: 10%; overflow: hidden;"><select id="listAnalysisUnit'+rowNumber+'" class="focused" onchange="loadLabourCategory('+rowNumber+',this.value,0)" data-live-search="true" data-width="fit" data-size="5" data-style="btn btn-default btn-xs" data-container="body">'+listAnalysisUnit+'</select></td>' +
							'<td style="width: 10%; overflow: hidden;" class="info"><select id="listLabourCategory'+rowNumber+'" class="focused" onchange="loadLabourDetail('+rowNumber+',this.value)" data-live-search="true" data-width="fit" data-size="5" data-style="btn btn-default btn-xs" data-container="body"></select></td>' +
							'<td style="width: 10%; overflow: hidden;" class="info"><select id="listLabourDetail'+rowNumber+'" class="focused" data-live-search="true" data-width="fit" data-size="5" data-style="btn btn-default btn-xs" data-container="body"></select></td>' +
							'<td style="width: 10%; overflow: hidden;" class="warning"><select id="listDealCategory'+rowNumber+'" class="focused" onchange="loadDealDetail('+rowNumber+',this.value)" data-live-search="true" data-width="fit" data-size="5" data-style="btn btn-default btn-xs" data-container="body"></select></td>' +
							'<td style="width: 10%; overflow: hidden;" class="warning"><select id="listDealDetail'+rowNumber+'" class="focused" data-live-search="true" data-width="fit" data-size="5" data-style="btn btn-default btn-xs" data-container="body"></select></td>' +
							'<td style="width: 4%; overflow: hidden;"><input class="form-control input-sm numbersOnlyFloat2 focused" style="text-align: right;" onfocusout="verifyValue(this,this.value,1,0,0)"></input></td>' +
							'<td style="width: 4%; overflow: hidden;"><input class="form-control input-sm numbersOnlyFloat2 focused" style="text-align: right;" onfocusout="verifyValue(this,this.value,50000,0,0)"></input></td>' +
							'<td style="width: 4%; overflow: hidden;"><input class="form-control input-sm numbersOnlyFloat2 focused" style="text-align: right;" onfocusout="verifyValue(this,this.value,'+maxExtraHours+',0,1)"></input></td>' +
						'</tr>';
				$("#tableTallyBody").append(row);
				$("#listPersonal"+rowNumber).selectpicker();
				$("#listAnalysisUnit"+rowNumber).selectpicker();
				$("#listLabourCategory"+rowNumber).selectpicker();
				$("#listLabourDetail"+rowNumber).selectpicker();
				var modalProgressHide = false;
				loadDealCategory(rowNumber,$("#lblTallyPlantID").text(),0,0,modalProgressHide);
				startParameters();

				$('button[data-id=listPersonal'+rowNumber+']').focus(function(event){
					$($(".focused").parent().parent().children()).css('background-color','transparent');
					$(".info").css('background-color','#D9EDF7');
					$(".warning").css('background-color','#FCF8E3');
					$($(this).parent().parent().parent().children()).css('background-color','#ccff9a');
				});
				$('button[data-id=listAnalysisUnit'+rowNumber+']').focus(function(event){
					$($(".focused").parent().parent().children()).css('background-color','transparent');
					$(".info").css('background-color','#D9EDF7');
					$(".warning").css('background-color','#FCF8E3');
					$($(this).parent().parent().parent().children()).css('background-color','#ccff9a');
				});
				$('button[data-id=listLabourCategory'+rowNumber+']').focus(function(event){
					$($(".focused").parent().parent().children()).css('background-color','transparent');
					$(".info").css('background-color','#D9EDF7');
					$(".warning").css('background-color','#FCF8E3');
					$($(this).parent().parent().parent().children()).css('background-color','#ccff9a');
				});
				$('button[data-id=listLabourDetail'+rowNumber+']').focus(function(event){
					$($(".focused").parent().parent().children()).css('background-color','transparent');
					$(".info").css('background-color','#D9EDF7');
					$(".warning").css('background-color','#FCF8E3');
					$($(this).parent().parent().parent().children()).css('background-color','#ccff9a');
				});
				$('button[data-id=listDealCategory'+rowNumber+']').focus(function(event){
					$($(".focused").parent().parent().children()).css('background-color','transparent');
					$(".info").css('background-color','#D9EDF7');
					$(".warning").css('background-color','#FCF8E3');
					$($(this).parent().parent().parent().children()).css('background-color','#ccff9a');
				});
				$('button[data-id=listDealDetail'+rowNumber+']').focus(function(event){
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

								$("#listAnalysisUnit"+oldRowNumber).selectpicker('destroy');
								$("#listAnalysisUnit"+oldRowNumber).prop('id','listAnalysisUnit'+rowNumber);
								$("#listAnalysisUnit"+rowNumber).attr('onchange','loadLabourCategory('+rowNumber+',this.value,0)');
								$("#listAnalysisUnit"+rowNumber).selectpicker();

								$("#listLabourCategory"+oldRowNumber).selectpicker('destroy');
								$("#listLabourCategory"+oldRowNumber).prop('id','listLabourCategory'+rowNumber);
								$("#listLabourCategory"+rowNumber).attr('onchange','loadLabourDetail('+rowNumber+',this.value,0)');
								$("#listLabourCategory"+rowNumber).selectpicker();

								$("#listLabourDetail"+oldRowNumber).selectpicker('destroy');
								$("#listLabourDetail"+oldRowNumber).prop('id','listLabourDetail'+rowNumber);
								$("#listLabourDetail"+rowNumber).selectpicker();								

								$("#listDealCategory"+oldRowNumber).selectpicker('destroy');
								$("#listDealCategory"+oldRowNumber).prop('id','listDealCategory'+rowNumber);
								$("#listDealCategory"+rowNumber).attr('onchange','loadDealDetail('+rowNumber+',this.value,0)');
								$("#listDealCategory"+rowNumber).selectpicker();

								$("#listDealDetail"+oldRowNumber).selectpicker('destroy');
								$("#listDealDetail"+oldRowNumber).prop('id','listDealDetail'+rowNumber);
								$("#listDealDetail"+rowNumber).selectpicker();

								$($(this).children()[1]).text(rowNumber);


							}

							rowNumber++;						
						});
					}
				});
			});

			$("#btnTallySave").click(function() {

				$("#modalProgress").modal('show');
				var count = 0;
				var total = $('#tableTallyBody > tr').length;
				var list = '';
				if(total>0){
					returnNoSeparator();
					var arrayList = {};
					var messageListPersonal = '';
					$('#tableTallyBody > tr').each(function() {
						var rowNumber = $($(this).children()[1]).text();
						if(count>0){
							list += '&&&&';
						}

						list += $($(this).children()[1]).text() + '&&' +
								$("#listPersonal"+rowNumber).val() + '&&' +
								$("#listPersonal"+rowNumber+" option:selected").attr('data-value-name') + '&&' +
								$("#listAnalysisUnit"+rowNumber).val() + '&&' +
								$("#listLabourCategory"+rowNumber).val() + '&&' +
								$("#listLabourDetail"+rowNumber).val() + '&&' +
								$("#listDealCategory"+rowNumber).val() + '&&' +
								$("#listDealDetail"+rowNumber).val() + '&&' +
								$($($(this).children()[8]).children()[0]).val() + '&&' +
								$($($(this).children()[9]).children()[0]).val() + '&&' +
								$($($(this).children()[10]).children()[0]).val();

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
    						if(messageList!=''){
								$("#modalProgress").modal('hide');
    							$("#modalList-text").html("Hay trabajadores con jornadas erróneas o incompletas:" + messageList);
								$("#modalList").modal('show');
								return;
							}

							$.post('../../phps/tallies_Save.php', {
								type: 'save', 
				            	plant: $("#lblTallyPlantID").text(),
								date: $("#lblTallyDate").text(),
				            	total: total,
				            	list: list
							}, function(data, textStatus, xhr) {
								console.log(data);
								$("#modalProgress").modal('hide');
								if(data=='OK'){
									$("#modal-text").text("Registros almacenados correctamente");
									$("#modal").modal('show');
								}else{
									$("#modal-text").text("Ha ocurrido un error, favor reintente (si el problema persiste, contacte al administrador)");
									$("#modal").modal('show');
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


			$("#btnAddMassive").click(function() {
				if($("#divMassive").css('display')=='none'){
					$("#divMassive").css('display','block');
				}else{
					$("#divMassive").css('display','none');
				}
			});

			$("#btnAddMassiveApply").click(function() {
				if($("#listAnalysisUnitMassive").val()!=-1 && $("#listLabourCategoryMassive").val()!=-1 && $("#listLabourDetailMassive").val()!=-1 && $("#listDealCategoryMassive").val()!=-1 && $("#listDealDetailMassive").val()!=-1 && $("#listAnalysisUnitMassive").val()!=null && $("#listLabourCategoryMassive").val()!=null && $("#listLabourDetailMassive").val()!=null && $("#listDealCategoryMassive").val()!=null && $("#listDealDetailMassive").val()!=null){
					$('#tableTallyBody > tr').each(function() {
						if($(this).find('td').find(">:first-child").is(':checked')){
							var rowNumber = $($(this).children()[1]).text();
							$("#listAnalysisUnit"+rowNumber).selectpicker('destroy');
							$("#listAnalysisUnit"+rowNumber).val($("#listAnalysisUnitMassive").val());
							$("#listAnalysisUnit"+rowNumber).selectpicker();

							loadLabourCategory(rowNumber,$("#listAnalysisUnitMassive").val(),$("#listLabourCategoryMassive").val(),$("#listLabourDetailMassive").val(),false);

							$("#listDealCategory"+rowNumber).selectpicker('destroy');
							$("#listDealCategory"+rowNumber).val($("#listDealCategoryMassive").val());
							$("#listDealCategory"+rowNumber).selectpicker();

							loadDealDetail(rowNumber,$("#listDealCategoryMassive").val(),$("#listDealDetailMassive").val());
							
							$($($(this).children()[8]).children()[0]).val($("#txtWorkingDay").val());
							$($($(this).children()[9]).children()[0]).val($("#txtPerformance").val());
							$($($(this).children()[10]).children()[0]).val($("#txtExtraHours").val());
						}
					});
				}else{
					$("#modal-text").text("Una o más listas no están seleccionadas");
					$("#modal").modal('show');
				}
			});

			loadPlant();
			loadYear();

			loadAnalysisUnit();
			loadLabourCategoryAll();
			loadLabourDetailAll();
			loadDealCategoryAll();
			loadDealDetailAll();
		});

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
		            "url": "../../phps/tallies_Load.php",
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
				$("#btnAddMassive").css('display','inline-block');
				$("#btnDeletePersonal").css('display','inline-block');
				
				$("#btnDeleteTally").css('display','inline-block');
			}else{
				$("#btnTallySave").css('display','none');
				$("#tableTally").css('pointer-events','none');
				$("#btnSelectAll").css('display','none');
				$("#btnAddPersonal").css('display','none');
				$("#btnAddMassive").css('display','none');
				$("#btnDeletePersonal").css('display','none');
			}

			$("#modalTallyNew").modal('hide');
			$("#lblTallyPlant").text(plant);
			$("#lblTallyDate").text(tally_date);
			$("#lblTallyDateDay").text(day);
			$("#lblTallyPlantID").text(plant_id);
			loadDealCategoryMassive(plant_id);
			$("#modalTally").modal('show');


			$("#modalProgress").modal('show');


			$.post('../../phps/tallies_Load.php', {
				type: 'one',
            	date: tally_date,
            	plant: plant_id}, function(data, textStatus, xhr) {
            		console.log(data);
				if(data!=0){
					var data = JSON.parse(data);
					for(i=0;i<data.length;i++){
		        		var rowNumber = data[i]["codtj"];

						var row = '<tr>' +
									'<td style="width: 2%; overflow: hidden;"><input type="checkbox" class="focused"></input></td>' +
									'<td style="width: 2%; overflow: hidden;">'+rowNumber+'</td>' +
									'<td style="width: 29%; overflow: hidden;"><select id="listPersonal'+rowNumber+'" class="focused" data-live-search="true" data-width="fit" data-size="5" data-style="btn btn-default btn-xs" data-live-search-style="btn btn-default btn-xs" data-container="body">'+listPersonal+'</select></td>' +
									'<td style="width: 10%; overflow: hidden;"><select id="listAnalysisUnit'+rowNumber+'" class="focused" onchange="loadLabourCategory('+rowNumber+',this.value,0)" data-live-search="true" data-width="fit" data-size="5" data-style="btn btn-default btn-xs" data-container="body">'+listAnalysisUnit+'</select></td>' +
									'<td style="width: 10%; overflow: hidden;" class="info"><select id="listLabourCategory'+rowNumber+'" class="focused" onchange="loadLabourDetail('+rowNumber+',this.value)" data-live-search="true" data-width="fit" data-size="5" data-style="btn btn-default btn-xs" data-container="body"></select></td>' +
									'<td style="width: 10%; overflow: hidden;" class="info"><select id="listLabourDetail'+rowNumber+'" class="focused" data-live-search="true" data-width="fit" data-size="5" data-style="btn btn-default btn-xs" data-container="body"></select></td>' +
									'<td style="width: 10%; overflow: hidden;" class="warning"><select id="listDealCategory'+rowNumber+'" class="focused" onchange="loadDealDetail('+rowNumber+',this.value)" data-live-search="true" data-width="fit" data-size="5" data-style="btn btn-default btn-xs" data-container="body"></select></td>' +
									'<td style="width: 10%; overflow: hidden;" class="warning"><select id="listDealDetail'+rowNumber+'" class="focused" data-live-search="true" data-width="fit" data-size="5" data-style="btn btn-default btn-xs" data-container="body"></select></td>' +
									'<td style="width: 5%; overflow: hidden;"><input class="form-control input-sm numbersOnlyFloat2 focused" style="text-align: right;" onfocusout="verifyValue(this,this.value,1,0,0)" value="'+data[i]["jornadatj"]+'"></input></td>' +
									'<td style="width: 5%; overflow: hidden;"><input class="form-control input-sm numbersOnlyFloat2 focused" style="text-align: right;" onfocusout="verifyValue(this,this.value,50000,0,0)" value="'+data[i]["rendtj"]+'"></input></td>' +
									'<td style="width: 5%; overflow: hidden;"><input class="form-control input-sm numbersOnlyFloat2 focused" style="text-align: right;" onfocusout="verifyValue(this,this.value,'+maxExtraHours+',0,1)" value="'+data[i]["hhtj"]+'"></input></td>' +
								'</tr>';

						$("#tableTallyBody").append(row);
						$("#listPersonal"+rowNumber).val(data[i]["rut_per"]);
						$("#listPersonal"+rowNumber).selectpicker();
						$("#listAnalysisUnit"+rowNumber).val(data[i]["cc2"]);
						$("#listAnalysisUnit"+rowNumber).selectpicker();
						var modalProgressHide = false;

						if(i+1==data.length){
							modalProgressHide = true;
						}
						loadLabourCategory(rowNumber,data[i]["cc2"],data[i]["cc3"],data[i]["cc4"],modalProgressHide);
						loadDealCategory(rowNumber,plant_id,data[i]["cattrt"],data[i]["codtrt"],modalProgressHide);

						//Aplicación de color en filas al enfocar elementos (listas, inputs)
						$('button[data-id=listPersonal'+rowNumber+']').focus(function(event){
							$($(".focused").parent().parent().children()).css('background-color','transparent');
							$(".info").css('background-color','#D9EDF7');
							$(".warning").css('background-color','#FCF8E3');
							$($(this).parent().parent().parent().children()).css('background-color','#ccff9a');
						});
						$('button[data-id=listAnalysisUnit'+rowNumber+']').focus(function(event){
							$($(".focused").parent().parent().children()).css('background-color','transparent');
							$(".info").css('background-color','#D9EDF7');
							$(".warning").css('background-color','#FCF8E3');
							$($(this).parent().parent().parent().children()).css('background-color','#ccff9a');
						});
						$('button[data-id=listLabourCategory'+rowNumber+']').focus(function(event){
							$($(".focused").parent().parent().children()).css('background-color','transparent');
							$(".info").css('background-color','#D9EDF7');
							$(".warning").css('background-color','#FCF8E3');
							$($(this).parent().parent().parent().children()).css('background-color','#ccff9a');
						});
						$('button[data-id=listLabourDetail'+rowNumber+']').focus(function(event){
							$($(".focused").parent().parent().children()).css('background-color','transparent');
							$(".info").css('background-color','#D9EDF7');
							$(".warning").css('background-color','#FCF8E3');
							$($(this).parent().parent().parent().children()).css('background-color','#ccff9a');
						});
						$('button[data-id=listDealCategory'+rowNumber+']').focus(function(event){
							$($(".focused").parent().parent().children()).css('background-color','transparent');
							$(".info").css('background-color','#D9EDF7');
							$(".warning").css('background-color','#FCF8E3');
							$($(this).parent().parent().parent().children()).css('background-color','#ccff9a');
						});
						$('button[data-id=listDealDetail'+rowNumber+']').focus(function(event){
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
							startParameters();
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
						'<th>CC2</th>' +
						'<th>Unidad Análisis</th>' +
						'<th>CC3</th>' +
						'<th>Categoría Labor</th>' +
						'<th>CC4</th>' +
						'<th>Detalle Labor</th>' +
						'<th>Código Categoría Trato</th>' +
						'<th>Descripción Categoría Trato</th>' +
						'<th>Código Trato</th>' +
						'<th>Descripción Trato</th>' +
						'<th>Valor $</th>' +
						'<th>Jornada</th>' +
						'<th>Rendimiento</th>' +
						'<th>Horas Extras</th>' +
					'</tr>');

			$.post('../../phps/tallies_Load.php', {
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
							'<td>'+data[i]['cc2tj']+'</td>' +
							'<td>'+data[i]['CC2Descrip']+'</td>' +
							'<td>'+data[i]['cc3tj']+'</td>' +
							'<td>'+data[i]['CC3Descrip']+'</td>' +
							'<td>'+data[i]['cc4tj']+'</td>' +
							'<td>'+data[i]['CC4Descrip']+'</td>' +
							'<td>'+data[i]['cattrt']+'</td>' +
							'<td>'+data[i]['T1Descrip']+'</td>' +
							'<td>'+data[i]['codtrt']+'</td>' +
							'<td>'+data[i]['T11Descrip']+'</td>' +
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


		function loadPlant(){
			$.post('../../phps/plant_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					if(data[i]["Pl_codigo"]<10){
						data[i]["Pl_codigo"] = '0'+data[i]["Pl_codigo"];
					}
					$("#listPlant").append('<option value="'+data[i]["Pl_codigo"]+'">'+data[i]["PlNombre"]+'</option>');
				}
				if(userProfile=='ADM'){
					$("#listPlant").val('09');
				}
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

		function loadAnalysisUnit(){
			$.post('../../phps/costCenterGeneral_Load.php', {
				type: "allCC2"
			}, function(data, textStatus, xhr) {
				listAnalysisUnit = '<option value="-1">SEL. UNIDAD</option>';
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					listAnalysisUnit += '<option value="'+data[i]["cc2"]+'">'+data[i]["cc2"]+' '+data[i]["UnidAnalisis"]+'</option>';
					if(i+1==data.length){
						$("#listAnalysisUnitMassive").html(listAnalysisUnit);
						$("#listAnalysisUnitMassive").selectpicker();
						$("#listLabourCategoryMassive").html('<option value="-1">SEL. CATEGORÍA</option>');
						$("#listLabourCategoryMassive").selectpicker();
						$("#listLabourDetailMassive").html('<option value="-1">SEL. DETALLE</option>');
						$("#listLabourDetailMassive").selectpicker();
					}
				}
			});
		}

		function loadLabourCategoryAll(rowNumber,cc2,selected,selected2,modalProgressHide){
			listLabourCategoryArray = {};
			$.post('../../phps/costCenterGeneral_Load.php', {
				type: "allCC21Full"
			}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){

					if(data[i]['cc2'] in listLabourCategoryArray){
						listLabourCategoryArray[data[i]['cc2']][data[i]['cc3']] = data[i]['Labor'];
					
					}else{
						listLabourCategoryArray[data[i]['cc2']] = {};
						listLabourCategoryArray[data[i]['cc2']][data[i]['cc3']] = data[i]['Labor'];
					}
				}
			});
		}

		function loadLabourCategory(rowNumber,cc2,selected,selected2,modalProgressHide){
			$("#listLabourCategory"+rowNumber).selectpicker('destroy');
			$("#listLabourCategory"+rowNumber).html('<option value="-1">SEL. CATEGORÍA</option>');

			$("#listLabourDetail"+rowNumber).selectpicker('destroy');
			$("#listLabourDetail"+rowNumber).html('<option value="-1">SEL. DETALLE</option>');
			if(cc2!=-1){
				var default0Value = '';
				for (key in listLabourCategoryArray[cc2]){ //key = cc3
   					default0Value = key;
					$("#listLabourCategory"+rowNumber).append('<option value="'+key+'">'+key+' '+listLabourCategoryArray[cc2][key]+'</option>');
				}
				if(i==1){
					$("#listLabourCategory"+rowNumber).val(default0Value);
				}
				if(selected!=-1){
					$("#listLabourCategory"+rowNumber).val(selected);
				}
				$("#listLabourCategory"+rowNumber).selectpicker();
				loadLabourDetail(rowNumber,selected,selected2,modalProgressHide);

			}else{
				$("#listLabourCategory"+rowNumber).selectpicker();
				$("#listLabourDetail"+rowNumber).selectpicker();
			}
		}

		//function loadLabourDetailAll(rowNumber,cc2,selected,selected2,modalProgressHide){
		function loadLabourDetailAll(){
			listLabourDetailArray = {};
			$.post('../../phps/costCenterGeneral_Load.php', {
				type: "allCC22Full"
			}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){

					if(data[i]['cc2'] in listLabourDetailArray){
						if(data[i]['cc3'] in listLabourDetailArray[data[i]['cc2']]){
							listLabourDetailArray[data[i]['cc2']][data[i]['cc3']][data[i]['cc4']] = data[i]['Labor2'];
						}else{
							listLabourDetailArray[data[i]['cc2']][data[i]['cc3']] = {};
							listLabourDetailArray[data[i]['cc2']][data[i]['cc3']][data[i]['cc4']] = data[i]['Labor2'];
						}
					}else{
						listLabourDetailArray[data[i]['cc2']] = {};
						listLabourDetailArray[data[i]['cc2']][data[i]['cc3']] = {};
						listLabourDetailArray[data[i]['cc2']][data[i]['cc3']][data[i]['cc4']] = data[i]['Labor2'];
					}
				}
			});
		}

		function loadLabourDetail(rowNumber,cc3,selected,modalProgressHide){
			var cc2 = $("#listAnalysisUnit"+rowNumber).val();
			$("#listLabourDetail"+rowNumber).selectpicker('destroy');
			$("#listLabourDetail"+rowNumber).html('<option value="-1">SEL. DETALLE</option>');
			if(cc3!=-1){
				var default0Value = '';
				for (key in listLabourDetailArray[cc2][cc3]){ //key = cc4
   					default0Value = key;
					$("#listLabourDetail"+rowNumber).append('<option value="'+key+'">'+key+' '+listLabourDetailArray[cc2][cc3][key]+'</option>');
				}
				if(i==1){
					$("#listLabourDetail"+rowNumber).val(default0Value);
				}
				if(selected!=-1){
					$("#listLabourDetail"+rowNumber).val(selected);
				}
				$("#listLabourDetail"+rowNumber).selectpicker();
				if(modalProgressHide){
					$("#modalProgress").modal('hide');
				}

			}else{
				$("#listLabourDetail"+rowNumber).selectpicker();
				if(modalProgressHide){
					$("#modalProgress").modal('hide');
				}
			}
		}

		function loadDealCategoryAll(){
			listDealCategoryArray = {};
			$.post('../../phps/costCenterGeneral_Load.php', {
				type: "allDeal1Full"
			}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){

					if(data[i]['cc1trt'] in listDealCategoryArray){
						listDealCategoryArray[data[i]['cc1trt']][data[i]['cattrt']] = data[i]['descattrt'];
					
					}else{
						listDealCategoryArray[data[i]['cc1trt']] = {};
						listDealCategoryArray[data[i]['cc1trt']][data[i]['cattrt']] = data[i]['descattrt'];
					}
				}
			});
		}

		function loadDealCategory(rowNumber,cc1trt,selected,selected2,modalProgressHide){
			$("#listDealCategory"+rowNumber).selectpicker('destroy');
			$("#listDealCategory"+rowNumber).html('<option value="-1">SEL. CATEGORÍA</option>');

			$("#listDealDetail"+rowNumber).selectpicker('destroy');
			$("#listDealDetail"+rowNumber).html('<option value="-1">SEL. DETALLE</option>');
			var j = 0;
			if(cc1trt!=-1){
				var default0Value = '';
				for (key in listDealCategoryArray[cc1trt]){ //key = cc3
   					j++;
   					default0Value = key;
					$("#listDealCategory"+rowNumber).append('<option value="'+key+'">'+key+' '+listDealCategoryArray[cc1trt][key]+'</option>');
				}
				if(j==1){
					$("#listDealCategory"+rowNumber).val(default0Value);
					//$("#listDealCategory"+rowNumber).attr('disabled','disabled');
				}
				if(selected!=-1){
					$("#listDealCategory"+rowNumber).val(selected);
				}
				$("#listDealCategory"+rowNumber).selectpicker();
				loadDealDetail(rowNumber,selected,selected2,modalProgressHide);

			}else{
				$("#listDealCategory"+rowNumber).selectpicker();
				$("#listDealDetail"+rowNumber).selectpicker();
			}
		}


		function loadDealCategoryMassive(cc1trt){
			$("#listDealCategoryMassive").selectpicker('destroy');
			$("#listDealCategoryMassive").html('<option value="-1">SEL. TRATO</option>');
			
			var j = 0;
			if(cc1trt!=-1){
				var default0Value = '';
				for (key in listDealCategoryArray[cc1trt]){ //key = cc3
   					j++;
   					default0Value = key;
					$("#listDealCategoryMassive").append('<option value="'+key+'">'+key+' '+listDealCategoryArray[cc1trt][key]+'</option>');
				}
				if(j==1){
					$("#listDealCategoryMassive").val(default0Value);
					loadDealDetail('Massive',default0Value);
				}
				$("#listDealCategoryMassive").selectpicker();

			}else{
				$("#listDealCategoryMassive").selectpicker();
			}
		}


		function loadDealDetailAll(){
			listDealDetailArray = {};
			$.post('../../phps/costCenterGeneral_Load.php', {
				type: "allDeal11Full"
			}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){

					/*if(data[i]['cattrt'] in listDealDetailArray){
						listDealDetailArray[data[i]['cattrt']][data[i]['codtrt']] = data[i]['desctrt'];
					
					}else{
						listDealDetailArray[data[i]['cattrt']] = {};
						listDealDetailArray[data[i]['cattrt']][data[i]['codtrt']] = data[i]['desctrt'];
					}*/


					if(data[i]['cc1trt'] in listDealDetailArray){
						if(data[i]['cattrt'] in listDealDetailArray[data[i]['cc1trt']]){
							listDealDetailArray[data[i]['cc1trt']][data[i]['cattrt']][data[i]['codtrt']] = data[i]['desctrt'];
						}else{
							listDealDetailArray[data[i]['cc1trt']][data[i]['cattrt']] = {};
							listDealDetailArray[data[i]['cc1trt']][data[i]['cattrt']][data[i]['codtrt']] = data[i]['desctrt'];
						}
					}else{
						listDealDetailArray[data[i]['cc1trt']] = {};
						listDealDetailArray[data[i]['cc1trt']][data[i]['cattrt']] = {};
						listDealDetailArray[data[i]['cc1trt']][data[i]['cattrt']][data[i]['codtrt']] = data[i]['desctrt'];
					}
				}
			});
		}

		function loadDealDetail(rowNumber,cattrt,selected,modalProgressHide){

			var cc1trt = $("#lblTallyPlantID").text();
			$("#listDealDetail"+rowNumber).selectpicker('destroy');
			$("#listDealDetail"+rowNumber).html('<option value="-1">SEL. DETALLE</option>');
			if(cattrt!=-1){
				var i = 0, default0Value = '';
				for (key in listDealDetailArray[cc1trt][cattrt]){ //key = codtrt
   					i++;
   					default0Value = key;
					$("#listDealDetail"+rowNumber).append('<option value="'+key+'">'+key+' '+listDealDetailArray[cc1trt][cattrt][key]+'</option>');
				}
				if(i==1){
					$("#listDealDetail"+rowNumber).val(default0Value);
				}
				if(selected!=-1){
					$("#listDealDetail"+rowNumber).val(selected);
				}
				$("#listDealDetail"+rowNumber).selectpicker();
				if(modalProgressHide==true){
					$("#modalProgress").modal('hide');
				}
			}else{
				$("#listDealDetail"+rowNumber).selectpicker();
				$("#modalProgress").modal('hide');
			}
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
				$("#lblTotal1").text("Jornadas: "+val1);
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
			$("#listAnalysisUnitMassive").val(0);
			$("#txtWorkingDay").val(0);
			$("#txtPerformance").val(0);
			$("#txtExtraHours").val(0);
			if(userProfile!='ADM'){
				$("#btnDeleteTally").css('display','none');
			}
			
			$("#divMassive").css('display','none');

			$("#lblTotal1").text("Jornadas: ");
			$("#lblTotal2").text("Rendimiento: ");
			$("#lblTotal3").text("Horas Extra: ");
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
								<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
									<button id="btnAddMassive" class="btn btn-primary btn-sm"><i class="fa fa-users fa-lg fa-fw"></i>&nbsp;&nbsp;Agregar Datos Masivos</button>
								</div>
								<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
									<button id="btnExcel" class="btn btn-success btn-sm">Exportar a Excel <img src="../../images/excel.ico"/></button>
								</div>
								<div id="divMassive" class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="display: none;">
									<br/>
									<div class="panel panel-primary">
										<div class="panel-body">
											<table style="font-size: 12px; width: 100%; table-layout: fixed;">
												<tr>
													<th style="width: 16%;">
														<label style="font-size: 12px;">Unidad Análisis</label>
														<br/>
														<select id="listAnalysisUnitMassive" class="form-control input-sm" onchange="loadLabourCategory('Massive',this.value,0)" data-live-search="true" data-width="fit" data-size="5" data-style="btn btn-default btn-xs" data-container="body"></select>
														</select>
													</th>
													<th style="width: 16%;">
														<label style="font-size: 12px;">Labor Categoría</label>
														<br/>
														<select id="listLabourCategoryMassive" onchange="loadLabourDetail('Massive',this.value)" data-live-search="true" data-width="fit" data-size="5" data-style="btn btn-default btn-xs" data-container="body"></select>
														</select>
													</th>
													<th style="width: 16%;">
														<label style="font-size: 12px;">Labor Detalle</label>
														<br/>
														<select id="listLabourDetailMassive" data-live-search="true" data-width="fit" data-size="5" data-style="btn btn-default btn-xs" data-container="body"></select>
														</select>
													</th>
													<th style="width: 16%;">
														<label style="font-size: 12px;">Trato Categoría</label>
														<br/>
														<select id="listDealCategoryMassive" onchange="loadDealDetail('Massive',this.value)" data-live-search="true" data-width="fit" data-size="5" data-style="btn btn-default btn-xs" data-container="body"></select>
														</select>
													</th>
													<th style="width: 16%;">
														<label style="font-size: 12px;">Trato Detalle</label>
														<br/>
														<select id="listDealDetailMassive" data-live-search="true" data-width="fit" data-size="5" data-style="btn btn-default btn-xs" data-container="body"></select>
														</select>
													</th>

													<th style="width: 6%;">
														<label style="font-size: 12px;">Jorn.</label>
														<br/>
														<input id="txtWorkingDay" class="form-control input-sm numbersOnlyFloat2" style="text-align: right;" onfocusout="verifyValue(this,this.value,1,0,0)" value="0"></input>
													</th>
													</th>
													<th style="width: 7%;">
														<label style="font-size: 12px;">Rend.</label>
														<br/>
														<input id="txtPerformance" class="form-control input-sm numbersOnlyFloat2" style="text-align: right;" onfocusout="verifyValue(this,this.value,50000,0,0)" value="0"></input>
													</th>
													<th style="width: 7%;">
														<label style="font-size: 12px;">Hrs Extr.</label>
														<br/>
														<input id="txtExtraHours" class="form-control input-sm numbersOnlyFloat2" style="text-align: right;" onfocusout="verifyValue(this,this.value,5,0,1)" value="0"></input>
													</th>
												</tr>
											</table>
												
											<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="text-align: center;">
												<br/>
												<button id="btnAddMassiveApply" class="btn btn-success btn-sm"><i class="fa fa-share-square-o fa-lg fa-fw"></i>&nbsp;&nbsp;Aplicar a Trabajadores Seleccionados</button>
											</div>
										</div>
									</div>
									<br/>
								</div>
								<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
									<!--<table id="tableTally" class="table table-hover" style="font-size: 12px;">-->
									<table id="tableTallyHeader" class="table table-hover" style="font-size: 12px; width: 100%; table-layout: fixed;">
										<thead>
											<tr>
												<th style="width: 2%;"></th>
												<th style="width: 2%;"></th>
												<th style="width: 29%;"></th>
												<th style="width: 10%;">Unidad</th>
												<th class="info" colspan="2" style="width: 20%; text-align: center;">Labor</th>
												<th class="warning" colspan="2" style="width: 20%; text-align: center;">Tratos</th>
												<th style="width: 5%;"></th>
												<th style="width: 5%;"></th>
												<th style="width: 5%;"></th>
											</tr>
											<tr>
												<th style="width: 2%;">Sel.</th>
												<th style="width: 2%;">N°</th>
												<th style="width: 29%;">Trabajador</th>
												<th style="width: 10%;">Análisis</th>
												<th class="info" style="width: 10%; text-align: center;">Categoría</th>
												<th class="info" style="width: 10%; text-align: center;">Detalle</th>
												<th class="warning" style="width: 10%; text-align: center;">Categoría</th>
												<th class="warning" style="width: 10%; text-align: center;">Detalle</th>
												<th style="width: 5%; text-align: center;">Jorn.</th>
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
									<label id="lblTotal1">Jornadas:</label><br/>
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