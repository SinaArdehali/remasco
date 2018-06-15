(function () {
    var dataBallModule = angular.module('dataBallApp');

     // Function to decode string
    String.prototype.decode = function(){
      return decodeURIComponent(escape(this));
    }

    // Function to encode string
    String.prototype.encode = function() {
      return unescape(encodeURIComponent(this));
    }


    dataBallModule.service('dataBallService', ['dataService', function (dataService) {

        this.search = function (query, callBack, errorCallback) {
            dataService.post('search.php', { query: escape(query) }).then(function (data) {

                    var statut = $('#statut').attr('class');

                    if(statut == 'local') {
                        callBack(data);    
                    }

                    if(statut == 'api') {
                        callBack(JSON.parse(data));    
                    }

            }).catch(function (err) {
                errorCallback(err);
            });
        }

        this.addHistory = function (history, callBack, errorCallback) {
            dataService.post('addHistory.php', history).then(function (data) {
                callBack(data);
            }).catch(function (err) {
                errorCallback(err);
            });
        }

        this.removeHistory = function (id, callBack, errorCallback) {
            dataService.post('removeHistory.php', { idHistoric :id}).then(function (data) {
                callBack(data);
            }).catch(function (err) {
                errorCallback(err);
            });
        }

        this.addTheme = function (theme, callBack, errorCallback) {
            dataService.post('addTheme.php', theme).then(function (data) {
                callBack(data);
            }).catch(function (err) {
                errorCallback(err);
            });
        }

        this.removeTheme = function (id, callBack, errorCallback) {
            dataService.post('removeTheme.php', {idTheme: id}).then(function (data) {
                callBack(data);
            }).catch(function (err) {
                errorCallback(err);
            });
        }

        this.addHistoricToTheme = function (historicId, themeId, callBack, errorCallback) {
            dataService.post('addHistoricToTheme.php', {
                idHistoric: historicId,
                idTheme: themeId
            }).then(function (data) {
                callBack(data);
            }).catch(function (err) {
                errorCallback(err);
            });
        }

        this.removeHistoricFromTheme = function (historicId, themeId, callBack, errorCallback) {
            dataService.post('removeHistoryFromTheme.php', {
                idHistoric: historicId,
                idTheme: themeId
            }).then(function (data) {
                callBack(data);
            }).catch(function (err) {
                errorCallback(err);
            });
        }

        this.load = function (callBack, errorCallback) {

            dataService.get('load.php').then(function (data) {
                //callBack({
                //    themes: [{
                //        themeId: 1,
                //        themeName: 'Theme 1',
                //        historics: [{
                //            idHistoric: 1,
                //            query: 'office'
                //        }, {
                //            idHistoric: 2,
                //            query: 'macron'
                //        }]
                //    }, {
                //        themeId: 2,
                //        themeName: 'Theme 2',
                //        historics: [{
                //            idHistoric: 3,
                //            query: 'test'
                //        }, {
                //            idHistoric: 4,
                //            query: 'test2'
                //        }]
                //    }]
                //});
                callBack(data);
            }).catch(function (err) {
                //callBack({
                //    themes: [{
                //        themeName: 'Theme 1',
                //        historics: [{
                //            idHistoric: 1,
                //            query: 'office'
                //        }, {
                //            idHistoric: 2,
                //            query: 'macron'
                //        }]
                //    }, {
                //        themeName: 'Theme 2',
                //        historics: [{
                //            idHistoric: 3,
                //            query: 'test'
                //        }, {
                //            idHistoric: 4,
                //            query: 'test2'
                //        }]
                //    }]
                //});
                errorCallback(err);
            });
        }

    }]);
})();
