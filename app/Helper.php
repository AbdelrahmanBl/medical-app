<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Setting;
use App\DoctorWallet;
 
class Helper extends Model
{
    public static function return($result)
    {
    return [
        'error_flag' => 0,
        'message' => 'success',
        'result'=> $result
      ];
    }
    public static function returnError($result)
    {
    return [
        'error_flag'    => 1,
        'message'       => $result,
        'result'        => NULL,
      ];
    }
    public static function returnException($e)
    {
    $Exception = true;//Setting::where('key','EXCEPTION')->first();
    ($Exception/*->value*/)? $msg = $e->getMessage() : $msg = 'Something Went Wrong . Please Try Again' ;
    return $msg;
    }
    public static function notFound($message)
    {
      return response()->json([
            'error_flag' => 404,
            'message' => $message,
            'result'=> NULL
        ]);
    }
    public static function loginUsingId($model)
    {
            $token = str_random(64);
            $model->update([
            'remember_token'   => $token,
            ]); 
            return $token;
    }
    public static function getNextDate($day,$time)
    {
        $from = strtotime($time);
        $now  = strtotime(date('H:i'));
        $remain_minutes = round(abs($now - $from) / 60,2);

        $day_status = '';
        if($remain_minutes <= 30)
            $day_status = 'next';
        $date = $date = date("Y-m-d", strtotime("{$day_status} {$day}"));
        return $date;
    }
    public static function check_now($day,$time,$date)
    {
        $from = strtotime(date("{$date} {$time}"));
        $now  = strtotime(date('Y-m-d H:i'));
        $remain_minutes = round(($from - $now) / 60,2);
        
        return $remain_minutes;
    }
    public static function image($image,$mode,$destinationPath,$filepath = NULL,$counter = 0)
    {
        if( $mode == 'update'){
            $oldImageName = explode('/',$filepath);
            $oldImageName = $oldImageName[count($oldImageName)-1];
            $imageName    =  $oldImageName;
        }
        else 
        $imageName =  (time() + $counter) .'.'.$image->getClientOriginalExtension();
        // $destinationPath = 'delievery/main_icons';
        $url = $destinationPath . '/' . $imageName;

        $image->move($destinationPath, $imageName);
        $access_url =  env('APP_URL').'/' . $url;
        return $access_url;
    }
    public static function completedBooking($id,$image,$note,$doctor_id,$ticket_id,$total)
    {
        
        $ticket = Ticket::where('id',$ticket_id);
        $commission_per = Setting::where('key','commission_per')->first()->value;
        $commission     = $total * ( $commission_per / 100 );

        $EndBooking = new EndBooking();
        $EndBooking->booking_id     = $id;
        $EndBooking->image          = Helper::image($image,'add','prescriptions');
        $EndBooking->note           = $note;
        $EndBooking->commission     = $commission;
        $EndBooking->commission_per = $commission_per;
    
        $wallet = new DoctorWallet();
        $wallet->doctor_id          = $doctor_id;
        $wallet->transaction_id     = time();
        $wallet->type               = 'C';
        $wallet->amount             = $commission;

        $EndBooking->save();
        $wallet->save();
        $ticket->update(['availability' => 'YES']);
    }
    public static function disable($model,$col,$on,$off,$return_on,$return_off)
    {
        if($model->first()->$col == $on){
        $model->update([
            $col    => $off
        ]);
        $status = $return_off;
        }else{
        $model->update([
            $col    => $on
        ]);
        $status = $return_on;
        }
        return $status;
    }
    
}
