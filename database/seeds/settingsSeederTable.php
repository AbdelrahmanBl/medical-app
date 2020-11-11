<?php

use Illuminate\Database\Seeder;
use App\Setting;
class settingsSeederTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::truncate();
        Setting::insert([
        	[
        		'key'    =>  'commission_per',
        		'value'  =>  10,
        	]
        ]); 
    }
}
