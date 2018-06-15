<?php
include_once('constants.php');
$json_data = file_get_contents('php://input');
$input_array = json_decode($json_data,true);

// Connection To DB
$con = new mysqli(SERVER,USERNAME,PASSWORD,DATABASE);
$data["status"] = "error";
if($con->connect_error)
    $data["status"] = "connection error";
else{
    $sql = "DELETE FROM `Theme` WHERE idTheme = " . $input_array["idTheme"];

    if($con->query($sql) == true)
        $data["status"] = "Deleted successfully...";
}
$con->close();
header('content-type: application/json');
echo json_encode($data);