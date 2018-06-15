(function () {
    var dataBallModule = angular.module('dataBallApp', ['ngDialog']);

    dataBallModule.service('dataService', ['$q', '$http', function ($q, $http) {
        this.get = function (url) {
            var deferred = $q.defer();

            $http.get(url).success(function (result) {
                deferred.resolve(result);
            }).error(function (err) {
                deferred.reject(err);
            });

            return deferred.promise;
        };

        this.post = function (url, data) {
            var ext = '?p=' + new Date().getTime();
            var deferred = $q.defer();

            $http.post(url + ext, data).success(function (result) {
                deferred.resolve(result);
            }).error(function (err) {
                deferred.reject(err);
            });

            return deferred.promise;
        };
    }]);

    dataBallModule.directive('ngEnter', function () {
        return function (scope, element, attrs) {
            element.bind("keydown keypress", function (event) {
                if (event.which === 13) {
                    scope.$apply(function () {
                        scope.$eval(attrs.ngEnter);
                    });

                    event.preventDefault();
                }
            });
        };
    });
})();
