<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset-UTF-8');

include_once '../../../core/initialize.php';

if($_SERVER["REQUEST_METHOD"] == "GET"){
    $User = new User($mysqli);
    if($User->getAllUsers()){
        $results = $User->getAllUsers();
        if($results->num_rows > 0){
            http_response_code($User->code);
            $arr = array();
            while($row = $results->fetch_assoc()){
                extract($row);
                $item = array(
                    'id' => $id,
                    'username' => $username,
                    'email' => $email,
                    'name' => $name,
                    'surname' => $surname,
                    'role' => $role,
                    'baseDir' => $baseDir,
                    'created_at' => $created_at,
                );
                array_push($arr,$item);
            }
            echo json_encode($arr);
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