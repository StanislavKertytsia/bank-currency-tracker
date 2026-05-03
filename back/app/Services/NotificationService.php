<?php

namespace App\Services;

use App\Models\RateHistory;
use App\Models\User;
use App\Notifications\RateChangeNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    public function notifyOnRateChange(RateHistory $history): void
    {
        $history->loadMissing(['bank', 'currency']);

        $bankId = $history->bank_id;
        $currencyId = $history->currency_id;

        $users = User::where('notification_enabled', true)
            ->where(function ($query) use ($bankId, $currencyId) {
                $query->whereDoesntHave('subscriptions')
                    ->orWhereHas('subscriptions', function ($q) use ($bankId, $currencyId) {
                        $q->where('bank_id', $bankId)
                            ->orWhere('currency_id', $currencyId);
                    });
            })
            ->get();

        if ($users->isEmpty()) {
            return;
        }

        Notification::send($users, new RateChangeNotification($history));

        Log::info('NotificationService: rate change notifications dispatched', [
            'bank' => $history->bank->slug ?? $bankId,
            'currency' => $history->currency->code ?? $currencyId,
            'source' => $history->source,
            'recipients' => $users->count(),
        ]);
    }
}
