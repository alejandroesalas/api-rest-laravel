<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\User;
use Illuminate\Validation\Rule;

class userController extends Controller {

    public function register(Request $request) {
        $json = $request->input('json', null);
        //limpiar datos
        $params_array = array_map('trim', json_decode($json, true));
        //validamos los datos
        if (!Empty($params_array)) {
            $validate = Validator::make($params_array, [
                        'name' => 'required|alpha',
                        'surname' => 'required|alpha',
                        'email' => 'required|email|unique:users',
                        'password' => 'required|confirmed|min:4'
            ]);
            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'No se ha podido crear el objeto',
                    'errors' => $validate->errors()
                );
            } else {
                //encriptar la contraseña
                //$params_array['password'] = bcrypt($params_array['password']);
                $params_array['password']=hash('sha256',$params_array['password']);
                $params_array['role'] ='ROLE_USER';
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
                'code' => 400,
                'message' => 'Los datos enviados no son correctos'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function login(Request $request) {
        $jwtAuth = new \App\helpers\JwtAuth();
        $json = $request->input('json', null);
        $params_array = array_map('trim', json_decode($json,true));
        if (!Empty($params_array)) {
            $validate = Validator::make($params_array, [
                        'email' => 'required|email',
                        'password' => 'required|min:4'
            ]);
            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'Campos No cumplen con los requisitos',
                    'errors' => $validate->errors()
                );
            } else {
                //encriptar la contraseña
                //$params_array['password'] = bcrypt($params_array['password']);
                $params_array['password']=hash('sha256',$params_array['password']);
                $data = $jwtAuth->signup($params_array['email'], $params_array['password']);
                if (!Empty($params_array['gettoken'])) {
                    $data = $jwtAuth->signup($params_array['email'], $params_array['password'], true);
                }
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Los datos enviados no son correctos'
            );
        }
        return response()->json($data,200);
    }

    public function update(Request $request) {
        $token = $request->header('Authorization');
        $jwtAuth = new \App\helpers\JwtAuth();
        $istokenValid = $jwtAuth->checkToken($token);
        if($istokenValid){
            $json = $request->input('json', null);
            //limpiar datos
            $params_array = array_map('trim', json_decode($json, true));
            $user = $jwtAuth->checkToken($token, true);
            //validamos los datos
            if (!Empty($params_array)) {
                $validate = Validator::make($params_array,[
                    'name' => 'required|alpha',
                    'surname' => 'required|alpha',
                    'email' => [
                        'required',
                        'email',
                        Rule::unique('users')->ignore($user->sub)
                    ]
                ]);
                if ($validate->fails()) {
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'Datos Invalidos',
                        'errors' => $validate->errors()
                    );
                } else {
                    //qitar campos que no quiero actualizar
                    unset($params_array['id']);
                    unset($params_array['role']);
                    unset($params_array['password']);
                    unset($params_array['created_at']);
                    unset($params_array['remember_token']);
                    unset($params_array['password_confirmation']);
                    //Actualizar el usuario en la base de datos
                    User::where('id',$user->sub)->update($params_array);
                    $userTarget = User::find($user->sub);
                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'El usuario  ha sido actualizado con exito',
                        'user' => $userTarget,
                        'changes' => $params_array
                    );
                }
            } else {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'Los datos enviados no son correctos'
                );
            }
        }else{
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Token Invalido'
            );
        }
        return response()->json($data,$data['code']);
    }

    public function upload(Request $request) {
        $image = $request->file('file0');
        $validate = Validator::make($request->all(), [
                    'file0' => 'required|image|mimes:jpg,jpeg,png',
        ]);
        if (!$image || $validate->fails()) {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'error al subir la imagen',
                'errors'=>$validate->errors()
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
