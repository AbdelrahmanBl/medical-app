<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Doctor;
use App\Ticket;
use App\Booking;
use App\EndBooking;
use App\Day;
use App\Speciality;
use App\Rating;

use App\Helper;
use validate;
use Hash;
class doctorController extends Controller{
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

        $model = new Doctor();
        $model_select = $model::where('email',$email);
        $model_data = $model_select->first();
        if(!$model_data)
            return Helper::returnError('email or password is incorrect');
        if($model_data->is_approved == 0)
            return Helper::returnError('this account is not approved');
        if($model_data->status == 'OFF')
            return Helper::returnError('admin closed this account');
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
    public function add_ticket(Request $req)
    {
        try{
        $doctor_id = $req->get('id');
        $req->validate([
            'day'     =>  'required|numeric|exists:days,id',
            'from'    =>  'required|date_format:H:i',
            'to'      =>  'required|date_format:H:i|after:from'
        ]);
        $day    = $req->input('day');
        $from   = $req->input('from');
        $to     = $req->input('to');

        $where = array(
            'day_id'   => $day,
            'from'     => $from
        );
        $model = new Ticket();
        $model_data = $model->where($where)->first();
        if($model_data)
            return Helper::returnError('you have enter another ticket at the same time');

        $from_cal   = strtotime($req->input('from'));
        $to_cal     = strtotime($req->input('to'));
        $duration = round(abs($to_cal - $from_cal) / 60,2);

        $model->doctor_id     = $doctor_id;
        $model->day_id        = $day;
        $model->from          = $from;
        $model->to            = $to;
        $model->duration      = $duration;
        $model->save();
        return Helper::return([]);
        }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
    public function cancel_booking(Request $req)
    {
        try{
        $doctor_id = $req->get('id');
        $req->validate([
            'id'            => "required|numeric|exists:bookings,id,doctor_id,{$doctor_id}",
            'cancel_reason' => 'required|string|max:100',
        ]);
        $id             = $req->input('id');
        $cancel_reason  = $req->input('cancel_reason');

        $booking = Booking::where('id',$id);
        $booking_data = $booking->first();
        if($booking_data->status != 'WAITING')
            return Helper::returnError("you cant't cancel this booking");
        $ticket = Ticket::where('id',$booking_data->ticket_id);
        
        $EndBooking = new EndBooking();
        $EndBooking->booking_id     = $id;
        $EndBooking->cancel_reason  = $cancel_reason;
        $EndBooking->save();

        $booking->update(['status' => 'CANCELLED']);
        $ticket->update(['availability' => 'YES']);
        return Helper::return([]);
        }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
    public function update_booking(Request $req)
    {
        try{
        $doctor_id = $req->get('id');
        $req->validate([
            'id'            => "required|numeric|exists:bookings,id,doctor_id,{$doctor_id}",
            'status'        => 'required|string|in:STARTED,COMPLETED',
        ]);
        $id      = $req->input('id');
        $status  = $req->input('status');
        
        $booking = Booking::where('id',$id);
        $booking_data = $booking->first();
        $status_arr = ['WAITING','STARTED','COMPLETED'];
        $search_db = array_search($booking_data->status, $status_arr);

        if($booking_data->status == 'CANCELLED')
            return Helper::returnError("this booking has been cancelled");
        if($booking_data->status == 'COMPLETED')
            return Helper::returnError("this booking has been completed");
        $expected  = $status_arr[++$search_db];
        if($status != $expected)
            return Helper::returnError("the expected status for this booking is : {$expected}");
        if($status == 'STARTED'){
            $time = Ticket::where('tickets.id',$booking_data->ticket_id)->join('days','days.id','tickets.day_id')->select(['days.day','tickets.from'])->first();
            $chk = Helper::check_now($time->day,$time->from,$booking_data->date);
            if($chk > 5)
                return Helper::returnError("cant't start now remaining time : {$chk} minutes");
        } 
        if($status == 'COMPLETED'){
            $req->validate([
            'image'    => 'required|image|mimes:jpeg,png,jpg|max:2000',
            'note'     => 'required|string|max:150',
        ]);
        $image  = $req->file('image');
        $note   = $req->input('note');
        Helper::completedBooking($id,$image,$note,$doctor_id,$booking_data->ticket_id,$booking_data->total);
        }
        $booking->update(['status' => $status]);
        return Helper::return([]);
        }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
    public function tickets_status(Request $req)
    {
        try{
        $doctor_id = $req->get('id');
        $req->validate([
            'id'      => 'required|array',
            'id.*'    => "required|numeric|exists:tickets,id,doctor_id,{$doctor_id}",
            'status'  => 'required|string|in:ON,OFF'
        ]);
        $id        = $req->input('id');
        $status    = $req->input('status');

        $model = new Ticket();
        $model->whereIn('id',$id)->update([
            'status'   => $status
        ]);

        return Helper::return([]);
        }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    } 
/*-----------------------------------------------*/
    public function get_profile(Request $req)
    {
        try{
        $doctor_id = $req->get('id');
        

        $model = new Doctor();
        $model_data = $model->where('id',$doctor_id)->first();
        $rating = ($model_data->visitor_rating > 0)?floor(( ($model_data->rate / 5) / $model_data->visitor_rating ) * 5) : NULL;
        return Helper::return([
            'rate'     => $rating,
            'profile'  => $model_data,
        ]);
        }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
    public function get_tickets(Request $req)
    {
        try{
        $doctor_id = $req->get('id');
        $day       = $req->get('day');
        
        $where = array();
        $where[] = ['doctor_id',$doctor_id];
        if($day)
        $where[] = ['day_id',$day];

        $model = new Ticket();
        $model_data = $model->where($where)->paginate(10);

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
    public function get_bookings(Request $req)
    {
        try{
        $req->validate([
            'status'   => 'required|string|in:UPCOMING,LAST'
        ]);
        $doctor_id = $req->get('id');
        $status    = $req->get('status');
        switch ($status) {
            case 'UPCOMING':
                $status = 'WAITING';
                break;
            case 'LAST':
                $status = 'COMPLETED';
                break;
        }
        $where = array();
        $where[] = ['tickets.doctor_id',$doctor_id];
        $where[] = ['bookings.status',$status];

        $select = array(
            'bookings.id',
            'patients.image as patient_image',
            'patients.first_name',
            'patients.last_name',
            'patients.phone',
            'patients.gender',
            'patients.date_of_birth',
            'tickets.from',
            'tickets.to',
            'days.day',
            'bookings.payment',
            'bookings.date',
        );
        $model = Booking::where($where)->join('patients','bookings.patient_id','patients.id')->join('tickets','tickets.id','bookings.ticket_id')->join('days','tickets.day_id','days.id');
        if($status == 'COMPLETED'){
            $model->join('end_bookings','bookings.id','end_bookings.booking_id'); 
            $select[] = 'end_bookings.image as prescrption_image';          
            $select[] = 'end_bookings.note';          
        }
        $model_data = $model->orderBy('tickets.from','ASC')->select($select)->paginate(10);

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
    public function get_rates(Request $req)
    {
        try{
        $doctor_id = $req->get('id');

        $collection = Rating::where('doctor_id',$doctor_id)->paginate(10);
        $model_data = $collection->getCollection()->transform(function ($value){
        $map['rating']    = (int)$value['rating'];
        $map['comment']   = $value['comment'];
        return $map;     
        });
        return Helper::return([ 
            'total'    => $collection->total(),
            'per_page' => $collection->perPage(),
            'ratings'  => $model_data
        ]);
        }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
    public function get_current_booking(Request $req)
    {
        try{
        $doctor_id = $req->get('id');
        
        $where = array();
        $where[] = ['tickets.doctor_id',$doctor_id];
        $where[] = ['bookings.status','STARTED'];

        $select = array(
            'bookings.id',
            'patients.image',
            'patients.first_name',
            'patients.last_name',
            'patients.phone',
            'patients.gender',
            'patients.date_of_birth',
            'tickets.from',
            'tickets.to',
            'days.day',
            'bookings.payment',
            'bookings.date',
        );
        $model = Booking::where($where)->join('patients','bookings.patient_id','patients.id')->join('tickets','tickets.id','bookings.ticket_id')->join('days','tickets.day_id','days.id')->select($select);
        $model_data = $model->first();

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
    public function get_days(Request $req)
    {
        try{
        $model = new Day();
        $model_data = $model->get();

        return Helper::return([
            'days'  => $model_data
        ]);
        }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
    public function get_specialities(Request $req)
    {
        try{
        $model = new Speciality();
        $model_data = $model->where('status','ON')->get(['speciality']);

        return Helper::return([
            'specialities'  => $model_data
        ]);
        }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
/*-----------------------------------------------------------------*/
    public function update_profile(Request $req)
    {
        try{
        $id = $req->get('id');
        $req->validate([
            'first_name'        => 'required|string|max:20',
            'last_name'         => 'required|string|max:20',
            'city'              => 'required|string|max:20',
            'email'             => "required|email|unique:doctors,email,{$id},id",
            'password'          => 'required|string|min:6|max:16',
            'hospital_name'     => 'required|string|max:40',
            'specialities'      => 'required|string|exists:specialities,speciality',
            'mobile_number'     => 'required|string|max:15',
            'phone_number'      => 'required|string|max:15',
            'fees'              => 'required|numeric|between:1,9999',
            'info'              => 'required|string|max:255',
            'date_of_birth'     => 'required|date:Y-m-d',
        ]);
        $specialities = $req->input('specialities');
        $password     = $req->input('password');

        $my_array = $req->all(['first_name','last_name','city','email','hospital_name','mobile_number','phone_number','fees','info','date_of_birth','specialities']);
        $my_array['password']      = Hash::make($password);
        
    	$doctor = Doctor::where('id',$id);
        $doctor->update($my_array);
    	
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

        $doctor = Doctor::where('id',$id);
        $exist  = $doctor->first();
        if($exist->image)
            Helper::image($image,'update','doctors',$exist->image);
        else{
            $url = Helper::image($image,'add','doctors');
            $doctor->update(['image' => $url]);
        }
        
        return Helper::return([]);
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
            'city'              => 'required|string|max:20',
            'email'             => "required|email|unique:doctors,email",
            'password'          => 'required|string|min:6|max:16',
            'hospital_name'     => 'required|string|max:40',
            'specialities'      => 'required|string|exists:specialities,speciality',
            'mobile_number'     => 'required|string|max:15',
            'phone_number'      => 'required|string|max:15',
            'fees'              => 'required|numeric|between:1,9999',
            'info'              => 'required|string|max:255',
            'date_of_birth'     => 'required|date:Y-m-d',
        ]);
        $specialities = $req->input('specialities');
        $password     = $req->input('password');

        $my_array = $req->all(['first_name','last_name','city','email','hospital_name','mobile_number','phone_number','fees','info','date_of_birth','specialities']);
        $my_array['password']      = Hash::make($password);

        $doctor = new Doctor($my_array);
        $doctor->save();
        $model  = Doctor::where('id',$doctor->id);
        $token  = Helper::loginUsingId($model);
       
        return Helper::return([
            'token'    => $token
        ]);
        }catch(Exception $e){
        if($e instanceof ValidationException) {
             throw $e;
          }
         return Helper::returnError(Helper::returnException($e));
    }
    }
}