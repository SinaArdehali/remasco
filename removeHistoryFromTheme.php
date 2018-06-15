<?php
include_once('constants.php');

$json_data = file_get_contents('php://input');
$update_array = json_decode($json_data,true);

// Connection To DB
$con = new mysqli(SERVER,USERNAME,PASSWORD,DATABASE);
$data["status"] = "error";
if($con->connect_error)
    $data["status"] = "connection error";
else{
    // Generate Query and Execute
    $sql = "UPDATE Historic SET idTheme = NULL WHERE idHistoric = ".$update_array['idHistoric'];


    if($con->query($sql) == true){
        $data["status"] = "inserted successfully...";
    }
}
$con->close();
header('content-type: application/json');
echo json_encode($data);