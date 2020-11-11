<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
    	'transaction_id','doctor_id','transaction_date','transaction_refrence','type','amount'
    ];
}
