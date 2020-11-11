<?php

use Illuminate\Database\Seeder;

class ticketsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	DB::table('tickets')->truncate();
        factory( App\Ticket::class , 50 )->create();
    }
}
