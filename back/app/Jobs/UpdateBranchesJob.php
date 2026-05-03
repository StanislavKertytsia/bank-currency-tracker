<?php

namespace App\Jobs;

use App\Models\Bank;
use App\Models\Branch;
use App\Services\Providers\FinanceUaProvider;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class UpdateBranchesJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 300;

    public function handle(): void
    {
        /** @var FinanceUaProvider $provider */
        $provider = app('branch.provider.finance_ua');

        $banks = Bank::where('slug', '!=', 'nbu')->get();

        $totalUpserted = 0;

        foreach ($banks as $bank) {
            $branchDTOs = $provider->getBranches($bank->slug);

            foreach ($branchDTOs as $dto) {
                Branch::updateOrCreate(
                    [
                        'bank_id'   => $bank->id,
                        'latitude'  => $dto->latitude,
                        'longitude' => $dto->longitude,
                    ],
                    [
                        'branch_name' => $dto->name,
                        'address'     => $dto->address,
                        'phone'       => $dto->phone,
                    ],
                );

                $totalUpserted++;
            }

            Log::info('UpdateBranchesJob: branches updated for bank', [
                'bank' => $bank->slug,
                'count' => count($branchDTOs),
            ]);
        }

        Log::info('UpdateBranchesJob completed', ['total_upserted' => $totalUpserted]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('UpdateBranchesJob failed', [
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
