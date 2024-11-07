function loadMenu(){
	var path = window.location.pathname;
	path = path.split('/');

	if(path[2]=='include'){
		$.post('../phps/loadMenu.php', {where:"include", user: ""}, function(data, textStatus, xhr) {
			$("#menuPrincipal").append(data);
		});
	}else{
		$.post('phps/loadMenu.php', {where:"campos", user: ""}, function(data, textStatus, xhr) {
			$("#menuPrincipal").append(data);
		});
	}
}
loadMenu();

function dataTable(idContainer,table,tableIndex,quantityValue,dataArray,dataTitles){//Agregar opción para añadir celdas especiales (ej: ver, editar, eliminar)

	var content = '<div class="col-xs-9 col-sm-9 col-md-9 col-lg-9 form-inline"></div>'+
					'<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3 form-inline">'+
		  				'<label>Mostrar:</label>'+
						'<select id="tableQuantity" class="form-control">'+
					    	'<option value="10">10</option>'+
					    	'<option value="25">25</option>'+
					    	'<option value="50">50</option>'+
					    	'<option value="100">100</option>'+
		  				'</select>'+
		  				'<label>Registros</label>'+
	  				'</div>'+
					'<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">'+
						'<div class="table-responsive">'+
							'<table id="'+table+'" class="table table-hover">'+
								'<tr>';
	for(j=0;j<dataTitles.length;j++){
		content+='<th>'+dataTitles[j]+'</th>';
	}
	content+='</tr>'+
			'</table>'+
		'</div>'+
	'</div>'+
	'<div class="col-xs-9 col-sm-9 col-md-9 col-lg-9 form-inline">'+
		'<nav>'+
	  		'<ul class="pagination">'+
	    		'<li class="disabled"><a href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>'+
	    		'<li class="active"><a class="tableIndexPage" href="#">1<span class="sr-only">(current)</span></a></li>';
	    		if(dataArray.length>10) content+='<li><a class="tableIndexPage" href="#">2</a></li>';
	    		if(dataArray.length>20) content+='<li><a class="tableIndexPage" href="#">3</a></li>';
	    		if(dataArray.length>30) content+='<li><a class="tableIndexPage" href="#">4</a></li>';
	    		if(dataArray.length>40) content+='<li><a class="tableIndexPage" href="#">5</a></li>';
	    		if(dataArray.length>50) content+='<li><a href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
	    		else content+='<li class="disabled"><a href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
	  		'</ul>'+
		'</nav>'+
	'</div>'+
	'<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3 form-inline">'+
		'<label>Mostrando&nbsp;</label>'+
		'<label id="many">0&nbsp;</label>'+
		'<label>de&nbsp;</label>'+
		'<label>'+dataArray.length+'&nbsp;</label>'+
		'<label>Registros</label>'+
	'</div>';


	$("#"+idContainer).html('');
	$("#"+idContainer).append(content);

	$('#tableQuantity').val(quantityValue);//Se define cantidad de datos a mostrar
	$('#tableQuantity').change(function(){
		dataTable(idContainer,table,tableIndex,$('#tableQuantity').val(),dataArray,dataTitles);
	});
	$('.tableIndexPage').click(function(){//Selección de paginador
		this.parentElement.className='active';
	    dataTable(idContainer,table,this.text-1,$('#tableQuantity').val(),dataArray,dataTitles);
	});

	$('#'+table+' tr').each(function() {
	});
	
	var maxRows = quantityValue;
	if ((tableIndex+1)*quantityValue>dataArray.length) maxRows=dataArray.length-(tableIndex*quantityValue);//Calcula índice (página) en la que se encuentra. En caso de ser la última, debe mostrar la cantidad restante (ej: muestra 10, 23 registros, última página debe mostrar 3)
	
	for(i=0;i<maxRows;i++){
		list = "<tr id='id"+dataArray[i+(quantityValue*tableIndex)]['id']+"'>";
		for(x in dataArray[i+(quantityValue*tableIndex)]){
			list += "<td>"+dataArray[i+(quantityValue*tableIndex)][x]+"</td>";
		}

		list += '<td><button id="save" class="btn btn-warning" onclick="editRow(\'id'+idList+'\')"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button></td>';
		list += '<td><button id="edit" class="btn btn-danger" onclick="deleteRow(\'id'+idList+'\')"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button></td><tr/>';
		$("#"+table).append(list);
		idList++;
	}
	//console.log($("#"+table).html());
	/*
	1- Contar cantidad de filas
	2- Determinar cantidad de filas a mostrar
	3- Almacenar filas en un arreglo, con posiciones indicadas en enteros, ejemplo array(fila,columna)
	4- Crear paginadores en base a registros/filas a mostrar (50 registros divididos en páginas de 10, total de 5 paginadores)
	5- Redibujar tabla (en caso sea tabla fija; idealmente realizar función directamente sobre la consulta POST)
	*/
}