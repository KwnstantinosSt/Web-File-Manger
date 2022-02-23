<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset-UTF-8');

include_once '../../../core/initialize.php';

if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(file_get_contents("php://input") != null){
        $data = json_decode(file_get_contents("php://input"));
        $user = new User($mysqli);

        if(isset($data->password)){
            $user->password = filter_var($data->password,FILTER_SANITIZE_STRING);
        }
        if(isset($data->email)){
            $user->email = filter_var($data->email,FILTER_SANITIZE_EMAIL);
        }

        if($user->login()){
            http_response_code(200);
            $result = $user->login();
            echo json_encode($result);
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