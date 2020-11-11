<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Admin;

use App\Doctor;
use App\Patient;
use App\Booking;
use App\Ticket;
use App\Day;
use App\AdminWallet;
use App\DoctorWallet;
use App\Payment;

use App\Helper;
use validate;
use Hash;
use DateTime;
class adminController extends Controller{
    protected $specialitie_sequence = '#%#' ;
    public function login(Request $req)
    {
        try{
        $req->validate([
            'email'      =>  'required|email',
            'password'   =>  'required',
        ]);
        $email    = $req->input('email');
        $password = $req->input('password');

        $model = new Admin();
        $model_select = $model::where('email',$email);
        $model_data = $model_select->first();
        if(!$model_data)
            return Helper::returnError('email or password is incorrect');
        if(!Hash::check($password, $model_data->password))
            return Helper::returnError('email or password is incorrect');
        $token    = Helper::loginUsingId($model_select);
        return Helper::return([
            "token" => $token
        ]);
        }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
    public function update_profile(Request $req)
    {
        try{
        $admin_id = $req->get('id');
        $req->validate([
            'first_name'      =>  'required|string|max:20',
            'last_name'       =>  'required|string|max:20',
            'email'           =>  "required|string|max:64|unique:admins,email,{$admin_id},id",
            'old_password'    =>  'nullable|string',
        ]);
        $old_password = $req->input('old_password');

        $my_arr = $req->all(['first_name','last_name','email']);
        $model = Admin::where('id',$admin_id);
        if($old_password){
        $req->validate(['new_password'    =>  'required|string|min:6|max:16']);
        $model_data = $model->first();
        if(!Hash::check($old_password, $model_data->password))
            return Helper::returnError('password is incorrect');
        $my_arr['password'] = Hash::make($req->input('new_password'));
        }
        $model->update($my_arr);
        return Helper::return([]);
        }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
    public function update_profile_pic(Request $req)
    {
        try{
        $admin_id = $req->get('id');
        $req->validate([
            'image'    => 'required|image|mimes:jpeg,png,jpg|max:2000'
        ]);
        $image = $req->file('image');

        $model = Admin::where('id',$admin_id);
        
        $exist  = $model->first();
        if($exist->image)
            Helper::image($image,'update','admin',$exist->image);
        else{
            $url = Helper::image($image,'add','admin');
            $model->update(['image' => $url]);
        }
        return Helper::return([]);
        }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
    public function pay(Request $req)
    {
        try{
        $req->validate([
            'pay'           =>  'required|array',
            'pay.*.id'      =>  'required|numeric|exists:doctors,id',
            'pay.*.date'    =>  'required|string|date:Y-m-d',
            'pay.*.refrence'=>  'nullable|string|unique:payments,transaction_refrence|max:30|distinct',
            'pay.*.type'    =>  'required|string|in:BANK,CASH',
        ]);
        $pay = $req->input('pay');
        $id = array();
        foreach($pay as $obj){
            $id[] = $obj['id'];
        }
        $model = Doctor::whereIn('id',$id)->where('wallet_balance','<',0);
        $model_data = $model->get(['id','wallet_balance']);
        if(!$model_data || count($id) != count($model_data))
            return Helper::returnError('this method not allowed');
        $counter = 0;
        $pay_group = array();
        $doctors_wallets = array();
        $total_amount = 0;
        foreach( $pay as $doctor){
            if( $doctor['type'] == 'BANK' && $doctor['refrence'] == NULL )
                return Helper::returnError('check empty refrence no at row '.(++$counter));
            $amount = $model_data->where('id',$doctor['id'])->first()->wallet_balance * -1;
            $pay = new Payment();
            $pay->transaction_id        = time() + $counter;
            $pay->doctor_id             = $doctor['id'];
            $pay->transaction_date      = $doctor['date'];
            $pay->transaction_refrence  = $doctor['refrence'];
            $pay->type                  = $doctor['type'];
            $pay->amount                = $amount;
            $pay->created_at            = date('Y-m-d H:i:s');
            
            $wallets = new DoctorWallet();
            $wallets->doctor_id         = $doctor['id'];
            $wallets->transaction_id    = time() + $counter;
            $wallets->type              = 'D';
            $wallets->amount            = $amount;
            $wallets->created_at        = date('Y-m-d H:i:s');
            
            $pay_group[] = $pay->toArray();
            $doctors_wallets[] = $wallets->toArray();
            $counter++;
            $total_amount += $amount;
        }
            $admin_wallet = new AdminWallet();
            $admin_wallet->transaction_id    = time();
            $admin_wallet->type              = 'D';
            $admin_wallet->amount            = $total_amount;

        Payment::insert($pay_group);
        DoctorWallet::insert($doctors_wallets);
        $admin_wallet->save();
        return Helper::return([]);
        }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
    public function approve_doctor(Request $req)
    {
        try{
        $req->validate([
            'id'      =>  'required|numeric|exists:doctors,id',
        ]);
        $id    = $req->input('id');

        $model = Doctor::where('id',$id);
        $model_data = $model->first();
        if($model_data->is_approved == 1)
            return Helper::returnError('this doctor has been approved before');
        
        $model->update(['is_approved' => 1]);
        return Helper::return([]);
        }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
    public function change_doctor_status(Request $req)
    {
        try{
            $req->validate([
                'id'        => "required|exists:doctors,id",
            ]);
            
            $id   = $req->input('id');
            $model = Doctor::where('id',$id);
            $status = Helper::disable($model,'status','ON','OFF','ON','OFF');
            
            return Helper::return([
                'status'   => $status,
            ]);
        }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
    public function change_patient_status(Request $req)
    {
        try{
            $req->validate([
                'id'        => "required|exists:patients,id",
            ]);
            
            $id   = $req->input('id');
            $model = Patient::where('id',$id);
            $status = Helper::disable($model,'status','ON','OFF','ON','OFF');
            
            return Helper::return([
                'status'   => $status,
            ]);
        }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
/*-----------------------------------------------*/
    public function logout(Request $req)
    {
        try{
        $admin_id = $req->get('id');
        $model = Admin::where('id',$admin_id);
        $model->update(['remember_token' => NULL]);

        return Helper::notFound('success');
        }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
    public function get_profile(Request $req)
    { 
        try{
        $admin_id = $req->get('id');
        $model = Admin::where('id',$admin_id);
        $model_data = $model->first(['first_name','last_name','email','image']);
        $wallet_amount = AdminWallet::orderBy('id','DESC')->first();
        return Helper::return([
            'doctors'    => Doctor::where('is_approved',1)->count(),
            'doctors_na' => Doctor::where('is_approved',0)->count(),
            'patients'   => Patient::count(),
            'waiting'    => Booking::where('status','WAITING')->count(),
            'started'    => Booking::where('status','STARTED')->count(),
            'completed'  => Booking::where('status','COMPLETED')->count(),
            'cancelled'  => Booking::where('status','CANCELLED')->count(),
            'wallet_amount' => ($wallet_amount)? floor($wallet_amount->close_balance) : 0 ,
            'payments'   => Doctor::where('wallet_balance','<',0)->sum('wallet_balance') * -1,
            'payments_transactions' => Payment::count(),
            'profile'    => $model_data,
        ]);
        }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
    public function get_doctors_requests(Request $req)
    {
        try{
        $model = Doctor::where('is_approved',0);
        $select = array('id','first_name','last_name','email','image','specialities','mobile_number','city','gender','fees','date_of_birth','created_at');
        $collection = $model->select($select)->paginate(10);

        $model_data = $collection->getCollection()->transform(function ($value){
        $map['id']           = $value['id'];
        $map['first_name']   = $value['first_name'];
        $map['last_name']    = $value['last_name'];
        $map['email']        = $value['email'];
        $map['mobile_number']= $value['mobile_number'];
        $map['image']        = $value['image'];
        $map['fees']         = $value['fees'];
        $map['city']         = $value['city'];
        $map['specialities'] = $value['specialities'];
        $map['gender']       = $value['gender'];
        
        $birthDate = $value['date_of_birth'];
        $date = new DateTime($birthDate);
        $now = new DateTime();
        $interval = $now->diff($date);
        $age = $interval->y;

        $map['age']          = ($age)? $age : NULL ;
        $map['created_at']   = date($value['created_at']);
        return $map;     
        });
        return Helper::return([
            'total'    => $collection->total(),
            'per_page' => $collection->perPage(),
            'doctors'  => $model_data,
        ]);
        }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
    public function get_doctors(Request $req)
    {
        try{
        $model = Doctor::where('is_approved',1);
        $select = array('id','first_name','last_name','email','image','specialities','mobile_number','city','is_busy','status','gender','fees','date_of_birth','rate','visitor_rating','wallet_balance','created_at');
        $collection = $model->select($select)->orderBy('wallet_balance','ASC')->paginate(10);

        $model_data = $collection->getCollection()->transform(function ($value){
        $map['id']           = $value['id'];
        $map['first_name']   = $value['first_name'];
        $map['last_name']    = $value['last_name'];
        $map['email']        = $value['email'];
        $map['mobile_number']= $value['mobile_number'];
        $map['image']        = $value['image'];
        $map['fees']         = $value['fees'];
        $map['city']         = $value['city'];
        $map['specialities'] = $value['specialities'];
        $map['is_busy']      = $value['is_busy'];
        $map['status']      = $value['status'];
        $map['wallet_balance'] = $value['wallet_balance'];
        $map['gender']       = $value['gender'];
        $rating = ($value['visitor_rating'] > 0)?floor(( ($value['rate'] / 5) / $value['visitor_rating'] ) * 5) : NULL;
        $map['rating']       = $rating;

        $birthDate = $value['date_of_birth'];
        $date = new DateTime($birthDate);
        $now = new DateTime();
        $interval = $now->diff($date);
        $age = $interval->y;

        $map['age']          = ($age)? $age : NULL ;
        $map['created_at']   = date($value['created_at']);
        return $map;     
        });
        return Helper::return([
            'total'    => $collection->total(),
            'per_page' => $collection->perPage(),
            'doctors'  => $model_data,
        ]);
        }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
    public function get_patients(Request $req)
    {
        try{
        $model = new Patient();
        $select = array('id','first_name','last_name','email','image','phone','city','gender','date_of_birth','created_at','status');
        $collection = $model::select($select)->orderBy('created_at','DESC')->paginate(10);

        $model_data = $collection->getCollection()->transform(function ($value){
        $map['id']           = $value['id'];
        $map['first_name']   = $value['first_name'];
        $map['last_name']    = $value['last_name'];
        $map['email']        = $value['email'];
        $map['phone']        = $value['phone'];
        $map['image']        = $value['image'];
        $map['fees']         = $value['fees'];
        $map['city']         = $value['city'];
        $map['gender']       = $value['gender'];
        $map['status']       = $value['status'];
        

        $birthDate = $value['date_of_birth'];
        $date = new DateTime($birthDate);
        $now = new DateTime();
        $interval = $now->diff($date);
        $age = $interval->y;

        $map['age']          = ($age)? $age : NULL ;
        $map['created_at']   = date($value['created_at']);
        return $map;     
        });
        return Helper::return([
            'total'    => $collection->total(),
            'per_page' => $collection->perPage(),
            'patients'  => $model_data,
        ]);
        }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
    public function get_bookings(Request $req)
    {
        try{
        $req->validate([
            'status'  => 'nullable|string|in:WAITING,STARTED,COMPLETED,CANCELLED'
        ]);
        $where = array();
        $status = $req->input('status');
        if($status)
        $where = ['bookings.status' => $status];
        $model = Booking::where($where);
        $select = array('doctors.first_name as doctor_first_name','doctors.last_name as doctor_last_name','doctors.image as doctor_image','doctors.mobile_number as doctor_phone',
            'patients.first_name as patient_first_name','patients.last_name as patient_last_name','patients.image as patient_image','patients.phone as patient_phone',
            'doctors.city','bookings.date','bookings.status','bookings.total','bookings.created_at');
        $collection = $model->join('doctors','doctors.id','bookings.doctor_id')->join('patients','patients.id','bookings.patient_id')->orderBy('bookings.created_at','DESC')->select($select)->paginate(10);
        
        return Helper::return([
            'total'    => $collection->total(),
            'per_page' => $collection->perPage(),
            'bookings' => $collection->getCollection(),
        ]);
        }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
    public function get_funds(Request $req)
    {
        try{
        $model = Doctor::where('wallet_balance','<',0);
        $select = array('id','first_name','last_name','email','image','mobile_number','phone_number','city','gender','wallet_balance');
        $collection = $model->select($select)->orderBy('wallet_balance','ASC')->paginate(10);

        $model_data = $collection->getCollection()->transform(function ($value){
        $map['id']           = $value['id'];
        $map['first_name']   = $value['first_name'];
        $map['last_name']    = $value['last_name'];
        $map['email']        = $value['email'];
        $map['mobile_number']= $value['mobile_number'];
        $map['phone_number'] = $value['phone_number'];
        $map['image']        = $value['image'];
        $map['city']         = $value['city'];
        $map['total']        = $value['wallet_balance'];
        $map['gender']       = $value['gender'];

        return $map;     
        });
        return Helper::return([
            'total'    => $collection->total(),
            'per_page' => $collection->perPage(),
            'payments'  => $model_data,
        ]);
        }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
    public function get_payments(Request $req)
    {
        try{
        $model = Payment::join('doctors','doctors.id','payments.doctor_id');
        $select = array('doctors.image','doctors.first_name','doctors.last_name','doctors.wallet_balance','payments.transaction_id','payments.transaction_refrence','payments.type','payments.amount','payments.created_at');
        $collection = $model->select($select)->orderBy('payments.id','DESC')->paginate(10);

        return Helper::return([
            'total'    => $collection->total(),
            'per_page' => $collection->perPage(),
            'payments'  => $collection->getCollection(),
        ]);
        }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
    public function get_wallet(Request $req)
    {
        try{
        $model = new AdminWallet();
        $select = array('transaction_id','type','amount','open_balance','close_balance','created_at');
        $collection = $model::select($select)->orderBy('created_at','DESC')->paginate(10);

        $model_data = $collection->getCollection()->transform(function ($value){
        $map['transaction_id']  = $value['transaction_id'];
        $map['type']            = $value['type'];
        $map['amount']          = $value['amount'];
        $map['open_balance']    = $value['open_balance'];
        $map['close_balance']   = $value['close_balance'];
        $map['created_at']      = date($value['created_at']);

        return $map;     
        });
        return Helper::return([
            'total'    => $collection->total(),
            'per_page' => $collection->perPage(),
            'wallet'   => $model_data,
        ]);
        }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
}
