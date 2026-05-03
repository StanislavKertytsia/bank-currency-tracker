<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE exchange_rates MODIFY COLUMN source ENUM('nbu','minfin','privatbank','monobank','derived') NOT NULL");
        DB::statement("ALTER TABLE rate_histories MODIFY COLUMN source ENUM('nbu','minfin','privatbank','monobank','derived') NOT NULL");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE exchange_rates MODIFY COLUMN source ENUM('nbu','minfin') NOT NULL");
        DB::statement("ALTER TABLE rate_histories MODIFY COLUMN source ENUM('nbu','minfin') NOT NULL");
    }
};
