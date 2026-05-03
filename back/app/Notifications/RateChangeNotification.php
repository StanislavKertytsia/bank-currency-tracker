<?php

namespace App\Notifications;

use App\Models\RateHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RateChangeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly RateHistory $history,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->history->loadMissing(['bank', 'currency']);

        return (new MailMessage)
            ->subject($this->buildSubject())
            ->view('mail.rate_change', [
                'history' => $this->history,
                'bank' => $this->history->bank,
                'currency' => $this->history->currency,
            ]);
    }

    private function buildSubject(): string
    {
        $bank = $this->history->bank->name ?? 'Банк';
        $currency = $this->history->currency->code ?? '???';
        $direction = $this->history->buy_rate > $this->history->previous_buy_rate ? '↑' : '↓';
        $percent = abs((float) $this->history->change_percent);

        return "[{$direction}{$percent}%] Зміна курсу {$currency} — {$bank}";
    }
}
