<?php

namespace App\Http\Middleware;
use App\Admin;
use Closure;
use Exception;
use App\Helper;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {try{
        $email            = $request->header('email');

        $model = Admin::where('email',$email);
        $data = $model->first();

        if(!$data) 
            return Helper::notFound('Not Authenicated !');
        
        /*$access_token     = ($request->header('access-token'));
        if($data->remember_token != $access_token){
            return Helper::notFound('Not Authenicated !');
        }*/
        
        $request->attributes->add([
            'id'       => $data->id
        ]);
        return $next($request);
        }catch(Exception $e){
         return Helper::notFound('Not Authenicated !');
        }
    }
}

