<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Зміна курсу валюти</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #1a56db; color: #fff; padding: 28px 32px; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 600; }
        .header p { margin: 6px 0 0; font-size: 14px; opacity: 0.85; }
        .body { padding: 32px; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; margin-bottom: 24px; }
        .badge.up { background: #dcfce7; color: #16a34a; }
        .badge.down { background: #fee2e2; color: #dc2626; }
        .meta { font-size: 15px; color: #374151; margin-bottom: 24px; }
        .meta span { font-weight: 600; color: #111827; }
        .rates-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .rates-table th { text-align: left; padding: 10px 16px; background: #f9fafb; font-size: 13px; color: #6b7280; font-weight: 500; border-bottom: 1px solid #e5e7eb; }
        .rates-table td { padding: 12px 16px; font-size: 15px; border-bottom: 1px solid #f3f4f6; }
        .rates-table tr:last-child td { border-bottom: none; }
        .old-rate { color: #9ca3af; text-decoration: line-through; }
        .new-rate { font-weight: 700; color: #111827; }
        .change-up { color: #16a34a; font-weight: 600; }
        .change-down { color: #dc2626; font-weight: 600; }
        .footer { background: #f9fafb; padding: 20px 32px; border-top: 1px solid #e5e7eb; font-size: 13px; color: #9ca3af; }
        .footer a { color: #6b7280; text-decoration: none; }
        .source-badge { font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>Зміна курсу валюти</h1>
        <p>{{ $bank->name }} &mdash; {{ $currency->name }} ({{ $currency->code }})</p>
    </div>

    <div class="body">
        @php
            $isUp = (float) $history->buy_rate > (float) $history->previous_buy_rate;
            $changeAbs = abs((float) $history->change_percent);
            $arrow = $isUp ? '↑' : '↓';
            $badgeClass = $isUp ? 'up' : 'down';
            $changeClass = $isUp ? 'change-up' : 'change-down';
        @endphp

        <div class="badge {{ $badgeClass }}">{{ $arrow }} {{ number_format($changeAbs, 2) }}%</div>

        <div class="meta">
            Банк: <span>{{ $bank->name }}</span><br>
            Валюта: <span>{{ $currency->code }} — {{ $currency->name }}</span><br>
            Зафіксовано: <span>{{ $history->recorded_at->format('d.m.Y H:i') }}</span>
            <span class="source-badge">&nbsp;/ {{ strtoupper($history->source) }}</span>
        </div>

        <table class="rates-table">
            <thead>
                <tr>
                    <th>Тип</th>
                    <th>Попередній курс</th>
                    <th>Новий курс</th>
                    <th>Зміна</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Купівля</td>
                    <td><span class="old-rate">{{ number_format((float) $history->previous_buy_rate, 4) }}</span></td>
                    <td><span class="new-rate">{{ number_format((float) $history->buy_rate, 4) }}</span></td>
                    <td>
                        @php $buyDiff = (float) $history->buy_rate - (float) $history->previous_buy_rate; @endphp
                        <span class="{{ $buyDiff >= 0 ? 'change-up' : 'change-down' }}">
                            {{ $buyDiff >= 0 ? '+' : '' }}{{ number_format($buyDiff, 4) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>Продаж</td>
                    <td><span class="old-rate">{{ number_format((float) $history->previous_sell_rate, 4) }}</span></td>
                    <td><span class="new-rate">{{ number_format((float) $history->sell_rate, 4) }}</span></td>
                    <td>
                        @php $sellDiff = (float) $history->sell_rate - (float) $history->previous_sell_rate; @endphp
                        <span class="{{ $sellDiff >= 0 ? 'change-up' : 'change-down' }}">
                            {{ $sellDiff >= 0 ? '+' : '' }}{{ number_format($sellDiff, 4) }}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">
        Ви отримали це повідомлення, бо підписані на сповіщення про зміни курсів.<br>
        Щоб відписатись — змініть налаштування у <a href="#">профілі</a>.
    </div>
</div>
</body>
</html>
