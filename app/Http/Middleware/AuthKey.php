<?php

namespace App\Http\Middleware;

use Closure;
use App\Helper;
class AuthKey
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
        $token = $request->header('app-key');
        if($token != env("APP_PASSWORD") ){
            return Helper::notFound('APP KEY IS NOT CORRECT');
        }
        return $next($request);
    }
}
