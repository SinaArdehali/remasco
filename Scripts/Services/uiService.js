(function () {
    var dataBallModule = angular.module('dataBallApp');
    dataBallModule.service('uiService', function () {

        this.startLoading = function () {
            var exist = $('.appLoading');
            if (exist.length == 0) {
                var elem = $('[data-ng-app]');
                var box = '<div class="appLoading"><table><tr><td><img style="display:inline-block" src="_layouts/15/webparts/Styles/images/loading-24.gif"></td><td><b style="color:#ff9900;padding-left:5px;">لطفاً صبر کنید ...</b></td></tr></table></div>';
                elem.append(box);
            }
            $('.appLoading').animate({
                right: '10px'
            }, 300);
        };

        this.endLoading = function () {
            $('.appLoading').remove();

        };

        this.error = function (message) {
            alertify.error(message);
        };

        this.success = function (message) {
            alertify.success(message);
        }

        this.alert = function (message) {
            alertify.alert(message);
        }

        this.confirm = function (message, callBack) {
            alertify.confirm(message, function (e) {
                if (e) {
                    callBack();
                };
            });
        }
    });
})();