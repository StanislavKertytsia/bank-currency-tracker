<?php

namespace App\Jobs;

use App\Services\ExchangeRateService;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UpdateExchangeRatesJob implements ShouldQueue
{
    use Queueable;

    private const CURRENCIES = ['USD', 'EUR', 'GBP', 'CHF', 'PLN'];

    public int $tries = 3;

    public int $backoff = 60;

    /** Banks with no public API — rates are sourced from Minfin. */
    private const MINFIN_ONLY_SLUGS = ['oschadbank', 'pumb', 'ukreximbank'];

    public function handle(
        ExchangeRateService $rateService,
        NotificationService $notificationService,
    ): void {
        $privatBankProvider = app('exchange_rate.provider.privatbank');
        $monoBankProvider = app('exchange_rate.provider.monobank');
        $nbuProvider = app('exchange_rate.provider.nbu');
        $minfinProvider = app('exchange_rate.provider.minfin');

        $processed = 0;
        $changed = 0;

        // Step 1: NBU official rates.
        foreach (self::CURRENCIES as $currency) {
            foreach ($nbuProvider->getRates($currency) as $dto) {
                $history = $rateService->upsertRate($dto);
                $processed++;

                if ($history !== null) {
                    $changed++;
                    $notificationService->notifyOnRateChange($history);
                }
            }
        }

        // Step 2: Direct bank API rates (PrivatBank, MonoBank).
        foreach ([$privatBankProvider, $monoBankProvider] as $provider) {
            foreach (self::CURRENCIES as $currency) {
                foreach ($provider->getRates($currency) as $dto) {
                    $history = $rateService->upsertRate($dto);
                    $processed++;

                    if ($history !== null) {
                        $changed++;
                        $notificationService->notifyOnRateChange($history);
                    }
                }
            }
        }

        // Step 3: Minfin rates for banks without a direct public API.
        foreach (self::CURRENCIES as $currency) {
            foreach ($minfinProvider->getRates($currency) as $dto) {
                if (! in_array($dto->bankSlug, self::MINFIN_ONLY_SLUGS, true)) {
                    continue;
                }

                $history = $rateService->upsertRate($dto);
                $processed++;

                if ($history !== null) {
                    $changed++;
                    $notificationService->notifyOnRateChange($history);
                }
            }
        }

        Cache::tags(['rates', 'nbu'])->flush();

        Log::info('UpdateExchangeRatesJob completed', [
            'processed' => $processed,
            'significant_changes' => $changed,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('UpdateExchangeRatesJob failed', [
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
