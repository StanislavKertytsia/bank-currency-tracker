<?php

namespace Database\Factories;

use App\Models\Bank;
use App\Models\Currency;
use App\Models\RateHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RateHistory>
 */
class RateHistoryFactory extends Factory
{
    public function definition(): array
    {
        $buyRate = fake()->randomFloat(4, 30, 60);
        $previousBuyRate = fake()->randomFloat(4, 30, 60);

        return [
            'bank_id' => Bank::factory(),
            'currency_id' => Currency::factory(),
            'buy_rate' => $buyRate,
            'sell_rate' => fake()->randomFloat(4, 30, 60),
            'previous_buy_rate' => $previousBuyRate,
            'previous_sell_rate' => fake()->randomFloat(4, 30, 60),
            'change_percent' => round(abs($buyRate - $previousBuyRate) / $previousBuyRate * 100, 2),
            'source' => 'minfin',
            'recorded_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
