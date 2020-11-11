<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Doctor;
use Faker\Generator as Faker;

$factory->define(Doctor::class, function (Faker $faker) {
    return [
        'first_name'       =>  $faker->firstName,      
		'last_name'        =>  $faker->lastName,         
		'hospital_name'    =>  $faker->randomElement(['new hospital','el-maady hospital','world hospital']),           
		'specialities'     =>  $faker->randomElement(['heart','surgery','sight','kids','bones']),       
		'mobile_number'    =>  $faker->phoneNumber,           
		'phone_number'     =>  $faker->phoneNumber,          
		'email'            =>  $faker->safeEmail,               
		'password'         =>  App::make('hash')->make(123456),   
		'info'             =>  $faker->sentence(10), 
		'city'             =>  $faker->randomElement(['ismailia','cairo','port said']),  
		'fees'             =>  $faker->randomElement([100,200,300,400,500]),  
		'gender'           =>  $faker->randomElement(['MALE','FEMALE']),  
		'rate'             =>  rand(20,100),  
		'visitor_rating'   =>  20,  
    ];
});
