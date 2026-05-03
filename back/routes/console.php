<?php

use App\Jobs\UpdateBranchesJob;
use App\Jobs\UpdateExchangeRatesJob;
use Illuminate\Support\Facades\Schedule;

Schedule::job(UpdateExchangeRatesJob::class)->everyThirtyMinutes()->withoutOverlapping();
Schedule::job(UpdateBranchesJob::class)->dailyAt('03:00')->withoutOverlapping();
