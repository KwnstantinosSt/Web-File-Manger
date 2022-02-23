<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset-UTF-8');

include_once '../../../core/initialize.php';

if($_SERVER["REQUEST_METHOD"] == "GET"){
        $user = new User($mysqli);
        if($user->checkforAdmin()){
            $result = $user->checkforAdmin();
            if($result->num_rows == 0){
                http_response_code(200);
                $admin = $user->intializeAdmin();
                echo json_encode($admin);
            }else{
                 http_response_code(404);
                 echo json_encode(array("error" => "Initialize failed, admin already exists!"));
                 die();
            }
        }else{
            http_response_code($user->code);
            echo json_encode(array("error" => $user->error));
        }
}else{
    http_response_code(404);
    echo json_encode(array("error" => "Wrong request method"));
}

?>