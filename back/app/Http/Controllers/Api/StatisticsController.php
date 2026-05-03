<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Currency;
use App\Models\RateHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'from'        => ['nullable', 'date'],
            'to'          => ['nullable', 'date', 'after_or_equal:from'],
            'bank_id'     => ['nullable', 'integer', 'exists:banks,id'],
            'currency_id' => ['nullable', 'integer', 'exists:currencies,id'],
        ]);

        $query = RateHistory::select([
            DB::raw('DATE(recorded_at) as date'),
            'bank_id',
            'currency_id',
            DB::raw('AVG(buy_rate) as avg_buy_rate'),
            DB::raw('AVG(sell_rate) as avg_sell_rate'),
            DB::raw('MIN(buy_rate) as min_buy_rate'),
            DB::raw('MAX(buy_rate) as max_buy_rate'),
        ])
            ->groupBy(DB::raw('DATE(recorded_at)'), 'bank_id', 'currency_id')
            ->orderBy(DB::raw('DATE(recorded_at)'));

        if ($request->filled('from')) {
            $query->where('recorded_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->where('recorded_at', '<=', $request->input('to').' 23:59:59');
        }

        if ($request->filled('bank_id')) {
            $query->where('bank_id', $request->integer('bank_id'));
        }

        if ($request->filled('currency_id')) {
            $query->where('currency_id', $request->integer('currency_id'));
        }

        $currencyMap = Currency::pluck('code', 'id');
        $bankMap = Bank::pluck('name', 'id');

        $rows = $query->get()->map(fn ($row) => [
            'recorded_at'   => $row->date,
            'bank_id'       => $row->bank_id,
            'bank_name'     => $bankMap[$row->bank_id] ?? null,
            'currency_id'   => $row->currency_id,
            'currency_code' => $currencyMap[$row->currency_id] ?? null,
            'buy_rate'      => round((float) $row->avg_buy_rate, 4),
            'sell_rate'     => round((float) $row->avg_sell_rate, 4),
            'min_buy_rate'  => round((float) $row->min_buy_rate, 4),
            'max_buy_rate'  => round((float) $row->max_buy_rate, 4),
        ]);

        return response()->json(['data' => $rows]);
    }
}
