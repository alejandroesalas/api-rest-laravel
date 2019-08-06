<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;

class JwtAuth {

    private $key;

    public function __construct() {
        $this->key = 'MBhhUYiPUoYZpesAWaXfYRyzT2LUukI1YeWQF2QODXnPKHMqh-N8p3--ofN6ObHy4av-Gc2fc42mZaK8C-xHOzSpds0H2q0Fr0ncHDLFN1J2COKksB8pXQnfJ2ZYMhkU2cjC1QmIU_6VxENuX_p24FwW3nU-r_WPjrhIvT5EfolmPqLSkI7jJgKlH6mQ8Yxbn5DHtWcHhFIUiHXoIxRKvSq9iKBH1j06Y8l3-1BxgD31bKsdg709vMRiIjQ-9X_zj7HxafL2Bjdy14NFMsE6PN_CszH5YJVg7s36rGBMO3Jx_cRvtpY8gPP25RSFmZ16ga9QhJfqkcp859kOFL7uSg';
    }

    //1. buscar si existe el usuario con sus credenciales
    //2. comrpobar si las credenciales son correctas.
    //3. Generar el token con los datos del usuario identificado.
    //4.devolver el token en funcion de un parametro
    function signup($email, $password, $getToken = null) {
        $user = User::where([
                    'email' => $email,
                    'password' => $password
                ])->first();
        //$signup = false;
        if (is_object($user)) {
            // $signup = true;
            $token = array(
                'sub' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'surname' => $user->surname,
                'description'=>$user->description,
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60)
            );
            $jwt = JWT::encode($token, $this->key);
            if (is_null($getToken)) {
                $data = $jwt;
            } else {
                $decode = JWT::decode($jwt, $this->key,array("HS256"));
                $data = $decode;
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Correo y/o Contraseña son incorrectos'
            );
        }
        return $data;
    }

    function checkToken($token, $getIdentity = false) {
        $auth = false;
        try {
            $decodeToken = JWT::decode($token, $this->key,array("HS256"));
            if (!Empty($decodeToken) && is_object($decodeToken) && isset($decodeToken->sub)) {
                $auth = true;
            }
        } catch (Exception $ex) {
            $auth = false;
        }
        if ($getIdentity) {
            return $decodeToken;
        }
        return $auth;
    }

}
