<?php

namespace App\DTOs;

use Carbon\Carbon;

readonly class RateDTO
{
    public function __construct(
        public string $bankSlug,
        public string $currencyCode,
        public float $buyRate,
        public float $sellRate,
        public string $source,
        public Carbon $updatedAt,
    ) {}
}
