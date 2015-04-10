var hoy = new Date(); 
var dd = hoy.getDate(); 
var mm = hoy.getMonth()+1;//enero es 0! 
if (mm < 10) { mm = '0' + mm; }
if (dd < 10) { dd = '0' + dd; }

var yyyy = hoy.getFullYear();
//armamos fecha para los datepicker
var FechaAct = dd + '/' + mm + '/' + yyyy;

app.controller('loginCtrl', function($scope, $rootScope, auth, $location) {

    $scope.inicio = function(){
        
        $scope.user = '';
        $scope.psw = '';
        $rootScope.mensaje = '';
        $rootScope.cargar = false;
        $rootScope.cerrar = true;
    }

    $scope.login = function(){

        $rootScope.mensaje = '';
        auth.login($scope.user,$scope.psw);
    }
    
});

app.controller('anualCtrl', function($scope,busqueda , DTOptionsBuilder, DTColumnBuilder , $rootScope, $location){

    var ruta = 'api/api.php?funcion=ExpedientesXfecha&unidad=' + $rootScope.unidad;

    $scope.inicio = function(){

        $scope.detalle = false;
        $scope.grafica = false;
        $scope.tabla = false;
        $scope.cargando = false;
        $scope.unidadN = 'Sin Unidad Seleccionada';

        $scope.datos = {
            mes:mm,
            ano:yyyy
        }

        $scope.buscaMeses();

    }

    $scope.buscaMeses = function(){

        busqueda.estadisticaatenciones(yyyy).success(function (data){
            $scope.meses = data;
        });

        busqueda.estadisticaatenciones(yyyy).success(function (data){
            // $scope.datos2 = data;
            // console.log(data);
            var chart = AmCharts.makeChart("chartdiv-6",{
                "type": "serial",
                "startDuration": 1,
                "categoryField": "MES",
                "graphs": [
                    {
                        "balloonText": "[[category]]<br><b>Atenciones: [[value]]</b>",
                        "type": "column",
                        "fillAlphas": 0.9,
                        "lineAlpha": 0.2,
                        "valueField": "Cantidad"
                    }
                ],
                "dataProvider": data
            });



        });


    }

    $scope.muestraDetalle = function(info) {

        $location.path('/detalle/expediente/'+ info.Exp_folio);

    };

    $scope.buscaExpedientes = function(mes,nombre,ano){


        $scope.detalle = true;
        $scope.nombremes = nombre;
        if (mes < 10) { mes = '0' + mes; }
        
        busqueda.estadisticaatencionesxsemana(mes,ano).success(function (data){

            var chart = AmCharts.makeChart("chartdiv-7", {
                "type": "serial",
                "startDuration": 1,
                "categoryField": "Semana",
                "graphs": [
                    {
                        "balloonText": "[[category]]<br><b>Atenciones: [[value]]</b>",
                        "type": "column",
                        "fillAlphas": 0.9,
                        "lineAlpha": 0.2,
                        "valueField": "Cantidad"
                    }
                ],
                "dataProvider": data
            });

        });

        $scope.datos.mes = mes; 
        $scope.datos.ano = ano;   
        $scope.Buscar();
            


    }

    $scope.$on('event:dataTableLoaded', function(event, data) { 
        // console.log(event); 
        // console.log(data);

        $scope.tableId = data.id;

        $scope.Buscar = function() {
            $scope.searchData = angular.copy($scope.datos);
            $scope.cargando = true;

            $('#'+$scope.tableId).DataTable().ajax.reload(function (data) {
                $scope.$apply(function() {
                    $scope.cargando = false;
                }); 
            });
        };

    });

    $scope.dtOptions = DTOptionsBuilder.newOptions()
    .withOption('ajax', {
        "url": ruta,
        "type": 'POST',
        "data": function ( d ) {
                console.log("data");
                d.search = $scope.datos || {}; //search criteria
                return JSON.stringify(d);
        }
            
    })
    .withOption('lengthMenu', [ [ 25, 50, 100, -1], [ 25, 50, 100, "Todo"] ])
    // .withOption('serverSide', true)
    .withPaginationType('full_numbers')
    .withOption('language', {
        paginate: {
            first: "«",
            last: "»",
            next: "→",
            previous: "←"
        },
        search: "Buscar:",
        loadingRecords: "Cargando Información....",
        lengthMenu: "    Mostrar _MENU_ entradas",
        processing: "Procesando Información",
        infoEmpty: "No se encontro información",
        emptyTable: "Sin Información disponible",
        info: "Mostrando pagina _PAGE_ de _PAGES_ , Registros encontrados _TOTAL_ ",
        infoFiltered: " - encontrados _MAX_ coincidencias"
    })

    .withOption('rowCallback', function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
        $('td', nRow).bind('click', function() {
            $scope.$apply(function() {
                $scope.muestraDetalle(aData);
            });
        });
        return nRow;
    })


    // Add Bootstrap compatibility
    .withBootstrap()
    // Add ColVis compatibility
    .withColVis()
    // Add a state change function
    .withColVisStateChange(function(iColumn, bVisible) {
        console.log('The column' + iColumn + ' has changed its status to ' + bVisible)
    })

    .withOption("colVis",{
        buttonText: "Mostrar / Ocultar Columnas"
    })
    // Exclude the last column from the list
    // .withColVisOption('aiExclude', [2])

    // Add ColReorder compatibility
    .withColReorder()
    // Set order
    // .withColReorderOrder([1, 0, 2])
    // Fix last right column
    .withColReorderOption('iFixedColumnsRight', 1)
    .withColReorderCallback(function() {
        console.log('Columns order has been changed with: ' + this.fnOrder());
    })
    //Add Table tools compatibility

    .withTableTools('js/swf/copy_csv_xls_pdf.swf')
    .withTableToolsButtons([

        {
            "sExtends":     "copy",
             "sButtonText": "Copiar"
        },
        {
            'sExtends': 'collection',
            'sButtonText': 'Exportar',
            'aButtons': ['xls', 'pdf']
        }
    ]);
        
    $scope.dtColumns = [

        DTColumnBuilder.newColumn('Exp_folio').withTitle('Folio'),
        DTColumnBuilder.newColumn('Cia_nombrecorto').withTitle('Cliente'),
        DTColumnBuilder.newColumn('Exp_poliza').withTitle('Poliza'),
        DTColumnBuilder.newColumn('Exp_siniestro').withTitle('Siniestro'),
        DTColumnBuilder.newColumn('EXP_reporte').withTitle('Reporte'),
        DTColumnBuilder.newColumn('Exp_completo').withTitle('Lesionado'),
        DTColumnBuilder.newColumn('Exp_fecreg').withTitle('Fecha Atención'),
        DTColumnBuilder.newColumn('ClasL_tipo').withTitle('Clasificacion'),
        DTColumnBuilder.newColumn('EXP_estatus').withTitle('Estatus Documental'),
        DTColumnBuilder.newColumn('EXP_estatusFac').withTitle('Estatus Facturación')
    ];
    
});

app.controller('busquedasCtrl', function($scope, $rootScope, $location, DTOptionsBuilder, DTColumnBuilder , $http, busqueda) {
    
    var ruta = 'api/api.php?funcion=buscaExpedientes&unidad=' + $rootScope.unidad;
    $scope.leyendabusqueda = 'Ultimas 100 atenciones';

    $scope.inicio = function(){

		$rootScope.cargar = false;
		$scope.detalle = true;
        $scope.cargando = false;
        $('#win').tooltip();
        
        $scope.busqueda = false;
        
        $scope.datos = {
            fechaini:'',
            fechafin:'',
            folio:'',
            lesionado:'',
            siniestro:'',
            poliza:'',
            reporte:'',
            dia:false,
            mes:false,
            ano:false
        }

	}

    $scope.muestramensaje = function(){

        $scope.leyendabusqueda = '';

        if ($scope.datos.dia) {
            $scope.leyendabusqueda += ' Lesionados del Dia';
        };

        if ($scope.datos.mes) {
            $scope.leyendabusqueda += ' Lesionados del Mes';
        };

        if ($scope.datos.ano) {
            $scope.leyendabusqueda += ' Lesionados del Año';
        };


        if ($scope.datos.lesionado != '') {
            $scope.leyendabusqueda += ' Lesionado: '+ $scope.datos.lesionado;
        };

        if ($scope.datos.folio != '') {
            $scope.leyendabusqueda += ' Folio: '+ $scope.datos.folio;
        };

        if ($scope.datos.siniestro != '') {
            $scope.leyendabusqueda += ' Siniestro: '+ $scope.datos.siniestro;
        };

        if ($scope.datos.poliza != '') {
            $scope.leyendabusqueda += ' Poliza: '+ $scope.datos.poliza;
        };

        if ($scope.datos.fechaini != '') {
            $scope.leyendabusqueda += ' del: '+ $scope.datos.fechaini + ' al '+ $scope.datos.fechafin;
        };

    }



    $scope.verificaFolio = function(folio){

        $scope.datos.folio = busqueda.rellenaFolio(folio);
    }

	$scope.muestra = function(valor){

		$scope.fechas = (valor == 'fechas')? true:false;
		$scope.lesionado = (valor == 'lesionado')? true:false;
		$scope.avanzado = (valor == 'avanzado')? true:false;
        $scope.inicio();

	}

    $scope.muestradia = function(valor){

        $scope.inicio();
        $scope.datos.dia = true;
        $scope.Buscar();

    }

    $scope.muestrames = function(valor){

        $scope.inicio();
        $scope.datos.mes = true;
        $scope.Buscar();

    }

    $scope.muestraano = function(valor){

        $scope.inicio();
        $scope.datos.ano = true;
        $scope.Buscar();

    }

    $scope.muestraDetalle = function(info) {

        console.log(info);
        $location.path('/detalle/expediente/'+ info.Exp_folio);

    };

    $scope.$on('event:dataTableLoaded', function(event, data) { 
        // console.log(event); 
        // console.log(data);

        $scope.tableId = data.id;

        $scope.Buscar = function() {

            $scope.muestramensaje();
            $scope.searchData = angular.copy($scope.datos);
            $scope.cargando = true;

            $('#'+$scope.tableId).DataTable().ajax.reload(function (data) {
                $scope.$apply(function() {
                    $scope.cargando = false;
                    $scope.inicio();
                }); 
            });
        };


    });

    $scope.dtOptions = DTOptionsBuilder.newOptions()
        .withOption('ajax', {
            "url": ruta,
            "type": 'POST',
            "data": function ( d ) {
                    console.log("data");
                    d.search = $scope.datos || {}; //search criteria
                    return JSON.stringify(d);
            }
                
        })
        .withOption('lengthMenu', [ [25, 50, 100, -1], [25, 50, 100, "Todo"] ])
        .withOption('responsive', true)
        // .withOption('serverSide', true)
        .withPaginationType('full_numbers')
        .withOption('language', {
            paginate: {
                first: "«",
                last: "»",
                next: "→",
                previous: "←"
            },
            search: "Buscar:",
            loadingRecords: "Cargando Información....",
            lengthMenu: "    Mostrar _MENU_ entradas",
            processing: "Procesando Información",
            infoEmpty: "No se encontro información",
            emptyTable: "Sin Información disponible",
            info: "Mostrando pagina _PAGE_ de _PAGES_ , Registros encontrados _TOTAL_ ",
            infoFiltered: " - encontrados _MAX_ coincidencias"
        })

        .withOption('rowCallback', function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            $('td', nRow).bind('click', function() {
                $scope.$apply(function() {
                    $scope.muestraDetalle(aData);
                });
            });
            return nRow;
        })


        // Add Bootstrap compatibility
        .withBootstrap()
        // Add ColVis compatibility
        .withColVis()
        // Add a state change function
        .withColVisStateChange(function(iColumn, bVisible) {
            console.log('The column' + iColumn + ' has changed its status to ' + bVisible)
        })

        .withOption("colVis",{
            buttonText: "Mostrar / Ocultar Columnas"
        })
        // Exclude the last column from the list
        // .withColVisOption('aiExclude', [2])

        // Add ColReorder compatibility
        .withColReorder()
        // Set order
        // .withColReorderOrder([1, 0, 2])
        // Fix last right column
        .withColReorderOption('iFixedColumnsRight', 1)
        .withColReorderCallback(function() {
            console.log('Columns order has been changed with: ' + this.fnOrder());
        })
        //Add Table tools compatibility

        .withTableTools('js/swf/copy_csv_xls_pdf.swf')
        .withTableToolsButtons([

            {
                "sExtends":     "copy",
                 "sButtonText": "Copiar"
            },
            {
                'sExtends': 'collection',
                'sButtonText': 'Exportar',
                'aButtons': ['xls', 'pdf']
            }
        ]);
        
    $scope.dtColumns = [

        DTColumnBuilder.newColumn('Exp_folio').withTitle('Folio'),
        DTColumnBuilder.newColumn('Cia_nombrecorto').withTitle('Cliente'),
        DTColumnBuilder.newColumn('Exp_poliza').withTitle('Poliza'),
        DTColumnBuilder.newColumn('Exp_siniestro').withTitle('Siniestro'),
        DTColumnBuilder.newColumn('EXP_reporte').withTitle('Reporte'),
        DTColumnBuilder.newColumn('Exp_completo').withTitle('Lesionado'),
        DTColumnBuilder.newColumn('Exp_fecreg').withTitle('Fecha Atención'),
        DTColumnBuilder.newColumn('ClasL_tipo').withTitle('Clasificacion'),
        DTColumnBuilder.newColumn('EXP_estatus').withTitle('Estatus Documental'),
        DTColumnBuilder.newColumn('EXP_estatusFac').withTitle('Estatus Facturación')
    ];

});

app.controller('categoriaCtrl', function($scope,busqueda , DTOptionsBuilder, DTColumnBuilder , $rootScope, $location, $routeParams){

    var ruta = 'api/api.php?funcion=buscaExpedientesCategoria&unidad=' + $rootScope.unidad;

    $scope.inicio = function(){

        $scope.titulo = 'Detalle de Lesionados sin documentación en el periodo ' + $routeParams.categoria + ' dias';
        $scope.grafica = false;
        $scope.tabla = false;
        $scope.cargando = false;
        $scope.unidadN = 'Sin Unidad Seleccionada';

        $scope.datos = {
            categoria:$routeParams.categoria
        }

        //$scope.buscaExpedientes();

    }

    $scope.muestraDetalle = function(info) {

        console.log(info);
        $location.path('/detalle/expediente/'+ info.Exp_folio);
        
    };

    $scope.$on('event:dataTableLoaded', function(event, data) { 
        // console.log(event); 
        // console.log(data);

        $scope.tableId = data.id;

        $scope.Buscar = function() {
            $scope.searchData = angular.copy($scope.datos);
            $scope.cargando = true;

            $('#'+$scope.tableId).DataTable().ajax.reload(function (data) {
                $scope.$apply(function() {
                    $scope.cargando = false;
                }); 
            });
        };

    });

    $scope.dtOptions = DTOptionsBuilder.newOptions()
    .withOption('ajax', {
        "url": ruta,
        "type": 'POST',
        "data": function ( d ) {
                console.log("data");
                d.search = $scope.datos || {}; //search criteria
                return JSON.stringify(d);
        }
            
    })
    .withOption('lengthMenu', [ [ 25, 50, 100, -1], [ 25, 50, 100, "Todo"] ])
    // .withOption('serverSide', true)
    .withPaginationType('full_numbers')
    .withOption('language', {
        paginate: {
            first: "«",
            last: "»",
            next: "→",
            previous: "←"
        },
        search: "Buscar:",
        loadingRecords: "Cargando Información....",
        lengthMenu: "    Mostrar _MENU_ entradas",
        processing: "Procesando Información",
        infoEmpty: "No se encontro información",
        emptyTable: "Sin Información disponible",
        info: "Mostrando pagina _PAGE_ de _PAGES_ , Registros encontrados _TOTAL_ ",
        infoFiltered: " - encontrados _MAX_ coincidencias"
    })

    .withOption('rowCallback', function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
        $('td', nRow).bind('click', function() {
            $scope.$apply(function() {
                $scope.muestraDetalle(aData);
            });
        });
        return nRow;
    })


    // Add Bootstrap compatibility
    .withBootstrap()
    // Add ColVis compatibility
    .withColVis()
    // Add a state change function
    .withColVisStateChange(function(iColumn, bVisible) {
        console.log('The column' + iColumn + ' has changed its status to ' + bVisible)
    })

    .withOption("colVis",{
        buttonText: "Mostrar / Ocultar Columnas"
    })
    // Exclude the last column from the list
    // .withColVisOption('aiExclude', [2])

    // Add ColReorder compatibility
    .withColReorder()
    // Set order
    // .withColReorderOrder([1, 0, 2])
    // Fix last right column
    .withColReorderOption('iFixedColumnsRight', 1)
    .withColReorderCallback(function() {
        console.log('Columns order has been changed with: ' + this.fnOrder());
    })
    //Add Table tools compatibility

    .withTableTools('js/swf/copy_csv_xls_pdf.swf')
    .withTableToolsButtons([

        {
            "sExtends":     "copy",
             "sButtonText": "Copiar"
        },
        {
            'sExtends': 'collection',
            'sButtonText': 'Exportar',
            'aButtons': ['xls', 'pdf']
        }
    ]);
        
    $scope.dtColumns = [

        DTColumnBuilder.newColumn('Exp_folio').withTitle('Folio'),
        DTColumnBuilder.newColumn('Cia_nombrecorto').withTitle('Cliente'),
        DTColumnBuilder.newColumn('Exp_poliza').withTitle('Poliza'),
        DTColumnBuilder.newColumn('Exp_siniestro').withTitle('Siniestro'),
        DTColumnBuilder.newColumn('EXP_reporte').withTitle('Reporte'),
        DTColumnBuilder.newColumn('Exp_completo').withTitle('Lesionado'),
        DTColumnBuilder.newColumn('Exp_fecreg').withTitle('Fecha Atención'),
        DTColumnBuilder.newColumn('ClasL_tipo').withTitle('Clasificacion'),
        DTColumnBuilder.newColumn('EXP_estatus').withTitle('Estatus Documental'),
        DTColumnBuilder.newColumn('EXP_estatusFac').withTitle('Estatus Facturación')
    ];
    
});

app.controller('clientesCtrl', function($scope, $rootScope, $cookies, $location) {

    $scope.inicio = function(){

        if (typeof($cookies.username) == 'undefined') {
            $rootScope.cerrar = true;
        };
        
    }

    $scope.asignaCliente = function(nombre,cliente,ruta){

        if (typeof($cookies.username) == 'undefined') {
            $rootScope.username =  $cookies.usernametemp;
            $cookies.username = $rootScope.username;    
        };
        $cookies.cliente = cliente;
        $cookies.nombreC = nombre;
        $rootScope.cliente = cliente;
        $rootScope.nombreC = nombre;
        $cookies.ruta = ruta;
        $rootScope.ruta = ruta;

        $location.path('/home');
    }
    
});

app.controller('expedienteCtrl', function($scope, $rootScope,  busqueda, $routeParams, $location, JSTagsCollection, envios) {
    

    $scope.inicio = function(){

        //$scope.folio = $routeParams.folio;
        $scope.estatus = 'Información';
        $scope.cargando = true;
        $scope.pdf = false;
        $scope.xml = false;
        $scope.ano = 2014;
        $scope.mes = 1;
        $scope.mensaje = '';
        
        $scope.consultaFolio();
        $scope.mail = {
            asunto:'',
            tema:'',
            comentarios:'',
            respuesta:$rootScope.correo,
            copias: $scope.copias.tags,
            cliente: $rootScope.nombreC,
            folio: $routeParams.folio,
            lesionado: ''
        }

    }

    // $scope.imagenOneDrive = function(){

        
    // }

    function onLogin (session) {
        if (!session.error) {
            WL.api({
                path: "me",
                method: "GET"
            }).then(
                function (response) {
                    document.getElementById("info").innerText =
                        "Hello, " + response.first_name + " " + response.last_name + "!";
                },
                function (responseFailed) {
                    document.getElementById("info").innerText =
                        "Error calling API: " + responseFailed.error.message;
                }
            );
        }
        else {
            document.getElementById("info").innerText =
                "Error signing in: " + session.error_description;
        }
    }

    $scope.consultaFolio = function(){

        busqueda.detalleExpediente($routeParams.folio).success(function (data){

            console.log(data);
            $scope.datos = data.detalle;
            $scope.autorizaciones = data.autorizacion;
            $scope.movimientos = data.movimientos;
            $scope.hospitalarios = data.hospitalario;
            $scope.tickets = data.tickets;
            $scope.documentos = data.documentos;

            $scope.mail.lesionado = $scope.datos.lesionado;

            $scope.cargando = false;
        })
    }

    $scope.muestraDocumento = function (archivo,tipo){

        console.log(archivo);
        $scope.documento = archivo;
        $scope.nombredoc = tipo;

        if (typeof(archivo) == "undefined"){$scope.existe = false;}else{$scope.existe = true;}
        
        $('#DocumentoModal').modal('show');
    }

    $scope.muestraInforme = function (){

        console.log($scope.documentos.informe1);
        $scope.documento = $scope.documentos.informe1;
        $scope.nombredoc = 'Nota Médica';

        if (typeof($scope.documento) == "undefined"){$scope.existe = false;}else{$scope.existe = true;}
        
        $('#DocumentoModal').modal('show');
    }

    $scope.copias = new JSTagsCollection([]);

    $scope.jsTagOptions = {
        "texts": {
          "inputPlaceHolder": "Presiona enter"
        },
        "tags": $scope.copias
    };

    $scope.enviar = function(){

        $('#boton').button('loading');
        $scope.mensaje = '';
        envios.correo($scope.mail).success(function (data){
            console.log(data);
            $scope.mensaje = data.respuesta;
            $('#boton').button('reset');
        });

    }

});

app.controller('facturasCtrl', function($scope, $rootScope , $http, busqueda, $routeParams, $location) {
    
    $scope.inicio = function(){
        
    }

});

app.controller('fechaCtrl', function($scope, $rootScope, DTOptionsBuilder, DTColumnBuilder , $http, busqueda, $routeParams, $location) {
    
    var ruta = 'api/api.php?funcion=buscaExpedientes&unidad=' + $rootScope.unidad;


    $scope.inicio = function(){

        $rootScope.cargar = false;
        $scope.detalle = true;

        $scope.datos = {
            fechaini:'',
            fechafin:'',
            folio:'',
            lesionado:'',
            siniestro:'',
            poliza:'',
            reporte:'',
            dia:false,
            mes:false,
            ano:false
        }

        if ($routeParams.valor == 'ano') {
            $scope.muestraano();
        }else if ($routeParams.valor == 'mes') {
            $scope.muestrames();
        }else if ($routeParams.valor == 'dia'){
            $scope.muestradia();
        }
        
    }


    $scope.muestradia = function(valor){

        $scope.datos.dia = true;
        $scope.Buscar();

    }

    $scope.muestrames = function(valor){

        $scope.datos.mes = true;
        $scope.Buscar();

    }

    $scope.muestraano = function(valor){

        $scope.datos.ano = true;
        $scope.Buscar();

    }

    $scope.muestraDetalle = function(info) {

        console.log(info);
        $location.path('/detalle/expediente/'+ info.Exp_folio);
        
    };

    $scope.$on('event:dataTableLoaded', function(event, data) { 
        console.log('event:dataTableLoaded:'+data); 
        $scope.tableId = data.id;

        $scope.Buscar = function() {
            $scope.searchData = angular.copy($scope.datos);
            console.log("search");
            $('#'+$scope.tableId).DataTable().ajax.reload();
        };
    });

    $scope.dtOptions = DTOptionsBuilder.newOptions()
        .withOption('ajax', {
            "url": ruta,
            "type": 'POST',
            "data": function ( d ) {
                    console.log("data");
                    d.search = $scope.datos || {}; //search criteria
                    return JSON.stringify(d);
            }
        })
        .withOption('lengthMenu', [ [ 25, 50, 100, -1], [ 25, 50, 100, "Todo"] ])
        // .withOption('serverSide', true)
        .withPaginationType('full_numbers')
        .withOption('language', {
            paginate: {
                first: "«",
                last: "»",
                next: "→",
                previous: "←"
            },
            search: "Buscar:",
            loadingRecords: "Cargando Información....",
            lengthMenu: "    Mostrar _MENU_ entradas",
            processing: "Procesando Información",
            infoEmpty: "No se encontro información",
            emptyTable: "Sin Información disponible",
            info: "Mostrando pagina _PAGE_ de _PAGES_ , Registros encontrados _TOTAL_ ",
            infoFiltered: " - encontrados _MAX_ coincidencias"
        })
        // Add Bootstrap compatibility
        .withBootstrap()
        // Add ColVis compatibility
        .withColVis()
        // Add a state change function
        .withColVisStateChange(function(iColumn, bVisible) {
            console.log('The column' + iColumn + ' has changed its status to ' + bVisible)
        })

        .withOption('rowCallback', function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            $('td', nRow).bind('click', function() {
                $scope.$apply(function() {
                    $scope.muestraDetalle(aData);
                });
            });
            return nRow;
        })

        .withOption("colVis",{
            buttonText: "Mostrar / Ocultar Columnas"
        })
        // Exclude the last column from the list
        // .withColVisOption('aiExclude', [2])

        // Add ColReorder compatibility
        .withColReorder()
        // Set order
        // .withColReorderOrder([1, 0, 2])
        // Fix last right column
        .withColReorderOption('iFixedColumnsRight', 1)
        .withColReorderCallback(function() {
            console.log('Columns order has been changed with: ' + this.fnOrder());
        })
        //Add Table tools compatibility

        .withTableTools('js/swf/copy_csv_xls_pdf.swf')
        .withTableToolsButtons([

            {
                "sExtends":     "copy",
                 "sButtonText": "Copiar"
            },
            {
                'sExtends': 'collection',
                'sButtonText': 'Exportar',
                'aButtons': ['xls', 'pdf']
            }
        ]);
        
    $scope.dtColumns = [

        DTColumnBuilder.newColumn('Exp_folio').withTitle('Folio'),
        DTColumnBuilder.newColumn('UNI_nombreMV').withTitle('Unidad'),
        DTColumnBuilder.newColumn('Exp_poliza').withTitle('Poliza'),
        DTColumnBuilder.newColumn('Exp_siniestro').withTitle('Siniestro'),
        DTColumnBuilder.newColumn('EXP_reporte').withTitle('Reporte'),
        DTColumnBuilder.newColumn('Exp_completo').withTitle('Lesionado'),
        DTColumnBuilder.newColumn('Exp_fecreg').withTitle('Fecha Atención'),
        DTColumnBuilder.newColumn('RIE_nombre').withTitle('Riesgo'),
        DTColumnBuilder.newColumn('EXP_estatus').withTitle('Estatus')
    ];
    
});

app.controller('historicoCtrl', function($scope, $rootScope, busqueda, $filter, $location) {
    
    $scope.inicio = function(){

        $rootScope.cerrar = false;
        $rootScope.cargar = false;
        $scope.inicial = 0;
        $scope.muestraGraficas(yyyy);
        $scope.cargaInfo();

    }


    $scope.cargaInfo = function(){

        busqueda.expedientexsdia().success(function (data){
            $scope.dia = data.Folios;
            // console.log(data);
        });

        busqueda.expedientexsmes().success(function (data){
            $scope.mes = data.Folios;
            // console.log(data);
        });

        busqueda.expedientexsano().success(function (data){
            $scope.ano = data.Folios;
            // console.log(data);
        });
        
    }

    $scope.muestraGraficas = function(ano){ 

        $scope.anoactual = ano;

        busqueda.estadisticaatenciones(ano).success(function (data){
            // $scope.datos2 = data;
            // console.log(data);
            var chart = AmCharts.makeChart("chartdiv-1",{
                "type": "serial",
                "startDuration": 1,
                "categoryField": "MES",
                "graphs": [
                    {
                        "balloonText": "[[category]]<br><b>Atenciones: [[value]]</b>",
                        "type": "column",
                        "fillAlphas": 0.9,
                        "lineAlpha": 0.2,
                        "valueField": "Cantidad"
                    }
                ],
                "dataProvider": data
            });

        });

        busqueda.estadisticaatencionesxsemana(mm,ano).success(function (data){

            var chart = AmCharts.makeChart("chartdiv-2", {
                "type": "serial",
                "startDuration": 1,
                "categoryField": "Semana",
                "graphs": [
                    {
                        "balloonText": "[[category]]<br><b>Atenciones: [[value]]</b>",
                        "type": "column",
                        "fillAlphas": 0.9,
                        "lineAlpha": 0.2,
                        "valueField": "Cantidad"
                    }
                ],
                "dataProvider": data
            });

        });
        
    }
    
});

app.controller('homeCtrl', function($scope, $rootScope, busqueda, $filter, $location, webStorage) {
    
    $scope.inicio = function(){

        $rootScope.cerrar = false;
        $scope.cargando = false;
        $scope.inicial = 0;
        $scope.inicio = '';
        $scope.fin = '';
        $scope.cargaperiodos();

    }

    $scope.cargaperiodos = function(){

        $scope.cargando = true;

        busqueda.periodos().success(function (data){

            $scope.periodos = data;

            $scope.inicio = $scope.periodos[1];
            $scope.fin = $scope.periodos[1];

            if (webStorage.local.has('datos')) {
                $scope.muestraInfoLocal();
            }else{
                $scope.buscaInfo();
            }
        });
    }

    $scope.verificafechas = function(){
        console.log($scope.inicio);
        console.log($scope.fin);
        if ( ($scope.fin.ano < $scope.inicio.ano) || (  ($scope.fin.mes < $scope.inicio.mes) && ($scope.fin.ano >= $scope.inicio.ano) ) ) {
            alert('La fecha no debe ser menor a la inicial');
            $scope.fin = '';
        }
    }

    $scope.ir = function(categoria){

        var mes1 = $scope.inicio.mes;
        var ano1 = $scope.inicio.ano;
        var mes2 = $scope.fin.mes;
        var ano2 = $scope.fin.ano;

        $location.path('/listado/'+categoria+'/'+mes1+'/'+ano1+'/'+mes2+'/'+ano2);

    }

    $scope.buscaInfo = function(){

        $('#periodos').modal('hide');

        $scope.cargando = true;

        $scope.datos = {
            inicio:$scope.inicio,
            fin:$scope.fin
        }    
        busqueda.estadocuenta($scope.datos).success(function (data){
            console.log(data);

            webStorage.local.add('inicio',JSON.stringify($scope.inicio));
            webStorage.local.add('fin',JSON.stringify($scope.fin));
            webStorage.local.add('datos',JSON.stringify(data));

            $scope.total = data.total.folios;
            $scope.cancelados = data.cancelados.folios;
            $scope.buenos = data.buenos.folios;
            $scope.sindocumentos = data.sindocumentos.folios;
            $scope.problema = data.problema.folios;
            $scope.pagados = data.pagados.folios;
            $scope.procesos = data.proceso.folios;
            $scope.listos = 0;
            $scope.etapa2 = data.etapa2.folios;
            $scope.etapa3 = data.etapa3.folios;

            $scope.pagoEt1 = data.pagosEt1.total;
            $scope.pagoEt2 = data.pagosEt2.total;
            $scope.pagoEt3 = data.pagosEt3.total;

            $scope.totalPagos = Number(data.pagosEt1.total) + Number(data.pagosEt2.total) + Number(data.pagosEt3.total);

            $scope.fecha = data.fechaPago.fecha;

            $scope.cargando = false;
        });
    }

    $scope.cargaInicio = function(){

        $scope.inicio = $scope.periodos[1];
        $scope.fin = $scope.periodos[1];

        $scope.buscaInfo();
        
    }

    $scope.muestraInfoLocal = function(){

        // JSON.parse();
        var data = JSON.parse(webStorage.local.get('datos'));
        $scope.inicio = JSON.parse(webStorage.local.get('inicio'));
        $scope.fin = JSON.parse(webStorage.local.get('fin'));

        $scope.total = data.total.folios;
        $scope.cancelados = data.cancelados.folios;
        $scope.buenos = data.buenos.folios;
        $scope.sindocumentos = data.sindocumentos.folios;
        $scope.problema = data.problema.folios;
        $scope.pagados = data.pagados.folios;
        $scope.procesos = data.proceso.folios;
        $scope.listos = 0;
        $scope.etapa2 = data.etapa2.folios;
        $scope.etapa3 = data.etapa3.folios;

        $scope.pagoEt1 = data.pagosEt1.total;
        $scope.pagoEt2 = data.pagosEt2.total;
        $scope.pagoEt3 = data.pagosEt3.total;

        $scope.fecha = data.fechaPago.fecha;

        $scope.totalPagos = Number(data.pagosEt1.total) + Number(data.pagosEt2.total) + Number(data.pagosEt3.total);
            
        $scope.cargando = false;
    }
    
});

app.controller('listadoCtrl', function($scope,busqueda , DTOptionsBuilder, DTColumnBuilder , $rootScope, $location, $routeParams){

    var ruta = 'api/api.php?funcion=listadosestadocuenta&unidad=' + $rootScope.unidad;

    $scope.inicio = function(){

        $scope.grafica = false;
        $scope.tabla = false;
        $scope.cargando = false;
        $scope.unidadN = 'Sin Unidad Seleccionada';
        $scope.datos = {
            mes1:$routeParams.mes1,
            ano1:$routeParams.ano1,
            mes2:$routeParams.mes2,
            ano2:$routeParams.ano2,
            categoria:$routeParams.categoria
        }

    }

    $scope.muestraDetalle = function(info) {

        $location.path('/detalle/expediente/'+ info.Exp_folio);
        
    };

    $scope.$on('event:dataTableLoaded', function(event, data) { 
        // console.log(event); 
        // console.log(data);

        $scope.tableId = data.id;

        $scope.Buscar = function() {
            $scope.searchData = angular.copy($scope.datos);
            $scope.cargando = true;

            $('#'+$scope.tableId).DataTable().ajax.reload(function (data) {
                $scope.$apply(function() {
                    $scope.cargando = false;
                }); 
            });
        };

    });

    $scope.dtOptions = DTOptionsBuilder.newOptions()
    .withOption('ajax', {
        "url": ruta,
        "type": 'POST',
        "data": function ( d ) {
                console.log("data");
                d.search = $scope.datos || {}; //search criteria
                return JSON.stringify(d);
        }
            
    })
    .withOption('lengthMenu', [ [ 25, 50, 100, -1], [ 25, 50, 100, "Todo"] ])
    // .withOption('serverSide', true)
    .withPaginationType('full_numbers')
    .withOption('language', {
        paginate: {
            first: "«",
            last: "»",
            next: "→",
            previous: "←"
        },
        search: "Buscar:",
        loadingRecords: "Cargando Información....",
        lengthMenu: "    Mostrar _MENU_ entradas",
        processing: "Procesando Información",
        infoEmpty: "No se encontro información",
        emptyTable: "Sin Información disponible",
        info: "Mostrando pagina _PAGE_ de _PAGES_ , Registros encontrados _TOTAL_ ",
        infoFiltered: " - encontrados _MAX_ coincidencias"
    })

    .withOption('rowCallback', function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
        $('td', nRow).bind('click', function() {
            $scope.$apply(function() {
                $scope.muestraDetalle(aData);
            });
        });
        return nRow;
    })


    // Add Bootstrap compatibility
    .withBootstrap()
    // Add ColVis compatibility
    .withColVis()
    // Add a state change function
    .withColVisStateChange(function(iColumn, bVisible) {
        console.log('The column' + iColumn + ' has changed its status to ' + bVisible)
    })

    .withOption("colVis",{
        buttonText: "Mostrar / Ocultar Columnas"
    })
    // Exclude the last column from the list
    // .withColVisOption('aiExclude', [2])

    // Add ColReorder compatibility
    .withColReorder()
    // Set order
    // .withColReorderOrder([1, 0, 2])
    // Fix last right column
    .withColReorderOption('iFixedColumnsRight', 1)
    .withColReorderCallback(function() {
        console.log('Columns order has been changed with: ' + this.fnOrder());
    })
    //Add Table tools compatibility

    .withTableTools('js/swf/copy_csv_xls_pdf.swf')
    .withTableToolsButtons([

        {
            "sExtends":     "copy",
             "sButtonText": "Copiar"
        },
        {
            'sExtends': 'collection',
            'sButtonText': 'Exportar',
            'aButtons': ['xls', 'pdf']
        }
    ]);
        
    $scope.dtColumns = [

        DTColumnBuilder.newColumn('Exp_folio').withTitle('Folio'),
        DTColumnBuilder.newColumn('Cia_nombrecorto').withTitle('Cliente'),
        DTColumnBuilder.newColumn('Exp_poliza').withTitle('Poliza'),
        DTColumnBuilder.newColumn('Exp_siniestro').withTitle('Siniestro'),
        DTColumnBuilder.newColumn('EXP_reporte').withTitle('Reporte'),
        DTColumnBuilder.newColumn('Exp_completo').withTitle('Lesionado'),
        DTColumnBuilder.newColumn('Exp_fecreg').withTitle('Fecha Atención'),
        DTColumnBuilder.newColumn('ClasL_tipo').withTitle('Clasificacion'),
        DTColumnBuilder.newColumn('EXP_estatus').withTitle('Estatus Documental'),
        DTColumnBuilder.newColumn('EXP_estatusFac').withTitle('Estatus Facturación')
    ];
    
});

app.controller('mesCtrl', function($scope,busqueda , DTOptionsBuilder, DTColumnBuilder , $rootScope, $location){

    var ruta = 'api/api.php?funcion=buscaExpedientesXUnidad&unidad=' + $rootScope.unidad;

    $scope.inicio = function(){

        $scope.titulo = 'Detalle de Lesionados en el Mes';
        $scope.grafica = false;
        $scope.tabla = false;
        $scope.cargando = false;
        $scope.unidadN = 'Sin Unidad Seleccionada';

        $scope.datos = {
            mes:0
        }

        //$scope.buscaExpedientes();

    }

    $scope.muestraDetalle = function(info) {

        console.log(info);
        $location.path('/detalle/expediente/'+ info.Exp_folio);
        
    };

    $scope.$on('event:dataTableLoaded', function(event, data) { 
        // console.log(event); 
        // console.log(data);

        $scope.tableId = data.id;

        $scope.Buscar = function() {
            $scope.searchData = angular.copy($scope.datos);
            $scope.cargando = true;

            $('#'+$scope.tableId).DataTable().ajax.reload(function (data) {
                $scope.$apply(function() {
                    $scope.cargando = false;
                }); 
            });
        };

    });

    $scope.dtOptions = DTOptionsBuilder.newOptions()
    .withOption('ajax', {
        "url": ruta,
        "type": 'POST',
        "data": function ( d ) {
                console.log("data");
                d.search = $scope.datos || {}; //search criteria
                return JSON.stringify(d);
        }
            
    })
    .withOption('lengthMenu', [ [ 25, 50, 100, -1], [ 25, 50, 100, "Todo"] ])
    // .withOption('serverSide', true)
    .withPaginationType('full_numbers')
    .withOption('language', {
        paginate: {
            first: "«",
            last: "»",
            next: "→",
            previous: "←"
        },
        search: "Buscar:",
        loadingRecords: "Cargando Información....",
        lengthMenu: "    Mostrar _MENU_ entradas",
        processing: "Procesando Información",
        infoEmpty: "No se encontro información",
        emptyTable: "Sin Información disponible",
        info: "Mostrando pagina _PAGE_ de _PAGES_ , Registros encontrados _TOTAL_ ",
        infoFiltered: " - encontrados _MAX_ coincidencias"
    })

    .withOption('rowCallback', function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
        $('td', nRow).bind('click', function() {
            $scope.$apply(function() {
                $scope.muestraDetalle(aData);
            });
        });
        return nRow;
    })


    // Add Bootstrap compatibility
    .withBootstrap()
    // Add ColVis compatibility
    .withColVis()
    // Add a state change function
    .withColVisStateChange(function(iColumn, bVisible) {
        console.log('The column' + iColumn + ' has changed its status to ' + bVisible)
    })

    .withOption("colVis",{
        buttonText: "Mostrar / Ocultar Columnas"
    })
    // Exclude the last column from the list
    // .withColVisOption('aiExclude', [2])

    // Add ColReorder compatibility
    .withColReorder()
    // Set order
    // .withColReorderOrder([1, 0, 2])
    // Fix last right column
    .withColReorderOption('iFixedColumnsRight', 1)
    .withColReorderCallback(function() {
        console.log('Columns order has been changed with: ' + this.fnOrder());
    })
    //Add Table tools compatibility

    .withTableTools('js/swf/copy_csv_xls_pdf.swf')
    .withTableToolsButtons([

        {
            "sExtends":     "copy",
             "sButtonText": "Copiar"
        },
        {
            'sExtends': 'collection',
            'sButtonText': 'Exportar',
            'aButtons': ['xls', 'pdf']
        }
    ]);
        
    $scope.dtColumns = [

        DTColumnBuilder.newColumn('Exp_folio').withTitle('Folio'),
        DTColumnBuilder.newColumn('Cia_nombrecorto').withTitle('Cliente'),
        DTColumnBuilder.newColumn('Exp_poliza').withTitle('Poliza'),
        DTColumnBuilder.newColumn('Exp_siniestro').withTitle('Siniestro'),
        DTColumnBuilder.newColumn('EXP_reporte').withTitle('Reporte'),
        DTColumnBuilder.newColumn('Exp_completo').withTitle('Lesionado'),
        DTColumnBuilder.newColumn('Exp_fecreg').withTitle('Fecha Atención'),
        DTColumnBuilder.newColumn('ClasL_tipo').withTitle('Clasificacion'),
        DTColumnBuilder.newColumn('EXP_estatus').withTitle('Estatus Documental'),
        DTColumnBuilder.newColumn('EXP_estatusFac').withTitle('Estatus Facturación')
    ];
    
});

app.controller('usuariosCtrl', function($scope, $rootScope,  busqueda, DTOptionsBuilder, DTColumnBuilder, $http ) {
    
    $('#myModal').on('hidden.bs.modal', function (e) {

        $scope.$apply(function() {
            $scope.dtOptions.reloadData();
        });

    });

    $scope.inicio = function(){

        $scope.datos = {
            nombre:'',
            usuario:'',
            psw:'',
            correo:'',
            empresa:'',
            admin:0
        }     

        $scope.mensaje = '';
        $scope.muestraUnidades();

    }

    $scope.muestraUnidades = function(){
        busqueda.unidades().success(function (data){
            $scope.empresas = data;
        });

    }

    $scope.guardar = function(){

        $scope.mensaje = '';
        $('#boton').button('loading');

        $http({
            url:'api/api.php?funcion=usuario',
            method:'POST', 
            contentType: 'application/json', 
            dataType: "json", 
            data: $scope.datos
        }).success( function (data){

            $scope.mensaje = data.respuesta;
            $('#boton').button('reset');
            
        }).error( function (xhr,status,data){

            $('#boton').button('reset');
            alert('Existe Un Problema de Conexion Intente Cargar Nuevamente la Pagina');

        });

    }

    $scope.muestraDetalle = function(info) {

        console.log(info);
        //$location.path('/detalle/expediente/'+ info.Exp_folio);
        //$scope.message = info.id + ' - ' + info.firstName;

    };

    $scope.dtOptions = DTOptionsBuilder
        
        .fromSource('api/api.php?funcion=usuarios')
        .withOption('lengthMenu', [ [ 25, 50, 100, -1], [ 25, 50, 100, "Todo"] ])
        .withOption('responsive', true)
        // .withOption('serverSide', true)
        .withPaginationType('full_numbers')
        .withOption('language', {
            paginate: {
                first: "«",
                last: "»",
                next: "→",
                previous: "←"
            },
            search: "Buscar:",
            loadingRecords: "Cargando Información....",
            lengthMenu: "    Mostrar _MENU_ entradas",
            processing: "Procesando Información",
            infoEmpty: "No se encontro información",
            emptyTable: "Sin Información disponible",
            info: "Mostrando pagina _PAGE_ de _PAGES_ , Registros encontrados _TOTAL_ ",
            infoFiltered: " - encontrados _MAX_ coincidencias"
        })

        .withOption('rowCallback', function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            $('td', nRow).bind('click', function() {
                $scope.$apply(function() {
                    $scope.muestraDetalle(aData);
                });
            });
            return nRow;
        })


        // Add Bootstrap compatibility
        .withBootstrap()
        // Add ColVis compatibility
        .withColVis()
        // Add a state change function
        .withColVisStateChange(function(iColumn, bVisible) {
            console.log('The column' + iColumn + ' has changed its status to ' + bVisible)
        })

        .withOption("colVis",{
            buttonText: "Mostrar / Ocultar Columnas"
        })
        // Exclude the last column from the list
        // .withColVisOption('aiExclude', [2])

        // Add ColReorder compatibility
        .withColReorder()
        // Set order
        // .withColReorderOrder([1, 0, 2])
        // Fix last right column
        .withColReorderOption('iFixedColumnsRight', 1)
        .withColReorderCallback(function() {
            console.log('Columns order has been changed with: ' + this.fnOrder());
        })
        //Add Table tools compatibility

        .withTableTools('js/swf/copy_csv_xls_pdf.swf')
        .withTableToolsButtons([

            {
                "sExtends":     "copy",
                 "sButtonText": "Copiar"
            },
            {
                'sExtends': 'collection',
                'sButtonText': 'Exportar',
                'aButtons': ['xls', 'pdf']
            }
        ]);
        
    $scope.dtColumns = [

        DTColumnBuilder.newColumn('USU_claveint').withTitle('id'),
        DTColumnBuilder.newColumn('USU_Nombre').withTitle('Nombre'),
        DTColumnBuilder.newColumn('USU_login').withTitle('Usuario'),
        DTColumnBuilder.newColumn('USU_correo').withTitle('Correo')
    ];

});

app.controller('sinDocumentacionCtrl', function($scope, $rootScope, busqueda, $filter, $location) {
    
    $scope.inicio = function(){

        $rootScope.cerrar = false;
        $rootScope.cargar = false;
        $scope.inicial = 0;
        $scope.muestraGraficas();

    }

    $scope.muestraGraficas = function(){ 

        busqueda.sindocumentacion().success(function (data){
            // $scope.datos2 = data;
            // console.log(data);
            var chart = AmCharts.makeChart("chartdiv-9",{
                "theme": "none",
                "type": "serial",
                "startDuration": 2,
                "dataProvider": data,
                "graphs": [{
                    "balloonText": "[[category]]: <b>[[value]]</b>",
                    "colorField": "#1E1E1E",
                    "labelText": "[[value]] Atn.",
                    "labelPosition":"top",
                    "labelOffset":10,
                    "labelColorField":"#1E1E1E",
                    "fontSize":19,
                    "color":"blue",
                    "fillAlphas": 0.85,
                    "lineAlpha": 0.1,
                    "type": "column",
                    "topRadius":1,
                    "valueField": "folios"
                }],
                "depth3D": 40,
                "angle": 30,
                "chartCursor": {
                    "categoryBalloonEnabled": false,
                    "cursorAlpha": 0,
                    "zoomable": false
                },    
                "categoryField": "Periodo",
                "categoryAxis": {
                    "gridPosition": "start",
                    "axisAlpha":0,
                    "gridAlpha":0
                    
                },
                "exportConfig":{
                    "menuTop":"20px",
                    "menuRight":"20px",
                    "menuItems": [{
                    "icon": '/lib/3/images/export.png',
                    "format": 'png'   
                    }]  
                }
            });

            chart.addListener("clickGraphItem", function(valor){
                console.log(valor);

                // se requiere aplicar para que cause efectos en el scope de las variables 
                $scope.$apply(function() {

                    $scope.muestracategoria(valor.item.category)
                    
                });
            });

        });
        
    }

    $scope.muestracategoria = function(categoria){
        console.log(categoria);
        $location.path('/detalle/categoria/'+categoria);
    }
    
});

app.controller('ticketCtrl', function($scope, $rootScope, busqueda, $filter, $location, DTOptionsBuilder, DTColumnBuilder) {
    
    var ruta = 'api/api.php?funcion=tickets&unidad=' + $rootScope.unidad;

    $scope.inicio = function(){

        $rootScope.cerrar = false;
        $rootScope.cargar = false;
        $scope.cargando = false;
        $scope.inicial = 0;
        $scope.muestraGraficas();

    }

    $scope.muestraGraficas = function(){ 

        busqueda.estadisticatickets().success(function (data){

            console.log(data);
            var chart = AmCharts.makeChart("chartdiv-8",{
                "theme": "none",
                "type": "serial",
                "startDuration": 2,
                "dataProvider": data,
                "graphs": [{
                    "balloonText": "[[category]]: <b>[[value]]</b>",
                    "colorField": "color",
                    "fillAlphas": 0.85,
                    "lineAlpha": 0.1,
                    "type": "column",
                    "topRadius":1,
                    "valueField": "numero"
                }],
                "depth3D": 40,
                "angle": 30,
                "chartCursor": {
                    "categoryBalloonEnabled": false,
                    "cursorAlpha": 0,
                    "zoomable": false
                },    
                "categoryField": "categoria",
                "categoryAxis": {
                    "gridPosition": "start",
                    "axisAlpha":0,
                    "gridAlpha":0
                    
                },
                "exportConfig":{
                    "menuTop":"20px",
                    "menuRight":"20px",
                    "menuItems": [{
                    "icon": '/lib/3/images/export.png',
                    "format": 'png'   
                    }]  
                }
            });

        });
        
    }

    $scope.muestraDetalle = function(info) {

        console.log(info);
        $location.path('/detalle/expediente/'+ info.Folio_Web);
        
    };

    $scope.$on('event:dataTableLoaded', function(event, data) { 
        // console.log(event); 
        // console.log(data);

        $scope.tableId = data.id;

        $scope.Buscar = function() {
            $scope.searchData = angular.copy($scope.datos);
            $scope.cargando = true;

            $('#'+$scope.tableId).DataTable().ajax.reload(function (data) {
                $scope.$apply(function() {
                    $scope.cargando = false;
                }); 
            });
        };

    });

    $scope.dtOptions = DTOptionsBuilder.newOptions()
    .withOption('ajax', {
        "url": ruta,
        "type": 'POST',
        "data": function ( d ) {
                console.log("data");
                d.search = $scope.datos || {}; //search criteria
                return JSON.stringify(d);
        }
            
    })
    .withOption('lengthMenu', [ [ 25, 50, 100, -1], [ 25, 50, 100, "Todo"] ])
    // .withOption('serverSide', true)
    .withPaginationType('full_numbers')
    .withOption('language', {
        paginate: {
            first: "«",
            last: "»",
            next: "→",
            previous: "←"
        },
        search: "Buscar:",
        loadingRecords: "Cargando Información....",
        lengthMenu: "    Mostrar _MENU_ entradas",
        processing: "Procesando Información",
        infoEmpty: "No se encontro información",
        emptyTable: "Sin Información disponible",
        info: "Mostrando pagina _PAGE_ de _PAGES_ , Registros encontrados _TOTAL_ ",
        infoFiltered: " - encontrados _MAX_ coincidencias"
    })

    .withOption('rowCallback', function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
        $('td', nRow).bind('click', function() {
            $scope.$apply(function() {
                $scope.muestraDetalle(aData);
            });
        });
        return nRow;
    })


    // Add Bootstrap compatibility
    .withBootstrap()
    // Add ColVis compatibility
    .withColVis()
    // Add a state change function
    .withColVisStateChange(function(iColumn, bVisible) {
        console.log('The column' + iColumn + ' has changed its status to ' + bVisible)
    })

    .withOption("colVis",{
        buttonText: "Mostrar / Ocultar Columnas"
    })
    // Exclude the last column from the list
    // .withColVisOption('aiExclude', [2])

    // Add ColReorder compatibility
    .withColReorder()
    // Set order
    // .withColReorderOrder([1, 0, 2])
    // Fix last right column
    .withColReorderOption('iFixedColumnsRight', 1)
    .withColReorderCallback(function() {
        console.log('Columns order has been changed with: ' + this.fnOrder());
    })
    //Add Table tools compatibility

    .withTableTools('js/swf/copy_csv_xls_pdf.swf')
    .withTableToolsButtons([

        {
            "sExtends":     "copy",
             "sButtonText": "Copiar"
        },
        {
            'sExtends': 'collection',
            'sButtonText': 'Exportar',
            'aButtons': ['xls', 'pdf']
        }
    ]);
        
    $scope.dtColumns = [

        DTColumnBuilder.newColumn('Cliente').withTitle('Cliente'),
        DTColumnBuilder.newColumn('Folio_Interno').withTitle('Folio Interno'),
        DTColumnBuilder.newColumn('Folio_Web').withTitle('Folio Web'),
        DTColumnBuilder.newColumn('Asignado').withTitle('Asignado'),
        DTColumnBuilder.newColumn('Categoria').withTitle('Categoria'),
        DTColumnBuilder.newColumn('Registro').withTitle('Registro'),
        DTColumnBuilder.newColumn('Status').withTitle('Status')
    ];
    
});


