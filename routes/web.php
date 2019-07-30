<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('/api/register','UserController@register');
Route::post('/login','UserController@login');
Route::put('/users/update','UserController@update')->middleware(\App\Http\Middleware\ApiAuthMiddleware::class);
Route::post('/users/upload','UserController@upload')->middleware(\App\Http\Middleware\ApiAuthMiddleware::class);
Route::get('/users/avatar/{filename}','UserController@getImage');
Route::get('/api/users/details/{id}','UserController@details');

//ruta del controlador de categorias
Route::resource('/category','CategoryController');
//ruta del controlador de posts
Route::resource('/post','PostController');
Route::post('/users/upload','PostController@upload');
Route::get('/post/avatar/{filename}','PostController@getImage');
Route::get('/post/category/{id}','PostController@getPostsByCategory');
Route::get('/post/user/{id}','PostController@getPostsByUser');
