<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DoctorWallets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doctor_wallets', function (Blueprint $table) {
            $table->increments('id');
            $table->string('doctor_id',15);
            $table->string('transaction_id',15);
            $table->string('transaction_alias')->nullable();
            $table->string('transaction_desc')->nullable();            
            $table->enum('type', [
                    'C',
                    'D',
                ]);
            $table->double('amount', 15, 8)->default(0);
            $table->double('open_balance', 15, 8)->default(0);
            $table->double('close_balance', 15, 8)->default(0);
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
        DB::unprepared('DROP Table `doctor_wallets`');
    }
}
