<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Booking;
use Faker\Generator as Faker;

$factory->define(Booking::class, function (Faker $faker) {
    return [
        'patient_id'     => $faker->randomElement([1,2,3,4,5,6,7,8,9,10]), 
        'doctor_id'      => $faker->randomElement([1,2,3,4,5,6,7,8,9,10]), 
        'ticket_id'      => $faker->randomElement([1,2,3,4]), 
        'date'           => $faker->date('Y-m-d'), 
        'status'         => $faker->randomElement(['WAITING', 'STARTED', 'COMPLETED', 'CANCELLED']), 
        'payment'        => 'CASH',
        'total'          => $faker->randomElement([100,200,300,400]), 
    ];
});
