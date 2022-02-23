<?php
try{
    $host='localhost';
    $db = 'webfilemanager';
    $user='root';
    $pass='';


    if(gethostname()=='users.iee.ihu.gr') {
        $mysqli = new mysqli($host, $user, $pass, $db,null,'/home/student/it/2014/it144346/mysql/run/mysql.sock');
    } else {
        $mysqli = new mysqli($host, $user, $pass, $db);
    }

    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . 
        $mysqli->connect_errno . ") " . $mysqli->connect_error;
    }
}catch(Exception $e){
    http_response_code(404);
    echo json_encode(array("error:" => $e->getMessage()));
    die();
}
  

?>
