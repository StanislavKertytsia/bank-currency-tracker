<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_id')->constrained()->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained()->cascadeOnDelete();
            $table->decimal('buy_rate', 10, 4);
            $table->decimal('sell_rate', 10, 4);
            $table->enum('source', ['nbu', 'minfin']);
            $table->timestamps();

            $table->unique(['bank_id', 'currency_id', 'source']);
            $table->index(['bank_id', 'currency_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
