<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('exchange_rates')->where('source', 'minfin')->delete();
    }

    public function down(): void
    {
        // Intentionally empty — minfin data is no longer a valid source.
    }
};
