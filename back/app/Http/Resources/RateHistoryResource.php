<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RateHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'bank'                => new BankResource($this->whenLoaded('bank')),
            'currency'            => new CurrencyResource($this->whenLoaded('currency')),
            'buy_rate'            => (float) $this->buy_rate,
            'sell_rate'           => (float) $this->sell_rate,
            'previous_buy_rate'   => (float) $this->previous_buy_rate,
            'previous_sell_rate'  => (float) $this->previous_sell_rate,
            'change_percent'      => (float) $this->change_percent,
            'source'              => $this->source,
            'recorded_at'         => $this->recorded_at?->toISOString(),
        ];
    }
}
