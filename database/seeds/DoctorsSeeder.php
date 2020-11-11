<?php

use Illuminate\Database\Seeder;
use App\Doctor;
class DoctorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	Doctor::truncate();
        factory( Doctor::class , 50 )->create();
    }
}
