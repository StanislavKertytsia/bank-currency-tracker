<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_id')->constrained()->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained()->cascadeOnDelete();
            $table->decimal('buy_rate', 10, 4);
            $table->decimal('sell_rate', 10, 4);
            $table->decimal('previous_buy_rate', 10, 4);
            $table->decimal('previous_sell_rate', 10, 4);
            $table->decimal('change_percent', 5, 2);
            $table->enum('source', ['nbu', 'minfin']);
            $table->timestamp('recorded_at')->useCurrent();

            $table->index(['bank_id', 'currency_id', 'recorded_at']);
            $table->index('recorded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_histories');
    }
};
