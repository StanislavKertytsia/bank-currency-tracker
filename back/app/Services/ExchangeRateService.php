<?php

namespace App\Services;

use App\DTOs\RateDTO;
use App\Models\Bank;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\RateHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    private const SIGNIFICANT_CHANGE_THRESHOLD = 5.0;

    public function upsertRate(RateDTO $dto): ?RateHistory
    {
        $bank = $this->resolveBank($dto->bankSlug, $dto->source);

        if ($bank === null) {
            Log::warning('ExchangeRateService: bank not found, skipping', ['slug' => $dto->bankSlug]);

            return null;
        }

        $currency = Currency::where('code', $dto->currencyCode)->first();

        if ($currency === null) {
            Log::warning('ExchangeRateService: currency not found, skipping', ['code' => $dto->currencyCode]);

            return null;
        }

        return DB::transaction(function () use ($dto, $bank, $currency) {
            $current = ExchangeRate::where([
                'bank_id' => $bank->id,
                'currency_id' => $currency->id,
                'source' => $dto->source,
            ])->lockForUpdate()->first();

            $rateHistory = null;

            if ($current !== null && (float) $current->buy_rate > 0) {
                $oldBuy = (float) $current->buy_rate;
                $changePercent = abs($dto->buyRate - $oldBuy) / $oldBuy * 100;

                if ($changePercent >= self::SIGNIFICANT_CHANGE_THRESHOLD) {
                    $rateHistory = RateHistory::create([
                        'bank_id' => $bank->id,
                        'currency_id' => $currency->id,
                        'buy_rate' => $dto->buyRate,
                        'sell_rate' => $dto->sellRate,
                        'previous_buy_rate' => $current->buy_rate,
                        'previous_sell_rate' => $current->sell_rate,
                        'change_percent' => round($changePercent, 2),
                        'source' => $dto->source,
                        'recorded_at' => now(),
                    ]);

                    Log::info('ExchangeRateService: significant rate change recorded', [
                        'bank' => $dto->bankSlug,
                        'currency' => $dto->currencyCode,
                        'source' => $dto->source,
                        'old_buy' => $current->buy_rate,
                        'new_buy' => $dto->buyRate,
                        'change_percent' => $rateHistory->change_percent,
                    ]);
                }
            }

            if ($current !== null) {
                $current->update([
                    'buy_rate' => $dto->buyRate,
                    'sell_rate' => $dto->sellRate,
                    'updated_at' => $dto->updatedAt,
                ]);
            } else {
                ExchangeRate::create([
                    'bank_id' => $bank->id,
                    'currency_id' => $currency->id,
                    'source' => $dto->source,
                    'buy_rate' => $dto->buyRate,
                    'sell_rate' => $dto->sellRate,
                    'updated_at' => $dto->updatedAt,
                ]);
            }

            return $rateHistory;
        });
    }

    private function resolveBank(string $slug, string $source): ?Bank
    {
        if ($slug === 'nbu' && $source === 'nbu') {
            return Bank::firstOrCreate(
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
        }

        return Bank::where('slug', $slug)->first();
    }
}
