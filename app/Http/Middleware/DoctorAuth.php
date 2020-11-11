<?php

namespace App\Http\Middleware;
use App\Doctor;
use Closure;
use Crypt;
use Exception;
use App\Helper;

class DoctorAuth
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

        $model = Doctor::where('email',$email);
        $data = $model->first();

        if(!$data) 
            return Helper::notFound('Not Authenicated !');
        if($data->is_approved == 0)
            return Helper::notFound('this account is not approved');
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
