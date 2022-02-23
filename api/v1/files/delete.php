<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset-UTF-8');

include_once '../../../core/initialize.php';


if($_SERVER["REQUEST_METHOD"] == "DELETE"){
    $User = new User($mysqli);
    if(isset($_GET['id'])){
        $User->id = filter_var($_GET['id'],FILTER_SANITIZE_NUMBER_INT);
    }

    if($User->deleteUser()){
        http_response_code($User->code);
        echo json_encode(array("status" => "User deleted."));
    }else{
        http_response_code($User->code);
        echo json_encode(array("error" => $User->error));
    }

}else{
    http_response_code(404);
    echo json_encode(array("error" => "Wrong request method."));
}

?>