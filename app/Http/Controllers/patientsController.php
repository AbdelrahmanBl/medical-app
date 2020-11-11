<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Exception; 

use App\Patient;
use App\Doctor;
use App\Booking;
use App\Ticket;
use App\Day;
use App\user_devices;
use App\Rating;

use App\Helper;
use validate;
use Hash;
use DB;

class patientsController extends Controller
{
    protected $specialitie_sequence = '#%#' ;
    public function get_profile(Request $req)
    {
        try{
        $patient_id = $req->get('id');
        
        $model = new Patient();
        $model_data = $model->where('id',$patient_id)->first();
        return Helper::return([
            'profile'  => $model_data,
        ]);
        }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
    public function get_doctors(Request $request)
    {
        try{
        $city         = $request->get('city');
        $specialities = $request->get('specialities');
        
        $where = array();
        if($city)
            $where[] = ['city',$city];
        if($specialities)
            $where[] = ['specialities',$specialities];
        
        $model = Doctor::where($where);
        $collection = $model->select(['id','first_name','last_name','image','fees','rate','visitor_rating','is_busy','gender','specialities'])->paginate(12);

        $model_data = $collection->getCollection()->transform(function ($value){
        $map['id']           = $value['id'];
        $map['first_name']   = $value['first_name'];
        $map['last_name']    = $value['last_name'];
        $map['image']        = $value['image'];
        $map['fees']         = $value['fees'];
        $map['is_busy']      = $value['is_busy'];
        $map['gender']       = $value['gender'];
        $rating = ($value['visitor_rating'] > 0)?floor(( ($value['rate'] / 5) / $value['visitor_rating'] ) * 5) : NULL;
        $map['rating']       = $rating;
        $map['specialities']  = $value['specialities'];
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
    public function get_rate(Request $req)
    {
        try{
        $patient_id  = $req->get('id');
        $req->validate([
            'doctor_id'  => 'required|numeric|exists:doctors,id'
        ]); 
        $doctor_id   = $req->get('doctor_id');

        $where = array(
            'patient_id'  => $patient_id,
            'doctor_id'   => $doctor_id,
        );
        $rating      = Rating::where($where);
        $rating_data = $rating->first();
        $rate = null;
        if($rating_data)
            $rate = $rating_data->rating;
        return Helper::return([
            'rate'    => $rate
        ]);
    }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
    public function get_tickets(Request $request)
    {
        try{
        $request->validate([
            'doctor_id' => 'required|numeric|exists:doctors,id',
            'day_no'    => 'nullable|numeric|exists:days,id',
        ]);
        $doctor_id    = $request->get('doctor_id');
        $day_no       = $request->get('day_no');

        $where = array();
        $where[] = ['tickets.doctor_id',$doctor_id];
        $where[] = ['tickets.status','ON'];
        if($day_no)
            $where[] = ['days.id',$day_no];
        else $where[] = ['days.day',date("l", strtotime(date('Y-m-d')))];

        $model = Ticket::where($where)->join('days','days.id','tickets.day_id');
        $collection = $model->get(['tickets.id','days.day','tickets.from','tickets.to','tickets.duration','tickets.availability']);
        $model_data = $collection->transform(function ($value){
        $map['id']             = $value['id'];
        $map['day']            = $value['day'];
        $map['from']           = $value['from'];
        $map['to']             = $value['to'];
        $map['duration']       = $value['duration'];
        $map['availability']   = $value['availability'];
        $map['date']           = Helper::getNextDate($value['day'],$value['from']);

        return $map;     
        });
        return Helper::return([
            'tickets'  => $model_data
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
            'status'   => 'required|string|in:UPCOMING,LAST'
        ]);
        $patient_id = $req->get('id');
        $status     = $req->get('status');
        switch ($status) {
            case 'UPCOMING':
                $status = 'WAITING';
                break;
            case 'LAST':
                $status = 'COMPLETED';
                break;
        }
        $where = array();
        $where[] = ['bookings.patient_id',$patient_id];
        $where[] = ['bookings.status',$status];

        $select = array(
            // 'bookings.id',
            'doctors.image as doctor_image',
            'doctors.first_name',
            'doctors.last_name',
            'tickets.from',
            'tickets.to',
            'days.day',
            'bookings.payment',
            'bookings.date',
        );
        $model = Booking::where($where)->join('doctors','bookings.doctor_id','doctors.id')->join('tickets','tickets.id','bookings.ticket_id')->join('days','tickets.day_id','days.id');
        if($status == 'COMPLETED'){
            $model->join('end_bookings','bookings.id','end_bookings.booking_id'); 
            $select[] = 'end_bookings.image as prescrption_image';          
            $select[] = 'end_bookings.note';          
        }
        $model_data = $model->orderBy('bookings.id','desc')->select($select)->paginate(10);

        return Helper::return([
            'data'  => $model_data
        ]);
        }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
/*-------------------------------------------------------------------*/
    public function show( Person $person )
    {
        return new PersonResource(["data" => $person]);
    }
    public function index():PersonResourceCollection
    {
        return new PersonResourceCollection(Person::paginate());
    }
    public function destroy(Person $person)
    {
        $person->delete();
        return response()->json('Record Is Deleted Successfully');
        /*return new PersonResource([
            "message" => "DELETED"
        ]);*/
    }
    public function login( Request $req )
    {
        $req->validate([
            'email'      =>  'required|email',
            'password'   =>  'required',
        ]);
        $email    = $req->input('email');
        $password = $req->input('password');

        $model = new Patient();
        $model_select = $model::where('email',$email);
        $model_data = $model_select->first();
        if(!$model_data)
            return Helper::returnError('email or password is incorrect');
        if($model_data->status == 'OFF')
            return Helper::returnError('admin closed this account');
        if(!Hash::check($password, $model_data->password))
            return Helper::returnError('email or password is incorrect');
        $token    = Helper::loginUsingId($model_select);
        return Helper::return([
            "token" => $token
        ]);
    }
    public function generate_otp($mobile)
    {
        $where = array(
            'mobile'   => $mobile,
            'valid'    => 1
        );
        $CHK_OTP = DB::table('user_devices')->where($where)->first();
        if($CHK_OTP)
            $OTP = (string)$CHK_OTP->otp; 
        else {
            $OTP = mt_rand(1000, 9999);
            $User_Device = new user_devices([
                'mobile' => $mobile,
                'otp'    => $OTP,
            ]);
            $User_Device->save();
        }
       
      return (string)$OTP;
    }
    public function invalidOTP($where)
    {
        return DB::table('user_devices')->where($where)->update([
            'valid'   => 0
        ]);
    }
    public function sent_otp(Request $req)
    {
        $req->validate([
            'mobile' => 'required|numeric',
        ]);
        $mobile = $req->input('mobile');
        $OTP = $this->generate_otp($mobile);
        
        $Controller = new BulkSmsController;
        $Controller->sendSms($mobile,$OTP);
        
        return Helper::return([]);
    }
    public function verify_otp(Request $req)
    {
        try{
        $req->validate([
            'otp'     => 'required|digits:4|exists:user_devices',
            'mobile'  => 'required|exists:user_devices'
        ]);
        $OTP     = $req->input('otp');
        $mobile  = $req->input('mobile');
        $where = array(
            'otp'    =>  $OTP,
            'mobile' =>  $mobile,
            'valid'  =>  1
        );
        $person = DB::table('user_devices')->where($where)->first();
        if($person){
        $model = DB::table('patients')->where('phone',$person->mobile);
        $this->invalidOTP($where);
        if($model->first()){
            return Helper::return([
                "register" => true ,
                "access_token" => Helper::loginUsingId($model)
            ]);
        }    
        else{
            return Helper::return([
                "register" => false ,
                "access_token"=> NULL
            ]);
          }  
        }
        return Helper::returnError('please go to sent otp');
    }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }    
    public function register(Request $req)
    {
        try{
        $req->validate([
            'first_name'        => 'required|string|max:20',
            'last_name'         => 'required|string|max:20',
            'city'              => 'required|string|max:30',
            'phone'             => 'required|string|max:15',
            'email'             => 'required|email|max:64|unique:patients',
            'password'          => 'required|string|min:6|max:16',
            'gender'            => 'required|string|in:MALE,FEMALE',
            'date_of_birth'     => 'required|date',
        ]);
        $my_arr = $req->all(['first_name','last_name','city','phone','email','gender','date_of_birth']);
        $my_arr['password'] = Hash::make($req->input('password'));
        $person = new Patient($my_arr);
        $person->save();
        $model = Patient::where('id',$person->id);
        $access_token = Helper::loginUsingId($model);
        return Helper::return([
            'access_token'  => $access_token
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
        $patient_id = $req->get('id');
        $req->validate([
            'first_name'        => 'required|string|max:20',
            'last_name'         => 'required|string|max:20',
            'city'              => 'required|string|max:30',
            'phone'             => 'required|string|max:15',
            'email'             => "required|email|max:64|unique:patients,email,{$patient_id},id",
            'password'          => 'required|string|min:6|max:16',
            'gender'            => 'required|string|in:MALE,FEMALE',
            'date_of_birth'     => 'required|date',
        ]);
        $my_arr = $req->all(['first_name','last_name','city','phone','email','gender','date_of_birth']);
        $my_arr['password'] = Hash::make($req->input('password'));
        $patient = Patient::where('id',$patient_id);
        $patient->update($my_arr);
        
        return Helper::return([]);
        }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
    public function update_profile_image(Request $req)
    {
        try{
        $id = $req->get('id');
        $req->validate([
            'image'    => 'required|image|mimes:jpeg,png,jpg|max:2000'
        ]);
        $image        = $req->file('image');

        $patient = Patient::where('id',$id);
        $exist  = $patient->first();
        if($exist->image)
            Helper::image($image,'update','patients',$exist->image);
        else{
            $url = Helper::image($image,'add','patients');
            $patient->update(['image' => $url]);
        }
        
        return Helper::return([]);
    }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
    public function book(Request $req)
    {
        try{
        $patient_id   = $req->get('id');
        $doctor_id = $req->input('doctor_id');
        $req->validate([
            'doctor_id'    => "required|numeric|exists:doctors,id",
            'ticket_id'    => "required|numeric|exists:tickets,id,doctor_id,{$doctor_id}",
            'payment'      => 'required|string|in:CASH,VISA',
        ]);
        $ticket_id    = $req->input('ticket_id');
        $payment      = $req->input('payment');

        $where = array(
            'doctor_id'  => $doctor_id,
            'id'         => $ticket_id
        );
        $ticket = Ticket::where($where);
        $ticket_data = $ticket->first();
        $doctor   = Doctor::where('id',$doctor_id)->first();
        if($doctor->status == 'OFF' || $doctor->is_approved == 0)
            return Helper::returnError('booking is not allowed to this doctor right now');
        if($doctor->is_busy == 1)
            return Helper::returnError('this doctor is not available right now');
        if($ticket_data->status == 'OFF')
            return Helper::returnError('this ticket has been closed');
        if($ticket_data->availability == 'NO')
            return Helper::returnError('this ticket has been booked');

        $where = array(
            'patient_id' => $patient_id,
            'status'     => 'WAITING'
        );
        $booking_count = Booking::where($where)->count();
        if($booking_count >= 15)
            return Helper::returnError('you exceeded your limit of bookings');

        $day = Day::where('id',$ticket_data->day_id)->first()->day;
        $from = $ticket_data->from;
        $date = Helper::getNextDate($day,$from);

        $booking = new Booking();
        $booking->patient_id  = $patient_id;
        $booking->doctor_id   = $doctor_id;
        $booking->ticket_id   = $ticket_id;
        $booking->date        = $date;
        $booking->payment     = $payment;
        $booking->total       = Doctor::where('id',$doctor_id)->first()->fees;
        $booking->save();

        $ticket->update(['availability' => 'NO']);
        return Helper::return([]);
    }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
    public function rate(Request $req)
    {
        try{
        $patient_id   = $req->get('id');
        $req->validate([
            'doctor_id'    => "required|numeric|exists:doctors,id",
            'rating'       => 'required|numeric|in:1,2,3,4,5',
            'comment'      => 'nullable|string|max:150',
        ]);
        $doctor_id = $req->input('doctor_id');
        $rating    = $req->input('rating');
        $comment   = $req->input('comment');

        $where = array(
            'doctor_id'  => $doctor_id,
            'patient_id' => $patient_id
        );
        $rate      = Rating::where($where);
        $rate_data = $rate->first();
        $doctor    = Doctor::where('id',$doctor_id);
        if($rate_data){
            $update_rating = $rating;
            $rating = $rating - $rate_data->rating ;
            $rate->update([
                'rating'  => $update_rating
            ]);
        }else{
            $Rating = new Rating();
            $Rating->patient_id  = $patient_id;
            $Rating->doctor_id   = $doctor_id;
            $Rating->rating      = $rating;
            $Rating->comment     = $comment;
            $Rating->save();
            $doctor->increment('visitor_rating');
        }
        
        $doctor->increment('rate',$rating);
        return Helper::return([]);
    }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
}
