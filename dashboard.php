<?php
session_start();



if(!isset($_SESSION["username"])){
    echo "Accès à l'application non autorisé !";
    exit;
}

require_once('constants.php');

echo '<h3 class="userInfo">';
echo "Bonjour ".$_SESSION["firstname"].".<br><small>Vous êtes connecté en tant que : ".$_SESSION["username"]."</small>";
echo '<a style="margin-left: 40px; font-size: 14px; text-decoration: none; display: inline-block; padding: 8px; background-color: #666; color: #fff;" href="logout.php">Déconnexion</a>';
echo "</h3>";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>REMASCO by MyDataBall</title>
    <!-- --------------- Shared Scripts ------------------------- -->
    <script src="Scripts/Shared/jquery-1.9.0.min.js"></script>
    <script src="Scripts/Shared/angular.min.js"></script>
    <script src="Scripts/Shared/ngDialog.min.js"></script>
    <script src="Scripts/Shared/alertify.min.js"></script>
    <script src="Scripts/Shared/lodash.js"></script>
    <script src="Scripts/Shared/vis.js"></script>
    <script src="Scripts/Shared/xml2json.min.js"></script>
    <!-- --------------- Services Scripts ----------------------- -->
    <script src="Scripts/Services/dataService.js"></script>
    <script src="Scripts/Services/select2.js"></script>
    <script src="Scripts/Services/uiService.js"></script>
    <script src="Scripts/Services/xmlService.js"></script>
    <script src="Scripts/Services/dataBall.service.js"></script>
    <!-- --------------- App Scripts ---------------------------- -->
    <script type="text/javascript" src="Scripts/App/dataBall.controller.js"></script>
    <!-- --------------- Styles --------------------------------- -->
    <link rel="stylesheet" href="Styles/vis-network.min.css">
    <link rel="stylesheet" href="Styles/index.css">
    <link rel="stylesheet" href="Styles/alertify.core.css">
    <link rel="stylesheet" href="Styles/alertify.default.css">
    <link rel="stylesheet" href="Styles/select2.css">
    <link rel="stylesheet" href="Styles/ngDialog.min.css">
    <link rel="stylesheet" href="Styles/ngDialog-theme-flat.css">
    <!-- -------------------------------------------------------- -->
</head>
<body>
<div id ="statut" class="<?php echo STATUT; ?>"></div>
<div class="container" data-ng-app="dataBallApp" data-ng-controller="dataBallController">
    <div class="leftPanel">
        <div class="detailHeader">Mes thèmes</div>
        <div style="height:calc(50% - 48px);position:relative;overflow-y:auto;font-size:0.8em">
            <div class="addTheme"  data-ng-click="addTheme()">
                <img src="Images/add-button.png" class="addTheme" style="top:0;right:0" />
            </div>
            <br />
            <br />
            <div data-ng-repeat="theme in themes" style="margin:5px 0 0 5px;">
                <div style="position:relative">
                    <span style="font-weight:bold;">- {{theme.themeName}}</span>
                    <img src="Images/corbeille.png" class="deleteButton" data-ng-click="removeTheme(theme)" />
                    <img src="Images/winrar.png" class="zipButton" data-ng-show="theme.historics && theme.historics.length > 1" />
                </div>
                <div data-ng-repeat="h in theme.historics" style="margin:5px 0 0 10px">
                    <span style="cursor:pointer" data-ng-click="reloadSearch(h)">- {{h.query}}</span>
                    <img src="Images/corbeille.png" class="deleteButton" data-ng-click="removeHistoricFromTheme(theme, h)" />
                </div>
            </div>
        </div>

        <div class="detailHeader">Historique</div>
        <div style="height:calc(50% - 48px);position:relative;overflow-y:auto;font-size:0.8em">
            <div data-ng-repeat="h in historics" style="margin:5px 0 0 5px;">
                <div style="position:relative">
                    <span style="font-weight:bold;cursor:pointer" data-ng-click="reloadSearch(h)">- {{h.query}}</span>
                    <img src="Images/corbeille.png" class="deleteButton" data-ng-click="removeHistoric(h)" />
                    <img src="Images/file_add.png" class="addHistoricButton" data-ng-click="addHistoricToTheme(h)" />
                </div>
            </div>
        </div>
    </div>
    <div class="midPanel">
        <div class="midContainer">
            <div class="queryBox">
                <input type="text" data-ng-model="query" data-ng-enter="search()" placeholder="Rechercher ..." />
                <img src="Images/search.png" title="Search" data-ng-click="search()" />
            </div>
            <div id="netContainer">
            </div>
            <div id="loadingContainer">
                <div id="loader"></div>
            </div>
        </div>
    </div>
    <div class="rightPanel">
        <div class="detailHeader">Résultats</div>
        <div class="detailContainer">
            <div data-ng-show="details.type=='group'">
                <div class="detailGroupBox" data-ng-repeat="grp in details.groups">
                    {{grp.title}}
                </div>
                <div class="detailGroupBox" data-ng-repeat="doc in details.documents">
                    {{doc.title}}
                </div>
            </div>
            <div data-ng-show="details.type=='document'">
                <table>
                    <tr>
                        <td>Titre :</td>
                    </tr>
                    <tr>
                        <td>{{details.document.title}}</td>
                    </tr>
                    <tr data-ng-show="details.document.snippet && details.document.snippet.length > 0">
                        <td>Résumé :</td>
                    </tr>
                    <tr data-ng-show="details.document.snippet && details.document.snippet.length > 0">
                        <td>{{details.document.snippet}}</td>
                    </tr>
                    <tr data-ng-show="details.document.url && details.document.url.length > 0">
                        <td>Adresse du site :</td>
                    </tr>
                    <tr data-ng-show="details.document.url && details.document.url.length > 0">
                        <!-- <td><a href="{{details.document.url}}" target="_blank">Document Url</a></td> -->
                        <td><a href="{{details.document.url}}" target="_blank">{{details.document.url}}</a></td>
                    </tr>
                    <tr data-ng-show="details.document.sources && details.document.sources.length > 0">
                        <td>Sources du résultat :</td>
                    </tr>
                    <tr data-ng-show="details.document.sources && details.document.sources.length > 0">
                        <td>
                            <div class="detailGroupBox" data-ng-repeat="s in details.document.sources">{{s}}</div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>

