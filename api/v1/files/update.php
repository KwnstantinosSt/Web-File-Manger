<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset-UTF-8');

include_once '../../../core/initialize.php';

if($_SERVER["REQUEST_METHOD"] == "PUT"){
    if(file_get_contents("php://input") != null){
        $data = json_decode(file_get_contents("php://input"));
        $user = new User($mysqli);
        if(isset($data->username)){
            $user->username = filter_var($data->username,FILTER_SANITIZE_STRING);
        }
        if(isset($data->password)){
            $user->password = filter_var($data->password,FILTER_SANITIZE_STRING);
        }
        if(isset($data->email)){
            $user->email = filter_var($data->email,FILTER_SANITIZE_EMAIL);
        }
        if(isset($data->name)){
            $user->name = filter_var($data->name,FILTER_SANITIZE_STRING);
        }
        if(isset($data->surname)){
            $user->surname = filter_var($data->surname,FILTER_SANITIZE_STRING);
        }
        if($user->updateUser()){
            http_response_code($user->code);
            echo json_encode(array("status" => 'User information updated.'));
        }else{
            http_response_code($user->code);
            echo json_encode(array("error" => $user->error));
        }
    }else{
        http_response_code(404);
        echo json_encode(array("error" => "No Data"));
    }
   
}else{
    http_response_code(404);
    echo json_encode(array("error" => "Wrong request method"));
}

?>