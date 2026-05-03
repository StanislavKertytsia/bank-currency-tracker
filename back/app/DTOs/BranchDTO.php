<?php

namespace App\DTOs;

readonly class BranchDTO
{
    public function __construct(
        public string $bankSlug,
        public string $name,
        public string $address,
        public ?string $phone,
        public float $latitude,
        public float $longitude,
    ) {}
}
