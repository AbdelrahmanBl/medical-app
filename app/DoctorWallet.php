<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DoctorWallet extends Model
{
    protected $fillable = [
    	'doctor_id','transaction_id','type','amount'
    ];
}
