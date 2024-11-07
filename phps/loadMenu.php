<?php
header('Content-Type: text/html; charset=UTF-8'); 

include("../connection/connection.php");

if($_POST['where']=='include'){
	$where='../';
}else if($_POST['where']=='main' || $_POST['where']=='conf' || $_POST['where']=='trans' || $_POST['where']=='tally'){
	$where='../../';
}else{
	$where='';
}

session_start();

//Actualización de estado finalizado
$array = executeSelect("SELECT fp.*, 
						FORMAT(f.fecha_creacion,'dd/mm/yyyy') AS settlementDate 
						FROM (FINIQUITO_PERSONAL fp 
						LEFT JOIN FINIQUITO f ON f.ID=fp.ID_FINIQUITO)
						WHERE fp.pago_estado='PENDIENTE'
						AND (fp.vacaciones_proporcionales+fp.liquidaciones+fp.gratificacion+fp.colacion+fp.movilizacion+fp.indemnizacion_servicio+fp.indemnizacion_aviso+fp.indemnizacion_voluntaria+fp.afc+fp.indemnizacion_mes) - (fp.otros_descuentos+fp.prestamo_empresa+fp.prestamo_caja)>0");
$alertTotal = count($array);
for($i=0;$i<count($array);$i++){
	$result = glob('../documents/uploads/'.$array[$i]["ID"].'.*');
	if($result){
		executeSql("UPDATE FINIQUITO_PERSONAL SET pago_estado='FINALIZADO' WHERE ID=".$array[$i]["ID"]);
		$alertTotal--;
	}else{
		$arrayDate = explode("/", $array[$i]["settlementDate"]);
		if($array[$i]["settlementDate"][2]=='-'){
			$arrayDate = explode("-", $array[$i]["settlementDate"]);
		}

		$dateEnd = strtotime($arrayDate[2]."/".$arrayDate[1]."/".$arrayDate[0]);
		$dateNow = strtotime(date('Y/m/d'));
		$days = 60 - round(abs($dateNow-$dateEnd)/86400);
		$array[$i]["expire"] = $days;
		if($days>45){
			$alertTotal--;
		}
	}
}

$menu1 = 'style="display: none;"';
$menu11 = 'style="display: none;"';

$menu2 = 'style="display: none;"';
$menu21 = 'style="display: none;"';
$menu22 = 'style="display: none;"';
$menu23 = 'style="display: none;"';
$menu24 = 'style="display: none;"';
$menu25 = 'style="display: none;"';
$menu26 = 'style="display: none;"';
$menu27 = 'style="display: none;"';

$menu3 = 'style="display: none;"';
$menu31 = 'style="display: none;"';
$menu32 = 'style="display: none;"';
$menu33 = 'style="display: none;"';
$menu34 = 'style="display: none;"';

$menu4 = 'style="display: none;"';
$menu41 = 'style="display: none;"';

$menu5 = 'style="display: none;"';
$menu51 = 'style="display: none;"';
$menu52 = 'style="display: none;"';
$menu53 = 'style="display: none;"';
$menu54 = 'style="display: none;"';

$menuAlert = 'style="display: none;"';

//if($_SESSION['profile']!='ADM'){

$menu11 = $_SESSION['display']['personal']['view'];
$menu21 = $_SESSION['display']['remunerationBook']['view'];
$menu22 = $_SESSION['display']['remuneration']['view'];
$menu23 = $_SESSION['display']['remuneration']['view']; //Para los históricos
$menu24 = $_SESSION['display']['settlement']['view'];
$menu25 = $_SESSION['display']['settlementPayment']['view'];
$menu26 = $_SESSION['display']['vacations']['view'];
$menu27 = $_SESSION['display']['loan']['view'];
$menu31 = $_SESSION['display']['tallies']['view'];
$menu32 = $_SESSION['display']['talliesPersonal']['view'];
$menu33 = $_SESSION['display']['talliesBuk']['view'];
//$menu34 = $_SESSION['display']['talliesPersonalB']['view'];
$menu41 = $_SESSION['display']['history']['view'];
$menu51 = $_SESSION['display']['holidays']['view'];
$menu52 = $_SESSION['display']['users']['view'];
$menu53 = $_SESSION['display']['user_types']['view'];
$menu54 = $_SESSION['display']['change_pass']['view'];
$menuAlert = $_SESSION['display']['alert']['view'];


if($menu11==''){
	$menu1 = '';
}
if($menu21=='' || $menu22=='' || $menu23=='' || $menu24=='' || $menu25=='' || $menu26=='' || $menu27==''){
	$menu2 = '';
}
if($menu31=='' || $menu32==''|| $menu33=='' || $menu34==''){
	$menu3 = '';
}
if($menu41==''){
	$menu4 = '';
}
if($menu51=='' || $menu52=='' || $menu53=='' || $menu54==''){
	$menu5 = '';
}
	
//<li '.$menu34.'><a href="'.$where.'include/tally/tallies.php"><i class="fa fa-address-book-o fa-lg fa-fw"></i>&nbsp;Proceso de Tarjas</a></li>

$menu =	'<nav class="navbar navbar-default navbar-static-top">
		<div class="container-fluid">
			<!-- Brand and toggle get grouped for better mobile display -->
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="'.$where.'index.php"><span class="glyphicon glyphicon-home" aria-hidden="true"></span>&nbsp;Inicio</a>
			</div>

			<!-- Collect the nav links, forms, and other content for toggling -->
			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<ul class="nav navbar-nav" '.$menu1.'>
					<li class="dropdown dropdownMenu">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" data-hover="dropdown">Mantención de Fichas <span class="caret"></span></a>
						<ul class="dropdown-menu dropdownMenu-menu">
							<li '.$menu11.'><a href="'.$where.'include/files/personal.php" ><i class="fa fa-address-book fa-lg fa-fw"></i>&nbsp;Personal</a></li>
						</ul>
					</li>
				</ul>

				<ul class="nav navbar-nav" '.$menu2.'>
					<li class="dropdown dropdownMenu">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" data-hover="dropdown">Remuneraciones <span class="caret"></span></a>
						<ul class="dropdown-menu dropdownMenu-menu">
							<li '.$menu21.'><a href="'.$where.'include/trans/remunerationBook.php"><i class="fa fa-book fa-lg fa-fw"></i>&nbsp;Libro de Remuneraciones</a></li>
							<li '.$menu22.'><a href="'.$where.'include/trans/remuneration.php"><i class="fa fa-user"></i><i class="fa fa-money"></i>&nbsp;Liquidaciones</a></li>
							<li '.$menu23.'><a href="'.$where.'include/trans/remunerationHistoric.php"><i class="fa fa-user"></i><i class="fa fa-clock-o"></i>&nbsp;Liquidaciones Histórico</a></li>
							<li '.$menu24.'><a href="'.$where.'include/trans/settlement.php"><i class="fa fa-user-times fa-lg fa-fw"></i>&nbsp;Finiquitos</a></li>
							<li '.$menu24.'><a href="'.$where.'include/trans/settlementHistoric.php"><i class="fa fa-user-times fa-lg fa-fw"></i><i class="fa fa-clock-o fa-fw"></i>&nbsp;Finiquitos Histórico</a></li>
							<li '.$menu25.'><a href="'.$where.'include/trans/settlementPayment.php"><i class="fa fa-vcard fa-lg fa-fw"></i>&nbsp;Cta Corriente Finiquitos</a></li>
							<li '.$menu26.'><a href="'.$where.'include/trans/vacations.php"><i class="fa fa-image fa-lg fa-fw"></i>&nbsp;Vacaciones</a></li>
							<li '.$menu27.'><a href="'.$where.'include/trans/loan.php"><i class="fa fa-money fa-lg fa-fw"></i>&nbsp;Préstamos</a></li>
						</ul>
					</li>
				</ul>

				<ul class="nav navbar-nav" '.$menu3.'>
					<li class="dropdown dropdownMenu">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" data-hover="dropdown">Tarjas <span class="caret"></span></a>
						<ul class="dropdown-menu dropdownMenu-menu">
							<li '.$menu31.'><a href="'.$where.'include/tally/talliesBuk.php"><i class="fa fa-vcard-o fa-lg fa-fw"></i>&nbsp;Proceso Tarjas a Buk</a></li>
							<li '.$menu32.'><a href="'.$where.'include/tally/talliesPersonalB.php"><i class="fa fa-vcard-o fa-lg fa-fw"></i>&nbsp;Tarjas x Rut</a></li>
							<li '.$menu33.'><a href="'.$where.'include/tally/configBuk.php"><i class="fa fa-vcard-o fa-lg fa-fw"></i>&nbsp;Config Buk</a></li>
							
							
							
						</ul>
					</li>
				</ul>

				<ul class="nav navbar-nav" '.$menu4.'>
					<li class="dropdown dropdownMenu">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" data-hover="dropdown">Personal <span class="caret"></span></a>
						<ul class="dropdown-menu dropdownMenu-menu">
							<li '.$menu41.'><a href="'.$where.'include/main/history.php"><i class="fa fa-history fa-lg fa-fw"></i>&nbsp;Historial</a></li>
						</ul>
					</li>
				</ul>

				<ul class="nav navbar-nav" '.$menu5.'>
					<li class="dropdown dropdownMenu">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Configurar <span class="caret"></span></a>
						<ul class="dropdown-menu dropdownMenu-menu">
							<li '.$menu51.'><a href="'.$where.'include/conf/holidays.php"><i class="fa fa-calendar fa-lg fa-fw"></i>&nbsp;Días Festivos</a></li>
							<li '.$menu52.'><a href="'.$where.'include/conf/users.php"><i class="fa fa-group fa-lg fa-fw"></i>&nbsp;Lista Usuarios</a></li>
							<li '.$menu53.'><a href="'.$where.'include/conf/user_types.php"><i class="fa fa-vcard fa-lg fa-fw"></i>&nbsp;Tipos de Usuario</a></li>
							<li '.$menu54.'><a href="'.$where.'include/conf/change_pass.php"><i class="fa fa-key fa-lg fa-fw"></i>&nbsp;Cambiar Contraseña</a></li>
						</ul>
					</li>
				</ul>
				


				<ul class="nav navbar-nav navbar-right">
	        		<li><a href="#" onclick="logout()">Desconectar&nbsp;<i class="fa fa-sign-out fa-lg fa-fw"></i></a></li>
	        	</ul>
				<ul class="nav navbar-nav navbar-right" '.$menuAlert.'>
					<li class="dropdown dropdownMenu" role="presentation">
						<a href="'.$where.'include/trans/settlementPayment.php?type=expired">Finiquitos por avisar&nbsp;<span class="badge"><span class="badge" style="color: yellow;">'.$alertTotal.'</span></a>
					</li>
				</ul>
				</div><!-- /.navbar-collapse -->
			</div><!-- /.container-fluid -->
		</nav>';

echo $menu;

?>