<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\User;

class userController extends Controller {

    public function register(Request $request) {

        $json = $request->input('json', null);
        //limpiar datos
        $params_array = array_map('trim', json_decode($json, true));
        //validamos los datos
        if (!isEmpty($params_array)) {
            $validate = Validator::make($params_array, [
                        'name' => 'required|alpha',
                        'surname' => 'required|alpha',
                        'email' => 'required|email|unique:users',
                        'password' => 'required|confirmed|min:6'
            ]);
            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'No se ha podido crear el objeto',
                    'errors' => $validate->errors()
                );
            } else {
                //encriptar la contraseña
                $params_array['password'] = bcrypt($params_array['password']);
                $params_array['role'] = 'ROLE_USER';
                $nuevoUsuario = \App\User::create($params_array);
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El usuario se ha creado con exito',
                    'data' => $nuevoUsuario
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 200,
                'message' => 'Los datos enviados no son correctos'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function login(Request $request) {
        $jwtAuth = new \App\helpers\JwtAuth();
        $json = $request->input('json', null);
        $params_array = array_map('trim', json_decode($json, true));
        if (!isEmpty($params_array)) {
            $validate = Validator::make($params_array, [
                        'email' => 'required|email',
                        'password' => 'required|min:6'
            ]);
            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'No se ha podido Identificar',
                    'errors' => $validate->errors()
                );
            } else {
                //encriptar la contraseña
                $params_array['password'] = bcrypt($params_array['password']);
                $data = $jwtAuth->signup($params_array['email'], $params_array['password']);
                if (!isEmpty($params_array['gettoken'])) {
                    $singup = $jwtAuth->signup($params_array['email'], $params_array['password'], true);
                }
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 200,
                'message' => 'Los datos enviados no son correctos'
            );
        }
        return response()->json($data, 200);
    }

    public function update(Request $request) {
        $token = $request->header('Authorization');
        $jwtAuth = new \App\helpers\JwtAuth();
        //actualizar usuario
        $json = $request->input('json', null);
        //limpiar datos
        $params_array = array_map('trim', json_decode($json, true));
        $user = $jwtAuth->checkToken($token, true);
        //validamos los datos
        if (!isEmpty($params_array)) {
            $validate = Validator::make($params_array, [
                        'name' => 'required|alpha',
                        'surname' => 'required|alpha',
                        'email' => 'required|email|unique:users,' . $user->sub
            ]);
            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'No se ha podido crear el objeto',
                    'errors' => $validate->errors()
                );
            } else {
                //qitar campos que no quiero actualizar
                unset($params_array['id']);
                unset($params_array['role']);
                unset($params_array['password']);
                unset($params_array['created_at']);
                unset($params_array['rembember_token']);
                //Actualizar el usuario en la base de datos
                $userTarget = User::where('id', $user->id)->update($params_array);
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El usuario  ha sido actualizado con exito',
                    'data' => $userTarget
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 200,
                'message' => 'Los datos enviados no son correctos'
            );
        }
        return response()->json($data);
    }

    public function upload(Request $request) {
        $image = $request->file('file0');
        $validate = Validator::make($request->all(), [
                    'file0' => 'required|image|mimes:jpg,jpeg,png',
        ]);
        var_dump($image);
        if (!$image || $validate->fails()) {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'error al subir la imagen'
            );
        } else {
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));
            $data = array(
                'code' => 200,
                'status' => 'succes',
                'image' => $image_name
            );
        }
        return response()->json($data, $data['code']);
    }

    public function getImage($fileName) {
        if((\Storage::disk('users')->exists($fileName))){
            $file = \Storage::disk('users')->get($fileName);
            return new Response($file,200);
        }else{
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'la imagen no existe'
            );
            return  response()->json($data, $data['code']);
        }

    }
    public function details($id){
        $user = User::find($id);
        if(is_object($user)){
            $data = array(
                'code' => 200,
                'status' => 'succes',
                'user'=>$user
            );
        }else{
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'el usuario no existe'
            );
        }
        return response()->json($data,$data['code']);
    }
}
