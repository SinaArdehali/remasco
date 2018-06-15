<?php
session_start();
$idUser = $_SESSION["idUser"];
include_once('constants.php');
// Connection To DB
$con = new mysqli(SERVER,USERNAME,PASSWORD,DATABASE);
$themes = array();
$theme = array();
$historics = array();
if($con->connect_error)
    $data["status"] = "connection error";
else{
    $query = "SELECT * FROM Theme WHERE idUser = $idUser";
    $result = $con->query($query);
    while($row = $result->fetch_assoc())
    {
        $themes[$row['idTheme']]= array(
            "idTheme"=>$row['idTheme'],
            "themeName"=>$row['themeName'],
            "idUser"=>$row['idUser'],
            "sysDate"=>$row['sysDate'],
            );
        $query1 = "SELECT * FROM Historic WHERE idTheme = '".$row["idTheme"]."'";
        $result1 = $con->query($query1);

        if($result1->num_rows>0){
            while($row1 = $result1->fetch_assoc())
            {
                $themes[$row['idTheme']]["historics"][] = array(
                    "idHistoric"=>$row1['idHistoric'],
                    "query"=>$row1['query'],
                    "idTheme"=>$row1['idTheme'],
                    "idUser"=>$row1['idUser'],
                    "XMLfile"=>$row1['XMLfile'],
                    "sysDate"=>$row1['sysDate']
                );

            }
        }

        $theme[] = $themes[$row['idTheme']];

    }
    $data["themes"] = $theme;



    $sql = "SELECT * FROM Historic WHERE idUser = $idUser";
    $result = $con->query($sql);
    if($result->num_rows>0)
        while($row = $result->fetch_assoc()){
            $historics[] = $row;
        }
    $data["historics"] = $historics;
}
$con->close();
header('content-type: application/json');
echo json_encode($data);