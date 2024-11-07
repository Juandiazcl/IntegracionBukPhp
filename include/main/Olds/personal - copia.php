<?php
header('Content-Type: text/html; charset=utf8'); 
session_start();

if(!isset($_SESSION['userId'])){
	header('Location: ../../login.php');
}elseif($_SESSION['display']['personal']['view']!=''){
	header('Location: ../../login.php');
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
	<link rel="stylesheet" href="../../libs/dynatable/jquery.dynatable.css"></script>
	<script type="text/javascript" src="../../libs/dynatable/jquery.dynatable.js"></script>	
	<script type="text/javascript" src="../../libs/jquery.table2excel.js"></script>	
	<script type="text/javascript" src="../../libs/bootstrap_multiselect/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../../libs/bootstrap_multiselect/css/bootstrap-multiselect.css">
	<title></title>
	<script type="text/javascript">

	var idList = 1;

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

			$(".datepickerTxt").focusout(function(){
				$(".datepickerTxt").datepicker('hide');
			});

			$("#btnFilter").click(function(event) {
				loadRegistros();
			});			

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

			$('#listPaymentMode').change(function(event) {
				if($(this).val()=='CHEQUE'){
					$("#divBank").css('display', 'none');
					$("#divBankAccount").css('display', 'none');
					$("#txtBank").val('');
					$("#txtBankAccount").val('');
					$("#txtBankAccount").prop('disabled','false');

				}else if($(this).val()=='CUENTA RUT'){
					$("#divBank").css('display', 'none');
					$("#divBankAccount").css('display', 'block');
					$("#txtBank").val('');
					$("#txtBankAccount").val($("#txtRUT").val());
					$("#txtBankAccount").removeAttr('disabled');
					//$("#txtBankAccount").prop('disabled','true');

				}else if($(this).val()=='OTRA CUENTA'){
					$("#divBank").css('display', 'block');
					$("#divBankAccount").css('display', 'block');
					$("#txtBank").val('');
					$("#txtBankAccount").val('');
					$("#txtBankAccount").removeAttr('disabled');
				}
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

			$("#modalContactHide").click(function() {
				$("#listContact").val('NO CONTACTADO');
				$("#contactObservation").val('');
				$("#modalContact").modal('hide');	
			});

			$("#new").click(function() {
				$("#modalNew").modal('show');
			});

			$("#cancel").click(function() {
				cleanModal();
			});

			$("#delete").click(function() {
				var id = $("#modal-delete-id").text();
				$.post('../../phps/personal_Save.php', {type: 'delete', id: id}, function(data, textStatus, xhr) {
					if(data=='OK'){
						loadRegistros();
						$("#modalDelete").modal('hide');
						$("#modal-text").text("Registro Eliminado Satisfactoriamente");
						$("#modal").modal('show');
					}else{
						$("#modalDelete").modal('hide');
						$("#modal-text").text("No puede eliminar una persona asociada a 1 o más contratos");
						$("#modal").modal('show');
					}
				});
			});

			$("#save").click(function() {
				var listLicense = '';
				for(d=0;d<$("#listDriverLicense").val().length;d++){
					listLicense += $("#listDriverLicense").val()[d]+'-';
				}
				if($("#listClothingSize").val()!='0' && $("#listShoeSize").val()!='0'){
					if(verifyRUT($('#txtRUT').val())==true){
						if(verifyData()==true){
							var id = 0
							if($('#labelID').text()!=''){
								id=$('#labelID').text();
							}
							$("#modalProgress").modal('show');
							$.post('../../phps/personal_Load.php', {type: 'verifyPersonal', id: id, rut: $('#txtRUT').val()}, function(data, textStatus, xhr) {
								$("#modalProgress").modal('hide');
								if(data!=0){
									$("#modal-text").text("RUT duplicado");
									$("#modal").modal('show');
									$("#modalNew").modal('show');
								}else{

									var type="update";
									if($("#divID").css('display')=='none'){
										type='save';
									}
									var dBirthDate = $('#txtBirthDate').val().split('/');
									var dLicenseDate = $('#txtLicenseDate').val().split('/');
									var dRutDate = $('#txtRutDate').val().split('/');
									var dateBirthDate = dBirthDate[2]+'-'+dBirthDate[1]+'-'+dBirthDate[0];
									var dateLicenseDate = dLicenseDate[2]+'-'+dLicenseDate[1]+'-'+dLicenseDate[0];
									var dateRutDate = dRutDate[2]+'-'+dRutDate[1]+'-'+dRutDate[0];
									$.post('../../phps/personal_Save.php', {type: type, id: $('#labelID').text(), 
										rut: $('#txtRUT').val().toUpperCase(), 
										name: $('#txtName').val().toUpperCase(),   
										lastname1: $('#txtLastname1').val().toUpperCase(),  
										lastname2: $('#txtLastname2').val().toUpperCase(), 
										birthdate: dateBirthDate,
										gender: $('#listGender').val().toUpperCase(), 
										civil_status: $('#listCivilStatus').val().toUpperCase(), 
										commune_id: $('#listCommune').val(),
										sector_id: $('#listSector').val(),
										address: $('#txtAddress').val().toUpperCase(), 
										address_number: '',
										charge_id: $('#listCharge').val(),
										health_system_id: $('#listHealthSystem').val(),
										afp_id: $('#listAFP').val(),
										driver_license_id: listLicense,
										driver_license_date: dateLicenseDate,
										turn: $('#listTurn').val(),
										cellphone: $('#txtCellphone').val(),
										phone: $('#txtPhone').val(),
										mail: $('#txtMail').val().toUpperCase(), 
										clothing_size: $('#listClothingSize').val(),
										shoe_size: $('#listShoeSize').val(),
										payment_mode: $('#listPaymentMode').val(),
										bank: $('#txtBank').val(),
										bank_account: $('#txtBankAccount').val(),
										rut_date: dateRutDate
									}, function(data, textStatus, xhr) {
											console.log(data);
										if(data=='OK'){
											loadRegistros();
											$("#modal-text").text("Almacenado");
											$("#modal").modal('show');
											cleanModal();

										}else{
											$("#modal-text").text("Datos de personal duplicados");
											$("#modal").modal('show');
										}
									});
								}
							});
						}else{
							$("#modal-text").text('Debe ingresar los datos obligatorios (*)');
							$("#modal").modal('show');
						}
					}else{
						if($('#txtRUT').val()!=''){
							$("#txtRUT").parent().addClass('has-error');
							$("#modal-text").text('RUT inválido');
							$("#modal").modal('show');
						}else{
							$("#txtRUT").parent().addClass('has-error');
							$("#modal-text").text('Debe ingresar RUT (*)');
							$("#modal").modal('show');
						}
					}
				}else{
					$("#modal-text").text('Debe seleccionar talla y número de calzado');
					$("#modal").modal('show');
				}
			});

			loadRegistros();
			loadRegions();
			loadCharges();
			loadHealthSystem();
			loadAFP();
			loadDriverLicense();
		});

		function loadRegistros(){
			$("#tablaRegistros").html('<thead><tr>' +
				'<th data-dynatable-column="id">ID</th>' +
				'<th data-dynatable-column="rut">RUT</th>' +
				'<th data-dynatable-column="name">Nombre</th>' +
				'<th data-dynatable-column="lastnames">Apellidos</th>' +
				'<th data-dynatable-column="commune_name">Comuna</th>' +
				'<th data-dynatable-column="cellphone">Celular</th>' +
				'<th data-dynatable-column="state">Estado</th>' +
				'<th data-dynatable-column="contact">Contactado</th>' +
				'<th data-dynatable-column="editar">Editar</th>' +
				'<th data-dynatable-column="eliminar">Eliminar</th>'+
				'</tr></thead><tbody id="tablaRegistrosBody"></tbody>');			
			$.post('../../phps/personal_Load.php', {type: "all", filter: ""/*$("#txtFilter").val()*/}, function(data, textStatus, xhr) {
				
				if(data!=0){
					var data = JSON.parse(data);
					var dynatable = $("#tablaRegistros").dynatable({
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
					$("#tablaRegistrosExcel").html('<tr><th>RUT</th><th>Nombres</th><th>Apellido Paterno</th><th>Apellido Materno</th><th>Fecha Nacimiento</th><th>Estado Civil</th><th>Sexo</th><th>Región</th><th>Comuna</th><th>Sector</th><th>Dirección</th><th>AFP</th><th>Sistema Salud</th><th>Licencia Conducir</th><th>Vigencia Licencia</th><th>Especialidad</th><th>Turno Preferencia</th><th>Teléfono Fijo</th><th>Teléfono Celular</th><th>E-Mail</th><th>N° Calzado</th><th>Talla Ropa</th><th>Tipo Cuenta</th><th>Banco</th><th>Cuenta</th></tr>');
					for(i=0;i<data.length;i++){
						list = "<tr id='id"+data[i]['id']+"'>";
						list += "<td>"+data[i]['rut'].toUpperCase()+"</td>";
						list += "<td>"+data[i]['name'].toUpperCase()+"</td>";
						list += "<td>"+data[i]['lastname1'].toUpperCase()+"</td>";
						list += "<td>"+data[i]['lastname2'].toUpperCase()+"</td>";
						list += "<td>"+data[i]['birthdate'].toUpperCase()+"</td>";
						list += "<td>"+data[i]['civil_status'].toUpperCase()+"</td>";
						list += "<td>"+data[i]['gender'].toUpperCase()+"</td>";
						
						list += "<td>"+data[i]['region_name'].toUpperCase()+"</td>";
						list += "<td>"+data[i]['commune_name'].toUpperCase()+"</td>";
						list += "<td>"+data[i]['sector_name'].toUpperCase()+"</td>";
						list += "<td>"+data[i]['address'].toUpperCase()+"</td>";
						list += "<td>"+data[i]['afp_name'].toUpperCase()+"</td>";
						list += "<td>"+data[i]['health_system_name'].toUpperCase()+"</td>";
						list += "<td>"+data[i]['driver_license'].toUpperCase()+"</td>";
						list += "<td>"+data[i]['driver_license_date'].toUpperCase()+"</td>";
						list += "<td>"+data[i]['charge_name'].toUpperCase()+"</td>";
						list += "<td>"+data[i]['turn'].toUpperCase()+"</td>";
						list += "<td>"+data[i]['phone'].toUpperCase()+"</td>";
						list += "<td>"+data[i]['cellphone'].toUpperCase()+"</td>";

						list += "<td>"+data[i]['mail'].toUpperCase()+"</td>";
						list += "<td>"+data[i]['shoe_size'].toUpperCase()+"</td>";
						list += "<td>"+data[i]['clothing_size'].toUpperCase()+"</td>";
						list += "<td>"+data[i]['payment_type'].toUpperCase()+"</td>";
						list += "<td>"+data[i]['payment_bank'].toUpperCase()+"</td>";
						list += "<td>"+data[i]['payment_account'].toUpperCase()+"</td></tr>";
						$("#tablaRegistrosExcel").append(list);
					}
				}
			});
		}

		function editRow(id){
			$('#txtID').removeAttr('disabled');
			$("#divID").css('display','block');
			$("#labelID").text(id);
			$("#modalNew").modal('show');
			$.post('../../phps/personal_Load.php', {type: "one", id: id}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				$("#labelState").text(data[0]['state']);
				$('#txtRUT').val(data[0]['rut'].toUpperCase());
				$('#txtName').val(data[0]['name'].toUpperCase());
				$('#txtLastname1').val(data[0]['lastname1'].toUpperCase());
				$('#txtLastname2').val(data[0]['lastname2'].toUpperCase());
				var dBirthDate = data[0]['birthdate'].split('-');
				var dateBirthDate = dBirthDate[2]+'/'+dBirthDate[1]+'/'+dBirthDate[0];
				$('#txtBirthDate').val(dateBirthDate);

				$('#listGender').val(data[0]['gender'].toUpperCase());
				$('#listCivilStatus').val(data[0]['civil_status'].toUpperCase());
				
				$('#listRegion').val(data[0]['region_id']);
				loadCommunes("selected",data[0]['commune_id'],data[0]['sector_id']);
				//$('#listRegion').val(data[0]['region_id']).change();
				//$('#listCommune').val(data[0]['commune_id']);
				//$('#listCommune').val(data[0]['commune_id']).change();
				//loadSectors("selected",data[0]['sector_id']);
				//$('#listSector').val(data[0]['sector_id']);
				
				$('#txtAddress').val(data[0]['address'].toUpperCase());
				//$('#txtAddressNumber').val(data[0]['address_number']);
				$('#listCharge').val(data[0]['charge_id']);
				$('#listHealthSystem').val(data[0]['health_system_id']);
				$('#listAFP').val(data[0]['afp_id']);
				//$('#listDriverLicense').val(data[0]['driver_license_id']);
				if(data[0]['arrayLicense']!='0'){
					var listLicense = data[0]['arrayLicense'].split('-');
					listLicense.splice(listLicense.length-1,1);
					$('#listDriverLicense').multiselect('select',listLicense);
				}

				var dLicenseDate = data[0]['driver_license_date'].split('-');
				var dateLicenseDate = dLicenseDate[2]+'/'+dLicenseDate[1]+'/'+dLicenseDate[0];
				$('#txtLicenseDate').val(dateLicenseDate);
				$('#listTurn').val(data[0]['turn'].toUpperCase());
				$('#txtPhone').val(data[0]['phone']);
				$('#txtCellphone').val(data[0]['cellphone']);
				$('#txtMail').val(data[0]['mail'].toUpperCase());
				$('#listClothingSize').val(data[0]['clothing_size'].toUpperCase());
				$('#listShoeSize').val(data[0]['shoe_size'].toUpperCase());
				var dRutDate = data[0]['rut_date'].split('-');
				var dateRutDate = dRutDate[2]+'/'+dRutDate[1]+'/'+dRutDate[0];
				$('#txtRutDate').val(dateRutDate);

				$('#listPaymentMode').val(data[0]['payment_type'].toUpperCase());
				$('#txtBank').val(data[0]['payment_bank'].toUpperCase());
				$('#txtBankAccount').val(data[0]['payment_account'].toUpperCase());

				if($('#listPaymentMode').val()=='CHEQUE'){
					$("#divBank").css('display', 'none');
					$("#divBankAccount").css('display', 'none');
					$("#txtBank").val('');
					$("#txtBankAccount").val('');
					$("#txtBankAccount").prop('disabled','false');

				}else if($('#listPaymentMode').val()=='CUENTA RUT'){
					$("#divBank").css('display', 'none');
					$("#divBankAccount").css('display', 'block');
					$("#txtBank").val('');
					$("#divBankAccount").css('display', 'block');
					$("#txtBankAccount").removeAttr('disabled');
					//$("#txtBankAccount").val($("#txtRUT").val());
					//$("#txtBankAccount").prop('disabled','true');

				}else if($('#listPaymentMode').val()=='OTRA CUENTA'){
					$("#divBank").css('display', 'block');
					$("#divBankAccount").css('display', 'block');
					$("#txtBankAccount").removeAttr('disabled');
				}
			});
		}

		function deleteRow(id){
			$("#modal-delete-text").text('¿Está seguro de eliminar el registro '+id+'?');
			$("#modal-delete-id").text(id);
			$("#modalDelete").modal('show');
		}

		function contactRow(id, state, observation){
			$("#modal-contact-text").text('Seleccione estado de contacto');
			$("#listContact").val(state);
			$("#contactObservation").val(observation);
			$("#modalContact").modal('show');
			$("#btnContactSave").removeAttr("onClick");
			$("#btnContactSave").attr("onClick","contactSave("+id+");");
		}

		function contactSave(id){
			$.post('../../phps/personal_Save.php', {type: 'contact', id: id, state: $("#listContact").val(), observation: $("#contactObservation").val()}, function(data, textStatus, xhr) {
				var contact_color = 'default';
				
				if($("#listContact").val()=='CONTACTADO') contact_color='success';
				if($("#listContact").val()=='VOLVER A CONTACTAR') contact_color='warning';
				if($("#listContact").val()=='NO CONTACTAR') contact_color='danger';

				$("#contactId_"+id).removeAttr("class");
				$("#contactId_"+id).attr("class","btn btn-"+contact_color);
				$("#contactId_"+id).removeAttr("data-content");
				$("#contactId_"+id).attr("data-content",$("#contactObservation").val());
				$("#contactId_"+id).removeAttr("onClick");
				$("#contactId_"+id).attr("onClick","contactRow("+id+",'"+$("#listContact").val()+"','"+$("#contactObservation").val()+"')");
				
				$("#listContact").val('NO CONTACTADO');
				$("#contactObservation").val('');
				$("#modalContact").modal('hide');

			});
		}

		function loadSectors(type, id){
			if(type=="all"){
				$.post('../../phps/sector_Load.php', {type: "list", commune_id: $("#listCommune").val()}, function(data, textStatus, xhr) {
					var data = JSON.parse(data);
					$("#listSector").html('');
					$("#listSector").append('<option value="0">-</option>');
					for(i=0;i<data.length;i++){
						$("#listSector").append('<option value="'+data[i]["id"]+'">'+data[i]["name"]+'</option>');
					}
				});
			}else{
				$.post('../../phps/sector_Load.php', {type: "list", commune_id: $("#listCommune").val()}, function(data, textStatus, xhr) {
					var data = JSON.parse(data);
					$("#listSector").html('');
					$("#listSector").append('<option value="0">-</option>');
					for(i=0;i<data.length;i++){
						if(id==data[i]["id"]) selected="selected";
						else selected="";
						$("#listSector").append('<option value="'+data[i]["id"]+'" '+selected+'>'+data[i]["name"]+'</option>');
					}
				});
			}
		}

		function loadCommunes(type, id, idSector){
			if(type=="all"){
				$.post('../../phps/commune_Load.php', {type: "list", region_id: $("#listRegion").val()}, function(data, textStatus, xhr) {
					var data = JSON.parse(data);
					$("#listCommune").html('');
					for(i=0;i<data.length;i++){
						$("#listCommune").append('<option value="'+data[i]["id"]+'">'+data[i]["name"]+'</option>');
					}
					loadSectors("all",0);
				});
			}else{
				$.post('../../phps/commune_Load.php', {type: "list", region_id: $("#listRegion").val()}, function(data, textStatus, xhr) {
					var data = JSON.parse(data);
					$("#listCommune").html('');
					var selected = "";
					for(i=0;i<data.length;i++){
						if(id==data[i]["id"]) selected="selected";
						else selected="";
						$("#listCommune").append('<option value="'+data[i]["id"]+'" '+selected+'>'+data[i]["name"]+'</option>');
					}
					loadSectors("selected",idSector);
				});
			}
			$("#listCommune").change(function(){
				loadSectors("all",0);
			});
		}

		function loadRegions(){
			$.post('../../phps/region_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				for(i=0;i<data.length;i++){
					$("#listRegion").append('<option value="'+data[i]["id"]+'">'+data[i]["number"]+' - '+data[i]["name"]+'</option>');
				}
				loadCommunes('all',0);
			});
			$("#listRegion").change(function(){
				loadCommunes('all',0);
			});
		}

		function loadCharges(){
			$.post('../../phps/charge_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				$("#listCharge").append('<option value="0">SELECCIONAR</option>');

				for(i=0;i<data.length;i++){
					$("#listCharge").append('<option value="'+data[i]["id"]+'">'+data[i]["name"]+'</option>');
				}
			});
		}

		function loadHealthSystem(){
			$.post('../../phps/healthSystem_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				$("#listHealthSystem").append('<option value="0">SELECCIONAR</option>');

				for(i=0;i<data.length;i++){
					$("#listHealthSystem").append('<option value="'+data[i]["id"]+'">'+data[i]["name"]+'</option>');
				}
			});
		}

		function loadAFP(){
			$.post('../../phps/afp_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				$("#listAFP").append('<option value="0">SELECCIONAR</option>');

				for(i=0;i<data.length;i++){
					$("#listAFP").append('<option value="'+data[i]["id"]+'">'+data[i]["name"]+'</option>');
				}
			});
		}

		function loadDriverLicense(){
			$.post('../../phps/driverLicense_Load.php', {type: "all"}, function(data, textStatus, xhr) {
				var data = JSON.parse(data);
				//$("#listDriverLicense").append('<option value="0">NO POSEE</option>');
				for(i=0;i<data.length;i++){
					$("#listDriverLicense").append('<option value="'+data[i]["id"]+'">'+data[i]["name"]+'</option>');
				}
				$('#listDriverLicense').multiselect({
					includeSelectAllOption: false
				});

			});
		}

		function cleanModal(){
			$("#divID").css('display','none');
			$("#labelID").text('');
			$("#labelState").text('DISPONIBLE');
			$("#modalNew").modal('hide');
			$('#txtRUT').val('');
			$('#txtName').val('');
			$('#txtLastname1').val('');
			$('#txtLastname2').val('');
			$('#txtBirthDate').val(moment().format('DD/MM/YYYY'));

			$('#listGender').val('MASCULINO');
			$('#listCivilStatus').val('SOLTERO');
			/*$('#listRegion').val('');
			$('#listRegion').val('');
			$('#listCommune').val('');
			$('#listCommune').val('');
			$('#listSector').val('');*/
			$('#txtAddress').val('');
			//$('#txtAddressNumber').val('');
			//$('#listCharge').val('');
			//$('#listDriverLicense').val(0);
			$('#listDriverLicense').multiselect('deselectAll',false);
			$('#listDriverLicense').multiselect('refresh');

			$('#txtLicenseDate').val(moment().format('DD/MM/YYYY'));
			$('#listTurn').val('A');
			$('#txtPhone').val('');
			$('#txtCellphone').val('');
			$('#txtMail').val('');
			$('#listClothingSize').val("0");
			$('#listShoeSize').val("0");
			$('#txtRutDate').val(moment().format('DD/MM/YYYY'));


			$("#listPaymentMode").val('CHEQUE');
			$("#txtBank").val('');
			$("#txtBankAccount").val('');
			$("#divBank").css('display', 'none');
			$("#divBankAccount").css('display', 'none');

			$("#listCharge").val('0');
			$("#listHealthSystem").val('0');
			$("#listAFP").val('0');
		}

		function verifyData(){
			var result=true;
			if($("#txtName").val()==''){
				$("#txtName").parent().addClass('has-error');
				result=false;
			}
			if($("#txtLastname1").val()==''){
				$("#txtLastname1").parent().addClass('has-error');
				result=false;
			}
			if($("#txtLastname2").val()==''){
				$("#txtLastname2").parent().addClass('has-error');
				result=false;
			}
			if($("#txtAddress").val()==''){
				$("#txtAddress").parent().addClass('has-error');
				result=false;
			}
			return result;
		}

	</script>

</head>
<body id="body">
	<div class="container">
		<div class="row">
			<div id="menuPrincipal">
			</div>
		</div>
	</div>
	<div class="container">
		<div class="row">
			<div class="panel panel-redto">
				<div class="panel-heading"><i class="fa fa-group fa-lg fa-fw"></i>&nbsp;&nbsp; Personal</div>
				<div class="panel-body">	
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<button id="new" class="btn btn-redto" <?php echo $_SESSION["display"]["personal"]["insert"]; ?>><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>&nbsp;&nbsp; Ingresar Nuevo</button></td>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
							<button class="btn btn-default"><i class="fa fa-phone fa-lg fa-fw"></i></button>NO CONTACTADO
							<button class="btn btn-success"><i class="fa fa-phone fa-lg fa-fw"></i></button>CONTACTADO
							<button class="btn btn-warning"><i class="fa fa-phone fa-lg fa-fw"></i></button>VOLVER A CONTACTAR
							<button class="btn btn-danger"><i class="fa fa-phone fa-lg fa-fw"></i></button>NO CONTACTAR
						</div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
							<button id="toExcel" class="btn btn-success">Exportar a Excel  <img src="../../images/excel.ico"/></button>
						</div>
						<!--<div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
							<input id="txtFilter" type="text" class="form-control">
						</div>
						<div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
							<button id="btnFilter" class="btn btn-redto"><i class="fa fa-filter"></i>&nbsp;&nbsp; Filtrar</button>
						</div>-->
					</div>	
					<br/>
					<br/>
					<div class="table-responsive">
						<table id="tablaRegistros" class="table table-hover">
							<tr>
								<th>ID</th>
								<th>RUT</th>
								<th>Nombre</th>
								<th>Apellidos</th>
								<th>Comuna</th>
								<th>Celular</th>
								<th>Contactado</th>
								<th>Editar</th>
								<th>Eliminar</th>

							</tr>
						</table>
						<table id="tablaRegistrosExcel" style="display: none;">
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
		        	<button id="modalHide" type="button" class="btn btn-redto">Aceptar</button>
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
		        	<button id="modalDeleteHide" type="button" class="btn btn-redto">Cancelar</button>
		      	</div>
		    </div>
		</div>
	</div>

	<div id="modalContact" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
	        	<div class="modal-body">
		    	    <p id="modal-contact-text"></p>
		    	    <label>Estado contacto:</label>
		    	    <select id="listContact" class="form-control">
  						<option value="NO CONTACTADO">NO CONTACTADO</option>
						<option value="CONTACTADO">CONTACTADO</option>
						<option value="VOLVER A CONTACTAR">VOLVER A CONTACTAR</option>
						<option value="NO CONTACTAR">NO CONTACTAR</option>
					</select>
					<label>Observación:</label>
					<textarea id="contactObservation" class="form-control rowText" maxlength="100"></textarea>
		      	</div>
		      	<div class="modal-footer">
		        	<button id="btnContactSave" type="button" class="btn btn-success">Almacenar</button>
		        	<button id="modalContactHide" type="button" class="btn btn-redto">Cancelar</button>
		      	</div>
		    </div>
		</div>
	</div>

	<div id="modalNew" class="modal fade" data-backdrop="static">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
	        	<div class="modal-body">
				   	<div id="addNew" class="container-fluid">
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
								<div class="panel panel-redto">
									<div class="panel-heading"><span class="glyphicon glyphicon-copy" aria-hidden="true"></span>&nbsp;&nbsp; Ingreso de Registro</div>
									<div class="panel-body">
										<div class="container-fluid">
											<div class="row">
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
										  			<label>RUT(*):</label>
					  								<input id="txtRUT" type="Name" class="form-control rutOnly mandatory" maxlength="15">
												</div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
													<label>Fecha Nacimiento(*):</label>
					  								<div class="input-group">
														<input id="txtBirthDate" type="text" class="form-control datepickerTxt">
														<span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></span>
													</div>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
										  			<label>Estado Civil(*):</label>
					  								<select id="listCivilStatus" class="form-control" style="width: 100%;">
									  					<option value="SOLTERO">SOLTERO</option>
					  									<option value="CASADO">CASADO</option>
					  									<option value="CONVIVIENTE">CONVIVIENTE</option>
					  									<option value="DIVORCIADO">DIVORCIADO</option>
					  									<option value="SEPARADO">SEPARADO</option>
					  									<option value="VIUDO">VIUDO</option>
					  								</select>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
										  			<label>Nombre(*):</label>
					  								<input id="txtName" type="Name" class="form-control mandatory" maxlength="45">
												</div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
										  			<label>Apellido Paterno(*):</label>
					  								<input id="txtLastname1" type="Name" class="form-control mandatory" maxlength="45">
												</div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
										  			<label>Apellido Materno(*):</label>
					  								<input id="txtLastname2" type="Name" class="form-control mandatory" maxlength="45">
												</div>

												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
										  			<label>Región:</label>
					  								<select id="listRegion" class="form-control" style="width: 100%;"></select>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
										  			<label>Comuna:</label>
					  								<select id="listCommune" class="form-control" style="width: 100%;"></select>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
										  			<label>Sector:</label>
					  								<select id="listSector" class="form-control" style="width: 100%;"></select>
												</div>

												<div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
										  			<label>Dirección(*):</label>
					  								<input id="txtAddress" type="Name" class="form-control mandatory" maxlength="100">
												</div>
												<!--<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
										  			<label>Número:</label>
					  								<input id="txtAddressNumber" type="Name" class="form-control" maxlength="45">
												</div>-->
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
										  			<label>Sexo:</label>
					  								<select id="listGender" class="form-control" style="width: 100%;">
					  									<option value="M">MASCULINO</option>
									  					<option value="F">FEMENINO</option>
					  								</select>
												</div>

												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
										  			<label>Sistema Salud:</label>
					  								<select id="listHealthSystem" class="form-control" style="width: 100%;"></select>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
										  			<label>AFP:</label>
					  								<select id="listAFP" class="form-control" style="width: 100%;"></select>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
										  			<label>Especialidad:</label>
					  								<select id="listCharge" class="form-control" style="width: 100%;"></select>
												</div>

												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
										  			<label>Licencia de Conducir:</label>
										  			<br/>
					  								<select id="listDriverLicense" class="form-control" multiple="multiple" style="width: 100%;"></select>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
													<label>Vigencia Licencia:</label>
					  								<div class="input-group">
														<input id="txtLicenseDate" type="text" class="form-control datepickerTxt">
														<span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></span>
													</div>
												</div>


												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
										  			<label>Teléfono Fijo:</label>
					  								<input id="txtPhone" type="Name" class="form-control" maxlength="45">
												</div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
										  			<label>Celular:</label>
					  								<input id="txtCellphone" type="Name" class="form-control" maxlength="45">
												</div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
										  			<label>E-Mail:</label>
					  								<input id="txtMail" type="Name" class="form-control" maxlength="45">
												</div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
													<label>Vigencia Cédula Identidad:</label>
					  								<div class="input-group">
														<input id="txtRutDate" type="text" class="form-control datepickerTxt">
														<span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></span>
													</div>
												</div>

												<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
										  			<label>Turno Pref.:</label>
					  								<select id="listTurn" class="form-control" style="width: 100%;">
					  									<option value="A">A</option>
									  					<option value="B">B</option>
									  					<option value="C">C</option>
									  					<option value="R">ROT</option>
					  								</select>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
										  			<label>Talla:</label>
					  								<select id="listClothingSize" class="form-control" style="width: 100%;">
					  									<option value="0">-</option>
					  									<option value="XS">XS</option>
									  					<option value="S">S</option>
									  					<option value="M">M</option>
									  					<option value="L">L</option>
									  					<option value="XL">XL</option>
									  					<option value="XXL">XXL</option>
					  								</select>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
										  			<label>N° Calzado:</label>
					  								<select id="listShoeSize" class="form-control" style="width: 100%;">
					  									<option value="0">-</option>
					  									<option value="35">35</option>
					  									<option value="36">36</option>
					  									<option value="37">37</option>
					  									<option value="38">38</option>
					  									<option value="39">39</option>
					  									<option value="40">40</option>
					  									<option value="41">41</option>
					  									<option value="42">42</option>
					  									<option value="43">43</option>
					  									<option value="44">44</option>
					  									<option value="45">45</option>
					  								</select>
												</div>
												<div class="col-xs-0 col-sm-0 col-md-2 col-lg-2"></div>
												<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
										  			<label>Modalidad de Pago:</label>
					  								<select id="listPaymentMode" class="form-control" style="width: 100%;">
					  									<option value="CHEQUE">CHEQUE</option>
									  					<option value="CUENTA RUT">CUENTA RUT</option>
									  					<option value="OTRA CUENTA">OTRA CUENTA</option>
					  								</select>
												</div>
												<div class="col-xs-0 col-sm-0 col-md-8 col-lg-8"></div>

												<div id="divBank" class="col-xs-12 col-sm-12 col-md-4 col-lg-4" style="display: none;">
										  			<label>Banco:</label>
					  								<input id="txtBank" type="Name" class="form-control" maxlength="45">
												</div>

												<div class="col-xs-0 col-sm-0 col-md-8 col-lg-8"></div>

												<div id="divBankAccount" class="col-xs-0 col-sm-0 col-md-4 col-lg-4" style="display: none;">
										  			<label>N° Cuenta:</label>
					  								<input id="txtBankAccount" type="Name" class="form-control" maxlength="45">
												</div>
											</div>
											<div class="row">
												<br/>
												<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
										  			<label>Estado:</label>
										  			<label id="labelState">DISPONIBLE</label>
												</div>												
												<div id="divID" class="col-xs-4 col-sm-4 col-md-4 col-lg-4" style="display: none;">
										  			<label>ID:</label>
										  			<label id="labelID"></label>
												</div>		
												<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
										  			<label>(*) Datos obligatorios:</label>
												</div>											

											</div>
										</div>
										<br/>
										<div style="text-align:right;">
											<div style="display:inline-block;"><button id="save" class="btn btn-success"><span class="glyphicon glyphicon-save" aria-hidden="true"></span>&nbsp;&nbsp; Almacenar</button></div>
											<div style="display:inline-block;"><button id="cancel" class="btn btn-redto"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span>&nbsp;&nbsp; Cancelar</button></div>
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