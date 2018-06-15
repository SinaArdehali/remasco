<?php
session_start();
$idUser = $_SESSION['idUser'];
include_once('constants.php');
require_once('model/Carrot2.php');

$json_data = file_get_contents('php://input');
$insert_array = json_decode($json_data,true);

if(STATUT == 'local'){
	$query = $insert_array["query"];
	$num = "100";
	$source = "web";
	$algorithm = "lingo";
	$format = "xml";
	$hierarchy = "max-hierarchy-depth";
	$level_hierarchie = "5";

	$processor = new Carrot2Processor();
	$job = new Carrot2Job();

	$job->setSource($source);
	$job->setQuery($query);
	$job->setAlgorithm($algorithm);
	$job->setAttribute("results", $num);
	$job->setAttribute($hierarchy, $level_hierarchie);
	$job->setAttribute("EToolsDocumentSource.language", LANG);

	try {
	    $result = $processor->cluster($job);
	}
	catch (Carrot2Exception $e) {
	    echo 'An error occurred during processing: ' . $e->getMessage();
	    exit(10);
	}

	switch ($format) {

	    case "xml":
        	displayRawXml($result->getXml());
        	exit();
	        break;

	    case "json":
	        $xml = simplexml_load_string($result->getXml());
	        $json = json_encode($xml);
	        $array = json_decode($json, true);
	        json_encode($xml, JSON_FORCE_OBJECT);
	        break;

	    default:
	        break;
	}
}
else {

	$URL = "http://".CLUSTERING_SERVER."/search.php?query=".$insert_array["query"]."&maxResult=100&fts=xml&deep=5&token=".TOKEN."";
	$data = file_get_contents($URL);
	header('Content-Type: text/html; charset=utf-8');
	echo json_encode($data);

}

function displayRawXml($xml) {

    header("Content-type: text/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo $xml;
}
?>


