<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Patient;
use Faker\Generator as Faker;

$factory->define(Patient::class, function (Faker $faker) {
    return [
      	'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email'  => $faker->safeEmail,
        'password' => App::make('hash')->make(123456),
        'phone' => $faker->phoneNumber,
        'city'  => $faker->city,
        'gender' => $faker->randomElement(['MALE','FEMALE']),
        'date_of_birth' => $faker->date('Y-m-d'),
    ];
});
