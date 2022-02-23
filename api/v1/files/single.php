<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset-UTF-8');

include_once '../../../core/initialize.php';

if($_SERVER["REQUEST_METHOD"] == "GET"){
    $User = new User($mysqli);
    if(isset($_GET['id']) && $_GET['id'] != null){
        $User->id = filter_var($_GET['id'],FILTER_SANITIZE_NUMBER_INT);
    }
        if($User->getSingleUser()){
            $results = $User->getSingleUser();
            if($results != null){
                http_response_code($User->code);
                unset($results['password']);
                echo json_encode($results);
            }else{
                http_response_code(404);
                echo json_encode(array("error" => "No results."));
            }
        }else{
            http_response_code($User->code);
            echo json_encode(array("error" => $User->error));
        }
}else{
    http_response_code(404);
    echo json_encode(array("error" => "Wrong request method"));
}

?>