<?php

namespace App\Http\Middleware;

use Closure;

class ClientMiddleware
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
        if($request->header('client-key') == 'W3mZL6jT93GrgrCT'){
            return $next($request);
        }else{
            return response()->json(['response' => ['error' => ['Bad, very bad']]], 400);
        }
    }
}
