<?php
header('Content-Type: text/html; charset=utf8'); 
session_start();

if(!isset($_SESSION['userId'])){
	header('Location: ../../login.php');
}elseif($_SESSION['display']['settlement']['view']!=''){
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

			$(".dateFilter	").on('changeDate', function(ev) {
				loadData();
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


			$(".mandatory").keypress(function(event){
				$(this).parent().removeClass('has-error');	
			});
			
			$("#modalHide").click(function() {
				$("#modal").modal('hide');	
			});

			$("#modalDeleteHide").click(function() {
				$("#modalDelete").modal('hide');	
			});

			$("#modalPDFHide").click(function() {
				$("#txtPDFDate").datepicker('setValue', '');
				$("#txtPDFDate").attr("disabled","disabled");
				$("#checkPDF").prop('checked', false);
				$("#modalPDF").modal('hide');	
			});

			$("#checkPDF").change(function(){
				if($(this).is(':checked')){
					$("#txtPDFDate").removeAttr("disabled");
				}else{
					$("#txtPDFDate").attr("disabled","disabled");
				}
			});
		    	
			$("#generatePDFOK").click(function(){
				if($("#checkPDF").is(':checked')){
					generatePDFLink($("#modal-pdf-type").text(),$("#modal-pdf-id").text(),$("#txtPDFDate").val());
				}else{
					generatePDFLink($("#modal-pdf-type").text(),$("#modal-pdf-id").text(),'-');
				}
				$("#txtPDFDate").datepicker('setValue', '');
				$("#txtPDFDate").attr("disabled","disabled");
				$("#checkPDF").prop('checked', false);
				$("#modalPDF").modal('hide');	
			});


			$("#new").click(function() {
				var count = 0;
				$('#tableData > tbody > tr').each(function() {
					if($(this).find('td').find(">:first-child").is(':checked')){
						if($($(this).children()[5]).html()=='V'){
							addRowPersonal($($(this).children()[1]).html(),$($(this).children()[2]).html(),$($(this).children()[3]).html(),$($(this).children()[4]).html(),$($(this).children()[6]).html(),$($(this).children()[7]).html(),$($(this).children()[10]).html());
							count++;
						}
					}
				});
				if(count>0){
					$("#modalNew").modal('show');
				}else{				
					$("#modal-text").text("Debe seleccionar al menos 1 trabajador en estado vigente");
					$("#modal").modal('show');
				}
			});

			$("#cancel").click(function() {
				cleanModal();
			});

			$("#cancelView").click(function() {
				$("#modalView").modal('hide');
				$("#tableSettlement").html('<thead><tr><th>Empresa</th><th>Campo</th><th>Sueldo Base (Ficha)</th><th>Inicio Contrato</th><th>Fin Contrato</th><th>Causa</th><th>Días Trab.</th><th>Vacaciones Prop.</th><th>Estado Pago</th><th>PDF</th></tr></thead><tbody></tbody>');
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

			$("#save").click(function() {
				var type="update", id=0;
				if($("#divID").css('display')=='none'){
					type='save';
				}else{
					id=$("#labelID").text();
				}
				if(calculation){
					var personalList = "";
					contador = 0;
					$('#tableSelected > tbody > tr').each(function() {
						contador = 0;
						$(this).find('td').each(function() {
							var cellValue = $(this).html(); //Valor de cada celda
							
							if(contador==8){
								if($($(this).children()[0]).is(':checked')==true){
									personalList += $($(this).children()[1]).val();
								}else{
									personalList += 0;
								}

							}else if(contador==14 || contador==15 || contador ==16){
								if($($(this).children()[0]).is(':checked')==true){
									cellValue = cellValue.split('>');
									personalList += cellValue[1];
								}else{
									personalList += 0;
								}
/*
							}else if(contador==15){
								if($($(this).children()[0]).is(':checked')==true){
									cellValue = cellValue.split('>');
									personalList += cellValue[1];
								}else{
									personalList += 0;
								}

								}else if(contador==15){
								if($($(this).children()[0]).is(':checked')==true){
									cellValue = cellValue.split('>');
									personalList += cellValue[1];
								}else{
									personalList += 0;
								}*/

							}else if(contador==17 || contador==19 || contador==20){
								if($.isNumeric($($(this).children()[0]).val())){
									personalList += $($(this).children()[0]).val();
								}else{
									personalList += 0;
								}
							}else if(contador==21){
								personalList += $($(this).children()[0]).val();
							}else{
								if(cellValue==""){
									if(contador==18){
										personalList += "0";
									}else{
										personalList += "-";
									}
								}else{
									personalList += cellValue;
								}
							}
							if(contador<21){
								personalList += "&&";
							}
							contador++;
						});
						personalList += "&&&&";
					});

					//return;
					if(contador>0){
						//$("#modalProgress-text").html('<i class="fa fa-spinner fa-spin fa-2x"></i><br/>Almacenando...');
						//$("#modalProgress").modal('show');
						$.post('../../phps/settlement_Save.php', {type: type,
							id: id,
							fecha_creacion: moment().format("DD/MM/YYYY"),
							fecha_finiquito: $("#txtFireDate").val(),
							articulo: $("#listEndCause").val(),
							personalList: personalList
						}, function(data, textStatus, xhr) {
							//console.log(data);
							if($.isNumeric(data)){
								//loadData();
								//$("#modalProgress").modal('hide');
								$("#divID").css('display','block');
								$("#labelID").text(data);
								$("#generatePDF").removeAttr('disabled');
								$("#modal-text").text("Almacenado");
								$("#modal").modal('show');
								//cleanModal();

							}else{
								//$("#modalProgress").modal('hide');
								$("#modal-text").text("Error");
								$("#modal").modal('show');
							}
						});
					}else{
						$("#modal-text").text("Debe ingresar 1 o más trabajadores");
						$("#modal").modal('show');
					}
				}else{
					$("#modal-text").text("Debe realizar cálculo antes de finiquitar");
					$("#modal").modal('show');
				}
			});

			$("#listEndCause").change(function(){
				if($(this).val()=='159.2'){
					contador = 0;
					$('#tableSelected > tbody > tr').each(function() {
						contador = 0;
						$(this).find('td').each(function() {
							var cellValue = $(this).html(); //Valor de cada celda
							if(contador==14){
								if($($(this).children()[0]).is(':checked')==true){
									$($(this).children()[0]).prop('checked', false);
									$($(this).children()[0]).attr('disabled', 'disabled');
								}
							}
							contador++;
						});
					});
				}else{
					contador = 0;
					$('#tableSelected > tbody > tr').each(function() {
						contador = 0;
						$(this).find('td').each(function() {
							var cellValue = $(this).html(); //Valor de cada celda
							if(contador==14){
								if($($(this).children()[0]).is(':checked')==false){
									$($(this).children()[0]).prop('checked', true);
									$($(this).children()[0]).removeAttr('disabled', 'disabled');
								}
							}
							contador++;
						});
					});
				}

			});

			$("#calculate").click(function(){
				$("#modalProgress-text").html('<i class="fa fa-spinner fa-spin fa-2x"></i><br/>Calculando...');
				$("#modalProgress").modal('show');
				var count = 0;
				var list = "";
				var listDate = "";
				$('#tableSelected > tbody > tr').each(function() {
					
					var id = $($(this).children()[2]).html().split("-");
					if(id[0].length==1) list += "\'         "+id[0]+"\',";
					if(id[0].length==2) list += "\'        "+id[0]+"\',";
					if(id[0].length==3) list += "\'       "+id[0]+"\',";
					if(id[0].length==4) list += "\'      "+id[0]+"\',";
					if(id[0].length==5) list += "\'     "+id[0]+"\',";
					if(id[0].length==6) list += "\'    "+id[0]+"\',";
					if(id[0].length==7) list += "\'   "+id[0]+"\',";
					if(id[0].length==8) list += "\'  "+id[0]+"\',";
					if(id[0].length==9) list += "\' "+id[0]+"\',";
					if(id[0].length==10) list += "\'"+id[0]+"\',";

					//listDate += id[0]+"&&"+$($(this).children()[5]).html()+",";
					count++;

					if(count==$('#tableSelected > tbody > tr').length){
						list = list.slice(0,-1);
						var progress = setInterval(function(){ progressBar() }, 1000);
						$.post('../../phps/settlement_Load.php', {type: "salary", list: list, endDate: $('#txtFireDate').val()}, function(data, textStatus, xhr) {
							console.log(data);
							var data = JSON.parse(data);
							$('#tableSelected > tbody > tr').each(function() {

								var dateStart = $($(this).children()[5]).html().split('/');
								var dateEnd = $('#txtFireDate').val().split('/');
								var start = moment([dateStart[2],dateStart[1]-1,dateStart[0]]);
								var end = moment([dateEnd[2],dateEnd[1]-1,dateEnd[0]]);
								var days = end.diff(start, 'days')+1;
								//console.log(days);

								//Cálculo de meses para indemnización fijos
								var months = end.diff(start, 'months');
								var startX = moment(start);
								var startMonth = startX.add(months, 'M');
								if(end.diff(startMonth, 'days')>=15){//En caso de que la cantidad de días "sueltos" sea igual o mayor a 15, se considerará 1 mes extra
									months++;
								}

								$($(this).children()[7]).html(days);

								var serviceTimeStart = moment(start);
								var serviceTimeEnd = moment(end);
								//serviceTimeStart.subtract(1, 'days');

								var serviceYears = serviceTimeEnd.diff(serviceTimeStart, 'years');

								if(serviceYears>0){
									serviceTimeStart.add(serviceYears, 'years');
									var serviceMonths = serviceTimeEnd.diff(serviceTimeStart, 'months');
									if(serviceMonths>=6){
										serviceYears++;
									}/*else if(serviceMonths==6){
										serviceTimeStart.add(serviceTimeEnd.diff(serviceMonths, 'months'), "months");
										var serviceDays = serviceTimeEnd.diff(serviceTimeStart, 'days');
										if(serviceDays)
									}*/
								}
								if(serviceYears>11){
									serviceYears=11;
								}
									
								var id = $($(this).children()[2]).html().split("-");
								var rut = "";
								if(id[0].length==1) rut = "         "+id[0];
								if(id[0].length==2) rut = "        "+id[0];
								if(id[0].length==3) rut = "       "+id[0];
								if(id[0].length==4) rut = "      "+id[0];
								if(id[0].length==5) rut = "     "+id[0];
								if(id[0].length==6) rut = "    "+id[0];
								if(id[0].length==7) rut = "   "+id[0];
								if(id[0].length==8) rut = "  "+id[0];
								if(id[0].length==9) rut = " "+id[0];

								var salaryBase = 0;
								var salaryBaseVacation = 0;
								var salaryGratification = 0;
								var salaryCollation = 0;
								var salaryMobilization = 0;
								var salaryLoan = 0;
								var salaryLast = 0;
								var duration = 0;
								for(i=0;i<data.length;i++){
									if(data[i]['rut']==rut){
										salaryBase = parseInt(data[i]['salary']);
										salaryBaseVacation = parseInt(data[i]['salaryBonus']);
										salaryGratification = parseInt(data[i]['gratification']);
										salaryCollation = parseInt(data[i]['collation']);
										salaryMobilization = parseInt(data[i]['mobilization']);
										salaryLoan = parseInt(data[i]['loan']);
										salaryLast = data[i]['salaryLast'];
										duration = data[i]['duration'];
										i=data.length;
									}
								}

								$($(this).children()[8]).html('<input type="checkbox" checked="checked"/><select>'+salaryLast+'<select>');
								$($(this).children()[9]).html(salaryBase);
								$($(this).children()[10]).html(salaryGratification);
								$($(this).children()[11]).html(salaryCollation);
								$($(this).children()[12]).html(salaryMobilization);
								$($(this).children()[18]).html(salaryLoan);
								if($("#listEndCause").val()=='159.2'){
									$($(this).children()[14]).html('<input type="checkbox" disabled/>'+(serviceYears*(salaryBase+salaryGratification+salaryCollation+salaryMobilization)));
								}else{
									$($(this).children()[14]).html('<input type="checkbox" checked="checked"/>'+(serviceYears*(salaryBase+salaryGratification+salaryCollation+salaryMobilization)));
								}
								$($(this).children()[15]).html('<input type="checkbox" />'+(salaryBase+salaryGratification+salaryCollation+salaryMobilization));

								if(duration!=1){//Si contrato no es indefinido, se calcula indemnización por mes trabajado
									$($(this).children()[16]).html('<input type="checkbox" />'+parseInt(months*((salaryBase+salaryGratification+salaryCollation+salaryMobilization)/30)*2.5));
								}else{
									$($(this).children()[16]).html('<input type="checkbox" disabled/>0');
								}

								var salaryDay = salaryBaseVacation/30;
								if(days>=30){
									//var settlement = (((days/365)*12*1.25)-$($(this).children()[6]).html())*salaryDay;
									//var vacationDays = ((days/365)*12*1.25)-$($(this).children()[6]).html();
									if($($(this).children()[6]).html()=='-'){
										$($(this).children()[6]).html(0);
									}
									var vacationDays = ((15/12/30)*(days))-$($(this).children()[6]).html();
									var vacationCell = $($(this).children()[13]);
									var dateFire = $('#txtFireDate').val().split('/');
									var startVacation = moment([dateFire[2],dateFire[1]-1,dateFire[0]]).add(1,'days');
									$.post('../../phps/settlement_Load.php', {type: "vacations", start: startVacation.format('DD/MM/YYYY'), vacationDays: vacationDays}, function(data, textStatus, xhr) {
										var settlement = data*salaryDay;
										vacationCell.html(parseInt(settlement));
									});

									//$($(this).children()[12]).html(parseInt(settlement));

								}else{
									$($(this).children()[13]).html(0);
								}
								clearInterval(progress);
								$("#modalProgress").modal('hide');
							});

						});
					}

				});
				calculation = true;
			});

			$("#listState").change(function(){
				if($(this).val()=='V'){
					$(".dateFilter").attr("disabled","disabled");
					$("#btnPrintSelected").attr("disabled","disabled");
				}else{
					$(".dateFilter").removeAttr("disabled");
					$("#btnPrintSelected").removeAttr("disabled");
				}
				loadData();
			});
			$("#listPlant").change(function(){
				loadData();
			});

			$("#generatePDF").click(function() {
				if($("#divID").css('display')=='none'){
					$("#modal-text").text("Debe primero guardar Finiquito");
					$("#modal").modal('show');
				}else{
					generatePDFLink('all',$("#labelID").text(),'-');
				}
			});

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

			$("#btnChangeMode").click(function() {
				if($(this).text()=='Ver Agrupados'){
					$(this).text('Ver Individual');
					$("#listState").attr("disabled","disabled");
					$("#listPlant").attr("disabled","disabled");
					$("#tableDataAgrupado").css("display","block");
					$("#tableData").css("display","none");
					loadDataGroup();
				}else{
					$(this).text('Ver Agrupados');
					$("#listState").removeAttr("disabled");
					$("#listPlant").removeAttr("disabled");
					$("#tableDataAgrupado").css("display","none");
					$("#tableData").css("display","block");
					loadData();
				}
			});			

			$("#undoSettlement").click(function() {
				undoSettlement($("#labelIDView").text());
			});

			$("#btnPrintSelected").click(function() {
				var count = 0;
				var listEmployee = '';
				$('#tableData > tbody > tr').each(function() {
					if($(this).find('td').find(">:first-child").is(':checked')){
						if(count==0){
							listEmployee += $($(this).children()[3]).html().split('-')[0];
						}else{
							listEmployee += ','+$($(this).children()[3]).html().split('-')[0];
						}
						count++;
					}
				});
				if(count>0){
					generatePDF('group',listEmployee);
				}else{				
					$("#modal-text").text("Debe seleccionar 1 o más trabajadores");
					$("#modal").modal('show');
				}
			});

			loadPlant();
			loadData();
			loadEndCause();
		});

		function loadData(){
			$("#modalProgress-text").html('<i class="fa fa-spinner fa-spin fa-2x"></i><br/>Cargando Registros');
			$("#modalProgress").modal('show');
			$("#tableData").html('<thead><tr>' +
				'<th>Sel.</th>' +
				'<th>Empresa</th>' +
				'<th>Campo</th>' +
				'<th>RUT</th>' +
				'<th>Nombre</th>' +
				'<th>Stat</th>' +
				'<th>Sueldo Base (Ficha)</th>' +
				'<th>Inicio</th>' +
				'<th>Fin</th>' +
				'<th>Creación Finiquito</th>' +
				'<th>Vac.</th>' +
				'<th>Ver</th>' +
				'</tr></thead>');			

				var plant = 98;
				if($("#listPlant").val()!=null){
					plant = $("#listPlant").val();
				}


			$('#tableData').dataTable({
				destroy: true,
				pageLength: 50,
				language: { "url": "../../libs/datatables/language/Spanish.json"},
                ajax: {
		            "url": "../../phps/settlement_Load.php",
		            "type": "POST",
		            "data": {
		            	type: "all", 
		            	state: $("#listState").val(), 
		            	plant: plant, 
		            	startDate: $("#txtDateStart").val(), 
		            	endDate: $("#txtDateEnd").val()
					},
		            "dataSrc": ""
		        },
		        columnDefs: [
					{
						targets: [5,6,7,8,9,10,11],
						className: 'text-right'
				    }
				],
                columns: [
					{"data" : "select"},
					{"data" : "enterprise"},
					{"data" : "plant"},
					{"data" : "rut"},
					{"data" : "fullname"},
					{"data" : "status"},
					{"data" : "salary"},
					{"data" : "contractStart"},
					{"data" : "contractEnd"},
					{"data" : "settlementDate"},
					{"data" : "vacationDays"},
					{"data" : "view"}
                ],
                "fnInitComplete": function(oSettings, json) {
					$("#modalProgress").modal('hide');
			    }
            });


			$.post('../../phps/settlement_Load.php', {type: "all", state: $("#listState").val(), plant: plant, startDate: $("#txtDateStart").val(), endDate: $("#txtDateEnd").val()}, function(data, textStatus, xhr) {

				if(data!=0){
					var data = JSON.parse(data);
					/*var dynatable = $("#tableData").dynatable({
				  		dataset: {
				    		records: data
				  		}
					}).data('dynatable');
	                dynatable.settings.dataset.originalRecords = data;
	                dynatable.process();
					$(function () {//Inicializa popover
						$('[data-toggle="popover"]').popover()
					});*/
					var list = "";

					if($("#listState").val()=="V"){
						$("#tableDataExcel").html('<tr><th>Empresa</th><th>Campo</th><th>RUT</th><th>Nombre</th><th>Cargo</th><th>Estado</th><th>Duración</th><th>Inicio</th><th>Fin</th><th>Vacaciones Usadas</th></tr>');
						for(i=0;i<data.length;i++){
							var total=(parseInt(data[i]['vacationAmount'])+parseInt(data[i]['salaryPayment'])+parseInt(data[i]['salaryService'])+parseInt(data[i]['salaryAdvice'])+parseInt(data[i]['salaryVoluntary']))-(parseInt(data[i]['loanEnterprise'])+parseInt(data[i]['loanCompensation'])+parseInt(data[i]['afc']));


							list = "<tr id='id"+data[i]['rut']+"'>";
							list += "<td>"+data[i]['enterprise']+"</td>";
							list += "<td>"+data[i]['plant']+"</td>";
							list += "<td>"+data[i]['rut']+"</td>";
							list += "<td>"+data[i]['name']+" "+data[i]['lastname1']+" "+data[i]['lastname2']+"</td>";
							list += "<td>"+data[i]['charge']+"</td>";
							list += "<td>"+data[i]['status']+"</td>";
							list += "<td>"+data[i]['duration']+"</td>";
							list += "<td>"+data[i]['contractStart']+"</td>";
							list += "<td>"+data[i]['contractEnd']+"</td>";
							list += "<td>"+data[i]['vacationDays']+"</td></tr>";
							$("#tableDataExcel").append(list);
						}
					}else{
						$("#tableDataExcel").html('<tr><th>Empresa</th><th>Campo</th><th>RUT</th><th>Nombre</th><th>Cargo</th><th>Estado</th><th>Duración</th><th>Inicio</th><th>Fin</th><th>Mes Liquidación</th><th>Vacaciones Proporcionales</th><th>Liquidación</th><th>Indemnización Años de Servicio</th><th>Indemnización Aviso</th><th>Indemnización Voluntaria</th><th>Ind. por Mes Trabajado</th><th>Préstamo Empresa</th><th>Préstamo Caja</th><th>AFC</th><th>Total</th></tr>');
						for(i=0;i<data.length;i++){
							var total=(parseInt(data[i]['vacationAmount'])+parseInt(data[i]['salaryPayment'])+parseInt(data[i]['salaryService'])+parseInt(data[i]['salaryAdvice'])+parseInt(data[i]['salaryVoluntary'])+parseInt(data[i]['salaryMonth']))-(parseInt(data[i]['loanEnterprise'])+parseInt(data[i]['loanCompensation'])+parseInt(data[i]['afc']));


							list = "<tr id='id"+data[i]['rut']+"'>";
							list += "<td>"+data[i]['enterprise']+"</td>";
							list += "<td>"+data[i]['plant']+"</td>";
							list += "<td>"+data[i]['rut']+"</td>";
							list += "<td>"+data[i]['name']+" "+data[i]['lastname1']+" "+data[i]['lastname2']+"</td>";
							list += "<td>"+data[i]['charge']+"</td>";
							list += "<td>"+data[i]['status']+"</td>";
							list += "<td>"+data[i]['duration']+"</td>";
							list += "<td>"+data[i]['contractStart']+"</td>";
							list += "<td>"+data[i]['contractEnd']+"</td>";
							list += "<td>"+data[i]['salaryPaymentDate']+"</td>";
							list += "<td>"+data[i]['vacationAmount']+"</td>";
							list += "<td>"+data[i]['salaryPayment']+"</td>";
							list += "<td>"+data[i]['salaryService']+"</td>";
							list += "<td>"+data[i]['salaryAdvice']+"</td>";
							list += "<td>"+data[i]['salaryVoluntary']+"</td>";
							list += "<td>"+data[i]['salaryMonth']+"</td>";
							list += "<td>"+data[i]['loanEnterprise']+"</td>";
							list += "<td>"+data[i]['loanCompensation']+"</td>";
							list += "<td>"+data[i]['afc']+"</td>";
							list += "<td>"+total+"</td></tr>";
							$("#tableDataExcel").append(list);
						}
					}
					$("#modalProgress").modal('hide');
				}else{
					$("#modalProgress").modal('hide');
				}
			});
		}


		function loadDataGroup(){
			$("#modalProgress-text").html('<i class="fa fa-spinner fa-spin fa-2x"></i><br/>Cargando Registros');
			$("#modalProgress").modal('show');
			$("#tableDataAgrupado").html('<thead><tr>' +
				'<th>ID</th>' +
				'<th>Fecha Creación</th>' +
				'<th>Fecha Finiquito</th>' +
				'<th>Artículo asociado</th>' +
				'<th>Cant. Personal</th>' +
				'<th>Ver</th>' +
				'</tr></thead><tbody id="tableDataAgrupadoBody"></tbody>');			

				var plant = 98;
				if($("#listPlant").val()!=null){
					plant = $("#listPlant").val();
				}


			$('#tableData').dataTable({
				destroy: true,
				paging: false,
				language: { "url": "../../libs/datatables/language/Spanish.json"},
                ajax: {
		            "url": "../../phps/settlement_Load.php",
		            "type": "POST",
		            "data": {
		            	type: "list", 
		            	state: $("#listState").val(), 
		            	plant: plant
					},
		            "dataSrc": ""
		        },
		        /*columnDefs: [
					{
						targets: [5,6,7,8,9,10,11],
						className: 'text-right'
				    }
				],*/
                columns: [
					{"data" : "id"},
					{"data" : "fecha_creacion"},
					{"data" : "fecha_finiquito"},
					{"data" : "articulo"},
					{"data" : "cantidad"},
					{"data" : "view"}
                ],
                "fnInitComplete": function(oSettings, json) {
					$("#modalProgress").modal('hide');
			    }
            });
			/*$.post('../../phps/settlement_Load.php', {type: "list", state: $("#listState").val(), plant: plant}, function(data, textStatus, xhr) {
				if(data!=0){
					var data = JSON.parse(data);
					var dynatable = $("#tableDataAgrupado").dynatable({
				  		dataset: {
				    		records: data
				  		}
					}).data('dynatable');
	                dynatable.settings.dataset.originalRecords = data;
	                dynatable.process();
					$(function () {//Inicializa popover
						$('[data-toggle="popover"]').popover()
					});
					var list = "";

					$("#modalProgress").modal('hide');
				}else{
					$("#modalProgress").modal('hide');
				}
			});*/
		}

		function viewRow(id){
			$("#modalView").modal('show');
			$.post('../../phps/settlement_Load.php', {type: "one", id: id}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				$('#txtViewRUT').val(data[0]['rut']);
				$('#txtViewName').val(data[0]['name']);
				for(i=0;i<data.length;i++){
					if(data[i]['enterprise_initials']!=null){
						var dateStart = data[i]['contract_start'].split('/');
						var dateEnd = data[i]['contract_end'].split('/');
						if(data[i]['contract_start'][2]=='-'){
							dateStart = data[i]['contract_start'].split('-');
							dateEnd = data[i]['contract_end'].split('-');
						}
						var start = moment([dateStart[2],dateStart[1]-1,dateStart[0]]);
						var end = moment([dateEnd[2],dateEnd[1]-1,dateEnd[0]]);
						var days = end.diff(start, 'days')+1;

						list = '<tr>';
						list += '<td>'+data[i]['enterprise_initials']+'</td>';
						list += '<td>'+data[i]['plant']+'</td>';
						list += '<td style="text-align: right;">'+data[i]['sueldo_base']+'</td>';
						list += '<td style="text-align: center;">'+data[i]['contract_start']+'</td>';
						list += '<td style="text-align: center;">'+data[i]['contract_end']+'</td>';
						list += '<td style="text-align: center;">'+data[i]['articulo']+'</td>';
						list += '<td style="text-align: center;">'+days+'</td>';
						list += '<td style="text-align: right;">'+data[i]['vacaciones_proporcionales']+'</td>';
						if(data[i]['pago_estado']==undefined){
							list += '<td style="text-align: right;">Pendiente</td>';
						}else{
							list += '<td style="text-align: right;">'+data[i]['pago_estado']+'</td>';
						}
						list += '<td><button class="btn btn-danger btn-sm" onclick="generatePDF(\'one\','+data[i]['ID']+')"><i class="fa fa-file-pdf-o fa-lg fa-fw"></i></button></td></tr>';

						$("#tableSettlement").append(list);

						if(i==data.length-1){
							$('#labelIDView').text(data[i]['ID']);
						}
					}
				}

			});
		}

		function addRowPersonal(enterprise, plant, rut, name, salary, startDate, vacationDays){
			var tds = '<tr id="sel'+rut+'">';
				tds += '<td>'+enterprise+'</td>';
				tds += '<td>'+plant+'</td>';
				tds += '<td style="text-align: right;">'+rut+'</td>';
				tds += '<td>'+name+'</td>';
				tds += '<td style="text-align: right;">'+salary+'</td>';
				tds += '<td style="text-align: center;">'+startDate+'</td>';
				tds += '<td style="text-align: center;">'+vacationDays+'</td>';
				tds += '<td style="text-align: right;"></td>';
				tds += '<td style="text-align: right;"></td>';
				tds += '<td style="text-align: right;"></td>';
				tds += '<td style="text-align: right;"></td>';
				tds += '<td style="text-align: right;"></td>';
				tds += '<td style="text-align: right;"></td>';
				tds += '<td style="text-align: right;"></td>';
				tds += '<td style="text-align: right;"></td>';
				tds += '<td style="text-align: right;"></td>';
				tds += '<td style="text-align: right;"></td>';
				tds += '<td style="text-align: right;"><input type="number" style="text-align: right;" value="0"/></td>';
				tds += '<td style="text-align: right;"></td>';
				tds += '<td style="text-align: right;"><input type="number" style="text-align: right;" value="0"/></td>';
				tds += '<td style="text-align: right;"><input type="number" style="text-align: right;" value="0"/></td>';
				tds += '<td style="text-align: right;"><input type="text" value="TRABAJADOR AGRICOLA"/></td>';
				tds += '</tr>';
				$("#tableSelected").append(tds);
		}
		
		function generatePDF(type,id){
			$("#modal-pdf-type").text(type);
			$("#modal-pdf-id").text(id);

			$("#modalPDF").modal('show');
		}

		function generatePDFLink(type,id,date){
			window.open("format_pdf.php?type="+type+"&id="+id+"&date="+date);
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

		function loadEndCause(){
			$.post('../../phps/endCause_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					$("#listEndCause").append('<option value="'+data[i]["finiq_codigo"]+'">'+data[i]["finiq_codigo"]+' - '+data[i]["finiq_descrip"]+'</option>');
				}
			});
		}

		function deleteRow(id){
			$("#modal-delete-text").text('¿Está seguro de eliminar el registro '+id+'?');
			$("#modal-delete-id").text(id);
			$("#modalDelete").modal('show');
		}

		function undoSettlement(id){
			$("#modal-delete-text").text('¿Está seguro de deshacer el último finiquito? (ID: '+id+')');
			$("#modal-delete-id").text(id);
			$("#modalDelete").modal('show');
		}

		function cleanModal(){
			$("#divID").css('display','none');
			$("#labelID").text('');
			$("#labelState").text('DISPONIBLE');
			$("#modalNew").modal('hide');
			$('#generatePDF').attr('disabled', 'true');
			$('#txtFireDate').val(moment().format('DD/MM/YYYY'));
			calculation = false;
			$('#tableSelected').html('<thead><tr><th>Empresa</th><th>Campo</th><th>RUT</th><th>Nombre</th><th>Sueldo Base (Ficha)</th><th>Inicio Contrato</th><th>Vacaciones Usadas</th><th>Días Trab.</th><th>Liquidación</th><th>Sueldo Base (Íntegro)</th><th>Gratificación</th><th>Colación</th><th>Movilización</th><th>Vacaciones Prop.</th><th>Indemnización Años de Servicio</th><th>Indemnización Sustitutiva del Aviso Previo</th><th>Indemnización por mes trabajado (Obra/Faena)</th><th>Indemnización Voluntaria</th><th>Descuento Préstamo Empresa</th><th>Descuento Préstamo Caja de Compensación</th><th>Descuento aporte AFC</th><th>Cargo</th></tr></thead><tbody></tbody>');
			loadData();
		}


		function progressBar(){
			$.post('../../phps/parameters_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				/*var data = JSON.parse(data);
				console.log(data[0]['ValorA']);*/
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
				<div class="panel-heading"><i class="fa fa-user-times fa-lg fa-fw"></i>&nbsp;&nbsp; Finiquitos</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<button id="new" class="btn btn-primary btn-sm"><i class="fa fa-user-times fa-lg fa-fw"></i>&nbsp;&nbsp; Crear Finiquitos</button></td>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<label style="font-size: 12px;">Estado contrato:</label>
				    	    <select id="listState" class="form-control input-sm">
		  						<option value="V">VIGENTE</option>
								<option value="S">FINIQUITADO</option>
								<!--<option value="T">TODOS</option>-->
							</select>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<label style="font-size: 12px;">Campo:</label>
				    	    <select id="listPlant" class="form-control input-sm">
							</select>
						</div>

						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<label style="font-size: 12px;">Fecha Desde:</label>
							<div class="input-group input-group-sm">
								<input id="txtDateStart" type="text" class="form-control datepickerTxt dateFilter" disabled>
								<span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></span>
							</div>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<label style="font-size: 12px;">Hasta:</label>
							<div class="input-group input-group-sm">
								<input id="txtDateEnd" type="text" class="form-control datepickerTxt dateFilter" disabled>
								<span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></span>
							</div>
						</div>

						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<button id="toExcel" class="btn btn-success btn-sm">Exportar a Excel  <img src="../../images/excel.ico"/></button>
							<select class="form-control" style="visibility: hidden;"></select>

						</div>

						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<button id="btnSelectAll" class="btn btn-primary btn-sm">Seleccionar Todo</button>
						</div>
						<div class="col-xs-0 col-sm-0 col-md-2 col-lg-2"></div>
						<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4" style="text-align: center;">
							<button id="btnPrintSelected" class="btn btn-danger btn-sm" disabled><i class="fa fa-file-pdf-o fa-lg fa-fw"></i>&nbsp;&nbsp;Generar PDF Seleccionados</button>
						</div>
						<div class="col-xs-0 col-sm-0 col-md-2 col-lg-2"></div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<!--<button id="btnChangeMode" class="btn btn-primary">Ver Agrupados</button>-->
						</div>
					</div>	
					<br/>
					<br/>
					<div class="table-responsive">
						<table id="tableData" class="table table-hover" style="font-size: 12px;">
						</table>
						<table id="tableDataExcel" style="display: none;">
						</table>
						<table id="tableDataAgrupado" class="table table-hover" style="font-size: 12px; display: none;">
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

	<div id="modalNew" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-xl">
			<div class="modal-content">
	        	<div class="modal-body">
				   	<div id="addNew" class="container-fluid">
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
								<div class="panel panel-primary">
									<div class="panel-heading"><span class="glyphicon glyphicon-copy" aria-hidden="true"></span>&nbsp;&nbsp; Emisión Finiquito</div>
									<div class="panel-body">
										<div class="container-fluid">
											<div class="row">
												<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
													<label>Fecha Finiquito:</label>
					  								<div class="input-group">
														<input id="txtFireDate" type="text" class="form-control datepickerTxt">
														<span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></span>
													</div>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-9 col-lg-9">
										  			<label>Artículo - Causal:</label>
					  								<select id="listEndCause" class="form-control" style="width: 100%;">
					  								</select>
												</div>

												<!--<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
										  			<label>Vacaciones:</label>
					  								<input id="txtVacations" type="Name" class="form-control" maxlength="45">
												</div>
												<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
										  			<label>Indemnización:</label>
					  								<input id="txtCompensation" type="Name" class="form-control" maxlength="45">
												</div>
												<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
										  			<label>Otros:</label>
					  								<input id="txtOthers" type="Name" class="form-control" maxlength="45">
												</div>
												<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
										  			<label>Fórmula:</label>
					  								<input id="txtFormula" type="Name" class="form-control"  style="text-align: center;" maxlength="100" value="((DIAS/30)*1.75)*SBD" disabled>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
										  			<label>Días Mínimos:</label>
										  			<input id="txtMinDays" type="Name" class="form-control numbersOnly" value="30" style="text-align: right;">
												</div>-->
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">&nbsp;<br/></div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4" style="text-align: center;">
													<br/>
													<button id="calculate" class="btn btn-primary"><i class="fa fa-calculator fa-lg fa-fw"></i>&nbsp;&nbsp;Calcular</button>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">&nbsp;<br/></div>
												<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">&nbsp;<br/></div>
												<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
													<div class="panel panel-primary">
														<div class="panel-body">
															<div class="table-responsive" style="overflow: auto; max-height: 500px;">
																<table id="tableSelected" class="table" style="font-size: 12px; width: 1200px;">
																	<thead>
																		<tr>
																			<th>Empresa</th>
																			<th>Campo</th>
																			<th>RUT</th>
																			<th>Nombre</th>
																			<th>Sueldo Base (Ficha)</th>
																			<th>Inicio Contrato</th>
																			<th>Vacaciones Usadas</th>
																			<th>Días Trab.</th>
																			<th>Liquidación</th>
																			<th>Sueldo Base (Íntegro)</th>
																			<th>Gratificación</th>
																			<th>Colación</th>
																			<th>Movilización</th>
																			<th>Vacaciones Prop.</th>
																			<th>Indemnización Años de Servicio</th>
																			<th>Indemnización Sustitutiva del Aviso Previo</th>
																			<th>Indemnización por mes trabajado (Obra/Faena)</th>
																			<th>Indemnización Voluntaria</th>
																			<th>Descuento Préstamo Empresa</th>
																			<th>Descuento Préstamo Caja de Compensación</th>
																			<th>Descuento aporte AFC</th>
																			<th>Cargo</th>
																		</tr>
																	</thead>
																	<tbody>
																	</tbody>
																</table>
															</div>
														</div>
													</div>
												</div>
											</div>
											<br/>
											<div class="row">
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4"></div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4" style="text-align: center;">
													<button id="save" class="btn btn-redto"><img src="../../images/fire.png"/><br/>Finiquitar</button>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">&nbsp;<br/>&nbsp;<br/>&nbsp;<br/>&nbsp;</div>

												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4"></div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4" style="text-align: center;">
													<button id="generatePDF" class="btn btn-danger" disabled><i class="fa fa-file-pdf-o fa-lg fa-fw"></i>&nbsp;&nbsp;Generar PDF</button>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4"></div>
											</div>
											<div style="text-align:right;">
												<div style="display:inline-block;"><button id="cancel" class="btn btn-primary"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span>&nbsp;&nbsp; Cancelar</button></div>
											</div>
											<div class="row">
												<div id="divID" class="col-xs-4 col-sm-4 col-md-4 col-lg-4" style="display: none;">
										  			<label>ID:</label>
										  			<label id="labelID"></label>
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
		</div>
	</div>

	<div id="modalView" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
	        	<div class="modal-body">
					<div class="panel panel-primary">
						<div class="panel-heading"><i class="fa fa-eye fa-lg fa-fw"></i>&nbsp;&nbsp; Ver Finiquitos</div>
						<div class="panel-body">
							<div class="container-fluid">
								<div class="row">
									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							  			<label style="font-size: 12px">RUT:</label>
		  								<input id="txtViewRUT" type="Name" class="form-control input-sm" style="text-align: right;" disabled>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
							  			<label style="font-size: 12px">Nombre:</label>
							  			<input id="txtViewName" type="Name" class="form-control input-sm" disabled>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">&nbsp;<br/></div>
									<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
										<div class="panel panel-primary">
											<div class="panel-body">
												<table id="tableSettlement" class="table" style="font-size: 12px;">
													<thead>
														<tr>
															<th>Empresa</th>
															<th>Campo</th>
															<th>Sueldo Base (Ficha)</th>
															<th>Inicio Contrato</th>
															<th>Fin Contrato</th>
															<th>Causa</th>
															<th>Días Trab.</th>
															<th>Vacaciones Prop.</th>
															<th>Estado Pago</th>
															<th>PDF</th>
														</tr>
													</thead>
													<tbody>
													</tbody>
												</table>
											</div>
										</div>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4" style="text-align: center;">
										<button id="undoSettlement" class="btn btn-warning btn-sm"><i class="fa fa-reply fa-lg fa-fw"></i>&nbsp;&nbsp;Deshacer Último Finiquito</button>
									</div>
										<div id="divID" class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
								  			<label>ID:</label>
								  			<label id="labelIDView"></label>
										</div>		
									</div>
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
</body>
</html>