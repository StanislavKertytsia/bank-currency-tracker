<?php

namespace Database\Factories;

use App\Models\Bank;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'bank_id' => Bank::factory(),
            'branch_name' => fake()->company().' відділення',
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'latitude' => fake()->randomFloat(7, 49.0, 52.0),
            'longitude' => fake()->randomFloat(7, 29.0, 33.0),
        ];
    }
}
