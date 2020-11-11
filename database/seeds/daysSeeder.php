<?php

use Illuminate\Database\Seeder;
use App\Day; 

class daysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Day::truncate();
        Day::insert([
        	[
        		'day'  =>  'Saturday'
        	],
        	[
        		'day'  =>  'Sunday'
        	],
        	[
        		'day'  =>  'Monday'
        	],
        	[
        		'day'  =>  'Tuesday'
        	],
        	[
        		'day'  =>  'Wednesday'
        	],
        	[
        		'day'  =>  'Thursday'
        	],
        	[
        		'day'  =>  'Friday'
        	],

        ]);
    }
}
