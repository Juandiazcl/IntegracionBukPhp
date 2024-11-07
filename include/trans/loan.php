<?php
header('Content-Type: text/html; charset=utf8'); 
session_start();

if(!isset($_SESSION['userId'])){
	header('Location: ../../login.php');
}elseif($_SESSION['display']['loan']['view']!=''){
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
	<link rel="stylesheet" type="text/css" href="../../libs/bootstrap/css/bootstrap-redto.css">
	<link rel="stylesheet" type="text/css" href="../../style/style.css">
	<link rel="stylesheet" href="../../libs/font-awesome-4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="../../libs/datepicker/css/datepicker.css">
	<script type="text/javascript" src="../../libs/moment.js"></script>
	<script type="text/javascript" src="../../libs/loadParameters.js"></script>
	<script type="text/javascript" src="../../libs/jquery.mask.js"></script>
	<link rel="stylesheet" type="text/css" href="../../libs/datatables/datatables.min.css"/>
 	<script type="text/javascript" src="../../libs/datatables/datatables.min.js"></script>
	<script type="text/javascript" src="../../libs/jquery.table2excel.js"></script>	
	<title></title>
	<script type="text/javascript">

	var idList = 1, calculation = false;

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

			/*$(".datepickerTxt").focusout(function(){
				$(".datepickerTxt").datepicker('hide');
			});*/

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

			$("#toExcelHistory").click(function() {
				$("#tableHistoryExcel").table2excel({
					exclude: ".noExl",
					name: "Excel Document Name",
					filename: "Lista",
					fileext: ".xls",
					exclude_img: true,
					exclude_links: true,
					exclude_inputs: true
				});
			});
	
			$("#modalHide").click(function() {
				$("#modal").modal('hide');	
			});

			$("#cancel").click(function() {
				cleanModal();
			});

			$("#cancelLoan").click(function() {
				cleanLoan();
			});

			$("#cancelView").click(function() {
				$("#modalView").modal('hide');
				$("#txtViewRUT").val('');
				$("#txtViewName").val('');
				$("#tableHistory").html('<thead><tr><th>Empresa</th><th>Campo</th><th>Sueldo Base</th><th>Inicio Contrato</th><th>Préstamos activos</th><th>Causa</th><th>Ver Ficha</th></tr></thead><tbody></tbody>');
				cleanLoan();
				$("#panelRedo").css('display','none');
				$('.loanClass').removeAttr('disabled');
			});

			$("#toExcelAll").click(function() {
				toExcelAll();
			});

			$("#toPDFAll").click(function() {
				toPDFAll();
			});

			$("#btnSaveMonthDues").click(function() {
				$.post('../../phps/loan_Load.php', {
					type: 'getActualMonth'
				}, function(data, textStatus, xhr) {
					var data = JSON.parse(data);
					$("#listDuesMonth").val(data[0]['month']);
					$("#listDuesYear").val(data[0]['year']);
					$("#modalPayDues").modal('show');
				});	
			});

			$("#btnDelete").click(function() {
				$.post('../../phps/loan_Save.php', {
					type: 'delete',
					id: $("#modal-delete-id").text()
				}, function(data, textStatus, xhr) {
					if(data=='OK'){
						$("#modal-text").text("Préstamo Eliminado");
						$("#modal").modal('show');
						var rut = $("#txtViewRUT").val().split('-');
						viewRow(rut[0],$("#txtViewRUT").val(),$("#txtViewName").val());

					}else{
						$("#modal-text").text("Error");
						$("#modal").modal('show');
					}
					$("#modalDelete").modal('hide');
				});	
			});

			$("#modalDeleteHide").click(function() {
				$("#modalDelete").modal('hide');	
			});

			$("#modalPDFHide").click(function() {
				$("#txtPDFDate").datepicker('setValue', '');
				$("#checkPDF").prop('checked', false);
				$("#modalPDF").modal('hide');	
			});

			$("#generatePDFOK").click(function(){
				toPDFMandate($("#modal-pdf-id").text(), $("#modal-pdf-rut").text(), $("#modal-pdf-name").text(), $("#modal-pdf-type").text(), $("#txtPDFDate").val());
				$("#txtPDFDate").datepicker('setValue', '');
				$("#checkPDF").prop('checked', false);
				$("#modalPDF").modal('hide');	
			});

			$("#payDues").click(function() {
				$("#modalProgress").modal('show');
				$.post('../../phps/loan_Save.php', {type: 'payAllDues',
					month: $("#listDuesMonth").val(),
					year: $("#listDuesYear").val()
				}, function(data, textStatus, xhr) {
					if(data=='OK'){
						$("#modalProgress").modal('hide');
						$("#modal-text").text("Almacenado");
						$("#modal").modal('show');
					}else{
						$("#modalProgress").modal('hide');
						$("#modal-text").text("Error");
						$("#modal").modal('show');
					}
					$("#modalPayDues").modal('hide');
				});			
			});			

			$("#modalPayDuesHide").click(function() {
				$("#modalPayDues").modal('hide');
			});

			$("#listState").change(function(){
				loadData();
			});
			$("#listPlant").change(function(){
				loadData();
			});
			$("#listEnterprise").change(function(){
				loadData();
			});
			$("#listLoanState").change(function(){
				loadData();
			});

			$("#txtLoanDues").change(function(){
				if($(this).val()!=""){
					var dateStart = $('#txtLoanStart').val().split('/');
					var start = moment([dateStart[2],dateStart[1]-1,1]);
					$("#txtLoanEnd").val(start.add($("#txtLoanDues").val()-1, 'months').format('DD/MM/YYYY'));
				}
			});

			$("#txtRedoLoanDues").change(function(){
				if($(this).val()!=""){
					var dateStart = $('#txtRedoLoanStart').val().split('/');
					var start = moment([dateStart[2],dateStart[1]-1,1]);
					$("#txtRedoLoanEnd").val(start.add($("#txtRedoLoanDues").val()-1, 'months').format('DD/MM/YYYY'));
				}
			});

			$(".loanCalculate").change(function(){
				calculate();
			});

			$(".loanCalculate").on('changeDate', function(ev) {
				calculate();
			});

			$(".redoLoanCalculate").change(function(){
				calculateRedo();
			});

			$(".redoLoanCalculate").on('changeDate', function(ev) {
				calculateRedo();
			});

			$("#btnNewLoan").click(function() {
				$(".loanClass").removeAttr('disabled');
				$('.loanButton').attr('disabled','disabled');
				$("#btnNewLoan").attr('disabled','disabled');
				$("#btnNewLoanUnify").attr('disabled','disabled');
			});

			$("#btnRedoLoan").click(function() {
				$('#tableDues > tbody > tr').each(function() {
					if($($(this).children()[4]).html()=="IMPAGO"){
						$('#txtRedoLoanStart').val('01/'+$($(this).children()[2]).html());
						$('#txtRedoLoanEnd').val('01/'+$($(this).children()[2]).html());
						return false;
					}
				});

				$("#panelRedo").css('display','block');
				$('.loanClass').attr('disabled','disabled');
				calculateRedo();
			});

			$("#btnRedoLoanCancel").click(function() {
				cleanRedoLoan();
			});

			$("#btnSaveLoan").click(function() {
				if($("#txtLoanValue").prop('disabled')==true){
					$("#modalUnifySave").modal("show");
				}else{

					var totalIrregular = 0;
					var countRegular = 0;
					var i = 0;
					$('#tableDues > tbody > tr').each(function() {
						if($($(this).children()[0]).children().first().is(':checked')){
							totalIrregular += parseInt($($(this).children()[4]).children().first().val());
						}else{
							countRegular++;
						}
						i++;

						if(i==$('#tableDues > tbody > tr').length){
							var totalRegular =  parseInt($("#txtLoanValue").val())-totalIrregular;
							if(totalRegular<0){
								$("#modal-text").text("Los montos editados superan el total del préstamo, favor verificar");
								$("#modal").modal('show');
								return;
							}else{
								saveDues();
							}
						}
					});
				}
			});

			$("#btnRedoLoanSave").click(function() {
				saveDues();
				cleanRedoLoan();
			});

			$("#btnUnifySave").click(function() {
				saveDues();
				$("#modalUnifySave").modal("hide");
			});

			$("#modalUnifySaveHide").click(function() {
				$("#modalUnifySave").modal("hide");
			});

			$("#btnNewLoanUnify").click(function() {
				if($(this).html()=='<i class="fa fa-gg fa-lg fa-fw"></i>&nbsp;&nbsp;Unificar Préstamos'){
					$("#btnCancelLoanUnify").css('visibility','visible');
					$("#btnNewLoanUnify").html('<i class="fa fa-gg-circle fa-lg fa-fw"></i>&nbsp;&nbsp;Unificar');
					$(".loanButton").attr('disabled','disabled');
					$("#btnNewLoan").attr('disabled','disabled');
					$(".checkUnify").css('display','block');
				}else{
					var balanceTotal = 0;
					var countLoan = 0;
					$('#tableLoans > tbody > tr').each(function() {
						if($($(this).children()[0]).children().length>0){
							if($($(this).children()[0]).children().first().is(':checked')){
								balanceTotal += parseInt($($(this).children()[7]).html());
								countLoan++;
							}
						}
					});
					if(countLoan>1){
						$("#modal-unify-text").text("Saldo a unificar: "+balanceTotal);
						$("#modalUnify").modal('show');
					}else{
						$("#modal-text").text("Debe seleccionar al menos 2 préstamos");
						$("#modal").modal('show');
					}
				}
			});
			$("#btnCancelLoanUnify").click(function() {
				$("#btnCancelLoanUnify").css('visibility','hidden');
				$("#btnNewLoanUnify").html('<i class="fa fa-gg fa-lg fa-fw"></i>&nbsp;&nbsp;Unificar Préstamos');
				$(".loanButton").removeAttr('disabled');
				$("#btnNewLoan").removeAttr('disabled');
				$(".checkUnify").css('display','none');
				$(".checkboxUnify").prop('checked', false);
			});

			$("#modalUnifyHide").click(function() {
				$("#modalUnify").modal('hide');	
			});

			$("#btnUnify").click(function() {
				$("#modalUnify").modal('hide');	
				$(".loanClass").removeAttr('disabled');
				$('.loanButton').attr('disabled','disabled');
				$("#btnNewLoan").attr('disabled','disabled');
				$("#btnNewLoanUnify").attr('disabled','disabled');
				$("#btnCancelLoanUnify").css('visibility','hidden');
				var balance = $("#modal-unify-text").text().split(": ");
				$("#txtLoanValue").val(balance[1]);
				$("#txtLoanValue").attr('disabled','disabled');
				$(".checkboxUnify").attr('disabled','disabled');
			});

			$("#listType").change(function() {
				if($(this).val()=="A_CUENTA"){
					$("#txtLoanDues").attr('disabled','disabled');
					$("#txtLoanDues").val(1);
				}else{
					$("#txtLoanDues").removeAttr('disabled');
				}
			});

			$("#btnAddPayment").click(function() {
				$.post('../../phps/loan_Load.php', {type: "oneLoan", id: $("#labelID").text()}, function(data, textStatus, xhr) {
					var data = JSON.parse(data);
					if(data[0]["Estado"]=='PAGADO'){
						$("#modal-text").text("No puede agregar más abonos a un préstamo ya pagado");
						$("#modal").modal('show');
					}else{
						var number = $("#tablePaymentBody > tr").length + 1;
						$("#tablePaymentBody").append('<tr>' +
														'<td>'+number+'</td>' +
														'<td><input class="form-control datepickerTxt" value="'+moment().format('DD/MM/YYYY')+'"/></td>' +
														'<td><input type="Number" class="form-control"/></td>' +
														'<td><button class="btn btn-success" title="Almacenar Abono" onclick="savePayment(this)"><i class="fa fa-save fa-lg fa-fw"></i></button></td>' +
													'</tr>');
						$(".datepickerTxt").datepicker({
							format: 'dd/mm/yyyy',
							weekStart: 1
						});
					}
				});
			});

			$("#btnDueAddSave").click(function() {
				var id = $("#modal-due-id").text();
				var amount = $("#txtDueAdd").val();
				if(amount==""){
					amount = 0;
				}else if(amount<0){
					amount = 0;
				}

				$.post('../../phps/loan_Save.php', {
					type: "update_due_add", 
					id: id,
					amount: amount
				}, function(data, textStatus, xhr) {
					//var data = data.split('-');
					if(data=='OK'){
						$("#txtDueAdd").val('');
						$("#modal-due-id").text('');
						$("#modalDueAdd").modal('hide');
						
						$("#modal-text").text("Abono Almacenado");
						$("#modal").modal('show');
						loadDues($("#labelID").text());
					}else if(data=='Mayor'){
						$("#modal-text").text("Abono no puede ser mayor al valor de la cuota");
						//$("#modal-text").text("Abono no puede ser igual o mayor al valor de la cuota");
						$("#modal").modal('show');
					}
				});
			});

			$("#btnDueAddCancel").click(function() {
				$("#txtDueAdd").val('');
				$("#modal-due-id").text('');
				$("#modalDueAdd").modal('hide');
			});

			loadPlant();
			loadEnterprise();
			loadData();
		});

		function loadData(){
			$("#modalProgress").modal('show');
			$("#tableData").html('<thead><tr>' +
				'<th>Empresa</th>' +
				'<th>Campo</th>' +
				'<th>RUT</th>' +
				'<th>Nombre</th>' +
				'<th>Inicio</th>' +
				'<th>Préstamos activos</th>' +
				'<th>Ver</th>' +
				'</tr></thead><tbody id="tableDataBody"></tbody>');			

			var plant = 98;
			if($("#listPlant").val()!=null){
				plant = $("#listPlant").val();
			}

			$('#tableData').dataTable({
				destroy: true,
				paging: false,
				language: { "url": "../../libs/datatables/language/Spanish.json"},
                ajax: {
		            "url": "../../phps/loan_Load.php",
		            "type": "POST",
		            "data": {
		            	type: "all", 
		            	state: $("#listState").val(), 
		            	plant: plant, 
		            	enterprise: $("#listEnterprise").val(), 
		            	loanState: $("#listLoanState").val()
					},
		            "dataSrc": ""
		        },
                columns: [
	                {"data" : "enterprise"},
	                {"data" : "plant"},
					{"data" : "rut"},
					{"data" : "fullname"},
					{"data" : "contractStart"},
					{"data" : "loans"},
					{"data" : "view"}
                ],
                "fnInitComplete": function(oSettings, json) {
					$("#modalProgress").modal('hide');
			    }
            });
		}

		function viewRow(id, rut, name){
			loadPeriod(id);
			loadRow(id, rut, name);
		}

		function loadRow(id, rut, name){

			$("#modalView").modal('show');
			$.post('../../phps/loan_Load.php', {type: "one", id: id, period: $("#listPeriod").val()}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				$('#txtViewRUT').val(rut);
				$('#txtViewName').val(name);

				$("#tableLoans").html('<thead><tr><th style="display: none;" class="checkUnify">Sel.</th><th>ID</th><th>Tipo</th><th>Fecha Inicio</th><th>Fecha Fin</th><th>Cuotas</th><th>Valor Total</th><th>Saldo</th><th>Estado</th><th>Editar</th><th>Eliminar</th><th>Excel</th><th>PDF</th><th>Mandato</th></tr></thead><tbody></tbody>');
				for(i=0;i<data.length;i++){
					
					var list = '<tr id="loan'+data[i]['ID']+'" class="loanRow">';
					if(data[i]['Estado']=='IMPAGO'){
						list += '<td style="display: none;" class="checkUnify"><input class="checkboxUnify" type="checkbox"/></td>';
					}else{
						list += '<td style="display: none;" class="checkUnify"></td>';
					}
					list += '<td>'+data[i]['ID']+'</td>';
					var tipo = "";
					if(data[i]['Tipo']=='CUOTAS_AUTO'){
						tipo = 'Cuotas';
					}else{
						tipo = 'A Cuenta';
					}
					list += '<td>'+tipo+'</td>';
					list += '<td>'+data[i]['FechaInicio']+'</td>';
					list += '<td>'+data[i]['FechaFin']+'</td>';
					list += '<td>'+data[i]['Cuotas_Totales']+'</td>';
					list += '<td style="text-align: right;">'+data[i]['Valor_Total']+'</td>';
					if(data[i]['Saldo']==null || data[i]['Estado']=='UNIFICADO'){
						list += '<td style="text-align: right;">0</td>';
					}else{
						list += '<td style="text-align: right;">'+parseInt(data[i]['Saldo'])+'</td>';
					}
					list += '<td>'+data[i]['Estado']+'</td>';
					list += '<td><button class="btn btn-warning loanButton" onclick="editLoan('+data[i]['ID']+',\''+data[i]['FechaInicio']+'\',\''+data[i]['FechaFin']+'\',\''+data[i]['Cuotas_Totales']+'\',\''+data[i]['Valor_Total']+'\',\''+data[i]['Tipo']+'\')"><i class="fa fa-folder-open-o fa-lg fa-fw"></i></button></td>';
					list += '<td><button class="btn btn-danger loanButton" onclick="deleteLoan('+data[i]['ID']+')"><i class="fa fa-remove fa-lg fa-fw"></i></button></td>';
					list += '<td><button class="btn btn-success loanButton" onclick="toExcelOne('+data[i]['ID']+',\''+rut+'\',\''+name+'\',\''+tipo+'\')"><i class="fa fa-file-excel-o fa-lg fa-fw"></i></button></td>';
					list += '<td><button class="btn btn-danger loanButton" onclick="toPDFOne('+data[i]['ID']+',\''+rut+'\',\''+name+'\',\''+tipo+'\')"><i class="fa fa-file-pdf-o fa-lg fa-fw"></i></button></td>';
					list += '<td><button class="btn btn-primary loanButton" onclick="generatePDF('+data[i]['ID']+',\''+rut+'\',\''+name+'\',\''+tipo+'\')"><i class="fa fa-file-pdf-o fa-lg fa-fw"></i></button></td></tr>';
					$("#tableLoans").append(list);

				}

			});
		}

		function editLoan(id, startDate, endDate, dues, value, type){
			$('.loanClass').removeAttr('disabled');
			$('#btnRedoLoan').removeAttr('disabled');
			$('#btnNewLoan').attr('disabled','disabled');
			$('#listType').val(type);
			$('#txtLoanStart').val(startDate);
			$('#txtLoanEnd').val(endDate);
			$('#txtLoanDues').val(dues);
			$('#txtLoanValue').val(value);

			$('#loan'+id).addClass('success');
			$('.loanButton').attr('disabled','disabled');
			$('#btnNewLoanUnify').attr('disabled','disabled');

			$("#divID").css('visibility','visible');
			$("#labelID").text(id);
			loadDues(id);

			if(type=='A_CUENTA'){
				loadPayments(id);
			}
		}

		function deleteLoan(id){
			$.post('../../phps/loan_Save.php', {
					type: 'deleteVerify',
					id: id
			}, function(data, textStatus, xhr) {
				if(data==0){
					$("#modal-delete-id").text(id);
					$("#modalDelete").modal('show');
				}else{
					$("#modal-text").text("No puede eliminar un préstamo con pagos ya realizados");
					$("#modal").modal('show');
				}
			});	
		}

		function saveDues(){
			var type="update", id=0;
			if($("#divID").css('visibility')=='hidden'){
				type='save';
			}else{
				id=$("#labelID").text();	
			}
			
			var listUnify = "";
			if($("#txtLoanValue").prop('disabled')==true){
				$('#tableLoans > tbody > tr').each(function() {
					if($($(this).children()[0]).children().length>0){
						if($($(this).children()[0]).children().first().is(':checked')){
							listUnify += $($(this).children()[1]).html()+"&&";
						}
					}
				});
			}

			var stateOk = true;
			var duesList = "";
			var countDues = 0;
			var lastDate = "";
			var meter = 0;
			if($("#listType").val()=='CUOTAS_AUTO'){
				if(type=='save'){
					$('#tableDues > tbody > tr').each(function() {
						meter = 0;
						if($.isNumeric($($($(this).children()[4]).children()[0]).val())){
							$($(this).children()[4]).html($($($(this).children()[4]).children()[0]).val());
						}

						$(this).find('td').each(function() {
							if(meter>0){
								var cellValue = $(this).html(); //Valor de cada celda
								duesList += cellValue;
								if(meter==4){
									if(cellValue==0){
										stateOk = false;
									}
								}
								if(meter==3){
									lastDate = '01/'+cellValue;
								}
								if(meter<6){
									duesList += "&&";	
								}
							}
							meter++;
						});
						countDues++;
						duesList += "&&&&";
					});
				}else{
					$('#tableDues > tbody > tr').each(function() {
						meter = 0;
						if($.isNumeric($($($(this).children()[4]).children()[0]).val())){
							$($(this).children()[4]).html($($($(this).children()[4]).children()[0]).val());
						}
						
						//Se revisa cada celda de cada cuota; se utilizarán distintos índices de celda dependiendo de si la cuota está ya pagada o no
						if($.isNumeric($($(this).children()[0]).text())){
							$(this).find('td').each(function() {
								var cellValue = $(this).html(); //Valor de cada celda
								duesList += cellValue;
								if(meter==3){
									if(cellValue==0){
										stateOk = false;
									}
								}
								if(meter==2){
									lastDate = '01/'+cellValue;
								}
								if(meter<5){
									duesList += "&&";
								}
								meter++;
							});
						}else{
							$(this).find('td').each(function() {
								if(meter>0){
									var cellValue = $(this).html(); //Valor de cada celda
									duesList += cellValue;
									if(meter==4){
										if(cellValue==0){
											stateOk = false;
										}
									}
									if(meter==3){
										lastDate = '01/'+cellValue;
									}
									if(meter<6){
										duesList += "&&";	
									}
								}
								meter++;
							});
						}
						countDues++;
						duesList += "&&&&";
					});
				}
			}else{ //Casos pago a cuenta
				$('#tableDues > tbody > tr').each(function() {
					meter = 0;
					if($.isNumeric($($($(this).children()[3]).children()[0]).val())){
						$($(this).children()[3]).html($($($(this).children()[3]).children()[0]).val());
					}

					$(this).find('td').each(function() {
						var cellValue = $(this).html(); //Valor de cada celda
						duesList += cellValue;
						if(meter==3){
							if(cellValue==0){
								stateOk = false;
							}
						}
						if(meter==2){
							lastDate = '01/'+cellValue;
						}
						if(meter<5){
							duesList += "&&";	
						}
						meter++;
					});
					countDues++;
					duesList += "&&&&";
				});
			}

			var rut = $("#txtViewRUT").val().split('-');
			if(stateOk){
				if(countDues>0){
					$.post('../../phps/loan_Save.php', {type: type,
						id: id,
						RUT: rut[0],
						Tipo: $("#listType").val(),
						Fecha_Inicio: $("#txtLoanStart").val(),
						Fecha_Fin: lastDate,
						Cuotas: countDues,
						Valor_Total: $("#txtLoanValue").val(),
						duesList: duesList,
						unify: $("#txtLoanValue").prop('disabled'),
						listUnify: listUnify
					}, function(data, textStatus, xhr) {
						$("#txtLoanDues").val(countDues);
						$("#txtLoanEnd").val(lastDate);
						console.log(data);
						if($.isNumeric(data)){
							//loadData();
							viewRow(rut[0],$("#txtViewRUT").val(),$("#txtViewName").val());
							
							loadDues(data);
							$("#divID").css('visibility','visible');
							$("#labelID").text(data);
							$("#btnRedoLoan").removeAttr('disabled');
							$("#modal-text").text("Almacenado");
							$("#modal").modal('show');
							//cleanModal();

						}else{
							$("#modal-text").text("Error");
							$("#modal").modal('show');
						}
					});
				}else{
					$("#modal-text").text("No puede almacenar préstamo vacío");
					$("#modal").modal('show');
				}
			}else{
				$("#modal-text").text("No puede almacenar cuotas en 0");
				$("#modal").modal('show');
			}
		}

		function loadDues(id){
			$.post('../../phps/loan_Load.php', {type: "dues", id: id, period: $("#listPeriod").val()}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				$("#tableDues").html('<thead><tr><th>Número</th><th>Tipo</th><th>Fecha</th><th>Monto</th><th>Estado</th><th></th><th></th></tr></thead><tbody></tbody>');
				var list = "";
				for(i=0;i<data.length;i++){
					list = '<tr>';
					list += '<td>'+data[i]['Numero']+'</td>';
					list += '<td>'+data[i]['Tipo']+'</td>';
					if(data[i]['Fecha1'][2]=='/'){
						list += '<td>'+data[i]['Fecha1']+'</td>';
					}else{
						list += '<td>'+data[i]['Fecha1'].replace('-','/')+'</td>';
					}
					list += '<td>'+data[i]['Monto']+'</td>';
					list += '<td>'+data[i]['Estado']+'</td>';
					if(data[i]['Estado']=='IMPAGO'){
						list += '<td><button id="due'+data[i]['ID']+'" class="btn btn-danger" title="Pagar" onclick="payDue('+data[i]['ID']+',\'PAGADO\')"><i class="fa fa-remove fa-lg fa-fw"></i></button></td>';
					}else if(data[i]['Estado']=='PAGADO'){
						list += '<td><button id="due'+data[i]['ID']+'" class="btn btn-success" title="Deshacer Pago" onclick="payDue('+data[i]['ID']+',\'IMPAGO\')" '+data[i]['status']+'><i class="fa fa-check fa-lg fa-fw"></i></button></td>';
					}else if(data[i]['Estado']=='ABONADO'){
						list += '<td><button id="due'+data[i]['ID']+'" class="btn btn-warning" title="Pagar" onclick="payDue('+data[i]['ID']+',\'PAGADO\')" ><i class="fa fa-check fa-lg fa-fw"></i></button></td>';
					}else{
						list += '<td><button id="due'+data[i]['ID']+'" class="btn btn-primary" title="Unificado" disabled><i class="fa fa-gg fa-lg fa-fw"></i></button></td>';
					}

					if(data[i]['Estado']=='PAGADO'){ //Caso abono cuota
						list += '<td></td>';
					}else{
						list += '<td><button id="dueAdd'+data[i]['ID']+'" class="btn btn-primary" title="Abonar" onclick="showModalDueAdd('+data[i]['ID']+',\'ABONADO\')">Abonar</button></td>';
					}
					list += '</tr>';
					$("#tableDues").append(list);
				}
				$(".loanButton").attr('disabled','disabled');

			});
		}

		function payDue(id, state){

			$.post('../../phps/loan_Save.php', {type: "update_due", id: id, Estado: state}, function(data, textStatus, xhr) {
				var data = data.split('-');
				if($.isNumeric(data[0])){
					if(state=='IMPAGO'){
						$("#due"+id).removeClass('btn-success');
						$("#due"+id).addClass('btn-danger');
						$("#due"+id).attr('onclick','payDue('+id+',\'PAGADO\')');
						$("#due"+id).attr('title','Pagar');
						$("#due"+id).html('<i class="fa fa-remove fa-lg fa-fw"></i>');
						//$($("#due"+id).parent().parent().children()[0]).html('<td style="display: none;" class="checkUnify"><input type="checkbox"/></td>');
						$($("#due"+id).parent().parent().children()[4]).html('IMPAGO');
						$($("#loan"+$("#labelID").text()).children()[8]).html(data[1]);
						if(data[1]=="PAGADO"){
							$($("#loan"+$("#labelID").text()).children()[0]).html('<td style="display: none;" class="checkUnify"></td>');
						}else{
							$($("#loan"+$("#labelID").text()).children()[0]).html('<td style="display: none;" class="checkUnify"><input type="checkbox"/></td>');
						}
						$("#modal-text").text("Pago deshecho");
						$("#modal").modal('show');

					}else if(state=='ABONADO'){
						$("#due"+id).removeClass('btn-danger');
						$("#due"+id).addClass('btn-warning');
						$("#due"+id).attr('onclick','payDue('+id+',\'PAGADO\')');
						$("#due"+id).attr('title','Pagar');
						$("#due"+id).html('<i class="fa fa-remove fa-lg fa-fw"></i>');
						//$($("#due"+id).parent().parent().children()[0]).html('<td style="display: none;" class="checkUnify"><input type="checkbox"/></td>');
						$($("#due"+id).parent().parent().children()[4]).html('IMPAGO');
						$($("#loan"+$("#labelID").text()).children()[8]).html(data[1]);
						if(data[1]=="PAGADO"){
							$($("#loan"+$("#labelID").text()).children()[0]).html('<td style="display: none;" class="checkUnify"></td>');
						}else{
							$($("#loan"+$("#labelID").text()).children()[0]).html('<td style="display: none;" class="checkUnify"><input type="checkbox"/></td>');
						}
						$("#modal-text").text("Pago abonado");
						$("#modal").modal('show');

					}else{
						$("#due"+id).removeClass('btn-danger');
						$("#due"+id).addClass('btn-success');
						$("#due"+id).attr('onclick','payDue('+id+',\'IMPAGO\')');
						$("#due"+id).attr('title','Deshacer Pago');
						$("#due"+id).html('<i class="fa fa-check fa-lg fa-fw"></i>');
						//$($("#due"+id).parent().parent().children()[0]).html('<td style="display: none;" class="checkUnify"></td>');
						$($("#due"+id).parent().parent().children()[4]).html('PAGADO');
						$($("#due"+id).parent().parent().children()[6]).html('');
						$($("#loan"+$("#labelID").text()).children()[8]).html(data[1]);
						if(data[1]=="PAGADO"){
							$($("#loan"+$("#labelID").text()).children()[0]).html('<td style="display: none;" class="checkUnify"></td>');
						}else{
							$($("#loan"+$("#labelID").text()).children()[0]).html('<td style="display: none;" class="checkUnify"><input type="checkbox"/></td>');
						}
						$("#modal-text").text("Pago almacenado");
						$("#modal").modal('show');
					}
				}else{
					$("#modal-text").text("Error");
					$("#modal").modal('show');
				}
			});

		}

		function showModalDueAdd(id){
			$("#modal-due-id").text(id);
			$.post('../../phps/loan_Load.php', {type: "oneDue", id: id}, function(data, textStatus, xhr) {
				if(data!=0){
					var data = JSON.parse(data);
					if($.isNumeric(data[0]['Abono'])){
						$("#txtDueAdd").val(data[0]['Abono']);
					}
				}
			});
			$("#modalDueAdd").modal('show');
		}

		function payDueAdd(id, state, amount){

			$.post('../../phps/loan_Save.php', {type: "update_due_add", id: id, Estado: state, amount: amount}, function(data, textStatus, xhr) {
				var data = data.split('-');
				if($.isNumeric(data[0])){
					if(state=='IMPAGO'){
						$("#due"+id).removeClass('btn-success');
						$("#due"+id).addClass('btn-danger');
						$("#due"+id).attr('onclick','payDue('+id+',\'PAGADO\')');
						$("#due"+id).attr('title','Pagar');
						$("#due"+id).html('<i class="fa fa-remove fa-lg fa-fw"></i>');
						//$($("#due"+id).parent().parent().children()[0]).html('<td style="display: none;" class="checkUnify"><input type="checkbox"/></td>');
						$($("#due"+id).parent().parent().children()[4]).html('IMPAGO');
						$($("#loan"+$("#labelID").text()).children()[8]).html(data[1]);
						if(data[1]=="PAGADO"){
							$($("#loan"+$("#labelID").text()).children()[0]).html('<td style="display: none;" class="checkUnify"></td>');
						}else{
							$($("#loan"+$("#labelID").text()).children()[0]).html('<td style="display: none;" class="checkUnify"><input type="checkbox"/></td>');
						}
						$("#modal-text").text("Pago deshecho");
						$("#modal").modal('show');

					}else if(state=='ABONADO'){
						$("#due"+id).removeClass('btn-danger');
						$("#due"+id).addClass('btn-warning');
						$("#due"+id).attr('onclick','payDue('+id+',\'PAGADO\')');
						$("#due"+id).attr('title','Pagar');
						$("#due"+id).html('<i class="fa fa-remove fa-lg fa-fw"></i>');
						//$($("#due"+id).parent().parent().children()[0]).html('<td style="display: none;" class="checkUnify"><input type="checkbox"/></td>');
						$($("#due"+id).parent().parent().children()[4]).html('IMPAGO');
						$($("#loan"+$("#labelID").text()).children()[8]).html(data[1]);
						if(data[1]=="PAGADO"){
							$($("#loan"+$("#labelID").text()).children()[0]).html('<td style="display: none;" class="checkUnify"></td>');
						}else{
							$($("#loan"+$("#labelID").text()).children()[0]).html('<td style="display: none;" class="checkUnify"><input type="checkbox"/></td>');
						}
						$("#modal-text").text("Pago abonado");
						$("#modal").modal('show');

					}else{
						$("#due"+id).removeClass('btn-danger');
						$("#due"+id).addClass('btn-success');
						$("#due"+id).attr('onclick','payDue('+id+',\'IMPAGO\')');
						$("#due"+id).attr('title','Deshacer Pago');
						$("#due"+id).html('<i class="fa fa-check fa-lg fa-fw"></i>');
						//$($("#due"+id).parent().parent().children()[0]).html('<td style="display: none;" class="checkUnify"></td>');
						$($("#due"+id).parent().parent().children()[4]).html('PAGADO');
						$($("#due"+id).parent().parent().children()[6]).html('');
						$($("#loan"+$("#labelID").text()).children()[8]).html(data[1]);
						if(data[1]=="PAGADO"){
							$($("#loan"+$("#labelID").text()).children()[0]).html('<td style="display: none;" class="checkUnify"></td>');
						}else{
							$($("#loan"+$("#labelID").text()).children()[0]).html('<td style="display: none;" class="checkUnify"><input type="checkbox"/></td>');
						}
						$("#modal-text").text("Pago almacenado");
						$("#modal").modal('show');
					}
				}else{
					$("#modal-text").text("Error");
					$("#modal").modal('show');
				}
			});

		}
		function loadPayments(id){
			$("#panelPayment").css('display','block');

			$.post('../../phps/loan_Load.php', {type: "payments", id: id}, function(data, textStatus, xhr) {
				$("#tablePaymentBody").html('');
				if(data!=0){
					var data = JSON.parse(data);
					var list = '';
					for(i=0;i<data.length;i++){
						list+='<tr>' +
								'<td>'+data[i]['Numero']+'</td>' +
								'<td>'+data[i]['Fecha1']+'</td>' +
								'<td>'+data[i]['Monto']+'</td>' +
								'<td><button class="btn btn-danger" title="Deshacer Abono" onclick="deletePayment('+data[i]['ID']+','+data[i]['ID_PRESTAMO']+')"><i class="fa fa-remove fa-lg fa-fw"></i></button></td>' +
							'</tr>';
						if(i+1==data.length){
							$("#tablePaymentBody").append(list);
						}
					}
				}

			});
		}

		function savePayment(btn){
			var id = $("#labelID").text();
			var number = $($(btn).parent().parent().children()[0]).text();
			var date = $($($(btn).parent().parent().children()[1]).children()[0]).val();
			var amount = $($($(btn).parent().parent().children()[2]).children()[0]).val();

			if(amount==0){
				$("#modal-text").text("Debe ingresar un monto válido");
				$("#modal").modal('show');
				return;
			}else{
				var totalAmount = parseInt($($("#tableDues > tbody > tr").children()[3]).html());
				var toPay = parseInt(amount);
				var i = 0;
				$('#tablePayment > tbody > tr').each(function() {
					if(i<$('#tablePayment > tbody > tr').length-1){
						toPay += parseInt($($(this).children()[2]).html());
					}
					i++;
				});

				if(toPay>totalAmount){
					$("#modal-text").text("El abono ingresado supera el saldo del préstamo, favor verificar");
					$("#modal").modal('show');
					return;
				}else{
					var goPaid = false;
					if(toPay==totalAmount){
						goPaid = true;
					}
					$.post('../../phps/loan_Save.php', {type: "savePayment", 
						idLoan: id,
						number: number,
						date: date,
						amount: amount,
						goPaid: goPaid
					}, function(data, textStatus, xhr) {
						if(data=='OK'){
							loadPayments(id);
							loadDues(id);
							var rut = $("#txtViewRUT").val().split('-');
							viewRow(rut[0],$("#txtViewRUT").val(),$("#txtViewName").val());
						}
					});
				}
			}

			
		}

		function deletePayment(id,idLoan){
			$.post('../../phps/loan_Save.php', {type: "deletePayment", id: id, idLoan: idLoan}, function(data, textStatus, xhr) {
				if(data=='OK'){
					loadPayments(idLoan);
					loadDues(idLoan);
					var rut = $("#txtViewRUT").val().split('-');
					viewRow(rut[0],$("#txtViewRUT").val(),$("#txtViewName").val());
				}
			});
		}


		function calculate(){
			if($("#listType").val()=="CUOTAS_AUTO"){
				var dateStart = $('#txtLoanStart').val().split('/');
				var dateEnd = $('#txtLoanEnd').val().split('/');

				var start = moment([dateStart[2],dateStart[1]-1,1]);
				var end = moment([dateEnd[2],dateEnd[1]-1,1]);
				$('#txtLoanStart').val(start.format('DD/MM/YYYY'));
				$('#txtLoanEnd').val(end.format('DD/MM/YYYY'));
				var days = end.diff(start, 'days')+1;

				var loanTimeStart = moment(start);
				var loanTimeEnd = moment(end);

				var loanMonths = loanTimeEnd.diff(loanTimeStart, 'months');
				
				$("#txtLoanDues").val(loanMonths+1);
				var dueValue = Math.floor($("#txtLoanValue").val()/$("#txtLoanDues").val());
				var totalDues = dueValue * $("#txtLoanDues").val();

				$("#tableDues").html('<thead><tr><th>Sel.</th><th>Número</th><th>Tipo</th><th>Fecha</th><th>Monto</th><th>Estado</th><th></th></tr></thead><tbody></tbody>');
				var list = "";
				for(i=0;i<$("#txtLoanDues").val();i++){
					list = '<tr>';
					list += '<td><input class="checkboxDues" type="checkbox"/></td>';
					list += '<td>'+(i+1)+'</td>';
					list += '<td>Cuota</td>';
					if(i==0){
						list += '<td>'+start.format('MM/YYYY')+'</td>';
					}else{
						list += '<td>'+start.add(1, 'months').format('MM/YYYY')+'</td>';
					}

					if(i==$("#txtLoanDues").val()-1){
						list += '<td>'+(dueValue+($("#txtLoanValue").val()-totalDues))+'</td>';
					}else{
						list += '<td>'+dueValue+'</td>';
					}
					list += '<td>IMPAGO</td>';
					list += '<td>&nbsp;</td>';
					
					list += '</tr>';
					$("#tableDues").append(list);

					if(i+1==$("#txtLoanDues").val()){
						$(".checkboxDues").change(function(){
							if($(this).is(':checked')){
								$($(this).parent().parent().children()[4]).html('<input onkeyup="calculateIrregular()" type="number" style="width: 70px;" value="'+$($(this).parent().parent().children()[4]).text()+'"/>')
							}else{
								$($(this).parent().parent().children()[4]).html(0);
								calculateIrregular();
							}
						});
					}
				}
			
			}else if($("#listType").val()=="A_CUENTA"){
				$("#tableDues").html('<thead><tr><th>Número</th><th>Tipo</th><th>Fecha</th><th>Monto</th><th>Estado</th><th></th></tr></thead><tbody></tbody>');
				var list = '<tr>';
				list += '<td>1</td>';
				list += '<td>A Cuenta</td>';
				var dateStart = $('#txtLoanStart').val().split('/');
				var start = moment([dateStart[2],dateStart[1]-1,1]);
				list += '<td>'+start.format('MM/YYYY')+'</td>';
				list += '<td>'+$('#txtLoanValue').val()+'</td>';
				list += '<td>IMPAGO</td>';
				list += '<td>&nbsp;</td>';
				list += '</tr>';
				$("#tableDues").append(list);
			}

		}

		function calculateIrregular(){
			var totalIrregular = 0;
			var countRegular = 0;
			var i = 0;
			$('#tableDues > tbody > tr').each(function() {
				if($($(this).children()[0]).children().first().is(':checked')){
					totalIrregular += parseInt($($(this).children()[4]).children().first().val());
				}else{
					countRegular++;
				}
				i++;

				if(i==$('#tableDues > tbody > tr').length){
					var totalRegular =  parseInt($("#txtLoanValue").val())-totalIrregular;
					if(totalRegular<0){
						$("#modal-text").text("Los montos editados superan el total del préstamo, favor verificar");
						$("#modal").modal('show');
						return;
					}else{
						var dueValue = Math.floor(totalRegular/countRegular);		
						var totalDues = dueValue*countRegular;
						var countRegularFlag = 0;//Se utiliza para conocer la última cuota regular, y así adicionar el resto del saldo por efecto de los decimales
						$('#tableDues > tbody > tr').each(function() {
							if($($(this).children()[0]).children().first().is(':checked')==false){
								countRegularFlag++;
								if(countRegular==countRegularFlag){
									$($(this).children()[4]).text(dueValue+(totalRegular-totalDues));
								}else{
									$($(this).children()[4]).text(dueValue);
								}
							}
						});
					}
				}
			});
		}

		function calculateRedo(){
			if($("#listType").val()=="CUOTAS_AUTO"){
				var dateStart = $('#txtRedoLoanStart').val().split('/');
				var dateEnd = $('#txtRedoLoanEnd').val().split('/');

				var start = moment([dateStart[2],dateStart[1]-1,1]);
				var end = moment([dateEnd[2],dateEnd[1]-1,1]);
				$('#txtRedoLoanStart').val(start.format('DD/MM/YYYY'));
				$('#txtRedoLoanEnd').val(end.format('DD/MM/YYYY'));
				var days = end.diff(start, 'days')+1;

				var loanTimeStart = moment(start);
				var loanTimeEnd = moment(end);

				var loanMonths = loanTimeEnd.diff(loanTimeStart, 'months');
				
				$("#txtRedoLoanDues").val(loanMonths+1);
				var paid = 0;
				var lastNumber = 0;
				$('#tableDues > tbody > tr').each(function() {
					if($($(this).children()[4]).html()=="PAGADO"){
						paid += parseInt($($(this).children()[3]).html());
						lastNumber = parseInt($($(this).children()[0]).html());
					}else{
						$(this).remove();
					}
				});
				$.post('../../phps/loan_Load.php', {type: "fullAdd", id: $("#labelID").text()}, function(data, textStatus, xhr) {
					paid += parseInt(data); //Se agregan los valores restantes de abonos

					$("#txtRedoLoanValue").val($("#txtLoanValue").val()-paid);
					var dueValue = Math.floor($("#txtRedoLoanValue").val()/$("#txtRedoLoanDues").val());
					var totalDues = dueValue * $("#txtRedoLoanDues").val();

					//$("#tableDues").html('<thead><tr><th>Sel.</th><th>Número</th><th>Tipo</th><th>Fecha</th><th>Monto</th><th>Estado</th><th></th></tr></thead><tbody></tbody>');

					var list = "";
					for(i=0;i<$("#txtRedoLoanDues").val();i++){
						lastNumber++;
						list = '<tr>';
						list += '<td><input class="checkboxDues" type="checkbox"/></td>';
						list += '<td>'+lastNumber+'</td>';
						list += '<td>Cuota</td>';
						if(i==0){
							list += '<td>'+start.format('MM/YYYY')+'</td>';
						}else{
							list += '<td>'+start.add(1, 'months').format('MM/YYYY')+'</td>';
						}

						if(i==$("#txtRedoLoanDues").val()-1){
							list += '<td>'+(dueValue+($("#txtRedoLoanValue").val()-totalDues))+'</td>';
						}else{
							list += '<td>'+dueValue+'</td>';
						}
						list += '<td>IMPAGO</td>';
						list += '<td>&nbsp;</td>';
						
						list += '</tr>';
						$("#tableDues").append(list);
					}

				});


				

			}else if($("#listType").val()=="A_CUENTA"){

			}

		}

		function loadPlant(){
			$.post('../../phps/plant_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					$("#listPlant").append('<option value="'+data[i]["Pl_codigo"]+'">'+data[i]["PlNombre"]+'</option>');
				}
				$("#listPlant").val(98);
			});
		}

		function loadEnterprise(){
			$.post('../../phps/enterprise_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				$("#listEnterprise").append('<option value="0">TODAS</option>');
				for(i=0;i<data.length;i++){
					$("#listEnterprise").append('<option value="'+data[i]["Emp_codigo"]+'">'+data[i]["EmpSigla"]+'</option>');
				}
				$("#listEnterprise").val(0);
			});
		}

		function cleanModal(){
			$("#divID").css('visibility','hidden');
			$("#labelID").text('');
			$("#modalNew").modal('hide');
			loadData();
		}

		function cleanLoan(){
			$("#divID").css('visibility','hidden');
			$("#labelID").text('');
			$(".loanClass").attr('disabled','disabled');
			$("#btnRedoLoan").attr('disabled','disabled');
			$("#btnNewLoan").removeAttr('disabled');
			$("#btnNewLoanUnify").removeAttr('disabled');
			$("#txtLoanStart").val(moment().format('DD/MM/YYYY'));
			$("#txtLoanEnd").val(moment().format('DD/MM/YYYY'));
			$("#listType").val('CUOTAS_AUTO');
			$("#txtLoanDues").val('');
			$("#txtLoanValue").val('');
			$('.loanRow').removeClass('success');
			$('.loanButton').removeAttr('disabled');
			$("#tableDues").html('<thead><tr><th>Número</th><th>Tipo</th><th>Fecha</th><th>Monto</th><th>Estado</th><th></th></tr></thead><tbody></tbody>');
			$("#btnCancelLoanUnify").css('visibility','hidden');
			$("#btnNewLoanUnify").html('<i class="fa fa-gg fa-lg fa-fw"></i>&nbsp;&nbsp;Unificar Préstamos');
			$(".checkUnify").css('display','none');
			$(".checkboxUnify").prop('checked', false);
			$(".checkboxUnify").removeAttr('disabled');
			$("#panelPayment").css('display','none');
		}

		function cleanRedoLoan(){
			$("#panelRedo").css('display','none');
			$('.loanClass').removeAttr('disabled');
			loadDues($("#labelID").text());
		}



///////////////////////EXCEL/PDF RESUMEN & DETALLE/////////////////////////
		function toExcelAll() {
			$("#toExcelAll").attr('disabled','disabled');
			$("#toExcelAllSpin").css('display','block');
			var plant = 98;
			if($("#listPlant").val()!=null){
				plant = $("#listPlant").val();
			}
			$.post('../../phps/loan_Load.php', {type: "allDetail", 
				state: $("#listState").val(), 
				plant: plant, 
				enterprise: $("#listEnterprise").val(), 
				loanState: $("#listLoanState").val()
			}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				$("#tableAll").html('<thead>' + 
										'<tr>' +
											'<th>Empresa</th>' +
											'<th>Campo</th>' +
											'<th>RUT</th>' +
											'<th>Nombre</th>' +
											'<th>Fecha Inicio</th>' +
											'<th>Fecha Fin</th>' +
											'<th>Tipo</th>' +
											'<th>Monto Total</th>' +
											'<th>N° Cuotas</th>' +
											'<th>Monto Cuota</th>' +
											'<th>Cuotas Pendientes</th>' +
											'<th>Saldo Pendiente</th>' +
											'<th>Estado</th>' +
										'</tr>' +
									'</thead>' +
									'<tbody></tbody>');

				var totalAmount = 0, totalBalance = 0;
				for(i=0;i<data.length;i++){
					var list = '<tr>';
					list += '<td>'+data[i]["enterpriseInitials"]+'</td>';
					list += '<td>'+data[i]["plant"]+'</td>';
					list += '<td>'+data[i]["rut"]+'</td>';
					list += '<td>'+data[i]["fullname"]+'</td>';
					if(data[i]["startDate"][2]=="-"){
						var startDate = data[i]["startDate"].split('-');
						var endDate = data[i]["endDate"].split('-');
						list += '<td>'+startDate[0]+'/'+startDate[1]+'/'+startDate[2]+'</td>';
						list += '<td>'+endDate[0]+'/'+endDate[1]+'/'+endDate[2]+'</td>';
					}else{
						list += '<td>'+data[i]["startDate"]+'</td>';
						list += '<td>'+data[i]["endDate"]+'</td>';
					}
					if(data[i]['typeLoan']=='CUOTAS_AUTO'){
						list += '<td>Cuotas</td>';
					}else{
						list += '<td>A Cuenta</td>';
					}
					list += '<td>'+data[i]["amountTotal"]+'</td>';
					list += '<td>'+data[i]["duesNumber"]+'</td>';
					list += '<td>'+data[i]["amountDue"]+'</td>';
					list += '<td>'+data[i]["balanceDues"]+'</td>';

					if(data[i]["balance"]==null) data[i]["balance"] = 0;
					
					if(data[i]["balance"]==0){
						list += '<td>'+parseInt(data[i]["balance"])+'</td>';
						list += '<td>Pagado</td>';
					}else{
						if(data[i]['typeLoan']=='CUOTAS_AUTO'){
							list += '<td>'+parseInt(data[i]["balance"])+'</td>';
							list += '<td>Pendiente</td>';
						}else{
							if(data[i]["payment"]!=null){
								list += '<td>'+(parseInt(data[i]["balance"])-parseInt(data[i]["payment"]))+'</td>';
							}else{
								list += '<td>'+parseInt(data[i]["balance"])+'</td>';
							}
							list += '<td>Pendiente</td>';
						}
					}
					list += '</tr>';

					totalAmount += parseInt(data[i]["amountTotal"]);
					totalBalance += parseInt(data[i]["balance"]);
					
					$("#tableAll").append(list);
				}
				
				var list = '<tr>';
				list += '<th colspan="5"></th>';
				list += '<th colspan="2">Total Préstamos</th>';
				list += '<th style="text-align: right;">'+totalAmount+'</th>';
				list += '<th></th>';
				list += '<th colspan="2">Saldo Total</th>';
				list += '<th style="text-align: right;">'+totalBalance+'</th>';
				list += '<th></th>';
				list += '</tr>';
				$("#tableAll").append(list);

				$("#tableAll").table2excel({
					exclude: ".noExl",
					name: "Excel Document Name",
					filename: "Lista",
					fileext: ".xls",
					exclude_img: true,
					exclude_links: true,
					exclude_inputs: true
				});
				$("#toExcelAll").removeAttr('disabled');
				$("#toExcelAllSpin").css('display','none');
			});
		}


		function toExcelOne(id, rut, name, type) {
			$.post('../../phps/loan_Load.php', {type: "dues", id: id, period: $("#listPeriod").val()}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);

				var headTo = '';
				//headTo +='<tr><td colspan="8"></td></tr>';
				headTo += '<tr>';
				headTo +='<th colspan="3">Empresa</th>';
				headTo +='<th>RUT</th>';
				headTo +='</tr>';

				headTo += '<tr>';
				headTo +='<td colspan="3">'+data[0]['enterpriseName']+'</td>';
				headTo +='<td>'+data[0]['enterpriseRUT']+'</td>';
				headTo +='</tr>';
				headTo +='<tr><td colspan="7"></td></tr>';

				headTo += '<tr>';
				headTo +='<th>RUT</th>';
				headTo +='<th colspan="3">Nombre</th>';
				headTo +='<th>Fecha de Hoy</th>';
				headTo +='<th></th>';
				headTo +='<th></th>';
				headTo +='<th></th>';
				headTo +='<th></th>';
				headTo +='</tr>';

				headTo += '<tr>';
				headTo +='<td>'+rut+'</td>';
				headTo +='<td colspan="3">'+name+'</td>';
				headTo +='<td>'+moment().format('DD/MM/YYYY')+'</td>';
				headTo +='<td></td>';
				headTo +='<td></td>';
				headTo +='<td></td>';
				headTo +='</tr>';
				headTo +='<tr><td colspan="7"></td></tr>';

				var totalAmount = 0, countDues = 0, balance = 0;
				var list = "", allList = "", start = "", end = "";
				for(i=0;i<data.length;i++){
					list = '<tr>';
					list += '<td>'+data[i]['Numero']+'</td>';
					list += '<td>'+data[i]['Tipo']+'</td>';
					if(data[i]['Fecha1'][2]=='/'){
						list += '<td>'+data[i]['Fecha1']+'</td>';
						if(i==0) start = data[i]['Fecha1'];
						if(i+1==data.length) end = data[i]['Fecha1'];
					}else{
						list += '<td>'+data[i]['Fecha1'].replace('-','/')+'</td>';
						if(i==0) start = data[i]['Fecha1'].replace('-','/');
						if(i+1==data.length) end = data[i]['Fecha1'].replace('-','/');
					}
					list += '<td>'+data[i]['Monto']+'</td>';
					list += '<td>'+data[i]['Estado']+'</td>';

					totalAmount += parseInt(data[i]['Monto']);
					if(data[i]['Estado']=='IMPAGO'){
						countDues++;
						balance += parseInt(data[i]['Monto']);
					}
					list += '</tr>';
					allList += list;
				}

				headTo += '<tr>';
				headTo += '<th>Tipo Préstamo</th>';
				headTo += '<th>Inicio</th>';
				headTo += '<th>Fin</th>';
				headTo += '<th>N° Cuotas</th>';
				headTo += '<th>Monto Total</th>';
				headTo += '<th>Cuotas Pendientes</th>';
				headTo += '<th>Saldo Pendiente</th>';
				headTo += '</tr>';

				headTo += '<tr>';
				headTo += '<td>'+type+'</td>';
				headTo += '<td>'+start+'</td>';
				headTo += '<td>'+end+'</td>';
				headTo += '<td>'+data.length+'</td>';
				headTo += '<td>'+totalAmount+'</td>';
				headTo += '<td>'+countDues+'</td>';

				if(balance>0){
					headTo += '<td>'+(parseInt(balance)-parseInt(data[0]['payment']))+'</td>';
				}else{
					headTo += '<td>'+balance+'</td>';
				}

				headTo += '</tr>';
				headTo +='<tr><td colspan="7"></td></tr>';

				$("#tableOne").html('<thead>'+headTo+'</thead><tbody><tr><th>Número</th><th>Tipo</th><th>Fecha</th><th>Monto</th><th>Estado</th><th></th></tr><tbody>'+allList+'</tbody>');


				$("#tableOne").table2excel({
					exclude: ".noExl",
					name: "Excel Document Name",
					filename: "Préstamo",
					fileext: ".xls",
					exclude_img: true,
					exclude_links: true,
					exclude_inputs: true
				});
			});

		}


		function toPDFAll(){
			var plant = 98;
			if($("#listPlant").val()!=null){
				plant = $("#listPlant").val();
			}
			window.open("loan_pdf.php?type=all&state="+$("#listState").val()+"&plant="+plant+"&enterprise="+$("#listEnterprise").val()+"&loanState="+$("#listLoanState").val());
		}

		function toPDFOne(id, rut, name, type){
			window.open("loan_pdf.php?type=one&id="+id+"&rut="+rut+"&name="+name+"&rut="+rut+"&period="+$("#listPeriod").val()+"&typeLoan="+type);
		}

		function generatePDF(id, rut, name, type){
			$("#modal-pdf-id").text(id);
			$("#modal-pdf-rut").text(rut);
			$("#modal-pdf-name").text(name);
			$("#modal-pdf-type").text(type);

			$("#modalPDF").modal('show');
		}

		function toPDFMandate(id, rut, name, type, date){
			window.open("loanMandate_pdf.php?type=one&id="+id+"&rut="+rut+"&name="+name+"&rut="+rut+"&period="+$("#listPeriod").val()+"&typeLoan="+type+"&date="+date);
		}

		function loadPeriod(id){
			$.post('../../phps/settlement_Load.php', {type: "one", id: id}, function(data, textStatus, xhr) {
				$("#listPeriod").html('<option value="0">Actual</option>');
				var data = JSON.parse(data);
				if(data.length>=1 && data[0]["contract_start"]!=''){
					for(i=0;i<data.length;i++){
						$("#listPeriod").append('<option value="'+data[i]["ID"]+'">'+data[i]["contract_start"]+' a '+data[i]["contract_end"]+'</option>');
					}
				}
				$("#listPeriod").val(0);
				$("#listPeriod").change(function(){
					console.log('here');
					var idRut = $('#txtViewRUT').val().split('-');
					loadRow(idRut[0],$('#txtViewRUT').val(),$('#txtViewName').val());
				});
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
				<div class="panel-heading"><i class="fa fa-money fa-lg fa-fw"></i>&nbsp;&nbsp; Préstamos</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<label>Estado contrato:</label>
				    	    <select id="listState" class="form-control">
								<option value="T">TODOS</option>
		  						<option value="V" selected>VIGENTE</option>
								<option value="S">FINIQUITADO</option>
							</select>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<label>Campo:</label>
				    	    <select id="listPlant" class="form-control">
							</select>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<label>Empresa:</label>
				    	    <select id="listEnterprise" class="form-control">
							</select>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<label>Estado Préstamo:</label>
				    	    <select id="listLoanState" class="form-control">
								<option value="T">TODOS</option>
		  						<option value="V" selected>PENDIENTES</option>
								<option value="S">PAGADOS</option>
							</select>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<button id="toPDFAll" class="btn btn-danger">&nbsp;PDF Resumen&nbsp;<i class="fa fa-file-pdf-o fa-lg fa-fw"></i></button>
							<select class="form-control" style="visibility: hidden;"></select>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<button id="toExcelAll" class="btn btn-success"><i id="toExcelAllSpin" class="fa fa-spinner fa-spin" style="display: none;"></i>&nbsp;Excel Resumen&nbsp;<img src="../../images/excel.ico"/></button>
							<select class="form-control" style="visibility: hidden;"></select>
						</div>
						<!--<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<button id="toExcel" class="btn btn-success">Exportar a Excel  <img src="../../images/excel.ico"/></button>
							<select class="form-control" style="visibility: hidden;"></select>
						</div>-->
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<button id="btnSaveMonthDues" class="btn btn-success"><i class="fa fa-plus fa-lg fa-fw"></i>&nbsp;&nbsp;Pagar cuotas del mes</button>
						</div>
					</div>	
					<br/>
					<br/>
					<div class="table-responsive">
						<table id="tableData" class="table table-hover" style="font-size: 12px;">
						</table>
						<table id="tableDataExcel" style="display: none;">
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>


	<div id="modal" class="modal fade" data-backdrop="static" style="z-index: 1060">
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

	<div id="modalDelete" class="modal fade" data-backdrop="static" style="z-index: 1052">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
	        	<div class="modal-body">
		    	    <p id="modal-delete-text">¿Está seguro de eliminar este préstamo?</p>
		    	    <p id="modal-delete-id" style="visibility: hidden"></p>
		      	</div>
		      	<div class="modal-footer">
		        	<button id="btnDelete" type="button" class="btn btn-danger">Eliminar</button>
		        	<button id="modalDeleteHide" type="button" class="btn btn-primary">Cancelar</button>
		      	</div>
		    </div>
		</div>
	</div>

	<div id="modalUnify" class="modal fade" data-backdrop="static" style="z-index: 1052">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
	        	<div class="modal-body">
		    	    <p id="modal-unify-text"></p>
		    	    <p id="modal-unify-id" style="visibility: hidden"></p>
		      	</div>
		      	<div class="modal-footer">
		        	<button id="btnUnify" type="button" class="btn btn-success">Unificar</button>
		        	<button id="modalUnifyHide" type="button" class="btn btn-primary">Cancelar</button>
		      	</div>
		    </div>
		</div>
	</div>

	<div id="modalUnifySave" class="modal fade" data-backdrop="static" style="z-index: 1052">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
	        	<div class="modal-body">
		    	    <p id="modal-unifySave-text">Las cuotas impagas de los préstamos seleccionados serán eliminadas, y el saldo se almacenará como un nuevo préstamo, ¿desea continuar?</p>
		    	    <p id="modal-unifySave-id" style="visibility: hidden"></p>
		      	</div>
		      	<div class="modal-footer">
		        	<button id="btnUnifySave" type="button" class="btn btn-success">Sí</button>
		        	<button id="modalUnifySaveHide" type="button" class="btn btn-primary">Cancelar</button>
		      	</div>
		    </div>
		</div>
	</div>

	<div id="modalPayDues" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
	        	<div class="modal-body">
		    	    <p>¿Desea pagar todas las cuotas del mes?</p>
		    	    <p>Mes a cancelar: </p>
		    	    <select id="listDuesMonth" class="form-control" disabled>
						<option value="01">Enero</option>
						<option value="02">Febrero</option>
						<option value="03">Marzo</option>
						<option value="04">Abril</option>
						<option value="05">Mayo</option>
						<option value="06">Junio</option>
						<option value="07">Julio</option>
						<option value="08">Agosto</option>
						<option value="09">Septiembre</option>
						<option value="10">Octubre</option>
						<option value="11">Noviembre</option>
						<option value="12">Diciembre</option>
					</select>
					<select id="listDuesYear" class="form-control" disabled>
						<option value="2017">2017</option>
						<option value="2018">2018</option>
						<option value="2019">2019</option>
						<option value="2020">2020</option>
						<option value="2021">2021</option>
						<option value="2022">2022</option>
						<option value="2023">2023</option>
						<option value="2024">2024</option>
						<option value="2025">2025</option>
						<option value="2026">2026</option>
						<option value="2027">2027</option>
						<option value="2028">2028</option>
						<option value="2029">2029</option>
						<option value="2030">2030</option>
						<option value="2031">2031</option>
						<option value="2032">2032</option>
						<option value="2033">2033</option>
						<option value="2034">2034</option>
						<option value="2035">2035</option>
						<option value="2036">2036</option>
						<option value="2037">2037</option>
						<option value="2038">2038</option>
						<option value="2039">2039</option>
						<option value="2040">2040</option>
					</select>
		      	</div>
		      	<div class="modal-footer">
		        	<button id="payDues" type="button" class="btn btn-success">Almacenar</button>
		        	<button id="modalPayDuesHide" type="button" class="btn btn-primary">Cancelar</button>
		      	</div>
		    </div>
		</div>
	</div>

	<div id="modalView" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-xl">
			<div class="modal-content">
	        	<div class="modal-body">
					<div class="panel panel-primary">
						<div class="panel-heading"><i class="fa fa-usd fa-lg fa-fw"></i>&nbsp;&nbsp; Ver Préstamos</div>
						<div class="panel-body">
							<div class="container-fluid">
								<div class="row">
									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							  			<label>RUT:</label>
		  								<input id="txtViewRUT" type="Name" class="form-control"  style="text-align: right;" disabled>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
							  			<label>Nombre:</label>
							  			<input id="txtViewName" type="Name" class="form-control" disabled>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							  			<label>Período:</label>
										<select id="listPeriod" class="form-control">
											<option value="0">Actual</option>
										</select>
									</div>
									<!--<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							  			<label>Inicio Contrato:</label>
		  								<div class="input-group">
											<input id="txtContractStart" type="text" class="form-control datepickerTxt" disabled>
											<span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></span>
										</div>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							  			<label>Fecha Actual:</label>
		  								<div class="input-group">
											<input id="txtToday" type="text" class="form-control datepickerTxt" disabled>
											<span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></span>
										</div>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							  			<label>Vacaciones acumuladas:</label>
							  			<input id="txtVacationsTotal" type="Name" class="form-control" style="text-align: center;" disabled>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							  			<label>Vacaciones pendientes:</label>
							  			<input id="txtVacationsPending" type="Name" class="form-control" style="text-align: center;" disabled>
									</div>-->

									<!--<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							  			<label style="visibility: hidden;">Nombre:</label>
										<button id="toExcelHistory" class="btn btn-success">Exportar a Excel  <img src="../../images/excel.ico"/></button>
										<select class="form-control" style="visibility: hidden;"></select>
									</div>-->

									<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">&nbsp;<br/></div>
									<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
										<div class="panel panel-primary">
											<div class="panel-body">
												<div class="table-responsive">
													<table id="tableLoans" class="table" style="font-size: 12px;">
														<thead>
															<tr>
																<th>ID</th>
																<th>Tipo</th>
																<th>Fecha Inicio</th>
																<th>Fecha Fin</th>
																<th>Cuotas</th>
																<th>Valor Total</th>
																<th>Saldo</th>
																<th>Estado</th>
																<th>Editar</th>
																<th>Eliminar</th>
																<th>Excel</th>
																<th>PDF</th>
																<th>Mandato</th>
															</tr>
														</thead>
														<tbody>
														</tbody>
													</table>
												</div>
											</div>
										</div>
									</div>

									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3" style="text-align: center;"></div>
									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3" style="text-align: center;">
										<button id="btnNewLoan" class="btn btn-success"><i class="fa fa-plus-square fa-lg fa-fw"></i>&nbsp;&nbsp;Nuevo Préstamo</button>
										<br/>
										<br/>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3" style="text-align: center;">
										<button id="btnNewLoanUnify" class="btn btn-primary"><i class="fa fa-gg fa-lg fa-fw"></i>&nbsp;&nbsp;Unificar Préstamos</button>
										<br/>
										<br/>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3" style="text-align: center; visibility: hidden;">
										<button id="btnCancelLoanUnify" class="btn btn-danger"><i class="fa fa-remove fa-lg fa-fw"></i>&nbsp;&nbsp;Cancelar</button>
										<br/>
										<br/>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
										<div class="panel panel-primary" style="height: 310px;">
											<div class="panel-body">
												<div class="row">
													<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3" style="text-align: center;"></div>
													<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6" style="text-align: center;">
											  			<label>Tipo:</label>
														<select id="listType" class="form-control loanClass" disabled>
															<option value="CUOTAS_AUTO">Cuotas</option>
															<option value="A_CUENTA">A Cuenta de</option>
														</select>
													</div>
													<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3" style="text-align: center;"></div>
													<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
											  			<label>Inicio:</label>
						  								<div class="input-group">
															<input id="txtLoanStart" type="text" class="form-control datepickerTxt loanCalculate loanClass" disabled>
															<span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></span>
														</div>
													</div>
													<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
											  			<label>Fin:</label>
						  								<div class="input-group">
															<input id="txtLoanEnd" type="text" class="form-control datepickerTxt loanCalculate" disabled>
															<span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></span>
														</div>
													</div>
													<div class="col-xs-12 col-sm-12 col-md-5 col-lg-5">
											  			<label>N° Cuotas</label>
											  			<input id="txtLoanDues" type="Number" class="form-control loanCalculate loanClass" style="text-align: center;" disabled>
													</div>
													<div class="col-xs-12 col-sm-12 col-md-7 col-lg-7">
											  			<label>Monto Total:</label>
											  			<input id="txtLoanValue" type="Number" class="form-control loanCalculate loanClass" style="text-align: right;" disabled>
													</div>
													<div id="divID" class="col-xs-12 col-sm-12 col-md-1 col-lg-1" style="visibility: hidden;">
											  			<label>ID:</label>
											  			<label id="labelID"></label>
													</div>
													<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6" style="text-align: center;">
														<br/>
														<button id="btnSaveLoan" class="btn btn-success loanClass" disabled>Almacenar Préstamo</button>
													</div>
													<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3" style="text-align: center;">
														<br/>
														<button id="cancelLoan" class="btn btn-danger loanClass" disabled><i class="fa fa-remove fa-lg fa-fw"></i>&nbsp;&nbsp;Cancelar</button>
													</div>

													<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">&nbsp;<br/></div>
													<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6" style="text-align: center;">
														<br/>
														<button id="btnRedoLoan" class="btn btn-primary" disabled><i class="fa fa-plus fa-lg fa-fw"></i>&nbsp;&nbsp;Repactar Saldo</button>
													</div>
													<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">&nbsp;<br/></div>
													<div id="panelRedo" class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="display: none;">
														<div class="panel panel-primary">
															<div class="panel-body">
																<div class="row">
																	<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
															  			<label>Inicio:</label>
										  								<div class="input-group">
																			<input id="txtRedoLoanStart" type="text" class="form-control datepickerTxt redoLoanCalculate">
																			<span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></span>
																		</div>
																	</div>
																	<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
															  			<label>Fin:</label>
										  								<div class="input-group">
																			<input id="txtRedoLoanEnd" type="text" class="form-control datepickerTxt redoLoanCalculate" disabled>
																			<span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></span>
																		</div>
																	</div>
																	<div class="col-xs-12 col-sm-12 col-md-5 col-lg-5">
															  			<label>N° Cuotas</label>
															  			<input id="txtRedoLoanDues" type="Number" class="form-control redoLoanCalculate" style="text-align: center;">
																	</div>
																	<div class="col-xs-12 col-sm-12 col-md-7 col-lg-7">
															  			<label>Saldo:</label>
															  			<input id="txtRedoLoanValue" type="Number" class="form-control redoLoanCalculate" style="text-align: right;" disabled>
																	</div>
																	<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6" style="text-align: center;">
																		<br/>
																		<button id="btnRedoLoanSave" class="btn btn-success">Almacenar</button>
																	</div>
																	<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6" style="text-align: center;">
																		<br/>
																		<button id="btnRedoLoanCancel" class="btn btn-danger">Cancelar</button>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>

									<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
										<div class="panel panel-primary" style="height: 310px;">
											<div class="panel-body" style="overflow: auto; height: 306px;">
												<table id="tableDues" class="table" style="font-size: 12px;">
													<thead>
														<tr>
															<th>Número</th>
															<th>Tipo</th>
															<th>Fecha</th>
															<th>Monto</th>
															<th>Estado</th><th></th>
														</tr>
													</thead>
													<tbody>
													</tbody>
												</table>
												<div id="panelPayment" class="panel panel-success" style="display: none;">
													<div class="panel-body">
														<button id="btnAddPayment" class="btn btn-success"><i class="fa fa-plus fa-lg fa-fw"></i>&nbsp;Agregar Abono</button>
														<table id="tablePayment" class="table" style="font-size: 12px;">
															<thead>
																<tr>
																	<th>Número</th>
																	<th>Fecha</th>
																	<th>Monto</th>
																	<th></th>
																</tr>
															</thead>
															<tbody id="tablePaymentBody">
															</tbody>
														</table>
													</div>
												</div>
											</div>
										</div>
									</div>



									<!--<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3"></div>
									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
										<br/>
										<button id="btnSaveVacation" class="btn btn-success"><i class="fa fa-image fa-lg fa-fw"></i>&nbsp;&nbsp;Almacenar Registro</button>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
										<br/>
										<button id="btnCancelVacation" class="btn btn-danger" disabled><i class="fa fa-remove fa-lg fa-fw"></i>&nbsp;&nbsp;Cancelar</button>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3"></div>-->

								</div>
								<br/>
								<div style="text-align:right;">
									<div style="display:inline-block;"><button id="cancelView" class="btn btn-primary"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span>&nbsp;&nbsp; Salir</button></div>
								</div>
							</div>
						</div>
					</div>
		      	</div>
		    </div>
		</div>
	</div>

	<div id="modalPDF" class="modal fade" data-backdrop="static" style="z-index: 1051">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
	        	<div class="modal-body">
		    	    <label>Fecha a mostrar:</label>
						<div class="input-group">
						<input id="txtPDFDate" type="text" class="form-control datepickerTxt">
						<span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></span>
					</div>
		    	    <p id="modal-pdf-id" style="display: none;"></p>
		    	    <p id="modal-pdf-rut" style="display: none;"></p>
		    	    <p id="modal-pdf-name" style="display: none;"></p>
		    	    <p id="modal-pdf-type" style="display: none;"></p>
		      	</div>
		      	<div class="modal-footer">
		        	<button id="generatePDFOK" type="button" class="btn btn-danger">Generar PDF</button>
		        	<button id="modalPDFHide" type="button" class="btn btn-primary">Cancelar</button>
		      	</div>
		    </div>
		</div>
	</div>

	<div id="modalDueAdd" class="modal fade" data-backdrop="static" style="z-index: 1050">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
	        	<div class="modal-body">
		    	    <label>Monto Abono:</label>
					<input id="txtDueAdd" type="number" class="form-control">
					<p id="modal-due-id" style="display: none;"></p>
		      	</div>
		      	<div class="modal-footer">
		        	<button id="btnDueAddSave" type="button" class="btn btn-success">Almacenar Abono</button>
		        	<button id="btnDueAddCancel" type="button" class="btn btn-primary">Cancelar</button>
		      	</div>
		    </div>
		</div>
	</div>

	<table id="tableOne" style="display: none;"></table>
	<table id="tableAll" style="display: none;"></table>

</body>
</html>