<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['code' => 'USD', 'name' => 'Долар США'],
            ['code' => 'EUR', 'name' => 'Євро'],
            ['code' => 'GBP', 'name' => 'Фунт стерлінгів'],
            ['code' => 'CHF', 'name' => 'Швейцарський франк'],
            ['code' => 'PLN', 'name' => 'Польський злотий'],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(['code' => $currency['code']], $currency);
        }
    }
}
