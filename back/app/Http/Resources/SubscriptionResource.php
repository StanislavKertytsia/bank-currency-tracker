<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'bank'       => $this->bank_id ? new BankResource($this->whenLoaded('bank')) : null,
            'currency'   => $this->currency_id ? new CurrencyResource($this->whenLoaded('currency')) : null,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
