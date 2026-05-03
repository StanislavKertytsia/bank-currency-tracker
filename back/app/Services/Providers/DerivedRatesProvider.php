<?php

namespace App\Services\Providers;

use App\DTOs\RateDTO;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Generates approximate exchange rates for banks that have no public API
 * (Oschadbank, PUMB, UkrExim) by applying a per-bank spread to NBU rates.
 *
 * Spread values are calibrated against historically observed bank margins.
 */
class DerivedRatesProvider
{
    private const SOURCE = 'derived';

    /**
     * Per-bank multipliers: [buy multiplier, sell multiplier].
     * E.g. buy = NBU_rate × 0.988 means the bank buys at 1.2% below NBU.
     *
     * @var array<string, array{float, float}>
     */
    private const BANK_SPREADS = [
        'oschadbank' => [0.988, 1.015],
        'pumb' => [0.990, 1.013],
        'ukreximbank' => [0.985, 1.018],
    ];

    /**
     * @param  array<string, RateDTO[]>  $nbuRatesByCode  NBU DTOs keyed by currency code
     * @return array<int, RateDTO>
     */
    public function deriveFromNbu(array $nbuRatesByCode): array
    {
        $derived = [];

        foreach ($nbuRatesByCode as $code => $dtos) {
            foreach ($dtos as $nbuDto) {
                foreach (self::BANK_SPREADS as $slug => [$buyMul, $sellMul]) {
                    $derived[] = new RateDTO(
                        bankSlug: $slug,
                        currencyCode: $code,
                        buyRate: round($nbuDto->buyRate * $buyMul, 4),
                        sellRate: round($nbuDto->sellRate * $sellMul, 4),
                        source: self::SOURCE,
                        updatedAt: Carbon::now(),
                    );
                }
            }
        }

        Log::info('DerivedRatesProvider: rates derived from NBU', ['count' => count($derived)]);

        return $derived;
    }
}
