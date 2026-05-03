<?php

namespace App\Contracts;

use App\DTOs\BranchDTO;
use App\DTOs\RateDTO;

interface BankApiInterface
{
    /**
     * @return array<int, RateDTO>
     */
    public function getRates(string $currency): array;

    /**
     * @return array<int, BranchDTO>
     */
    public function getBranches(string $slug): array;
}
