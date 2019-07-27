<?php

namespace App\Http\Middleware;

use Closure;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->header('Authorization');
        $jwtAuth = new \App\helpers\JwtAuth();
        $chekToken = $jwtAuth->checkToken($token);
        //return $next($request);
         if ($chekToken) {
             return $next($request);
         }else{
              $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'EL usuario no esta autenticado'
            );
               return response()->json($data,$data['code']);
         }
    }
}
