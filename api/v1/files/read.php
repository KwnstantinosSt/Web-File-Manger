<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset-UTF-8');

include_once '../../../core/initialize.php';

if($_SERVER["REQUEST_METHOD"] == "GET"){
    $File = new File($mysqli);
    if(!isset($_GET["path"])){
        http_response_code(404);
        echo json_encode(array("error" => "Path and Authorized token is required!"));
        die();
    }
    $results = $File->navigation($_GET["path"]);
    if($results){
        http_response_code($File->code);
        echo json_encode($results);
    }else{
        http_response_code($File->code);
        echo json_encode(array("error" => $File->error));
    }
}else{
    http_response_code(404);
    echo json_encode(array("error" => "Wrong request method"));
}

?>