<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset-UTF-8');

include_once '../../../core/initialize.php';

/* if($_SERVER["REQUEST_METHOD"] == "GET"){
    $Authorization = new Authorization();
    if($Authorization->auth()){
        http_response_code(200);
        echo json_encode($Authorization->auth());
    }else{
        http_response_code(404);
        echo json_encode("Authentication Error");
    }
}else{
    echo json_encode("Wrong request method");
} */

http_response_code(404);
echo json_encode(array("Error" => "Not Found."));

?>