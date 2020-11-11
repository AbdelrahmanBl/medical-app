<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $fillable = [
        'first_name', 'last_name','hospital_name','specialities','mobile_number','phone_number', 'email','password','fees','info','image','date_of_birth','city'
    ];
    protected $hidden = [
    	'password'
    ];
}
