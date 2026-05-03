<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankDetailResource extends JsonResource
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
            'name'        => $this->name,
            'description' => $this->description,
            'logo_url'    => $this->logo_url
                ? (str_starts_with($this->logo_url, 'http') ? $this->logo_url : asset($this->logo_url))
                : null,
            'website'     => $this->website,
            'phone'       => $this->phone,
            'email'       => $this->email,
            'address'     => $this->address,
            'rating'      => (float) $this->rating,
            'slug'        => $this->slug,
            'rates'       => ExchangeRateResource::collection($this->whenLoaded('exchangeRates')),
            'branches'    => BranchResource::collection($this->whenLoaded('branches')),
        ];
    }
}
