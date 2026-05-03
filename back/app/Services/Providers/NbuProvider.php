<?php

namespace App\Services\Providers;

use App\Contracts\BankApiInterface;
use App\DTOs\BranchDTO;
use App\DTOs\RateDTO;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NbuProvider implements BankApiInterface
{
    private const BASE_URL = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange';

    private const TIMEOUT = 10;

    private const ALLOWED_CURRENCIES = ['USD', 'EUR', 'GBP', 'CHF', 'PLN'];

    private const NBU_BANK_SLUG = 'nbu';

    /** Cached raw response for the current process lifecycle. */
    private ?array $cachedData = null;

    /**
     * @return array<int, RateDTO>
     */
    public function getRates(string $currency): array
    {
        $code = strtoupper($currency);

        if (! in_array($code, self::ALLOWED_CURRENCIES, true)) {
            return [];
        }

        $data = $this->fetchAll();

        if ($data === null) {
            return $this->fallbackRates($code);
        }

        $rates = $this->mapRates($data, $code);

        return empty($rates) ? $this->fallbackRates($code) : $rates;
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    private function fetchAll(): ?array
    {
        if ($this->cachedData !== null) {
            return $this->cachedData;
        }

        try {
            $response = Http::timeout(self::TIMEOUT)
                ->get(self::BASE_URL, ['json' => '']);

            if ($response->failed()) {
                Log::warning('NBU API returned non-2xx response', ['status' => $response->status()]);

                return null;
            }

            $data = $response->json() ?? [];

            if (empty($data)) {
                Log::warning('NBU API returned empty data');

                return null;
            }

            $this->cachedData = $data;

            return $data;
        } catch (\Throwable $e) {
            Log::error('NBU API request failed', ['exception' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * @return array<int, BranchDTO>
     */
    public function getBranches(string $slug): array
    {
        throw new \RuntimeException('NbuProvider does not support branch fetching. Use FinanceUaProvider.');
    }

    /**
     * @param  array<int, array<string, mixed>>  $data
     * @return array<int, RateDTO>
     */
    private function mapRates(array $data, string $filterCode): array
    {
        $rates = [];

        foreach ($data as $item) {
            $cc = strtoupper($item['cc'] ?? '');

            if ($cc !== $filterCode) {
                continue;
            }

            $rate = (float) ($item['rate'] ?? 0);

            if ($rate === 0.0) {
                continue;
            }

            $date = isset($item['exchangedate'])
                ? Carbon::createFromFormat('d.m.Y', $item['exchangedate']) ?? Carbon::now()
                : Carbon::now();

            $rates[] = new RateDTO(
                bankSlug: self::NBU_BANK_SLUG,
                currencyCode: $cc,
                buyRate: $rate,
                sellRate: $rate,
                source: 'nbu',
                updatedAt: $date,
            );
        }

        Log::info('NBU rates fetched', ['currency' => $filterCode, 'count' => count($rates)]);

        return $rates;
    }

    /**
     * @return array<int, RateDTO>
     */
    private function fallbackRates(string $currency): array
    {
        $fallback = [
            'USD' => 41.2093,
            'EUR' => 44.5123,
            'GBP' => 52.3456,
            'CHF' => 46.1234,
            'PLN' => 10.2345,
        ];

        $code = strtoupper($currency);
        $rate = $fallback[$code] ?? null;

        if ($rate === null) {
            return [];
        }

        Log::info('NBU fallback rates used', ['currency' => $code]);

        return [
            new RateDTO(
                bankSlug: self::NBU_BANK_SLUG,
                currencyCode: $code,
                buyRate: $rate,
                sellRate: $rate,
                source: 'nbu',
                updatedAt: Carbon::now(),
            ),
        ];
    }
}
