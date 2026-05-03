<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropIndex(['latitude', 'longitude']);
            $table->unique(['bank_id', 'latitude', 'longitude'], 'branches_bank_lat_lng_unique');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropUnique('branches_bank_lat_lng_unique');
            $table->index(['latitude', 'longitude']);
        });
    }
};
