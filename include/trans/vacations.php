<?php
header('Content-Type: text/html; charset=utf8'); 
session_start();

if(!isset($_SESSION['userId'])){
	header('Location: ../../login.php');
}elseif($_SESSION['display']['vacations']['view']!=''){
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

			$("#toPDFAll").click(function() {
				toPDFAll();
			});

			$("#toExcelAll").click(function() {
				toExcelAll();
			});

			$("#toExcelAllVacation").click(function() {
				toExcelAllVacation();
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

			$("#modalDeleteHide").click(function() {
				$("#modalDelete").modal('hide');	
			});

			$("#modalVacationHide").click(function() {
				$("#modalVacation").modal('hide');	
			});

			$("#delete").click(function() {
				var id = $("#modal-delete-id").text();
				$.post('../../phps/vacation_Save.php', {type: 'delete', id: id}, function(data, textStatus, xhr) {
					if(data=='OK'){
						var id = $("#txtViewRUT").val().split('-');
						loadRow(id[0], $("#txtViewRUT").val(), $("#txtViewName").val());
						$("#modalDelete").modal('hide');
						$("#modal-text").text("Registro Eliminado Satisfactoriamente");
						$("#modal").modal('show');
					}else{
						$("#modalDelete").modal('hide');
						$("#modal-text").text("Ha ocurrido un error, contacte al administrador");
						$("#modal").modal('show');
					}
				});
			});


			$("#cancelView").click(function() {
				cleanModal();
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
			$("#btnCancelVacation").click(function() {
				$('#tableVacations > tbody > tr').each(function() {
					if($(this).hasClass("success")){
						cleanEdit($(this).attr("id"));
					}
				});
			});

			$(".vacationDate").on('changeDate', function(ev) {
				calculate($(this).attr("id"));
			});

			$(".vacationDate").focusout(function() {
				calculate($(this).attr("id"));
			});

			$("#btnSaveVacation").click(function(){
				if(calculate("txtVacationReturn")){
					if($("#txtVacationDayPending").val()>0){
						saveVacation();
						$("#modalVacation").modal('hide');
					}else{
						$("#modal-vacation-text").text('Las vacaciones solicitadas superan la cantidad de días disponibles del trabajador ¿Desea continuar?');
						$("#modalVacation").modal('show');
					}
				}
			});

			$("#saveVacation").click(function(){
				saveVacation();
				$("#modalVacation").modal('hide');
			});

			$("#rbExcelAll1").click(function(){
				if($(this).is(':checked')){
					$("#txtDateExcelAll").val(moment().format('DD/MM/YYYY'));
					$("#txtDateExcelAll").attr('disabled','disabled');
				}
			});

			$("#rbExcelAll2").click(function(){
				if($(this).is(':checked')){
					$("#txtDateExcelAll").removeAttr('disabled');
				}
			});

			loadYear();
			loadPlant();
			loadEnterprise();
			loadData();
		});

		function loadData(){
			$("#tableData").html('<thead><tr>' +
				'<th>Campo</th>' +
				'<th>RUT</th>' +
				'<th>Nombre</th>' +
				'<th>Inicio</th>' +
				'<th>Fin</th>' +
				'<th>Ver</th>' +
				'<th>Resumen</th>' +
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
		            "url": "../../phps/vacation_Load.php",
		            "type": "POST",
		            "data": {
		            	type: "all", 
		            	state: $("#listState").val(), 
		            	plant: plant, 
		            	enterprise: $("#listEnterprise").val()
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
					{"data" : "plant"},
					{"data" : "rut"},
					{"data" : "fullname"},
					{"data" : "contractStart"},
					{"data" : "contractEnd"},
					{"data" : "view"},
					{"data" : "excel"}
                ],
                "fnInitComplete": function(oSettings, json) {
					$("#modalProgress").modal('hide');
			    }
            });

			$.post('../../phps/vacation_Load.php', {type: "all", state: $("#listState").val(), plant: plant, enterprise: $("#listEnterprise").val()}, function(data, textStatus, xhr) {
				
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
					$("#tableDataExcel").html('<tr><th>Empresa</th><th>Campo</th><th>RUT</th><th>Apellido Paterno</th><th>Apellido Materno</th><th>Nombres</th><th>Cargo</th><th>Estado</th><th>Duración</th><th>Inicio</th><th>Fin</th></tr>');
					for(i=0;i<data.length;i++){
						list = "<tr id='id"+data[i]['rut']+"'>";
						list += "<td>"+data[i]['enterprise']+"</td>";
						list += "<td>"+data[i]['plant']+"</td>";
						list += "<td>"+data[i]['rut']+"</td>";
						list += "<td>"+data[i]['lastname1']+"</td>";
						list += "<td>"+data[i]['lastname2']+"</td>";
						list += "<td>"+data[i]['name']+"</td>";
						list += "<td>"+data[i]['charge']+"</td>";
						list += "<td>"+data[i]['status']+"</td>";
						list += "<td>"+data[i]['duration']+"</td>";
						list += "<td>"+data[i]['contractStart']+"</td>";
						list += "<td>"+data[i]['contractEnd']+"</td></tr>";
						$("#tableDataExcel").append(list);
					}
				}
			});
		}

		function viewRow(id, rut, name){
			loadPeriod(id);
			loadRow(id, rut, name);
		}

		function loadRow(id, rut, name){
			$("#modalView").modal('show');
			if($("#listPeriod").val()!=0){
				$("#btnSaveVacation").attr('disabled','disabled');
			}else{
				$("#btnSaveVacation").removeAttr('disabled');
			}
			$.post('../../phps/vacation_Load.php', {type: "one", id: id, period: $("#listPeriod").val()}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				$('#txtViewRUT').val(rut);
				$('#txtViewName').val(name);
				if(data[0]['contract_entry'][2]=='-'){
					var contract_entry = data[0]['contract_entry'].split('-');
					$('#txtContractEntry').val(contract_entry[0]+'/'+contract_entry[1]+'/'+contract_entry[2]);
				}else{
					$('#txtContractEntry').val(data[0]['contract_entry']);
				}
				var dateStart = $('#txtContractEntry').val().split('/');
				var dateEnd = $('#txtToday').val().split('/');
				var start = moment([dateStart[2],dateStart[1]-1,dateStart[0]]);
				var end = moment([dateEnd[2],dateEnd[1]-1,dateEnd[0]]);
				var days = end.diff(start, 'days')+1;
				if(days>=30){
					//$("#txtVacationsTotal").val(((days/365)*12*1.25).toFixed(4));
					$("#txtVacationsTotal").val(((15/12/30)*(days)).toFixed(4));
				}else{
					$("#txtVacationsTotal").val(0);
				}
				var used = 0;
				
				$("#tableVacations").html('<thead><tr><th>Inicio</th><th>Fin</th><th>Días Hábiles</th><th>Días Inhábiles</th><th>Reintegración</th><th>Progresivos</th><th>Período</th><th>PDF</th><th>Editar</th><th>Eliminar</th><th>Ver</th></tr></thead><tbody></tbody>');

				if(data[0]['FechaInicio']==undefined){
					$("#txtVacationsPending").val($("#txtVacationsTotal").val());
				}else{
					for(i=0;i<data.length;i++){
						var list = '<tr id="row'+data[i]['ID']+'">';

						if(data[0]['contract_start'][2]=='-'){
							var FechaInicio = data[i]['FechaInicio'].split('-');
							var FechaFin = data[i]['FechaFin'].split('-');
							var FechaReintegracion = data[i]['FechaReintegracion'].split('-');
							list +='<td>'+FechaInicio[0]+'/'+FechaInicio[1]+'/'+FechaInicio[2]+'</td>';
							list +='<td>'+FechaFin[0]+'/'+FechaFin[1]+'/'+FechaFin[2]+'</td>';
							list +='<td>'+data[i]['Dias_Habiles']+'</td>';
							list +='<td>'+data[i]['Dias_Inhabiles']+'</td>';
							list +='<td>'+FechaReintegracion[0]+'/'+FechaReintegracion[1]+'/'+FechaReintegracion[2]+'</td>';
						}else{
							list +='<td>'+data[i]['FechaInicio']+'</td>';
							list +='<td>'+data[i]['FechaFin']+'</td>';
							list +='<td>'+data[i]['Dias_Habiles']+'</td>';
							list +='<td>'+data[i]['Dias_Inhabiles']+'</td>';
							list +='<td>'+data[i]['FechaReintegracion']+'</td>';
						}

						list +='<td>'+data[i]['Dias_Progresivos']+'</td>';
						list +='<td>'+data[i]['Periodo']+'</td>';
						list +='<td><button class="btn btn-danger btn-sm vacationButton" onclick="generatePDF('+data[i]['ID']+')"><i class="fa fa-file-pdf-o fa-lg fa-fw"></i></button></td>';
						if(i+1==data.length && $("#listPeriod").val()==0){
							list +='<td><button class="btn btn-warning btn-sm vacationButton" onclick="editRow(\'row'+data[i]['ID']+'\')"><i class="fa fa-edit fa-lg fa-fw"></i></button></td>';
							list +='<td><button class="btn btn-danger btn-sm vacationButton" onclick="deleteRow(\'row'+data[i]['ID']+'\')"><i class="fa fa-remove fa-lg fa-fw"></i></button></td>';
						}else{
							list +='<td></td><td></td>';
						}
						
						if(data[i]['link']==''){
							list += '<td><button class="btn btn-warning btn-sm vacationButton" title="Pendiente ID: '+data[i]['ID']+'" onclick="uploadFile(\'upload\','+data[i]['ID']+')"><i class="fa fa-upload fa-lg fa-fw"></i></button></td></tr>';
						}else{
							list += '<td><button class="btn btn-success btn-sm vacationButton" title="Finalizado ID: '+data[i]['ID']+'" onclick="viewFile(\''+data[i]['link']+'\')"><i class="fa fa-file-text fa-lg fa-fw"></i></button></td></tr>';
						}

						list +='</tr>';
						
						used += parseInt(data[i]['Dias_Habiles']);
						used -= parseInt(data[i]['Dias_Progresivos']);
						$("#tableVacations").append(list);
					}
				}
				
				//$("#txtVacationsPending").val((((days/365)*12*1.25)-used).toFixed(4));
				
				$("#txtVacationsPending").val((((15/12/30)*(days))-used).toFixed(4));
				$("#txtVacationsUsed").val($("#txtVacationsTotal").val()-$("#txtVacationsPending").val());

			});
		}

		function editRow(id){
			$('#txtVacationStart').val($($('#'+id).children()[0]).html());
			$('#txtVacationEnd').val($($('#'+id).children()[1]).html());
			$('#txtVacationReturn').val($($('#'+id).children()[4]).html());
			$('#txtVacationDayProgressive').val($($('#'+id).children()[5]).html());
			$('#txtVacationDayBusiness').val($($('#'+id).children()[2]).html());
			$('#txtVacationDayNoBusiness').val($($('#'+id).children()[3]).html());
			$('#txtVacationDayTotal').val(parseInt($($('#'+id).children()[2]).html())+parseInt($($('#'+id).children()[3]).html()));
			var period = $($('#'+id).children()[6]).html().split(' - ');
			$('#listPeriodYear1').val(period[0]);
			$('#listPeriodYear2').val(period[1]);
			$('#'+id).addClass('success');
			$('.vacationButton').attr('disabled','disabled');
			$('#btnCancelVacation').removeAttr('disabled');
			calculate();
			/*var dateRow = $($(this).children()[1]).html().split('/');
			if(start<=moment([dateRow[2],dateRow[1]-1,dateRow[0]])){
				lastVacation=false;
			}*/
		}

		function deleteRow(idrow){
			var id = idrow.split('row');
			$("#modal-delete-text").text('¿Está seguro de eliminar el registro?');
			$("#modal-delete-id").text(id[1]);
			$("#modalDelete").modal('show');
		}

		function calculate(id){
			//UUUU
			var dateStart = $('#txtVacationStart').val().split('/');
			var dateEnd = $('#txtVacationEnd').val().split('/');
			var start = moment([dateStart[2],dateStart[1]-1,dateStart[0]]);
			var end = moment([dateEnd[2],dateEnd[1]-1,dateEnd[0]]);
			var days = end.diff(start, 'days')+1;
			if(id!='txtVacationReturn'){
				var returnValue = moment(end);
				returnValue.add(1,'days');
				$('#txtVacationReturn').val(returnValue.format('DD/MM/YYYY'));
			}

			var dateReturn = $('#txtVacationReturn').val().split('/');
			var returnDate = moment([dateReturn[2],dateReturn[1]-1,dateReturn[0]]);

			var lastVacation = true;
			var editVacation = false;
			var used = 0;
			$('#tableVacations > tbody > tr').each(function() {
				if($(this).hasClass("success")==false){
					var dateRow = $($(this).children()[1]).html().split('/');
					if(start<=moment([dateRow[2],dateRow[1]-1,dateRow[0]])){
						lastVacation=false;
					}
					used += parseInt($($(this).children()[2]).html());
					used -= parseInt($($(this).children()[5]).html());
				}else{
					editVacation = true;
				}
			});

			var progressive = parseInt($("#txtVacationDayProgressive").val());
			if($("#txtVacationDayProgressive").val()==""){
				progressive = 0;
			}

			$("#txtVacationsPending").val(($("#txtVacationsTotal").val()-used).toFixed(4));
			$("#txtVacationsUsed").val($("#txtVacationsTotal").val()-$("#txtVacationsPending").val());

			if(lastVacation){
				if(days>0){
					if(end<returnDate){
						$('#txtVacationDayTotal').val(days);

						var dayBusiness = 0;
						var dayNoWork = 0;

						var actualdate = moment(start);
						var arrayDate = [];
						arrayDate[0] = moment(start);
						if(arrayDate[0].day()==0 || arrayDate[0].day()==6){
							dayNoWork++;
						}else{
							dayBusiness++;
						}

						for(i=1;i<days;i++){
							arrayDate[i] = moment(actualdate.add(1, 'days'));
							if(arrayDate[i].day()==0 || arrayDate[i].day()==6){
								dayNoWork++;
							}else{
								dayBusiness++;
							}
						}

						$.post('../../phps/vacation_Load.php', {type: "verify", start: $("#txtVacationStart").val(), end: $("#txtVacationEnd").val()}, function(data, textStatus, xhr) {
							if(data!=0){
								var data = JSON.parse(data);
								for(j=0;j<data.length;j++){
									var dateHoliday = data[j]['FechaX'].split('/');
									if(moment([dateHoliday[2],dateHoliday[1]-1,dateHoliday[0]]).day()!=0 && moment([dateHoliday[2],dateHoliday[1]-1,dateHoliday[0]]).day()!=6){
										dayBusiness--;
										dayNoWork++;
									}

									if(j==data.length-1){
										$("#txtVacationDayBusiness").val(dayBusiness);
										$("#txtVacationDayNoBusiness").val(dayNoWork);
										$('#txtVacationDayPending').val($('#txtVacationsPending').val()-dayBusiness);
										$('#txtVacationDayPending').val(parseFloat($('#txtVacationDayPending').val())+progressive);
									}
								}
							}else{
								$("#txtVacationDayBusiness").val(dayBusiness);
								$("#txtVacationDayNoBusiness").val(dayNoWork);
								$('#txtVacationDayPending').val($('#txtVacationsPending').val()-dayBusiness);
								$('#txtVacationDayPending').val(parseFloat($('#txtVacationDayPending').val())+progressive);
							}
						});
						return true;
					}else{
						$('#txtVacationDayPending').val($('#txtVacationsPending').val());
						$('#txtVacationDayPending').val(parseFloat($('#txtVacationDayPending').val())+progressive);
						$('#txtVacationDayTotal').val(0);
						$("#txtVacationDayBusiness").val(0);
						$("#txtVacationDayNoBusiness").val(0);
						$("#modal-text").text("La fecha de reintegración no puede ser menor o igual a la fecha de finalización de vacaciones");
						$("#modal").modal('show');
						return false;
					}
				}else{
					$('#txtVacationDayPending').val($('#txtVacationsPending').val());
					$('#txtVacationDayPending').val(parseFloat($('#txtVacationDayPending').val())+progressive);
					$('#txtVacationDayTotal').val(0);
					$("#txtVacationDayBusiness").val(0);
					$("#txtVacationDayNoBusiness").val(0);					
					$("#modal-text").text("La fecha de inicio no puede ser mayor a la fecha de finalización de vacaciones");
					$("#modal").modal('show');
					return false;
				}
			}else{
				$('#txtVacationDayPending').val($('#txtVacationsPending').val());
				$('#txtVacationDayPending').val(parseFloat($('#txtVacationDayPending').val())+progressive);
				$('#txtVacationDayTotal').val(0);
				$("#txtVacationDayBusiness").val(0);
				$("#txtVacationDayNoBusiness").val(0);					
				$("#modal-text").text("La fecha de inicio se superpone a un período de vacaciones ya utilizado");
				$("#modal").modal('show');
				return false;
			}
		}

		function saveVacation(){
			var type = 'save', id=0, idrow=0;
			var rut = $("#txtViewRUT").val().split('-');
			$('#tableVacations > tbody > tr').each(function() {
				if($(this).hasClass("success")){
					id = $(this).attr("id").split("row")[1];
					idrow = $(this).attr("id");
					type = 'update';
				}
			});

			$.post('../../phps/vacation_Save.php', {type: type,
				id: id,
				Fecha_Inicio: $("#txtVacationStart").val(),
				Fecha_Fin: $("#txtVacationEnd").val(),
				Fecha_Reintegracion: $("#txtVacationReturn").val(),
				Dias_Habiles: $("#txtVacationDayBusiness").val(),
				Dias_Inhabiles: $("#txtVacationDayNoBusiness").val(),
				Periodo_Inicio: $("#listPeriodYear1").val(),
				Periodo_Fin: $("#listPeriodYear2").val(),
				Dias_Progresivos: $("#txtVacationDayProgressive").val(),
				Rut: rut[0]}, function(data, textStatus, xhr) {

				if(idrow!=0){
					cleanEdit(idrow);
				}else{
					var used = 0;
					$('#tableVacations > tbody > tr').each(function() {
						used += parseInt($($(this).children()[2]).html());
						used -= parseInt($($(this).children()[5]).html());
					});
					$("#txtVacationsPending").val(($("#txtVacationsTotal").val()-used).toFixed(4));
					$("#txtVacationsUsed").val($("#txtVacationsTotal").val()-$("#txtVacationsPending").val());
					$("#txtVacationDayBusiness").val('');
					$("#txtVacationDayNoBusiness").val('');
					$("#txtVacationDayTotal").val('');
					$("#txtVacationDayPending").val('');
					$("#txtVacationDayProgressive").val('');
					var d = new Date();
					var n = d.getFullYear();
					$("#listPeriodYear1").val(n-1);
					$("#listPeriodYear2").val(n);
					$(".vacationDate").val(moment().format("DD/MM/YYYY"));
				}
				loadRow(rut[0], $("#txtViewRUT").val(), $("#txtViewName").val());
				
				$("#modal-text").text("Almacenado");
				$("#modal").modal('show');
			});
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
					var idRut = $('#txtViewRUT').val().split('-');
					loadRow(idRut[0],$('#txtViewRUT').val(),$('#txtViewName').val());
				});
			});
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


		function loadEndCause(){
			$.post('../../phps/endCause_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					$("#listEndCause").append('<option value="'+data[i]["finiq_codigo"]+'">'+data[i]["finiq_codigo"]+' - '+data[i]["finiq_descrip"]+'</option>');
				}
			});
		}

		function loadYear(){
			var d = new Date();
			var n = d.getFullYear();
			for(i=1980;i<=n+1;i++){
				$("#listPeriodYear1").append('<option value="'+i+'">'+i+'</option>');
				$("#listPeriodYear2").append('<option value="'+i+'">'+i+'</option>');
			}
			$("#listPeriodYear1").val(n-1);
			$("#listPeriodYear2").val(n);
		}

		function generatePDF(id){
			window.open("vacation_pdf.php?id="+id);
		}

		function cleanModal(){
			$("#divID").css('display','none');
			$("#labelID").text('');
			$("#modalView").modal('hide');
			$("#txtViewRUT").val('');
			$("#txtViewName").val('');
			$("#txtVacationDayBusiness").val('');
			$("#txtVacationDayNoBusiness").val('');
			$("#txtVacationDayTotal").val('');
			$("#txtVacationDayProgressive").val('');
			var d = new Date();
			var n = d.getFullYear();
			$("#listPeriodYear1").val(n-1);
			$("#listPeriodYear2").val(n);
			$(".vacationDate").val(moment().format("DD/MM/YYYY"));
			$("#tableVacations").html('<thead><tr><th>Inicio</th><th>Fin</th><th>Días Hábiles</th><th>Días Inhábiles</th><th>Reintegración</th><th>Progresivos</th><th>Período</th><th>PDF</th><th>Editar</th><th>Eliminar</th></tr></thead><tbody></tbody>');
			loadData();
		}

		function cleanEdit(idrow){
			$('#'+idrow).removeClass('success');
			$('.vacationButton').removeAttr('disabled');
			$('#btnCancelVacation').attr('disabled','disabled');

			/*$($('#'+idrow).children()[0]).html($('#txtVacationStart').val());
			$($('#'+idrow).children()[1]).html($('#txtVacationEnd').val());
			$($('#'+idrow).children()[5]).html($('#txtVacationDayProgressive').val());
			$($('#'+idrow).children()[4]).html($('#txtVacationReturn').val());
			$($('#'+idrow).children()[2]).html($('#txtVacationDayBusiness').val());
			$($('#'+idrow).children()[3]).html($('#txtVacationDayNoBusiness').val());
			var progressive = $('#txtVacationDayProgressive').val();
			if(progressive=="") progressive=0;
			$($('#'+idrow).children()[5]).html(0);
			$($('#'+idrow).children()[6]).html($('#listPeriodYear1').val()+' - '+$('#listPeriodYear2').val());
			var used = 0;
			$('#tableVacations > tbody > tr').each(function() {
				used += parseInt($($(this).children()[2]).html());
				used -= parseInt($($(this).children()[5]).html());
			});
			$("#txtVacationsPending").val(($("#txtVacationsTotal").val()-used).toFixed(4));
*/
			$("#txtVacationDayBusiness").val('');
			$("#txtVacationDayNoBusiness").val('');
			$("#txtVacationDayTotal").val('');
			$("#txtVacationDayPending").val('');
			$("#txtVacationDayProgressive").val('');
			var d = new Date();
			var n = d.getFullYear();
			$("#listPeriodYear1").val(n-1);
			$("#listPeriodYear2").val(n);
			$(".vacationDate").val(moment().format("DD/MM/YYYY"));
		}

///////////////////////EXCEL RESUMEN & DETALLE/////////////////////////
		function toExcelAll() {
			$("#toExcelAll").attr('disabled','disabled');
			$("#toExcelAllSpin").css('display','block');
			var plant = 98;
			if($("#listPlant").val()!=null){
				plant = $("#listPlant").val();
			}

			var date = 0;
			if($("#rbExcelAll2").is(':checked')){
				date = $("#txtDateExcelAll").val();
			}

			$.post('../../phps/vacation_Load.php', {type: "allDetail", state: $("#listState").val(), plant: plant, enterprise: $("#listEnterprise").val(), date: date}, function(data, textStatus, xhr) {
				
				if(data!=0){
					//console.log(data);
					var data = JSON.parse(data);
					//$("#tableAll").html('<thead><tr><th>Campo</th><th>RUT</th><th>Nombre</th><th>Inicio Contrato</th><th>Utilizados</th><th>Pendientes</th></thead><tbody></tbody>');
					$("#tableAll").html('<thead><tr><th>Campo</th><th>RUT</th><th>Nombre</th><th>Inicio Contrato</th><th>Utilizados</th><th>Pendientes</th></thead><tbody></tbody>');
				
					for(i=0;i<data.length;i++){
						var list = '<tr>';
						list += '<td>'+data[i]["plant"]+'</td>';
						list += '<td>'+data[i]["rut"]+'</td>';
						list += '<td>'+data[i]["fullname"]+'</td>';
						if(data[i]["contract_start"][2]=="-"){
							var contract_start = data[i]["contract_start"].split('-');
							list += '<td>'+contract_start[0]+'/'+contract_start[1]+'/'+contract_start[2]+'</td>';
						}else{
							list += '<td>'+data[i]["contract_start"]+'</td>';
						}
						list += '<td>'+data[i]["used"]+'</td>';
						list += '<td>'+data[i]["pending"]+'</td>';
						list += '</tr>';
						$("#tableAll").append(list);
						if(i==data.length-1){
							
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
						}
					}
				}else{
					$("#toExcelAll").removeAttr('disabled');
					$("#toExcelAllSpin").css('display','none');
				}

				
			});
		}

		function toExcelOne(id, rut, name) {
			$.post('../../phps/vacation_Load.php', {type: "one", id: id, period: 0}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				var contract_start = data[0]['contract_start'];
				if(data[0]['contract_start'][2]=='-'){
					contract_start = data[0]['contract_start'].split('-');
					contract_start = contract_start[0]+'/'+contract_start[1]+'/'+contract_start[2];
				}
				var dateStart = contract_start.split('/');
				var dateEnd = moment().format('DD/MM/YYYY').split('/');
				var start = moment([dateStart[2],dateStart[1]-1,dateStart[0]]);
				var end = moment([dateEnd[2],dateEnd[1]-1,dateEnd[0]]);
				var days = end.diff(start, 'days')+1;
				var vacationsTotal = 0
				if(days>=30){
					vacationsTotal = ((15/12/30)*(days)).toFixed(4);
				}
				var used = 0;
				
				var headTo = '';
				//headTo +='<tr><td colspan="8"></td></tr>';
				headTo += '<tr>';
				headTo +='<th>RUT</th>';
				headTo +='<th colspan="3">Nombre</th>';
				headTo +='<th>Inicio Contrato</th>';
				headTo +='<th>Fecha de Hoy</th>';
				headTo +='<th></th>';
				headTo +='<th></th>';
				headTo +='<th></th>';
				headTo +='<th></th>';
				headTo +='</tr>';

				headTo += '<tr>';
				headTo +='<td>'+rut+'</td>';
				headTo +='<td colspan="3">'+name+'</td>';
				if(data[0]['contract_start'][2]=='-'){
					var contract_start = data[0]['contract_start'].split('-');
					headTo +='<td>'+contract_start[0]+'/'+contract_start[1]+'/'+contract_start[2]+'</td>';
				}else{
					headTo +='<td>'+data[0]['contract_start']+'</td>';
				}
				headTo +='<td>'+moment().format('DD/MM/YYYY')+'</td>';
				headTo +='<td></td>';
				headTo +='<td></td>';
				headTo +='<td></td>';
				headTo +='<td></td>';
				headTo +='</tr>';
				headTo +='<tr><td colspan="8"></td></tr>';
				
				$("#tableOne").html('<thead>'+headTo+'</thead><tbody><tr><th>Período</th><th>Inicio</th><th>Fin</th><th>Reintegración</th><th>Días Hábiles</th><th>Días Inhábiles</th><th>Progresivos</th><th>ID</th></tr></tbody>');


				if(data[0]['FechaInicio']==undefined){
					//$("#txtVacationsPending").val($("#txtVacationsTotal").val());
				}else{
					for(i=0;i<data.length;i++){
						var list = '<tr id="row'+data[i]['ID']+'">';
						list +='<td>'+data[i]['Periodo']+'</td>';

						if(data[0]['contract_start'][2]=='-'){
							var FechaInicio = data[i]['FechaInicio'].split('-');
							var FechaFin = data[i]['FechaFin'].split('-');
							var FechaReintegracion = data[i]['FechaReintegracion'].split('-');
							list +='<td>'+FechaInicio[0]+'/'+FechaInicio[1]+'/'+FechaInicio[2]+'</td>';
							list +='<td>'+FechaFin[0]+'/'+FechaFin[1]+'/'+FechaFin[2]+'</td>';
							list +='<td>'+FechaReintegracion[0]+'/'+FechaReintegracion[1]+'/'+FechaReintegracion[2]+'</td>';
							list +='<td>'+data[i]['Dias_Habiles']+'</td>';
							list +='<td>'+data[i]['Dias_Inhabiles']+'</td>';
						}else{
							list +='<td>'+data[i]['FechaInicio']+'</td>';
							list +='<td>'+data[i]['FechaFin']+'</td>';
							list +='<td>'+data[i]['FechaReintegracion']+'</td>';
							list +='<td>'+data[i]['Dias_Habiles']+'</td>';
							list +='<td>'+data[i]['Dias_Inhabiles']+'</td>';
						}

						list +='<td>'+data[i]['Dias_Progresivos']+'</td>';
						list +='<td>'+data[i]['ID']+'</td>';
						list +='</tr>';
						
						used += parseInt(data[i]['Dias_Habiles']);
						used -= parseInt(data[i]['Dias_Progresivos']);
						$("#tableOne").append(list);
					}
				}
				var pending = (((15/12/30)*(days))-used).toFixed(4).toString().replace(".",",");

				$("#tableOne").append('<tr><td></td></tr>');
				$("#tableOne").append('<tr><td></td><th colspan="2">Días Utilizados</th><td>'+used+'<td></tr>');
				$("#tableOne").append('<tr><td></td><th colspan="2">Días Pendientes</th><td>'+pending+'<td></tr>');

				//$("#txtVacationsPending").val((((15/12/30)*(days))-used).toFixed(4));
				//$("#txtVacationsUsed").val($("#txtVacationsTotal").val()-$("#txtVacationsPending").val());
				$("#tableOne").table2excel({
					exclude: ".noExl",
					name: "Excel Document Name",
					filename: "Detalle",
					fileext: ".xls",
					exclude_img: true,
					exclude_links: true,
					exclude_inputs: true
				});
			});
			
		}


		function toExcelAllVacation(){
			var plant = 98;
			if($("#listPlant").val()!=null){
				plant = $("#listPlant").val();
			}
			$.post('../../phps/vacation_Load.php', {type: "AllVacations", state: $("#listState").val(), plant: plant, enterprise: $("#listEnterprise").val()}, function(data, textStatus, xhr) {
				
				if(data!=0){
					var data = JSON.parse(data);
					var list = "";
					$("#tableAllVacation").html('<tr><th>Empresa</th><th>Campo</th><th>RUT</th><th>Apellido Paterno</th><th>Apellido Materno</th><th>Nombres</th><th>Cargo</th><th>Estado</th><th>Duración</th><th>Inicio</th><th>Fin</th><th>Período</th><th>Inicio</th><th>Fin</th><th>Reintegración</th><th>Días Hábiles</th><th>Días Inhábiles</th><th>Progresivos</th><th>ID</th><th>Estado</th></tr>');
					for(i=0;i<data.length;i++){
						list = "<tr id='id"+data[i]['rut']+"'>";
						list += "<td>"+data[i]['enterprise']+"</td>";
						list += "<td>"+data[i]['plant']+"</td>";
						list += "<td>"+data[i]['rut']+"</td>";
						list += "<td>"+data[i]['lastname1']+"</td>";
						list += "<td>"+data[i]['lastname2']+"</td>";
						list += "<td>"+data[i]['name']+"</td>";
						list += "<td>"+data[i]['charge']+"</td>";
						list += "<td>"+data[i]['status']+"</td>";
						list += "<td>"+data[i]['duration']+"</td>";
						list += "<td>"+data[i]['contractStart']+"</td>";
						list += "<td>"+data[i]['contractEnd']+"</td>";
						
						list +='<td>'+data[i]['Periodo']+'</td>';
						if(data[i]['contractStart'][2]=='-'){
							var FechaInicio = data[i]['FechaInicio'].split('-');
							var FechaFin = data[i]['FechaFin'].split('-');
							var FechaReintegracion = data[i]['FechaReintegracion'].split('-');
							list +='<td>'+FechaInicio[0]+'/'+FechaInicio[1]+'/'+FechaInicio[2]+'</td>';
							list +='<td>'+FechaFin[0]+'/'+FechaFin[1]+'/'+FechaFin[2]+'</td>';
							list +='<td>'+FechaReintegracion[0]+'/'+FechaReintegracion[1]+'/'+FechaReintegracion[2]+'</td>';
							list +='<td>'+data[i]['Dias_Habiles']+'</td>';
							list +='<td>'+data[i]['Dias_Inhabiles']+'</td>';
						}else{
							list +='<td>'+data[i]['FechaInicio']+'</td>';
							list +='<td>'+data[i]['FechaFin']+'</td>';
							list +='<td>'+data[i]['FechaReintegracion']+'</td>';
							list +='<td>'+data[i]['Dias_Habiles']+'</td>';
							list +='<td>'+data[i]['Dias_Inhabiles']+'</td>';
						}
						list +='<td>'+data[i]['Dias_Progresivos']+'</td>';
						list +='<td>'+data[i]['ID']+'</td>';
						list +='<td>'+data[i]['vacationStatus']+'</td></tr>';

						$("#tableAllVacation").append(list);

						if(i==data.length-1){
							$("#tableAllVacation").table2excel({
								exclude: ".noExl",
								name: "Excel Document Name",
								filename: "Detalle",
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




		function toPDFAll(){
			var plant = 98;
			if($("#listPlant").val()!=null){
				plant = $("#listPlant").val();
			}
			window.open("vacationAll_pdf.php?type=allDetail&state="+$("#listState").val()+"&plant="+plant+"&enterprise="+$("#listEnterprise").val());
		}

		function toPDFOne(){
			var rut = $("#txtViewRUT").val().split('-');
			window.open("history_pdf.php?type=one&id="+rut[0]);
		}

		function uploadFile(type, id){
			$("#modal-text").text("En construcción");
			$("#modal").modal('show');
			//$("#modalUpload").modal('show');
		}

		function viewFile(link){
			window.open("../"+link);
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
				<div class="panel-heading"><i class="fa fa-image fa-lg fa-fw"></i>&nbsp;&nbsp; Vacaciones</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<label style="font-size: 12px;">Estado contrato:</label>
				    	    <select id="listState" class="form-control input-sm">
								<option value="T">TODOS</option>
		  						<option value="V" selected>VIGENTE</option>
								<option value="S">FINIQUITADO</option>
							</select>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<label style="font-size: 12px;">Campo:</label>
				    	    <select id="listPlant" class="form-control input-sm">
							</select>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<label style="font-size: 12px;">Empresa:</label>
				    	    <select id="listEnterprise" class="form-control input-sm">
							</select>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<input id="rbExcelAll1" type="radio" name="rbExcelAll" checked><label style="font-size: 12px;">&nbsp;Hasta Hoy</label>
							<input id="rbExcelAll2" type="radio" name="rbExcelAll"><label style="font-size: 12px;">&nbsp;Hasta Fecha:</label>
							<input id="txtDateExcelAll" type="text" class="form-control datepickerTxt input-sm" disabled>
							<button id="toExcelAll" class="btn btn-success btn-sm"><i id="toExcelAllSpin" class="fa fa-spinner fa-spin" style="display: none;"></i>&nbsp;Excel Resumen&nbsp;<img src="../../images/excel.ico"/></button>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<button id="toExcelAllVacation" class="btn btn-success btn-sm">Excel Completo&nbsp;<img src="../../images/excel.ico"/></button>
							<select class="form-control" style="visibility: hidden;"></select>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<button id="toExcel" class="btn btn-success btn-sm">Exportar a Excel  <img src="../../images/excel.ico"/></button>
							<select class="form-control" style="visibility: hidden;"></select>
						</div>

						<div class="col-xs-12 col-sm-12 col-md-10 col-lg-10">
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<button id="toPDFAll" class="btn btn-danger btn-sm">PDF Resumen&nbsp;<i class="fa fa-file-pdf-o fa-lg fa-fw"></i></button>
							<select class="form-control" style="visibility: hidden;"></select>
						</div>
						<!--<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<button id="toPDF" class="btn btn-success">Exportar a Excel  <img src="../../images/excel.ico"/></button>
							<select class="form-control" style="visibility: hidden;"></select>
						</div>-->
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


	<div id="modal" class="modal fade" data-backdrop="static" style="z-index: 1055">
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

	<div id="modalDelete" class="modal fade" data-backdrop="static" style="z-index: 1053">
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

	<div id="modalVacation" class="modal fade" data-backdrop="static" style="z-index: 1052">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
	        	<div class="modal-body">
		    	    <p id="modal-vacation-text"></p>
		    	    <p id="modal-vacation-id" style="visibility: hidden"></p>
		      	</div>
		      	<div class="modal-footer">
		        	<button id="saveVacation" type="button" class="btn btn-success">Almacenar</button>
		        	<button id="modalVacationHide" type="button" class="btn btn-primary">Cancelar</button>
		      	</div>
		    </div>
		</div>
	</div>

	<div id="modalView" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
	        	<div class="modal-body">
					<div class="panel panel-primary">
						<div class="panel-heading"><i class="fa fa-file-image-o fa-lg fa-fw"></i>&nbsp;&nbsp; Registro de Vacaciones</div>
						<div class="panel-body">
							<div class="container-fluid">
								<div class="row">
									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							  			<label style="font-size: 12px;">RUT:</label>
		  								<input id="txtViewRUT" type="Name" class="form-control input-sm"  style="text-align: right;" disabled>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
							  			<label style="font-size: 12px;">Nombre:</label>
							  			<input id="txtViewName" type="Name" class="form-control input-sm" disabled>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							  			<label style="font-size: 12px;">Período:</label>
										<select id="listPeriod" class="form-control input-sm">
											<option value="0">Actual</option>
										</select>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							  			<label style="font-size: 12px;">Fecha Ingreso:</label>
		  								<div class="input-group input-group-sm">
											<input id="txtContractEntry" type="text" class="form-control datepickerTxt" disabled>
											<span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></span>
										</div>
										<!--<label>Inicio Contrato:</label>
		  								<div class="input-group">
											<input id="txtContractStart" type="text" class="form-control datepickerTxt" disabled>
											<span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></span>
										</div>-->
									</div>
									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							  			<label style="font-size: 12px;">Fecha Actual:</label>
		  								<div class="input-group input-group-sm">
											<input id="txtToday" type="text" class="form-control datepickerTxt" disabled>
											<span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></span>
										</div>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							  			<label style="font-size: 12px;">Acumulados:</label>
							  			<input id="txtVacationsTotal" type="Name" class="form-control input-sm" style="text-align: center;" disabled>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							  			<label style="font-size: 12px;">Usados:</label>
							  			<input id="txtVacationsUsed" type="Name" class="form-control input-sm" style="text-align: center;" disabled>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							  			<label style="font-size: 12px;">Pendientes:</label>
							  			<input id="txtVacationsPending" type="Name" class="form-control input-sm" style="text-align: center;" disabled>
									</div>
									<!--<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							  			<label style="visibility: hidden;">Nombre:</label>
										<button id="toExcelHistory" class="btn btn-success">Exportar a Excel  <img src="../../images/excel.ico"/></button>
										<select class="form-control" style="visibility: hidden;"></select>
									</div>-->

									<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">&nbsp;<br/></div>
									<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
										<div class="panel panel-primary">
											<div class="panel-body">
												<div class="table-responsive" style="overflow: auto; max-height: 200px;">
													<table id="tableVacations" class="table" style="font-size: 12px;">
														<thead>
															<tr>
																<th>Inicio</th>
																<th>Fin</th>
																<th>Días Hábiles</th>
																<th>Días Inhábiles</th>
																<th>Reintegración</th>
																<th>Progresivos</th>
																<th>Período</th>
																<th>PDF</th>
																<th>Editar</th>
																<th>Eliminar</th>
															</tr>
														</thead>
														<tbody>
														</tbody>
													</table>
												</div>
											</div>
										</div>
									</div>


									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							  			<label style="font-size: 12px;">Inicio Vacaciones:</label>
		  								<div class="input-group input-group-sm">
											<input id="txtVacationStart" type="text" class="form-control datepickerTxt vacationDate">
											<span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></span>
										</div>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							  			<label style="font-size: 12px;">Fin Vacaciones:</label>
		  								<div class="input-group input-group-sm">
											<input id="txtVacationEnd" type="text" class="form-control datepickerTxt vacationDate">
											<span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></span>
										</div>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							  			<label style="font-size: 12px;">Reintegración:</label>
		  								<div class="input-group input-group-sm">
											<input id="txtVacationReturn" type="text" class="form-control datepickerTxt vacationDate">
											<span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></span>
										</div>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
										<label style="font-size: 12px;">Período:</label>
							  			<select id="listPeriodYear1" class="form-control input-sm">
										</select>
										<select id="listPeriodYear2" class="form-control input-sm">
										</select>
							  		</div>
									
									<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							  			<label style="font-size: 12px;">Progresivos</label>
							  			<input id="txtVacationDayProgressive" type="number" class="form-control vacationDate input-sm" style="text-align: center;">
									</div>
									<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							  			<label style="font-size: 12px;">Días Hábiles</label>
							  			<input id="txtVacationDayBusiness" type="Name" class="form-control input-sm" style="text-align: center;" disabled>
									</div>
									

									<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							  			<label style="font-size: 12px;">Días Inhábiles:</label>
							  			<input id="txtVacationDayNoBusiness" type="Name" class="form-control input-sm" style="text-align: center;" disabled>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							  			<label style="font-size: 12px;">Días Totales</label>
							  			<input id="txtVacationDayTotal" type="Name" class="form-control input-sm" style="text-align: center;" disabled>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							  			<label style="font-size: 12px;">Días Pendientes</label>
							  			<input id="txtVacationDayPending" type="Name" class="form-control input-sm" style="text-align: center;" disabled>
									</div>

									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3"></div>
									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
										<br/>
										<button id="btnSaveVacation" class="btn btn-success btn-sm"><i class="fa fa-image fa-lg fa-fw"></i>&nbsp;&nbsp;Almacenar Registro</button>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
										<br/>
										<button id="btnCancelVacation" class="btn btn-danger btn-sm" disabled><i class="fa fa-remove fa-lg fa-fw"></i>&nbsp;&nbsp;Cancelar</button>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3"></div>

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
	<table id="tableOne" style="display: none;"></table>
	<table id="tableAll" style="display: none;"></table>
	<table id="tableAllVacation" style="display: none;"></table>
</body>
</html>