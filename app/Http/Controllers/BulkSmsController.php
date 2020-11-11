<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;
use App\Http\Resources\PersonResource;

class BulkSmsController extends Controller
{
     public function sendSms( $mobile,$otp )
     {
     	$sid    = env( 'TWILIO_SID' );
       $token  = env( 'TWILIO_TOKEN' );
       $client = new Client( $sid, $token );

       //$rndval = rand(1000, 9999);
       $number  = '+20' . $mobile; //$request->input('phone');
       $message = 'Your Verification Code : ' . $otp;  
       $client->messages->create(
                   $number,
                   [ 
                       'from' => env( 'TWILIO_FROM' ),
                       'body' => $message,
                   ]
               );

     	/*return new PersonResource([
    		'send' => true
    	]);*/
     }
}
