<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdminWallet extends Model
{
    protected $fillable = [
    	'transaction_id','type','amount'
    ];
}
