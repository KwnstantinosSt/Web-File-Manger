<?php
require_once(realpath(dirname(__FILE__) . '/../../api/v1/authorization/jwt/vendor/autoload.php'));

use Firebase\JWT\JWT;

    class Authorization{
        private $key = 'stoy9814!';

        public function __construct()
        {
            
        }

        public function auth($user){
            if($user == null || !isset($user['id'])){return false;}
            $iat = time();
            $exp = $iat + 600;
            $payload = array(
                'iss' => 'https://users.iee.ihu.gr/~it144346/jwt/api/',
                'aud' => 'https://users.iee.ihu.gr/~it144346/jwt/',
                'iat' => $iat,
                'exp' => $exp
            );
            $jwt = JWT::encode($payload,$this->key,'HS512');
            return array(
                'token' => $jwt,
                'expires' => $exp,
                'userId' => $user['id'],
                'userName' => $user['username'],
                'userRole' => $user['role']

            );
        }

        public function authorize_valid_token($token){
            if($token){
                try{
                    $token = JWT::decode($token,$this->key,array('HS512'));
                    return true;
                }catch(Exception $e){
                    return false;
                }
            }
        }
        
        
    }
?>