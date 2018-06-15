<?php
session_start();
$idUser = $_SESSION['idUser'];
include_once('constants.php');
$json_data = file_get_contents('php://input');
$insert_array = json_decode($json_data,true);

// Connection To DB
$con = new mysqli(SERVER,USERNAME,PASSWORD,DATABASE);
$data["insert_id"] = 0;
$data["status"] = "Erreur";
if($con->connect_error)
    $data["status"] = "Erreur de connexion à la base de données.";
else{
    // Generate Query and Execute
    $sys_date = date("Y-m-d H:i:s");
    $sql = "INSERT INTO `Theme` (themeName,idUser,sysDate) VALUES ('" . $insert_array["themeName"] . "','" . $idUser . "','". $sys_date . "');";

    if($con->query($sql) == true){
        $data["status"] = "Enregistrement validé...";
        $data["idTheme"] = $con->insert_id;
    }
}
$con->close();
header('content-type: application/json');
echo json_encode($data);