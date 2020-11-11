<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateWalletsBalance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
        CREATE TRIGGER DoctorWalletTrigger BEFORE INSERT ON `doctor_wallets` FOR EACH ROW
            BEGIN
            DECLARE gtclose_balance double;
            DECLARE WalletTable CURSOR
            FOR SELECT close_balance from doctor_wallets 
            where doctor_id = NEW.doctor_id 
            ORDER BY  id DESC 
            LIMIT 1 ;
            OPEN WalletTable;
            IF(FOUND_ROWS() > 0) THEN 
            FETCH NEXT FROM WalletTable INTO gtclose_balance;
            IF(NEW.type = "C") THEN
                SET NEW.open_balance = gtclose_balance;
                SET NEW.close_balance = gtclose_balance - NEW.amount ;
                SET NEW.amount = - NEW.amount;
            else 
                SET NEW.open_balance = gtclose_balance;
                SET NEW.close_balance = gtclose_balance + NEW.amount ;
            END IF;
            else
            IF(NEW.type = "C") THEN
                SET NEW.open_balance = 0;
                SET NEW.close_balance = -NEW.amount ;
                SET NEW.amount = - NEW.amount;
            else 
                SET NEW.open_balance = 0;
                SET NEW.close_balance = NEW.amount ;
            END IF;
            END IF;
            SET NEW.created_at = CURRENT_TIMESTAMP;
            CLOSE WalletTable;
                UPDATE doctors
                SET wallet_balance = NEW.close_balance
                WHERE id = NEW.doctor_id ;
            END
        ');
        DB::unprepared('            
            CREATE TRIGGER AdminWalletTrigger BEFORE INSERT ON `admin_wallets` FOR EACH ROW
            BEGIN
            DECLARE gtclose_balance double;
            DECLARE WalletTable CURSOR
            FOR SELECT close_balance from admin_wallets 
            ORDER BY  id DESC 
            LIMIT 1 ;
            OPEN WalletTable;
            IF(FOUND_ROWS() > 0) THEN 
            FETCH NEXT FROM WalletTable INTO gtclose_balance;
            IF(NEW.type = "C") THEN
                SET NEW.open_balance = gtclose_balance;
                SET NEW.close_balance = gtclose_balance - NEW.amount ;
                SET NEW.amount = - NEW.amount;
            else 
                SET NEW.open_balance = gtclose_balance;
                SET NEW.close_balance = gtclose_balance + NEW.amount ;
            END IF;
            else
            IF(NEW.type = "C") THEN
                SET NEW.open_balance = 0;
                SET NEW.close_balance = -NEW.amount ;
                SET NEW.amount = - NEW.amount;
            else 
                SET NEW.open_balance = 0;
                SET NEW.close_balance = NEW.amount ;
            END IF;
            END IF;
            SET NEW.created_at = CURRENT_TIMESTAMP;
            CLOSE WalletTable;
            END
        ');
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER `DoctorWalletTrigger`');
        DB::unprepared('DROP TRIGGER `AdminWalletTrigger`');
    }
}
