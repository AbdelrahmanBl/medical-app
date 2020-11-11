<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Patients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email',100)->unique();
            $table->string('password');
            $table->string('phone');
            $table->string('city');
            $table->string('image')->nullable();
            $table->enum('gender',['MALE', 'FEMALE']);
            $table->enum('status',['ON', 'OFF'])->default('ON');

            $table->date('date_of_birth');
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
        DB::unprepared('DROP Table `patients`');
    }
}
