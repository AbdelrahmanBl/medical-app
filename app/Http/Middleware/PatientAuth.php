<?php

namespace App\Http\Middleware;
use App\Patient;
use Closure;
use Crypt;
use Exception;
use App\Helper;

class PatientAuth
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

        $model = Patient::where('email',$email);
        $data = $model->first();

        if(!$data) 
            return Helper::notFound('Not Authenicated !');
        if($data->status == 'OFF')
            return Helper::notFound('admin closed this account');
        
        // $access_token     = ($request->header('access-token'));
        // if($data->remember_token != $access_token){
        //     return Helper::notFound('Not Authenicated !');
        // }
        
        $request->attributes->add([
            'id'       => $data->id
        ]);
        return $next($request);
        }catch(Exception $e){
         return Helper::notFound('Not Authenicated !');
        }
    }
}

