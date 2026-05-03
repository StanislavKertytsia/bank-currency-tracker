<?php

namespace App\DTOs;

readonly class BankDTO
{
    public function __construct(
        public string $name,
        public string $slug,
        public ?string $logoUrl,
        public ?string $phone,
        public ?string $email,
        public ?string $website,
        public ?string $address,
    ) {}
}
