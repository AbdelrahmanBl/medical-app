<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'doctor_id','day_id','from','to','duration'
    ];

    public $timestamps = true;
}
