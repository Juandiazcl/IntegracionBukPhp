<?php
header('Content-Type: text/html; charset=utf8'); 
session_start();

if(!isset($_SESSION['userId'])){
	header('Location: ../../login.php');
}elseif($_SESSION['display']['holidays']['view']!=''){
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
	<link rel="stylesheet" href="../../libs/dynatable/jquery.dynatable.css"></script>
	<script type="text/javascript" src="../../libs/dynatable/jquery.dynatable.js"></script>	
	<script type="text/javascript" src="../../libs/jquery.table2excel.js"></script>	
	<title></title>
	<script type="text/javascript">

	var idList = 1, calculation = false;
	var d = new Date();

		$(document).ready(function() {
			$("#txtYear").val(d.getFullYear());

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

			$("#cancelView").click(function() {
				$("#modalView").modal('hide');
				$("#txtViewRUT").val('');
				$("#txtViewName").val('');
				$("#tableHistory").html('<thead><tr><th>Empresa</th><th>Campo</th><th>Sueldo Base</th><th>Inicio Contrato</th><th>Fin Contrato</th><th>Causa</th><th>Ver Ficha</th></tr></thead><tbody></tbody>');
			});

			$("#listState").change(function(){
				loadRegistros();
			});
			$("#listPlant").change(function(){
				loadRegistros();
			});

			$("#txtYear").focusout(function(){
				if($(this).val()==""){
					$(this).val(d.getFullYear());
				}
				loadRegistros();
			});

			$(".calendarTD").click(function(){
				var id = $(this).attr("id");
				var day = $(this).html();
				if(day.length==1){
					day = "0"+day;
				}
				var month = $(this).attr("id")[0]+$(this).attr("id")[1];
				var year = $("#txtYear").val();
				var date = day+"/"+month+"/"+year;

				$.post('../../phps/holiday_Save.php', {type: 'save', date: date, cellID: id}, function(data, textStatus, xhr) {
					if(data=="save"){
						$("#"+id).css("color","red");
					}else{
						$("#"+id).css("color","black");
					}
				});

			});

			loadRegistros();
		});

		function loadRegistros(){
			$(".calendarTD").html('&nbsp;');
			$(".calendarNormal").css('color','black');
			$(".calendarSaturday").css('color','blue');
			for(i=1;i<=12;i++){
				var monthNumber = "";
				if(i<10){
					monthNumber = "0"+i.toString();
				}else{
					monthNumber = i.toString();
				}
				var month = moment("01-"+monthNumber+"-"+$("#txtYear").val(), "DD-MM-YYYY");
				var day = month.day();
				if(day==0){
					day = 7;
				}
				var week = "1";
				$("#"+monthNumber+week+day).html(1);
				for(j=2;j<=month.daysInMonth();j++){
					day++;
					if(day==8){
						day=1;
						week++;
					}
					$("#"+monthNumber+week+day).html(j);
				}
			}
			$.post('../../phps/holiday_Load.php', {type: "all", year: $("#txtYear").val()}, function(data, textStatus, xhr) {
				if(data!=0){
					var data = JSON.parse(data);
					for(i=0;i<data.length;i++){
						$("#"+data[i]['CeldaID']).css("color","red");
					}
				}
			});
		}

		function cleanModal(){
			$("#divID").css('display','none');
			$("#labelID").text('');
			$("#modalNew").modal('hide');
			loadRegistros();
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
				<div class="panel-heading"><i class="fa fa-calendar fa-lg fa-fw"></i>&nbsp;&nbsp; Días Festivos</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-5 col-lg-5" style="text-align: center;"></div>
						<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2" style="text-align: center;">
							<label>Año:</label>
							<input id="txtYear" type="Name" class="form-control numbersOnly" style="text-align: center;">
						</div>
					</div>	
					<br/>
					<br/>
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							<div class="panel panel-primary">
								<div class="panel-heading" style="text-align: center;">Enero</div>
								<div class="panel-body">
									<table style="width:100%">
										<thead>
											<tr>
												<th class="calendarDayTitle" style="width:14.2%;">L</th>
												<th class="calendarDayTitle" style="width:14.2%;">M</th>
												<th class="calendarDayTitle" style="width:14.2%;">M</th>
												<th class="calendarDayTitle" style="width:14.2%;">J</th>
												<th class="calendarDayTitle" style="width:14.2%;">V</th>
												<th class="calendarDayTitle" style="width:14.2%;">S</th>
												<th class="calendarDayTitle" style="width:14.8%;">D</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td id="0111" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0112" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0113" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0114" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0115" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0116" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0117" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0121" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0122" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0123" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0124" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0125" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0126" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0127" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0131" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0132" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0133" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0134" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0135" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0136" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0137" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0141" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0142" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0143" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0144" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0145" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0146" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0147" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0151" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0152" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0153" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0154" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0155" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0156" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0157" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0161" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0162" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0163" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0164" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0165" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0166" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0167" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							<div class="panel panel-primary">
								<div class="panel-heading" style="text-align: center;">Febrero</div>
								<div class="panel-body">
									<table style="width:100%">
										<thead>
											<tr>
												<th class="calendarDayTitle" style="width:14.2%;">L</th>
												<th class="calendarDayTitle" style="width:14.2%;">M</th>
												<th class="calendarDayTitle" style="width:14.2%;">M</th>
												<th class="calendarDayTitle" style="width:14.2%;">J</th>
												<th class="calendarDayTitle" style="width:14.2%;">V</th>
												<th class="calendarDayTitle" style="width:14.2%;">S</th>
												<th class="calendarDayTitle" style="width:14.8%;">D</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td id="0211" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0212" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0213" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0214" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0215" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0216" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0217" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0221" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0222" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0223" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0224" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0225" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0226" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0227" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0231" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0232" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0233" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0234" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0235" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0236" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0237" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0241" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0242" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0243" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0244" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0245" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0246" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0247" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0251" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0252" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0253" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0254" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0255" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0256" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0257" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0261" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0262" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0263" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0264" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0265" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0266" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0267" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							<div class="panel panel-primary">
								<div class="panel-heading" style="text-align: center;">Marzo</div>
								<div class="panel-body">
									<table style="width:100%">
										<thead>
											<tr>
												<th class="calendarDayTitle" style="width:14.2%;">L</th>
												<th class="calendarDayTitle" style="width:14.2%;">M</th>
												<th class="calendarDayTitle" style="width:14.2%;">M</th>
												<th class="calendarDayTitle" style="width:14.2%;">J</th>
												<th class="calendarDayTitle" style="width:14.2%;">V</th>
												<th class="calendarDayTitle" style="width:14.2%;">S</th>
												<th class="calendarDayTitle" style="width:14.8%;">D</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td id="0311" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0312" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0313" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0314" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0315" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0316" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0317" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0321" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0322" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0323" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0324" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0325" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0326" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0327" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0331" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0332" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0333" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0334" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0335" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0336" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0337" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0341" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0342" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0343" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0344" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0345" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0346" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0347" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0351" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0352" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0353" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0354" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0355" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0356" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0357" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0361" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0362" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0363" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0364" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0365" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0366" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0367" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							<div class="panel panel-primary">
								<div class="panel-heading" style="text-align: center;">Abril</div>
								<div class="panel-body">
									<table style="width:100%">
										<thead>
											<tr>
												<th class="calendarDayTitle" style="width:14.2%;">L</th>
												<th class="calendarDayTitle" style="width:14.2%;">M</th>
												<th class="calendarDayTitle" style="width:14.2%;">M</th>
												<th class="calendarDayTitle" style="width:14.2%;">J</th>
												<th class="calendarDayTitle" style="width:14.2%;">V</th>
												<th class="calendarDayTitle" style="width:14.2%;">S</th>
												<th class="calendarDayTitle" style="width:14.8%;">D</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td id="0411" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0412" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0413" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0414" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0415" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0416" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0417" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0421" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0422" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0423" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0424" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0425" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0426" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0427" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0431" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0432" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0433" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0434" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0435" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0436" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0437" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0441" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0442" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0443" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0444" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0445" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0446" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0447" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0451" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0452" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0453" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0454" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0455" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0456" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0457" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0461" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0462" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0463" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0464" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0465" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0466" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0467" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>


						<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							<div class="panel panel-primary">
								<div class="panel-heading" style="text-align: center;">Mayo</div>
								<div class="panel-body">
									<table style="width:100%">
										<thead>
											<tr>
												<th class="calendarDayTitle" style="width:14.2%;">L</th>
												<th class="calendarDayTitle" style="width:14.2%;">M</th>
												<th class="calendarDayTitle" style="width:14.2%;">M</th>
												<th class="calendarDayTitle" style="width:14.2%;">J</th>
												<th class="calendarDayTitle" style="width:14.2%;">V</th>
												<th class="calendarDayTitle" style="width:14.2%;">S</th>
												<th class="calendarDayTitle" style="width:14.8%;">D</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td id="0511" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0512" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0513" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0514" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0515" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0516" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0517" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0521" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0522" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0523" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0524" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0525" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0526" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0527" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0531" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0532" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0533" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0534" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0535" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0536" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0537" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0541" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0542" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0543" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0544" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0545" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0546" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0547" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0551" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0552" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0553" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0554" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0555" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0556" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0557" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0561" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0562" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0563" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0564" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0565" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0566" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0567" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							<div class="panel panel-primary">
								<div class="panel-heading" style="text-align: center;">Junio</div>
								<div class="panel-body">
									<table style="width:100%">
										<thead>
											<tr>
												<th class="calendarDayTitle" style="width:14.2%;">L</th>
												<th class="calendarDayTitle" style="width:14.2%;">M</th>
												<th class="calendarDayTitle" style="width:14.2%;">M</th>
												<th class="calendarDayTitle" style="width:14.2%;">J</th>
												<th class="calendarDayTitle" style="width:14.2%;">V</th>
												<th class="calendarDayTitle" style="width:14.2%;">S</th>
												<th class="calendarDayTitle" style="width:14.8%;">D</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td id="0611" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0612" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0613" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0614" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0615" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0616" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0617" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0621" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0622" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0623" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0624" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0625" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0626" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0627" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0631" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0632" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0633" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0634" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0635" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0636" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0637" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0641" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0642" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0643" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0644" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0645" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0646" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0647" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0651" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0652" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0653" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0654" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0655" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0656" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0657" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0661" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0662" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0663" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0664" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0665" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0666" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0667" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							<div class="panel panel-primary">
								<div class="panel-heading" style="text-align: center;">Julio</div>
								<div class="panel-body">
									<table style="width:100%">
										<thead>
											<tr>
												<th class="calendarDayTitle" style="width:14.2%;">L</th>
												<th class="calendarDayTitle" style="width:14.2%;">M</th>
												<th class="calendarDayTitle" style="width:14.2%;">M</th>
												<th class="calendarDayTitle" style="width:14.2%;">J</th>
												<th class="calendarDayTitle" style="width:14.2%;">V</th>
												<th class="calendarDayTitle" style="width:14.2%;">S</th>
												<th class="calendarDayTitle" style="width:14.8%;">D</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td id="0711" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0712" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0713" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0714" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0715" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0716" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0717" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0721" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0722" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0723" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0724" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0725" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0726" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0727" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0731" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0732" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0733" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0734" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0735" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0736" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0737" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0741" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0742" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0743" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0744" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0745" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0746" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0747" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0751" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0752" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0753" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0754" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0755" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0756" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0757" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0761" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0762" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0763" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0764" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0765" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0766" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0767" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							<div class="panel panel-primary">
								<div class="panel-heading" style="text-align: center;">Agosto</div>
								<div class="panel-body">
									<table style="width:100%">
										<thead>
											<tr>
												<th class="calendarDayTitle" style="width:14.2%;">L</th>
												<th class="calendarDayTitle" style="width:14.2%;">M</th>
												<th class="calendarDayTitle" style="width:14.2%;">M</th>
												<th class="calendarDayTitle" style="width:14.2%;">J</th>
												<th class="calendarDayTitle" style="width:14.2%;">V</th>
												<th class="calendarDayTitle" style="width:14.2%;">S</th>
												<th class="calendarDayTitle" style="width:14.8%;">D</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td id="0811" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0812" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0813" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0814" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0815" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0816" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0817" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0821" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0822" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0823" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0824" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0825" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0826" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0827" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0831" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0832" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0833" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0834" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0835" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0836" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0837" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0841" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0842" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0843" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0844" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0845" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0846" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0847" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0851" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0852" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0853" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0854" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0855" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0856" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0857" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0861" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0862" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0863" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0864" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0865" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0866" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0867" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>

						<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							<div class="panel panel-primary">
								<div class="panel-heading" style="text-align: center;">Septiembre</div>
								<div class="panel-body">
									<table style="width:100%">
										<thead>
											<tr>
												<th class="calendarDayTitle" style="width:14.2%;">L</th>
												<th class="calendarDayTitle" style="width:14.2%;">M</th>
												<th class="calendarDayTitle" style="width:14.2%;">M</th>
												<th class="calendarDayTitle" style="width:14.2%;">J</th>
												<th class="calendarDayTitle" style="width:14.2%;">V</th>
												<th class="calendarDayTitle" style="width:14.2%;">S</th>
												<th class="calendarDayTitle" style="width:14.8%;">D</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td id="0911" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0912" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0913" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0914" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0915" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0916" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0917" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0921" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0922" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0923" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0924" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0925" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0926" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0927" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0931" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0932" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0933" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0934" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0935" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0936" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0937" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0941" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0942" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0943" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0944" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0945" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0946" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0947" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0951" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0952" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0953" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0954" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0955" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0956" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0957" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="0961" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0962" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0963" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0964" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0965" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="0966" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="0967" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							<div class="panel panel-primary">
								<div class="panel-heading" style="text-align: center;">Octubre</div>
								<div class="panel-body">
									<table style="width:100%">
										<thead>
											<tr>
												<th class="calendarDayTitle" style="width:14.2%;">L</th>
												<th class="calendarDayTitle" style="width:14.2%;">M</th>
												<th class="calendarDayTitle" style="width:14.2%;">M</th>
												<th class="calendarDayTitle" style="width:14.2%;">J</th>
												<th class="calendarDayTitle" style="width:14.2%;">V</th>
												<th class="calendarDayTitle" style="width:14.2%;">S</th>
												<th class="calendarDayTitle" style="width:14.8%;">D</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td id="1011" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1012" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1013" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1014" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1015" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1016" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="1017" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="1021" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1022" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1023" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1024" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1025" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1026" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="1027" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="1031" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1032" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1033" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1034" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1035" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1036" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="1037" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="1041" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1042" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1043" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1044" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1045" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1046" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="1047" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="1051" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1052" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1053" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1054" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1055" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1056" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="1057" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="1061" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1062" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1063" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1064" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1065" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1066" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="1067" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							<div class="panel panel-primary">
								<div class="panel-heading" style="text-align: center;">Noviembre</div>
								<div class="panel-body">
									<table style="width:100%">
										<thead>
											<tr>
												<th class="calendarDayTitle" style="width:14.2%;">L</th>
												<th class="calendarDayTitle" style="width:14.2%;">M</th>
												<th class="calendarDayTitle" style="width:14.2%;">M</th>
												<th class="calendarDayTitle" style="width:14.2%;">J</th>
												<th class="calendarDayTitle" style="width:14.2%;">V</th>
												<th class="calendarDayTitle" style="width:14.2%;">S</th>
												<th class="calendarDayTitle" style="width:14.8%;">D</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td id="1111" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1112" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1113" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1114" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1115" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1116" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="1117" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="1121" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1122" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1123" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1124" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1125" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1126" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="1127" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="1131" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1132" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1133" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1134" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1135" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1136" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="1137" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="1141" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1142" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1143" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1144" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1145" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1146" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="1147" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="1151" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1152" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1153" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1154" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1155" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1156" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="1157" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="1161" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1162" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1163" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1164" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1165" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1166" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="1167" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							<div class="panel panel-primary">
								<div class="panel-heading" style="text-align: center;">Diciembre</div>
								<div class="panel-body">
									<table style="width:100%">
										<thead>
											<tr>
												<th class="calendarDayTitle" style="width:14.2%;">L</th>
												<th class="calendarDayTitle" style="width:14.2%;">M</th>
												<th class="calendarDayTitle" style="width:14.2%;">M</th>
												<th class="calendarDayTitle" style="width:14.2%;">J</th>
												<th class="calendarDayTitle" style="width:14.2%;">V</th>
												<th class="calendarDayTitle" style="width:14.2%;">S</th>
												<th class="calendarDayTitle" style="width:14.8%;">D</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td id="1211" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1212" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1213" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1214" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1215" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1216" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="1217" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="1221" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1222" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1223" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1224" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1225" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1226" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="1227" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="1231" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1232" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1233" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1234" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1235" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1236" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="1237" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="1241" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1242" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1243" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1244" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1245" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1246" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="1247" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="1251" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1252" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1253" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1254" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1255" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1256" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="1257" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
											<tr>
												<td id="1261" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1262" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1263" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1264" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1265" class="calendarTD calendarNormal">&nbsp;</td>
												<td id="1266" class="calendarTD calendarSaturday">&nbsp;</td>
												<td id="1267" class="calendarTD calendarSunday">&nbsp;</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
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

	<div id="modalDelete" class="modal fade" data-backdrop="static">
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

</body>
</html>