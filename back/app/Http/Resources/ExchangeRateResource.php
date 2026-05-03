<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExchangeRateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'bank_id'     => $this->bank_id,
            'currency_id' => $this->currency_id,
            'bank'        => new BankResource($this->whenLoaded('bank')),
            'currency'    => new CurrencyResource($this->whenLoaded('currency')),
            'buy_rate'    => (float) $this->buy_rate,
            'sell_rate'   => (float) $this->sell_rate,
            'source'      => $this->source,
            'updated_at'  => $this->updated_at?->toISOString(),
        ];
    }
}
