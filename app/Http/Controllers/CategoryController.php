<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Category;

class CategoryController extends Controller {

    public function __construct() {
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    public function index() {
        $categories = Category::all();
        return response()->json([
                    'code' => 200,
                    'status' => 'succes',
                    'categories' => $categories
                        ]
        );
    }

    public function show($id) {
        $category = Category::find($id);
        if (is_object($category)) {
            $data = array([
                    'code' => 200,
                    'status' => 'succes',
                    'category' => $category
            ]);
        } else {
            $data = array([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'La categoria no existe'
            ]);
        }
        return response()->json($data, $data['code']);
    }

    public function store(Request $request) {
        $json = $request->input('json', null);
        $params_array = array_map('trim', json_decode($json, true));
        if (!isEmpty($params_array)) {
            $validate = Validator::make($params_array, [
                        'name' => 'required',
            ]);
            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'No se ha podido crear el objeto',
                    'errors' => $validate->errors()
                );
            } else {
                $category = new Category();
                $category->name = $params_array['name'];
                $category->save();
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Se ha creado con exito el objeto',
                    'category' => $category);
            }
            return response()->json($data, $data['code']);
        }
    }

    public function update(Request $request, $id) {
         $json = $request->input('json', null);
        $params_array = array_map('trim', json_decode($json, true));
        if (!isEmpty($params_array)) {
            $validate = Validator::make($params_array, [
                        'name' => 'required',
            ]);
            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'No se ha podido Actualizar el objeto',
                    'errors' => $validate->errors()
                );
            } else {
                $category = Category::where('id',$id).update(
                        ['name'=>$params_array['name']]);
               // $category->name = $params_array['name'];
                //$category->;
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Se Actualizo con exito el objeto',
                    'category' => $category);
            }
            return response()->json($data, $data['code']);
        }
    }

}
