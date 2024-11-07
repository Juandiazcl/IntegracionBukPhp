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
	<link rel="stylesheet" type="text/css" href="../../libs/datatables/datatables.min.css"/>
 	<script type="text/javascript" src="../../libs/datatables/datatables.min.js"></script>
	<script type="text/javascript" src="../../libs/jquery.table2excel.js"></script>	
	<link rel="stylesheet" type="text/css" href="../../libs/bootstrap-select/css/bootstrap-select.css"/>
 	<script type="text/javascript" src="../../libs/bootstrap-select/js/bootstrap-select.js"></script>
	<title></title>
	<script type="text/javascript">

	var idList = 1, cancelCalculate;

		$(document).ready(function() {
			$(function () {
			  $('[data-toggle="popover"]').popover()
			})

			loadMenu();
			startParameters();
			
			$('#txtCellphone').mask('00-0000000');
			$('#txtPhone').mask('00-0-0000000');

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

			$("#toExcel").click(function() {
				$("#tableDataExcel").table2excel({
					exclude: ".noExl",
					name: "Excel Document Name",
					filename: "Lista",
					fileext: ".xls",
					exclude_img: true,
					exclude_links: true,
					exclude_inputs: true
				});
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

			$("#modalDeleteHide").click(function() {
				$("#modalDelete").modal('hide');	
			});

			$("#listAccount").change(function() {
				if($(this).val()=='banco'){
					$("#btnGenerateTxt").removeClass('btn-warning');
					$("#btnGenerateTxt").addClass('btn-primary');
					$("#btnGenerateTxt").html('<i class="fa fa-paypal fa-lg fa-fw"></i>&nbsp;&nbsp;Generar TXT Banco');
					$(".servipagDate").css('visibility','hidden');
				}else{
					$("#btnGenerateTxt").removeClass('btn-primary');
					$("#btnGenerateTxt").addClass('btn-warning');
					$("#btnGenerateTxt").html('Generar Excel&nbsp;&nbsp;<img src="../../images/servipag.png" style="width: 64px;" />');
					$(".servipagDate").css('visibility','visible');
				}
				$("#modalDelete").modal('hide');	
			});

			$("#listPay").change(function() {
				changeColorValues();
			});

			$("#btnGenerateTxt").click(function() {
				if($("#listAccount").val()=='banco'){
					if($("#listEnterprise").val()!=0){
						var count = 0, countTotal = 0;
						var list = '';
						$('#tableData > tbody > tr').each(function() {
							if($(this).find('td').find(">:first-child").is(':checked')){
								if(count==0){
									list = $($($(this).children()[3]).children()[0]).text();
								}else{
									list += ','+$($($(this).children()[3]).children()[0]).text();
								}
								count++;
							}
							
							countTotal++;
							if(countTotal==$('#tableData > tbody > tr').length){
								if(count>0){
									$.post('../../phps/remunerationTxt_Load.php', {
										type: 'setTxt', 
										enterprise: $("#listEnterprise").val(),
						            	year: $("#listYear").val(),
						            	month: $("#listMonth").val(),
						            	state: $("#listState").val(),
						            	plant: $("#listPlant").val(),
						            	pay: $("#listPay").val(),
						            	list: list
									}, function(data, textStatus, xhr) {
										if(data=='OK'){
											$("#modal-text").text("Nómina generada correctamente, revisar en 'Ver nóminas'");
											$("#modal").modal('show');
										}else{
											$("#modal-text").text("Error al generar");
											$("#modal").modal('show');
										}
									});
									
								}else{				
									$("#modal-text").text("Debe seleccionar al menos 1 trabajador");
									$("#modal").modal('show');
								}
							}
						});
						
					}else{
						$("#modal-text").text("Debe seleccionar 1 sola empresa");
						$("#modal").modal('show');
					}
				}else{
					if($("#listEnterprise").val()!=0){
						var count = 0, countTotal = 0;
						var valueTotal = 0;
						var list = '';

						var dateStart = $('#txtServipagDate').val().split('/');
						var dateEnd = moment([dateStart[2],dateStart[1]-1,dateStart[0]]).add(1,'months').format('DD/MM/YYYY').split('/');
						var dateStart = ''+dateStart[2]+dateStart[1]+dateStart[0];
						var dateEnd = ''+dateEnd[2]+dateEnd[1]+dateEnd[0];

						$('#tableData > tbody > tr').each(function() {
							if($(this).find('td').find(">:first-child").is(':checked')){
								count++;
								var payment = parseInt($($($(this).children()[3]).children()[2]).text());
								if($("#listPay").val()=='D026'){
									payment = parseInt($($($(this).children()[3]).children()[3]).text());
								}
								while (payment>700000) { //Para los sueldos mayores a 700mil
									list += '<tr>' +
											'<td>&#8203;02</td>' +
											'<td>'+$($($(this).children()[3]).children()[0]).text()+'-'+$($($(this).children()[3]).children()[1]).text()+'</td>' +
											'<td>R</td>' +
											'<td>'+count+'</td>' +
											'<td>'+$($(this).children()[4]).html()+'</td>' +
											'<td>'+dateStart+'</td>' +
											'<td>'+dateEnd+'</td>' +
											'<td>700.000</td>' +
											'<td>SUELDOS '+$("#listMonth option:selected").text().toUpperCase()+' '+$("#listYear").val()+'</td>' +
										'</tr>';
									payment -= 700000;
									count++;
								}

								//'<td  style="mso-number-format:\"\@\";">&#8203;02&#8203;</td>' +
								list += '<tr>' +
											'<td>&#8203;02</td>' +
											'<td>'+$($($(this).children()[3]).children()[0]).text()+'-'+$($($(this).children()[3]).children()[1]).text()+'</td>' +
											'<td>R</td>' +
											'<td>'+count+'</td>' +
											'<td>'+$($(this).children()[4]).html()+'</td>' +
											'<td>'+dateStart+'</td>' +
											'<td>'+dateEnd+'</td>' +
											'<td>'+toSeparator(payment)+'</td>' +
											'<td>SUELDOS '+$("#listMonth option:selected").text().toUpperCase()+' '+$("#listYear").val()+'</td>' +
										'</tr>';

								if($("#listPay").val()=='D026'){
									valueTotal += parseInt($($($(this).children()[3]).children()[3]).text()); //Valor total anticipo
								}else{
									valueTotal += parseInt($($($(this).children()[3]).children()[2]).text()); //Valor total
								}
							}
							countTotal++;
							if(countTotal==$('#tableData > tbody > tr').length){
								if(count>0){
									list = '<tr>' +
											'<td>&#8203;01</td>' +
											'<td>'+count+'</td>' +
											'<td>'+valueTotal+'</td>' +
											'<td>I</td>' +
											'<td></td>' +
											'<td></td>' +
											'<td></td>' +
											'<td></td>' +
											'<td></td>' +
										'</tr>'+list;


									$("#tableDataExcelServipag").html(list);
									$("#tableDataExcelServipag").table2excel({
										exclude: ".noExl",
										name: "Excel Document Name",
										filename: "Lista",
										fileext: ".xls",
										exclude_img: true,
										exclude_links: true,
										exclude_inputs: true
									});

									/*$.post('../../phps/remunerationTxt_Load.php', {
										type: 'setTxt', 
										enterprise: $("#listEnterprise").val(),
						            	year: $("#listYear").val(),
						            	month: $("#listMonth").val(),
						            	state: $("#listState").val(),
						            	plant: $("#listPlant").val(),
						            	list: list
									}, function(data, textStatus, xhr) {
										if(data=='OK'){
											$("#modal-text").text("Nómina generada correctamente, revisar en 'Ver nóminas'");
											$("#modal").modal('show');
										}else{
											$("#modal-text").text("Error al generar");
											$("#modal").modal('show');
										}
									});*/
									
								}else{				
									$("#modal-text").text("Debe seleccionar al menos 1 trabajador");
									$("#modal").modal('show');
								}
							}
						});
						
					}else{
						$("#modal-text").text("Debe seleccionar 1 sola empresa");
						$("#modal").modal('show');
					}
				}
			});

			$("#btnGenerateFullPDF").click(function() {
				var count = 0, countTotal = 0;
				var list = '', listCostCenter = '', firstSettlement = 0;
				$('#tableData > tbody > tr').each(function() {
					if($(this).find('td').find(">:first-child").is(':checked')){
						if(count==0){
							list = $($($(this).children()[3]).children()[0]).text();
							listCostCenter = $($($(this).children()[3]).children()[5]).text();
							if($($($(this).children()[3]).children()[6]).text()!=0){
								firstSettlement = 1;
							}
						}else{
							list += ','+$($($(this).children()[3]).children()[0]).text();
							listCostCenter += ','+$($($(this).children()[3]).children()[5]).text();
						}
						count++;
					}
					
					countTotal++;
					if(countTotal==$('#tableData > tbody > tr').length){
						if(count>0){
							$.post('../../phps/remuneration_Save.php', {
								type: 'generate', 
				            	list: list,
				            	listCostCenter: listCostCenter
							}, function(data, textStatus, xhr) {
								if(data=='OK'){

									generatePDFLink('all',$("#listYear").val(),$("#listMonth").val(),0,0,firstSettlement);
								}else{
									$("#modal-text").text("Error al generar");
									$("#modal").modal('show');
								}
							});
							
						}else{				
							$("#modal-text").text("Debe seleccionar al menos 1 trabajador");
							$("#modal").modal('show');
						}
					}
				});
			});

			$("#btnCalculateModal").click(function() {
				var countTotal = 0, count = 0;
				if($('#tableData > tbody > tr').length>0){
					$('#tableData > tbody > tr').each(function() {
						if($(this).find('td').find(">:first-child").is(':checked')){
							count++;
						}
						countTotal++;
						if(countTotal==$('#tableData > tbody > tr').length){
							if(count==0){
								$("#modal-text").text("Debe seleccionar al menos 1 trabajador");
								$("#modal").modal('show');
							}else{
								if(count==1){
									$("#lblCalculateQuantity").text('Se calculará 1 liquidación, presione Calcular para continuar');
								}else{
									$("#lblCalculateQuantity").text('Se calcularán '+count+' liquidaciones, presione Calcular para continuar');
								}
								$("#lblCalculateQuantityTotal").text(count);
								$("#pbCalculate").text("0 / "+count);
								$("#modalCalculate").modal('show');
							}
						}
					});
				}else{
					$("#modal-text").text("Debe seleccionar al menos 1 trabajador");
					$("#modal").modal('show');
				}
			});

			$("#btnCalculateHide").click(function() {
				$("#lblCalculateQuantityTotal").text("");
				$("#pbCalculate").text("");
				$("#pbCalculate").attr('aria-valuenow',0);
				$("#pbCalculate").css('width','0%');
				$("#divCalculate").html('');
				$("#modalCalculate").modal('hide');
				loadData();
			});

			$("#btnCalculateCancel").click(function() {
				cancelCalculate = true;
				$("#modalProgressCancel").modal('show');
			});

			$("#btnCalculate").click(function() {
				var total = parseInt($("#lblCalculateQuantityTotal").text());
				$("#pbCalculate").text("0 / "+total);
				$("#pbCalculate").attr('aria-valuenow',0);
				$("#pbCalculate").css('width','0%');
				$("#btnCalculate").attr('disabled','disabled');
				$("#btnCalculateSpinner").css('display','inline-block');
				$("#pbCalculate").addClass('active');
				$("#pbCalculate").removeClass('progress-bar-success');
				$("#pbCalculate").removeClass('progress-bar-warning');
				$("#btnCalculateCancel").css('display','inline-block');
				$("#divCalculate").html('');

				var count = 0, countTotal = 0;
				var list = '', listCostCenter = '', listName = '', listSettlement = '';

				var count = 0;

				$('#tableData > tbody > tr').each(function() {
					if($(this).find('td').find(">:first-child").is(':checked')){
						if(count==0){
							list = $($($(this).children()[3]).children()[0]).text();
							listCostCenter = $($($(this).children()[3]).children()[5]).text();
							listName = $($(this).children()[4]).text();
							listSettlement = $($($(this).children()[3]).children()[6]).text();
						}else{
							list += ','+$($($(this).children()[3]).children()[0]).text();
							listCostCenter += ','+$($($(this).children()[3]).children()[5]).text();
							listName += ','+$($(this).children()[4]).text();
							listSettlement += ','+$($($(this).children()[3]).children()[6]).text();
						}
						count++;
					}
					
					countTotal++;
					if(countTotal==$('#tableData > tbody > tr').length){
						///////Limpieza de registros antiguos///////
						$.post('../../phps/remuneration_Save.php', {
							type: 'clean',
							year: $("#listYear").val(),
	            			month: $("#listMonth").val(),
			            	list: list,
			            	listCostCenter: listCostCenter,
			            	listSettlement: listSettlement
						}, function(data, textStatus, xhr) {
							console.log('Limpieza de registros: '+data);
							//return;
							if(data=='OK'){
								///////Cálculo de Liquidaciones///////
								count = 0;
								total = parseInt($("#lblCalculateQuantityTotal").text());
								var i=0, actualCount = 0;

								var arrayList = list.split(',');
								var arrayListCC = listCostCenter.split(',');
								var arrayListName = listName.split(',');
								var arrayListSettlement = listSettlement.split(',');

								calculate($("#listYear").val(),$("#listMonth").val(),arrayList,arrayListCC,arrayListName,arrayListSettlement,total,0);
								
							}else{
								var total = parseInt($("#lblCalculateQuantityTotal").text());
								$("#pbCalculate").text("0 / "+total);
								$("#pbCalculate").attr('aria-valuenow',0);
								$("#pbCalculate").css('width','0%');
								$("#btnCalculate").removeAttr('disabled');
								$("#btnCalculateSpinner").css('display','none');
								$("#btnCalculateCancel").css('display','none');

								$("#modal-text").text("Ha ocurrido un error al limpiar datos, favor reintentar");
								$("#modal").modal('show');
							}
						});
					}
				});
				

			});

			$("#btnModalManualHide").click(function() {
				$("#modalManual").modal('hide');
				$("#tableManualHBody").html('');
				$("#tableManualDBody").html('');
				$("#lblManualYear").text('');
				$("#lblManualMonth").text('');
				$("#lblManualCostCenter").text('');
				$("#lblManualRUT").text('');
				$("#lblManualSettlement").text('');
				$("#txtManualRUT").val('');
				$("#txtManualName").val('');
			});

			$("#btnModalManualSave").click(function() {
				$("#modalProgress").modal('show');
				returnNoSeparator();
				var list = '';
				var countH = 0, countD = 0;
				$('#tableManualHBody > tr').each(function() {
					if(countH>0){
						list += '&&&&';
					}
					list += $($(this).children()[0]).text()+'&&'+
							$($($(this).children()[2]).children()[0]).val()+'&&'+
							$($($(this).children()[2]).children()[1]).text();
					countH++;
					if(countH==$('#tableManualHBody > tr').length){

						$('#tableManualDBody > tr').each(function() {
							list += '&&&&' +
									$($(this).children()[0]).text()+'&&'+
									$($($(this).children()[2]).children()[0]).val()+'&&'+
									$($($(this).children()[2]).children()[1]).text();
							countD++;
							if(countD==$('#tableManualDBody > tr').length){
								$.post('../../phps/remuneration_Save.php', {
									type: 'oneManual',
									year: $("#lblManualYear").text(),
			            			month: $("#lblManualMonth").text(),
			            			costCenter: $("#lblManualCostCenter").text(),
			            			rut: $("#lblManualRUT").text(),
					            	list: list,
					            	settlement: $("#lblManualSettlement").text()
								}, function(data, textStatus, xhr) {
									console.log(data);
									$("#modalProgress").modal('hide');
									returnSeparator();
									if(data=='OK'){
										$("#modal-text").text("Datos almacenados");
										$("#modal").modal('show');
									}else{
										$("#modal-text").text("Error al almacenar, contactar al administrador");
										$("#modal").modal('show');
									}
								});
							}
						});
					}
				});
			});

			$("#btnManualFull").click(function() {
				var count = 0, countTotal = 0;
				var list = '', listCostCenter = '', listRUT = '', listName = '';
				if($('#tableData > tbody > tr').length>0){
					$('#tableData > tbody > tr').each(function() {
						if($(this).find('td').find(">:first-child").is(':checked')){
							if(count==0){
								list = $($($(this).children()[3]).children()[0]).text();
								listCostCenter = $($($(this).children()[3]).children()[5]).text();
								listRUT = $($($(this).children()[3]).children()[4]).text();
								listName = $($(this).children()[4]).text();
							}else{
								list += ','+$($($(this).children()[3]).children()[0]).text();
								listCostCenter += ','+$($($(this).children()[3]).children()[5]).text();
								listRUT += ','+$($($(this).children()[3]).children()[4]).text();
								listName += ','+$($(this).children()[4]).text();
							}
							count++;
						}
						
						countTotal++;
						if(countTotal==$('#tableData > tbody > tr').length){
							if(count>0){
								$("#modalManualFull").modal('show');
								$("#lblManualFullYear").text($("#listYear").val());
								$("#lblManualFullMonth").text($("#listMonth").val());
								var arrayList = list.split(',');
								var arrayListCC = listCostCenter.split(',');
								var arrayListRUT = listRUT.split(',');
								var arrayListName = listName.split(',');
								for(i=0;i<arrayList.length;i++){
									var row = '<tr>' +
												'<td style="display: none;">'+arrayList[i]+'</td>' +
												'<td style="display: none;">'+arrayListCC[i]+'</td>' +
												'<td>'+arrayListRUT[i]+'</td>' +
												'<td>'+arrayListName[i]+'</td>' +
												'<td><input id="txtManualFull_'+arrayList[i]+'_'+arrayListCC[i]+'" class="form-control input-sm numbersOnlyFloatMoney" style="text-align: right;" disabled></td>' +
											'</tr>';
									$("#tableManualFullBody").append(row);
								}
								
							}else{				
								$("#modal-text").text("Debe seleccionar al menos 1 trabajador");
								$("#modal").modal('show');
							}
						}
					});

				}else{				
					$("#modal-text").text("Debe seleccionar al menos 1 trabajador");
					$("#modal").modal('show');
				}
			});


			$("#btnManualRefresh").click(function() {
				if($("#listManualFull").val()!=0){
					$("#modalProgress").modal('show');
					var countTotal = 0;
					var list = '', listCostCenter = '';
					$("#lblManualFull").text($("#listManualFull").val());
					$('#tableManualFullBody > tr').each(function() {
						if(countTotal==0){
							list = $($(this).children()[0]).text();
							listCostCenter = $($(this).children()[1]).text();
						}else{
							list += ','+$($(this).children()[0]).text();
							listCostCenter += ','+$($(this).children()[1]).text();
						}
						
						countTotal++;
						if(countTotal==$('#tableData > tbody > tr').length){
							$("#modalManualFull").modal('show');
							$.post('../../phps/remuneration_Load.php', {
								type: 'fullManual',
				            	year: $("#listYear").val(),
				            	month: $("#listMonth").val(),
				            	manual: $("#listManualFull").val(),
				            	list: list,
				            	listCostCenter: listCostCenter
							}, function(data, textStatus, xhr) {
								$("#modalProgress").modal('hide');
								var data = JSON.parse(data);
								for(i=0;i<data.length;i++){
									$("#txtManualFull_"+data[i]['rut']+"_"+data[i]['costCenter']).removeAttr('disabled');
									$("#txtManualFull_"+data[i]['rut']+"_"+data[i]['costCenter']).val(data[i]['manual']);
								}
							});
						}
					});
				}else{
					$("#lblManualFull").text('');
					$('#tableManualFullBody > tr').each(function() {
						$($($(this).children()[4]).children()[0]).attr('disabled','disabled');
						$($($(this).children()[4]).children()[0]).val('');
					});
				}
			});


			$("#btnManualFullSave").click(function() {
				if($("#lblManualFull").text()!=''){
					$("#modalProgress").modal('show');
					returnNoSeparator();
					var countTotal = 0;
					var list = '', listCostCenter = '';
					$("#lblManualFull").text($("#listManualFull").val());
					$('#tableManualFullBody > tr').each(function() {
						if(countTotal==0){
							list = $($(this).children()[0]).text();
							listCostCenter = $($(this).children()[1]).text();
							if($($($(this).children()[4]).children()[0]).val()!=''){
								listValues = $($($(this).children()[4]).children()[0]).val();
							}else{
								listValues = 0;
							}
						}else{
							list += ','+$($(this).children()[0]).text();
							listCostCenter += ','+$($(this).children()[1]).text();
							if($($($(this).children()[4]).children()[0]).val()!=''){
								listValues += ','+$($($(this).children()[4]).children()[0]).val();
							}else{
								listValues += ',0';
							}
						}
						
						countTotal++;
						if(countTotal==$('#tableData > tbody > tr').length){
							$("#modalManualFull").modal('show');
							$.post('../../phps/remuneration_Save.php', {
								type: 'fullManual', 
				            	year: $("#listYear").val(),
				            	month: $("#listMonth").val(),
				            	manual: $("#lblManualFull").text(),
				            	list: list,
				            	listCostCenter: listCostCenter,
				            	listValues: listValues
							}, function(data, textStatus, xhr) {
								returnSeparator();
								$("#modalProgress").modal('hide');
								/*for(i=0;i<data.length;i++){
									$("#txtManualFull_"+data[i]['rut']+"_"+data[i]['costCenter']).removeAttr('disabled');
									$("#txtManualFull_"+data[i]['rut']+"_"+data[i]['costCenter']).val(data[i]['manual']);
								}*/
							});
						}
					});
				}else{
					$("#modal-text").text("Debe seleccionar un valor a modificar");
					$("#modal").modal('show');
				}

			});


			$("#btnManualFullHide").click(function() {
				$("#listManualFull").val(0);
				$("#lblManualFullYear").text('');
				$("#lblManualFullMonth").text('');
				$("#tableManualFullBody").html('');
				$("#modalManualFull").modal('hide');
			});


			$("#delete").click(function() {
				var id = $("#modal-delete-id").text();
				$.post('../../phps/settlement_Save.php', {type: 'delete', id: id}, function(data, textStatus, xhr) {
					if(data=='OK'){
						loadData();
						$("#modalDelete").modal('hide');
						$("#modal-text").text("Finiquito deshecho Satisfactoriamente");
						$("#modal").modal('show');
					}else{
						$("#modalDelete").modal('hide');
						$("#modal-text").text("Error al deshacer");
						$("#modal").modal('show');
					}
				});
			});

		
			$("#listState").change(function(){
			});

			$("#listPlant").change(function(){
			});

			/*$("#generatePDF").click(function() {
				if($("#divID").css('display')=='none'){
					$("#modal-text").text("Debe primero guardar Finiquito");
					$("#modal").modal('show');
				}else{
					generatePDFLink('all',$("#labelID").text(),'-');
				}
			});*/

			$("#btnSelectAll").click(function() {
				if($(this).text()=='Seleccionar Todo'){
					$(this).text('Borrar Selección');
					$('#tableData > tbody > tr').each(function() {
						$($(this).children()[0]).find(">:first-child").prop('checked', true);
					});
				}else{
					$(this).text('Seleccionar Todo');
					$('#tableData > tbody > tr').each(function() {
						$($(this).children()[0]).find(">:first-child").prop('checked', false);
					});
				}
			});

			$("#toExcel").click(function() {
				toExcel();
			});

			loadEnterprise();
			loadPlant();
			loadYear();
			loadManualFull();
		});

		function loadData(){
			
			$("#modalProgress").modal('show');
			$("#tableData").html('<thead><tr>' +
						'<th>Sel.</th>' +
						'<th>Empresa</th>' +
						'<th>Campo</th>' +
						'<th>RUT</th>' +
						'<th>Nombre</th>' +
						'<th>TRAB</th>' +
						'<th>SEPT</th>' +
						'<th>LIC</th>' +
						'<th>AUS</th>' +
						'<th>Líquido $</th>' +
						'<th>Anticipo $</th>' +
						'<th>Bono $</th>' +
						'<th>A Pago $</th>' +
						'<th>Datos Man.</th>' +
						'<th>Liq.</th>' +
					'</tr></thead><tbody id="tableDataBody"></tbody>');

			var zeroValues = 0;
			if($("#chkValues").prop('checked')==true){
				zeroValues = 1;
			}

			var onlyCalculate = 0;
			if($("#chkCalculate").prop('checked')==true){
				onlyCalculate = 1;
			}



			var plant = "";
			for(i=0;i<$("#listPlant").val().length;i++){
				if(i>0){
					plant += '&&';
				}
				plant += $("#listPlant").val()[i];
			}
			if(plant==''){
				$("#modalProgress").modal('hide');
				$("#modal-text").text("Debe seleccionar al menos 1 campo");
				$("#modal").modal('show');
				return;
			}
			/*console.log(plant);
			$("#modalProgress").modal('hide');
			return;*/

			/*$.post('../../phps/remuneration_Load.php', {type: 'all',
            	enterprise: $("#listEnterprise").val(),
            	year: $("#listYear").val(),
            	month: $("#listMonth").val(),
            	state: $("#listState").val(),
            	plant: plant,
            	accountType: $("#listAccount").val(),
            	zeroValues: zeroValues,
            	onlyCalculate: onlyCalculate,
            	pay: $("#listPay").val()}, function(data, textStatus, xhr) {
            		console.log(data);
			});*/

			$('#tableData').dataTable({
				destroy: true,
				paging: false,
				language: { "url": "../../libs/datatables/language/Spanish.json"},
                ajax: {
		            "url": "../../phps/remuneration_Load.php",
		            "type": "POST",
		            "data": {
		            	type: 'all',
		            	enterprise: $("#listEnterprise").val(),
		            	year: $("#listYear").val(),
		            	month: $("#listMonth").val(),
		            	state: $("#listState").val(),
		            	plant: plant,
		            	accountType: $("#listAccount").val(),
		            	zeroValues: zeroValues,
		            	onlyCalculate: onlyCalculate,
		            	pay: $("#listPay").val()
					},
		            "dataSrc": ""
		        },
		        columnDefs: [
					{
						targets: [5,6,7,8,9,10,11,12],
						className: 'text-right'
				    }
				],
                columns: [
	                {"data" : "sel"},
	                {"data" : "enterprise"},
	                {"data" : "plant"},
					{"data" : "rut"},
					{"data" : "fullname"},
					{"data" : "days_worked"},
					{"data" : "days_seventh"},
					{"data" : "days_license"},
					{"data" : "days_abscent"},
					{"data" : "rem_total"},
					{"data" : "rem_advance"},
					{"data" : "rem_bonus"},
					{"data" : "rem_topay"},
					{"data" : "manual"},
					{"data" : "pdf"}
                ],
                "fnInitComplete": function(oSettings, json) {
                	changeColorValues();
					$("#modalProgress").modal('hide');
			    }
            });
		}

		function calculate(year, month, arrayList, arrayListCC, arrayListName, arrayListSettlement, total, actual){
			if(cancelCalculate==true){
				$("#modalProgressCancel").modal('hide');
				$("#modal-text").text("Calculo cancelado");
				$("#modal").modal('show');
				$("#btnCalculate").removeAttr('disabled');
				$("#btnCalculateSpinner").css('display','none');
				$("#btnCalculateCancel").css('display','none');
				$("#pbCalculate").removeClass('active');
				$("#pbCalculate").addClass('progress-bar-warning');
				cancelCalculate = false;
				return;
			}

			if(actual<total){
				var rut = arrayList[actual];
				var costCenter = arrayListCC[actual];
				var name = arrayListName[actual];
				var settlement = arrayListSettlement[actual];
				$.post('../../phps/remuneration_Save.php', {
					type: 'calculate',
					year: year,
	    			month: month,
	            	list: rut,
	            	listCostCenter: costCenter,
	            	settlement: settlement
				}, function(dataX, textStatus, xhr) {
					console.log(dataX);
					actual++;
					var percentage = (actual * 100) / total;
					$("#pbCalculate").text(actual+" / "+total);
					$("#pbCalculate").attr('aria-valuenow',percentage);
					$("#pbCalculate").css('width',percentage+'%');

					var row = '<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3" style="height: 30px;">' +
								name+'<br/><br/>' +
							'</div>' +
							'<div class="col-xs-12 col-sm-12 col-md-1 col-lg-1">';	
					/*if(actual==5 || actual==7 || actual==8){
						dataX = 'Test error';
					}*/

					if(dataX=='OK'){
						row += '<button class="btn btn-danger" onclick="generatePDFLink(\'one\','+year+','+month+','+rut+','+costCenter+','+settlement+')"><i class="fa fa-file-pdf-o fa-lg fa-fw"></i></button>';
					}else{
						row += '<button class="btn btn-default" title="Ocurrió un error al calcular, favor recalcule la liquidación de forma individual"><i class="fa fa-remove fa-lg fa-fw"></i></button>';
					}
					row += '</div>';

					$("#divCalculate").append(row);

					calculate(year, month, arrayList, arrayListCC, arrayListName, arrayListSettlement, total, actual);
				});
			}else{
				$("#modal-text").text("Calculo realizado");
				$("#modal").modal('show');
				$("#btnCalculate").removeAttr('disabled');
				$("#btnCalculateSpinner").css('display','none');
				$("#btnCalculateCancel").css('display','none');
				$("#pbCalculate").removeClass('active');
				$("#pbCalculate").addClass('progress-bar-success');
				cancelCalculate = false;
			}
		}


		function toExcel(){

			$("#modalProgress").modal('show');
			$("#tablaRegistrosExcel").html('<tr>' +
						'<th>Empresa</th>' +
						'<th>Campo</th>' +
						'<th>RUT</th>' +
						'<th>Nombre</th>' +
						'<th>TRAB</th>' +
						'<th>SEPT</th>' +
						'<th>LIC</th>' +
						'<th>AUS</th>' +
						'<th>Líquido $</th>' +
						'<th>Anticipo $</th>' +
						'<th>A Pago $</th>' +
					'</tr>');


			/*$.post('../../phps/remuneration_Load.php', {
				type: 'all',
         		enterprise: $("#listEnterprise").val(),
            	year: $("#listYear").val(),
            	month: $("#listMonth").val(),
            	state: $("#listState").val(),
            	plant: $("#listPlant").val(),
            	accountType: $("#listAccount").val(),
            	zeroValues: $("#listValues").val(),
            	pay: $("#listPay").val()
            }, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){

					var list = '<tr>' +	
						'<td>'+data[i]['sel']+'</td>' +
		                '<td>'+data[i]['enterprise']+'</td>' +
		                '<td>'+data[i]['plant']+'</td>' +
						'<td>'+data[i]['rut']+'</td>' +
						'<td>'+data[i]['fullname']+'</td>' +
						'<td>'+data[i]['days_worked']+'</td>' +
						'<td>'+data[i]['days_seventh']+'</td>' +
						'<td>'+data[i]['days_license']+'</td>' +
						'<td>'+data[i]['days_abscent']+'</td>' +
						'<td>'+data[i]['rem_total']+'</td>' +
						'<td>'+data[i]['rem_advance']+'</td>' +
						'<td>'+data[i]['rem_topay']+'</td>' +
					'</tr>';

					

					if(i+1==data.length){
						$("#tablaRegistrosExcel").table2excel({
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
			});*/

			var count = 0;
			$('#tableData > tbody > tr').each(function() {
				if($(this).find('td').find(">:first-child").is(':checked')){
					var list = '<tr>' +	
		                '<td>'+$($(this).children()[1]).text()+'</td>' +
		                '<td>'+$($(this).children()[2]).text()+'</td>' +
		                '<td>'+$($($(this).children()[3]).children()[4]).text()+'</td>' +
		                '<td>'+$($(this).children()[4]).text()+'</td>' +
		                '<td>'+$($(this).children()[5]).text()+'</td>' +
		                '<td>'+$($(this).children()[6]).text()+'</td>' +
		                '<td>'+$($(this).children()[7]).text()+'</td>' +
		                '<td>'+$($(this).children()[8]).text()+'</td>' +
		                '<td>'+$($(this).children()[9]).text()+'</td>' +
		                '<td>'+$($(this).children()[10]).text()+'</td>' +
		                '<td>'+$($(this).children()[11]).text()+'</td>' +
					'</tr>';
					$("#tablaRegistrosExcel").append(list);
				}
				count++;
				if(count==$('#tableData > tbody > tr').length){
					$("#tablaRegistrosExcel").table2excel({
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
			});

		}

		function modalManual(year,month,plant,rut,fullrut,fullname,settlement){
			$("#lblManualYear").text(year);
			$("#lblManualMonth").text(month);
			$("#lblManualCostCenter").text(plant);
			$("#lblManualRUT").text(rut);
			$("#txtManualRUT").val(fullrut);
			$("#txtManualName").val(fullname);
			$("#lblManualSettlement").text(settlement);

			$("#modalManual").modal('show');

			$.post('../../phps/remuneration_Load.php', {
				type: 'oneManual',
				year: year,
				month: month,
				costCenter: plant,
				rut: rut,
				settlement: settlement
			}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);

				for(i=0;i<data.length;i++){
					var row = '<tr>' +
								'<td style="text-align: center;">'+data[i]['codhdrem']+'</td>' +
								'<td>'+data[i]['descriphdrem']+'</td>' +
								'<td>' +
									'<input class="numbersOnlyFloatMoney" value="'+data[i]['valrem2Manual']+'" style="text-align: right;"></input>' + 
									'<label>'+data[i]['linrem']+'</label>' +
								'</td>' +
							'</tr>';
					$("#tableManual"+data[i]['codhdrem'][0]+"Body").append(row);
					
					if(i+1==data.length){
						startParameters();
					}
				}

			});
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

		function loadPlant(){
			$.post('../../phps/plant_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					$("#listPlant").append('<option value="'+data[i]["Pl_codigo"]+'">'+data[i]["PlNombre"]+'</option>');
				}
				//$("#listPlant").val(98);
				$("#listPlant").selectpicker();
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
							//loadData();
						});
					}
				}
			});
		}

		function loadManualFull(){
			$.post('../../phps/remunerationManual_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				$("#listManualFull").append('<option value="0">SELECCIONE</option>');
				for(i=0;i<data.length;i++){
					$("#listManualFull").append('<option value="'+data[i]["codhdrem"]+'">'+data[i]["codhdrem"]+' - '+data[i]["descriphdrem"]+'</option>');
				}
				$("#listManualFull").val(0);
			});
		}

		function changeColorValues(){
			if($("#listPay").val()=='D026'){
				$(".classD026").css('color','blue');
				$(".classD029").css('color','#333333');
				$(".classH046").css('color','#333333');
			}else if($("#listPay").val()=='D029'){
				$(".classD026").css('color','#333333');
				$(".classD029").css('color','blue');
				$(".classH046").css('color','#333333');
			}else{
				$(".classD026").css('color','#333333');
				$(".classD029").css('color','#333333');
				$(".classH046").css('color','blue');
			}
		}


		function generatePDFLink(type,year,month,rut,costCenter,settlement){
			window.open("remuneration_pdf.php?type="+type+"&year="+year+"&month="+month+"&rut="+rut+"&costCenter="+costCenter+"&settlement="+settlement);
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
				<div class="panel-heading"><i class="fa fa-user"></i><i class="fa fa-money"></i>&nbsp;&nbsp; Liquidaciones</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<label style="font-size: 12px;">Empresa:</label>
				    	    <select id="listEnterprise" class="form-control input-sm">
							</select>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<label style="font-size: 12px;">Campo:</label>
				    	    <select id="listPlant" class="form-control input-sm" multiple>
							</select>
						</div>

						<div class="col-xs-12 col-sm-12 col-md-1 col-lg-1">
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
							<label style="font-size: 12px;">Estado contrato:</label>
				    	    <select id="listState" class="form-control input-sm">
		  						<option value="A">VIGENTE</option>
								<option value="F">FINIQUITADO</option>
								<!--<option value="T">TODOS</option>-->
							</select>
						</div>

						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<label style="font-size: 12px;">Pagar :</label>
				    	    <select id="listPay" class="form-control input-sm">
		  						<option value="H046">SUELDO</option>
								<option value="D026">ANTICIPO</option>
								<option value="D029">BONO</option>
								<option value="D026_D029">ANTICIPO+BONO</option>
							</select>
						</div>

						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<label style="font-size: 12px;">Cuenta:</label>
				    	    <select id="listAccount" class="form-control input-sm">
		  						<option value="banco">BANCO</option>
								<option value="servipag">SERVIPAG</option>
							</select>
						</div>

						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<br/>
							<button id="btnSelectAll" class="btn btn-primary btn-sm">Seleccionar Todo</button>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2" style="text-align: right;">
							<br/>
							<label style="font-size: 12px;">Incluir Pagos en 0</label>
							<input id="chkValues" type="checkbox" style="font-size: 12px;">
							<label style="font-size: 12px;">Cálculo de Liquidaciones</label>
							<input id="chkCalculate" type="checkbox" style="font-size: 12px;">
							<br/>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<br/>
							<button id="btnRefresh" class="btn btn-primary btn-sm"><i class="fa fa-refresh fa-lg fa-fw"></i>&nbsp;&nbsp;Recargar</button>
							<br/>
							<br/>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<br/>
							<button id="toExcel" class="btn btn-success btn-sm">Exportar a Excel  <img src="../../images/excel.ico"/></button>
							<br/>
							<br/>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<br/>
							<label class="servipagDate" style="display: inline-block; width:45%; visibility: hidden;">Fecha Pago</label>
							<input id="txtServipagDate" type="Name" class="servipagDate form-control datepickerTxt input-sm" style="display: inline-block; width:50%; visibility: hidden;">
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<br/>
							<button id="btnGenerateTxt" class="btn btn-primary btn-sm"><i class="fa fa-paypal fa-lg fa-fw"></i>&nbsp;&nbsp;Generar TXT Banco</button>
							<br/>
							<a href="remunerationList.php" target="blank">Ver nóminas</a>
						</div>

						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<div class="row">
								<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
									<br/>
								</div>
								<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
									<button id="btnManualFull" class="btn btn-primary btn-sm" disabled>Datos Manuales&nbsp;&nbsp;<i class="fa fa-edit fa-lg fa-fw"></i></button>
									<br/>
								</div>
								<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
									<button id="btnCalculateModal" class="btn btn-primary btn-sm">Calcular Liquidaciones&nbsp;&nbsp;<i class="fa fa-calculator fa-lg fa-fw"></i></button>
									<br/>
								</div>
								<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
									<button id="btnGenerateFullPDF" class="btn btn-danger btn-sm">Generar Liquid. Sel.&nbsp;&nbsp;<i class="fa fa-file-pdf-o fa-lg fa-fw"></i></button>
									<br/>
								</div>
							</div>	
						</div>
					</div>	
					<br/>
					<br/>
					<div class="table-responsive">
						<table id="tableData" class="table table-hover" style="font-size: 12px;">
						</table>
						<table id="tablaRegistrosExcel" style="display: none;">
						</table>
						<table id="tableDataExcelServipag" style="display: none;">
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

	<div id="modalDelete" class="modal fade" data-backdrop="static" style="z-index: 1051">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
	        	<div class="modal-body">
		    	    <p id="modal-delete-text"></p>
		    	    <p id="modal-delete-id" style="visibility: hidden"></p>
		      	</div>
		      	<div class="modal-footer">
		        	<button id="delete" type="button" class="btn btn-danger">Deshacer</button>
		        	<button id="modalDeleteHide" type="button" class="btn btn-primary">Cancelar</button>
		      	</div>
		    </div>
		</div>
	</div>

	<!--<div id="modalUndo" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
	        	<div class="modal-body">
		    	    <p id="modal-undo-text"></p>
		    	    <p id="modal-undo-id" style="visibility: hidden"></p>
		      	</div>
		      	<div class="modal-footer">
		        	<button id="undo" type="button" class="btn btn-danger">Deshacer Finiquito</button>
		        	<button id="modalUndoHide" type="button" class="btn btn-primary">Cancelar</button>
		      	</div>
		    </div>
		</div>
	</div>-->

	<div id="modalPDF" class="modal fade" data-backdrop="static" style="z-index: 1051">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
	        	<div class="modal-body">
	        		<input id="checkPDF" type="checkbox" /> Seleccionar
		    	    <label>Fecha a mostrar:</label>
						<div class="input-group">
						<input id="txtPDFDate" type="text" class="form-control datepickerTxt" disabled>
						<span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></span>
					</div>
		    	    <p id="modal-pdf-id" style="display: none;"></p>
		    	    <p id="modal-pdf-type" style="display: none;"></p>
		      	</div>
		      	<div class="modal-footer">
		        	<button id="generatePDFOK" type="button" class="btn btn-danger">Generar PDF</button>
		        	<button id="modalPDFHide" type="button" class="btn btn-primary">Cancelar</button>
		      	</div>
		    </div>
		</div>
	</div>

	<div id="modalManual" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-xl">
			<div class="modal-content">
	        	<div class="modal-body">
					<div class="panel panel-primary">
						<div class="panel-heading"><i class="fa fa-edit fa-lg fa-fw"></i>&nbsp;&nbsp; Modificar Datos Manuales</div>
						<div class="panel-body">
							<div class="container-fluid">
								<div class="row">
									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							  			<label>RUT:</label>
		  								<input id="txtManualRUT" type="Name" class="form-control"  style="text-align: right;" disabled>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
							  			<label>Nombre:</label>
							  			<input id="txtManualName" type="Name" class="form-control" disabled>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">&nbsp;<br/></div>
									<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6" style="text-align: center;">
										<label>HABERES</label>
										<table class="table" style="font-size: 12px;">
											<thead>
												<tr>
													<th>Código</th>
													<th>Descripción</th>
													<th>Valor</th>
												</tr>
											</thead>
											<tbody id="tableManualHBody">
											</tbody>
										</table>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6" style="text-align: center;">
										<label>DESCUENTOS</label>
										<table class="table" style="font-size: 12px;">
											<thead>
												<tr>
													<th>Código</th>
													<th>Descripción</th>
													<th>Valor</th>
												</tr>
											</thead>
											<tbody id="tableManualDBody">
											</tbody>
										</table>
									</div>
									<div id="divID" class="col-xs-4 col-sm-4 col-md-4 col-lg-4" style="display: none;">
							  			<label id="lblManualYear"></label>
							  			<label id="lblManualMonth"></label>
							  			<label id="lblManualCostCenter"></label>
							  			<label id="lblManualRUT"></label>
							  			<label id="lblManualSettlement" style="display: none;"></label>
									</div>
								</div>
								<br/>
								<div style="text-align:right;">
									<div style="display:inline-block;"><button id="btnModalManualSave" class="btn btn-success"><span class="glyphicon glyphicon-save" aria-hidden="true"></span>&nbsp;&nbsp; Almacenar</button></div>
									<div style="display:inline-block;"><button id="btnModalManualHide" class="btn btn-primary"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span>&nbsp;&nbsp; Salir</button></div>
								</div>
							</div>
						</div>
					</div>
		      	</div>
		    </div>
		</div>
	</div>

	<div id="modalManualFull" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
	        	<div class="modal-body">
					<div class="panel panel-primary">
						<div class="panel-heading"><i class="fa fa-edit fa-lg fa-fw"></i>&nbsp;&nbsp; Modificar Datos Manuales</div>
						<div class="panel-body">
							<div class="container-fluid">
								<div class="row">
									<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
							  			<label style="font-size: 12px;">Valor a modificar:</label>
							    	    <select id="listManualFull" class="form-control input-sm">
										</select>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
										<br/>
							  			<button id="btnManualRefresh" class="btn btn-primary"><i class="fa fa-refresh fa-lg fa-fw"></i>&nbsp;&nbsp;Recargar</button>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
										<label id="lblManualFull"></label>
										&nbsp;<br/>
									</div>
									
									<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="text-align: center;">
										<table class="table" style="font-size: 12px;">
											<thead>
												<tr>
													<th style="display: none;">RUT</th>
													<th style="display: none;">Centro de Costo</th>
													<th>RUT</th>
													<th>Nombre</th>
													<th>Valor</th>
												</tr>
											</thead>
											<tbody id="tableManualFullBody">
											</tbody>
										</table>
									</div>
									<div id="divID" class="col-xs-4 col-sm-4 col-md-4 col-lg-4" style="display: none;">
							  			<label id="lblManualFullYear"></label>
							  			<label id="lblManualFullMonth"></label>
									</div>
								</div>
								<br/>
								<div style="text-align:right;">
									<div style="display:inline-block;"><button id="btnManualFullSave" class="btn btn-success"><span class="glyphicon glyphicon-save" aria-hidden="true"></span>&nbsp;&nbsp; Almacenar</button></div>
									<div style="display:inline-block;"><button id="btnManualFullHide" class="btn btn-primary"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span>&nbsp;&nbsp; Salir</button></div>
								</div>
							</div>
						</div>
					</div>
		      	</div>
		    </div>
		</div>
	</div>

	<div id="modalCalculate" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-xl">
			<div class="modal-content">
	        	<div class="modal-body">
					<div class="panel panel-primary">
						<div class="panel-heading"><i class="fa fa-calculator fa-lg fa-fw"></i>&nbsp;&nbsp; Calcular</div>
						<div class="panel-body">
							<div class="container-fluid">
								<div class="row">
									<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="text-align: center;">
							  			<label id="lblCalculateQuantity"></label>
							  			<label id="lblCalculateQuantityTotal" style="display: none;"></label>
							  		</div>
									<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="text-align: center;">
							  			<button id="btnCalculate" class="btn btn-success">
							  				Calcular&nbsp;&nbsp;
							  				<i class="fa fa-calculator fa-lg fa-fw"></i>
							  				<i id="btnCalculateSpinner" class="fa fa-spinner fa-spin fa-lg" style="display: none;"></i>
							  			</button>
							  			<br/><br/>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="text-align: center;">
							  			<div class="progress">
											<div id="pbCalculate" class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
										    	<!--<span class="sr-only">45% Complete</span>-->
											</div>
										</div>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="text-align: center;">
							  			<button id="btnCalculateCancel" class="btn btn-danger" style="display: none;" title="Cancelar el proceso dejará en 0 las liquidaciones no calculadas">
							  				Cancelar&nbsp;&nbsp;
							  				<i class="fa fa-times-circle fa-lg fa-fw"></i>
							  			</button>
							  			<br/><br/>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
										<div id="divCalculate" class="row" style="font-size: 11px;">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
		        	<button id="btnCalculateHide" type="button" class="btn btn-primary">Salir</button>
		      	</div>
			</div>
		</div>
	</div>

	<div id="modalProgressCancel" class="modal fade" data-backdrop="static" style="z-index: 2000">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
	        	<div class="modal-body">
					<div style="text-align: center;">
						<span id="modalProgress-text"><i class="fa fa-spinner fa-spin fa-2x"></i><br/>Cancelando...</span>
					</div>
		      	</div>
		    </div>
		</div>
	</div>

</body>
</html>