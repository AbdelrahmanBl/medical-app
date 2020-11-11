<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Doctors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('hospital_name');
            $table->string('specialities');
            $table->string('mobile_number');
            $table->string('phone_number');
            $table->string('email',100)->unique();
            $table->string('password');
            $table->string('info');
            $table->string('city');
            $table->string('image')->nullable();

            $table->double('fees',6,2);
            $table->double('wallet_balance',6,2)->default(0);
            $table->integer('rate')->default(0);
            $table->integer('visitor_rating')->default(0);
            $table->boolean('is_busy')->default(0);
            $table->boolean('is_approved')->default(0);

            $table->date('date_of_birth')->nullable();
            
            $table->enum('gender',['MALE', 'FEMALE']);
            $table->enum('status',['ON', 'OFF'])->default('ON');

            $table->rememberToken();
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
        DB::unprepared('DROP Table `doctors`');
    }
}
