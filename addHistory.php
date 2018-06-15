<?php
session_start();
$idUser = $_SESSION["idUser"];
include_once('constants.php');

$json_data = file_get_contents('php://input');
$insert_array = json_decode($json_data,true);
$insert_array["idTheme"] = null;

// Connection To DB
$con = new mysqli(SERVER,USERNAME,PASSWORD,DATABASE);
$data["insert_id"] = 0;
$data["status"] = "Erruer";
if($con->connect_error)
    $data["status"] = "Erreur de connexion à la base de données.";
else{
    // Generate Query and Execute
    $sys_date = date("Y-m-d H:i:s");
    $sql = "INSERT INTO Historic (query,idTheme,idUser,XMLfile,sysDate) VALUES ('" . $insert_array["query"] . "',NULL,'" . $idUser . "','" . $insert_array["xml"] . "','". $sys_date . "');";


    if($con->query($sql) == true){
        $data["status"] = "Enregistrement validé...";
        $data["idHistoric"] = $con->insert_id;
    }
}
$con->close();
header('content-type: application/json');
echo json_encode($data);