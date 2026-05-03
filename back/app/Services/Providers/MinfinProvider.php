<?php

namespace App\Services\Providers;

use App\Contracts\BankApiInterface;
use App\DTOs\BranchDTO;
use App\DTOs\RateDTO;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MinfinProvider implements BankApiInterface
{
    private const BASE_URL = 'https://minfin.com.ua/api/currency';

    private const TIMEOUT = 10;

    /**
     * @return array<int, RateDTO>
     */
    public function getRates(string $currency): array
    {
        $code = strtolower($currency);

        try {
            $response = Http::timeout(self::TIMEOUT)
                ->get(self::BASE_URL."/rates/banks/{$code}");

            if ($response->failed()) {
                Log::warning('Minfin API returned non-2xx response', [
                    'currency' => $currency,
                    'status' => $response->status(),
                ]);

                return $this->fallbackRates($currency);
            }

            $data = $response->json('data') ?? [];

            if (empty($data)) {
                Log::warning('Minfin API returned empty data', ['currency' => $currency]);

                return $this->fallbackRates($currency);
            }

            return $this->mapRates($data, strtoupper($currency));
        } catch (\Throwable $e) {
            Log::error('Minfin API request failed', [
                'currency' => $currency,
                'exception' => $e->getMessage(),
            ]);

            return $this->fallbackRates($currency);
        }
    }

    /**
     * @return array<int, BranchDTO>
     */
    public function getBranches(string $slug): array
    {
        throw new \RuntimeException('MinfinProvider does not support branch fetching. Use FinanceUaProvider.');
    }

    /**
     * @param  array<int, array<string, mixed>>  $data
     * @return array<int, RateDTO>
     */
    private function mapRates(array $data, string $currencyCode): array
    {
        $rates = [];

        foreach ($data as $item) {
            $bankSlug = $item['organization']['slug'] ?? null;
            $bid = $item['bid'] ?? null;
            $ask = $item['ask'] ?? null;

            if ($bankSlug === null || $bid === null || $ask === null) {
                continue;
            }

            $date = isset($item['date']) ? Carbon::parse($item['date']) : Carbon::now();

            $rates[] = new RateDTO(
                bankSlug: $bankSlug,
                currencyCode: $currencyCode,
                buyRate: (float) $bid,
                sellRate: (float) $ask,
                source: 'minfin',
                updatedAt: $date,
            );
        }

        Log::info('Minfin rates fetched', ['currency' => $currencyCode, 'count' => count($rates)]);

        return $rates;
    }

    /**
     * @return array<int, RateDTO>
     */
    private function fallbackRates(string $currency): array
    {
        $fallback = [
            'USD' => ['privatbank' => [39.50, 40.10], 'monobank' => [39.45, 40.15], 'oschadbank' => [39.30, 40.20], 'pumb' => [39.40, 40.05], 'ukreximbank' => [39.35, 40.00]],
            'EUR' => ['privatbank' => [42.80, 43.50], 'monobank' => [42.75, 43.55], 'oschadbank' => [42.60, 43.60], 'pumb' => [42.70, 43.45], 'ukreximbank' => [42.65, 43.40]],
            'GBP' => ['privatbank' => [49.80, 50.60], 'monobank' => [49.75, 50.65], 'oschadbank' => [49.60, 50.70], 'pumb' => [49.70, 50.55], 'ukreximbank' => [49.65, 50.50]],
            'CHF' => ['privatbank' => [43.50, 44.20], 'monobank' => [43.45, 44.25], 'oschadbank' => [43.30, 44.30], 'pumb' => [43.40, 44.15], 'ukreximbank' => [43.35, 44.10]],
            'PLN' => ['privatbank' => [9.80, 10.10], 'monobank' => [9.75, 10.15], 'oschadbank' => [9.70, 10.20], 'pumb' => [9.78, 10.08], 'ukreximbank' => [9.72, 10.05]],
        ];

        $code = strtoupper($currency);
        $bankRates = $fallback[$code] ?? [];
        $rates = [];

        foreach ($bankRates as $slug => [$buy, $sell]) {
            $rates[] = new RateDTO(
                bankSlug: $slug,
                currencyCode: $code,
                buyRate: $buy,
                sellRate: $sell,
                source: 'minfin',
                updatedAt: Carbon::now(),
            );
        }

        Log::info('Minfin fallback rates used', ['currency' => $code, 'count' => count($rates)]);

        return $rates;
    }
}
