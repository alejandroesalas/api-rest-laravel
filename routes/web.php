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
Route::post('/api/login','UserController@login');
Route::put('/api/users/update','UserController@update')->middleware(\App\Http\Middleware\ApiAuthMiddleware::class);
Route::post('/api/user/upload','UserController@upload')->middleware(\App\Http\Middleware\ApiAuthMiddleware::class);
Route::get('/api/users/avatar/{filename}','UserController@getImage');
Route::get('/api/users/details/{id}','UserController@details');

//ruta del controlador de categorias
Route::resource('/api/category','CategoryController');
//ruta del controlador de posts
Route::resource('/api/post','PostController');
Route::post('/api/users/upload','PostController@upload');
Route::get('/api/post/avatar/{filename}','PostController@getImage');
Route::get('/api/post/category/{id}','PostController@getPostsByCategory');
Route::get('/api/post/user/{id}','PostController@getPostsByUser');
