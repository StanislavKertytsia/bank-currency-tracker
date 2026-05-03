<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\Currency;
use App\Models\RateHistory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RateHistorySeeder extends Seeder
{
    private array $baseRates = [
        'USD' => ['buy' => 41.20, 'sell' => 41.80],
        'EUR' => ['buy' => 44.50, 'sell' => 45.20],
        'GBP' => ['buy' => 52.10, 'sell' => 53.00],
        'CHF' => ['buy' => 46.80, 'sell' => 47.60],
        'PLN' => ['buy' => 10.10, 'sell' => 10.35],
    ];

    public function run(): void
    {
        $banks      = Bank::where('slug', '!=', 'nbu')->get();
        $currencies = Currency::all()->keyBy('code');

        foreach ($banks as $bank) {
            foreach ($this->baseRates as $code => $base) {
                $currency = $currencies[$code] ?? null;

                if (! $currency) {
                    continue;
                }

                $buyRate  = $base['buy'];
                $sellRate = $base['sell'];

                for ($daysAgo = 90; $daysAgo >= 1; $daysAgo--) {
                    // ~5% random daily fluctuation to generate meaningful history
                    $delta       = (mt_rand(-500, 500) / 10000) * $buyRate;
                    $newBuyRate  = round(max($buyRate + $delta, 0.01), 4);
                    $newSellRate = round(max($sellRate + $delta * 1.01, 0.01), 4);

                    $changePercent = $buyRate > 0
                        ? round(abs($newBuyRate - $buyRate) / $buyRate * 100, 2)
                        : 0;

                    // Only record significant changes (>=1%) to keep history meaningful
                    if ($changePercent >= 1.0) {
                        RateHistory::create([
                            'bank_id'             => $bank->id,
                            'currency_id'         => $currency->id,
                            'buy_rate'            => $newBuyRate,
                            'sell_rate'           => $newSellRate,
                            'previous_buy_rate'   => $buyRate,
                            'previous_sell_rate'  => $sellRate,
                            'change_percent'      => $changePercent,
                            'source'              => 'minfin',
                            'recorded_at'         => Carbon::now()->subDays($daysAgo)->startOfDay(),
                        ]);
                    }

                    $buyRate  = $newBuyRate;
                    $sellRate = $newSellRate;
                }
            }
        }
    }
}
