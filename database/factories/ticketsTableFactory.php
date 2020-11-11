<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Ticket;
use Faker\Generator as Faker;

$factory->define(Ticket::class, function (Faker $faker) {
    return [
        'day'       			=> $faker->randomElement(['Saturday','Sunday','Monday','Tuesday','Wednesday']),
    	'date'       			=> $faker->date('Y-m-d'),
    	'duration'            	=> $faker->randomElement(['20 Minutes','10 Minutes','15 Minutes','25 Minutes']),
    	'time'       			=> $faker->date('H-i'),
    	'availability'          => $faker->randomElement(['YES','NO']),
    	'doc_id'       			=> rand(1,20),
    	'patient_id'            => rand(1,20),
    	'pharmacy_id'           => rand(1,20),
    	'lap_id'    			=> rand(1,20),
    	'doc_name'    			=> $faker->firstName,
    	'medical_report'    	=> $faker->sentence(25),
    	'listDrugs'    			=> $faker->randomElement([
    								'Cocaine Cannabis Caffeine',
    								'Ecstasy Alcohol goon',
    								'E-cigarettes moggies sleepers',
    								]),
    	'created_at'			=> now(),
    	'updated_at'			=> now(),
    ];
});
