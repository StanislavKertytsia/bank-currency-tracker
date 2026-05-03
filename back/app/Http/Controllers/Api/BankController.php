<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BankDetailResource;
use App\Http\Resources\BankResource;
use App\Models\Bank;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BankController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $banks = Bank::where('slug', '!=', 'nbu')->orderBy('name')->get();

        return BankResource::collection($banks);
    }

    public function show(Bank $bank): BankDetailResource
    {
        $bank->load([
            'exchangeRates.currency',
            'branches',
        ]);

        return new BankDetailResource($bank);
    }
}
