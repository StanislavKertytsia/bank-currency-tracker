<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['bank_id', 'currency_id', 'buy_rate', 'sell_rate', 'previous_buy_rate', 'previous_sell_rate', 'change_percent', 'source', 'recorded_at'])]
class RateHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'buy_rate'           => 'decimal:4',
            'sell_rate'          => 'decimal:4',
            'previous_buy_rate'  => 'decimal:4',
            'previous_sell_rate' => 'decimal:4',
            'change_percent'     => 'decimal:2',
            'recorded_at'        => 'datetime',
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
