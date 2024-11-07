<?php
header('Content-Type: text/html; charset=utf8'); 
session_start();

if(!isset($_SESSION['userId'])){
	header('Location: ../../login.php');
}elseif($_SESSION['display']['settlementPayment']['view']!=''){
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
	<link rel="stylesheet" type="text/css" href="../../libs/bootstrap-select/css/bootstrap-select.css"/>
 	<script type="text/javascript" src="../../libs/bootstrap-select/js/bootstrap-select.js"></script>
	<title></title>
	<script type="text/javascript">

	var idList = 1, calculation = false;

		$(document).ready(function() {
			var url1 = window.location.href.split("?");
			var url2, expired_type = '';
			if(url1.length>1){
				var url2 = url1[1].split("=");
				expired_type = url2[1];
			}

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

			/*$(".dateFilter").on('changeDate', function(ev) {
				loadData();
			});
			/*$(".datepickerTxt").focusout(function(){
				$(".datepickerTxt").datepicker('hide');
			});*/

			$("#toExcel").click(function() {
				$("#tablaRegistrosExcel").table2excel({
					exclude: ".noExl",
					name: "Excel Document Name",
					filename: "Lista",
					fileext: ".xls",
					exclude_img: true,
					exclude_links: true,
					exclude_inputs: true
				});
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
			$("#modalUploadHide").click(function() {
				$("#fileToUpload").val('');
				$("#modalUpload").modal('hide');
			});

			$("#modalAdviceHide").click(function() {
				$("#modalAdvice").modal('hide');	
			});

			$("#modalAdviceUndoHide").click(function() {
				$("#modalAdviceUndo").modal('hide');	
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

			$("#modalPDFAdviceHide").click(function() {
				$("#txtPDFAdviceDate").datepicker('setValue', '');
				$("#txtPDFAdviceDate").attr("disabled","disabled");
				$("#checkPDFAdvice").prop('checked', false);
				$("#modalPDFAdvice").modal('hide');	
			});

			$("#checkPDFAdvice").change(function(){
				if($(this).is(':checked')){
					$("#txtPDFAdviceDate").removeAttr("disabled");
				}else{
					$("#txtPDFAdviceDate").attr("disabled","disabled");
				}
			});
		    	
			$("#generatePDFAdviceOK").click(function(){
				var dateAdvice = '-';
				if($("#checkPDFAdvice").is(':checked')){
					generatePDFAdviceLink($("#modal-pdfAdvice-type").text(),$("#modal-pdfAdvice-id").text(),$("#txtPDFAdviceDate").val());
					dateAdvice = $("#txtPDFAdviceDate").val();
				}else{
					generatePDFAdviceLink($("#modal-pdfAdvice-type").text(),$("#modal-pdfAdvice-id").text(),'-');
				}
				$("#txtPDFAdviceDate").datepicker('setValue', '');
				$("#txtPDFAdviceDate").attr("disabled","disabled");
				$("#checkPDFAdvice").prop('checked', false);
				$("#modalPDFAdvice").modal('hide');
				if($("#link"+$("#modal-pdfAdvice-id").text()).hasClass('btn-warning')){
					$("#modalAdvice").modal('show');
					$("#modal-advice-id").text($("#modal-pdfAdvice-id").text());
					$("#modal-advice-date").text(dateAdvice);
				}
			});

			$("#modalAdviceOK").click(function(){
				$.post('../../phps/settlement_Save.php', {type: 'updateAdvice', 
					id: $("#modal-advice-id").text(),
					date: $("#modal-advice-date").text(),
					state: 'AVISADO'
				}, function(data, textStatus, xhr) {
					if(data=='OK'){
						$("#modalAdvice").modal('hide');
						$("#modal-text").text("Almacenado");
						$("#modal").modal('show');
						$("#tableSettlement").html('<thead><tr><th>ID</th><th>Empresa</th><th>Campo</th><th>Sueldo Base (Ficha)</th><th>Inicio Contrato</th><th>Fin Contrato</th><th>Causa</th><th>Días Trab.</th><th>Vacaciones Prop.</th><th>Finiquito</th><th>Carta Aviso</th><th>Estado Pago</th></tr></thead><tbody></tbody>');
						viewRow($("#txtViewRUT").val().split('-')[0]);
					}else{
						$("#modalAdvice").modal('hide');
						$("#modal-text").text("Error");
						$("#modal").modal('show');
					}
				});
			});

			$("#modalAdviceUndoOK").click(function(){
				$.post('../../phps/settlement_Save.php', {type: 'updateAdvice', 
					id: $("#modal-adviceUndo-id").text(),
					date: '-',
					state: 'PENDIENTE'
				}, function(data, textStatus, xhr) {
					if(data=='OK'){
						$("#modalAdviceUndo").modal('hide');
						$("#modal-text").text("Almacenado");
						$("#modal").modal('show');
						$("#tableSettlement").html('<thead><tr><th>Empresa</th><th>Campo</th><th>Sueldo Base (Ficha)</th><th>Inicio Contrato</th><th>Fin Contrato</th><th>Causa</th><th>Días Trab.</th><th>Vacaciones Prop.</th><th>Finiquito</th><th>Carta Aviso</th><th>Estado Pago</th></tr></thead><tbody></tbody>');
						viewRow($("#txtViewRUT").val().split('-')[0]);
					}else{
						$("#modalAdviceUndo").modal('hide');
						$("#modal-text").text("Error");
						$("#modal").modal('show');
					}
				});
			});


			$("#new").click(function() {
				var count = 0;
				$('#tablaRegistros > tbody > tr').each(function() {
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
				$("#tableSettlement").html('<thead><tr><th>ID</th><th>Empresa</th><th>Campo</th><th>Sueldo Base (Ficha)</th><th>Inicio Contrato</th><th>Fin Contrato</th><th>Causa</th><th>Días Trab.</th><th>Vacaciones Prop.</th><th>Finiquito</th><th>Carta Aviso</th><th>Estado Pago</th></tr></thead><tbody></tbody>');
			});

			$("#btnFilter").click(function() {
				loadData();
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

							}else if(contador==14){
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
								}

							}else if(contador==16 || contador==18 || contador==19){
								if($.isNumeric($($(this).children()[0]).val())){
									personalList += $($(this).children()[0]).val();
								}else{
									personalList += 0;
								}
							}else if(contador==20){
								personalList += $($(this).children()[0]).val();
							}else{
								if(cellValue==""){
									if(contador==17){
										personalList += "0";
									}else{
										personalList += "-";
									}
								}else{
									personalList += cellValue;
								}
							}
							if(contador<20){
								personalList += "&&";	
							}
							contador++;
						});
						personalList += "&&&&";
					});

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


			$("#listStatePayment").change(function(){
				if($(this).val()=='TODOS' || $(this).val()=='SIN'){
					$("#btnPrintSelected").attr("disabled","disabled");
				}else{
					$("#btnPrintSelected").removeAttr("disabled");
				}
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
					$('#tablaRegistros > tbody > tr').each(function() {
						$($(this).children()[0]).find(">:first-child").prop('checked', true);
					});
				}else{
					$(this).text('Seleccionar Todo');
					$('#tablaRegistros > tbody > tr').each(function() {
						$($(this).children()[0]).find(">:first-child").prop('checked', false);
					});
				}
			});

			$("#undoSettlement").click(function() {
				undoSettlement($("#labelIDView").text());
			});

			$("#btnPrintSelected").click(function() {
				var count = 0;
				var listEmployee = '';
				$('#tablaRegistros > tbody > tr').each(function() {
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
			//loadData();
			loadEndCause();
			if(expired_type=='expired'){
				$("#listStatePayment").val('PENDIENTE_AVISAR');
				$("#listStatePayment").change();
				loadData();
			}
		});

		function loadData(){
			$("#modalProgress-text").html('<i class="fa fa-spinner fa-spin fa-2x"></i><br/>Cargando Registros');
			$("#modalProgress").modal('show');
			$("#tablaRegistros").html('<thead><tr>' +
					'<th>Sel.</th>' +
					'<th>Empresa</th>' +
					'<th>Campo</th>' +
					'<th>RUT</th>' +
					'<th>Nombre</th>' +
					'<th>Estado Actual</th>' +
					'<th>Inicio</th>' +
					'<th>Fin</th>' +
					'<th>Creación Finiquito</th>' +
					'<th style="display: none;">Vac.</th>' +
					'<th>A Pago</th>' +
					'<th>Ver</th>' +
					'<th>ID</th>' +
					'<th>Pago</th>' +
					'<th>Caduca en</th>' +
				'</tr></thead><tbody id="tablaRegistrosBody"></tbody>');			
				//'<th data-dynatable-column="salary">Sueldo Base (Ficha)</th>' +


			$("#tablaRegistrosExcel").html('<tr><th>ID</th><th>Pago</th><th>Empresa</th><th>Campo</th><th>RUT</th><th>Nombre</th><th>Cargo</th><th>Estado Actual</th><th>Duración</th><th>Inicio</th><th>Fin</th><th>Mes Liquidación</th><th>Vacaciones Proporcionales</th><th>Liquidación</th><th>Indemnización Años de Servicio</th><th>Indemnización Aviso</th><th>Indemnización Voluntaria</th><th>Préstamo Empresa</th><th>Préstamo Caja</th><th>AFC</th><th>Total</th><th>Caduca en</th></tr>');
			var plant = 98;
			if($("#listPlant").val()!=null){
				plant = $("#listPlant").val();
			}

			var arrayStatePayment = $('#listStatePayment').selectpicker('val');
			var statePayment = '';
			for(i=0;i<arrayStatePayment.length;i++){
				statePayment += arrayStatePayment[i]+'-';
			}

			var paymentZero = 0;
			if($("#chkPayment").is(':checked')){
				paymentZero = 1;
			}

			$.post('../../phps/settlementPayment_Load.php', {
				type: "allPayment", 
				state: 'T', 
				statePayment: statePayment, 
				plant: plant,
				paymentZero: paymentZero
			}, function(data, textStatus, xhr) {
				console.log(data);
				$("#modalProgress").modal('hide');

				if(data!=0){
					var data = JSON.parse(data);
					var list = '';
					var listExcel = "";

					for(i=0;i<data.length;i++){

						var expire = '-';
						var classColor = '', fontColor = '';
						if(data[i]['expire']!=undefined){
							expire = data[i]['expireString'];
							if(data[i]['expire']<=45){
								classColor = 'warning';
								fontColor = 'color: orange;';
							}
							if(data[i]['expire']<=30){
								classColor = 'danger';
								fontColor = 'color: red;';
							}
						}else if(data[i]['expire']!=undefined){
							expire = data[i]['expireString'];
						}

						if(data[i]['contractStart'][2]=='-'){
							contractStart = data[i]['contractStart'].split('-');
							data[i]['contractStart'] = contractStart[0]+'/'+contractStart[1]+'/'+contractStart[2];
						}

						//var total=(parseInt(data[i]['vacationAmount'])+parseInt(data[i]['salaryPayment'])+parseInt(data[i]['salaryService'])+parseInt(data[i]['salaryAdvice'])+parseInt(data[i]['salaryVoluntary']))-(parseInt(data[i]['loanEnterprise'])+parseInt(data[i]['loanCompensation'])+parseInt(data[i]['afc']));

						var total = parseInt(data[i]['total']);
						if(total<0){
							total=0;
						}
						list += '<tr class="'+classColor+'">' +
									'<td style="'+fontColor+'"><input type="checkbox"></input></td>' +
									'<td style="'+fontColor+'">'+data[i]['enterprise']+'</td>' +
									'<td style="'+fontColor+'">'+data[i]['plant']+'</td>' +
									'<td style="'+fontColor+'">'+data[i]['rut']+'</td>' +
									'<td style="'+fontColor+'">'+data[i]['fullname']+'</td>' +
									'<td style="'+fontColor+'">'+data[i]['status']+'</td>' +
									'<td style="'+fontColor+'">'+data[i]['contractStart']+'</td>' +
									'<td style="'+fontColor+'">'+data[i]['contractEnd']+'</td>' +
									'<td style="'+fontColor+'">'+data[i]['settlementDate']+'</td>' +
									'<td style="'+fontColor+' display: none;">'+data[i]['vacationDays']+'</td>' +
									'<td style="'+fontColor+' text-align: right;">'+toSeparator(total)+'</td>' +
									'<td style="'+fontColor+'"><button class="btn btn-warning" onclick="viewRow(\''+data[i]['rut_per']+'\')"><i class="fa fa-eye fa-lg fa-fw"></i></button></td>' +
									'<td style="'+fontColor+'">'+data[i]['settlementID']+'</td>' +
									'<td style="'+fontColor+'">'+data[i]['link']+'</td>' +
									'<td style="'+fontColor+' text-align: center;"">'+expire+'</td>' +
								'</tr>';
						

						listExcel += '<tr>' +
										'<td>'+data[i]['settlementID']+'</td>' +
										'<td>'+data[i]['paymentState']+'</td>' +
										'<td>'+data[i]['enterprise']+'</td>' +
										'<td>'+data[i]['plant']+'</td>' +
										'<td>'+data[i]['rut']+'</td>' +
										'<td>'+data[i]['name']+' '+data[i]['lastname1']+' '+data[i]['lastname2']+'</td>' +
										'<td>'+data[i]['charge']+'</td>' +
										'<td>'+data[i]['status']+'</td>' +
										'<td>'+data[i]['duration']+'</td>' +
										'<td>'+data[i]['contractStart']+'</td>' +
										'<td>'+data[i]['contractEnd']+'</td>' +
										'<td>'+data[i]['salaryPaymentDate']+'</td>' +
										'<td>'+data[i]['vacationAmount']+'</td>' +
										'<td>'+data[i]['salaryPayment']+'</td>' +
										'<td>'+data[i]['salaryService']+'</td>' +
										'<td>'+data[i]['salaryAdvice']+'</td>' +
										'<td>'+data[i]['salaryVoluntary']+'</td>' +
										'<td>'+data[i]['loanEnterprise']+'</td>' +
										'<td>'+data[i]['loanCompensation']+'</td>' +
										'<td>'+data[i]['afc']+'</td>' +
										'<td>'+total+'</td>' +
										'<td>'+expire+'</td>' +
									'</tr>';

						if(i+1==data.length){
							$("#tablaRegistrosBody").append(list);
							$("#tablaRegistrosExcel").append(listExcel);
							$('#tablaRegistros').dataTable().fnDestroy();
							$('#tablaRegistros').DataTable({
								"language": { "url": "../../libs/datatables/language/Spanish.json"},
								"pageLength": 25
							});
						}
					}

					$(function () {//Inicializa popover
						$('[data-toggle="popover"]').popover()
					});

					$("#modalProgress").modal('hide');
				}else{
					$("#modalProgress").modal('hide');
				}
			});
		}


		function viewRow(id){
			$("#modalView").modal('show');
			$.post('../../phps/settlementPayment_Load.php', {type: "one", id: id}, function(data, textStatus, xhr) {
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
						list += '<td>'+data[i]['ID']+'</td>';
						list += '<td>'+data[i]['enterprise_initials']+'</td>';
						list += '<td>'+data[i]['plant']+'</td>';
						list += '<td style="text-align: right;">'+toSeparator(data[i]['sueldo_base'])+'</td>';
						list += '<td style="text-align: center;">'+data[i]['contract_start']+'</td>';
						list += '<td style="text-align: center;">'+data[i]['contract_end']+'</td>';
						list += '<td style="text-align: center;">'+data[i]['articulo']+'</td>';
						list += '<td style="text-align: center;">'+days+'</td>';
						list += '<td style="text-align: right;">'+toSeparator(data[i]['vacaciones_proporcionales'])+'</td>';
						list += '<td><button class="btn btn-danger" onclick="generatePDF(\'one\','+data[i]['ID']+')"><i class="fa fa-file-pdf-o fa-lg fa-fw"></i></button></td>';
						list += '<td><button class="btn btn-warning" onclick="generatePDFAdvice(\'one\','+data[i]['ID']+')"><i class="fa fa-file-pdf-o fa-lg fa-fw"></i></button></td>';

						/*if(data[i]['pago_estado']==undefined || data[i]['pago_estado']=='PENDIENTE'){
							list += '<td><button class="btn btn-warning" title="Pendiente" onclick="uploadFile(\'upload\','+data[i]['ID']+')"><i class="fa fa-upload fa-lg fa-fw"></i></button></td></tr>';
						
						}else if(data[i]['pago_estado']=='DIGITALIZADO'){
							list += '<td><button class="btn btn-primary" title="Digitalizado" onclick="uploadFile(\'ok\','+data[i]['ID']+')"><i class="fa fa-inbox fa-lg fa-fw"></i></button></td></tr>';
						}else if(ddata[i]['pago_estado']=='FINALIZADO'){
							list += '<td><button class="btn btn-success" title="Finalizado" onclick="uploadFile(\'view\','+data[i]['ID']+')"><i class="fa fa-file-text fa-lg fa-fw"></i></button></td></tr>';
						}*/

						if(data[i]['link']==''){
							if(data[i]['pago_estado']=='PENDIENTE'){
								list += '<td><button id="link'+data[i]['ID']+'" class="btn btn-warning" title="Pendiente" onclick="uploadFile(\'upload\','+data[i]['ID']+')"><i class="fa fa-upload fa-lg fa-fw"></i></button>' + 
									'<button id="btnSettState'+data[i]['ID']+'" class="btn btn-warning" title="Pendiente" onclick="settState(\'upload\','+data[i]['ID']+')"><i class="fa fa-upload fa-lg fa-fw"></i></button>' +
								'</td></tr>';
							}else{
								list += '<td><button id="link'+data[i]['ID']+'" class="btn btn-primary" title="Avisado (Presionar para deshacer aviso)" onclick="uploadFile(\'advice\','+data[i]['ID']+')"><i class="fa fa-upload fa-lg fa-fw"></i></button></td></tr>';
							}
						}else{
							if(data[i]['pago_estado']=='REVISION'){
								list += '<td><button id="link'+data[i]['ID']+'" class="btn btn-success" title="Finalizado" onclick="viewFile(\''+data[i]['link']+'\')"><i class="fa fa-file-text fa-lg fa-fw"></i></button></td></tr>';
							}else{
								list += '<td><button id="link'+data[i]['ID']+'" class="btn btn-success" title="Finalizado" onclick="viewFile(\''+data[i]['link']+'\')"><i class="fa fa-file-text fa-lg fa-fw"></i></button></td></tr>';
							}
						}

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

		function generatePDFAdvice(type,id){
			$("#modal-pdfAdvice-type").text(type);
			$("#modal-pdfAdvice-id").text(id);

			$("#modalPDFAdvice").modal('show');
		}

		function generatePDFLink(type,id,date){
			window.open("format_pdf.php?type="+type+"&id="+id+"&date="+date);
		}

		function generatePDFAdviceLink(type,id,date){
			window.open("advice_pdf.php?type="+type+"&id="+id+"&date="+date);
		}

		function viewFile(link){
			window.open("../"+link);
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
			$('#tableSelected').html('<thead><tr><th>Empresa</th><th>Campo</th><th>RUT</th><th>Nombre</th><th>Sueldo Base (Ficha)</th><th>Inicio Contrato</th><th>Vacaciones Usadas</th><th>Días Trab.</th><th>Liquidación</th><th>Sueldo Base (Íntegro)</th><th>Gratificación</th><th>Colación</th><th>Movilización</th><th>Vacaciones Prop.</th><th>Indemnización Años de Servicio</th><th>Indemnización Sustitutiva del Aviso Previo</th><th>Indemnización Voluntaria</th><th>Descuento Préstamo Empresa</th><th>Descuento Préstamo Caja de Compensación</th><th>Descuento aporte AFC</th><th>Cargo</th></tr></thead><tbody></tbody>');
			loadData();
		}


		function uploadFile(type, id){
			if(type=='upload'){
				$("#modal-text").text("Subir a través de carpeta");
				$("#modal").modal('show');
			}else{
				$("#modalAdviceUndo").modal('show');
				$("#modal-adviceUndo-id").text(id);
			}
		}

		function uploadAjax(id){
			var element = document.getElementById('fileToUpload');
	        var myfiles= element.files;
	        var data = new FormData();
	        var i=0;
	        for (i = 0; i < myfiles.length; i++) {
	            data.append('fileToUpload', myfiles[i]);
			}

	        $.ajax({
				url: '../../documents/upload.php', 
				type: 'POST',
				contentType: false, 
				data: data, 
				processData: false, 
				cache: false
	        }).done(function(msg) {
				//do something
				if(msg=='EXISTS'){
					$("#modal-text").text("Archivo ya existe");
					$("#modal").modal('show');
				}else if(msg=='SIZE'){
					$("#modal-text").text("Archivo excede el tamaño máximo");
					$("#modal").modal('show');
				}else if(msg=='FORMAT'){
					$("#modal-text").text("Sólo se aceptan archivos de tipo JPG, JPEG, PNG, GIF y PDF");
					$("#modal").modal('show');
				}else{
					//Cambiar de estado y guardar link
					$("#modal-text").text("Almacenado");
					$("#modal").modal('show');
					$("#fileToUpload").val('');
					$("#modalUpload").modal('hide');
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
				<div class="panel-heading"><i class="fa fa-vcard fa-lg fa-fw"></i>&nbsp;&nbsp; Cuenta Corriente Finiquitos</div>
				<div class="panel-body">
					<div class="row">
						<!--<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<label>Estado contrato:</label>
				    	    <select id="listState" class="form-control">
		  						<option value="V">VIGENTE</option>
								<option value="S" selected>FINIQUITADO</option>
							</select>
						</div>-->
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<label>Estado finiquito:</label>
				    	    <select id="listStatePayment" class="form-control selectpicker" multiple>
		  						<option value="TODOS" selected>TODOS</option>
								<option value="PAGADO">PAGADO</option>
								<option value="REVISION">REVISIÓN DE ARCHIVO</option>
								<option value="PENDIENTE">PENDIENTE</option>
								<option value="PENDIENTE_AVISAR">PENDIENTE > 2 Semanas</option>
								<option value="AVISADO">AVISADO</option>
							</select>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<label>Campo:</label>
				    	    <select id="listPlant" class="form-control">
							</select>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<br/>
							Mostrar pagos en 0&nbsp;<input id="chkPayment" type="checkbox"/>
							<br/>
							<br/>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-1 col-lg-1">
							<br/>
							<button id="btnFilter" class="btn btn-primary"><i class="fa fa-search"></i>&nbsp;Filtrar</button>
							<br/>
							<br/>
						</div>

						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<br/>
							<button id="toExcel" class="btn btn-success">Exportar a Excel  <img src="../../images/excel.ico"/></button>
							<br/>
							<br/>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3" style="text-align: center;">
							<br/>
							<button id="btnPrintSelected" class="btn btn-danger" disabled><i class="fa fa-file-pdf-o fa-lg fa-fw"></i>&nbsp;&nbsp;Generar PDF Seleccionados</button>
							<br/>
							<br/>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<button id="btnSelectAll" class="btn btn-primary">Seleccionar Todo</button>
						</div>
						<div class="col-xs-0 col-sm-0 col-md-2 col-lg-2"></div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<!--<button id="btnChangeMode" class="btn btn-primary">Ver Agrupados</button>-->
						</div>
					</div>	
					<br/>
					<br/>
					<div class="table-responsive">
						<table id="tablaRegistros" class="table table-hover" style="font-size: 12px;">
						</table>
						<table id="tablaRegistrosExcel" style="display: none;">
						</table>
						<table id="tablaRegistrosAgrupado" class="table table-hover" style="font-size: 12px; display: none;">
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

	<div id="modalDelete" class="modal fade" data-backdrop="static" style="z-index: 1059">
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

	<div id="modalUpload" class="modal fade" data-backdrop="static" style="z-index: 1051">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
	        	<div class="modal-body">
					<input type="file" name="fileToUpload" id="fileToUpload">
		    	    <!--<p id="modal-pdf-id" style="display: none;"></p>
		    	    <p id="modal-pdf-type" style="display: none;"></p>-->
		      	</div>
		      	<div class="modal-footer">
		        	<button type="button" class="btn btn-primary" onclick="uploadAjax()"><i class="fa fa-upload fa-lg fa-fw"></i>&nbsp;Subir Archivo</button>
		        	<button id="modalUploadHide" type="button" class="btn btn-primary">Cancelar</button>
		      	</div>
		    </div>
		</div>
	</div>

	<div id="modalAdvice" class="modal fade" data-backdrop="static" style="z-index: 1051">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
	        	<div class="modal-body">
	        		<p>¿Desea pasar el estado a Avisado?</p>
	        		<p id="modal-advice-id" style="display: none;"></p>
	        		<p id="modal-advice-date" style="display: none;"></p>
		      	</div>
		      	<div class="modal-footer">
		        	<button id="modalAdviceOK" type="button" class="btn btn-primary">Sí</button>
		        	<button id="modalAdviceHide" type="button" class="btn btn-danger">No</button>
		      	</div>
		    </div>
		</div>
	</div>

	<div id="modalAdviceUndo" class="modal fade" data-backdrop="static" style="z-index: 1051">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
	        	<div class="modal-body">
	        		<p>¿Desea deshacer el aviso?</p>
	        		<p id="modal-adviceUndo-id" style="display: none;"></p>
		      	</div>
		      	<div class="modal-footer">
		        	<button id="modalAdviceUndoOK" type="button" class="btn btn-primary">Sí</button>
		        	<button id="modalAdviceUndoHide" type="button" class="btn btn-danger">No</button>
		      	</div>
		    </div>
		</div>
	</div>

	<div id="modalPDF" class="modal fade" data-backdrop="static" style="z-index: 1052">
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

	<div id="modalPDFAdvice" class="modal fade" data-backdrop="static" style="z-index: 1052">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
	        	<div class="modal-body">
	        		<input id="checkPDFAdvice" type="checkbox" /> Seleccionar
		    	    <label>Fecha a mostrar:</label>
						<div class="input-group">
						<input id="txtPDFAdviceDate" type="text" class="form-control datepickerTxt" disabled>
						<span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></span>
					</div>
		    	    <p id="modal-pdfAdvice-id" style="display: none;"></p>
		    	    <p id="modal-pdfAdvice-type" style="display: none;"></p>
		      	</div>
		      	<div class="modal-footer">
		        	<button id="generatePDFAdviceOK" type="button" class="btn btn-warning">Generar PDF</button>
		        	<button id="modalPDFAdviceHide" type="button" class="btn btn-primary">Cancelar</button>
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
							  			<label>RUT:</label>
		  								<input id="txtViewRUT" type="Name" class="form-control"  style="text-align: right;" disabled>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
							  			<label>Nombre:</label>
							  			<input id="txtViewName" type="Name" class="form-control" disabled>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">&nbsp;<br/></div>
									<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
										<div class="panel panel-primary">
											<div class="panel-body">
												<div class="table-responsive">
													<table id="tableSettlement" class="table" style="font-size: 12px;">
														<thead>
															<tr>
																<th>ID</th>
																<th>Empresa</th>
																<th>Campo</th>
																<th>Sueldo Base (Ficha)</th>
																<th>Inicio Contrato</th>
																<th>Fin Contrato</th>
																<th>Causa</th>
																<th>Días Trab.</th>
																<th>Vacaciones Prop.</th>
																<th>Finiquito</th>
																<th>Carta Aviso</th>
																<th>Estado Pago</th>
															</tr>
														</thead>
														<tbody>
														</tbody>
													</table>
												</div>
											</div>
										</div>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4" style="text-align: center;">
										<button id="undoSettlement" class="btn btn-warning"><i class="fa fa-reply fa-lg fa-fw"></i>&nbsp;&nbsp;Deshacer Último Finiquito</button>
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