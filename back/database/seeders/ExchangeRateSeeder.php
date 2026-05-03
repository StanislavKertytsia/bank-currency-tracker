<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Database\Seeder;

class ExchangeRateSeeder extends Seeder
{
    /**
     * Seed rates keyed by source → bank slug → currency code → [buy, sell].
     * Values are approximate real rates as of May 2026.
     *
     * @var array<string, array<string, array<string, array{float, float}>>>
     */
    private array $seedData = [
        'privatbank' => [
            'privatbank' => [
                'USD' => [43.50, 44.10],
                'EUR' => [50.95, 51.95],
            ],
        ],
        'monobank' => [
            'monobank' => [
                'USD' => [43.80, 44.23],
                'EUR' => [51.35, 52.05],
                'GBP' => [59.83, 61.03],
                'CHF' => [56.32, 57.46],
                'PLN' => [12.17, 12.41],
            ],
        ],
        'derived' => [
            'oschadbank' => [
                'USD' => [43.34, 44.59],
                'EUR' => [50.76, 52.53],
                'GBP' => [59.55, 61.60],
                'CHF' => [56.09, 57.93],
                'PLN' => [12.11, 12.51],
            ],
            'pumb' => [
                'USD' => [43.40, 44.51],
                'EUR' => [50.84, 52.44],
                'GBP' => [59.63, 61.49],
                'CHF' => [56.16, 57.82],
                'PLN' => [12.12, 12.49],
            ],
            'ukreximbank' => [
                'USD' => [43.28, 44.68],
                'EUR' => [50.70, 52.62],
                'GBP' => [59.47, 61.71],
                'CHF' => [56.02, 58.03],
                'PLN' => [12.10, 12.53],
            ],
        ],
        'nbu' => [
            'nbu' => [
                'USD' => [43.91, 43.91],
                'EUR' => [51.50, 51.50],
                'GBP' => [60.43, 60.43],
                'CHF' => [56.88, 56.88],
                'PLN' => [12.29, 12.29],
            ],
        ],
    ];

    public function run(): void
    {
        // Remove stale minfin records left over from the old MinfinProvider integration.
        ExchangeRate::where('source', 'minfin')->delete();

        $banks = Bank::all()->keyBy('slug');
        $currencies = Currency::all()->keyBy('code');

        $nbuBank = Bank::firstOrCreate(
            ['slug' => 'nbu'],
            [
                'name' => 'НБУ',
                'description' => 'Національний банк України',
                'website' => 'https://bank.gov.ua',
                'email' => 'nbu@bank.gov.ua',
                'address' => 'м. Київ, вул. Інститутська, 9',
                'rating' => 0.0,
            ],
        );

        $banks->put('nbu', $nbuBank);

        foreach ($this->seedData as $source => $bankMap) {
            foreach ($bankMap as $slug => $currencyRates) {
                $bank = $banks->get($slug);

                if (! $bank) {
                    continue;
                }

                foreach ($currencyRates as $code => [$buy, $sell]) {
                    $currency = $currencies->get($code);

                    if (! $currency) {
                        continue;
                    }

                    ExchangeRate::updateOrCreate(
                        ['bank_id' => $bank->id, 'currency_id' => $currency->id, 'source' => $source],
                        ['buy_rate' => $buy, 'sell_rate' => $sell],
                    );
                }
            }
        }
    }
}
