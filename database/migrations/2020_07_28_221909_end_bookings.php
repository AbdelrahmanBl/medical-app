<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EndBookings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('end_bookings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('booking_id');
 
            $table->string('cancel_reason',150)->nullable();
            $table->string('image',100)->nullable();
            $table->string('note',150)->nullable();
            
            $table->double('commission',6,2)->nullable();
            $table->double('commission_per',6,2)->nullable();
            // $table->enum('status',['COMPLETED','CANCELLED']);

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
        DB::unprepared('DROP Table `end_bookings`');
    }
}
