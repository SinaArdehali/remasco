<?php
// Start the session
session_start();

include_once('constants.php');

// Create connection
$conn = new mysqli(SERVER, USERNAME, PASSWORD, DATABASE);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Check login and password in Database
if (isset($_POST['login']) and isset($_POST['password']) && !empty($_POST['login']) && !empty($_POST['password'])){


    $login = addslashes($_POST['login']);
    $password = addslashes(md5($_POST['password']));


    $sql = "SELECT idUser,userName,firstName FROM user WHERE userName='".$login."' AND password='".$password."'";
    $result = $conn->query($sql);


    if ($result->num_rows > 0) {

        $info = $result->fetch_assoc();
        $_SESSION['idUser'] =$info['idUser'];
        $_SESSION['username'] =$info['userName'];
        $_SESSION['firstname'] =$info['firstName'];
        header('Location: dashboard.php');
        exit();

    } 

    else {
        echo "<script>alert('Ooops! login ou mot de passe incorrect')</script>";
    }
}

$conn->close();

?>
<!DOCTYPE html>
<html>
<head>
<title>Page d'identification</title>
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.4/angular.min.js"></script>
<link href="Styles/style.css" rel='stylesheet' >
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>

<body>
    <div ng-app='myApp' ng-controller='textCtrl'>
        <div id='boxshadow' class="text" ng-show="true">
            <form action="index.php" method="POST">
                <h2><b>Portail de recherche MyDataBall Remasco</b></h2><br>
                <img src="Images/mydataBall.jpg" height="400" width="600" class="img-responsive">
                <div class="container" style="width:30%">        
                    <div><h2 class="title" ng-show='login'> {{ welcome + ' ' + login  }} </h2></div>
                    <div class="form-group"><label>Nom d'utilisateur : </label><input type='text' class="form-control" ng-model='login' name='login' required></div>
                    <div class="form-group"><label>Mot de passe : </label><input type='password' class="form-control" ng-model='password' name='password'></div>
                    <br>
                    <input type='submit' class="btn btn-success" ng-model='send' ng-value='send' ng-show='password && login'>
                </div>
            </form>
        </div>
    </div>

    <script src="controller/controller.js"></script>

</body>
</html>
