<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 * schema="BookingResource",
 * title="Booking Resource",
 * description="Represents a user's booking for a class.",
 * @OA\Property(property="booking_id", type="integer", example=1),
 * @OA\Property(property="status", type="string", enum={"confirmed", "cancelled"}, example="confirmed"),
 * @OA\Property(property="booked_at", type="string", format="date-time", example="2025-10-30T14:30:00.000000Z"),
 * @OA\Property(property="class", ref="#/components/schemas/ClassModelResource"),
 * @OA\Property(property="partner", ref="#/components/schemas/PartnerResource")
 * )
 */
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