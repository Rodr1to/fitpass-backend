<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'booking_id' => $this->id,
            'status' => $this->status,
            'booked_at' => $this->created_at->toDateTimeString(),
            'class' => new ClassModelResource($this->whenLoaded('classModel')),
            'partner' => new PartnerResource($this->whenLoaded('classModel.partner')),
        ];
    }
}