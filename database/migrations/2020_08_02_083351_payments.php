<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Payments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('doctor_id');
            $table->string('transaction_id',15);
            $table->string('transaction_date',15);
            $table->string('transaction_refrence',30)->nullable(); 

            $table->enum('type', [
                    'BANK',
                    'CASH',
                ]);
            $table->double('amount', 6, 2)->default(0);
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
        DB::unprepared('DROP Table `payments`');
    }
}
