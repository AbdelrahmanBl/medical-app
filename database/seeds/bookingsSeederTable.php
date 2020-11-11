<?php

use Illuminate\Database\Seeder;
use App\Booking;
class bookingsSeederTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Booking::truncate();
        factory( Booking::class , 200 )->create();
    }
}
