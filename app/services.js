app.factory("busqueda", function($http, $rootScope){
    
    return{
        detalleExpediente:function(folio){
            return $http.get('api/api.php?funcion=detalleExpediente&unidad=' + $rootScope.unidad + '&expediente='+ folio);
        },
        estadisticaatenciones:function(ano){
            return $http.get('api/api.php?funcion=estadisticaAtencionesXmes&unidad=' + $rootScope.unidad + '&ano=' + ano);
        },
        estadisticaatencionesxsemana:function(mes,ano){
            return $http.get('api/api.php?funcion=estadisticaAtencionesXsemana&unidad=' + $rootScope.unidad + '&mes=' + mes + '&ano=' + ano);
        },
        estadisticaunidadesmes:function(mes){
            return $http.get('api/api.php?funcion=estadisticaAtencionesXunidadXmes&unidad=' + $rootScope.unidad + '&mes='+ mes);
        },
        estadisticaciudades:function(){
            return $http.get('api/api.php?funcion=estadisticaAtencionesXciudad&unidad=' + $rootScope.unidad);
        },
        estadisticatickets:function(){
            return $http.get('api/api.php?funcion=estadisticaTickets&unidad=' + $rootScope.unidad);
        },
        estadisticautilizadas:function(){
            return $http.get('api/api.php?funcion=estadisticaNoutlizada&unidad=' + $rootScope.unidad);
        },
        expedientexsdia:function(){
            return $http.get('api/api.php?funcion=expedientesdia&unidad=' + $rootScope.unidad);
        },
        expedientexsmes:function(){
            return $http.get('api/api.php?funcion=expedientesmes&unidad=' + $rootScope.unidad);
        },
        expedientexsano:function(){
            return $http.get('api/api.php?funcion=expedientesano&unidad=' + $rootScope.unidad);
        },
        expedientexfecha:function(mes,ano){
            return $http.get('api/api.php?funcion=buscaExpedientesXfecha&unidad=' + $rootScope.unidad + '&mes=' + mes + '&ano=' + ano);
        },
        estadocuenta:function(datos){
            return $http.post('api/api.php?funcion=estadocuenta&unidad=' + $rootScope.unidad, datos);
        },
        periodos:function(){
            return $http.get('api/api.php?funcion=periodos&unidad=' + $rootScope.unidad);
        },
        sindocumentacion:function(){
            return $http.post('api/api.php?funcion=periodossindocumentacion&unidad=' + $rootScope.unidad);
        },
        unidades:function(){
            return $http.get('api/api.php?funcion=unidades');
        },
        rellenaFolio:function(folio){

            if (folio != '') {

              var totalletras = folio.length;

              var letras = folio.substr(0,4);
              var numeros = folio.substr(4,totalletras);

              if(letras.length < 4 ){

                var faltantes = 4 - letras.length;

                for (var i = 0; i < faltantes; i++) {

                  var letra = letras.charAt(i);
                  letras = letras + "0";
                }
              }

              if(numeros.length < 6 ){

                var faltantes = 6 - numeros.length;

                for (var i = 0; i < faltantes; i++) {
                  
                  numeros = "0" + numeros;
                }
              }

              folio = letras + numeros;

              return folio;

            }else{

              return folio

            }

        }
    }
});


app.factory("envios", function($http, $rootScope){
    return{
        correo:function(datos){
            return $http.post('api/api.php?funcion=enviacorreo',datos);
        }
    }
});

app.factory('DTLoadingTemplate', function() {
    return {
        html: '<h1>CUSTOM LOADING</h1>'
    };
});