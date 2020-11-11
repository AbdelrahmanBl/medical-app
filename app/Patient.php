<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone','city','password','date_of_birth','gender'
    ];
    protected $hidden = [
    	'password'
    ];
}
