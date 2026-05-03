<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubscriptionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $subscriptions = $request->user()
            ->subscriptions()
            ->with(['bank', 'currency'])
            ->get();

        return SubscriptionResource::collection($subscriptions);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'bank_id'     => ['nullable', 'integer', 'exists:banks,id'],
            'currency_id' => ['nullable', 'integer', 'exists:currencies,id'],
        ]);

        if (empty($data['bank_id']) && empty($data['currency_id'])) {
            return response()->json(['message' => 'Необхідно вказати bank_id або currency_id.'], 422);
        }

        $exists = $request->user()->subscriptions()
            ->where('bank_id', $data['bank_id'] ?? null)
            ->where('currency_id', $data['currency_id'] ?? null)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Підписка вже існує.'], 409);
        }

        $subscription = $request->user()->subscriptions()->create([
            'bank_id'     => $data['bank_id'] ?? null,
            'currency_id' => $data['currency_id'] ?? null,
        ]);

        $subscription->load(['bank', 'currency']);

        return response()->json(['data' => new SubscriptionResource($subscription)], 201);
    }

    public function destroy(Request $request, Subscription $subscription): JsonResponse
    {
        if ($subscription->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Доступ заборонено.'], 403);
        }

        $subscription->delete();

        return response()->json(['message' => 'Підписку видалено.']);
    }
}
