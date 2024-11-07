function loadMenu(){
	var path = window.location.pathname;
	path = path.split('/');
	
	if(path[path.length-2]=='include' || path[path.length-2]=='ext'){
		$.post('../phps/loadMenu.php', {where:"include", user: ""}, function(data, textStatus, xhr) {
			$("#menuPrincipal").append(data);
		});
	}else if(path[path.length-2]=='main' || path[path.length-2]=='contracts' || path[path.length-2]=='conf' || path[path.length-2]=='trans' || path[path.length-2]=='files' || path[path.length-2]=='tally'){
		$.post('../../phps/loadMenu.php', {where:"main", user: ""}, function(data, textStatus, xhr) {
			$("#menuPrincipal").append(data);
		});
	}else{
		$.post('phps/loadMenu.php', {where:"asistencia", user: ""}, function(data, textStatus, xhr) {
			$("#menuPrincipal").append(data);
		});
	}
}
//loadMenu();

function orderRUT(number){
	var newNumber = "", verify = "";
	if(number.length>1){
		verify='-'+number[number.length-1].replace('k','K');
		number=number.substring(0,number.length-1);
		for(i=number.length;i>0;i=i-3){
			if(i!=number.length){
				newNumber=number.substring(i-3,i)+'.'+newNumber;
			}else{
				newNumber=number.substring(i-3,i)+newNumber;
			}
		}
	}
	newNumber=newNumber+verify;
	return newNumber;
}

function verifyRUT(rut){
	var number = rut.replace('-','');
	number = number.replace(/\./g,'');
	var verify = number[number.length-1];
	number = number.substring(0,number.length-1);
	if($.isNumeric(number)){
		if(getVerify(number)==verify){
			return true;
		}else{
			return false;
		}
	}else{
		return false;
	}
}

function getVerify(rut) {
	var M=0,S=1;
	for(;rut;rut=Math.floor(rut/10))
	    S=(S+rut%10*(9-M++%6))%11;
	return S?S-1:'K';
}

function startParameters(){
	$(".numbersOnly").keypress(function (e) {
	    if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
           return false;
	    }
  	});

	$(".numbersOnlyPoint").keypress(function (e) {
	    if (e.which != 8 && e.which != 0 && e.which != 46 && (e.which < 48 || e.which > 57)) {
           return false;
	    }else if(e.which==46){
	    	if($(this).val().indexOf('.') != -1){
    			return false;
			}
	    }
  	});

	$(".numbersOnlyFloat").keypress(function (e) {
	    if (e.which != 8 && e.which != 0 && e.which != 44 && e.which != 46 && (e.which < 48 || e.which > 57)) {
           return false;
	    }
  	});

	$(".numbersOnlyFloatMoney").keypress(function (e) {
	    if (e.which != 8 && e.which != 0 && e.which != 44 && (e.which < 48 || e.which > 57)) {
           return false;
	    }
  	}); 

  	$(".numbersOnlyFloatMoney").focus(function () {
		$(this).val($(this).val().replace(/\./g, ''));
  	}); 

  	$(".numbersOnlyFloatMoney").focusout(function () {
  		console.log('here');
  		if($(this).val()[0]=='-'){
			$(this).val($(this).val().substring(1));
			$(this).val('-'+$(this).val().replace(/\D/g, ",").replace(/\B(?=(\d{3})+(?!\d)\.?)/g, "."));
		}else{
			$(this).val($(this).val().replace(/\D/g, ",").replace(/\B(?=(\d{3})+(?!\d)\.?)/g, "."));
		}
  	});


  	$(".numbersOnlyFloat2").keypress(function (e) {
	    if (e.which != 8 && e.which != 0 && e.which != 44 && (e.which < 48 || e.which > 57)) {
           return false;
	    }
  	}); 

  	$(".numbersOnlyFloat2").focus(function () {
		$(this).val($(this).val().replace(/\./g, ''));
  	}); 

  	$(".numbersOnlyFloat2").focusout(function () {
  		/*value.replace(/\D/g, "")
        .replace(/([0-9])([0-9]{2})$/, '$1.$2')
        .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");*/


  		if($(this).val()[0]=='-'){
			$(this).val($(this).val().substring(1));
			var nStr = $(this).val()+'';
    		x = nStr.split(',');
			x1 = x[0];
			x2 = x.length > 1 ? ',' + x[1] : '';
			var rgx = /(\d+)(\d{3})/;
			while (rgx.test(x1)) {
				x1 = x1.replace(rgx, '$1' + '.' + '$2');
			}
			$(this).val('-'+ x1 + x2);
		}else{
			//$(this).val($(this).val().replace(/\D/g, ",").replace(/([0-9])([0-9]{2})$/, '$1,$2').replace(/\B(?=(\d{3})+(?!\d)\.?)/g, "."));
			//It's something
			var nStr = $(this).val()+'';
    		x = nStr.split(',');
			x1 = x[0];
			x2 = x.length > 1 ? ',' + x[1] : '';
			var rgx = /(\d+)(\d{3})/;
			while (rgx.test(x1)) {
				x1 = x1.replace(rgx, '$1' + '.' + '$2');
			}
			$(this).val(x1 + x2);
		}
  	}); 

	$(".rutOnly").keypress(function (e) {
	    if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57) && e.which != 75 && e.which != 107) {
           return false;
	    }
  	});
	/*$(".datepickerTxt").keypress(function (e) {
       return false;
  	});*/
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

	var modalProgress = '<div id="modalProgress" class="modal fade" data-backdrop="static" style="z-index: 2000">' +
							'<div class="modal-dialog modal-sm">' +
								'<div class="modal-content">' +
						        	'<div id="modalProgress_body" class="modal-body">' +
										'<div style="text-align: center;">' +
											'<span id="modalProgress-text"><i class="fa fa-spinner fa-spin fa-2x"></i><br/>En proceso...</span>' +
										'</div>' +
							      	'</div>' +
							    '</div>' +
							'</div>' +
						'</div>';
	$('body').append(modalProgress);
}

function logout(){

	var path = window.location.pathname;
	path = path.split('/');
	
	var previous_path = "";

	if(path[path.length-2]=='include' || path[path.length-2]=='ext'){
		previous_path='../';

	}else if(path[path.length-2]=='main' || path[path.length-2]=='contracts' || path[path.length-2]=='conf' || path[path.length-2]=='trans' || path[path.length-2]=='files' || path[path.length-2]=='tally'){
		previous_path='../../';
	}
	
	$.post(previous_path+'phps/logout.php', {param: ""}, function(data, textStatus, xhr) {
		if(data==1){
			window.location=previous_path+"login.php";
		}
	});

}

/*
function returnSeparator(){

	$(document).find('.numbersOnly').each(function() {
		$(this).val($(this).val().replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d)\.?)/g, "."));
	});

}

function returnNoSeparator(){

	$(document).find('.numbersOnly').each(function() {
		$(this).val($(this).val().replace(/\./g, '').replace(/\,/g, '.'));
	});

}

function toSeparator(number){
	return number.toString().replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ".");

}
function toNoSeparator(number){
	return parseInt(number.replace(/\./g, '').replace(/\,/g, '.'));
}*/


function returnSeparator(){
	$(document).find('.numbersOnly').each(function() {
		if($(this).val()[0]=='-'){
			$(this).val($(this).val().substring(1));
			$(this).val('-'+$(this).val().replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d)\.?)/g, "."));
		}else{
			$(this).val($(this).val().replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d)\.?)/g, "."));
		}
	});

	$(document).find('.numbersOnlyFloat').each(function() {
		if($(this).val()[0]=='-'){
			$(this).val($(this).val().substring(1));
			$(this).val('-'+$(this).val().replace(/\D/g, ",").replace(/\B(?=(\d{3})+(?!\d)\.?)/g, "."));
		}else{
			$(this).val($(this).val().replace(/\D/g, ",").replace(/\B(?=(\d{3})+(?!\d)\.?)/g, "."));
		}
	});

	$(document).find('.numbersOnlyFloatMoney').each(function() {
		if($(this).val()[0]=='-'){
			$(this).val($(this).val().substring(1));
			$(this).val('-'+$(this).val().replace(/\D/g, ",").replace(/\B(?=(\d{3})+(?!\d)\.?)/g, "."));
		}else{
			$(this).val($(this).val().replace(/\D/g, ",").replace(/\B(?=(\d{3})+(?!\d)\.?)/g, "."));
		}
	});


	$(document).find('.numbersOnlyFloat2').each(function() {
		if($(this).val()[0]=='-'){
			$(this).val($(this).val().substring(1));
			$(this).val('-'+$(this).val().replace(/\D/g, ",").replace(/\B(?=(\d{3})+(?!\d)\.?)/g, "."));
		}else{
			$(this).val($(this).val().replace(/\D/g, ",").replace(/\B(?=(\d{3})+(?!\d)\.?)/g, "."));
		}
  	});
}

function returnNoSeparator(){

	$(document).find('.numbersOnly').each(function() {
		$(this).val($(this).val().replace(/\./g, '').replace(/\,/g, '.'));
	});

	$(document).find('.numbersOnlyFloat').each(function() {
		$(this).val($(this).val().replace(/\./g, '').replace(/\,/g, '.'));
	});

	$(document).find('.numbersOnlyFloatMoney').each(function() {
		$(this).val($(this).val().replace(/\./g, '').replace(/\,/g, '.'));
	});

	$(document).find('.numbersOnlyFloat2').each(function() {
		$(this).val($(this).val().replace(/\./g, '').replace(/\,/g, '.'));
	});

}

function toSeparator(number){
	return number.toString().replace(/\D/g, ",").replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ".");
}
function toNoSeparator(number){
	return parseInt(number.replace(/\./g, '').replace(/\,/g, '.'));
}

function toNoSeparatorFloat(number){
	return parseFloat(number.replace(/\./g, '').replace(/\,/g, '.'));
}