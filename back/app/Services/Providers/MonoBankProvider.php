<?php

namespace App\Services\Providers;

use App\Contracts\BankApiInterface;
use App\DTOs\BranchDTO;
use App\DTOs\RateDTO;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MonoBankProvider implements BankApiInterface
{
    private const BASE_URL = 'https://api.monobank.ua/bank/currency';

    private const TIMEOUT = 10;

    private const BANK_SLUG = 'monobank';

    private const SOURCE = 'monobank';

    private const UAH_CODE = 980;

    /**
     * ISO 4217 numeric code → string code.
     *
     * @var array<int, string>
     */
    private const CURRENCY_MAP = [
        840 => 'USD',
        978 => 'EUR',
        826 => 'GBP',
        756 => 'CHF',
        985 => 'PLN',
    ];

    /**
     * For currencies without buy/sell (only rateCross), apply ±1% spread.
     * MonoBank provides rateBuy/rateSell for USD and EUR; the rest are cross rates.
     */
    private const CROSS_SPREAD = 0.01;

    /** Cached raw response for the current process lifecycle. */
    private ?array $cachedData = null;

    /**
     * @return array<int, RateDTO>
     */
    public function getRates(string $currency): array
    {
        $code = strtoupper($currency);

        if (! in_array($code, self::CURRENCY_MAP, true)) {
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
     * @return array<int, BranchDTO>
     */
    public function getBranches(string $slug): array
    {
        throw new \RuntimeException('MonoBankProvider does not support branch fetching. Use FinanceUaProvider.');
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
            $response = Http::timeout(self::TIMEOUT)->get(self::BASE_URL);

            if ($response->failed()) {
                Log::warning('MonoBank API returned non-2xx response', ['status' => $response->status()]);

                return null;
            }

            $data = $response->json() ?? [];

            if (empty($data)) {
                Log::warning('MonoBank API returned empty data');

                return null;
            }

            $this->cachedData = $data;

            return $data;
        } catch (\Throwable $e) {
            Log::error('MonoBank API request failed', ['exception' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $data
     * @return array<int, RateDTO>
     */
    private function mapRates(array $data, string $filterCode): array
    {
        $rates = [];
        $targetNumeric = (int) array_search($filterCode, self::CURRENCY_MAP, true);

        foreach ($data as $item) {
            $codeA = (int) ($item['currencyCodeA'] ?? 0);
            $codeB = (int) ($item['currencyCodeB'] ?? 0);

            if ($codeA !== $targetNumeric || $codeB !== self::UAH_CODE) {
                continue;
            }

            if (isset($item['rateBuy'], $item['rateSell'])) {
                $buy = (float) $item['rateBuy'];
                $sell = (float) $item['rateSell'];
            } elseif (isset($item['rateCross'])) {
                $cross = (float) $item['rateCross'];
                $buy = round($cross * (1 - self::CROSS_SPREAD), 4);
                $sell = round($cross * (1 + self::CROSS_SPREAD), 4);
            } else {
                continue;
            }

            if ($buy === 0.0 || $sell === 0.0) {
                continue;
            }

            $date = isset($item['date']) ? Carbon::createFromTimestamp((int) $item['date']) : Carbon::now();

            $rates[] = new RateDTO(
                bankSlug: self::BANK_SLUG,
                currencyCode: $filterCode,
                buyRate: $buy,
                sellRate: $sell,
                source: self::SOURCE,
                updatedAt: $date,
            );
        }

        Log::info('MonoBank rates fetched', ['currency' => $filterCode, 'count' => count($rates)]);

        return $rates;
    }

    /**
     * @return array<int, RateDTO>
     */
    private function fallbackRates(string $currency): array
    {
        $fallback = [
            'USD' => [43.80, 44.23],
            'EUR' => [51.35, 52.05],
            'GBP' => [59.83, 61.03],
            'CHF' => [56.32, 57.46],
            'PLN' => [12.17, 12.41],
        ];

        [$buy, $sell] = $fallback[$currency] ?? [0, 0];

        if ($buy === 0) {
            return [];
        }

        Log::info('MonoBank fallback rates used', ['currency' => $currency]);

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
