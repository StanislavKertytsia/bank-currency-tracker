<?php

namespace App\Providers;

use App\Services\Providers\FinanceUaProvider;
use App\Services\Providers\MinfinProvider;
use App\Services\Providers\MonoBankProvider;
use App\Services\Providers\NbuProvider;
use App\Services\Providers\PrivatBankProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('exchange_rate.provider.privatbank', PrivatBankProvider::class);
        $this->app->bind('exchange_rate.provider.monobank', MonoBankProvider::class);
        $this->app->bind('exchange_rate.provider.nbu', NbuProvider::class);
        $this->app->bind('exchange_rate.provider.minfin', MinfinProvider::class);
        $this->app->bind('branch.provider.finance_ua', FinanceUaProvider::class);
    }

    public function boot(): void {}
}
