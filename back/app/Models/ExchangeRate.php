<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['bank_id', 'currency_id', 'buy_rate', 'sell_rate', 'source'])]
class ExchangeRate extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'buy_rate'  => 'decimal:4',
            'sell_rate' => 'decimal:4',
        ];
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
