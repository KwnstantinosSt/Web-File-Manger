<?php
    class User{
        public $id;
        public $username;
        public $password;
        public $email;
        public $name;
        public $surname;
        public $role;
        public $created_at;
        public $usersBaseDir;
        public $error;
        public $code;
        private $table = 'users';
        private $conn;
        public $response;

        public function __construct($mysqli)
        {
            $this->conn = $mysqli;
            $this->usersBaseDir = realpath(dirname(__DIR__).'/../data/users/');
        }

        public function updateUserRole(){
            // function to update existing user role from "admin" to "user" or the opposite
        }

        public function intializeAdmin(){
            try{
                $query = "INSERT INTO $this->table(`username`,`password`,`email`,`name`,`surname`,`baseDir`,`role`) VALUES (?,?,?,?,?,?,?);";
                $stmt = $this->conn->prepare($query);
                $username = "admin";
                $password = "admin9814";
                $email = "admin9814@gmail.com";
                $name = "admin";
                $surname = "admin";
                $role = "admin";
                $password = password_hash($password,PASSWORD_DEFAULT);
                $baseUDir = $this->usersBaseDir.'/'.$username;
                $stmt->bind_param("sssssss",$username, $password,$email,$name,$surname,$baseUDir,$role);
                if($stmt->execute()){
                    if (!file_exists($this->usersBaseDir.'/'.$username))
                    {
                        mkdir($this->usersBaseDir.'/'.$username, 0777, true);
                    }
                    $id = $this->conn->insert_id;
                    $query = "SELECT * FROM $this->table where id = ?;";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bind_param("s",$id);
                    $stmt->execute();
                    return $stmt->get_result()->fetch_assoc();
                }else{
                    $this->error = "Something goes wrong.";
                    return false;
                }
            }catch(Exception $ex){
                 $this->code = 404;
                 $this->error = $ex->getMessage();
                 return false;
            }
        }

        public function checkforAdmin(){
            try{
                $query = "SELECT * FROM $this->table where role = ?;";
                $stmt = $this->conn->prepare($query);
                $role = "admin";
                $stmt->bind_param("s",$role);
                if($stmt->execute()){
                    $result = $stmt->get_result();
                    return $result;
                }else{
                    $this->error = "Something goes wrong.";
                    return false;
                }
            }catch(Exception $ex){
                 $this->code = 404;
                 $this->error = $ex->getMessage();
                 return false;
            }
        }

        public function register(){
            try{
                $headers = apache_request_headers();
                if(isset($headers['Authorization'])){
                    $token = $headers['Authorization'];
                    $Authorization = new Authorization();
                    if($Authorization->authorize_valid_token($token)){
                        if($this->checkUserToken($token)){
                            $user = $this->checkUserToken($token);
                            if($user['role'] == 'user'){
                                $this->code = 401;
                                $this->error = "Permission Denied.";
                                return false;
                            }
                            if($user['role'] == 'admin')
                            {
                                $this->code = 200;
                                // do functionality here
                                if($this->username && $this->password && $this->email && $this->role){
                                    $query = "INSERT INTO $this->table(`username`,`password`,`email`,`name`,`surname`,`baseDir`,`role`) VALUES (?,?,?,?,?,?,?);";
                                    $stmt = $this->conn->prepare($query);
                                    $this->password = password_hash($this->password,PASSWORD_DEFAULT);
                                    $baseUDir = $this->usersBaseDir.'/'.$this->username;
                                    $stmt->bind_param("sssssss",$this->username, $this->password,$this->email,$this->name,$this->surname,$baseUDir, $this->role);
                                    try{
                                        if($stmt->execute()){
                                            $this->created_at = date('Y-m-d',time());
                                            $this->id = $this->conn->insert_id;
                                            if (!file_exists($this->usersBaseDir.'/'.$this->username))
                                            {
                                                mkdir($this->usersBaseDir.'/'.$this->username, 0777, true);
                                            }
                                            return true;
                                    }else{
                                        if($stmt->errno == 1062){
                                            $this->error = "This email already exists.";
                                            return false;
                                        }else{
                                            $this->error = "Something goes wrong.";
                                            return false;
                                        }
                                    }
                                    } catch( Exception $e){
                                        $this->error = $e->getMessage();
                                        return false;
                                    }
                    
                                }else{
                                    $this->error = "User not created. Check the fields.";
                                    return false;
                                }
                            }
                        }else{
                            $this->code = 404;
                            $this->error = "Something goes wrong.";
                            return false;
                        }
                       
                    }else{
                        $this->code = 401;
                        $this->error = "Need a valid token.";
                        return false;
                    }
                }else{
                    $this->code = 404;
                    $this->error = "Need authorization token.";
                    return false;
                }
            }catch(Exception $ex){
                 $this->code = 404;
                 $this->error = $ex->getMessage();
                 return false;
            }
        }

        public function login(){
            try{
                if ($this->email != null && $this->password != null){
                    $query = "SELECT * FROM $this->table WHERE email = ? LIMIT 1;";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bind_param("s",$this->email);
                    $stmt->execute();
    
                    $result = $stmt->get_result()->fetch_assoc();
                    if($result != null){
                        if(password_verify($this->password,$result['password'])){
                            $Authorization = new Authorization();
                            if($Authorization->auth($result)){
                                $userToken = $Authorization->auth($result);
    
                                $query = "UPDATE $this->table SET token = ? , tokenExp = ? WHERE id = ?;";
                                $stmt = $this->conn->prepare($query);
                                $stmt->bind_param("sii",$userToken['token'],$userToken['expires'],$userToken['userId']);
                                $stmt->execute();
    
                                if($stmt->errno == null){
                                    return $userToken;
                                }else{
                                    $this->error = "Authentication Error, Please try again.";
                                    $this->code = 401;
                                    return false;
                                }
                            }else{
                                $this->error = "Authentication Error.";
                                $this->code = 401;
                                return false;
                            }
                        }else{
                            $this->error = "Wrong password for this user.";
                            $this->code = 401;
                            return false;
                        }
                    }else{
                        $this->error = "No user found with this email.";
                        $this->code = 401;
                        return false;
                    }
                  
                }else{
                    $this->error = "Wrong Fields or empty.";
                    $this->code = 401;
                    return false;
                }
                
            }catch(Exception $ex){
                 $this->code = 404;
                 $this->error = $ex->getMessage();
                 return false;
            }

        }

        public function getSingleUser(){
            try{
                $headers = apache_request_headers();
                if(isset($headers['Authorization'])){
                    $token = $headers['Authorization'];
                    $Authorization = new Authorization();
                    if($Authorization->authorize_valid_token($token)){
                        if($this->checkUserToken($token)){
                            $user = $this->checkUserToken($token);
                            if($user['role'] == 'user'){
                                $this->code = 200;
                                return $user;
                            }
                            if($user['role'] == 'admin'){
                                if($this->id == null)
                                {
                                    $this->code = 200;
                                    return $user;
                                }
                                $query = "SELECT * FROM $this->table WHERE id = ? LIMIT 1;";
                                $stmt = $this->conn->prepare($query);
                                $stmt->bind_param("i",$this->id);
                                $stmt->execute();
                                $result = $stmt->get_result()->fetch_assoc();
                                if($result == null){
                                    $this->code = 404;
                                    $this->error = "No user found with this id.";
                                    return false;
                                }
                                $this->code = 200;
                                return $result;
                            }
                        }else{
                            $this->code = 404;
                            $this->error = "Something goes wrong.";
                            return false;
                        }
                    }else{
                        $this->code = 401;
                        $this->error = "Need a valid token.";
                        return false;
                    }
                }else{
                    $this->code = 401;
                    $this->error = "Need a authorization token.";
                    return false;
                }
                
            }catch(Exception $ex){
                 $this->code = 404;
                 $this->error = $ex->getMessage();
                 return false;
            }
        }

        public function getAllUsers(){
            try{
                $headers = apache_request_headers();
                if(isset($headers['Authorization'])){
                    $token = $headers['Authorization'];
                    $Authorization = new Authorization();
                    if($Authorization->authorize_valid_token($token)){
                        if($this->checkUserToken($token)){
                            $user = $this->checkUserToken($token);
                            if($user['role'] == 'user'){
                                $this->code = 401;
                                $this->error = "Permission Denied.";
                                return false;
                            }
                            if($user['role'] == 'admin'){
                                $this->code = 200;
                                $query = "SELECT * FROM $this->table;";
                                $stmt = $this->conn->prepare($query);
                                $stmt->execute();
                                return $stmt->get_result();
                            }
                        }else{
                            $this->code = 404;
                            $this->error = "Something goes wrong.";
                            return false;
                        }
                       
                    }else{
                        $this->code = 401;
                        $this->error = "Need a valid token.";
                        return false;
                    }
                }else{
                    $this->code = 404;
                    $this->error = "Need authorization token.";
                    return false;
                }
                
            }catch(Exception $ex){
                 $this->code = 404;
                 $this->error = $ex->getMessage();
                 return false;
            }
        }

        public function deleteUser(){
            try{
                $headers = apache_request_headers();
                if(isset($headers['Authorization'])){
                    $token = $headers['Authorization'];
                    $Authorization = new Authorization();
                    if($Authorization->authorize_valid_token($token)){
                        if($this->checkUserToken($token)){
                            $user = $this->checkUserToken($token);
                            if($user['role'] == 'user'){
                                $this->code = 401;
                                $this->error = "Denied action for user.";
                                return false;
                            }
                            if($user['role'] == 'admin'){
                                if($this->id == null){$this->code = 404;$this->error = "Id must not be null."; return false;}
                                $query = "DELETE FROM $this->table WHERE id = ?;";
                                $stmt = $this->conn->prepare($query);
                                $stmt->bind_param("i",$this->id);
                                $stmt->execute();
                                if($stmt->errno == null && $stmt->affected_rows != null){
                                    $this->code = 200;
                                    return true;
                                }else{
                                    $this->code = 404;
                                    $this->error = "User does not deleted or does not exist.";
                                    return false;
                                }     
                            }
                        }else{
                            $this->code = 404;
                            $this->error = "Something goes wrong.";
                            return false;
                        }
                    }else{
                        $this->error = "Unauthorized or not active token.";
                        $this->code = 401;
                        return false;
                    }
                }else{$this->code = 401; $this->error = "Must send a token to authorize."; return false;}
                
            }catch(Exception $ex){
                 $this->code = 404;
                 $this->error = $ex->getMessage();
                 return false;
            }
        }

        public function updateUser(){
            try{
                $headers = apache_request_headers();
                if(isset($headers['Authorization'])){
                    $token = $headers['Authorization'];
                    $Authorization = new Authorization();
                    if($Authorization->authorize_valid_token($token)){
                        if($this->checkUserToken($token)){
                            $user = $this->checkUserToken($token);
                            if($user['role'] == 'user' || $user['role'] == 'admin'){
                                if($user['id'] == null){$this->code = 404;$this->error = "Id must not be null."; return false;}
                                    if($this->username == null && $this->password == null && $this->email == null && $this->name  == null && $this->surname  == null){
                                        $this->code = 404;
                                        $this->error = "Must be update at least one value.";
                                        return false;
                                    }
    
                                    if($this->username){
                                        $query = "UPDATE $this->table SET username = ? WHERE id = ?";
                                        $stmt = $this->conn->prepare($query);
                                        $stmt->bind_param("si", $this->username,$user['id']);
                
                                        if($stmt->execute()){
                                            if (file_exists($this->usersBaseDir . '/' . $user['username'])){
                                                $this->code = 200;
                                                $oldName = $this->usersBaseDir . '/' . $user['username'];
                                                $newName = $this->usersBaseDir . '/' . $this->username;
                                                rename($oldName, $newName);
                                                $query = "UPDATE $this->table SET baseDir = ? WHERE id = ?";
                                                $stmt = $this->conn->prepare($query);
                                                $stmt->bind_param("si",$newName,$user['id']);
                                                $stmt->execute();
                                                //$this->response =  $user['username'];
                                            }
                                        }else{
                                            $this->error = "Something goes wrong. Username not updated.";
                                            $this->code = 404;
                                            return false;
                                        }
                                    }
                                    if($this->password){
                                        $newPassword = password_hash($this->password,PASSWORD_DEFAULT);
                                        $query = "UPDATE $this->table SET `password` = ? WHERE id = ?";
                                        $stmt = $this->conn->prepare($query);
                                        $stmt->bind_param("si", $newPassword,$user['id']);
                
                                        if($stmt->execute()){
                                            $this->code = 200;
                                        }else{
                                            $this->error = "Something goes wrong. Password not updated.";
                                            $this->code = 404;
                                            return false;
                                        }
                                    }
                                    if($this->email){
                                        $query = "UPDATE $this->table SET email = ? WHERE id = ?";
                                        $stmt = $this->conn->prepare($query);
                                        $stmt->bind_param("si", $this->email,$user['id']);
                
                                        if($stmt->execute()){
                                            $this->code = 200;
                                        }else{
                                            $this->error = "Something goes wrong or Email exists from another user. Email not updated.";
                                            $this->code = 404;
                                            return false;
                                        }
                                    }
                                    if($this->name){
                                        $query = "UPDATE $this->table SET `name` = ? WHERE id = ?";
                                        $stmt = $this->conn->prepare($query);
                                        $stmt->bind_param("si", $this->name,$user['id']);
                
                                        if($stmt->execute()){
                                            $this->code = 200;
                                        }else{
                                            $this->error = "Something goes wrong. Name not updated.";
                                            $this->code = 404;
                                            return false;
                                        }
                                    }
                                    if($this->surname){
                                        $query = "UPDATE $this->table SET surname = ? WHERE id = ?";
                                        $stmt = $this->conn->prepare($query);
                                        $stmt->bind_param("si", $this->surname,$user['id']);
                
                                        if($stmt->execute()){
                                            $this->code = 200;
                                        }else{
                                            $this->error = "Something goes wrong. Surname not updated.";
                                            $this->code = 404;
                                            return false;
                                        }
                                    }
                                    if($this->code == 200 && $stmt->errno == null){
                                        return true;
                                    }else{
                                        $this->code = 404;
                                        $this->error = "Something goes wrong or records are the same.";
                                        return false;
                                    }
                            }
                        }else{
                            $this->code = 404;
                            $this->error = "Something goes wrong.";
                            return false;
                        }
                    }else{
                        $this->error = "Unauthorized or not active token.";
                        $this->code = 401;
                        return false;
                    }
                }else{$this->code = 401; $this->error = "Must send a token to authorize."; return false;}
            }catch(Exception $ex){
                 $this->code = 404;
                 $this->error = $ex->getMessage();
                 return false;
            }
        }


        public function checkUserToken($token){
            try{
                $query = "SELECT * FROM $this->table WHERE token = ? LIMIT 1";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("s", $token);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                if($result != null && $stmt->errno == null){
                    return $result;
                }else{
                    return false;
                }  
            }catch(Exception $ex){
                 $this->code = 404;
                 $this->error = $ex->getMessage();
                 return false;
            }
        }



    }
?>