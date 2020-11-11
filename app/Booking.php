<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
    	'patient_id','doctor_id','ticket_id','date','payment','total'
    ]; 
}
