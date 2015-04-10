var app = angular.module('infounidad', [
    'ui.bootstrap',
    'ngCookies',
    'ngRoute',
    'ngAnimate',
    'datatables',
    'countTo',
    'jsTag',
    'frapontillo.bootstrap-switch',
    'webStorageModule'
    
]);

app.config(function($routeProvider){

    //Configuramos la ruta que queremos el html que le toca y que controlador usara

    $routeProvider.when('/ayuda',{
            templateUrl: 'vistas/ayuda.html'
    });

    $routeProvider.when('/busquedas',{
            templateUrl: 'vistas/busquedas.html',
            controller : 'busquedasCtrl'
    });

    $routeProvider.when('/configuracion',{
            templateUrl: 'vistas/configuracion.html',
            controller : 'configuracionCtrl'
    });

    $routeProvider.when('/clientes',{
            templateUrl: 'vistas/clientes.html',
            controller : 'clientesCtrl'
    });

    $routeProvider.when('/contacto',{
            templateUrl: 'vistas/contacto.html'
    });

    $routeProvider.when('/detalle/expediente/:folio',{
            templateUrl: 'vistas/expediente.html',
            controller : 'expedienteCtrl'
    });

    $routeProvider.when('/detalle/anual',{
            templateUrl: 'vistas/anual.html',
            controller : 'anualCtrl'
    });

    $routeProvider.when('/detalle/mes',{
            templateUrl: 'vistas/mes.html',
            controller : 'mesCtrl'
    });

    $routeProvider.when('/detalle/categoria/:categoria',{
            templateUrl: 'vistas/mes.html',
            controller : 'categoriaCtrl'
    });

    $routeProvider.when('/facturas',{
            templateUrl: 'vistas/facturas.html',
            controller : 'facturasCtrl'
    });

    $routeProvider.when('/fechas/:valor',{
            templateUrl: 'vistas/fechas.html',
            controller : 'fechaCtrl'
    });

    $routeProvider.when('/historico',{
            templateUrl: 'vistas/historico.html',
            controller : 'historicoCtrl'
    });
    
    $routeProvider.when('/home',{
            templateUrl: 'vistas/home.html',
            controller : 'homeCtrl'
    });

    $routeProvider.when('/listado/:categoria/:mes1/:ano1/:mes2/:ano2',{
            templateUrl: 'vistas/listado.html',
            controller : 'listadoCtrl'
    });

    $routeProvider.when('/login',{
            templateUrl: 'vistas/login.html',
            controller : 'loginCtrl'
    });

    $routeProvider.when('/sindocumentacion',{
            templateUrl: 'vistas/sindocumentacion.html',
            controller : 'sinDocumentacionCtrl'
    });

    $routeProvider.when('/tickets',{
            templateUrl: 'vistas/ticket.html',
            controller : 'ticketCtrl'
    });

    $routeProvider.when('/usuarios',{
            templateUrl: 'vistas/usuarios.html',
            controller : 'usuariosCtrl'
    });

    
    $routeProvider.otherwise({redirectTo:'/login'});
    
});

app.run(function ($rootScope ,$cookies, $cookieStore, auth, $location, $http, webStorage){

    /**
     * Sidebar Toggle & Cookie Control
     *
     */
    $rootScope.admin = true;
    $rootScope.cerrar = false;

    var mobileView = 992;

    $rootScope.logout = function(){
        auth.cerrar();
    } 

    $rootScope.getWidth = function() { return window.innerWidth; };

    $rootScope.$watch($rootScope.getWidth, function(newValue, oldValue)
    {
        if(newValue >= mobileView)
        {
            if(angular.isDefined($cookieStore.get('toggle')))
            {
                if($cookieStore.get('toggle') == false)
                    $rootScope.toggle = false;

                else
                    $rootScope.toggle = true;
            }
            else 
            {
                $rootScope.toggle = true;
            }
        }
        else
        {
            $rootScope.toggle = false;
        }

    });

    $rootScope.toggleSidebar = function() 
    {
        $rootScope.toggle = ! $rootScope.toggle;

        $cookieStore.put('toggle', $rootScope.toggle);
    };

    window.onresize = function() { $rootScope.$apply(); };


    $rootScope.$on('$routeChangeStart', function(){

        $rootScope.cargar = true;
        // $rootScope.username =  $cookies.username;
        // $rootScope.unidad = $cookies.unidad;
        // $rootScope.user = $cookies.user;
        // $rootScope.nombreUni = $cookies.nombreUni;
        $rootScope.fechahoy = new Date();

        auth.checkStatus();

    });

    $rootScope.descarga = function(archivo){

        //this trick will generate a temp <a /> tag
        var link = document.createElement("a");    
        link.href = archivo;

        //set the visibility hidden so it will not effect on your web-layout
        link.style = "visibility:hidden";
        link.download = archivo;

        //this part will append the anchor tag and remove it after automatic click
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

    }

    //generamos al rootscope las variables que tenemos en las cookies para no perder la sesion 
    // $rootScope.username =  $cookies.username;
    //$rootScope.unidad = $cookies.unidad;
    // $rootScope.user = $cookies.user;
    // $rootScope.nombreUni = $cookies.nombreUni;

    $rootScope.username = webStorage.session.get('username');
    $rootScope.user = webStorage.session.get('user');
    $rootScope.unidad = webStorage.session.get('unidad');
    $rootScope.nombreUni = webStorage.session.get('nombreUni');

    $http.defaults.headers.common.Authorization = 'token';
    
});


app.factory("auth", function($cookies,$cookieStore,$location, $rootScope, $http, webStorage){
    
    return{
        login : function(username, password)
        {   
            $('#boton').button('loading');

            $http({

                url:'api/api.php?funcion=login',
                method:'POST', 
                contentType: 'application/json', 
                dataType: "json", 
                data:{user:username,psw:password}

            }).success( function (data){
                
                $('#boton').button('reset');

                //console.log(data);

                if(data.respuesta){

                    $rootScope.mensaje = data.respuesta;

                }else{
                    
                    
                    //creamos la cookie con el nombre que nos han pasado el api
                    // $cookies.username = data.Usu_nombre;
                    // $cookies.user = data.Usu_login;
                    // $cookies.unidad = data.Uni_clave;
                    // $cookies.nombreUni = data.Uni_nombre;
                    webStorage.local.clear();

                    webStorage.session.add('username', data.Usu_nombre);
                    webStorage.session.add('user', data.Usu_login);
                    webStorage.session.add('unidad', data.Uni_clave);
                    webStorage.session.add('nombreUni', data.Uni_nombre);
                    
                    $rootScope.username = data.Usu_nombre;
                    $rootScope.user = data.Usu_login;
                    $rootScope.unidad = data.Uni_clave;
                    $rootScope.nombreUni = data.Uni_nombre;

                    apiKey = data.Usu_nombre;

                    $location.path("/home");
                    
                }
                
            }).error( function (xhr,status,data){

                $('#boton').button('reset');
                alert('Existe Un Problema de Conexion Intente Cargar Nuevamente la Pagina');

            });

            
        },
        logout : function()
        {
            
            //al hacer logout eliminamos la cookie con $cookieStore.remove y los rootscope
            // $cookieStore.remove("username");
            // $cookieStore.remove("unidad");
            // $cookieStore.remove("user");
            // $cookieStore.remove("nombreUni");
            //$cookieStore.remove("toggle");

            webStorage.session.clear();
            webStorage.local.clear();

            $rootScope.username =  '';
            // $rootScope.unidad = '';
            // $rootScope.user = '';
            // $rootScope.nombreUni = '';

            

            //mandamos al login
            $location.path("/login");
 
        },
        cerrar : function(){

            webStorage.session.clear();
            webStorage.local.clear();
            $rootScope.username =  '';
            $location.path("/login");
            
        },
        checkStatus : function()
        {
            //console.log(webStorage.session.get('username'));
            //creamos un array con las rutas que queremos controlar
            if($location.path() != "/login" && webStorage.session.get('username') == null)
            {   
                $location.path("/login");
            }
            //en el caso de que intente acceder al login y ya haya iniciado sesión lo mandamos a la home
            if($location.path() == "/login" && webStorage.session.get('username') != null)
            {
                $location.path("/home");
            }
        }
    }
    
});


