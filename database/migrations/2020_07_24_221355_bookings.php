<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Bookings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('patient_id');
            $table->integer('doctor_id');
            $table->integer('ticket_id');
            $table->integer('rate_id')->nullable();

            $table->date('date');

            $table->enum('status',['WAITING', 'STARTED','COMPLETED','CANCELLED'])->default('WAITING');
            $table->enum('payment',['CASH', 'VISA'])->default('CASH');
            $table->double('total',6,2);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP Table `bookings`');
    }
}
