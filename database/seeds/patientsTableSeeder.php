<?php

use Illuminate\Database\Seeder;
use App\Patient;
class patientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Patient::truncate();
        factory( Patient::class , 50 )->create();
    }
}
