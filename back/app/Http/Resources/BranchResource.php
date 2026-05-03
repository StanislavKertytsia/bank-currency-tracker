<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bank_id' => $this->bank_id,
            'branch_name' => $this->branch_name,
            'address' => $this->address,
            'phone' => $this->phone,
            'lat' => (float) $this->latitude,
            'lng' => (float) $this->longitude,
            'distance' => $this->when(
                isset($this->resource->distance_meters),
                fn () => round((float) $this->resource->distance_meters, 1)
            ),
        ];
    }
}
