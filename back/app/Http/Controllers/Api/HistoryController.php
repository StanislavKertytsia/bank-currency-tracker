<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RateHistoryResource;
use App\Models\RateHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HistoryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'from'        => ['nullable', 'date'],
            'to'          => ['nullable', 'date', 'after_or_equal:from'],
            'bank_id'     => ['nullable', 'integer', 'exists:banks,id'],
            'currency_id' => ['nullable', 'integer', 'exists:currencies,id'],
            'per_page'    => ['nullable', 'integer', 'min:5', 'max:50'],
            'page'        => ['nullable', 'integer', 'min:1'],
        ]);

        $query = RateHistory::with(['bank', 'currency'])
            ->orderBy('recorded_at', 'desc');

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

        $perPage = $request->integer('per_page', 15);

        return RateHistoryResource::collection($query->paginate($perPage));
    }
}
