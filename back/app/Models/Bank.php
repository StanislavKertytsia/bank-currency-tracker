<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'logo_url', 'website', 'phone', 'email', 'address', 'rating', 'slug'])]
class Bank extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:1',
        ];
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function exchangeRates(): HasMany
    {
        return $this->hasMany(ExchangeRate::class);
    }

    public function rateHistories(): HasMany
    {
        return $this->hasMany(RateHistory::class);
    }
}
