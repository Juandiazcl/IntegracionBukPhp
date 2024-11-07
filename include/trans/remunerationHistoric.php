<?php
header('Content-Type: text/html; charset=utf8'); 
session_start();

if(!isset($_SESSION['userId'])){
	header('Location: ../../login.php');
}elseif($_SESSION['display']['personal']['view']!=''){
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

	var idList = 1, calculation = false;

		$(document).ready(function() {
			$(function () {
			  $('[data-toggle="popover"]').popover()
			})

			loadMenu();
			startParameters();
			
			/*$('#txtCellphone').mask('00-0000000');
			$('#txtPhone').mask('00-0-0000000');*/

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
					var id = 0;
					if($('#labelID').text()!=''){
						id=$('#labelID').text();
					}
					$.post('../../phps/personal_Load.php', {type: 'verifyPersonal', id: id, rut: $('#txtRUT').val()}, function(data, textStatus, xhr) {
						if(data!=0){
							$("#modal-text").text("RUT ya está registrado en sistema");
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

			$("#modalDealHide").click(function() {
				$("#lblDeal").text('');
				$("#modalDeal").modal('hide');	
			});

			$("#modalRemunerationHide").click(function() {
				$("#lblRemunerationRUT").text('');
				$("#tableRemuneration").html('');
				$("#modalRemuneration").modal('hide');	
			});

			$("#modalDeleteHide").click(function() {
				$("#modalDelete").modal('hide');	
			});

			$("#btnNew").click(function() {
				$("#modalNew").modal('show');
			});

			$("#btnNewCancel").click(function() {
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

			$("#btnRemunerationSearch").click(function(){
				searchRemuneration();
			});

			$("#btnGenerateFullPDF").click(function() {
				var type = "historic";
				var year1 = $("#listYear1").val();
				var year2 = $("#listYear2").val();
				var month1 = $("#listMonth1").val();
				var month2 = $("#listMonth2").val();
				var rut = $("#lblRemunerationRUT").text().trim();
				window.open("remunerationHistoric_pdf.php?type="+type+"&year1="+year1+"&year2="+year2+"&month1="+month1+"&month2="+month2+"&rut="+rut)
			});



			loadEnterprise();
			loadPlant();
			/*loadCommune();
			loadCivilState();
			loadMilitary();
			loadJob();
			loadEducation();
			loadAFP();
			loadHealthSystem();
			loadCompensation();
			loadBank();*/

			loadYear()
			loadData();
		});

		function loadData(){
			$("#modalProgress-text").html('<i class="fa fa-spinner fa-spin fa-2x"></i><br/>Cargando Registros');
			$("#modalProgress").modal('show');

			var columns = [
                    {"title" : "Empresa", 		"data" : "enterprise"},
					{"title" : "Campo", 		"data" : "plant"},
					{"title" : "RUT", 			"data" : "rut"},
					{"title" : "Nombre", 		"data" : "fullname"},
					{"title" : "Stat", 			"data" : "status"},
					{"title" : "Sueldo Base", 	"data" : "salary"},
					{"title" : "Inicio", 		"data" : "contractStart"},
					{"title" : "Fin", 			"data" : "contractEnd"},
					{"title" : "Liq.", 		"data" : "remuneration"}
				];

			var plant = 98;
			if($("#listPlant").val()!=null){
				plant = $("#listPlant").val();
			}

			$('#tableData').DataTable({
				destroy: true,
				order: [[ 0, "asc" ]],
				language: { "url": "../../libs/datatables/language/Spanish.json"},
                ajax: {
		            "url": "../../phps/personal_Load.php",
		            "type": "POST",
		            "data": {type: "all", state: $("#listState").val(), plant: plant},
		            "dataSrc": ""
		        },
                columns: columns,
                initComplete: function( settings, json ) {
					$("#modalProgress").modal('hide');
					$(function () {//Inicializa popover
						$('[data-toggle="popover"]').popover()
					});
				}
            });

		}

		function editRow(id){
			$("#modalNew").modal('show');
			$.post('../../phps/personal_Load.php', {type: "one", id: id}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				$('#txtRUT').val(data[0]['rut']);
				$('#txtRUTFile').val(data[0]['file']);
				$('#labelID').text(data[0]['file']);
				$('#listPersonalEnterprise').val(data[0]['emp_per']);
				$('#listPersonalPlant').val(data[0]['planta_per']);
				$('#listPersonalState').val(data[0]['estado_per']);

				$('#txtLastname1').val(data[0]['Apepat_per']);
				$('#txtLastname2').val(data[0]['Apemat_per']);
				$('#txtName').val(data[0]['Nom_per']);
				$('#txtAddress').val(data[0]['Direc_per']);
				$('#txtCity').val(data[0]['comuna_per']);
				$('#listCommune').val(data[0]['ciudad_per']);
				$('#txtPhone').val(data[0]['fono_per']);
				$('#txtCellphone').val(data[0]['cel_per']);
				$('#txtMail').val(data[0]['mail_per']);
				$('#listJob').val(data[0]['hi_tpcargo']);
				$('#listEducation').val(data[0]['Cestudio']);
				$('#txtCountry').val(data[0]['nac_per']);
				$('#txtBirthDate').val(data[0]['fecnac_per']);
				$('#listCivilState').val(data[0]['escciv_per']);
				$('#listGender').val(data[0]['sexo_per']);
				$('#listMilitary').val(data[0]['SMcodigo']);

				$('#txtFamilyLoadSection').val(data[0]['tramo_cargfam']);
				$('#txtFamilyLoadQuantity').val(data[0]['carg_ulin']);
				$('#txtCCSoftland').val(data[0]['cenco_per']);
				$('#txtContratStartDate').val(data[0]['fecing_per']);
				$('#txtContratEndDate').val(data[0]['fecter_per']);

				$('#listAFP').val(data[0]['afp_per']);
				//$('#listAFPVoluntary').val(data[0]['']);
				$('#txtAFPVoluntaryAmount').val(data[0]['cotizpevol_per']);
				$('#listHealthSystem').val(data[0]['isa_per']);
				$('#txtHealthSystemPercentage').val(data[0]['porc_isa_per']);
				$('#txtHealthSystemMoney').val(data[0]['peso_isa_per']);
				$('#txtHealthSystemUF').val(data[0]['uf_isa_per']);

				$('#listCompensation').val(data[0]['caja_per']);
				$('#txtINPPercentage').val(data[0]['porc_inp']);
				$('#txtSalaryAmount').val(data[0]['sbase_per']);
				if(data[0]['indef']==0){
					$('#rbContractDuration1').prop('checked','checked');
				}else{
					$('#rbContractDuration2').prop('checked','checked');
				}
				if(data[0]['labor_per']==0){
					$('#rbContractType1').prop('checked','checked');
				}else{
					$('#rbContractType2').prop('checked','checked');
				}

				$('#txtDeal1Number').val(data[0]['codtrt_per']);
				$('#txtDeal2Number').val(data[0]['codtrt2_per']);
				$('#txtDeal3Number').val(data[0]['codtrt3_per']);
				$('#txtDeal4Number').val(data[0]['codtrt4_per']);
				$('#txtDeal1Text').val(data[0]['Labortxt']);
				$('#txtDeal2Text').val(data[0]['Labortxt2']);
				$('#txtDeal3Text').val(data[0]['Labortxt3']);
				$('#txtDeal4Text').val(data[0]['Labortxt4']);
				$('#txtDeal1Amount').val(data[0]['preclabor']);
				$('#txtDeal2Amount').val(data[0]['preclabor2']);
				$('#txtDeal3Amount').val(data[0]['preclabor3']);
				$('#txtDeal4Amount').val(data[0]['preclabor4']);

				$('#listAccount').val(data[0]['cta_tipo']);
				$('#listBank').val(data[0]['cta_banco']);
				$('#txtAccount').val(data[0]['cta_numero']);

			});
		}

		function saveAccount(rut,btn){
			
			$.post('../../phps/personal_Save.php', {
				type: "account", 
				rut: rut,
				accountType: $($($(btn).parent().parent().children()[9]).children()[0]).val(),
				accountNumber: $($($(btn).parent().parent().children()[11]).children()[0]).val(),
				accountBank: $($($(btn).parent().parent().children()[10]).children()[0]).val()
			}, function(data, textStatus, xhr) {
				if(data=='OK'){
					$("#modal-text").text("Almacenado");
					$("#modal").modal('show');
				}else{
					$("#modal-text").text("Ha ocurrido un error al traer los datos, favor reintente. Si vuelve a fallar, contacte al administrador");
					$("#modal").modal('show');
				}
			});
		}

		function changeAccount(select, rut){
			if($(select).val()=='servipag'){
				$($($(select).parent().parent().children()[10]).children()[0]).val('');
				$($($(select).parent().parent().children()[10]).children()[0]).attr('disabled','disabled');
				$($($(select).parent().parent().children()[11]).children()[0]).val(rut);
				$($($(select).parent().parent().children()[11]).children()[0]).attr('disabled','disabled');
			}else if($(select).val()=='rut'){
				$($($(select).parent().parent().children()[10]).children()[0]).val('ESTADO');
				$($($(select).parent().parent().children()[10]).children()[0]).attr('disabled','disabled');
				$($($(select).parent().parent().children()[11]).children()[0]).val(rut);
				$($($(select).parent().parent().children()[11]).children()[0]).attr('disabled','disabled');
			}else{
				$($($(select).parent().parent().children()[10]).children()[0]).removeAttr('disabled');
				$($($(select).parent().parent().children()[11]).children()[0]).removeAttr('disabled');
			}
		}

		function loadPlant(){
			$.post('../../phps/plant_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				$("#listPersonalPlant").append('<option value="0" selected>SELECCIONE</option>');
				for(i=0;i<data.length;i++){
					$("#listPlant").append('<option value="'+data[i]["Pl_codigo"]+'">'+data[i]["PlNombre"]+'</option>');
					if(data[i]["Pl_codigo"]!=98){
						$("#listPersonalPlant").append('<option value="'+data[i]["Pl_codigo"]+'">'+data[i]["PlNombre"]+'</option>');
					}
				}
				$("#listPlant").val(98);
			});
		}

		function loadEnterprise(){
			$.post('../../phps/enterprise_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				$("#listPersonalEnterprise").append('<option value="0">SELECCIONE</option>');
				for(i=0;i<data.length;i++){
					$("#listPersonalEnterprise").append('<option value="'+data[i]["Emp_codigo"]+'">'+data[i]["EmpSigla"]+'</option>');
				}
				$("#listPersonalEnterprise").val(0);
			});
		}

		function loadCommune(){//BD con error: Ciudad y Comuna intercambiados
			$.post('../../phps/commune_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				$("#listCommune").append('<option value="0" selected>SELECCIONE</option>');
				for(i=0;i<data.length;i++){
					$("#listCommune").append('<option value="'+data[i]["cod_ciu"]+'">'+data[i]["ciu_des"]+'</option>');
				}
			});
		}

		function loadCivilState(){
			$.post('../../phps/civilState_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					$("#listCivilState").append('<option value="'+data[i]["Estciv"]+'">'+data[i]["desciv"]+'</option>');
				}
				$("#listCivilState").val(1);
			});
		}

		function loadMilitary(){
			$.post('../../phps/military_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					$("#listMilitary").append('<option value="'+data[i]["SMcodigo"]+'">'+data[i]["SMdescr"]+'</option>');
				}
				$("#listMilitary").val(2);
			});
		}

		function loadJob(){
			$.post('../../phps/job_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				$("#listJob").append('<option value="0" selected>SELECCIONE</option>');
				for(i=0;i<data.length;i++){
					$("#listJob").append('<option value="'+data[i]["codlb"]+'">'+data[i]["descriplb"]+'</option>');
				}
			});
		}

		function loadEducation(){
			$.post('../../phps/education_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				$("#listEducation").append('<option value="0" selected>SELECCIONE</option>');
				for(i=0;i<data.length;i++){
					$("#listEducation").append('<option value="'+data[i]["Cestudio"]+'">'+data[i]["Destudio"]+'</option>');
				}
			});
		}

		function loadAFP(){
			$.post('../../phps/afp_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					$("#listAFP").append('<option value="'+data[i]["cod_afp"]+'">'+data[i]["des_afp"]+'</option>');
				}
				$("#listAFP").val('000');
			});
		}

		function loadHealthSystem(){
			$.post('../../phps/healthSystem_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				$("#listHealthSystem").append('<option value="0" selected>SELECCIONE</option>');
				for(i=0;i<data.length;i++){
					$("#listHealthSystem").append('<option value="'+data[i]["cod_isa"]+'">'+data[i]["nom_isa"]+'</option>');
				}
			});
		}

		function loadCompensation(){
			$.post('../../phps/compensation_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				$("#listCompensation").append('<option value="0" selected>SELECCIONE</option>');
				for(i=0;i<data.length;i++){
					$("#listCompensation").append('<option value="'+data[i]["Caja_codigo"]+'">'+data[i]["Caja_desc"]+'</option>');
				}
			});
		}

		function loadBank(){
			$.post('../../phps/bank_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				$("#listCompensation").append('<option value="0" selected>SELECCIONE</option>');
				for(i=0;i<data.length;i++){
					$("#listBank").append('<option value="'+data[i]["Banco"]+'">'+data[i]["Banco"]+'</option>');
				}
			});
		}


		function loadDeal(dealIndex){
			$("#lblDeal").text(dealIndex);
			$("#modalDeal").modal('show');
			$("#modalProgress-text").html('<i class="fa fa-spinner fa-spin fa-2x"></i><br/>Cargando Registros');
			$("#modalProgress").modal('show');

			var columns = [
					{"title" : "Sel.", 		"data" : "select"},
                    {"title" : "Cod.", 		"data" : "codtrt"},
					{"title" : "Trato", 	"data" : "desctrt"},
					{"title" : "Unidad", 	"data" : "unidadtrt"},
					{"title" : "Valor", 	"data" : "val1trt"}
				];

			$('#tableDealSearch').DataTable({
				destroy: true,
				order: [[ 0, "asc" ]],
				language: { "url": "../../libs/datatables/language/Spanish.json"},
                ajax: {
		            "url": "../../phps/deal_Load.php",
		            "type": "POST",
		            "data": {type: "all", available: "S"},
		            "dataSrc": ""
		        },
                columns: columns,
                initComplete: function( settings, json ) {
					$("#modalProgress").modal('hide');
					$(function () {//Inicializa popover
						$('[data-toggle="popover"]').popover()
					});
				}
            });
		}

		function selectDeal(cod, desc, val){
			$("#txtDeal"+$("#lblDeal").text()+"Number").val(cod);
			$("#txtDeal"+$("#lblDeal").text()+"Text").val(desc);
			$("#txtDeal"+$("#lblDeal").text()+"Amount").val(val);
			$("#lblDeal").text('');
			$("#modalDeal").modal('hide');
		}

		function viewRemuneration(rut){
			$("#lblRemunerationRUT").text(rut);
			$("#modalRemuneration").modal('show');
		}

		function searchRemuneration(rut){
			$("#modalProgress-text").html('<i class="fa fa-spinner fa-spin fa-2x"></i><br/>Cargando Registros');
			$("#modalProgress").modal('show');

			var columns = [
					{"title" : "Año", 		"data" : "aaaarem"},
                    {"title" : "Mes", 		"data" : "mmrem"},
					{"title" : "Campo", 	"data" : "plant"},
					{"title" : "PDF",	 	"data" : "pdf"}
				];

			$.post('../../phps/remuneration_Load.php', {
				type: "historic",
				rut: $("#lblRemunerationRUT").text().trim(),
				year1: $("#listYear1").val(),
				month1: $("#listMonth1").val(),
				year2: $("#listYear2").val(),
				month2: $("#listMonth2").val()}, function(data, textStatus, xhr) {
				console.log(data);
				var data = JSON.parse(data);
					$("#modalProgress").modal('hide');
				

				return;
			});

			$('#tableRemuneration').DataTable({
				destroy: true,
				pageLength: 100,
				order: [[ 0, "asc" ]],
				language: { "url": "../../libs/datatables/language/Spanish.json"},
                ajax: {
		            "url": "../../phps/remuneration_Load.php",
		            "type": "POST",
		            "data": {
		            	type: "historic",
		            	rut: $("#lblRemunerationRUT").text().trim(),
		            	year1: $("#listYear1").val(),
		            	month1: $("#listMonth1").val(),
		            	year2: $("#listYear2").val(),
		            	month2: $("#listMonth2").val()
					},
		            "dataSrc": ""
		        },
                columns: columns,
                initComplete: function( settings, json ) {
					$("#modalProgress").modal('hide');
					$(function () {//Inicializa popover
						$('[data-toggle="popover"]').popover()
					});
				}
            });
		}

		function loadYear(){
			$.post('../../phps/remuneration_Load.php', {type: "year"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					$("#listYear1").append('<option value="'+data[i]["aaaarem"]+'">'+data[i]["aaaarem"]+'</option>');
					$("#listYear2").append('<option value="'+data[i]["aaaarem"]+'">'+data[i]["aaaarem"]+'</option>');

					if(i+1==data.length){
						$.post('../../phps/parameters_Load.php', {type: "all"}, function(data, textStatus, xhr) {
							var data = JSON.parse(data);
							$("#listMonth1").val(data[0]["Mes"]);
							$("#listYear1").val(data[0]["ANO"]);
							$("#listMonth2").val(data[0]["Mes"]);
							$("#listYear2").val(data[0]["ANO"]);
							//loadData();
						});
					}
				}
			});
		}

		function generatePDFLink(type,year,month,rut,costCenter){
			window.open("remuneration_pdf.php?type="+type+"&year="+year+"&month="+month+"&rut="+rut+"&costCenter="+costCenter);
		}

		function deleteRow(id){
			$("#modal-delete-text").text('¿Está seguro de eliminar el registro '+id+'?');
			$("#modal-delete-id").text(id);
			$("#modalDelete").modal('show');
		}

		function cleanModal(){
			$("#divID").css('display','none');
			$("#labelID").text('');
			$("#modalNew").modal('hide');

			$('#txtRUT').val('');
			$('#txtRUTFile').val('');
			$('#labelID').text('');
			$('#listPersonalEnterprise').val(0);
			$('#listPersonalPlant').val(0);
			$('#listPersonalState').val('T');

			$('#txtLastname1').val('');
			$('#txtLastname2').val('');
			$('#txtName').val('');
			$('#txtAddress').val('');
			$('#txtCity').val('');
			$('#listCommune').val(0);
			$('#txtPhone').val('');
			$('#txtCellphone').val('');
			$('#txtMail').val('');
			$('#listJob').val(0);
			$('#listEducation').val(0);
			$('#txtCountry').val('');
			$('#txtBirthDate').val('');
			$('#listCivilState').val(0);
			$('#listGender').val(0);
			$('#listMilitary').val(2);

			$('#txtFamilyLoadSection').val('');
			$('#txtFamilyLoadQuantity').val('');
			$('#txtCCSoftland').val('');
			$('#txtContratStartDate').val('');
			$('#txtContratEndDate').val('');

			$('#listAFP').val('000');
			//$('#listAFPVoluntary').val('');
			$('#txtAFPVoluntaryAmount').val('');
			$('#listHealthSystem').val(0);
			$('#txtHealthSystemPercentage').val('');
			$('#txtHealthSystemMoney').val('');
			$('#txtHealthSystemUF').val('');

			$('#listCompensation').val(0);
			$('#txtINPPercentage').val('');
			$('#txtSalaryAmount').val('');
			$('#rbContractDuration1').prop('checked','checked');
			$('#rbContractType1').prop('checked','checked');

			$('#txtDeal1Number').val('');
			$('#txtDeal2Number').val('');
			$('#txtDeal3Number').val('');
			$('#txtDeal4Number').val('');
			$('#txtDeal1Text').val('');
			$('#txtDeal2Text').val('');
			$('#txtDeal3Text').val('');
			$('#txtDeal4Text').val('');
			$('#txtDeal1Amount').val('');
			$('#txtDeal2Amount').val('');
			$('#txtDeal3Amount').val('');
			$('#txtDeal4Amount').val('');
			
			$('#listAccount').val('servipag');
			$('#listAccountBank').val('');
			$('#txtAccount').val('');
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
				<div class="panel-heading"><i class="fa fa-user"></i><i class="fa fa-clock-o"></i>&nbsp;&nbsp; Liquidaciones Histórico</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<label style="font-size: 12px;">Estado contrato:</label>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
				    	    <select id="listState" class="form-control input-sm">
								<option value="T">TODOS</option>
		  						<option value="V" selected>VIGENTE</option>
								<option value="S">FINIQUITADO</option>
							</select>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<label style="font-size: 12px;">Campo:</label>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
				    	    <select id="listPlant" class="form-control input-sm">
							</select>
						</div>

						<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
							<br/>
						</div>

						<!--<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<button id="toExcel" class="btn btn-success">Exportar a Excel  <img src="../../images/excel.ico"/></button>
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


	<div id="modalRemuneration" class="modal fade" data-backdrop="static" style="z-index: 1051">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
	        	<div class="modal-body" style="height: 80%; overflow-y: scroll">
	        		<div class="row">
		        		<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
		        			<label style="font-size: 12px;">Rango desde</label>
		        		</div>
		        		<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
				    	    <select id="listMonth1" class="form-control input-sm">
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
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
				    	    <select id="listYear1" class="form-control input-sm">
							</select>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-1 col-lg-1">
		        			<label style="font-size: 12px;">hasta</label>
		        		</div>
		        		<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
				    	    <select id="listMonth2" class="form-control input-sm">
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
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
				    	    <select id="listYear2" class="form-control input-sm">
							</select>
						</div>

						<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6" style="text-align: center;">
							<br/>
							<button id="btnRemunerationSearch" class="btn btn-primary btn-sm"><i class="fa fa-search fa-lg fa-fw"></i>&nbsp;&nbsp; Buscar</button></td>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6" style="text-align: center;">
								<br/>
							<button id="btnGenerateFullPDF" class="btn btn-danger btn-sm">Generar Rango<i class="fa fa-file-pdf-o fa-lg fa-fw"></i></button>
									<br/>
								</div>
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
			    	    	<table id="tableRemuneration" class="table" style="font-size: 12px;">
			    	    	</table>
			    	    </div>
			    	</div>
		      	</div>
		      	<div class="modal-footer">
		      		<label id="lblRemunerationRUT"></label>
		        	<button id="modalRemunerationHide" type="button" class="btn btn-primary">Cerrar</button>
		      	</div>
		    </div>
		</div>
	</div>
</body>
</html>