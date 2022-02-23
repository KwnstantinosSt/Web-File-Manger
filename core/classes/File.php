<?php
    class File{
        public $id;
        public $name;
        public $type;
        public $format;
        public $size;
        public $created_at;
        public $path;
        public $base;
        public $error;
        public $code;
        private $table = 'files';
        private $conn;

        public function __construct($mysqli)
        {
            $this->conn = $mysqli;
            $this->base = realpath(dirname(__DIR__).'/../data/users/');
        }

        function folderSize ($dir)
        {
            $size = 0;
            foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
                $size += is_file($each) ? filesize($each) : $this->folderSize($each);
            }
            return $size;
        }

        function navigation($path)
        {
            try{
                $headers = apache_request_headers();
                if(isset($headers['Authorization'])){
                    $token = $headers['Authorization'];
                    $Authorization = new Authorization();
                    if($Authorization->authorize_valid_token($token)){
                        $user = new User($this->conn);
                        if($user->checkUserToken($token)){
                            $user = $user->checkUserToken($token);
                            if($user['role'] == 'user' || $user['role'] == 'admin'){
                                // Functionallity of this function after token authentication
    
                                $sub = glob($this->base.'/'.$user['username'].'/'.$path."/*");
                                //echo json_encode($path);
                                $directories = array();
                                $files = array();
                                $fnd = array();
                                //$fnd['data'] = array();
                                if(!$sub){
                                    $this->code = 404;
                                    $this->error= "No data found!";
                                    return false;
                                }
                                for($i=0;$i<sizeof($sub);$i++)
                                {
                                    if(is_dir($sub[$i])){
                                        $directories[$i] = $sub[$i]; 
                                        $fol = explode("/",$directories[$i]);
                                        $len = sizeof($fol)-1;
                                        $items = array(
                                            "id" => $i,
                                            "filename" => $fol[$len],
                                            "type" => "folder",
                                            "format" => "folder",
                                            "BaseDir" => substr($directories[$i],strpos($directories[$i],$user['username'])),
                                            "size" => number_format($this->folderSize( $directories[$i])*0.00000095367432,1) == 0 ? round(number_format($this->folderSize($directories[$i])*0.00000095367432,10) * 1024,2) . " KB" :number_format($this->folderSize( $directories[$i])*0.00000095367432,1) . " MB",
                                            "lastDate" => trim(date("F d Y H:i:s.",filemtime( $directories[$i])),"."),
                                            "owner" => $user['username'],
                                            "icon" => "./assets/folder.png",
                                        );
                                        array_push($fnd,$items);
                                    }elseif(is_file($sub[$i]))
                                    {
                                        $files[$i] = $sub[$i];
                                        $exp = explode("/",$files[$i]);
                                        $len = sizeof($exp)-1;
                                        $dot = strpos($exp[$len],".");
                                        $items = array(
                                            "id" => $i,
                                            "filename" => substr($exp[$len],0,$dot),
                                            "type" => "file",
                                            "format" => trim(substr($exp[$len],$dot),"."),
                                            "BaseDir" => substr($files[$i],strpos($files[$i],"data")),
                                            "size" => number_format(filesize($files[$i])*0.00000095367432,1) == 0 ? round(number_format(filesize($files[$i])*0.00000095367432,10) * 1024,2) . " KB" : number_format(filesize($files[$i])*0.00000095367432,1) . " MB", 
                                            "lastDate" =>trim(date("F d Y H:i:s.", filemtime( $files[$i])),"."),
                                            "owner" => $user['username'],
                                            "icon" => "./assets/file.png",
                                        );
                                        array_push($fnd,$items);
                                    }
                                }
                                //array_push($fnd,array("results" => sizeof($fnd)));
                                return $fnd;
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

       function makefolder($name,$path,$base)
       {
           try{
               if($name == null)
               {
                   echo json_encode("Not Created."); 
                   die();
               }
       
               if($path == "root")
               {
                   if(!file_exists($base."/$name"))
                   {
                       mkdir($base."/$name");
                       $p = $base."/$name";
                       $this->refresh($p);
                   }else{
                       echo json_encode("Folder exists."); 
                       die();
                   }
                   
               }else{
                   if(!file_exists($base."/$path"."$name"))
                   {
                       mkdir($base."/$path"."$name");
                       $p = $base."/$path"."$name";
                       $this->refresh($p);
                   }else{
                       echo json_encode("Folder exists."); 
                       die();
                   }
               }
               
           }catch(Exception $ex){
                 $this->code = 404;
                 $this->error = $ex->getMessage();
                 return false;
            }
        }
        
        
        function refresh($p)
        {
            try{
                $s = "";
                $param = $p;
                $n = explode("/",$param);
                for ($i=0;$i<(sizeof($n)-1);$i++){
                    $s .= $n[$i] ."/";
                }
                $sub = glob($s."/*");
                $directories = array();
                $files = array();
                $fnd = array();
                for($i=0;$i<sizeof($sub);$i++){
                    if(is_dir($sub[$i]))
                    {
                        $directories[$i] = $sub[$i]; 
                        $fol = explode("/",$directories[$i]);
                        $len = sizeof($fol)-1;
                        $items = array(
                            "filename" => $fol[$len],
                            "type" => "folder",
                            "format" => null,
                            "size" => number_format($this->folderSize( $directories[$i])*0.00000095367432,2),
                            "lastDate" =>trim(date("F d Y H:i:s.", filemtime( $directories[$i])),".")
                        );
                        array_push($fnd,$items);
                    }elseif(is_file($sub[$i]))
                    {
                        $files[$i] = $sub[$i];
                        $exp = explode("/",$files[$i]);
                        $len = sizeof($exp)-1;
                        $dot = strpos($exp[$len],".");
                        $items = array(
                            "filename" => substr($exp[$len],0,$dot),
                            "type" => "file",
                            "format" => trim(substr($exp[$len],$dot),"."),
                            "BaseDir" => $files[$i], "size" => number_format(filesize($files[$i])*0.00000095367432,2),
                            "lastDate" =>trim(date("F d Y H:i:s.", filemtime( $files[$i])),".")
                        );
                        array_push($fnd,$items);
    
                    }
                }
                echo json_encode($fnd); 
            }catch(Exception $ex){
                 $this->code = 404;
                 $this->error = $ex->getMessage();
                 return false;
            }
        }


    }
?>