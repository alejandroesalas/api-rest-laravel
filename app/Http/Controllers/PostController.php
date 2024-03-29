<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Helpers\JwtAuth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function __construct() {
        $this->middleware('api.auth', ['except' => ['index'
            ,'show',
            'getImage',
            'getPostsByUser',
            'getPostByCategory']]);
    }
     public function index() {
        $posts = Post::all()->load('category');
        return response()->json([
                    'code' => 200,
                    'status' => 'succes',
                    'categories' => $posts
                        ]
        );
    }

    public function show($id) {
        $post = Post::find($id)->load('category');
        if (is_object($post)) {
            $data = array([
                    'code' => 200,
                    'status' => 'succes',
                    'post' => $post
            ]);
        } else {
            $data = array([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'La entrada no existe'
            ]);
        }
        return response()->json($data, $data['code']);
    }

    public function store(Request $request) {
       $json = $request->input('json', null);
       $params_array = array_map('trim', json_decode($json, true));
        if (!Empty($params_array)) {
            $user = $this->getidentity( $request->header('Authorization',null));

            $validate = Validator::make($params_array, [
                        'title' => 'required',
                        'content'=>'required',
                        'category_id'=>'required',
                        'image'=>'required'
            ]);
            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El post no es valido',
                    'errors' => $validate->errors()
                );
            }else{
                $post = new  Post();
                $post->user_id =$user->sub;
                $post->category_id = $params_array['category_id'];
                $post->title = $params_array['title'];
                $post->content = $params_array['content'];
                $post->image = $params_array['image'];
                $post->save();
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'post'=>$post);
            }
        }else{
            $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'post no valido',
                );
        }
        return response()->json($data,$data['code']);
    }

    public function update(Request $request, $id) {
         $json = $request->input('json', null);
        $params_array = array_map('trim', json_decode($json, true));
        if (!Empty($params_array)) {
            $validate = Validator::make($params_array, [
                'title' => 'required',
                'content'=>'required',
                'category_id'=>'required',
            ]);
            unset($params_array['id']);
            unset($params_array['user_id']);
            unset($params_array['cerated_at']);
            unset($params_array['user']);
            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'No se ha podido Actualizar el objeto',
                    'errors' => $validate->errors()
                );
            } else {
                $user = $this->getidentity( $request->header('Authorization',null));
                $post = Post::where('id',$id)
                    ->where('user_id',$user->sub)
                    ->first();
                if (is_object($post) &&!isEmpty($post)) {
                    $post->update($params_array);
                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Se Actualizo con exito el objeto',
                        'post' => $post,
                        'changes'=>$params_array);
                }
            }
        }else{
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Datos enciados incorrectos');
        }
        return response()->json($data, $data['code']);
    }
    public function destroy(Request $request,$id){
        $user = $this->getidentity( $request->header('Authorization',null));
        $post = Post::where('id',$id)
        ->where('user_id',$user->sub)
        ->first();
        if (is_object($post) &&!isEmpty($post)) {
                $post->delete();
            $data = array([
                'code' => 200,
                'status' => 'success',
                'post'=>$post
            ]);
        }else {
            $data = array([
                'code' => 404,
                'status' => 'error',
                'message' => 'La entrada no existe'
            ]);
        }
        return response()->json($data,$data['code']);
    }
    private  function getidentity($token){
        $jwtAuth = new JwtAuth();
        //$token = $request->header('Authorization',null);
        $user = $jwtAuth->checkToken($token, true);
        return $user;
    }
    public function upload(Request $request){
        $image = $request->file('file0');
        $validate = Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png',
        ]);
        if (!$image || $validate->fails()) {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir la imagen'
            );
        } else {
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('image')->put($image_name, \File::get($image));
            $data = array(
                'code' => 200,
                'status' => 'succes',
                'image' => $image_name
            );
        }
        return response()->json($data, $data['code']);
    }
    public function getImage($fileName) {
        if((\Storage::disk('images')->exists($fileName))){
            $file = \Storage::disk('images')->get($fileName);
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
    public function getPostsByCategory($id){
        $posts = Post::where('category_id',$id)->get();
        return \response()->json([
            'status'=>'success',
            'posts'=>$posts
        ],200);
    }
    public function getPostsByUser($id){
        $posts = Post::where('user_id',$id)->get();
        return \response()->json([
            'status'=>'success',
            'posts'=>$posts
        ],200);
    }
}

