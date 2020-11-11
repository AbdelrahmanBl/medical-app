<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EndBooking extends Model
{
    protected $fillable = [
    	'booking_id','cancel_reason','image','note','commission','commission_per'
    ];
}
