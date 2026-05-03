<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'logo_url'    => $this->logo_url
                ? (str_starts_with($this->logo_url, 'http') ? $this->logo_url : asset($this->logo_url))
                : null,
            'rating'      => (float) $this->rating,
            'phone'       => $this->phone,
            'email'       => $this->email,
            'slug'        => $this->slug,
            'description' => $this->description,
            'address'     => $this->address,
            'website'     => $this->website,
        ];
    }
}
