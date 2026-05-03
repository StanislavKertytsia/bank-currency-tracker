<?php

namespace Database\Factories;

use App\Models\Bank;
use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExchangeRate>
 */
class ExchangeRateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'bank_id' => Bank::factory(),
            'currency_id' => Currency::factory(),
            'buy_rate' => fake()->randomFloat(4, 30, 60),
            'sell_rate' => fake()->randomFloat(4, 30, 60),
            'source' => 'minfin',
        ];
    }
}
