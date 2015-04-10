var app = angular.module('infoweb', ['ui.bootstrap', 'ngCookies']);

/**
 * Loading Directive
 * @see http://tobiasahlin.com/spinkit/
 */
app.directive('loading', function () {
    return {
        restrict: 'AE',
        replace: 'false',
        template: '<div class="loading"><div class="double-bounce1"></div><div class="double-bounce2"></div></div>'
    }
});

app.run(function ($rootScope ,$cookies, $cookieStore){

    /**
     * Sidebar Toggle & Cookie Control
     *
     */
    var mobileView = 992;

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

});

app.controller('homeCtrl', function($scope, $cookieStore) {

    
});