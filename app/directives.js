app.directive( 'ruta', function ( $location ) {
  return function ( scope, element, attrs ) {
    var path;

    attrs.$observe( 'ruta', function (val) {
      path = val;
    });

    element.bind( 'click', function () {
      scope.$apply( function () {
        $location.path( path );
      });
    });
  };
});


app.directive('ngElevateZoom', function() {
  return {
    restrict: 'A',
    link: function(scope, element, attrs) {

      //Will watch for changes on the attribute
      attrs.$observe('zoomImage',function(){
        linkElevateZoom();
      })

      function linkElevateZoom(){
        //Check if its not empty
        if (!attrs.zoomImage) return;
        element.attr('data-zoom-image',attrs.zoomImage);
        $(element).elevateZoom();
      }

      linkElevateZoom();

    }
  };
});


app.directive('zoom', function() {
  return {
    restrict: 'A',
    link: function(scope, element, attrs) {

      console.log('entro');
      $(element).zoom({ on:'click' }); 

    }
  };
});

app.directive('linechart', function() {

    return {

        // required to make it work as an element
        restrict: 'E',
        template: '<div></div>',
        replace: true,
        // observe and manipulate the DOM
        link: function($scope, element, attrs) {

            var data = $scope[attrs.data],
                xkey = $scope[attrs.xkey],
                ykeys= $scope[attrs.ykeys],
                labels= $scope[attrs.labels];

            // Morris.Bar({
            //         element: element,
            //         data: data,
            //         xkey: xkey,
            //         ykeys: ykeys,
            //         labels: labels
            //     });

            Morris.Line({
              // ID of the element in which to draw the chart.
              element: element,
              // Chart data records -- each entry in this array corresponds to a point on
              // the chart.
              data: data,
              // The name of the data record attribute that contains x-values.
              xkey: xkey,
              // A list of names of data record attributes that contain y-values.
              ykeys: ykeys,
              // Labels for the ykeys -- will be displayed when you hover over the
              // chart.
              labels: labels

            });
        }

    };

});


app.directive('loading', function () {
    return {
        restrict: 'AE',
        replace: 'false',
        template: '<div class="loading"><div class="double-bounce1"></div><div class="double-bounce2"></div></div>'
    }
});

//funcion para convertir mayusculas
app.directive('folio', function() {
    return {
      restrict: 'A',
      require: 'ngModel',
      link: function(scope, element, attrs, modelCtrl) {

          var functionToCall = scope.$eval(attrs.folio);

          var rellenaFolio = function(folio){

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
          
          modelCtrl.$parsers.push(function (inputValue) {
             if (inputValue == undefined) return '' 
             var transformedInput = inputValue.toUpperCase();
             if (transformedInput!=inputValue) {
                modelCtrl.$setViewValue(transformedInput);
                modelCtrl.$render();
             }         

             return transformedInput;         
          });

          element.on('keydown', function(e){
                
                // console.log(scope);
                // console.log(element);
                // console.log(attrs);
                console.log(modelCtrl);
                

                var cantidad = modelCtrl.$modelValue.length;

                console.log(cantidad);
                console.log(e);

                //los primero cuatro caracteres NO deben ser numeros
                if(cantidad < 4){
                  if (e.keyCode >= 48 && e.keyCode <= 57 || e.keyCode >= 96 && e.keyCode <= 105) {
                        e.preventDefault();
                    }
                }

                //los ultimos 6 NO deben ser letras
                if(cantidad > 3 && cantidad < 10){
                  if (e.keyCode >= 65 && e.keyCode <= 90) {
                        e.preventDefault();
                  }
                }

                //Si son mas de 10 digitos no escribas mas
                if(cantidad > 9){
                    
                    if (e.keyCode >= 65 && e.keyCode <= 90 || e.keyCode >= 48 && e.keyCode <= 57 || e.keyCode >= 96 && e.keyCode <= 105) {
                      e.preventDefault();
                    }else{
                      console.log('Presionaste ' + e.keyCode);
                    } 

                }

                if (e.keyCode == 13 || e.keyCode == 9) {

                      if (cantidad > 4) {

                          functionToCall(modelCtrl.$modelValue);
                            
                      };
                      
                          
                }


          });



      }

    };
    
});

//funcion para convertir mayusculas
app.directive('mayusculas', function() {
   return {
     require: 'ngModel',
     link: function(scope, element, attrs, modelCtrl) {
        var capitalize = function(inputValue) {
           var capitalized = inputValue.toUpperCase();
           if(capitalized !== inputValue) {
              modelCtrl.$setViewValue(capitalized);
              modelCtrl.$render();
            }         
            return capitalized;
         }
         modelCtrl.$parsers.push(capitalize);
         capitalize(scope[attrs.ngModel]);  // capitalize initial value
     }
   };
});


app.directive('numeros', function(){
   return {
     require: 'ngModel',
     link: function(scope, element, attrs, modelCtrl) {

       modelCtrl.$parsers.push(function (inputValue) {
           if (inputValue == undefined) return '' 
           var transformedInput = inputValue.replace(/[^0-9]/g, ''); 
           if (transformedInput!=inputValue) {
              modelCtrl.$setViewValue(transformedInput);
              modelCtrl.$render();
           }         

           return transformedInput;         
       });
     }
   };
});


app.directive('archivo', function() {
  return {
      scope: true,
      scope: {
          archivo: '=',
      },
      link: function($scope, element, attrs) {
        
        console.log($scope);
        console.log(element);
        console.log(attrs);

        element.on('click',function(){
          
            var file = attrs.archivo;

            var link = document.createElement("a");    
            link.href = file;
            
            //set the visibility hidden so it will not effect on your web-layout
            link.style = "visibility:hidden";
            link.download = file;
            
            //this part will append the anchor tag and remove it after automatic click
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        })
      }
    }
});