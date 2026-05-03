<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExchangeRateResource;
use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class RateController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'bank_id' => ['nullable', 'integer', 'exists:banks,id'],
            'currency_id' => ['nullable', 'integer', 'exists:currencies,id'],
        ]);

        $query = ExchangeRate::with(['bank', 'currency'])
            ->where('source', '!=', 'nbu');

        if ($request->filled('bank_id')) {
            $query->where('bank_id', $request->integer('bank_id'));
        }

        if ($request->filled('currency_id')) {
            $query->where('currency_id', $request->integer('currency_id'));
        }

        return ExchangeRateResource::collection($query->get());
    }

    public function nbu(): JsonResponse
    {
        $data = Cache::tags(['rates', 'nbu'])->remember('rates.nbu', now()->addMinutes(30), function () {
            $nbuRates = ExchangeRate::with(['bank', 'currency'])
                ->where('source', 'nbu')
                ->get();

            $averages = ExchangeRate::with('currency')
                ->where('source', '!=', 'nbu')
                ->selectRaw('currency_id, AVG(buy_rate) as avg_buy, AVG(sell_rate) as avg_sell')
                ->groupBy('currency_id')
                ->get()
                ->keyBy('currency_id');

            $currencies = Currency::orderBy('code')->get();

            return [
                'nbu' => $nbuRates->map(fn (ExchangeRate $r) => [
                    'id' => $r->id,
                    'currency' => $r->currency ? [
                        'id' => $r->currency->id,
                        'code' => $r->currency->code,
                        'name' => $r->currency->name,
                    ] : null,
                    'buy_rate' => (float) $r->buy_rate,
                    'sell_rate' => (float) $r->sell_rate,
                    'source' => $r->source,
                    'updated_at' => $r->updated_at?->toISOString(),
                ])->values()->all(),
                'averages' => $currencies->map(function (Currency $currency) use ($averages) {
                    $avg = $averages->get($currency->id);

                    return [
                        'currency_id' => $currency->id,
                        'currency' => ['id' => $currency->id, 'code' => $currency->code, 'name' => $currency->name],
                        'avg_buy_rate' => $avg ? round((float) $avg->avg_buy, 4) : null,
                        'avg_sell_rate' => $avg ? round((float) $avg->avg_sell, 4) : null,
                    ];
                })->values()->all(),
            ];
        });

        return response()->json(['data' => $data]);
    }
}
