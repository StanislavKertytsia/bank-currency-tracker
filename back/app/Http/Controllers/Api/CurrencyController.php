<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CurrencyResource;
use App\Models\Currency;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class CurrencyController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $attributes = Cache::tags(['currencies'])->remember('currencies.all', now()->addHours(24), function () {
            return Currency::orderBy('code')->get()->toArray();
        });

        return CurrencyResource::collection(Currency::hydrate($attributes));
    }
}
