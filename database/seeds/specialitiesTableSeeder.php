<?php

use Illuminate\Database\Seeder;
use App\Speciality; 

class specialitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Speciality::truncate();
        Speciality::insert([
        	[
        		'speciality'  =>  'heart'
        	],
        	[
        		'speciality'  =>  'surgery'
        	],
        	[
        		'speciality'  =>  'sight'
        	],
        	[
        		'speciality'  =>  'kids'
        	],
        	[
        		'speciality'  =>  'bones'
        	]
        ]);
    }
}
