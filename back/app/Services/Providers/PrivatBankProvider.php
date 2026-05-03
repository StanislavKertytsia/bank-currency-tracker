<?php

namespace App\Services\Providers;

use App\Contracts\BankApiInterface;
use App\DTOs\BranchDTO;
use App\DTOs\RateDTO;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PrivatBankProvider implements BankApiInterface
{
    private const BASE_URL = 'https://api.privatbank.ua/p24api/pubinfo';

    private const TIMEOUT = 10;

    private const BANK_SLUG = 'privatbank';

    private const SOURCE = 'privatbank';

    /** PrivatBank public API exposes only USD and EUR */
    private const SUPPORTED_CURRENCIES = ['USD', 'EUR'];

    /**
     * @return array<int, RateDTO>
     */
    public function getRates(string $currency): array
    {
        $code = strtoupper($currency);

        if (! in_array($code, self::SUPPORTED_CURRENCIES, true)) {
            return [];
        }

        try {
            $response = Http::timeout(self::TIMEOUT)
                ->get(self::BASE_URL, ['exchange' => '', 'coursid' => 5]);

            if ($response->failed()) {
                Log::warning('PrivatBank API returned non-2xx response', [
                    'currency' => $currency,
                    'status' => $response->status(),
                ]);

                return $this->fallbackRates($code);
            }

            $data = $response->json() ?? [];

            if (empty($data)) {
                Log::warning('PrivatBank API returned empty data', ['currency' => $currency]);

                return $this->fallbackRates($code);
            }

            $rates = $this->mapRates($data, $code);

            return empty($rates) ? $this->fallbackRates($code) : $rates;
        } catch (\Throwable $e) {
            Log::error('PrivatBank API request failed', [
                'currency' => $currency,
                'exception' => $e->getMessage(),
            ]);

            return $this->fallbackRates($code);
        }
    }

    /**
     * @return array<int, BranchDTO>
     */
    public function getBranches(string $slug): array
    {
        throw new \RuntimeException('PrivatBankProvider does not support branch fetching. Use FinanceUaProvider.');
    }

    /**
     * @param  array<int, array<string, mixed>>  $data
     * @return array<int, RateDTO>
     */
    private function mapRates(array $data, string $filterCode): array
    {
        $rates = [];

        foreach ($data as $item) {
            $ccy = strtoupper($item['ccy'] ?? '');
            $baseCcy = strtoupper($item['base_ccy'] ?? '');

            if ($ccy !== $filterCode || $baseCcy !== 'UAH') {
                continue;
            }

            $buy = (float) ($item['buy'] ?? 0);
            $sell = (float) ($item['sale'] ?? 0);

            if ($buy === 0.0 || $sell === 0.0) {
                continue;
            }

            $rates[] = new RateDTO(
                bankSlug: self::BANK_SLUG,
                currencyCode: $ccy,
                buyRate: $buy,
                sellRate: $sell,
                source: self::SOURCE,
                updatedAt: Carbon::now(),
            );
        }

        Log::info('PrivatBank rates fetched', ['currency' => $filterCode, 'count' => count($rates)]);

        return $rates;
    }

    /**
     * @return array<int, RateDTO>
     */
    private function fallbackRates(string $currency): array
    {
        $fallback = [
            'USD' => [43.50, 44.10],
            'EUR' => [50.95, 51.95],
        ];

        [$buy, $sell] = $fallback[$currency] ?? [0, 0];

        if ($buy === 0) {
            return [];
        }

        Log::info('PrivatBank fallback rates used', ['currency' => $currency]);

        return [
            new RateDTO(
                bankSlug: self::BANK_SLUG,
                currencyCode: $currency,
                buyRate: $buy,
                sellRate: $sell,
                source: self::SOURCE,
                updatedAt: Carbon::now(),
            ),
        ];
    }
}
