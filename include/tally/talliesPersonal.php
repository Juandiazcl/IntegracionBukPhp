<?php
header('Content-Type: text/html; charset=utf8'); 
session_start();

if(!isset($_SESSION['userId'])){
	header('Location: ../../login.php');
}elseif($_SESSION['display']['talliesPersonal']['view']!=''){
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
			
			$(".datepickerTxt").datepicker({
				format: 'dd/mm/yyyy',
				weekStart: 1
			})
			$(".datepickerTxt").datepicker('setValue', '');

			$(".datepickerTxt").on('changeDate', function(ev) {
				$(".datepickerTxt").datepicker('hide');
			});

			$("#btnRefresh").click(function() {
				loadData();
			});

			$("#btnExcel").click(function() {
				toExcel($("#lblTallyRUT").text(),$("#lblTallyRUTID").text(),$("#lblTallyName").text(),$("#lblTallyPlant").text(),$("#lblTallyPlantID").text(),$("#lblTallyMonth").text(),$("#lblTallyYear").text());
			});

			$('#txtRUT').focusout(function(event) {
				$('#txtRUT').val(orderRUT($('#txtRUT').val()));
				if(verifyRUT($('#txtRUT').val())==true){
					var id = 0
					if($('#labelID').text()!=''){
						id=$('#labelID').text();
					}
					$.post('../../phps/personal_Load.php', {type: 'verifyPersonal', id: id, rut: $('#txtRUT').val()}, function(data, textStatus, xhr) {
						if(data!=0){
							$("#modal-text").text("RUT duplicado");
							$("#modal").modal('show');
							$("#modalNew").modal('show');
						}
					});
				}else{
					if($('#txtRUT').val()!=''){
						$("#modal-text").text('RUT inválido');
						$("#modal").modal('show');
					}
				}
			});
			$('#txtRUT').focus(function(event) {
				$('#txtRUT').val($('#txtRUT').val().replace('-',''));
				$('#txtRUT').val($('#txtRUT').val().replace(/\./g,''));
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
				$("#modalTally").modal('hide');
				cleanModalTally();
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
					$('#tableTallyBody > tr').each(function() {
						var rowNumber = count+1;
						if(count>0){
							list += '&&&&';
						}

						var date = $($(this).children()[2]).text();

						list += $($(this).children()[1]).text() + '&&' +
								date + '&&' +
								$("#listAnalysisUnit"+rowNumber).val() + '&&' +
								$("#listLabourCategory"+rowNumber).val() + '&&' +
								$("#listLabourDetail"+rowNumber).val() + '&&' +
								$("#listDealCategory"+rowNumber).val() + '&&' +
								$("#listDealDetail"+rowNumber).val() + '&&' +
								$($($(this).children()[8]).children()[0]).val() + '&&' +
								$($($(this).children()[9]).children()[0]).val() + '&&' +
								$($($(this).children()[10]).children()[0]).val();

												

						if(date in arrayList ){
							arrayList[date]['jornadatj'] += parseFloat($($($(this).children()[8]).children()[0]).val());
						}else{
							arrayList[date] = {};
							arrayList[date]['name'] = $("#listPersonal"+rowNumber+" option:selected").attr('data-value-name');
							arrayList[date]['jornadatj'] = parseFloat($($($(this).children()[8]).children()[0]).val());						
						}

						count++;
						if(count==total){
							var messageList = '';
							for (key in arrayList){
    							//console.log(key + ": " + arrayList[key]['name'] + " - " + arrayList[key]['jornadatj']);
    							if(arrayList[key]['jornadatj']!=1){
    								messageList += '<br/>' + arrayList[key]['name'] + " - " + toSeparator(arrayList[key]['jornadatj']);
    							}
    						}
    						if(messageList!=''){
    							$("#modalList-text").html("Hay jornadas erróneas o incompletas:" + messageList);
								$("#modalList").modal('show');
							}
							returnSeparator();

							$.post('../../phps/tallies_Save.php', {
								type: 'savePersonal', 
				            	plant: $("#lblTallyPlantID").text(),
								month: $("#lblTallyMonth").text(),
								year: $("#lblTallyYear").text(),
								rut: $("#lblTallyRUTID").text(),
								name: $("#lblTallyName").text(),
				            	total: total,
				            	list: list
							}, function(data, textStatus, xhr) {
								
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
						'<th>RUT</th>' +
						'<th>Nombre</th>' +
						'<th>Campo</th>' +
						'<th>Días Trabaj. Mes</th>' +
						'<th>Ver/Editar</th>' +
						'<th>Excel</th>' +
					'</tr></thead><tbody id="tableDataBody"></tbody>');

			/*$.post('../../phps/tallies_Load.php', {
		            	type: 'personal',
		            	year: $("#listYear").val(),
		            	month: $("#listMonth").val()}, function(data, textStatus, xhr) {
	            		console.log(data);
			});*/

			$('#tableData').dataTable({
				destroy: true,
				paging: false,
				language: { "url": "../../libs/datatables/language/Spanish.json"},
                ajax: {
		            "url": "../../phps/tallies_Load.php",
		            "type": "POST",
		            "data": {
		            	type: 'personal',
		            	plant: $("#listPlant").val(),
		            	year: $("#listYear").val(),
		            	month: $("#listMonth").val()
					},
		            "dataSrc": ""
		        },
		        columnDefs: [
					{
						targets: [3],
						className: 'text-center'
				    }
				],
                columns: [
	                {"data" : "rut"},
					{"data" : "fullname"},
					{"data" : "plant"},
					{"data" : "workingDays"},
					{"data" : "edit"},
					{"data" : "excel"}
                ],
                "fnInitComplete": function(oSettings, json) {
					$("#modalProgress").modal('hide');
			    }
            });
		}

		function modalTallyOne(type,rut,rut_per,name,plant,plant_id,month,year){
			if(type=='edit'){
				$("#btnTallySave").css('display','inline-block');
				$("#tableTally").css('pointer-events','auto');
				$("#btnSelectAll").css('display','inline-block');
				$("#btnAddPersonal").css('display','inline-block');
				$("#btnAddMassive").css('display','inline-block');
				$("#btnDeletePersonal").css('display','inline-block');
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
			$("#lblTallyRUT").text(rut);
			$("#lblTallyName").text(name);
			$("#lblTallyMonth").text(month);
			$("#lblTallyYear").text(year);
			$("#lblTallyPlantID").text(plant_id);
			$("#lblTallyRUTID").text(rut_per);

			/*loadAnalysisUnit();
			loadLabourCategoryAll();
			loadLabourDetailAll();
			loadDealCategory();
			loadDealDetailAll();*/
			$("#modalTally").modal('show');

			$("#modalProgress").modal('show');

			$.post('../../phps/tallies_Load.php', {
				type: 'onePersonal',
            	rut: rut_per,
            	month: month,
            	year: year,
            	plant: plant_id}, function(data, textStatus, xhr) {
				if(data!=0){
					var data = JSON.parse(data);
					for(i=0;i<data.length;i++){
		        		//var rowNumber = data[i]["codtj"];
		        		//var rowNumber = data[i]["fechatj"];
		        		var rowNumber = i+1;

						var row = '<tr>' +
									'<td style="width: 2%; overflow: hidden;"><input type="checkbox"></input></td>' +
									'<td style="width: 4%; overflow: hidden;">'+data[i]["codtj"]+'</td>' +
									'<td style="width: 10%; overflow: hidden;">'+data[i]["tally_date"]+'</td>' +
									'<td style="width: 13%; overflow: hidden;"><select id="listAnalysisUnit'+rowNumber+'" onchange="loadLabourCategory('+rowNumber+',this.value,0)" data-live-search="true" data-width="fit" data-size="5" data-style="btn btn-default btn-xs" data-container="body">'+listAnalysisUnit+'</select></td>' +
									'<td style="width: 13%; overflow: hidden;" class="info"><select id="listLabourCategory'+rowNumber+'" onchange="loadLabourDetail('+rowNumber+',this.value)" data-live-search="true" data-width="fit" data-size="5" data-style="btn btn-default btn-xs" data-container="body"></select></td>' +
									'<td style="width: 13%; overflow: hidden;" class="info"><select id="listLabourDetail'+rowNumber+'" data-live-search="true" data-width="fit" data-size="5" data-style="btn btn-default btn-xs" data-container="body"></select></td>' +
									'<td style="width: 13%; overflow: hidden;" class="warning"><select id="listDealCategory'+rowNumber+'" onchange="loadDealDetail('+rowNumber+',this.value)" data-live-search="true" data-width="fit" data-size="5" data-style="btn btn-default btn-xs" data-container="body"></select></td>' +
									'<td style="width: 13%; overflow: hidden;" class="warning"><select id="listDealDetail'+rowNumber+'" data-live-search="true" data-width="fit" data-size="5" data-style="btn btn-default btn-xs" data-container="body"></select></td>' +
									'<td style="width: 6%; overflow: hidden;"><input class="form-control input-sm numbersOnlyFloat2" style="text-align: right;" onfocusout="verifyValue(this,this.value,1,0,0)" value="'+data[i]["jornadatj"]+'"></input></td>' +
									'<td style="width: 6%; overflow: hidden;"><input class="form-control input-sm numbersOnlyFloat2" style="text-align: right;" onfocusout="verifyValue(this,this.value,50000,0,0)" value="'+data[i]["rendtj"]+'"></input></td>' +
									'<td style="width: 6%; overflow: hidden;"><input class="form-control input-sm numbersOnlyFloat2" style="text-align: right;" onfocusout="verifyValue(this,this.value,'+maxExtraHours+',0,1)" value="'+data[i]["hhtj"]+'"></input></td>' +
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

						//$("#listDealCategory"+rowNumber).val(data[i]["cattrt"]);
						
						//loadDealDetail(rowNumber,data[i]["cattrt"],data[i]["codtrt"]);
						/*if($("#listDealCategory"+rowNumber+" > option").length==1){//Si sólo existe un valor en tratos, se cargará de inmediato el detalle
							$("#listDealCategory"+rowNumber).attr('disabled','disabled');
							loadDealDetail(rowNumber,$("#listDealCategory"+rowNumber).val());
						}else{
							$("#listDealDetail"+rowNumber).selectpicker();
						}*/
						$("#listDealCategory"+rowNumber).selectpicker();
						if(i+1==data.length){
							calculateTotal();
						}
					}
					startParameters();
				}else{
					$("#modalProgress").modal('hide');
					$("#modal-text").text("El trabajador no tiene ninguna tarja registrada este mes");
					$("#modal").modal('show');
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

		function toExcel(rut,rut_per,name,plant,plant_id,month,year){
			$("#modalProgress").modal('show');
			$("#tableDataTallyExcel").html('<tr>' +
						'<th>RUT</th>' +
						'<th>Trabajador</th>' +
						'<th>Fecha Tarja</th>' +
						'<th>Unidad Análisis</th>' +
						'<th>Categoría Labor</th>' +
						'<th>Detalle Labor</th>' +
						'<th>Descripción Categoría Trato</th>' +
						'<th>Descripción Trato</th>' +
						'<th>Jornada</th>' +
						'<th>Horas Extras</th>' +
						'<th>Rendimiento</th>' +
						'<th>Valor Trato</th>' +
						'<th>Rend. en Pesos</th>' +
					'</tr>');

			$.post('../../phps/tallies_Load.php', {
				type: 'onePersonalExcel',
            	rut: rut_per,
            	month: month,
            	year: year,
            	plant: plant_id
            }, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					var list = '';
					if(i+1<data.length){
						list = '<tr>';
						if(i==0){
							list += '<td>'+rut_per+'</td>' +
									'<td>'+name+'</td>';
						}else{
							list += '<td></td>' +
									'<td></td>';
						}
						list += '<td>'+data[i]['tally_date']+'</td>' +
							'<td>'+data[i]['CC2Descrip']+'</td>' +
							'<td>'+data[i]['CC3Descrip']+'</td>' +
							'<td>'+data[i]['CC4Descrip']+'</td>' +
							'<td>'+data[i]['T1Descrip']+'</td>' +
							'<td>'+data[i]['T11Descrip']+'</td>' +
							'<td>'+data[i]['jornadatj']+'</td>' +
							'<td>'+data[i]['rendtj']+'</td>' +
							'<td>'+data[i]['hhtj']+'</td>' +
							'<td>'+data[i]['T11Val']+'</td>' +
							'<td>'+data[i]['rendTotal']+'</td>' +
						'</tr>';
					}else{
						list = '<tr>' +	
							'<th colspan="2">Total General</th>' +
							'<th></th>' +
							'<th></th>' +
							'<th></th>' +
							'<th></th>' +
							'<th></th>' +
							'<th></th>' +
							'<th style="text-align: right;">'+data[i]['jornadatjTotal']+'</th>' +
							'<th style="text-align: right;">'+data[i]['rendtjTotal']+'</th>' +
							'<th style="text-align: right;">'+data[i]['hhtjTotal']+'</th>' +
							'<th style="text-align: right;"></th>' +
							'<th style="text-align: right;">'+data[i]['rendTotal']+'</th>' +
						'</tr>';
					}				

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
					//$("#listPlant").val(1);
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
							}
						});
					}
				}
			});
		}

		function loadPersonal(){
			$.post('../../phps/personal_Load.php', {
				type: "allTally",
				plant: $("#lblTallyPlantID").text()
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
					//$("#listLabourCategory"+rowNumber).attr('disabled','disabled');
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

		function loadLabourDetailAll(rowNumber,cc2,selected,selected2,modalProgressHide){
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
					//$("#listLabourDetail"+rowNumber).attr('disabled','disabled');
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
			/*$.post('../../phps/costCenterGeneral_Load.php', {
				type: "allDeal1",
				cc1trt: $("#lblTallyPlantID").text()
			}, function(data, textStatus, xhr) {
				listDealCategory = '<option value="-1">SEL. TRATO</option>';
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					if(i+1==data.length){
						if(i+1==1){
							listDealCategory += '<option value="'+data[i]["cattrt"]+'" selected>'+data[i]["cattrt"]+' '+data[i]["descattrt"]+'</option>';
							$("#listDealCategoryMassive").html('<option value="-1">SEL. TRATO</option><option value="'+data[i]["cattrt"]+'">'+data[i]["cattrt"]+' '+data[i]["descattrt"]+'</option>');
						}else{
							listDealCategory += '<option value="'+data[i]["cattrt"]+'">'+data[i]["cattrt"]+' '+data[i]["descattrt"]+'</option>';
							$("#listDealCategoryMassive").html(listDealCategory);
						}
						$("#listDealCategoryMassive").selectpicker();
						$("#listDealDetailMassive").html('<option value="-1">SEL. DETALLE</option>');
						$("#listDealDetailMassive").selectpicker();
					}else{
						listDealCategory += '<option value="'+data[i]["cattrt"]+'">'+data[i]["cattrt"]+' '+data[i]["descattrt"]+'</option>';
					}
				}
			});*/
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
			$("#lblTallyPlant").text('');
			$("#lblTallyDate").text('');
			$("#lblTallyDateDay").text('');

			$("#tableTallyBody").html('');

			$("#listAnalysisUnitMassive").selectpicker('destroy');
			$("#listLabourCategoryMassive").selectpicker('destroy');
			$("#listLabourDetailMassive").selectpicker('destroy');
			$("#listDealCategoryMassive").selectpicker('destroy');
			$("#listDealDetailMassive").selectpicker('destroy');
			$("#listAnalysisUnitMassive").html('');
			$("#listLabourCategoryMassive").html('');
			$("#listLabourDetailMassive").html('');
			$("#listDealCategoryMassive").html('');
			$("#listDealDetailMassive").html('');
			$("#txtWorkingDay").val(0);
			$("#txtPerformance").val(0);
			$("#txtExtraHours").val(0);
			
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
				<div class="panel-heading"><i class="fa fa-vcard-o fa-lg"></i>&nbsp;&nbsp; Tarjas por Trabajador</div>
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
							<br/>
							<button id="btnRefresh" class="btn btn-primary"><i class="fa fa-refresh fa-lg fa-fw"></i>&nbsp;&nbsp;Recargar</button>
							<br/>
						</div>
						
					</div>	
					<br/>
					<br/>
					<div class="table-responsive">
						<table id="tableData" class="table table-hover" style="font-size: 12px;">
						</table>
						<table id="tableDataTallyExcel" style="display: none;">
						</table>
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

	<div id="modalList" class="modal fade" data-backdrop="static" style="z-index: 1051">
		<div class="modal-dialog modal-md">
			<div class="modal-content">
	        	<div class="modal-body">
		    	    <p id="modalList-text"></p>
		      	</div>
		      	<div class="modal-footer">
		        	<!--<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>-->
		        	<button id="modalListHide" type="button" class="btn btn-primary">Aceptar</button>
		      	</div>
		    </div>
		</div>
	</div>

	<div id="modalTallyNew" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-md">
			<div class="modal-content">
	        	<div id="modal_body" class="modal-body">
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
	        	<div id="modal_body" class="modal-body">
		    	    <div class="modal-body">
		        		<div class="panel panel-primary">
							<div class="panel-heading"><i class="fa fa-address-book-o fa-lg"></i>&nbsp;&nbsp; TARJA:&nbsp; 
								<label id="lblTallyPlant"></label>&nbsp;-
								<label id="lblTallyMonth"></label>/<label id="lblTallyYear"></label>&nbsp;-
								<label id="lblTallyRUT"></label>
								<label id="lblTallyName"></label>
								<label id="lblTallyPlantID" style="display: none;"></label>
								<label id="lblTallyRUTID" style="display: none;"></label>
							</div>
							<div class="panel-body">
								<div class="row">
									<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
										<button id="btnSelectAll" class="btn btn-primary btn-sm"><i class="fa fa-check-square-o fa-lg fa-fw"></i>&nbsp;Seleccionar Todo</button>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
										<!--<button id="btnAddPersonal" class="btn btn-success btn-sm"><i class="fa fa-plus fa-lg fa-fw"></i>&nbsp;&nbsp;Agregar Trabajador</button>-->
									</div>
									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
										<button id="btnDeletePersonal" class="btn btn-danger btn-sm"><i class="fa fa-minus fa-lg fa-fw"></i>&nbsp;&nbsp;Quitar Tarjas Seleccionadas</button>
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
													<button id="btnAddMassiveApply" class="btn btn-success btn-sm"><i class="fa fa-share-square-o fa-lg fa-fw"></i>&nbsp;&nbsp;Aplicar a Tarjas Seleccionadas</button>
												</div>
											</div>
										</div>
										<br/>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
										<table id="tableTallyHeader" class="table table-hover" style="font-size: 12px; width: 100%; table-layout: fixed;">
											<thead>
												<tr>
													<th style="width: 2%;"></th>
													<th style="width: 4%;"></th>
													<th style="width: 10%;"></th>
													<th style="width: 13%;">Unidad</th>
													<th class="info" colspan="2" style="width: 26%; text-align: center;">Labor</th>
													<th class="warning" colspan="2" style="width: 26%; text-align: center;">Tratos</th>
													<th style="width: 6%;"></th>
													<th style="width: 6%;"></th>
													<th style="width: 6%;"></th>
												</tr>
												<tr>
													<th style="width: 2%;">Sel.</th>
													<th style="width: 4%;">N° Tar.</th>
													<th style="width: 10%;">Fecha</th>
													<th style="width: 13%;">Análisis</th>
													<th class="info" style="width: 13%; text-align: center;">Categoría</th>
													<th class="info" style="width: 13%; text-align: center;">Detalle</th>
													<th class="warning" style="width: 13%; text-align: center;">Categoría</th>
													<th class="warning" style="width: 13%; text-align: center;">Detalle</th>
													<th style="width: 6%; text-align: center;">Jorn.</th>
													<th style="width: 6%; text-align: center;">Rend.</th>
													<th style="width: 6%; text-align: center;">Hrs.</th>
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

								</div>
							</div>
						</div>
			      	</div>
		      	</div>
		      	<div class="modal-footer">
		        	<button id="btnTallySave" type="button" class="btn btn-success"><i class="fa fa-save fa-lg"></i>&nbsp;Almacenar</button>
		        	<button id="modalTallyHide" type="button" class="btn btn-primary"><i class="fa fa-remove fa-lg"></i>&nbsp;Salir</button>
		      	</div>
		    </div>
		</div>
	</div>

</body>
</html>